<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise;

use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\async\EntityAbstract;

/**
 * PromiseAbstract
 *
 * @category   async
 * @package    zaboy
 */
abstract class PromiseAbstract extends EntityAbstract implements PromiseInterface
{

    const EXCEPTION_CLASS = PromiseException::class;

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function getState()
    {
        if (isset($this->data[Store::STATE])) {
            return $this->data[Store::STATE];
        } else {
            throw new PromiseException(
                "Promise State is not set."
            );
        }
    }

    /**
     * Retruns the result of Promise
     *
     * @return mixed|null
     */
    public function _wait()
    {
        if (isset($this->data[Store::RESULT])) {
            return $this->data[Store::RESULT];
        } else {
            return null;
        }
    }

}
