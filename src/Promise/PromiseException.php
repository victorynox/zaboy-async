<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise;

use zaboy\async\Promise\Interfaces\JsonSerialize;

/**
 * Exception class for PromiseException
 *
 * @category   async
 * @package    zaboy
 */
class PromiseException extends \Exception implements JsonSerialize
{

    public function jsonSerialize(\Exception $exception)
    {
        $arrayObject = new \ArrayObject;
        $arrayObject['class'] = get_class($this);
        $arrayObject['message'] = $this->getMessage();
        $arrayObject['code'] = $this->getCode();
        $prev = $this->getPrevious();
        $arrayObject['prev_exc'] = !$prev ? null : $prev->jsonSerialize();
        return json_encode($arrayObject);
    }

    public static function jsonUnserialize($serializedObject)
    {
        $stdObject = json_decode($serializedObject);
        $objectClass = $stdObject->class;
        $prev = !$stdObject->prev_exc ? null : PromiseException::jsonUnserialize($stdObject->prev_exc);
        $object = new $objectClass($stdObject->message, $stdObject->code, $prev);
        return $object;
    }

}
