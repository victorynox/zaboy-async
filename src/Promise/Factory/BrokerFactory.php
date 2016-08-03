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
use zaboy\async\Promise\Broker;
use zaboy\async\Promise\Store;
use zaboy\async\Promise\Factory\StoreFactory;
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
 *          'Broker' => 'zaboy\async\Promise\Factory\BrokerFactory'
 *      ],
 * '#Promise Broker' => [
 *      #Life Time => 600
 * ]
 * </code>
 *
 * @category   async
 * @package    zaboy
 */
class BrokerFactory extends FactoryAbstract
{

    const KEY = '#Promise Broker';

    /**
     * max time Promise in Store  (in sec)
     */
    const KEY_LIFE_TIME = '#Life Time';

    /**
     * Create and return an instance of the Promise Broker.
     *
     * @param  Interop\Container\ContainerInterface $container
     * @return zaboy\async\Promise\Broker
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        $lifeTime = isset($config[self::KEY][self::KEY_LIFE_TIME]) ?
                $config[self::KEY][self::KEY_LIFE_TIME] : null
        ;

        // $tableName by default
        if (!$container->has(StoreFactory::KEY)) {
            throw new Exception(
            'Can\'t create Store for Promise witout StoreFactory'
            );
        }
        /* @var $store Store */
        $store = $container->get(StoreFactory::KEY); //'mySqlPromiseAdapter'

        return new Broker($store);
    }

}
