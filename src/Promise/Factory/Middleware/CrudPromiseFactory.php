<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Factory\Middleware;

use Interop\Container\ContainerInterface;
use zaboy\rest\DataStore\DataStoreException;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use Zend\Db\TableGateway\TableGateway;
use zaboy\rest\FactoryAbstract;
use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Store;
use zaboy\async\Promise\Factory\StoreFactory;
use zaboy\async\Promise\Promise;
use zaboy\async\Promise\Middleware\CrudPromise;

/**
 * Create and return an instance of the DataStore which based on DbTable
 *
 * This Factory depends on Container (which should return an 'config' as array)
 *
 * The configuration can contain:
 * <code>
 *    'db' => [
 *        'driver' => 'Pdo_Mysql',
 *        'host' => 'localhost',
 *        'database' => '',
 *    ]
 * 'DataStore' => [
 *
 *     'DbTable' => [
 *         'class' => 'mydatabase',
 *         'tableName' => 'mytableName',
 *         'dbAdapter' => 'db' // Service Name. 'db' by default
 *     ]
 * ]
 * </code>
 *
 * @uses zend-db
 * @see https://github.com/zendframework/zend-db
 * @category   rest
 * @package    zaboy
 */
class CrudPromiseFactory extends FactoryAbstract
{

    const KEY_MIDDLEWARE_CRUD_PROMISE = 'MiddlewareCrudPromise';

    /**
     * Create and return an instance of the DataStore.
     *
     * 'use Zend\ServiceManager\AbstractFactoryInterface;' for V2 to
     * 'use Zend\ServiceManager\Factory\AbstractFactoryInterface;' for V3
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  array $options
     * @return DataStoresInterface
     * @throws DataStoreException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $store = $container->get(StoreFactory::KEY);

        if (null !== $store) {
            $middlewareCrudPromise = new CrudPromise($store);
        } else {
            throw new DataStoreException(
            'Can\'t create StoreFactory as service with name: ' . StoreFactory::KEY
            );
        }


        return $middlewareCrudPromise;
    }

}
