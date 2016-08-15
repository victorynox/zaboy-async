<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Promise;

use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\async\Json\JsonCoder;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Client;
use zaboy\async\Promise\Store;
use zaboy\async\Promise\Promise\PendingPromise;
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
     * @param Store $store
     * @throws PromiseException
     */
    public function __construct($promiseData = [])
    {
        parent::__construct($promiseData);
        $this->data[Store::PARENT_ID] = null;
        $this->data[Store::ON_FULFILLED] = null;
        $this->data[Store::ON_REJECTED] = null;
    }

    protected function serializeResult($result)
    {
        if ($result instanceof PromiseInterface) {
            return $result->getId();
        }
        try {
            $resultJson = JsonCoder::jsonSerialize($result);
        } catch (PromiseException $ex) {
            $class = is_object($result) ? 'for object ' . get_class($result) : '';
            throw new PromiseException("Cannot serialize result " . $class, 0, $ex);
        }

        return $resultJson;
    }

    protected function unserializeResult($resultJson)
    {
        if ($resultJson instanceof PromiseInterface) {
            return $resultJson;
        }
        if ($this->isId($resultJson)) {
            return $resultJson;
        }
        try {
            return JsonCoder::jsonUnserialize($resultJson);
        } catch (PromiseException $ex) {
            throw new PromiseException("Cannot unserialize string: " . $resultJson, 0, $ex);
        }
    }

    public function wait($unwrap = true)
    {
        if ($unwrap) {
            return new PromiseException('Do not try to call wait(true)');
        }
        $result = $this->unserializeResult($this->data[Store::RESULT]);
        if ($result instanceof Client) {
            $result = $result->wait(false);
        }
        return $result;
    }

    public function getResult()
    {
        return $this->unserializeResult($this->data[Store::RESULT]);
    }

}
