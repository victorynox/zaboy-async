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

    public function testJsonCoder_JsonSerialize()
    {
        $e1 = new PromiseException('Exception1', 1);
        $e11 = new PromiseException('Exception11', 11, $e1);

        $this->assertEquals(
                JsonCoder::jsonUnserialize(JsonCoder::jsonSerialize($e11)), $e11
        );
    }

}
