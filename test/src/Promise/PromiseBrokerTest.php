<?php

namespace zaboy\test\async\Promise;

use zaboy\async\Promise\Factory\StoreFactory;
use zaboy\async\Promise\Factory\BrokerFactory;
use zaboy\async\Promise\Broker;
use zaboy\async\Promise\Promise;
use Interop\Container\ContainerInterface;
use zaboy\rest\TableGateway\TableManagerMysql;

class BrokerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Broker
     */
    protected $object;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        global $testCase;
        $testCase = 'table_for_test';

        $this->container = include './config/container.php';
        $this->object = $this->container->get(BrokerFactory::KEY);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        /* @var $store Store */
        $store = $this->container->get(StoreFactory::KEY);
        $tableName = $store->table;
        /* @var $tableManagerMysql TableManagerMysql */
        $tableManagerMysql = $this->container->get(TableManagerMysql::KEY_IN_CONFIG);
        $tableManagerMysql->deleteTable($tableName);
    }

    public function test__makePromise()
    {
        $promise = $this->object->makePromise();
        $this->assertInstanceOf(
                Promise::class, $promise
        );
    }

    /**
     *
     * @todo LifeTime test
     */
    public function test__getPromise()
    {
        $promise = $this->object->makePromise();
        $promiseId = $promise->getPromiseId();
        $promise = $this->object->getPromise($promiseId);
        $this->assertEquals(
                $promiseId, $promise->getPromiseId()
        );
        $this->assertInstanceOf(
                Promise::class, $promise
        );
        $promise = $this->object->getPromise($promiseId);
        $this->assertInstanceOf(
                Promise::class, $promise
        );
    }

    public function test__deletePromise()
    {
        $promise = $this->object->makePromise();
        $promiseId = $promise->getPromiseId();
        $result = $this->object->deletePromise($promiseId);
        $this->assertTrue(
                $result
        );
        $this->setExpectedException('\zaboy\async\Promise\PromiseException');
        $promise = $this->object->getPromise($promiseId)->getState();
        $this->assertFalse(
                $this->object->deletePromise($promiseId)
        );
    }

}
