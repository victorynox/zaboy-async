<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Determined;

use zaboy\async\Promise\Determined\DeterminedPromise;
use zaboy\async\Promise\Pending\PendingPromise;
use zaboy\async\Promise\Pending\DependentPromise;
use GuzzleHttp\Promise\PromiseInterface;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Determined\Exception\RejectedException;
use zaboy\async\Promise\Determined\Exception\ReasonPendingException;
use zaboy\async\Promise\Determined\Exception\ReasonRejectedException;
use zaboy\async\Promise\PromiseAbstract;
use zaboy\async\Promise\Broker\PromiseBroker;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter as Store;
use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;
use zaboy\async\Promise\PromiseClient;

/**
 * FulfilledPromise
 *
 * @category   async
 * @package    zaboy
 */
class FulfilledPromise extends DeterminedPromise
{

    /**
     *
     * @param MySqlPromiseAdapter $promiseAdapter
     * @throws PromiseException
     */
    public function __construct(Store $promiseAdapter, $promiseData = [], $result = null)
    {
        parent::__construct($promiseAdapter, $promiseData);
        $this->promiseData[Store::STATE] = PromiseInterface::FULFILLED;
        if (!isset($this->promiseData[Store::RESULT]) && !is_null($result)) {
            $this->promiseData[Store::RESULT] = $this->serializeResult($result);
        }
    }

    public function getState()
    {
        return PromiseInterface::FULFILLED;
    }

    public function wait($unwrap = true)
    {
        if ($unwrap) {
            return new PromiseException('Do not try call wait(true)');
        }
        $result = $this->unserializeResult($this->promiseData[Store::RESULT]);
        if (PendingPromise::isPromiseId($result)) {
            $nextPromise = new PromiseClient($this->promiseAdapter, $result);
            $result = $nextPromise->wait(false);
        }
        return $result;
    }

    public function resolve($value)
    {
        if ($value != $this->promiseData[Store::RESULT]) {
            throw new PromiseException('Pomise already resolved.  Pomise: ' . $this->promiseData[Store::PROMISE_ID]);
        }
        return $this->promiseData;
    }

    public function reject($reason)
    {
        throw new PromiseException('Cannot reject a fulfilled promise.  Pomise: ' . $this->promiseData[Store::PROMISE_ID]);
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        $dependentPromise = new DependentPromise($this->promiseAdapter, [], $this->getPromiseId(), $onFulfilled, $onRejected);
        $result = $this->wait(false);
        $promiseData = $dependentPromise->resolve($result);
        return $promiseData;
    }

}
