<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Callback\Interfaces;

use zaboy\async\Promise\Client as PromiseClient;

/**
 * InterrupterInterface
 *
 * @category   async
 * @package    zaboy
 */
interface InterrupterInterface
{

    public function interrupt($value, PromiseClient $promise, callable $callback);
}
