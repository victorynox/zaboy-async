<?php

namespace zaboy\test\async\Callback\Interrupter;

use Interop\Container\ContainerInterface;
use zaboy\async\Message\Client;
use zaboy\async\Callback\AsyncCallback;
use zaboy\test\async\Callback\Example\JustCallable;
use zaboy\async\Callback\Interrupter\ViaHttp;
use zaboy\async\Callback\Interrupter\Factory\ViaHttpMiddlewareFactory;
use zaboy\async\Promise\Client as PromiseClient;
use zaboy\async\Promise\Store as PromiseStore;
use zaboy\async\Promise\Factory\StoreFactory;
use zaboy\async\Promise\Exception\RejectedException;

//
class ViaHttpTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var PromiseStore
     */
    protected $promiseStore;

    /**
     * @var ViaHttp
     */
    protected $object;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed
     */
    protected function setUp()
    {
        global $testCase;
        $testCase = 'table_for_test';
        // var_dump(fopen('./config/container.php', 'r'));
        $this->container = include './config/container.php';

        $this->promiseStore = AsyncCallback::setContaner($this->container);

        $this->object = new ViaHttp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /* ---------------------------------------------------------------------------------- */

    public function test_befaroSerialize__Invoke()
    {

        $callable = new JustCallable();

        $callback = new AsyncCallback([$callable, 'callReturnPromise'], $this->object); //[$callable, 'callReturnPromise']

        $promise = call_user_func($callback, 'CallbackTest__Invoke');
        var_dump($promise);
        sleep(2);
//$promise->wait(false);
//        $this->assertEquals(
//                'JustCallable::call resolve CallbackTest__Invoke', call_user_func($this->object, 'CallbackTest__Invoke')->wait(false)
//        );
    }

}
