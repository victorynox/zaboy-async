<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Determined;

use zaboy\async\Promise\Determined\Exception\RejectedException;
use zaboy\async\Promise\Determined\Exception\ReasonPendingException;
use zaboy\async\Promise\Determined\Exception\ReasonRejectedException;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Determined\DeterminedPromise;
use zaboy\async\Promise\Pending\PendingPromise;
use zaboy\async\Promise\PromiseClient;
use zaboy\async\romise\Interfaces\PromiseInterface;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\async\Promise\PromiseAbstract;
use zaboy\async\Promise\Broker\PromiseBroker;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter as Store;

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

    public function wait($unwrap = true)
    {
        if ($unwrap) {
            return new PromiseException('Do not try call wait(true)');
        }
        $result = $this->unserializeResult($this->promiseData[Store::RESULT]);
        if (!PendingPromise::isPromiseId($result)) {
            return $result;
        }
        $result = parent::wait(false);
        if (is_a($result, '\zaboy\async\Promise\Determined\Exception\RejectedException', true)) {
            //resalt is exception
            $reason = 'There is exception while Reason was resolving';
            return new ReasonRejectedException($reason, 0, $result);
        }
        if (is_a($result, '\zaboy\async\Promise\Pending\PendingPromise', true)) {
            /* @var $reason PendingPromise */  //resalt is pending
            $reason = $result->getPromiseId();
            return new ReasonPendingException($reason);
        }
        try {
            //result can be converted to string
            $reason = strval($result);
            return new RejectedException($reason);
        } catch (\Exception $exc) {
            //result can not be converted to string
            $reason = 'Reason can not be converted to string.';
            return new RejectedException($reason, 0, $exc);
        }
    }

    public function resolve($value)
    {
        throw new PromiseException('Can not resolve. Pomise already rejected.  Pomise: ' . $this->promiseData[Store::PROMISE_ID]);
    }

    public function reject($reason)
    {
        throw new PromiseException('Cannot reject a rejected promise.  Pomise: ' . $this->promiseData[Store::PROMISE_ID]);
    }

    protected function getReason($resalt)
    {
        if (is_a($resalt, '\zaboy\async\Promise\Determined\Exception\RejectedException', true)) {
            return $reason;
        }
        try {
            $strReason = strval($reason);
        } catch (\Exception $exc) {
            return new PromiseException;
        }
        return 0 === strpos($strParam, static::PROMISE_ID_PREFIX . static::ID_SEPARATOR);
    }

}
