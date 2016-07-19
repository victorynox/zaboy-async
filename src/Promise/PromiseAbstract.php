<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise;

use zaboy\scheduler\DataStore\UTCTime;
use GuzzleHttp\Promise\Promise;
use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Broker\PromiseBroker;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter as Store;
use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;

/**
 * PromiseAbstract
 *
 * @category   async
 * @package    zaboy
 */
abstract class PromiseAbstract implements PromiseInterface
{

    const PROMISE_ID_PREFIX = 'promise';
    const ID_SEPARATOR = '_';

    /**
     *
     * @var array
     */
    public $promiseData;

    /**
     *
     * @var MySqlPromiseAdapter
     */
    public $promiseAdapter;

    /**
     *
     * @param MySqlPromiseAdapter $promiseAdapter
     * @throws PromiseException
     */
    public function __construct(Store $promiseAdapter, $promiseData = null)
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->promiseData = $promiseData;
        if (!isset($this->promiseData[Store::PROMISE_ID])) {
            $this->setPromiseData();
        }
    }

    protected function setPromiseData()
    {
        $this->promiseData[Store::PROMISE_ID] = $this->makePromiseId();
        $this->promiseData[Store::CLASS_NAME] = get_class($this);
        $this->promiseData[Store::TIME_IN_FLIGHT] = $this->promiseAdapter->getUtcTime();
    }

    protected function makePromiseId()
    {
        $time = $this->promiseAdapter->getUtcMicrotime(); //Grivich UTC time in microsec
        $idWithDot = uniqid(
                self::PROMISE_ID_PREFIX . self::ID_SEPARATOR . self::ID_SEPARATOR
                . $time . self::ID_SEPARATOR . self::ID_SEPARATOR
                , true
        );
        $promiseId = str_replace('.', self::ID_SEPARATOR, $idWithDot);

        return $promiseId;
    }

    public function getPromiseId()
    {
        if (isset($this->promiseData[Store::PROMISE_ID])) {
            return $this->promiseData[Store::PROMISE_ID];
        } else {
            throw new PromiseException(
            "PomiseId is not set."
            );
        }
    }

    public function getState()
    {
        if (isset($this->promiseData[Store::STATE])) {
            return $this->promiseData[Store::STATE];
        } else {
            throw new PromiseException(
            "Pomise State is not set."
            );
        }
    }

    public function getPromiseData()
    {
        if (isset($this->promiseData)) {
            return $this->promiseData;
        } else {
            throw new PromiseException(
            "Pomise promiseData is not set."
            );
        }
    }

    public static function isPromiseId($param)
    {
        return (bool) strpos($param, static::PROMISE_ID_PREFIX . static::ID_SEPARATOR);
    }

}
