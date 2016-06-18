<?php

namespace zaboy\async\Queue\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\AbstractFactoryAbstract;

/**
 * Creates if can and returns an instance of class Queue\Adapter\DataStoresAbstruct - Adapter for Queue
 *
 * Class ScriptAbstractFactory
 * @package zaboy\scheduler\Callback\Factory
 */
class QueueAdapterAbstractFactory extends AbstractFactoryAbstract
{

    const KEY_QUEUE_ADAPTER = 'queueAdapter';
    const DEFAULT_MESSAGES_DATA_STORE = 'MessagesDataStore';
    const DEFAULT_QUEUES_DATA_STORE = 'QueuesDataStore';

    protected $suppotedClasses = [
        'zaboy\async\Queue\Adapter\MysqlOueueAdapter' =>
        'zaboy\async\Queue\Factory\Adapter\MySqlAdapterFactory',
        'zaboy\async\Queue\Adapter\DataStores' =>
        'zaboy\async\Queue\Factory\Adapter\DataStoresAdapterFactory',
        'zaboy\async\Queue\Adapter\MemoryStoresOueueAdapter' =>
        'zaboy\async\Queue\Factory\Adapter\MemoryStoresAdapterFactory',
    ];

    /**
     * Create and return an instance of the Queue Adapter.
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
        $serviceConfig = $config[self::KEY_QUEUE_ADAPTER][$requestedName];
        $requestedClassName = $serviceConfig[self::KEY_CLASS];
        $factoryClass = $this->suppotedClasses[$requestedClassName];
        $adapterFactory = new $factoryClass();
        $adapter = $adapterFactory->__invoke($container, $requestedName, $serviceConfig);
        return $adapter;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        if (!isset($config[self::KEY_QUEUE_ADAPTER][$requestedName][self::KEY_CLASS])) {
            return false;
        }

        $requestedClassName = $config[self::KEY_QUEUE_ADAPTER][$requestedName][self::KEY_CLASS];
        $suppotedAdaptersClasses = array_keys($this->suppotedClasses);
        return in_array($requestedClassName, $suppotedAdaptersClasses, true);
    }

}