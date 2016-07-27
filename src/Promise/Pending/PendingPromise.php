<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Pending;

use zaboy\async\Json\JsonCoder;
use zaboy\async\Promise\Interfaces\JsonSerialize;
use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Determined\Exception\RejectedException;
use zaboy\async\Promise\PromiseAbstract;
use zaboy\async\Promise\Broker\PromiseBroker;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter as Store;
use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;
use zaboy\async\Promise\Determined\FulfilledPromise;
use zaboy\async\Promise\Determined\RejectedPromise;

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
     * @param MySqlPromiseAdapter $promiseAdapter
     * @throws PromiseException
     */
    public function __construct(Store $promiseAdapter, $promiseData = [])
    {
        parent::__construct($promiseAdapter, $promiseData);
        $this->promiseData[Store::STATE] = PromiseInterface::PENDING;
    }

    public function resolve($value)
    {
        $fulfilledPromise = new FulfilledPromise($this->promiseAdapter, $this->getPromiseData(), $value);
        return $fulfilledPromise->getPromiseData();
    }

    public function reject($reason)
    {
        $rejectedPromise = new RejectedPromise($this->promiseAdapter, $this->getPromiseData(), $reason);


        return $rejectedPromise->getPromiseData();
    }

    public function wait($unwrap = true)
    {
        return $this;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        $dependentPromise = new DependentPromiseData($promiseAdapter, $parentPromiseId, $onFulfilled, $onRejected);
        $dependentPromiseData = $dependentPromise->getPromiseData();
        return $dependentPromiseData;
    }

}
