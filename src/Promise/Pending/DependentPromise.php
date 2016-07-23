<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Pending;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
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
    public function __construct(MySqlPromiseAdapter $promiseAdapter, $promiseData, $parentPromiseId = null, callable $onFulfilled = null, callable $onRejected = null)
    {
        parent::__construct($promiseAdapter, $promiseData);
        $this->promiseData[Store::PARENT_ID] = $parentPromiseId ? $parentPromiseId : $this->promiseData[Store::PARENT_ID];
        $this->promiseData[Store::ON_FULFILLED] = $onFulfilled ? $onFulfilled : $this->serializeCallback($onFulfilled);
        $this->promiseData[Store::ON_REJECTED] = $onRejected ? $onRejected : $this->serializeCallback($onRejected);
    }

    public function resolve($value)
    {
        $onFulfilledCallback = unserialize($onFulfilled);
        $resalt = call_user_func($onFulfilledCallback, $value);
        return parent::resolve($resalt);
    }

    protected function serializeCallback($callable)
    {
        if ($callable instanceof \Closure) {
            $callable = new SerializableClosure($closure);
        }
        return serialize($callable);
    }

}
