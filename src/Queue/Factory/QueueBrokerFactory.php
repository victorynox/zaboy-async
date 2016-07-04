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
use zaboy\async\Queue\Broker;
use zaboy\scheduler\Callback\CallbackManager;

/**
 * Creates and returns an instance of the Queue Broker
 *
 * This Factory depends on Container (which should return a 'config' as array)
 *
 * The configuration can contain:
 * <code>
 * 'queueBroker' =>[
 *      'ManagedQueueClient' => [
 *          'ManagedQueue1' => [
 *              'workerName' => 'someCallBack'
 *          ]
 *          'ManagedQueue2' => [
 *              'workerName' => 'anotherCallBack'
 *          ]
 *      ],
 *      'nextManagedQueueClient' => [
 *          'nextManagedQueue1' => [...
 * ]
 * </code>
 *
 * @category   async
 * @package    zaboy
 */
class QueueBrokerFactory extends FactoryAbstract
{

    const KEY_QUEUE_BROKER = 'queueBroker';

    /**
     * Create and return an instance of the QueueBroker.
     *
     * @param  \Interop\Container\ContainerInterface $container
     * @return \zaboy\async\Queue\Broker
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $serviceConfig = $config[self::KEY_QUEUE_BROKER];
        $queueClientsNames = array_keys($serviceConfig);
        $queuesClientsInstanses = [];
        foreach ($queueClientsNames as $queueClientName) {
            $queuesClientsInstanses[$queueClientName] = $container->get($queueClientName);
        }
        if ($container->has(CallbackManager::SERVICE_NAME)) {
            /* @var \zaboy\scheduler\Callback\CallbackManager $callbackManager */
            $callbackManager = $container->get(CallbackManager::SERVICE_NAME);
        } else {
            $callbackManager = new CallbackManager($container);
        }
        return new Broker\QueueBroker($callbackManager, $serviceConfig, $queuesClientsInstanses);
    }

}
