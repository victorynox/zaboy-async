<?php

namespace zaboy\test\async\Queue\DataStore;

use zaboy\async\Queue\DataStore\QueueDataStore;
use zaboy\async\Queue;
use zaboy\async\Queue\Client\Client;
use Xiag\Rql\Parser\Query;

class QueueDataStoreTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var QueueDataStore
     */
    protected $object;

    /**
     * @var Client
     */
    protected $queueClient;

    /**
     *
     * @var array
     */
    protected $_messageList;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

        $container = include 'config/container.php';
        $this->object = $container->get('test_QueueDataStore');

        $this->queueClient = $this->object->getQueueClient();
        $this->queueClient->deleteQueue('ManagedQueue11');
        $this->queueClient->createQueue('ManagedQueue11');

        $date = new \DateTime('@1419237113');
        $this->_messageList = array(
            12,
            12.12,
            'string12',
            [22, 22.22, 'string22'],
            $date
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->queueClient->deleteQueue('ManagedQueue11');
    }

    public function test_QueryEmpty()
    {
        $query = new Query();
        $this->assertEquals(
                []
                , $this->object->query($query)
        );
    }

    public function test_Count()
    {

        foreach ($this->_messageList as $message) {
            $this->queueClient->addMessage('ManagedQueue11', $message);
        }
        $number = $this->object->count();
        $this->assertEquals(
                5
                , $number
        );
    }

    public function test_Create()
    {

        foreach ($this->_messageList as $message) {
            $this->object->create([Client::BODY => $message]);
        }
        $number = $this->object->count();
        $this->assertEquals(
                5
                , $number
        );
    }

    public function test_getMessages()
    {

        foreach ($this->_messageList as $message) {
            $this->object->create([Client::BODY => $message]);
        }
        $msg = $this->object->read(null);

        $this->assertEquals(
                1
                , substr_count($msg[Client::MESSAGE_ID], 'ManagedQueue11')
        );
    }

    public function test_returnToQueueAfterTimeCRUD()
    {
        foreach ($this->_messageList as $message) {
            $this->object->create([Client::BODY => $message]);
        }
        $message1 = $this->object->read(null);
        $message2 = $this->object->read('null');
        $message3 = $this->object->read('null()');
        $number2 = $this->queueClient->getNumberMessages('ManagedQueue11');
        $this->assertEquals(
                2
                , $number2
        );
        $this->object->delete($message1[Client::MESSAGE_ID]);
        sleep(3);
        $number4 = $this->queueClient->getNumberMessages('ManagedQueue11');
        $this->assertEquals(
                4
                , $number4
        );
    }

    public function test_readMessage()
    {

        $prioritys = ['MID', 'HIGH', 'HIGH', 'LOW', 'MID',];
        foreach ($this->_messageList as $message) {
            $priority = array_shift($prioritys);

            $this->object->create([
                Client::MESSAGE_ID => 'ManagedQueue11',
                Client::BODY => $message,
                Client::PRIORITY => $priority
            ]);
        }
        $message = $this->object->read(null);
        $this->assertEquals(
                'HIGH'
                , $message[Client::PRIORITY]
        );
        $message = $this->object->read(null);
        $this->assertEquals(
                'HIGH'
                , $message[Client::PRIORITY]
        );
        $message = $this->object->read(null);
        $this->assertEquals(
                'MID'
                , $message[Client::PRIORITY]
        );
        $message = $this->object->read(null);
        $this->assertEquals(
                'MID'
                , $message[Client::PRIORITY]
        );
        $message = $this->object->read(null);
        $this->assertEquals(
                'LOW'
                , $message[Client::PRIORITY]
        );
        $message = $this->object->read(null);
        $this->assertNull(
                $message
        );
    }

}
