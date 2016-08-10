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
use zaboy\async\ClientInterface;
use zaboy\async\Store;
use Zend\Db\Sql\Select;

/**
 * Client
 *
 * @category   async
 * @package    zaboy
 */
abstract class ClientAbstract implements ClientInterface
{

    /**
     *
     * @var Store
     */
    public $store;

    /**
     *
     * @var string
     */
    public $id;

    /**
     * Creates ID for the entity.
     *
     * An algorithm of creation is common for the all entities except for prefix.
     *
     * For example for Promise it will be 'promise_', for Task - 'task_' etc.
     *
     * @return string
     */
    public function makeId()
    {

    }

    public static function isId($param)
    {
        $array = [];
        $regExp = '/(' . static::getPrefix() . '__[0-9]{10}_[0-9]{6}__[a-zA-Z0-9_]{23})/';
        if (is_string($param) && preg_match_all($regExp, $param, $array)) {
            return $array[0][0] == $param;
        } else {
            return false;
        }
    }

    /**
     * Returns an array created from data of entity.
     *
     * @return array mixed
     */
    public function getData()
    {

    }

    /**
     * Returns the class name of Entity
     *
     * @return string
     */
    protected function getClass($id = null)
    {

    }

    /**
     * Returns the Prefix for Id
     *
     * @return string
     */
    protected static function getPrefix()
    {
        return strtolower(substr(__CLASS__, strlen(__NAMESPACE__) + 1));
    }

    /**
     * Returns the Entity ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

}
