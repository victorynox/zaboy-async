<?php

namespace zaboy\test\async\Queue;

use zaboy\async\Queue\Client;
use Interop\Container\ContainerInterface;
use zaboy\rest\TableGateway\TableManagerMysql;
use zaboy\async\Queue\Store;
use zaboy\async\Queue\Factory\StoreFactory;
use zaboy\async\Message\Message\Message;
use zaboy\async\Message\Store as MessageStore;
use zaboy\async\Promise\Store as PromiseStore;
use zaboy\async\ClientAbstract;
use zaboy\async\Queue\Interfaces\ClientInterface;
use zaboy\async\Message\Client as MessageClient;

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
        $tableManagerMysql->deleteTable($this->store->getTable());
        $tableManagerMysql->deleteTable($this->store->getMessagesStore()->getTable());
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

    public function test_QueueTest__rename()
    {
        $this->object = new Client($this->store);
        $this->assertInstanceOf(
                'zaboy\async\Queue\Client', $this->object
        );
        $this->object->rename('new_queue_name');
        $this->assertEquals(
                'new_queue_name', $this->object->getName()
        );
    }

    public function test_QueueTest__addMessage()
    {
        $this->object = new Client($this->store);
        $message = $this->object->addMessage('body');
        $messageId = $message->getId();
        $this->assertInstanceOf(
                'zaboy\async\Message\Client', $message
        );
        $this->assertTrue(
                $message->isId($messageId)
        );
        $this->assertEquals(
                'body', $message->getBody()
        );
        $this->assertEquals(
                1, $this->object->getNumberMessages()
        );
    }

    public function test_QueueTest__deleteMessage()
    {
        $this->object = new Client($this->store);
        $message = $this->object->addMessage('body');
        $messageId = $message->getId();
        $this->assertEquals(
                1, $this->object->getNumberMessages()
        );
        $promise = $message->getPromise();
        $this->assertEquals(
                $promise::PENDING, $promise->getState()
        );
        $this->object->deleteMessage($messageId);
        $this->assertEquals(
                0, $this->object->getNumberMessages()
        );
    }

    public function test_QueueTest__pullMessage()
    {
        $this->object = new Client($this->store);
        $message = $this->object->addMessage('body');
        $messageId = $message->getId();
        $this->assertEquals(
                1, $this->object->getNumberMessages()
        );
        $body = $this->object->pullMessage();
        $this->assertEquals(
                0, $this->object->getNumberMessages()
        );
        $this->assertEquals(
                'body', $body
        );
        $promise = $message->getPromise();
        $this->assertEquals(
                $promise::PENDING, $promise->getState()
        );
    }

    public function test_QueueTest__pullMessagePriority()
    {
        $this->object = new Client($this->store);
        $message1 = $this->object->addMessage('2', Message::LOW);
        $message2 = $this->object->addMessage('1', Message::NORM);
        $message2 = $this->object->addMessage('3', Message::LOW);

        $body = $this->object->pullMessage();
        $this->assertEquals(
                1, $body
        );
        $body = $this->object->pullMessage();
        $this->assertEquals(
                2, $body
        );
        $body = $this->object->pullMessage();
        $this->assertEquals(
                3, $body
        );
    }

    public function test_QueueTest__pullMessagePullPriority()
    {
        $this->object = new Client($this->store);
        $message1 = $this->object->addMessage('3', Message::HIGH);
        $message2 = $this->object->addMessage('1', Message::NORM);
        $message2 = $this->object->addMessage('2', Message::LOW);

        $body = $this->object->pullMessage(Message::NORM);
        $this->assertEquals(
                1, $body
        );
        $body = $this->object->pullMessage(Message::LOW);
        $this->assertEquals(
                2, $body
        );
        $this->assertNull(
                $this->object->pullMessage(Message::LOW)
        );
        $body = $this->object->pullMessage(Message::HIGH);
        $this->assertEquals(
                3, $body
        );
    }

    public function test_QueueTest__purge()
    {

    }

}
