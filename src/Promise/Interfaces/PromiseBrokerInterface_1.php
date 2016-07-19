<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Interfaces;

/**
 * Full Interface for Async callable objects
 *
 *
 * @category   async
 * @package    zaboy
 */
interface AsyncInterface
{

    /**
     *
     * @param mix $parameter
     * @return PromiseInterface
     */
    public function asyncCall($parameter);
}
