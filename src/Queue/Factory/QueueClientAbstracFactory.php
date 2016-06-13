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
class QueueClientAbstracFactory extends AbstractFactoryAbstract
{

    const DEFAULT_MESSAGES_DATA_STORE = 'MessagesDataStore';
    const DEFAULT_QUEUES_DATA_STORE = 'QueuesDataStore';
    const MAX_TIME_IN_FLIGHT = 'maxTimeInFlight';

    /**
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
        $serviceConfig = $config['queues'][$requestedName];
        $requestedClassName = $serviceConfig['class'];
        $queuesDataStore = $container->get($serviceConfig['queuesDataStore']);
        $messagesDataStore = $container->get($serviceConfig['messagesDataStore']);
        if (empty($queuesDataStore) || empty($messagesDataStore)) {
            throw new QueueException('Can not load queuesDataStore or messagesDataStore');
        }
        $adapter = new Adapter\DataStores($queuesDataStore, $messagesDataStore);
        if (isset($serviceConfig[self::MAX_TIME_IN_FLIGHT])) {
            $adapter->setMaxTimeInFlight($serviceConfig[self::MAX_TIME_IN_FLIGHT]);
        }
        return new $requestedClassName($adapter);
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        if (!isset($config['queues'][$requestedName]['class'])) {
            return false;
        }
        $requestedClassName = $config['queues'][$requestedName]['class'];
        return is_a($requestedClassName, 'zaboy\async\Queue\Client', true);
    }

}
