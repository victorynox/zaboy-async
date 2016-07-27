<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Json;

use mindplay\jsonfreeze\JsonSerializer;

/**
 *
 *
 * @category   async
 * @package    zaboy
 * @todo useset_error_hadler in jsonEncode()
 */
class JsonCoder
{

    public static function jsonSerialize($value)
    {
        $serializer = new JsonSerializer();
        $serializedValue = $serializer->serialize($value);
        return $serializedValue;
    }

    public static function jsonUnserialize($serializedValue)
    {
        $serializer = new JsonSerializer();
        $value = $serializer->unserialize($serializedValue);
        return $value;
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
