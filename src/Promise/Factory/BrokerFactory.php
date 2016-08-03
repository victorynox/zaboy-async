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
use zaboy\async\Promise\Storer;
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
 *      ]
 * ]
 * </code>
 *
 * @category   async
 * @package    zaboy
 */
class BrokerFactory extends FactoryAbstract
{

    const KEY = '#Promise Broker';
    const KEY_TIME_LIFE = '#Time Life';

    /**
     * Create and return an instance of the Promise Broker.
     *
     * @param  Interop\Container\ContainerInterface $container
     * @return zaboy\async\Promise\Broker
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        $this->tableName = isset($config[self::KEY][self::KEY_TIME_LIFE]) ?
                $config[self::KEY][self::KEY_TIME_LIFE] : null
        ;

        // $tableName by default
        if (!$container->has(StoreFactory::KEY)) {
            throw new Exception(
            'Can\'t create Store for Promise witout StoreFactory'
            );
        }
        /* @var $store Storer */
        $store = $container->get(StoreFactory::KEY); //'mySqlPromiseAdapter'

        return new Broker($store);
    }

}
