<?php

namespace zaboy\async;

use Zend\Db\TableGateway\TableGateway;
use zaboy\rest\DataStore\Interfaces\ReadInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql;

/**
 * Store
 *
 * id => Entity_id_123456789qwerty
 * creation_time = 2216125; UTC time when Entity has sarted
 *
 * @category   async
 * @package    zaboy
 */
class StoreAbstract extends TableGateway
{

    //ADAPTER_DATA_STORE
    //
    //'id' - unique id of Entity: Entity_id_123456789qwerty
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
        $identifier = self::ID;
        $db = $this->getAdapter();
        $queryStr = 'SELECT ' . Select::SQL_STAR
                . ' FROM ' . $db->platform->quoteIdentifier($this->getTable())
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
        $where = [self::ID => $id];
        $rowset = $this->select($where);
        $data = $rowset->current();
        if (!isset($data)) {
            return null;
        } else {
            return $data->getArrayCopy();
        }
    }

    public function insert($data)
    {
        return parent::insert($data);
    }

    public function update($data, $where = null)
    {
        return parent::update($data, $where);
    }

    public function count($where = [])
    {
        $db = $this->getAdapter();
        $sql = new Sql\Sql($db);
        $select = $sql->select()
                ->from($this->getTable())
                ->columns(array('count' => new Sql\Expression('COUNT(*)')))
                ->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        return $rowset->current()['count'];
    }

}
