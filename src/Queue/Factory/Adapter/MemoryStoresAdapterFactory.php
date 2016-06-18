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
class MemoryStoresAdapterFactory extends FactoryAbstract
{

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $queuesDataStore = new Memory();
        $messagesDataStore = new Memory();

        $adapterQueues = new Adapter\DataStores($queuesDataStore, $messagesDataStore);

        return $adapterQueues;
    }

}
