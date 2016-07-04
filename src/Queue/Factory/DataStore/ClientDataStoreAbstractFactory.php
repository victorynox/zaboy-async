<?php

namespace zaboy\async\Queue\Factory\DataStore;

use Interop\Container\ContainerInterface;
use zaboy\rest\AbstractFactoryAbstract;
use zaboy\async\Queue\DataStore\ClientDataStore;
use zaboy\async\Queue\Factory\QueueBrokerFactory;

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
     * @param  \Interop\Container\ContainerInterface $container
     * @param  string $requestedName
     * @param  array $options
     * @return \ReputationVIP\QueueClient\Adapter\AdapterInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $queueClientServiceName = $config['dataStore'][$requestedName][self::KEY_QUEUE_CLIENT];
        $queueClientService = $container->get($queueClientServiceName);
        $queueBroker = $container->has(QueueBrokerFactory::KEY_QUEUE_BROKER) ? $container->get(QueueBrokerFactory::KEY_QUEUE_BROKER) : null;
        $clientDataStore = new ClientDataStore($queueClientService, $queueBroker);
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
                !isset($config['dataStore'][$requestedName][QueueDataStoreAbstractFactory::KEY_QUEUE_NAME]) &&
                $container->has($config['dataStore'][$requestedName][self::KEY_QUEUE_CLIENT])
        ;
    }

}
