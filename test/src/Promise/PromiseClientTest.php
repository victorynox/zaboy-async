<?php

namespace zaboy\test\async\PromiseClient;

use zaboy\test\async\Queue\Client\ClientTestAbstract;
use zaboy\async\Queue;

class PromiseClientTest extends ClientTestAbstract
{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $container = include 'config/container.php';
        $this->object = $container->get('testMysqlQueue');
        parent::setUp();
    }

}
