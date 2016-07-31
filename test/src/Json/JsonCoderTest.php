<?php

namespace zaboy\test\async\Json;

use zaboy\async\Promise\PromiseException;
use zaboy\async\Json\JsonCoder;

class JsonCoderTest extends \PHPUnit_Framework_TestCase
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

    public function testJsonCoder_ExceptionJsonSerialize()
    {
        $e1 = new \Exception('Exception1', 1);
        $e11 = new \Exception('Exception11', 11, $e1);
        $this->assertEquals(
                $e11, JsonCoder::jsonUnserialize(JsonCoder::jsonSerialize($e11))
        );
    }

// TODO suppoting closures
//    public function not_testJsonCoder_FunJsonSerialize()
//    {
//        $e1 = new PromiseException('Exception1', 1);
//        $message = 'Exception2';
//
//        $fun = function($message) use ($e1) {
//            return new PromiseException($message, 11, $e1);
//        };
//
//        $this->assertEquals(
//                new PromiseException('Exception2', 11, $e1), $fun($message)
//        );
//
//        $afterFun = JsonCoder::jsonUnserialize(JsonCoder::jsonSerialize($fun));
//        $this->assertEquals(
//                $afterFun($message), $fun($message)
//        );
//    }
}
