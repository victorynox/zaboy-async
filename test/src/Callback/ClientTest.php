<?php

namespace zaboy\test\async\Callback;

use Interop\Container\ContainerInterface;
use zaboy\rest\TableGateway\TableManagerMysql;
use zaboy\async\Message\Store;
use zaboy\async\Message\Client;
use zaboy\async\Message\Factory\StoreFactory;
use zaboy\async\Queue\Store as QueueStore;
use zaboy\async\Queue\Client as QueueClient;
use zaboy\async\Queue\Factory\StoreFactory as QueueStoreFactory;
use zaboy\async\Callback\Callback;
use zaboy\test\async\Callback\Example\JustCallable;

class CallbackTest extends \PHPUnit_Framework_TestCase
{

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
        Callback::setContaner($this->container);
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
        $callable = function($value) {
            return "Hello $value";
        };
        $this->object = new Callback($callable);
        $this->assertEquals(
                'Hello CallbackTest__Invoke', call_user_func($this->object, 'CallbackTest__Invoke')
        );
    }

    public function test_afetrSerialize__Closure()
    {
        $callable = function($value) {
            return "Hello $value";
        };
        $this->object = unserialize(serialize(new Callback($callable)));
        $this->assertEquals(
                'Hello CallbackTest__Invoke', call_user_func($this->object, 'CallbackTest__Invoke')
        );
    }

}
