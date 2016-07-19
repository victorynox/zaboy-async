<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise;

use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Pending\PendingPromise;
use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter as Store;
use Zend\Db\Sql\Select;

/**
 * PromiseClient
 *
 * @category   async
 * @package    zaboy
 */
class PromiseClient implements PromiseInterface//extends PromiseAbstract//implements PromiseInterface
{

    /**
     *
     * @var \zaboy\async\Promise\Adapter\MySqlPromiseAdapter
     */
    public $promiseAdapter;

    /**
     *
     * @var string
     */
    public $promiseId;

    public function __construct(Store $promiseAdapter, $promiseId = null)
    {
        $this->promiseAdapter = $promiseAdapter;
        if (!isset($promiseId)) {
            $promise = new PendingPromise($promiseAdapter);
            $this->insertPromise($promise);
            $this->promiseId = $promise->getPromiseId();
        } else {
            $this->promiseId = $promiseId;
        }
    }

    public function getPromiseId()
    {
        return $this->promiseId;
    }

    public function getState()
    {
        $promiseData = $this->getPromiseData(true);
        $state = $promiseData[Store::STATE];
        return $state;
    }

    protected function getPromiseData($exceptionIfAbsent = false, $promiseId = null)
    {
        $promiseId = !$promiseId ? $this->promiseId : $promiseId;
        $where = [Store::PROMISE_ID => $promiseId];
        $rowset = $this->promiseAdapter->select($where);
        $promiseData = $rowset->current();
        if (!isset($promiseData)) {
            if ($exceptionIfAbsent) {
                throw new PromiseException(
                "There is  not data in store  for promiseId: $promiseId"
                );
            } else {
                return null;
            }
        } else {
            return $promiseData->getArrayCopy();
        }
    }

    public function resolve($value)
    {
        $promiseData = $this->runTransaction('resolve', $value);
        return $promiseData;
    }

    public function reject($value)
    {
        $promiseData = $this->runTransaction('reject', $value);
        return $promiseData;
    }

    public function wait($waitingTime = 60)
    {
        $waitingCheckInterval = 1;
        $rejected = false;

        for ($index = 0; $index < (int) $waitingTime / $waitingCheckInterval; $index++) {
            $result = $this->getPromiseId();
            //walk along the chain of resolved promises, wich resolved via another promise
            while (PendingPromise::isPromiseId($result)) {
                $promiseData = $this->getPromiseData(true, $result);
                $promiseClass = $promiseData[Store::CLASS_NAME];
                /* @var $promise PromiseInterface  */
                $promise = new $promiseClass($this->promiseAdapter, $promiseData);
                //if rejected promise present in chain of promises - result will reject
                $rejected = $rejected || ($promise->getState() === PromiseInterface::REJECTED);
                $result = $promise->wait();
            }
            //promise->wait() has returned value or $this (if it is pending)
            if (($result instanceof PromiseInterface)) {
                //there is pending promise in the end of the chain - we wait
                sleep($waitingCheckInterval);
                continue;
            }

            if ($rejected) {
                //promise->wait() has returned value, but has rejected promise in the chain -   throw exception
                if (empty($result)) {
                    throw new PromiseException('Pomise was rejected without Reason.');
                }
                if (is_a($result, \Exception, true)) {
                    throw new PromiseException('Pomise was rejected with exception', 0, $result);
                }
                throw new PromiseException('Pomise was rejected ' . strval($result));
            }
            //promise->wait() has returned value - Promise resolved successfully
            return $result;
        }
        //Time is out
        $e = new PromiseException('Time is out for wait Pomise: ' . $this->promiseId);
        $promise->reject($e);
        throw $e;
    }

    public function insertPromise(PromiseAbstract $promise)
    {
        try {
            $promiseData = $promise->getPromiseData();
            $rowsCount = $this->promiseAdapter->insert($promiseData);
        } catch (\Exception $e) {
            throw new PromiseException('Can\'t insert promiseData', 0, $e);
        }
        if (!$rowsCount) {
            throw new PromiseException('Can\'t insert promiseData', 0, $e);
        }
    }

    protected function runTransaction($methodName, $param1 = null, $params2 = null)
    {
        $identifier = Store::PROMISE_ID;
        $db = $this->promiseAdapter->getAdapter();
        $queryStrPromise = 'SELECT ' . Select::SQL_STAR
                . ' FROM ' . $db->platform->quoteIdentifier($this->promiseAdapter->getTable())
                . ' WHERE ' . $db->platform->quoteIdentifier($identifier) . ' = ?'
                . ' FOR UPDATE';
        try {
            $errorMsg = "Can\'t start transaction for $methodName";
            $db->getDriver()->getConnection()->beginTransaction();
            //is row with this index exist?
            $rowset = $db->query($queryStrPromise, array($this->promiseId));
            $errorMsg = "Can not execute $methodName. Pomise is not exist.";
            if (is_null($rowset->current())) {
                throw new PromiseException( );
            }
            $promiseData = $rowset->current()->getArrayCopy();
            $promiseClass = $promiseData[Store::CLASS_NAME];
            $promise = new $promiseClass($this->promiseAdapter, $promiseData);
            $errorMsg = "Can not execute $methodName. Class: $promiseClass";
            $promiseDataReturned = call_user_func([$promise, $methodName], $param1, $params2);
            if (!is_null($promiseDataReturned)) {
                $errorMsg = "Can not promiseAdapter->update.";
                $this->promiseAdapter->update($promiseDataReturned);
            } else {
                $promiseDataReturned = $promiseData;
            }

            $db->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $db->getDriver()->getConnection()->rollback();
            throw new PromiseException($errorMsg . ' Pomise: ' . $this->promiseId, 0, $e);
        }
        return $promiseDataReturned;
    }

}
