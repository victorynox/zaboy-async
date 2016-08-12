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
class Client extends ClientAbstract implements PromiseInterface
{

    const EXCEPTION_CLASS = '\zaboy\async\Promise\PromiseException';

    protected function makeEntity($data = null)
    {
        $promise = new PendingPromise();
        try {
            $data = $promise->getData();
            $rowsCount = $this->store->insert($data);
        } catch (\Exception $e) {
            throw new PromiseException('Can\'t insert promiseData. Promise: ' . $promise->getId(), 0, $e);
        }
        if (!$rowsCount) {
            throw new PromiseException('Can\'t insert promiseData. Promise: ' . $promise->getId());
        }
        return $promise;
    }

    public function getState()
    {
        $data = $this->getStoredData();
        $state = $data[Store::STATE];
        return $state;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        $id = $this->runTransaction('then', $onFulfilled, $onRejected);
        return new static($this->store, $id);
    }

    public function resolve($value)
    {
        $id = $this->runTransaction('resolve', $value);
        return $id;
    }

    public function reject($value)
    {
        $id = $this->runTransaction('reject', $value);
        return $id;
    }

    public function wait($unwrap = true, $waitingTime = 60, $waitingCheckInterval = 1)
    {
        if (!$unwrap) {
            $id = $this->getId();
            $data = $this->getStoredData($id);
            $entityClass = $this->getClass();
            $promise = new $entityClass($data);
            $result = $promise->wait(false);
            return $result;
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

    protected function runTransaction($methodName, $param1 = null, $params2 = null)
    {
        $data = $this->getStoredData();
        $id = parent::runTransaction($methodName, $param1, $params2);
        $dataReturned = $this->getStoredData($id);
        if (
                $data[Store::STATE] === PromiseInterface::PENDING && (
                $dataReturned[Store::STATE] === PromiseInterface::FULFILLED ||
                $dataReturned[Store::STATE] === PromiseInterface::REJECTED)
        ) {
            $result = (new static($this->store, $id))->wait(false);
            $this->resolveDependent($result, $dataReturned[Store::STATE] === PromiseInterface::REJECTED);
        }

        return $id; //$dataReturned;
    }

    public function toArray()
    {
        return $this->getStoredData();
    }

    protected function resolveDependent($result, $isRejected)
    {
        //are dependent promises exist?
        $rowset = $this->store->select(array(Store::PARENT_ID => $this->id));
        $rowsetArray = $rowset->toArray();
        foreach ($rowsetArray as $dependentPromiseData) {
            $dependentPromiseId = $dependentPromiseData[Store::ID];
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

    protected function getClass($id = null)
    {
        $data = $this->getStoredData($id);
        switch (true) {
            case $data[Store::STATE] === PromiseInterface::FULFILLED:
                return '\zaboy\async\Promise\Promise\FulfilledPromise';
            case $data[Store::STATE] === PromiseInterface::REJECTED:
                return '\zaboy\async\Promise\Promise\RejectedPromise';
            case $data[Store::PARENT_ID] === null:
                return '\zaboy\async\Promise\Promise\PendingPromise';
            default:
                return '\zaboy\async\Promise\Promise\DependentPromise';
        }
    }

    protected function log($info)
    {
        //var_dump($info);
    }

}
