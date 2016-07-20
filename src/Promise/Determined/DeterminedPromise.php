<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Determined;

use zaboy\async\Json\JsonCoder;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\PromiseClient;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter;
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
    public function __construct(MySqlPromiseAdapter $promiseAdapter, $promiseId)
    {
        parent::__construct($promiseAdapter, $promiseId);
    }

    protected function unserializeResult($result)
    {
        switch (true) {
            case $this->isPromiseId($result):
                return $result;

            case JsonCoder::isSerializedObject($result):
                return JsonCoder::jsonUnserialize($result);
            case is_object($result):
                throw new PromiseException("Can not serialize object: " . get_class($result));

            default :
                try {
                    return JsonCoder::jsonDecode($result);
                } catch (PromiseException $ex) {
                    throw new PromiseException("Can not unserialize string: " . $result, 0, $ex);
                }
        }
    }

    public function wait($unwrap = true, $waitingTime = 60, $waitingCheckInterval = 1)
    {
        $result = $this->unserializeResult($this->promiseData[MySqlPromiseAdapter::RESULT]);
        if (PendingPromise::isPromiseId($result)) {
            $nextPromise = new PromiseClient($this->promiseAdapter, $result);
            $result = $nextPromise->wait(false);
        }
        return $result;
    }

}
