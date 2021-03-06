<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Promise;

use Opis\Closure\SerializableClosure;
use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Store;

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
     * @param Store $store
     * @throws PromiseException
     */
    public function __construct($promiseData, $parentPromiseId = null, callable $onFulfilled = null, callable $onRejected = null)
    {
        parent::__construct($promiseData);
        $this->data[Store::PARENT_ID] = $parentPromiseId ? $parentPromiseId : $this->data[Store::PARENT_ID];
        $this->data[Store::ON_FULFILLED] = !isset($promiseData[Store::ON_FULFILLED]) ? $this->serializeCallback($onFulfilled) : $promiseData[Store::ON_FULFILLED];
        $this->data[Store::ON_REJECTED] = !isset($promiseData[Store::ON_REJECTED]) ? $this->serializeCallback($onRejected) : $promiseData[Store::ON_REJECTED];
    }

    public function resolve($value)
    {
        //parent promise is fulfilled - we just resolve (there is not ON_FULFILLED)
        if (is_null($this->data[Store::ON_FULFILLED])) {
            return parent::resolve($value);
        }
        //parent promise is fulfilled by promise - we has new parent promise
        if ($value instanceof PromiseInterface) {
            $promiseIdOfResult = $value->getId();
            $this->data[Store::PARENT_ID] = $promiseIdOfResult;
            return $this->getData();
        }
        //parent promise is fulfilled by value - we try run ON_FULFILLED callback
        $onFulfilledCallback = unserialize($this->data[Store::ON_FULFILLED]);
        try {
            $result = call_user_func($onFulfilledCallback, $value);
        } catch (\Exception $ex) {
            return parent::reject($ex);
        }
        return parent::resolve($result);
    }

    public function reject($reason)
    {
        //parent promise is rejected - we just reject (there is not ON_REJECTED)
        if (is_null($this->data[Store::ON_REJECTED])) {
            return parent::reject($reason);
        }
        //parent promise is rejected by promise - we has new parent promise
        if ($reason instanceof PromiseInterface) {
            $promiseIdOfResult = $reason->getId();
            $this->data[Store::PARENT_ID] = $promiseIdOfResult;
            $this->data[Store::ON_FULFILLED] = $this->data[Store::ON_REJECTED];
            return $this->getData();
        }
        //parent promise is rejected by value - we try run ON_REJECTED callback
        $onRejectedCallback = unserialize($this->data[Store::ON_REJECTED]);
        try {
            $result = call_user_func($onRejectedCallback, $reason);
            // if $onRejectedCallback can not resolve problem it must throw exception
        } catch (\Exception $ex) {
            return parent::reject($ex);
        }
        // if $onRejectedCallback has resolved problem it return result
        return parent::resolve($result);
    }

    protected function serializeCallback($callable)
    {
        if (is_null($callable)) {
            return null;
        }
        if ($callable instanceof \Closure) {
            $callable = new SerializableClosure($callable);
        }
        return serialize($callable);
    }

}
