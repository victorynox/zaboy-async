<?php

namespace zaboy\async\Queue\Factory\Adapter;

use Interop\Container\ContainerInterface;
use zaboy\rest\DataStore\Memory;
use zaboy\rest\FactoryAbstract;
use zaboy\async\Queue\Adapter;

/**
 * Creates if can and returns an instance of class Queue\Adapter\DataStoresAbstruct - Adapter for Queue
 *
 * Class ScriptAbstractFactory
 *
 * @category   async
 * @package    zaboy
 */
class DataStoresAdapterFactory extends FactoryAbstract
{

    const KEY_QUEUES_DATA_STORE = 'QueuesDataStore';
    const KEY_MESSAGES_DATA_STORE = 'MessagesDataStore';

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $queuesDataStoreName = $options[self::KEY_QUEUES_DATA_STORE];
        $queuesDataStore = $container->get($queuesDataStoreName);
        $messagesDataStoreName = $options[self::KEY_MESSAGES_DATA_STORE];
        $messagesDataStore = $container->get($messagesDataStoreName);

        $adapterQueues = new Adapter\DataStores($queuesDataStore, $messagesDataStore);

        return $adapterQueues;
    }

}
