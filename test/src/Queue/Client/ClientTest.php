<?php

namespace zaboy\test\async\Queue\Client;

use zaboy\test\async\Queue\Client\ClientTestAbstract;

class ClientTest extends ClientTestAbstract
{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $container = include 'config/container.php';
        $this->object = $container->get('testDataStoresQueue');
        parent::setUp();
    }

}
