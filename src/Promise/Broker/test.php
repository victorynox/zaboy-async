<?php

namespace zaboy\async\Promise\Broker;

use zaboy\scheduler\Callback\Interfaces\CallbackInterface;
use zaboy\async\Promise\PromiseClient;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter;
use zaboy\async\Promise\PromiseException;
use zaboy\rest\DataStore\DbTable;
use Zend\ServiceManager\ServiceLocatorInterface;
use zaboy\scheduler\DataStore\UTCTime;
use zaboy\rest\DataStore\Interfaces\ReadInterface;
use GuzzleHttp\Promise\Promise;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class PromiseBroker implements PromiseBrokerInterface
{

    const SERVICE_NAME = 'promise_broker';

    /**
     *
     * @var MySqlPromiseAdapter
     */
    public $promiseAdapter;

    /**
     *
     *
     * @param MySqlPromiseAdapter $promiseAdapter
     */
    public function __construct(MySqlPromiseAdapter $promiseAdapter)
    {
        self::$promiseAdapter = $promiseAdapter;
    }

    public function getPromise($promiseId)
    {
        $promise = new PromiseClient($this->promiseAdapter, $promiseId);
        return $promise;
    }

//
//
//

    public function makePromise(
    callable $waitFn = null
    , callable $cancelFn = null
    , $parentId = null
    , callable $onFulfilled = null
    , callable $onRejected = null
    , $state = Promise::PENDING
    , $result = null
    )
    {
        $promiseId = $this->makePromiseId();
        $serializedWaitFn = !$waitFn ? $this->serializeCallable($waitFn) : null;
        $serializedCancelFn = !$cancelFn ? $this->serializeCallable($cancelFn) : null;
        $serializedOnFulfilled = !$onFulfilled ? $this->serializeCallable($onFulfilled) : null;
        $serializedOnRejected = !$onRejected ? $this->serializeCallable($onRejected) : null;
        $serializedEmptyArray = serialize([]);
        $timeEnd = (int) UTCTime::getUTCTimestamp(0, 0) + static::DEFAULT_MAX_LIFE_TIME;
        $itemData = [
            ReadInterface::DEF_ID => $promiseId,
            MySqlPromiseAdapter::STATE => $state,
            MySqlPromiseAdapter::RESULT => $result,
            MySqlPromiseAdapter::CANCEL_FN => $serializedCancelFn,
            MySqlPromiseAdapter::WAIT_FN => $serializedWaitFn,
            MySqlPromiseAdapter::WAIT_LIST => $serializedEmptyArray,
            MySqlPromiseAdapter::MAX_ENDING_TIME => $timeEnd,
            MySqlPromiseAdapter::PARENT_ID => $parentId,
            MySqlPromiseAdapter::ON_FULFILLED => $serializedOnFulfilled,
            MySqlPromiseAdapter::ON_REJECTED => $serializedOnRejected,
        ];
        $db = $this->dbTable->getAdapter();
        try {
            $rowsCount = $this->dbTable->insert($itemData);
        } catch (\Exception $e) {
            throw new PromiseException('Can\'t insert item', 0, $e);
        }
        if (!$rowsCount) {
            throw new PromiseException('Can\'t insert item', 0, $e);
        }
        return $promiseId;
    }

    public function hasPromise($promiseId)
    {
        $identifier = ReadInterface::DEF_ID;
        $rowset = $this->dbTable->select(array($identifier => $promiseId));
        $row = $rowset->current();
        return isset($row);
    }

    public function updatePromise($promiseId, $itemData)
    {
        $identifier = ReadInterface::DEF_ID;
        $db = $this->dbTable->getAdapter();
        try {
            $errorMsg = 'Can\'t update Promise';
            unset($itemData[$identifier]);
            $rowsCount = $this->dbTable->update($itemData, array($identifier => $promiseId));
        } catch (\Exception $e) {
            throw new PromiseException($errorMsg . ' Pomise: ' . $promiseId, 0, $e);
        }
        if (!$rowsCount) {
            throw new PromiseException('Can not update promise: ' . $promiseId, 0, $e);
        }
    }

    protected function serializeCallable($callback)
    {
        switch (true) {
            case is_null($callback):
            case is_string($callback):
                return $callback;
                break;

            default:
                throw new PromiseException(
                'Can\'t serialize callback'
                );
        }
    }

    protected function unserializeCallable($callbackString)
    {
        switch (true) {
            case is_null($callbackString):
            case is_string($callbackString):
                return $callbackString;
                break;

            default:
                throw new PromiseException(
                'Can\'t unserialize callback - it must be a string or null'
                );
        }
    }

    public function getState($promiseId)
    {
        $identifier = ReadInterface::DEF_ID;
        $rowset = $this->dbTable->select(array($identifier => $promiseId));
        $row = $rowset->current();
        if (isset($row)) {
            return $row[MySqlPromiseAdapter::STATE];
        } else {
            throw new PromiseException(
            "Pomise $promiseId is not exist"
            );
        }
    }

    public function resolve($promiseId, $result)
    {
        if ($this->isPromise($result)) {
            throw new PromiseException(
            "You can not set promise as resolved value."
            );
        }
        $identifier = ReadInterface::DEF_ID;
        $db = $this->dbTable->getAdapter();
        $queryStrPromise = 'SELECT ' . Select::SQL_STAR
                . ' FROM ' . $db->platform->quoteIdentifier($this->dbTable->getTable())
                . ' WHERE ' . $db->platform->quoteIdentifier($identifier) . ' = ?'
                . ' FOR UPDATE';

        $queryStrParentPromise = 'SELECT ' . Select::SQL_STAR
                . ' FROM ' . $db->platform->quoteIdentifier($this->dbTable->getTable())
                . ' WHERE ' . $db->platform->quoteIdentifier(MySqlPromiseAdapter::PARENT_ID) . ' = ?'
                . ' FOR UPDATE';
        try {
            $errorMsg = 'Can\'t start transaction for "resolve"';
            $db->getDriver()->getConnection()->beginTransaction();
            //is row with this index exist?
            $rowset = $db->query($queryStrPromise, array($promiseId));
            $errorMsg = 'Can not execute "resolve". Pomise is not exist.';
            if (is_null($rowset->current())) {
                throw new PromiseException( );
            }
            $row = $rowset->current();
            $state = $row[MySqlPromiseAdapter::STATE];
            $result = $row[MySqlPromiseAdapter::RESULT];

            switch (true) {
                case $state === Promise::PENDING:
                    $errorMsg = 'Can not make next pending promise.';
                    $this->updatePromise($promiseId, [MySqlPromiseAdapter::STATE => Promise::FULFILLED, MySqlPromiseAdapter::RESULT => $result]);
                    $rowsetDependentPromises = $db->query($queryStrParentPromise, array($promiseId));
                    break;

                case $state === Promise::REJECTED || $state === Promise::FULFILLED:
                    $errorMsg = 'Can not resolve not pending promise';
                    throw new PromiseException($errorMsg);
                default:
                    $errorMsg = 'Error state: ' . $state;
                    throw new PromiseException( );
            }

            $db->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $db->getDriver()->getConnection()->rollback();
            throw new PromiseException($errorMsg . ' Pomise: ' . $promiseId, 0, $e);
        }

        foreach ($rowsetDependentPromises as $dependentPromise) {
            $dependentPromiseId = $dependentPromise[$identifier];
            $this->asyncCall($onFulfilled, $result, $dependentPromiseId); //$promiseId as parent promise
        }
    }

    public function then($promiseId, callable $onFulfilled = null, callable $onRejected = null)
    {
        $identifier = ReadInterface::DEF_ID;
        $db = $this->dbTable->getAdapter();
        // begin Transaction
        $errorMsg = 'Can\'t start transaction for "then"';
        $queryStr = 'SELECT ' . Select::SQL_STAR
                . ' FROM ' . $db->platform->quoteIdentifier($this->dbTable->getTable())
                . ' WHERE ' . $db->platform->quoteIdentifier($identifier) . ' = ?'
                . ' FOR UPDATE';
        $db->getDriver()->getConnection()->beginTransaction();
        try {
            //is row with this index exist?
            $rowset = $db->query($queryStr, array($promiseId));
            $errorMsg = 'Can not execute "then". Pomise is not exist.';
            if (is_null($rowset->current())) {
                throw new PromiseException( );
            }
            $row = $rowset->current();
            $state = $row[MySqlPromiseAdapter::STATE];
            $result = $row[MySqlPromiseAdapter::RESULT];
            switch (true) {
                case $state === Promise::PENDING:
                    $errorMsg = 'Can not make next pending promise.';
                    $nextPromise = $this->makePromise(null, null, $promiseId, $onFulfilled, $onRejected);
                    break;

                case $state === Promise::REJECTED && is_null($onRejected):
                    $errorMsg = 'Can not make next rejected promise.';
                    $nextPromise = $this->makePromise(null, null, $promiseId, null, null, Promise::REJECTED, $result);
                    break;

                case $state === Promise::REJECTED && !is_null($onRejected):
                    $errorMsg = 'Can not call onRejected method.' . $promiseId;
                    $nextPromise = $this->asyncCall($onRejected, $result, $promiseId); //$promiseId as parent promise
                    break;

                case $state === Promise::FULFILLED && is_null($onFulfilled):
                    $errorMsg = 'Can not make next fulfilled promise.';
                    $nextPromise = $this->makePromise(null, null, $promiseId, null, null, Promise::FULFILLED, $result);
                    break;

                case $state === Promise::FULFILLED && !is_null($onFulfilled):
                    $errorMsg = 'Can not call $onFulfilled method.';
                    $nextPromise = $this->asyncCall($onFulfilled, $result, $promiseId); //$promiseId as parent promise
                    break;
                default:
                    $errorMsg = 'Error state: ' . $state;
                    throw new PromiseException( );
            }

            $db->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $db->getDriver()->getConnection()->rollback();
            throw new PromiseException($errorMsg . ' Pomise: ' . $promiseId, 0, $e);
        }
    }

    protected function isPromise($param)
    {
        return (bool) strpos($param, static::PROMISE_ID_PREFIX . static::ID_SEPARATOR);
    }

    protected function asyncCall(callable $callBack, $param)
    {
        $promiseId = $callBack($param);
        return $promiseId;
    }

    public function callOnFulfilled($promiseId, $result)
    {
        $promiseData = $this->getPromiseData($promiseId);
        if (isset($promiseData)) {
            if (isset($promiseData[MySqlPromiseAdapter::ON_FULFILLED])) {
                $onFulfilled = $this->unserializeCallable($promiseData[MySqlPromiseAdapter::ON_FULFILLED]);
            }
        }



        $promiseId = $callBack($param);
        return $promiseId;
    }

}
