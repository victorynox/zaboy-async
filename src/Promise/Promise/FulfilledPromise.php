<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Promise;

use zaboy\async\Promise\Promise\DeterminedPromise;
use zaboy\async\Promise\Promise\PendingPromise;
use zaboy\async\Promise\Promise\DependentPromise;
use GuzzleHttp\Promise\PromiseInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Store;
use zaboy\async\Promise\Promise;

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
     * @param Store $store
     * @throws PromiseException
     */
    public function __construct(Store $store, $promiseData = [], $result = null)
    {
        parent::__construct($store, $promiseData);
        $this->promiseData[Store::STATE] = PromiseInterface::FULFILLED;
        if (!isset($this->promiseData[Store::RESULT]) && !is_null($result)) {
            $this->promiseData[Store::RESULT] = $this->serializeResult($result);
        }
    }

    public function getState()
    {
        return PromiseInterface::FULFILLED;
    }

    public function resolve($value)
    {
        if ($value != $this->promiseData[Store::RESULT]) {
            throw new PromiseException('Pomise already resolved.  Pomise: ' . $this->promiseData[Store::ID]);
        }
        return $this->promiseData;
    }

    public function reject($reason)
    {
        throw new PromiseException('Cannot reject a fulfilled promise.  Pomise: ' . $this->promiseData[Store::ID]);
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        $dependentPromise = new DependentPromise($this->store, [], $this->getId(), $onFulfilled, $onRejected);
        $result = $this->wait(false);
        $promiseData = $dependentPromise->resolve($result);
        return $promiseData;
    }

}
