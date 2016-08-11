<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async;

use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Exception\TimeIsOutException;
use zaboy\async\Promise\Promise\PendingPromise;
use zaboy\async\Promise\PromiseAbstract;
use zaboy\async\Store;
use Zend\Db\Sql\Select;

/**
 * Client
 *
 * @category   async
 * @package    zaboy
 */
abstract class AsyncAbstract
{

    const EXCEPTION_CLASS = '\Exception';
    const ID_SEPARATOR = '_';

    /**
     * Creates ID for the entity.
     *
     * An algorithm of creation is common for the all entities except for prefix.
     *
     * For example for Promise it will be 'promise_', for Task - 'task_' etc.
     *
     * @return string
     */
    protected function makeId()
    {
        list($microSec, $sec) = explode(" ", microtime());
        $utcSec = $sec - date('Z');
        $microSec6digits = substr((1 + round($microSec, 6)) * 1000 * 1000, 1);
        $time = $utcSec . '.' . $microSec6digits; //Grivich UTC time in microsec
        $idWithDot = uniqid(
                $this->getPrefix() . self::ID_SEPARATOR . self::ID_SEPARATOR
                . $time . self::ID_SEPARATOR . self::ID_SEPARATOR
                , true
        );
        $promiseId = str_replace('.', self::ID_SEPARATOR, $idWithDot);

        return $promiseId;
    }

    /**
     * Checks string for tha match ID.
     *
     * @return boolean
     */
    public function isId($param)
    {
        $array = [];
        $regExp = '/(' . $this->getPrefix() . '__[0-9]{10}_[0-9]{6}__[a-zA-Z0-9_]{23})/';
        if (is_string($param) && preg_match_all($regExp, $param, $array)) {
            return $array[0][0] == $param;
        } else {
            return false;
        }
    }

    /**
     * Returns the Prefix for Id
     *
     * @return string
     */
    public function getPrefix()
    {
        return strtolower(explode('\\', get_class($this))[2]);
    }

    public function extractId($stringOrException, $idArray = [])
    {
        if (is_null($stringOrException)) {
            return $idArray;
        }
        if ($stringOrException instanceof \Exception) {
            $array = $this->extractId($stringOrException->getPrevious(), $idArray);
            $idArray = $this->extractId($stringOrException->getMessage(), $array);
            return $idArray;
        }
        $array = [];
        if (preg_match_all('/(' . $this->getPrefix() . '__[0-9]{10}_[0-9]{6}__[a-zA-Z0-9_]{23})/', $stringOrException, $array)) {
            return array_merge(array_reverse($array[0]), $idArray);
        } else {
            return [];
        }
    }

}
