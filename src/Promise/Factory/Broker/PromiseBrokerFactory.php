<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Factory\Broker;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\async\Promise\Broker\PromiseBroker;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter;
use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;
use zaboy\async\Promise\Exception;

/**
 * Create and return an instance of the Promise Broker
 *
 * This Factory depends on Container (which should return an 'config' as array)
 *
 * The configuration can contain:
 * <code>
 * 'services' => [
 *      'factories' => [
 *          'PromiseBroker' => 'zaboy\async\Promise\Factory\PromiseBrokerFactory'
 *      ]
 * ]
 * </code>
 *
 * @category   async
 * @package    zaboy
 */
class PromiseBrokerFactory extends FactoryAbstract
{

    const KEY_PROMISE_BROKER = 'PromiseBroker';

    /**
     * Create and return an instance of the Promise Broker.
     *
     * @param  Interop\Container\ContainerInterface $container
     * @return zaboy\async\Promise\PromiseBroker
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (isset($options[MySqlAdapterFactory::KEY_PROMISE_TABLE_NAME])) {
            //for tests
            $tableName = $options[MySqlAdapterFactory::KEY_PROMISE_TABLE_NAME];
            $mySqlAdapterFactory = new MySqlAdapterFactory();

            $mySqlAdapter = $mySqlAdapterFactory->__invoke($container, '', [MySqlAdapterFactory::KEY_PROMISE_TABLE_NAME => $tableName]);
        } else {
            // $tableName by default
            if (!$container->has(MySqlAdapterFactory::KEY_PROMISE_ADAPTER)) {
                throw new Exception(
                'Can\'t create MySqlAdapter for Promise witout MySqlAdapterFactory'
                );
            }
            /* @var $mySqlAdapter MySqlPromiseAdapter */
            $mySqlAdapter = $container->get(MySqlAdapterFactory::KEY_PROMISE_ADAPTER); //'mySqlPromiseAdapter'
        }


        return new PromiseBroker($mySqlAdapter);
    }

}
