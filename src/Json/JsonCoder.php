<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Json;

use mindplay\jsonfreeze\JsonSerializer;
use zaboy\async\Promise\PromiseException;

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

        $serializer->defineSerialization('Exception', [get_class(), 'serializeException'], [get_class(), 'unserializeException']);

        //if (is_object($value) && is_a(get_class($value), '\Exception', true)) {
        if (is_object($value) && static::isClassException(get_class($value))) {
            $serializer->defineSerialization(get_class($value), [get_class(), 'serializeException'], [get_class(), 'unserializeException']);
        }
        $serializedValue = $serializer->serialize($value);
        return $serializedValue;
    }

    public static function jsonUnserialize($serializedValue)
    {
        $serializer = new JsonSerializer();
        $jsonDecoded = static::jsonDecode($serializedValue);

        //if (isset($jsonDecoded[JsonSerializer::TYPE]) && is_a($jsonDecoded[JsonSerializer::TYPE], '\Exception', true)) {
        if (isset($jsonDecoded[JsonSerializer::TYPE]) && static::isClassException($jsonDecoded[JsonSerializer::TYPE])) {
            $serializer->defineSerialization($jsonDecoded[JsonSerializer::TYPE], [get_class(), 'serializeException'], [get_class(), 'unserializeException']);
        }

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
            throw new \Exception(
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
            throw new \Exception(
            'Unable to encode data to JSON - ' . $jsonErrorMsg
            );
        }
        return $result;
    }

    /**
     * @param DateTime|DateTimeImmutable $datetime
     *
     * @return array
     */
    public static function serializeException(\Exception $exception)
    {
        $data = array(
            JsonSerializer::TYPE => get_class($exception),
            "message" => $exception->getMessage(),
            "code" => $exception->getCode(),
            "line" => $exception->getLine(),
            "file" => $exception->getFile(),
            "prev" => $exception->getPrevious(),
        );
        return $data;
    }

    /**
     * @param array $data
     *
     * @return DateTime|DateTimeImmutable
     */
    public static function unserializeException($data)
    {
        if (!isset($data["prev"])) {
            $exc = new $data[JsonSerializer::TYPE]($data["message"], $data["code"], null);
        } else {
            $prev = static::unserializeException($data["prev"]);
            $exc = new $data[JsonSerializer::TYPE]($data["message"], $data["code"], $prev);
        }
        $class = new \ReflectionClass($data[JsonSerializer::TYPE]);

        $properties = $class->getProperties();

        foreach ($properties as $prop) {

            if ($prop->getName() === "line" || $prop->getName() === "file") {
                $prop->setAccessible(true);
                $prop->setValue($exc, $data[$prop->getName()]);
                $prop->setAccessible(false);
            }
        }
        return $exc;
    }

    protected static function isClassException($className)
    {
        // return (is_object($value) && is_a($className, '\Exception', true));
        return substr($className, strlen($className) - strlen('Exception')) === 'Exception';
    }

}
