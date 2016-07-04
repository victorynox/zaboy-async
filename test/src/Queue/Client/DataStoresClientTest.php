<?php

namespace zaboy\test\async\Queue\Client;

class DataStoresClientTest extends ClientTestAbstract
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
