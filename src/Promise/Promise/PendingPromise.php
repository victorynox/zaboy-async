<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Promise;

use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\PromiseAbstract;
use zaboy\async\Promise\Store;
use zaboy\async\Promise\Promise\FulfilledPromise;
use zaboy\async\Promise\Promise\RejectedPromise;

/**
 * PromiseAbstract
 *
 * @category   async
 * @package    zaboy
 */
class PendingPromise extends PromiseAbstract
{

    /**
     *
     * @param Store $store
     * @throws PromiseException
     */
    public function __construct(Store $store, $promiseData = [])
    {
        parent::__construct($store, $promiseData);
        $this->promiseData[Store::STATE] = PromiseInterface::PENDING;
    }

    public function resolve($value)
    {
        $fulfilledPromise = new FulfilledPromise($this->store, $this->getData(), $value);
        return $fulfilledPromise->getData();
    }

    public function reject($reason)
    {
        $rejectedPromise = new RejectedPromise($this->store, $this->getData(), $reason);
        return $rejectedPromise->getData();
    }

    public function wait($unwrap = true)
    {
        return $this;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        $dependentPromise = new DependentPromise($this->store, [], $this->getId(), $onFulfilled, $onRejected);
        $dependentPromiseData = $dependentPromise->getData();
        return $dependentPromiseData;
    }

}
