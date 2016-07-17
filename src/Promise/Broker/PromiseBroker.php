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
    protected $promiseAdapter;

    /**
     *
     *
     * @param MySqlPromiseAdapter $promiseAdapter
     */
    public function __construct(MySqlPromiseAdapter $promiseAdapter)
    {
        $this->promiseAdapter = $promiseAdapter;
    }

    public function makePromise()
    {
        $promise = new PromiseClient($this->promiseAdapter);
        return $promise;
    }

    public function getPromise($promiseId)
    {
        if (!isset($promiseId)) {
            throw new PromiseException('Can not run  "PromiseBroker::getPromise(NULL)"');
        }
        $promise = new PromiseClient($this->promiseAdapter, $promiseId);
        return $promise;
    }

}
