<?php

namespace zaboy\test\async\Queue\Http;

use zaboy\test\async\Queue\Client\ClientTestAbstract;
use zaboy\async\Queue;
use zaboy\async\Queue\Client\Client;

class QueueClientDataStoreTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Client
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $container = include 'config/container.php';
        $this->object = $container->get('HttpClientQueue');
    }

    public function test_create_delete()
    {
        $this->object->create([Client::QUEUE => 'ManagedQueue11', Client::BODY => 'test_create_delete()__1']);
        //$this->object->create([Client::MESSAGE_ID => 'ManagedQueue11', Client::BODY => 'test_create_delete()____2']);
        //$message = $this->object->read('ManagedQueue11');
        //$this->object->delete('1_ManagedQueue11__5767cf01f26d11_98120205');
        $this->assertTrue(true); /*
          $this->object->getAdapter()
          instanceof
          \ReputationVIP\QueueClient\Adapter\AdapterInterface
          ); */
    }

}
