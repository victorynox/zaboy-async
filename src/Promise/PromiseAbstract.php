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

    const EXCEPTION_CLASS = '\zaboy\async\Promise\PromiseException';

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
        $this->data = $promiseData;

        if (!isset($this->data[Store::ID])) {
            $this->data[Store::ID] = $this->makeId();
        }
        if (!isset($this->data[Store::CREATION_TIME])) {
            $this->data[Store::CREATION_TIME] = (int) (time() - date('Z'));
        }
    }

    public function getState()
    {
        if (isset($this->data[Store::STATE])) {
            return $this->data[Store::STATE];
        } else {
            throw new PromiseException(
            "Pomise State is not set."
            );
        }
    }

}
