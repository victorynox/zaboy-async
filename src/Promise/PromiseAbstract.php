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
     * @throws PromiseException
     */
    public function __construct($promiseData = [])
    {
        parent::__construct($promiseData);
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

    public function _wait()
    {
        if (isset($this->data[Store::RESULT])) {
            return $this->data[Store::RESULT];
        } else {
            return null;
        }
    }

}
