<?php

namespace zaboy\test\async\Queue;

use zaboy\async\Queue\Client;
use Interop\Container\ContainerInterface;
use zaboy\rest\TableGateway\TableManagerMysql;
use zaboy\async\Queue\Store;
use zaboy\async\Queue\Factory\StoreFactory;

class ClientTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Store
     */
    protected $store;

    /**
     * @var Client
     */
    protected $object;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        global $testCase;
        $testCase = 'table_for_test';
        $this->tableName = StoreFactory::TABLE_NAME . '_test';

        $this->container = include './config/container.php';
        $this->store = $this->container->get(StoreFactory::KEY);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $adapter = $this->container->get('db');
        $tableManagerMysql = new TableManagerMysql($adapter);
        $tableManagerMysql->deleteTable($this->tableName);
    }

    /* ---------------------------------------------------------------------------------- */

    public function test_QueueTest__extractIdFromString()
    {
        $this->object = new Client($this->store);
        $string = ' jkiuhs iuhis pi siuiughf]l;m74jn &568ihj983438h^&%  ';
        $this->assertEquals(
                [], $this->object->extractId($string)
        );
        $string = ' jkiuhs iuhis pi siu queue__1469864422_189511__579c84162e43e4_34952052 iughf]l;m74jn &568ihj983438h^&%  ';
        $this->assertEquals(
                ['queue__1469864422_189511__579c84162e43e4_34952052'], $this->object->extractId($string)
        );
        $string = ' jkiuhs iuhis pi s queue__2229864461_889811__579c843dd93ad1_08516192  AND queue__3339864461_889811__579c843dd93ad1_08516192';
        $this->assertEquals(
                [
            'queue__3339864461_889811__579c843dd93ad1_08516192',
            'queue__2229864461_889811__579c843dd93ad1_08516192',
                ]
                , $this->object->extractId($string)
        );
    }

    public function test_QueueTest__extractIdFromException()
    {
        $this->object = new Client($this->store);
        $exc1 = new \Exception(' Queue: queue__1119864461_889811__579c843dd93ad1_08516192');
        $exc2 = new \Exception('queue__2229864461_889811__579c843dd93ad1_08516192  AND queue__3339864461_889811__579c843dd93ad1_08516192', 0, $exc1);
        $exc3 = new \Exception('queue__4449864461_889811__579c843dd93ad1_08516192 of the end', 0, $exc2);

        $this->assertEquals(
                [
            'queue__4449864461_889811__579c843dd93ad1_08516192',
            'queue__3339864461_889811__579c843dd93ad1_08516192',
            'queue__2229864461_889811__579c843dd93ad1_08516192',
            'queue__1119864461_889811__579c843dd93ad1_08516192',
                ]
                , $this->object->extractId($exc3)
        );
    }

    public function test_QueueTest__makeWithoutName()
    {
        $this->object = new Client($this->store);
        $this->assertInstanceOf(
                'zaboy\async\Queue\Client', $this->object
        );
        $this->assertEquals(
                md5($this->object->getId()), $this->object->getName()
        );
    }

    public function test_QueueTest__makeWithName()
    {
        $this->object = new Client($this->store, [Store::NAME => 'queue_name']);
        $this->assertInstanceOf(
                'zaboy\async\Queue\Client', $this->object
        );
        $this->assertEquals(
                'queue_name', $this->object->getName()
        );
    }

    public function test_QueueTest__setName()
    {
        $this->object = new Client($this->store);
        $this->assertInstanceOf(
                'zaboy\async\Queue\Client', $this->object
        );
        $this->object->setName('new_queue_name');
        $this->assertEquals(
                'new_queue_name', $this->object->getName()
        );
    }

    public function test_QueueTest__purge()
    {

    }

}
