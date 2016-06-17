<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Queue\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\AbstractFactoryAbstract;
use zaboy\async\Queue\QueueException;
use zaboy\async\Queue\Adapter;
use zaboy\async\Queue\Client\Client;

/**
 * Create and return an instance of the array in Memory
 *
 * This Factory depends on Container (which should return an 'config' as array)
 *
 * The configuration can contain:
 * <code>
 * 'queues' => [
 *     'MainQueue' => [
 *         'class' => 'zaboy\async\Queue\Client',
 *         'maxTimeInFlight' => 60,
 *         'queuesDataStore' => QueuesDataStoreDbTable,
 *         'messagesDataStore' => MessagesDataStoreMemory,
 * ],
 * 'dataStore' => [
 *     'QueuesDataStoreDbTable' => [
 *         'class' => 'zaboy\async\DataStore\DbTable',
 *         'tableName' => 'test_queues_tablle'
 *     ],
 *     'MessagesDataStoreMemory' => [
 *         'class' => 'zaboy\async\DataStore\Memory'
 *     ],
 * ]
 * </code>
 *
 * @todo config key 'queues'
 * @category   rest
 * @package    zaboy
 */
class QueueClientAbstractFactory extends AbstractFactoryAbstract
{

    const MAX_TIME_IN_FLIGHT = 'maxTimeInFlight';
    const KEY_QUEUE_ADAPTER = 'QueueAdapter';
    const KEY_QUEUE_CLIENT = 'queueClient';

    /**
     *
     * Create and return an instance of the Queue Client.
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
        $serviceConfig = $config[self::KEY_QUEUE_CLIENT][$requestedName];
        $adapterSeviceName = $serviceConfig[self::KEY_QUEUE_ADAPTER];
        $adapter = $container->get($adapterSeviceName);
        if (isset($serviceConfig[self::MAX_TIME_IN_FLIGHT])) {
            $adapter->setMaxTimeInFlight($serviceConfig[self::MAX_TIME_IN_FLIGHT]);
        }
        return new Client($adapter);
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');

        return isset($config[self::KEY_QUEUE_CLIENT][$requestedName][self::KEY_QUEUE_ADAPTER]);
    }

}
