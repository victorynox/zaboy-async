<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Promise;

use zaboy\async\Promise\Exception\RejectedException;
use zaboy\async\Promise\Exception\ReasonPendingException;
use zaboy\async\Promise\Exception\ReasonRejectedException;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Promise\DeterminedPromise;
use zaboy\async\Promise\Promise\PendingPromise;
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
     * @param Store $store
     * @throws PromiseException
     */
    public function __construct($promiseData = [], $result = null)
    {
        parent::__construct($promiseData);
        $this->data[Store::STATE] = PromiseInterface::REJECTED;
        if (isset($this->data[Store::RESULT]) || is_null($result)) {
            return;
        }

        if ($result instanceof \Exception) {
            $reason = "Exception with class '" . get_class($result) . "' was thrown. Promise: " . $this->data[Store::ID];
            $result = new RejectedException($reason, 0, $result);
            $this->data[Store::RESULT] = $this->serializeResult($result);
            return;
        }
//
//        if ($result instanceof PromiseInterface) {
//            $result = $result->getId();
//        }

        if (!$this->isId($result) && !($result instanceof PromiseInterface)) {
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
                $reason = 'Reason can not be converted to string.  Promise: ' . $this->data[Store::ID];
                $result = new RejectedException($reason, 0, $exc);
            }
            restore_error_handler();
        }
        $this->data[Store::RESULT] = $this->serializeResult($result);
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
        $result = $this->unserializeResult($this->data[Store::RESULT]);

        if ($result instanceof PromiseInterface && $result->getState() === self::PENDING) {
            /* @var $result PendingPromise */  //result is pending
            $reason = $result->getId();
            return new ReasonPendingException($reason);
        }

        if (!($result instanceof PromiseInterface)) {
            return $result;
        }

        $result = parent::wait(false);
        if (is_a($result, '\zaboy\async\Promise\Exception\RejectedException', true)) {
            //result is exception
            $reason = 'Exception was thrown while Reason was resolving';
            return new ReasonRejectedException($reason, 0, $result);
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
        $this->data[Store::ID], 0, $this->wait(false)
        );
    }

    public function reject($reason)
    {
        throw new PromiseException(
        'Cannot reject a rejected promise.  Pomise: ' .
        $this->data[Store::ID], 0, $this->wait(false)
        );
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        $dependentPromise = new DependentPromise([], $this->getId(), null, $onRejected);
        $result = $this->wait(false);
        $promiseData = $dependentPromise->reject($result);
        return $promiseData;
    }

}
