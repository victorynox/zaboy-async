<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Callback;

use zaboy\async\Callback\Callback;
use zaboy\async\Callback\CallbackException;
use Interop\Container\ContainerInterface;
use Opis\Closure\SerializableClosure;
use zaboy\async\Callback\Interfaces\ServicesInitableInterface;
use zaboy\async\Promise\Client as PromiseClient;
use zaboy\async\Promise\Factory\StoreFactory as PromiseStoreFactory;
use zaboy\async\Callback\Interfaces\InterrupterInterface;

/**
 * AsyncCallback
 *
 * @category   async
 * @package    zaboy
 */
class AsyncCallback extends Callback
{

    /**
     *
     * @var InterrupterInterface
     */
    protected $interrupter;

    /**
     *
     * @param \zaboy\async\Callback\Callable $callback
     * @param PromiseClient|string|null $interrupter  it may be promiseId
     */
    public function __construct(Callable $callback, InterrupterInterface $interrupter = null)
    {
        $this->callback = $callback;
        $this->interrupter = $interrupter;
    }

    public function __invoke($value, PromiseClient $promise = null)
    {

        if (is_null($promise)) {
            $promiseStore = $this->getContaner()->get(PromiseStoreFactory::KEY);
            $promise = new PromiseClient($promiseStore);
        }
        if (is_callable($this->callback, true)) {
            try {
                if (isset($this->interrupter)) {
                    call_user_func([$this->interrupter, 'interrupt'], $value, $promise, $this->callback);
                    return $promise;
                } else {
                    return call_user_func($this->callback, $value, $promise);
                }
            } catch (\Exception $exc) {
                throw new CallbackException(
                'Cannot execute Callback. Reason: ' . $exc->getMessage(), 0, $exc
                );
            }
        } else {
            throw new CallbackException(
            'There was not correct instance callable in Callback'
            );
        }
    }

}
