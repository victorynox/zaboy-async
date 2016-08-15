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
use zaboy\async\Promise\CrudMiddleware;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Store;

class CrudMiddlewareFactory extends FactoryAbstract
{

    const KEY = '#Crud Middleware';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var $store Store */
        $store = $container->get(StoreFactory::KEY);
        if (null !== $store) {
            $crudMiddleware = new CrudMiddleware($store);
        } else {
            throw new PromiseException(
                'Can\'t create StoreFactory as service with name: ' . StoreFactory::KEY
            );
        }
        return $crudMiddleware;
    }

}
