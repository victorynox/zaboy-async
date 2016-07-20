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
            $objectMarker = '>>JsonCoder. Class:' . get_class($object) . '<<';
            return $objectMarker . $jsonString;
        } else {
            throw new \Exception(get_class($object) . "do not instanceof JsonSerialize Interface");
        }
    }

    public static function jsonUnserialize($serializedObject)
    {
        $className = self::getSerializedClass($serializedObject);
        $jsonString = substr($serializedObject, strpos($serializedObject, '<<') + 2);
        $object = $className::jsonUnserialize($jsonString);
        return $object;
    }

    public static function getSerializedClass($serializedObject)
    {
        if (strpos($serializedObject, '>>JsonCoder. Class:') === 0) {
            $start = strlen('>>JsonCoder. Class:');
            $length = strpos($serializedObject, '<<') - $start;
            $className = substr($serializedObject, $start, $length);
            return $className;
        } else {
            throw new \Exception("Can not unserialize string.");
        }
    }

    public static function isSerializedObject($serializedObject)
    {
        return strpos($serializedObject, '>>JsonCoder. Class:') === 0;
    }

    public static function jsonDecode($data)
    {
        json_encode(null); // Clear json_last_error()
        $result = json_decode((string) $data, 1); //json_decode($data);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $jsonErrorMsg = json_last_error_msg();
            json_encode(null);  // Clear json_last_error()
            $decode = json_decode($encodedValue, $objectDecodeType);
            throw new DataStoreException(
            'Unable to decode data from JSON - ' . $jsonErrorMsg
            );
        }
        return $result;
    }

    public static function jsonEncode($data)
    {
        json_encode(null); // Clear json_last_error()
        $result = json_encode($data, 79);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $jsonErrorMsg = json_last_error_msg();
            json_encode(null);  // Clear json_last_error()
            throw new DataStoreException(
            'Unable to encode data to JSON - ' . $jsonErrorMsg
            );
        }
        return $result;
    }

}
