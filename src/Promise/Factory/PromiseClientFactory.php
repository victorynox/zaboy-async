<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\async\Queue\Broker;
use zaboy\scheduler\Callback\CallbackManager;
use zaboy\async\Promise\Exception;

/**
 * Create and return an instance of the Promise Client
 *
 * This Factory depends on Container (which should return an 'config' as array)
 *
 * The configuration can contain:
 * <code>
 * 'promiseClient' =>[
 *      'PromiseAdapterDataStore' => 'PromiseAdapterDefaultDataStore'
 * ],
 * 'dataStore' => [
 *     'PromiseAdapterDefaultDataStore' => [
 *          'class' => 'zaboy\rest\DataStore\DbTable',
 *          'tableName' => 'promise_adapter_default'
 *     ],
 * ]
 * </code>
 *
 * @category   async
 * @package    zaboy
 */
class PromiseClientFactory extends FactoryAbstract
{

    const KEY_PROMISE_CLIENT = 'promiseClient';
    const KEY_PROMISE_ADAPTER_DATA_STORE = 'PromiseAdapterDataStore';

    /**
     * Create and return an instance of the Promise Client.
     *
     * @param  Interop\Container\ContainerInterface $container
     * @return zaboy\async\Queue\Broker
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (!key_exists(self::KEY_PROMISE_CLIENT, $config)) {
            throw new Exception("There is not key 'promiseClient' in  config");
        }
        if (!key_exists(self::KEY_PROMISE_ADAPTER_DATA_STORE, $config[self::KEY_PROMISE_CLIENT])) {
            throw new Exception("There is not key 'promiseClient'/'PromiseAdapterDataStore' in  config");
        }
        $dataStoreServiceName = $config[self::KEY_PROMISE_CLIENT][self::KEY_PROMISE_ADAPTER_DATA_STORE];
        $dataStoreService = $container->get($dataStoreServiceName);

        if ($container->has(CallbackManager::SERVICE_NAME)) {
            /* @var \zaboy\scheduler\Callback\CallbackManager $callbackManager */
            $callbackManager = $container->get(CallbackManager::SERVICE_NAME);
        } else {
            $callbackManager = new CallbackManager($container);
        }
        return new Broker\QueueBroker($callbackManager, $serviceConfig, $queuesClientsInstanses);
    }

}
