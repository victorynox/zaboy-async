<?php

namespace zaboy\test\async\Queue\Broker;

use zaboy\async\Queue\Broker\QueueBroker;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\async\Queue\Client\Client;
use Interop\Container\ContainerInterface;

class QueueBrokerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var QueueBroker
     */
    protected $object;

    /**
     * @var DataStoresInterface
     */
    protected $dataStore;

    /**
     *
     * @var \zaboy\async\Queue\Client\Client
     */
    protected $queueClient1;

    /**
     *
     * @var zaboy\async\Queue\Client\Client
     */
    protected $queueClient2;

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
        $this->container = include 'config/container.php';
        $this->dataStore = $this->container->get('test_worker_datastore');
        $messageOut = $this->dataStore->deleteAll();

        $date = new \DateTime('@1419237113');
        $this->_messageList = array(
            [12, 'LOW'],
            [12.12, 'HIGH'],
            ['string12', 'HIGH'],
            [[22, 22.22, 'string22'], 'MID'],
            [$date, 'LOW'],
        );

        $this->object = $this->container->get('QueueBroker');

        $this->queueClient1 = $this->container->get('testMysqlQueue');
        $this->queueClient1->purgeQueue('ManagedQueue11');
        $this->queueClient1->purgeQueue('ManagedQueue12');

        $this->queueClient2 = $this->container->get('testDataStoresQueue');
        $this->queueClient2->purgeQueue('ManagedQueue21');
        $this->queueClient2->purgeQueue('ManagedQueue22');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->queueClient1 = $this->container->get('testMysqlQueue');
        $this->queueClient1->purgeQueue('ManagedQueue11');
        $this->queueClient1->purgeQueue('ManagedQueue12');

        $this->queueClient2 = $this->container->get('testDataStoresQueue');
        $this->queueClient2->purgeQueue('ManagedQueue21');
        $this->queueClient2->purgeQueue('ManagedQueue22');
    }

    public function test_getReadMessage()
    {
        $messageIn = ['message1' => 'test_getReadMessage'];
        $this->queueClient1->addMessage('ManagedQueue11', $messageIn);
        $this->object->runAllWorkers();
        $messageOut = $this->dataStore->getIterator()->current();
        $this->assertEquals(
                ['message1' => 'test_getReadMessage']
                , $messageOut[Client::BODY]
        );
    }

    public function test_getReadMessages()
    {
        foreach ($this->_messageList as $value) {
            $this->queueClient1->addMessage('ManagedQueue11', $value[0], $value[1]);
        }
        $this->object->runAllWorkers();
        foreach ($this->dataStore as $messageOut) {
            $messagesOut[] = [$messageOut[Client::BODY], $messageOut['priority']];
        }
        $this->assertEquals(
                $messagesOut[0][1]
                , 'HIGH'
        );
        $this->assertEquals(
                $messagesOut[4][1]
                , 'LOW'
        );
    }

    public function test_addIn2Clients()
    {
        foreach ($this->_messageList as $value) {
            $this->queueClient1->addMessage('ManagedQueue11', $value[0], $value[1]);
        }
        foreach ($this->_messageList as $value) {
            $this->queueClient1->addMessage('ManagedQueue11', $value[0], $value[1]);
        }
        $this->object->runAllWorkers();
        $this->assertEquals(
                10
                , $this->dataStore->count()
        );
    }

    public function test_getReadHighPriorityMessage()
    {
        foreach ($this->_messageList as $value) {
            $this->queueClient1->addMessage('ManagedQueue11', $value[0], $value[1]);
        }
        foreach ($this->_messageList as $value) {
            $this->queueClient1->addMessage('ManagedQueue11', $value[0], $value[1]);
        }
        $this->object->runHighPriorityWorkers();
        $this->assertEquals(
                4
                , $this->dataStore->count()
        );
    }

}
