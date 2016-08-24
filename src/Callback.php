<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Callback;

use zaboy\async\Callback\CallbackException;
use Interop\Container\ContainerInterface;
use Opis\Closure\SerializableClosure;
use zaboy\async\Callback\Interfaces\ServicesInitableInterface;
use zaboy\async\Promise\Client as PromiseClient;

/**
 * Message
 *
 * @category   async
 * @package    zaboy
 */
class Callback
{

    /**
     *
     * @var ContainerInterface $container;
     */
    static protected $contaner;

    /**
     *
     * @var Callable
     */
    protected $callback;

    public function __construct(Callable $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke($value, PromiseClient $promise = null)
    {
        if (is_callable($this->callback, true)) {
            try {
                $result = call_user_func($this->callback, $value);
            } catch (\Exception $exc) {
                $callbackException = new CallbackException(
                        'Cannot execute Callback. Reason: ' . $exc->getMessage(), 0, $exc
                );
            }
        } else {
            $callbackException = new CallbackException(
                    'There was not correct instance callable in Callback'
            );
        }
        if (is_null($promise)) {
            if (isset($result)) {
                return $result;
            } else {
                throw $callbackException;
            }
        } else {
            if (isset($result)) {
                $promise->resolve($result);
            } else {
                $promise->reject($callbackException);
            }
        }
    }

    static protected function getContaner()
    {
        if (isset(static::$contaner)) {
            return static::$contaner;
        } else {
            throw new CallbackException(
            'Add "Callback::setContaner($contaner);" after ' .
            '"$container = include \'config/container.php\';"'
            );
        }
    }

    static public function setContaner(ContainerInterface $contaner)
    {
        static::$contaner = $contaner;
    }

    public function __sleep()
    {
        if ($this->callback instanceof \Closure) {
            $this->callback = new SerializableClosure($this->callback);
        }
        return array('callback');
    }

    public function __wakeup()
    {
        if ($this->callback instanceof ServicesInitableInterface) {

            $servicesList = $this->callback->getServicesList();
            $services = $this->getServices($servicesList);
            $this->callback->setServices($services);
        }
    }

    protected function getServices($servicesList)
    {
        $services = [];
        foreach ($servicesList as $propertyName => $serviceName) {
            if (is_array($serviceName)) {
                $services[$propertyName] = $this->getServices($serviceName);
            } else {
                $services[$propertyName] = $this->getContaner()->get($serviceName);
            }
        }
        return $services;
    }

    public static function __callStatic($serviceName, $arguments)
    {
        $callable = static::getContaner()->get($serviceName);
        $callback = new static($callable);
        $value = $arguments[0];
        $promise = isset($arguments[1]) ? $arguments[1] : null;
        return $callback($value, $promise);
    }

}
