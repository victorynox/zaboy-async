<?php

namespace zaboy\test\async\Callback;

use Interop\Container\ContainerInterface;
use zaboy\async\Message\Client;
use zaboy\async\Callback\AsyncCallback;
use zaboy\test\async\Callback\Example\JustCallable;
use zaboy\test\async\Callback\Example\CallableWithDb;
use zaboy\test\async\Callback\Example\CallableWithObjectWitb;
use zaboy\async\Promise\Client as PromiseClient;
use zaboy\async\Promise\Store as PromiseStore;
use zaboy\async\Promise\Factory\StoreFactory;
use zaboy\async\Promise\Exception\RejectedException;

class AsyncCallbackTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var PromiseStore
     */
    protected $promiseStore;

    /**
     * @var Client
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

        $this->container = include './config/container.php';

        $this->promiseStore = $this->container->get(StoreFactory::KEY);

        AsyncCallback::setContaner($this->container);
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
        $this->object = new AsyncCallback([$callable, 'callReturnPromise']);
        $this->assertEquals(
                'JustCallable::callReturnPromise resolve CallbackTest__Invoke', call_user_func($this->object, 'CallbackTest__Invoke')->wait(false)
        );
    }

    public function test_afetrSerialize__Invoke()
    {
        $callable = new JustCallable();
        $this->object = unserialize(serialize(new AsyncCallback([$callable, 'callReturnPromise'])));

        $this->assertEquals(
                'JustCallable::callReturnPromise resolve CallbackTest__Invoke', call_user_func($this->object, 'CallbackTest__Invoke')->wait(false)
        );
    }

    public function test_befaroSerialize__InvokeWithPromise()
    {
        $callable = new JustCallable();
        $this->object = new AsyncCallback([$callable, 'callReturnPromise']);
        $promise = new PromiseClient($this->promiseStore);
        $result = call_user_func($this->object, 'CallbackTest__Invoke', $promise);
        $this->assertEquals(
                $promise->getId(), $result->getId()
        );
        $this->assertEquals(
                'JustCallable::callReturnPromise resolve CallbackTest__Invoke', $result->wait(false)
        );
    }

    public function test_afetrSerialize__InvokeWithPromise()
    {
        $callable = new JustCallable();
        $this->object = unserialize(serialize(new AsyncCallback([$callable, 'callReturnPromise'])));
        $promise = new PromiseClient($this->promiseStore);
        $result = call_user_func($this->object, 'CallbackTest__Invoke', $promise)->wait(false);
        $this->assertEquals(
                'JustCallable::callReturnPromise resolve CallbackTest__Invoke', $result
        );
    }

    public function test_befaroSerialize__ExceptionWrong_CallBack()
    {
        $callable = new JustCallable();
        $this->object = new AsyncCallback([$callable, 'ReturnPromiseException']);
        $promise = call_user_func($this->object, 'test_CallbackTest__ExceptionWrong_CallBack');
        $this->setExpectedExceptionRegExp('\zaboy\async\Callback\CallbackException');
        throw $promise->wait(false)->getPrevious();
    }

//
//    public function test_afetrSerialize__ExceptionWrong_CallBack()
//    {
//        $callable = new JustCallable();
//        $this->object = unserialize(serialize(new AsyncCallback([$callable, 'ReturnPromiseException'])));
//        $this->setExpectedExceptionRegExp('\zaboy\async\Promise\Exception\RejectedException');
//        call_user_func($this->object, 'test_CallbackTest__ExceptionWrong_CallBack')->wait();
//    }
//
//    public function test_befaroSerialize__Closure()
//    {
//        $callable = function($value) {
//            return "Hello $value";
//        };
//        $this->object = new Callback($callable);
//        $this->assertEquals(
//                'Hello CallbackTest__Invoke', call_user_func($this->object, 'CallbackTest__Invoke')
//        );
//    }
//
//    public function test_afetrSerialize__Closure()
//    {
//        $callable = function($value) {
//            return "Hello $value";
//        };
//        $this->object = unserialize(serialize(new Callback($callable)));
//        $this->assertEquals(
//                'Hello CallbackTest__Invoke', call_user_func($this->object, 'CallbackTest__Invoke')
//        );
//    }
//
//    public function test_befaroSerialize__InvokeForServicesInitableInterface()
//    {
//        $callable = new CallableWithDb($this->container->get('db'));
//        $this->object = new Callback($callable);
//        $this->assertEquals(
//                'Platform name MySQL', call_user_func($this->object, '')
//        );
//    }
//
//    public function test_afetrSerialize__InvokeForServicesInitableInterface()
//    {
//        $callable = new CallableWithDb($this->container->get('db'));
//        $this->object = new Callback($callable);
//        $this->object = unserialize(serialize(new Callback($callable)));
//
//        $this->assertEquals(
//                'Platform name MySQL', call_user_func($this->object, '')
//        );
//    }
//
//    public function test_befaroSerialize__InvokeForServicesContanedObjectInitableInterface()
//    {
//        $callable = new CallableWithObjectWitb($this->container->get('db'));
//        $this->object = new Callback($callable);
//        $this->assertEquals(
//                'Platform name MySQL', call_user_func($this->object, '')
//        );
//    }
//
//    public function test_afetrSerialize__InvokeForServicesContanedObjectInitableInterface()
//    {
//        $callable = new CallableWithObjectWitb($this->container->get('db'));
//        $this->object = new Callback($callable);
//        $this->object = unserialize(serialize(new Callback($callable)));
//        $this->assertEquals(
//                'Platform name MySQL', call_user_func($this->object, '')
//        );
//    }
//
//    public function test_befaroSerialize__InvokeCallableServiceAsStatic()
//    {
//        $this->object = [Callback::class, 'CallableService'];
//        $this->assertEquals(
//                'CallableService resolve resalt', call_user_func($this->object, 'resalt')
//        );
//    }
//
//    public function test_afetrSerialize__InvokeCallableServiceAsStatic()
//    {
//        $this->object = unserialize(serialize([Callback::class, 'CallableService']));
//        $this->assertEquals(
//                'CallableService resolve resalt', call_user_func($this->object, 'resalt')
//        );
//    }
//
////---------------------- promise ------------------------------------
//
//    public function test_WithPromise_befaroSerialize__Call()
//    {
//        $promise = new PromiseClient($this->promiseStore);
//        $callable = new JustCallable();
//        $this->object = new Callback([$callable, 'call']);
//        call_user_func($this->object, 'CallbackTest__Invoke', $promise);
//        $this->assertEquals(
//                'JustCallable::call resolve CallbackTest__Invoke', $promise->wait(false)
//        );
//    }
//
//    public function test_WithPromise_afetrSerialize__Call()
//    {
//        $promise = new PromiseClient($this->promiseStore);
//        $callable = new JustCallable();
//        $this->object = unserialize(serialize(new Callback([$callable, 'call'])));
//        call_user_func($this->object, 'CallbackTest__Invoke', $promise);
//        $this->assertEquals(
//                'JustCallable::call resolve CallbackTest__Invoke', $promise->wait(false)
//        );
//    }
//
//    public function test_WithPromise_befaroSerialize__ExceptionWrong_CallBack()
//    {
//        $promise = new PromiseClient($this->promiseStore);
//        $callable = new JustCallable();
//        $this->object = new Callback([$callable, 'throwException'], $promise);
//        call_user_func($this->object, 'test_CallbackTest__ExceptionWrong_CallBack', $promise);
//        $this->setExpectedExceptionRegExp('zaboy\async\Promise\Exception\RejectedException');
//        $promise->wait(true);
//    }
//
//    public function test_WithPromise_afetrSerialize__ExceptionWrong_CallBack()
//    {
//        $promise = new PromiseClient($this->promiseStore);
//        $callable = new JustCallable();
//        $this->object = unserialize(serialize(new Callback([$callable, 'throwException'], $promise)));
//        call_user_func($this->object, 'test_CallbackTest__ExceptionWrong_CallBack', $promise);
//        $this->setExpectedExceptionRegExp('zaboy\async\Promise\Exception\RejectedException');
//        $promise->wait(true);
//    }
}
