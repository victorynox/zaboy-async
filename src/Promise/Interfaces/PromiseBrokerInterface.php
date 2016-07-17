<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Interfaces;

/**
 * Full Interface for Promise
 *
 * @category   async
 * @package    zaboy
 */
interface PromiseBrokerInterface
{

    public function make();
}
