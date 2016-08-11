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
use zaboy\async\EntityAbstract;

/**
 * PromiseAbstract
 *
 * @category   async
 * @package    zaboy
 */
abstract class PromiseAbstract extends EntityAbstract implements PromiseInterface
{

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

        if (!isset($this->promiseData[Store::ID])) {
            $this->promiseData[Store::ID] = $this->makeId();
        }
        if (!isset($this->promiseData[Store::CREATION_TIME])) {
            $this->promiseData[Store::CREATION_TIME] = (int) (time() - date('Z'));
        }
    }

    public function getId()
    {
        if (isset($this->promiseData[Store::ID])) {
            return $this->promiseData[Store::ID];
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

}
