<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Determined;

use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\async\Json\JsonCoder;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\PromiseClient;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter as Store;
use zaboy\async\Promise\Pending\PendingPromise;
use zaboy\async\Promise\PromiseAbstract;

/**
 * PromiseAbstract
 *
 * @category   async
 * @package    zaboy
 */
abstract class DeterminedPromise extends PromiseAbstract
{

    /**
     *
     * @param MySqlPromiseAdapter $promiseAdapter
     * @throws PromiseException
     */
    public function __construct(Store $promiseAdapter, $promiseData = [])
    {
        parent::__construct($promiseAdapter, $promiseData);
        $this->promiseData[Store::PARENT_ID] = null;
        $this->promiseData[Store::ON_FULFILLED] = null;
        $this->promiseData[Store::ON_REJECTED] = null;
    }

    protected function serializeResult($result)
    {
        if ($result instanceof PromiseInterface) {
            $result = $result->getPromiseId();
        }
        try {
            $resultJson = JsonCoder::jsonSerialize($result);
        } catch (PromiseException $ex) {
            $class = is_object($result) ? 'for object ' . get_class($result) : '';
            throw new PromiseException("Can not serialize result " . $class, 0, $ex);
        }

        return $resultJson;
    }

    protected function unserializeResult($resultJson)
    {
        try {
            return JsonCoder::jsonUnserialize($resultJson);
        } catch (PromiseException $ex) {
            throw new PromiseException("Can not unserialize string: " . $resultJson, 0, $ex);
        }
    }

    public function wait($unwrap = true, $waitingTime = 60, $waitingCheckInterval = 1)
    {
        $result = $this->unserializeResult($this->promiseData[Store::RESULT]);
        if (PendingPromise::isPromiseId($result)) {
            $nextPromise = new PromiseClient($this->promiseAdapter, $result);
            $result = $nextPromise->wait(false);
        }
        return $result;
    }

}
