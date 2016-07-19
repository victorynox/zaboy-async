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
use zaboy\async\Promise\PromiseAbstract;
use zaboy\async\Promise\Broker\PromiseBroker;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter as Store;
use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;
use zaboy\async\Promise\Determined\FulfilledPromise;

/**
 * PromiseAbstract
 *
 * @category   async
 * @package    zaboy
 */
class PendingPromise extends PromiseAbstract
{

    public function setPromiseData()
    {
        parent::setPromiseData();
        $this->promiseData[Store::STATE] = PromiseInterface::PENDING;
    }

    public function resolve($value)
    {
        $this->promiseData[Store::STATE] = PromiseInterface::FULFILLED;
        $this->promiseData[Store::CLASS_NAME] = '\zaboy\async\Promise\Determined\FulfilledPromise';
        $this->promiseData[Store::RESULT] = $value;
        return $this->promiseData;
    }

    public function reject($reason)
    {
        $this->promiseData[Store::STATE] = PromiseInterface::REJECTED;
        $this->promiseData[Store::CLASS_NAME] = '\zaboy\async\Promise\Determined\RejectedPromise';
        $this->promiseData[Store::RESULT] = $reason;
        return $this->promiseData;
    }

    public function wait()
    {
        return $this;
    }

}
