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
 * RejectedPromise
 *
 * @category   async
 * @package    zaboy
 */
class RejectedPromise extends DeterminedPromise
{

    public function setPromiseData()
    {
        parent::setPromiseData();
        $this->promiseData[Store::STATE] = PromiseInterface::REJECTED;
    }

    public function getState()
    {
        return PromiseInterface::REJECTED;
    }

    public function wait()
    {
        if (!isset($this->promiseData[Store::RESULT])) {
            throw new PromiseException('Pomise was rejected without Reason.');
        }
        $result = $this->promiseData[Store::RESULT];
        if (is_a($result, \Exception, true)) {
            throw new PromiseException('Pomise was rejected with exception', 0, $result);
        }
        if (self::isPromiseId($result)) {
            return $result;
        }
        throw new PromiseException('Pomise was rejected ' . strval($result));
    }

    public function resolve($value)
    {
        throw new PromiseException('Can not resolve. Pomise already rejected.  Pomise: ' . $this->promiseData[Store::PROMISE_ID]);
    }

    public function reject($reason)
    {
        throw new PromiseException('Cannot reject a rejected promise.  Pomise: ' . $this->promiseData[Store::PROMISE_ID]);
    }

}
