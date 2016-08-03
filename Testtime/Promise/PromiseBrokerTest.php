<?php

namespace zaboy\test\async\Promise;

use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;
use zaboy\async\Promise\Factory\BrokerFactory;
use zaboy\async\Promise\Broker;
use Interop\Container\ContainerInterface;
use zaboy\rest\TableGateway\TableManagerMysql;

class BrokerTest extends \PHPUnit_Framework_TestCase
{

    const TEST_TABLE_NAME = 'test_mysqlpromisebroker';

    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;

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
        $this->container = include './config/container.php';
        $this->adapter = $this->container->get('db');
        $BrokerFactory = new BrokerFactory();
        $this->object = $BrokerFactory->__invoke(
                $this->container
                , ''
                , [MySqlAdapterFactory::KEY_TABLE_NAME => self::TEST_TABLE_NAME]
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $tableManagerMysql = new TableManagerMysql($this->adapter);
        $tableManagerMysql->deleteTable(self::TEST_TABLE_NAME);
    }

    public function testBrokerTest__makePromise()
    {
        $promise = $this->object->makePromise();
        $promiseId = $promise->getPromiseId();
        $promise = $this->object->getPromise($promiseId);

        $this->assertInstanceOf(
                'zaboy\async\Promise\Promise', $promise
        );

        $this->assertEquals(
                $promise->getState(), 'pending'
        );
        $this->assertEquals(
                strpos($promise->promiseId, 'promise'), 0
        );
//        $time = $promise->timeEnd - 3600;
//        $this->assertTrue(
//                abs($time - (microtime(1) - date('Z'))) < 10
//        );
    }

    public function testBrokerTest__getPromise()
    {
        $promiseId = $this->object->makePromise();
        $this->assertSame(
                get_class($this->object->getPromise($promiseId)), 'zaboy\async\Promise\Promise'
        );
        $this->assertEquals(
                get_class($this->object->getPromise($promiseId)), 'zaboy\async\Promise\Promise'
        );
    }

}
