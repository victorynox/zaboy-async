<?php

namespace zaboy\async\Queue\Factory\DataStore;

use Interop\Container\ContainerInterface;
use zaboy\rest\AbstractFactoryAbstract;
use zaboy\async\Queue\DataStore\QueueDataStore;

/**
 * Creates if can and returns an instance of class Queue\DataStore\ClientDataStore
 *
 * Config:
 * <code>
 *   'services' => [
 *       'abstract_factories' => [
 *           'zaboy\async\Queue\Factory\DataStore\QueueDataStoreAbstractFactory'
 *       ]
 *   ],
 *   'dataStore' => [
 *       'test_QueueDataStore' => [
 *           'queueClient' => 'testMysqlQueue' //name of service
 *           'queueName' => 'theNameOfQueue'   //name of queue (not service name
 *       ]
 *   ],
 *   'queueClient' => [
 *       'testMysqlQueue' => [
 *           'QueueAdapter' => 'Test-Mysql_QueueAdapter 2sec',
 *           'maxTimeInFlight' => 2
 *    ]
 *  ],
 * <code>
 *
 * @category   async
 * @package    zaboy
 */
class QueueDataStoreAbstractFactory extends AbstractFactoryAbstract
{

    const KEY_QUEUE_CLIENT = 'queueClient';
    const KEY_QUEUE_NAME = 'queueName';

    /**
     * Creates and returns an instance of the ClientDataStore.
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
        $queueName = $config['dataStore'][$requestedName][self::KEY_QUEUE_NAME];
        $queueClientService = $container->get($queueClientServiceName);
        $queueDataStore = new QueueDataStore($queueClientService, $queueName);
        return $queueDataStore;
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
                isset($config['dataStore'][$requestedName][self::KEY_QUEUE_NAME]) &&
                $container->has($config['dataStore'][$requestedName][self::KEY_QUEUE_CLIENT])

        ;
    }

}
