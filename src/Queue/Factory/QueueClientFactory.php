<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Queue\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\async\Queue;

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
class QueueClientFactory extends FactoryAbstract
{

    const DEFAULT_MAX_TIME_IN_FLIGHT = 300;

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container)
    {
        $defaultQueueAdapter = $container->get('defaultQueueAdapter');
        $defaultQueueAdapter->setMaxTimeInFlight(self::DEFAULT_MAX_TIME_IN_FLIGHT);

        return new Queue\Client($defaultQueueAdapter);
    }

}
