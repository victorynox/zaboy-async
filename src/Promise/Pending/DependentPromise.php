<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Pending;

use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Broker\PromiseBroker;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter as Store;
use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;

/**
 * DependentPromise
 *
 * @category   async
 * @package    zaboy
 */
class DependentPromise extends PendingPromise
{

    /**
     *
     * @param MySqlPromiseAdapter $promiseAdapter
     * @throws PromiseException
     */
    public function __construct(Store $promiseAdapter, $promiseData, $parentPromiseId = null, callable $onFulfilled = null, callable $onRejected = null)
    {
        parent::__construct($promiseAdapter, $promiseData);
        $this->promiseData[Store::PARENT_ID] = $parentPromiseId ? $parentPromiseId : $this->promiseData[Store::PARENT_ID];
        if (!isset($promiseData[Store::ON_FULFILLED])) {

        }
        $this->promiseData[Store::ON_FULFILLED] = !isset($promiseData[Store::ON_FULFILLED]) ? $this->serializeCallback($onFulfilled) : $promiseData[Store::ON_FULFILLED];
        $this->promiseData[Store::ON_REJECTED] = !isset($promiseData[Store::ON_REJECTED]) ? $this->serializeCallback($onRejected) : $promiseData[Store::ON_REJECTED];
    }

    public function resolve($value)
    {
        //parent promise is fulfilled by promise - we has new parent promise
        if ($value instanceof PromiseInterface) {
            $promiseIdOfResult = $value->getPromiseId();
            $this->promiseData[Store::PARENT_ID] = $promiseIdOfResult;
            return $this->promiseData;
        }
        //parent promise is fulfilled by value - we just resolve (there is not ON_FULFILLED)
        if (is_null($this->promiseData[Store::ON_FULFILLED])) {
            return parent::resolve($value);
        }
        //parent promise is fulfilled by value - we try run ON_FULFILLED callback
        $onFulfilledCallback = unserialize($this->promiseData[Store::ON_FULFILLED]);
        $result = call_user_func($onFulfilledCallback, $value);
        return parent::resolve($result);
    }

    protected function serializeCallback($callable)
    {
        if (is_null($callable)) {
            return null;
        }
        if ($callable instanceof \Closure) {
            $callable = new SerializableClosure($closure);
        }
        return serialize($callable);
    }

}
