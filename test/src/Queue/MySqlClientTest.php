<?php

namespace zaboy\test\async\Queue;

use zaboy\test\async\Queue\ClientTestAbstract;
use zaboy\async\Queue\Client;

class MySqlClientTest extends ClientTestAbstract
{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $container = include 'config/container.php';
        //$defaultQueueAdapter = $container->get('defaultQueueAdapter');
        //$defaultQueueAdapter->setMaxTimeInFlight();
        //$this->object = new Client($defaultQueueAdapter);
        $this->object = $container->get('defaultQueueClient');

        $date = new \DateTime('@1419237113');
        $this->_messageList = array(
            12,
            12.12,
            'string12',
            [22, 22.22, 'string22'],
            $date
        );
    }

}
