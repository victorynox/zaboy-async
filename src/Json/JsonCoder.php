<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Json;

use zaboy\async\Promise\Interfaces\JsonSerialize;

/**
 *
 *
 * @category   async
 * @package    zaboy
 */
class JsonCoder
{

    public static function jsonSerialize($object)
    {
        if (!is_object($object)) {
            throw new \Exception("Param is non object");
        }
        if ($object instanceof JsonSerialize) {
            $jsonString = $object->jsonSerialize();
            $objectMarker = '>>JsonCoder. Class:' . get_class() . '<<';
            return $objectMarker . $jsonString;
        } else {
            throw new \Exception(get_class($object) . "do not instanceof JsonSerialize Interface");
        }
    }

    public static function jsonUnserialize($serializedObject)
    {

        $stdObject = json_decode($serializedObject);
        $objectClass = $stdObject->class;
        $prev = !$stdObject->prev_exc ? null : PromiseException::jsonUnserialize($stdObject->prev_exc);
        $object = new $objectClass($stdObject->message, $stdObject->code, $prev);
        return $object;
    }

    public static function getSerializedClass($serializedObject)
    {

        $serializedObject
        return $object;
    }

}
