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
        $resalt = $this->serializeResult($value);
        $this->promiseData[Store::RESULT] = $resalt;
        return $this->promiseData;
    }

    public function reject($result)
    {
        $this->promiseData[Store::STATE] = PromiseInterface::REJECTED;
        $this->promiseData[Store::CLASS_NAME] = '\zaboy\async\Promise\Determined\RejectedPromise';
        if (
                $this->isPromiseId($result) ||
                is_a($result, '\zaboy\async\Promise\Determined\Exception\RejectedException', true) ||
                is_a($result, '\zaboy\async\Promise\PromiseClient', true)
        ) {
            $this->promiseData[Store::RESULT] = $this->serializeResult($result);
        } else {
            if (is_object($result) || is_array($result) || is_callable($result) || is_resource($result)) {
                $reson = 'Reason can not be converted to string.';
            } else {
                $reson = strval($result);
            }
            $result = new RejectedException($reson);
            $this->promiseData[Store::RESULT] = $this->serializeResult($result);
        }
        return $this->promiseData;
    }

    public function wait($unwrap = true)
    {
        return $this;
    }

    protected function serializeResult($result)
    {
        switch (true) {
            case $this->isPromiseId($result):
                break;

            case $result instanceof PromiseInterface:
                $result = $result->getPromiseId();
                break;

            case is_object($result) && $result instanceof JsonSerialize:
                $result = JsonCoder::jsonSerialize($result);
                break;
            case is_object($result):
                throw new PromiseException("Can not serialize object: " . get_class($result));

            default :
                try {
                    $result = JsonCoder::jsonEncode($result);
                } catch (PromiseException $ex) {
                    throw new PromiseException("Can not serialize result" . get_class($result), 0, $ex);
                }
        }
        return $result;
    }

}
