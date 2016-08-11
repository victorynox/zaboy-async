<?php

namespace zaboy\async;

use Zend\Db\TableGateway\TableGateway;
use zaboy\rest\DataStore\Interfaces\ReadInterface;
use Zend\Db\Sql\Select;

/**
 * Store for states of  promises
 *
 * id => promise_id_123456789qwerty
 * state => pending || fulfilled || rejected;
 * result => mix;
 * creation_time = 2216125; UTC time when promise has sarted
 * parent_id => promise_id_123456789qwerty2 - promise that gave birth to it
 * on_fulfilled => string, php callable or callback service name;
 * on_rejected => string, php callable or callback service name;
 *
 * @category   async
 * @package    zaboy
 */
class StoreAbstract extends TableGateway
{

    //PROMISE_ADAPTER_DATA_STORE
    //
    //'id' - unique id of promise: promise_id_123456789qwerty
    const ID = ReadInterface::DEF_ID;
    const CREATION_TIME = 'creation_time';

    public function beginTransaction()
    {
        $db = $this->getAdapter();
        $db->getDriver()->getConnection()->beginTransaction();
    }

    public function commit()
    {
        $db = $this->getAdapter();
        $db->getDriver()->getConnection()->commit();
    }

    public function rollback()
    {
        $db = $this->getAdapter();
        $db->getDriver()->getConnection()->rollback();
    }

    public function readAndLock($id)
    {
        $identifier = static::ID;
        $db = $this->getAdapter();
        $queryStr = 'SELECT ' . Select::SQL_STAR
                . ' FROM ' . $db->platform->quoteIdentifier($this->store->getTable())
                . ' WHERE ' . $db->platform->quoteIdentifier($identifier) . ' = ?'
                . ' FOR UPDATE';

        $rowset = $db->query($queryStr, array($id));
        $data = $rowset->current();
        if (is_null($data)) {
            return null;
        } else {
            return $data->getArrayCopy();
        }
    }

    public function read($id)
    {
        $where = [static::ID => $id];
        $rowset = $this->select($where);
        $data = $rowset->current();
        if (!isset($data)) {
            return null;
        } else {
            return $data->getArrayCopy();
        }
    }

}
