<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Callback\Interrupter\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\async\Promise\CrudMiddleware;
use zaboy\async\Callback\CallbackException;
use zaboy\async\Promise\Store;
use zaboy\async\Promise\Factory\StoreFactory;
use zaboy\async\Callback\Interrupter\ViaHttpMiddleware;

class ViaHttpMiddlewareFactory extends FactoryAbstract
{

    const KEY = '#ViaHttp Middleware';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var $store Store */
        $store = $container->get(StoreFactory::KEY);
        if (null !== $store) {
            $viaHttpMiddleware = new ViaHttpMiddleware($store);
        } else {
            throw new CallbackException(
            'Can\'t create StoreFactory as service with name: ' . StoreFactory::KEY
            );
        }

        return $viaHttpMiddleware;
    }

}
