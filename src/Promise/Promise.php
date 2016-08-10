<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise;

use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Exception\TimeIsOutException;
use zaboy\async\Promise\Promise\PendingPromise;
use zaboy\async\Promise\PromiseAbstract;
use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\async\Promise\Store;
use zaboy\async\ClientAbstract;
use Zend\Db\Sql\Select;

/**
 * Promise
 *
 * @category   async
 * @package    zaboy
 */
class Promise extends ClientAbstract implements PromiseInterface
{

    public function __construct(Store $store, $promiseId = null)
    {
        if (!is_null($promiseId) && !static::isId($promiseId)) {
            throw new PromiseException('Wrong format $promiseId');
        }
        $this->store = $store;
        if (!isset($promiseId)) {
            $promise = new PendingPromise($store);
            $this->insertPromise($promise);
            $this->id = $promise->getId();
        } else {
            $this->id = $promiseId;
        }
    }

    public static function extractId($stringOrException, $promiseIdArray = [])
    {
        if (is_null($stringOrException)) {
            return $promiseIdArray;
        }
        if ($stringOrException instanceof \Exception) {
            $array = static::extractId($stringOrException->getPrevious(), $promiseIdArray);
            $promiseIdArray = static::extractId($stringOrException->getMessage(), $array);
            return $promiseIdArray;
        }
        $array = [];
        if (preg_match_all('/(promise__[0-9]{10}_[0-9]{6}__[a-zA-Z0-9_]{23})/', $stringOrException, $array)) {
            return array_merge(array_reverse($array[0]), $promiseIdArray);
        } else {
            return [];
        }
    }

    public function getState()
    {
        $promiseData = $this->getStoredPromiseData();
        $state = $promiseData[Store::STATE];
        return $state;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        $promiseId = $this->runTransaction('then', $onFulfilled, $onRejected);
        return new static($this->store, $promiseId);
    }

    public function resolve($value)
    {
        $promiseId = $this->runTransaction('resolve', $value);
        return $promiseId;
    }

    public function reject($value)
    {
        $promiseId = $this->runTransaction('reject', $value);
        return $promiseId;
    }

    public function wait($unwrap = true, $waitingTime = 60, $waitingCheckInterval = 1)
    {
        if (!$unwrap) {
            $promiseId = $this->getId();
            $promiseData = $this->getStoredPromiseData($promiseId);
            $promiseClass = $this->getClass();
            $promise = new $promiseClass($this->store, $promiseData);
            return $promise->wait(false);
        }
        $stepsNumber = $waitingTime / $waitingCheckInterval;
        $step = 0;
        do {
            $result = $this->wait(false);
            if (is_a($result, '\zaboy\async\Promise\Exception\RejectedException', true)) {
                throw $result;
            }
            if (!is_a($result, '\zaboy\async\Promise\Promise\PendingPromise', true)) {
                return $result;
            }
            //$result is pending promise in the end of the chain - we wait
            $step = $step + 1;
            if ($step <= $stepsNumber) {
                sleep($waitingCheckInterval); // if not last step
            }
        } while ($step <= $stepsNumber);
        $e = new TimeIsOutException($this->id);
        $result->reject($e);
        throw $e;
    }

    protected function insertPromise(PromiseAbstract $promise)
    {
        try {
            $promiseData = $promise->getData();
            $rowsCount = $this->store->insert($promiseData);
        } catch (\Exception $e) {
            throw new PromiseException('Can\'t insert promiseData. Promise: ' . $promise->getId(), 0, $e);
        }
        if (!$rowsCount) {
            throw new PromiseException('Can\'t insert promiseData. Promise: ' . $promise->getId());
        }
    }

    protected function runTransaction($methodName, $param1 = null, $params2 = null)
    {
        $identifier = Store::PROMISE_ID;
        $db = $this->store->getAdapter();
        $queryStrPromise = 'SELECT ' . Select::SQL_STAR
                . ' FROM ' . $db->platform->quoteIdentifier($this->store->getTable())
                . ' WHERE ' . $db->platform->quoteIdentifier($identifier) . ' = ?'
                . ' FOR UPDATE';
        try {
            $errorMsg = "Can\'t start transaction for $methodName";
            $db->getDriver()->getConnection()->beginTransaction();
            //is row with this index exist?
            $rowset = $db->query($queryStrPromise, array($this->id));
            $errorMsg = "Can not execute $methodName. Pomise is not exist.";
            if (is_null($rowset->current())) {
                throw new PromiseException( );
            }
            $promiseData = $rowset->current()->getArrayCopy();
            $promiseClass = $this->getClass();
            $promise = new $promiseClass($this->store, $promiseData);
            $errorMsg = "Can not execute $methodName. Class: $promiseClass";
            $promiseDataReturned = call_user_func([$promise, $methodName], $param1, $params2);
            if (!is_null($promiseDataReturned)) {
                $errorMsg = "Can not store->update.";
                $promiseId = $promiseDataReturned[Store::PROMISE_ID];
                unset($promiseDataReturned[Store::PROMISE_ID]);
                //or update promise
                $number = $this->store->update($promiseDataReturned, [Store::PROMISE_ID => $promiseId]);
                if (!$number) {
                    //or create new if absent
                    $promiseDataReturned[Store::PROMISE_ID] = $promiseId;
                    $this->store->insert($promiseDataReturned);
                }
            } else {
                $promiseDataReturned = $promiseData;
            }

            $db->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $db->getDriver()->getConnection()->rollback();
            throw new PromiseException($errorMsg . ' Pomise: ' . $this->id, 0, $e);
        }

        if (
                $promiseData[Store::STATE] === PromiseInterface::PENDING && (
                $promiseDataReturned[Store::STATE] === PromiseInterface::FULFILLED ||
                $promiseDataReturned[Store::STATE] === PromiseInterface::REJECTED)
        ) {
            $result = (new static($this->store, $promiseId))->wait(false);
            $this->resolveDependent($result, $promiseDataReturned[Store::STATE] === PromiseInterface::REJECTED);
        }

        return $promiseId; //$promiseDataReturned;
    }

    public function toArray()
    {
        return $this->getStoredPromiseData();
    }

    protected function resolveDependent($result, $isRejected)
    {
        //are dependent promises exist?
        $rowset = $this->store->select(array(Store::PARENT_ID => $this->id));
        $rowsetArray = $rowset->toArray();
        foreach ($rowsetArray as $dependentPromiseData) {
            $dependentPromiseId = $dependentPromiseData[Store::PROMISE_ID];
            $dependentPromise = new static($this->store, $dependentPromiseId);
            try {
                if (!$isRejected) {
                    $dependentPromise->resolve($result);
                } else {
                    $dependentPromise->reject($result);
                }
            } catch (\Exception $e) {
                $exception = new PromiseException('Can not resolve dependent Pomise: ' . $dependentPromiseId, 0, $e);
                $this->log($exception);
            }
        }
        return;
    }

    protected function getStoredPromiseData($promiseId = null)
    {
        $promiseId = !$promiseId ? $this->getId() : $promiseId;
        $where = [Store::PROMISE_ID => $promiseId];
        $rowset = $this->store->select($where);
        $promiseData = $rowset->current();
        if (!isset($promiseData)) {
            throw new PromiseException(
            "There is  not data in store  for promiseId: $promiseId"
            );
        } else {
            return $promiseData->getArrayCopy();
        }
    }

    protected function getClass($promiseId = null)
    {
        $promiseData = $this->getStoredPromiseData($promiseId);
        switch (true) {
            case $promiseData[Store::STATE] === PromiseInterface::FULFILLED:
                return '\zaboy\async\Promise\Promise\FulfilledPromise';
            case $promiseData[Store::STATE] === PromiseInterface::REJECTED:
                return '\zaboy\async\Promise\Promise\RejectedPromise';
            case $promiseData[Store::PARENT_ID] === null:
                return '\zaboy\async\Promise\Promise\PendingPromise';
            default:
                return '\zaboy\async\Promise\Promise\DependentPromise';
        }
    }

    protected function log($info)
    {
        //var_dump($info);
    }

    /**
     * Returns the Prefix for Id
     *
     * @return string
     */
    protected static function getPrefix()
    {
        return strtolower(substr(__CLASS__, strlen(__NAMESPACE__) + 1));
    }

}
