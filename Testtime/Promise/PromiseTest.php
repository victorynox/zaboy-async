<?php

namespace zaboy\test\async\Promise;

use zaboy\async\Promise\Client;
use Interop\Container\ContainerInterface;
use zaboy\rest\TableGateway\TableManagerMysql;
use zaboy\async\Promise\Store;
use zaboy\async\Promise\Factory\StoreFactory;
use zaboy\async\Promise\Interfaces\PromiseInterface;

class PromiseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Store
     */
    protected $store;

    /**
     * @var Promise
     */
    protected $object;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        global $testCase;
        $testCase = 'table_for_test';
        $this->tableName = StoreFactory::TABLE_NAME . '_test';

        $this->container = include './config/container.php';
        $this->store = $this->container->get(StoreFactory::KEY);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $adapter = $this->container->get('db');
        $tableManagerMysql = new TableManagerMysql($adapter);
        $tableManagerMysql->deleteTable($this->tableName);
    }

    public static function callback($value)
    {
        return $value . ' after callbak';
    }

    public static function callException($value)
    {
        throw new \Exception('Exception ', 0, new \Exception('prev Exception'));
    }

    /* ---------------------------------------------------------------------------------- */

    public function testPromiseTest__extractIdFromString()
    {
        $this->object = new Client($this->store);
        $string = ' jkiuhs iuhis pi siuiughf]l;m74jn &568ihj983438h^&%  ';
        $this->assertEquals(
                [], $this->object->extractId($string)
        );
        $string = ' jkiuhs iuhis pi siu promise__1469864422_189511__579c84162e43e4_34952052 iughf]l;m74jn &568ihj983438h^&%  ';
        $this->assertEquals(
                ['promise__1469864422_189511__579c84162e43e4_34952052'], $this->object->extractId($string)
        );
        $string = ' jkiuhs iuhis pi s promise__2229864461_889811__579c843dd93ad1_08516192  AND promise__3339864461_889811__579c843dd93ad1_08516192';
        $this->assertEquals(
                [
            'promise__3339864461_889811__579c843dd93ad1_08516192',
            'promise__2229864461_889811__579c843dd93ad1_08516192',
                ]
                , $this->object->extractId($string)
        );
    }

    public function testPromiseTest__extractIdFromException()
    {
        $this->object = new Client($this->store);
        $exc1 = new \Exception('Promise: promise__1119864461_889811__579c843dd93ad1_08516192');
        $exc2 = new \Exception('promise__2229864461_889811__579c843dd93ad1_08516192  AND promise__3339864461_889811__579c843dd93ad1_08516192', 0, $exc1);
        $exc3 = new \Exception('promise__4449864461_889811__579c843dd93ad1_08516192 of the end', 0, $exc2);

        $this->assertEquals(
                [
            'promise__4449864461_889811__579c843dd93ad1_08516192',
            'promise__3339864461_889811__579c843dd93ad1_08516192',
            'promise__2229864461_889811__579c843dd93ad1_08516192',
            'promise__1119864461_889811__579c843dd93ad1_08516192',
                ]
                , $this->object->extractId($exc3)
        );
    }

    public function testPromiseTest__make()
    {
        $this->object = new Client($this->store);
        $this->assertInstanceOf(
                'zaboy\async\Promise\Client', $this->object
        );
        $this->assertSame(
                $this->object->getState(), PromiseInterface::PENDING
        );
    }

    public function testPromiseTest__resolve()
    {
        $this->object = new Client($this->store);
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
        $this->object = new Client($this->store);
        $object = new \stdClass();
        $this->object->resolve($object);
        $this->assertEquals(
                $this->object->wait(), $object
        );
    }

    public function testPromiseTest__reject()
    {
        $this->object = new Client($this->store);
        $this->object->reject('reason');
        $this->assertSame(
                PromiseInterface::REJECTED, $this->object->getState()
        );
        $this->assertInstanceOf(
                'zaboy\async\Promise\Exception\RejectedException', $this->object->wait(false)
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
        $this->object = new Client($this->store);
        $object = new \stdClass();
        $this->object->reject(new \stdClass());
        $this->assertSame(
                $this->object->getState(), PromiseInterface::REJECTED
        );
        $this->assertInstanceOf(
                'zaboy\async\Promise\Exception\RejectedException', $this->object->wait(false)
        );
        $this->assertStringStartsWith(
                'RejectedPromise. String: Object of class stdClass could not be converted to string'
                , $this->object->wait(false)->getPrevious()->getMessage()
        );
        $this->assertStringStartsWith(
                'Reason cannot be converted to string.', $this->object->wait(false)->getMessage()
        );
    }

    /*     * ************* Wait() with $unwrap = true ******************************* */

    public function testPromiseTest__PendingWait()
    {
        $this->object = new Client($this->store);
        $this->setExpectedException('\zaboy\async\Promise\Exception\TimeIsOutException');
        $this->object->wait(true, 0);
    }

    public function testPromiseTest__RejectedWait()
    {
        $this->object = new Client($this->store);
        $this->object->reject('reason');
        $this->setExpectedException('\zaboy\async\Promise\Exception\RejectedException');
        $this->object->wait(true, 0);
    }

    public function testPromiseTest__PendingAfterPendingWait()
    {
        $this->object = new Client($this->store);
        $result = new Client($this->store);
        $this->object->resolve($result);
        $this->setExpectedException('\zaboy\async\Promise\Exception\TimeIsOutException');
        $this->object->wait(true, 1);
    }

    public function testPromiseTest__PendingAfterFulfilledWait()
    {
        $this->object = new Client($this->store);
        $result = new Client($this->store);
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
        $this->object = new Client($this->store);
        $result = new Client($this->store);
        $this->object->reject($result);
        $this->setExpectedException('\zaboy\async\Promise\Exception\ReasonPendingException');
        //ReasonPendingException
        $this->object->wait(true, 0);
    }

    /*     * ************* Wait() with $unwrap = false ******************************* */

    public function testPromiseTest__PendingWaitUnwrapFalse()
    {
        $this->object = new Client($this->store);
        $this->assertEquals(
                $this->object->wait(false)->getState(), PromiseInterface::PENDING
        );
    }

    public function testPromiseTest__RejectedWaitUnwrapFalse()
    {
        $this->object = new Client($this->store);
        $this->object->reject('reason');
        $this->assertInstanceOf(
                'zaboy\async\Promise\Exception\RejectedException', $this->object->wait(false)
        );
    }

    public function testPromiseTest__PendingAfterPendingWaitUnwrapFalse()
    {
        $this->object = new Client($this->store);
        $result = new Client($this->store);
        $this->object->resolve($result);
        $this->assertEquals(
                $this->object->wait(false)->getState(), PromiseInterface::PENDING
        );
    }

    public function testPromiseTest__PendingAfterFulfilledWaitUnwrapFalse()
    {
        $this->object = new Client($this->store);
        $result = new Client($this->store);
        $this->object->resolve($result);
        $result->resolve('result');
        $this->assertEquals(
                $this->object->wait(false), 'result'
        );
    }

    public function testPromiseTest__PendingAfterRejecteddWaitUnwrapFalse()
    {
        $this->object = new Client($this->store);
        $result = new Client($this->store);
        $this->object->reject($result);
        $this->assertInstanceOf(
                'zaboy\async\Promise\Exception\ReasonPendingException', $this->object->wait(false)
        );
        $this->assertTrue(
                $result->isId($this->object->wait(false)->getMessage())
        );
    }

    /*     * ************* Then()  ******************************* */

    public function testPromiseThen__Then()
    {
        $promise = new Client($this->store);
        $this->object = $promise->then([get_class($this), 'callback']);
        $this->assertInstanceOf(
                '\zaboy\async\Promise\Promise\DependentPromise', $this->object->wait(false)
        );
    }

    /*     * ************* Then() Fulfilled  ******************************* */

    public function testPromiseThen__ThenFulfilled()
    {
        $promise = new Client($this->store);
        $this->object = $promise->then([get_class($this), 'callback']);
        $promise->resolve('result');
        $this->assertEquals(
                'result after callbak', $this->object->wait(false)
        );
    }

    public function testPromiseThen__ThenThenFulfilled()
    {
        $promise1 = new Client($this->store);
        $promise2 = $promise1->then();
        $this->object = $promise2->then([get_class($this), 'callback']);
        $promise1->resolve('result');
        $this->assertEquals(
                'result after callbak', $this->object->wait(false)
        );
    }

    public function testPromiseThen__ThenFulfilledByPromise()
    {
        $result = new Client($this->store);
        $promise1 = new Client($this->store);
        $this->object = $promise1->then([get_class($this), 'callback']);
        $promise1->resolve($result);
        $promise = $this->object->wait(false);

        $this->assertEquals(
                $this->object->getId(), $promise->getId()
        );
        $this->assertEquals(
                PromiseInterface::PENDING, $this->object->getState()
        );

        $result->resolve('result');
        $this->assertEquals(
                'result after callbak', $this->object->wait(false)
        );
        $this->assertEquals(
                PromiseInterface::FULFILLED, $this->object->getState()
        );
    }

    public function testPromiseThen__ThenFromFulfilled()
    {
        $promise = new Client($this->store);
        $promise->resolve('result');
        $this->object = $promise->then([get_class($this), 'callback']);
        $this->assertEquals(
                'result after callbak', $this->object->wait(false)
        );
    }

    public function testPromiseThen__ThenFromFulfilledByPromise()
    {
        $result = new Client($this->store);
        $promise1 = new Client($this->store);
        $promise1->resolve($result);
        $this->object = $promise1->then([get_class($this), 'callback']);
        $this->assertEquals(
                $this->object->getId(), $this->object->wait(false)->getId()
        );
        $this->assertEquals(
                PromiseInterface::PENDING, $this->object->getState()
        );

        $result->resolve('result');
        $this->assertEquals(
                'result after callbak', $this->object->wait(false)
        );
        $this->assertEquals(
                PromiseInterface::FULFILLED, $this->object->getState()
        );
    }

    public function testPromiseThen__ExceptionInOnFulfilled()
    {
        $promise = new Client($this->store);
        $this->object = $promise->then([get_class($this), 'callException']);
        $promise->resolve('result');
        $this->assertInstanceOf(
                '\Exception', $this->object->wait(false)
        );
    }

    /*     * ************* Then() Rejected  ******************************* */

    public function testPromiseThen__ThenRejected()
    {
        $promise = new Client($this->store);
        $this->object = $promise->then(null, function ($reason) {
            return $reason->getMessage() . ' was resolved';
        });
        $promise->reject('Error');
        $this->assertEquals(
                'Error was resolved', $this->object->wait(false)
        );
    }

    public function testPromiseThen__ThenThen_Resolved_onFulfilledException_onRejectedResolved()
    {
        $promise1 = new Client($this->store);
        $promise2 = $promise1->then([get_class($this), 'callException'], null);
        $message = ' was resolved';
        $this->object = $promise2->then(null, function ($reason) use ($message) {
            return $reason->getMessage() . $message;
        });
        $promise1->resolve('result');
        $this->assertStringEndsWith(
                ' was resolved', $this->object->wait(false)
        );
    }

    public function testPromiseThen__ThenRejectedByPromiseButResolved()
    {
        $result = new Client($this->store);
        $promise1 = new Client($this->store);
        $this->object = $promise1->then(null, function ($reason) {
            return $reason->getMessage() . ' was resolved';
        });
        $promise1->reject($result);

        $this->assertEquals(
                $result->getId() . ' was resolved', $this->object->wait(false)
        );
        $this->assertEquals(
                PromiseInterface::FULFILLED, $this->object->getState()
        );
    }

}
