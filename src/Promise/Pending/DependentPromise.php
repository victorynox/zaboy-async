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
    public function __construct(MySqlPromiseAdapter $promiseAdapter, $parentPromiseId, callable $onFulfilled = null, callable $onRejected = null)
    {
        parent::setPromiseData($parentPromiseId, $onFulfilled, $onRejected);
    }

    public function setPromiseData($parentPromiseId, callable $onFulfilled = null, callable $onRejected = null)
    {
        parent::setPromiseData();
        $this->promiseData[Store::PARENT_ID] = $parentPromiseId;
        $this->promiseData[Store::ON_FULFILLED] = $this->serializeCallback($onFulfilled);
        $this->promiseData[Store::ON_REJECTED] = $this->serializeCallback($onRejected);
    }

    public function resolve($value)
    {

        $this->promiseData[Store::STATE] = PromiseInterface::FULFILLED;
        $this->promiseData[Store::CLASS_NAME] = '\zaboy\async\Promise\Determined\FulfilledPromise';
        $this->promiseData[Store::PARENT_ID] = null;
        $this->promiseData[Store::ON_FULFILLED] = null;
        $this->promiseData[Store::ON_REJECTED] = null;
        $promiseData = $this->getPromiseData();
        $onFulfilled = $promiseData[Store::ON_FULFILLED];
        $onFulfilledCallback = unserialize($onFulfilled);

        $resalt = call_user_func($onFulfilledCallback, $value);

        $serializeredResalt = $this->serializeResult($resalt);
        $this->promiseData[Store::RESULT] = $serializeredResalt;
        return $this->promiseData;
    }

}
