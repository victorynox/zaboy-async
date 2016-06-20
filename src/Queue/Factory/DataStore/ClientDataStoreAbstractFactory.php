<?php

namespace zaboy\async\Queue\Factory\DataStore;

use Interop\Container\ContainerInterface;
use zaboy\rest\AbstractFactoryAbstract;
use zaboy\async\Queue\DataStore\ClientDataStore;

/**
 * Creates if can and returns an instance of class Queue\DataStore\ClientDataStore
 *
 * Config:
 * <code>
 *   'services' => [
 *       'abstract_factories' => [
 *           'zaboy\async\Queue\Factory\DataStore\ClientDataStoreAbstractFactory'
 *       ]
 *   ],
 *   'dataStore' => [
 *       'test_ClientDataStore' => [
 *           'queueClient' => 'testMysqlQueue'
 *       ]
 *   ]
 * <code>
 *
 * @category   async
 * @package    zaboy
 */
class ClientDataStoreAbstractFactory extends AbstractFactoryAbstract
{

    const KEY_QUEUE_CLIENT = 'queueClient';

    /**
     * Create and return an instance of the ClientDataStore.
     *
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  string $requestedName
     * @param  array $options
     * @return \DataStores\Interfaces\DataStoresInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $queueClientServiceName = $config['dataStore'][$requestedName][self::KEY_QUEUE_CLIENT];
        $queueClientService = $container->get($queueClientServiceName);
        $clientDataStore = new ClientDataStore($queueClientService);
        return $clientDataStore;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        return
                isset($config['dataStore'][$requestedName][self::KEY_QUEUE_CLIENT]) &&
                $container->has($config['dataStore'][$requestedName][self::KEY_QUEUE_CLIENT])
        ;
    }

}
