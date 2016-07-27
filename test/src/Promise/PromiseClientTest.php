<?php

namespace zaboy\test\async\Promise;

use zaboy\async\Promise\PromiseClient;
use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;
use Interop\Container\ContainerInterface;
use zaboy\rest\TableGateway\TableManagerMysql;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter;
use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\async\Promise\Determined\Exception\RejectedException;

class PromiseClientTest extends \PHPUnit_Framework_TestCase
{

    const TEST_TABLE_NAME = 'test_mysqlpromisebroker';

    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;

    /**
     *
     * @var MySqlPromiseAdapter
     */
    protected $mySqlPromiseAdapter;

    /**
     * @var PromiseClient
     */
    protected $object;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->container = include './config/container.php';
        $this->adapter = $this->container->get('db');
        $mySqlAdapterFactory = new MySqlAdapterFactory();
        $this->mySqlPromiseAdapter = $mySqlAdapterFactory->__invoke(
                $this->container
                , ''
                , [MySqlAdapterFactory::KEY_PROMISE_TABLE_NAME => self::TEST_TABLE_NAME]
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $tableManagerMysql = new TableManagerMysql($this->adapter);
        $tableManagerMysql->deleteTable(self::TEST_TABLE_NAME);
    }

    public static function callback($value)
    {
        return $value . ' after callbak';
    }

    /* ---------------------------------------------------------------------------------- */

    public function testPromiseTest__makePromise()
    {
        $this->object = new PromiseClient($this->mySqlPromiseAdapter);
        $this->assertInstanceOf(
                'zaboy\async\Promise\PromiseClient', $this->object
        );
        $this->assertSame(
                $this->object->getState(), PromiseInterface::PENDING
        );
    }

    public function testPromiseTest__resolve()
    {
        $this->object = new PromiseClient($this->mySqlPromiseAdapter);
        $this->object->resolve(1);
        $this->assertSame(
                $this->object->getState(), PromiseInterface::FULFILLED
        );
        $this->assertSame(
                1, $this->object->wait(false)
        );
    }

    public function testPromiseTest__resolveWithObjectAsResult()
    {
        $this->object = new PromiseClient($this->mySqlPromiseAdapter);
        $object = new \stdClass();
        $this->object->resolve($object);
        $this->assertEquals(
                $this->object->wait(), $object
        );
    }

    public function testPromiseTest__reject()
    {
        $this->object = new PromiseClient($this->mySqlPromiseAdapter);
        $this->object->reject('reason');
        $this->assertSame(
                PromiseInterface::REJECTED, $this->object->getState()
        );
        $this->assertInstanceOf(
                'zaboy\async\Promise\Determined\Exception\RejectedException', $this->object->wait(false)
        );
        $this->assertNull(
                $this->object->wait(false)->getPrevious()
        );
        $this->assertSame(
                'reason', $this->object->wait(false)->getMessage()
        );
    }

    public function testPromiseTest__rejectWithObjectAsReason()
    {
        $this->object = new PromiseClient($this->mySqlPromiseAdapter);
        $object = new \stdClass();
        $this->object->reject(new \stdClass());
        $this->assertSame(
                $this->object->getState(), PromiseInterface::REJECTED
        );
        $this->assertInstanceOf(
                'zaboy\async\Promise\Determined\Exception\RejectedException', $this->object->wait(false)
        );
        $this->assertNull(
                $this->object->wait(false)->getPrevious()
        );
        $this->assertEquals(
                'Reason can not be converted to string.', $this->object->wait(false)->getMessage()
        );
    }

    /*     * ************* Wait() with $unwrap = true ******************************* */

    public function testPromiseTest__PendingWait()
    {
        $this->object = new PromiseClient($this->mySqlPromiseAdapter);
        $this->setExpectedException('\zaboy\async\Promise\Pending\TimeIsOutException');
        $this->object->wait(true, 0);
    }

    public function testPromiseTest__RejectedWait()
    {
        $this->object = new PromiseClient($this->mySqlPromiseAdapter);
        $this->object->reject('reason');
        $this->setExpectedException('\zaboy\async\Promise\Determined\Exception\RejectedException');
        $this->object->wait(true, 0);
    }

    public function testPromiseTest__PendingAfterPendingWait()
    {
        $this->object = new PromiseClient($this->mySqlPromiseAdapter);
        $result = new PromiseClient($this->mySqlPromiseAdapter);
        $this->object->resolve($result);
        $this->setExpectedException('\zaboy\async\Promise\Pending\TimeIsOutException');
        $this->object->wait(true, 1);
    }

    public function testPromiseTest__PendingAfterFulfilledWait()
    {
        $this->object = new PromiseClient($this->mySqlPromiseAdapter);
        $result = new PromiseClient($this->mySqlPromiseAdapter);
        $this->object->resolve($result);
        $result->resolve('result');
        $this->assertEquals(
                $this->object->wait(), 'result'
        );
        $this->assertEquals(
                $this->object->getState(), PromiseInterface::FULFILLED
        );
    }

    public function testPromiseTest__PendingAfterRejecteddWait()
    {
        $this->object = new PromiseClient($this->mySqlPromiseAdapter);
        $result = new PromiseClient($this->mySqlPromiseAdapter);
        $this->object->reject($result);
        $this->setExpectedException('\zaboy\async\Promise\Determined\Exception\ReasonPendingException');
        //time is out
        $this->object->wait(true, 0);
    }

    /*     * ************* Wait() with $unwrap = false ******************************* */

    public function testPromiseTest__PendingWaitUnwrapFalse()
    {
        $this->object = new PromiseClient($this->mySqlPromiseAdapter);
        $this->assertEquals(
                $this->object->wait(false)->getState(), PromiseInterface::PENDING
        );
    }

    public function testPromiseTest__RejectedWaitUnwrapFalse()
    {
        $this->object = new PromiseClient($this->mySqlPromiseAdapter);
        $this->object->reject('reason');
        $this->assertInstanceOf(
                'zaboy\async\Promise\Determined\Exception\RejectedException', $this->object->wait(false)
        );
    }

    public function testPromiseTest__PendingAfterPendingWaitUnwrapFalse()
    {
        $this->object = new PromiseClient($this->mySqlPromiseAdapter);
        $result = new PromiseClient($this->mySqlPromiseAdapter);
        $this->object->resolve($result);
        $this->assertEquals(
                $this->object->wait(false)->getState(), PromiseInterface::PENDING
        );
    }

    public function testPromiseTest__PendingAfterFulfilledWaitUnwrapFalse()
    {
        $this->object = new PromiseClient($this->mySqlPromiseAdapter);
        $result = new PromiseClient($this->mySqlPromiseAdapter);
        $this->object->resolve($result);
        $result->resolve('result');
        $this->assertEquals(
                $this->object->wait(false), 'result'
        );
    }

    public function testPromiseTest__PendingAfterRejecteddWaitUnwrapFalse()
    {
        $this->object = new PromiseClient($this->mySqlPromiseAdapter);
        $result = new PromiseClient($this->mySqlPromiseAdapter);
        $this->object->reject($result);
        $this->assertInstanceOf(
                'zaboy\async\Promise\Determined\Exception\ReasonPendingException', $this->object->wait(false)
        );
    }

//    /*     * ************* Then()  ******************************* */
//
//    public function testPromiseThen__PendingAfterRejecteddWaitUnwrapFalse()
//    {
//        $promise = new PromiseClient($this->mySqlPromiseAdapter);
//        $this->object = $promise->then([self, 'callback']);
//
//        $promise->resolve('result');
//        $this->assertEquals(
//                $this->object->wait(false), 'result after callbak'
//        );
//    }
}
