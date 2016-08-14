<?php

namespace zaboy\test\async\Message;

use Interop\Container\ContainerInterface;
use zaboy\rest\TableGateway\TableManagerMysql;
use zaboy\async\Message\Store;
use zaboy\async\Message\Client;
use zaboy\async\Message\Factory\StoreFactory;
use zaboy\async\Queue\Store as QueueStore;
use zaboy\async\Queue\Client as QueueClient;
use zaboy\async\Queue\Factory\StoreFactory as QueueStoreFactory;

class ClientTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var QueueStore
     */
    protected $queueStore;

    /**
     * @var QueueClient
     */
    protected $queueClient;

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
     * This method is called before a test is executed
     */
    protected function setUp()
    {
        global $testCase;
        $testCase = 'table_for_test';
        $this->tableName = QueueStoreFactory::TABLE_NAME . '_test';

        $this->container = include './config/container.php';
        $this->queueStore = $this->container->get(QueueStoreFactory::KEY);
        $this->queueClient = new QueueClient($this->queueStore);
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

    public function test_MessageTest__extractIdFromString()
    {
        $this->object = new Client($this->queueClient, []);
        $string = ' jkiuhs iuhis pi siuiughf]l;m74jn &568ihj983438h^&%  ';
        $this->assertEquals(
                [], $this->object->extractId($string)
        );
        $string = ' jkiuhs iuhis pi siu message__1469864422_189511__579c84162e43e4_34952052 iughf]l;m74jn &568ihj983438h^&%  ';
        $this->assertEquals(
                ['message__1469864422_189511__579c84162e43e4_34952052'], $this->object->extractId($string)
        );
        $string = ' jkiuhs iuhis pi s message__2229864461_889811__579c843dd93ad1_08516192  AND message__3339864461_889811__579c843dd93ad1_08516192';
        $this->assertEquals(
                [
            'message__3339864461_889811__579c843dd93ad1_08516192',
            'message__2229864461_889811__579c843dd93ad1_08516192',
                ]
                , $this->object->extractId($string)
        );
    }

    public function test_MessageTest__extractIdFromException()
    {
        $this->object = new Client($this->queueClient, []);
        $exc1 = new \Exception(' Message: message__1119864461_889811__579c843dd93ad1_08516192');
        $exc2 = new \Exception('message__2229864461_889811__579c843dd93ad1_08516192  AND message__3339864461_889811__579c843dd93ad1_08516192', 0, $exc1);
        $exc3 = new \Exception('message__4449864461_889811__579c843dd93ad1_08516192 of the end', 0, $exc2);

        $this->assertEquals(
                [
            'message__4449864461_889811__579c843dd93ad1_08516192',
            'message__3339864461_889811__579c843dd93ad1_08516192',
            'message__2229864461_889811__579c843dd93ad1_08516192',
            'message__1119864461_889811__579c843dd93ad1_08516192',
                ]
                , $this->object->extractId($exc3)
        );
    }

//    public function test_MessageTest__make()
//    {
//        $this->object = new Client($this->queueClient);
//        $this->assertInstanceOf(
//                'zaboy\async\Message\Client', $this->object
//        );
//        $this->assertEquals(
//                md5($this->object->getId()), $this->object->getName()
//        );
//    }
}
