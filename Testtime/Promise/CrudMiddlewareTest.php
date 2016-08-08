<?php

namespace zaboy\test\async\Promise;

use zaboy\async\Promise\Factory\StoreFactory;
use zaboy\async\Promise\Store;
use Interop\Container\ContainerInterface;
use zaboy\rest\DataStore\HttpClient;
use zaboy\async\Promise\Promise;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-29 at 18:23:51.
 */
class CrudMiddlewareTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var HttpClient
     */
    protected $object;

    /**
     * @var Store
     */
    protected $store;

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
        $this->object = $this->container->get('test_crud_client');
        $this->store = $this->container->get(StoreFactory::KEY);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    public function test__createPromise()
    {

        $promiseData = $this->object->create([]);
        $promise = new Promise($this->store, $promiseData[Store::PROMISE_ID]);
        $this->assertInstanceOf(
                Promise::class, $promise
        );
        $this->assertEquals(
                Promise::PENDING, $promise->getState()
        );
    }

    public function test_resolvePromise()
    {
        $promiseData = $this->object->create([]);
        $promiseData[Store::STATE] = Promise::FULFILLED;
        $promiseData[Store::RESULT] = 'test_result_success_fulfill';

        $this->object->update($promiseData);

        $promise = new Promise($this->store, $promiseData[Store::PROMISE_ID]);
        $result = $promise->wait(false);
        $this->assertEquals(
                'test_result_success_fulfill', $result
        );
        $this->assertEquals(
                Promise::FULFILLED, $promise->getState()
        );
    }

    public function test_rejectPromise()
    {
        $promiseData = $this->object->create([]);
        $promiseData[Store::STATE] = Promise::REJECTED;
        $promiseData[Store::RESULT] = 'test_result_error_reject';

        $this->object->update($promiseData);

        $promise = new Promise($this->store, $promiseData[Store::PROMISE_ID]);
        $result = $promise->wait(false);
        $this->assertInstanceOf(
                'zaboy\async\Promise\Exception\RejectedException', $result
        );
        $this->assertEquals(
                Promise::REJECTED, $promise->getState()
        );
    }

    public function test_tryToChangeFulfilledPromise()
    {
        $promiseData = $this->object->create([]);
        $promiseData[Store::STATE] = Promise::FULFILLED;
        $promiseData[Store::RESULT] = 'test_result_success_fulfill';
        $this->object->update($promiseData);

        $promise = new Promise($this->store, $promiseData[Store::PROMISE_ID]);
        $result = $promise->wait(false);
        $this->assertEquals(
                'test_result_success_fulfill', $result
        );
        $this->assertEquals(
                Promise::FULFILLED, $promise->getState()
        );

        $promiseData[Store::STATE] = Promise::REJECTED;
        $this->setExpectedExceptionRegExp('\zaboy\rest\DataStore\DataStoreException');
        $this->object->update($promiseData);
    }

}
