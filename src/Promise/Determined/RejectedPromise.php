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
use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\async\Promise\Store;

/**
 * RejectedPromise
 *
 * @category   async
 * @package    zaboy
 */
class RejectedPromise extends DeterminedPromise
{

    /**
     *
     * @param Storer $store
     * @throws PromiseException
     */
    public function __construct(Store $store, $promiseData = [], $result = null)
    {
        parent::__construct($store, $promiseData);
        $this->promiseData[Store::STATE] = PromiseInterface::REJECTED;
        if (isset($this->promiseData[Store::RESULT]) || is_null($result)) {
            return;
        }

        if ($result instanceof \Exception) {
            $reason = "Exception with class '" . get_class($result) . "' was thrown. Promise: " . $this->promiseData[Store::PROMISE_ID];
            $result = new RejectedException($reason, 0, $result);
            $this->promiseData[Store::RESULT] = $this->serializeResult($result);
            return;
        }

        if ($result instanceof PromiseInterface) {
            $result = $result->getPromiseId();
        }

        if (!PendingPromise::isPromiseId($result)) {
            set_error_handler(function ($number, $string) {
                throw new PromiseException(
                "RejectedPromise. String: $string,  Number: $number", null, null
                );
            });
            try {
                //result can be converted to string
                $result = new RejectedException(strval($result));
            } catch (\Exception $exc) {
                //result can not be converted to string
                $reason = 'Reason can not be converted to string.  Promise: ' . $this->promiseData[Store::PROMISE_ID];
                $result = new RejectedException($reason, 0, $exc);
            }
            restore_error_handler();
        }
        $this->promiseData[Store::RESULT] = $this->serializeResult($result);
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
            //result is exception
            $reason = 'Exception was thrown while Reason was resolving';
            return new ReasonRejectedException($reason, 0, $result);
        }
        if (is_a($result, '\zaboy\async\Promise\Pending\PendingPromise', true)) {
            /* @var $result PendingPromise */  //result is pending
            $reason = $result->getPromiseId();
            return new ReasonPendingException($reason);
        }

        set_error_handler(function ($number, $string) {
            throw new PromiseException(
            "RejectedPromise. String: $string,  Number: $number", null, null
            );
        });
        try {
            //result can be converted to string
            return new RejectedException(strval($result));
        } catch (\Exception $exc) {
            //result can not be converted to string
            $reason = 'Reason can not be converted to string.';
            return new RejectedException($reason, 0, $exc);
        }
        restore_error_handler();
    }

    public function resolve($value)
    {
        throw new PromiseException(
        'Can not resolve. Pomise already rejected.  Pomise: ' .
        $this->promiseData[Store::PROMISE_ID], 0, $this->wait(false)
        );
    }

    public function reject($reason)
    {
        throw new PromiseException(
        'Cannot reject a rejected promise.  Pomise: ' .
        $this->promiseData[Store::PROMISE_ID], 0, $this->wait(false)
        );
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        $dependentPromise = new DependentPromise($this->store, [], $this->getPromiseId(), null, $onRejected);
        $result = $this->wait(false);
        $promiseData = $dependentPromise->reject($result);
        return $promiseData;
    }

}
