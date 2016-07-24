<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Determined\Exception;

use zaboy\async\Promise\PromiseException;

/**
 * Exception class for RejectedException
 *
 * @category   async
 * @package    zaboy
 */
class RejectedException extends PromiseException
{

    public static function jsonUnserializeExc($excJsonString)
    {
        
    }

    public static function excSerializeJson(\Exception $exceptiom)
    {

    }

    public function __construct()
    {
        parent::__construct($message, $code, $previous);
    }

}
