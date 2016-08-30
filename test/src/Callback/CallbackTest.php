<?php

namespace zaboy\test\async\Callback;

use Interop\Container\ContainerInterface;
use zaboy\async\Callback\Callback;
use zaboy\async\Message\Client;
use zaboy\async\Promise\Factory\StoreFactory;
use zaboy\async\Promise\Store as PromiseStore;
use zaboy\test\async\Callback\Example\CallableWithDb;
use zaboy\test\async\Callback\Example\CallableWithObjectWitb;
use zaboy\test\async\Callback\Example\JustCallable;

class CallbackTest extends \PHPUnit_Framework_TestCase
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

    public function test_befaroSerialize__Invoke()
    {
        $callable = new JustCallable();
        $this->object = new Callback($callable);
        $this->assertEquals(
            'JustCallable resolve CallbackTest__Invoke', call_user_func($this->object, 'CallbackTest__Invoke')
        );
    }

    public function test_afetrSerialize__Invoke()
    {
        $callable = new JustCallable();
        $this->object = unserialize(serialize(new Callback($callable)));

        $this->assertEquals(
            'JustCallable resolve CallbackTest__Invoke', call_user_func($this->object, 'CallbackTest__Invoke')
        );
    }

    /* ---------------------------------------------------------------------------------- */

    public function test_befaroSerialize__Call()
    {
        $callable = new JustCallable();
        $this->object = new Callback([$callable, 'call']);
        $this->assertEquals(
            'JustCallable::call resolve CallbackTest__Invoke', call_user_func($this->object, 'CallbackTest__Invoke')
        );
    }

    public function test_afetrSerialize__Call()
    {
        $callable = new JustCallable();
        $this->object = unserialize(serialize(new Callback([$callable, 'call'])));

        $this->assertEquals(
            'JustCallable::call resolve CallbackTest__Invoke', call_user_func($this->object, 'CallbackTest__Invoke')
        );
    }

    public function test_befaroSerialize__ExceptionWrong_CallBack()
    {
        $callable = new JustCallable();
        $this->object = new Callback([$callable, 'throwException']);
        $this->setExpectedExceptionRegExp('\zaboy\async\Callback\CallbackException');
        call_user_func($this->object, 'test_CallbackTest__ExceptionWrong_CallBack');
    }

    public function test_afetrSerialize__ExceptionWrong_CallBack()
    {
        $callable = new JustCallable();
        $this->object = unserialize(serialize(new Callback([$callable, 'throwException'])));
        $this->setExpectedExceptionRegExp('\zaboy\async\Callback\CallbackException');
        call_user_func($this->object, 'test_CallbackTest__ExceptionWrong_CallBack');
    }

    public function test_befaroSerialize__Closure()
    {
        $callable = function ($value) {
            return "Hello $value";
        };
        $this->object = new Callback($callable);
        $this->assertEquals(
            'Hello CallbackTest__Invoke', call_user_func($this->object, 'CallbackTest__Invoke')
        );
    }

    public function test_afetrSerialize__Closure()
    {
        $callable = function ($value) {
            return "Hello $value";
        };
        $this->object = unserialize(serialize(new Callback($callable)));
        $this->assertEquals(
            'Hello CallbackTest__Invoke', call_user_func($this->object, 'CallbackTest__Invoke')
        );
    }

    public function test_befaroSerialize__InvokeForServicesInitableInterface()
    {
        $callable = new CallableWithDb($this->container->get('db'));
        $this->object = new Callback($callable);
        $this->assertEquals(
            'Platform name MySQL', call_user_func($this->object, '')
        );
    }

    public function test_afetrSerialize__InvokeForServicesInitableInterface()
    {
        $callable = new CallableWithDb($this->container->get('db'));
        $this->object = new Callback($callable);
        $this->object = unserialize(serialize(new Callback($callable)));

        $this->assertEquals(
            'Platform name MySQL', call_user_func($this->object, '')
        );
    }

    public function test_befaroSerialize__InvokeForServicesContanedObjectInitableInterface()
    {
        $callable = new CallableWithObjectWitb($this->container->get('db'));
        $this->object = new Callback($callable);
        $this->assertEquals(
            'Platform name MySQL', call_user_func($this->object, '')
        );
    }

    public function test_afetrSerialize__InvokeForServicesContanedObjectInitableInterface()
    {
        $callable = new CallableWithObjectWitb($this->container->get('db'));
        $this->object = new Callback($callable);
        $this->object = unserialize(serialize(new Callback($callable)));
        $this->assertEquals(
            'Platform name MySQL', call_user_func($this->object, '')
        );
    }

    public function test_befaroSerialize__InvokeCallableServiceAsStatic()
    {
        $this->object = [Callback::class, 'CallableService'];
        $this->assertEquals(
            'CallableService resolve resalt', call_user_func($this->object, 'resalt')
        );
    }

    public function test_afetrSerialize__InvokeCallableServiceAsStatic()
    {
        $this->object = unserialize(serialize([Callback::class, 'CallableService']));
        $this->assertEquals(
            'CallableService resolve resalt', call_user_func($this->object, 'resalt')
        );
    }

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

        Callback::setContaner($this->container);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

}
