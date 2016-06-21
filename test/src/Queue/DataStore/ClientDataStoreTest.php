<?php

namespace zaboy\test\async\Queue\DataStore;

use zaboy\async\Queue\DataStore\ClientDataStore;
use zaboy\async\Queue;
use zaboy\async\Queue\Client\Client;
use Xiag\Rql\Parser\Query;

class ClientDataStoreTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ClientDataStore
     */
    protected $object;

    /**
     * @var Client
     */
    protected $queueClient;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $container = include 'config/container.php';
        $this->object = $container->get('test_ClientDataStore');
        $this->queueClient = $this->object->getQueueClient();
        $this->queueClient->createQueue('ManagedQueue11');
        $this->queueClient->createQueue('ManagedQueue12');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->queueClient->deleteQueue('ManagedQueue11');
        $this->queueClient->deleteQueue('ManagedQueue12');
    }

    public function test_Query()
    {
        $query = new Query();
        $this->assertEquals(
                [['id' => 'ManagedQueue11'], [ 'id' => 'ManagedQueue12']]
                , $this->object->query($query)
        );
    }

    public function test_Read()
    {
        $this->assertEquals(
                ['id' => 'ManagedQueue11']
                , $this->object->read('ManagedQueue11')
        );
    }

    public function test_Count()
    {
        $this->assertEquals(
                2
                , $this->object->count()
        );
    }

}
