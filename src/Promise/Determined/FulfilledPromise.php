<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Determined;

use zaboy\async\Promise\Determined\DeterminedPromise;
use GuzzleHttp\Promise\PromiseInterface;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\PromiseAbstract;
use zaboy\async\Promise\Broker\PromiseBroker;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter as Store;
use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;

/**
 * FulfilledPromise
 *
 * @category   async
 * @package    zaboy
 */
class FulfilledPromise extends DeterminedPromise
{

    public function setPromiseData()
    {
        parent::setPromiseData();
        $this->promiseData[Store::STATE] = PromiseInterface::FULFILLED;
    }

    public function getState()
    {
        return PromiseInterface::FULFILLED;
    }

    public function wait()
    {
        return null;
    }

    public function resolve($value)
    {
        if ($value != $this->promiseData[Store::RESULT]) {
            throw new PromiseException('Pomise already resolved.  Pomise: ' . $this->promiseData[Store::PROMISE_ID]);
        }
        return $this->promiseData;
    }

}
