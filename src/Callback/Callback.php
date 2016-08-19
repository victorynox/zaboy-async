<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Callback;

use zaboy\async\Callback\CallbackException;
use zaboy\async\Message\Exception\TimeIsOutException;
use zaboy\async\Message\Message\PendingMessage;
use zaboy\async\Message\Message\Message;
use zaboy\async\Message\Interfaces\MessageInterface;
use zaboy\async\Message\Store;
use zaboy\async\ClientAbstract;
use zaboy\async\Message\Interfaces\ClientInterface;
use Zend\Db\Sql\Select;
use zaboy\async\Queue;
use zaboy\async\Promise\Store as PromiseStore;
use zaboy\async\Promise\Client as PromiseClient;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;
use Opis\Closure\SerializableClosure;

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

    public function __invoke($value)
    {
        if (is_callable($this->callback, true)) {
            try {
                return call_user_func($this->callback, $value);
            } catch (\Exception $exc) {
                throw new CallbackException(
                'Cannot execute Callback', 0, $exc
                );
            }
        } else {
            throw new CallbackException(
            'There was not correct instance callable in Callback'
            );
        }
    }

    protected function getContaner()
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

    }

}
