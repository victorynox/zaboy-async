<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Interfaces;

/**
 * Interfaces for json Serialize and for Unserialize for objects
 *
 *
 * @category   async
 * @package    zaboy
 */
interface JsonSerialize
{

    public function jsonSerialize();

    public static function jsonUnserialize($serializedObject);
}
