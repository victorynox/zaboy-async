<?php

namespace zaboy\test\async\Promise;

use zaboy\async\Promise\PromiseException;

class PromiseExceptionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var PromiseException
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    public function testPromise_JsonSerialize()
    {
        $e1 = new PromiseException('Exception1', 1);
        $e11 = new PromiseException('Exception11', 11, $e1);

        $this->assertEquals(
                PromiseException::jsonUnserialize($e11->jsonSerialize()), $e11
        );
    }

}
