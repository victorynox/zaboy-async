<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise;

use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Store;

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
     * @var Store
     */
    public $store;

    /**
     *
     * @param Store $store
     * @throws PromiseException
     */
    public function __construct(Store $store, $promiseData = [])
    {
        $this->store = $store;
        $this->promiseData = $promiseData;

        if (!isset($this->promiseData[Store::PROMISE_ID])) {
            $this->promiseData[Store::PROMISE_ID] = $this->makeId();
        }
        if (!isset($this->promiseData[Store::CREATION_TIME])) {
            $this->promiseData[Store::CREATION_TIME] = (int) (time() - date('Z'));
        }
    }

    public function getId()
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

    public function getData()
    {
        if (isset($this->promiseData)) {
            return $this->promiseData;
        } else {
            throw new PromiseException(
            "Pomise Data is not set."
            );
        }
    }

    protected function makeId()
    {
        list($microSec, $sec) = explode(" ", microtime());
        $utcSec = $sec - date('Z');
        $microSec6digits = substr((1 + round($microSec, 6)) * 1000 * 1000, 1);
        $time = $utcSec . '.' . $microSec6digits; //Grivich UTC time in microsec
        $idWithDot = uniqid(
                self::PROMISE_ID_PREFIX . self::ID_SEPARATOR . self::ID_SEPARATOR
                . $time . self::ID_SEPARATOR . self::ID_SEPARATOR
                , true
        );
        $promiseId = str_replace('.', self::ID_SEPARATOR, $idWithDot);

        return $promiseId;
    }

}
