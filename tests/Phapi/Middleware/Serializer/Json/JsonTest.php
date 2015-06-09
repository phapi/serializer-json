<?php

namespace Phapi\Tests\Middleware\Serializer\Json;

use Phapi\Middleware\Serializer\Json\Json;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Phapi\Middleware\Serializer\Json
 */
class JsonTest extends TestCase {

    public function testConstruct()
    {
        $serializer = new Json();
        $this->assertEquals('{"username":"phapi"}', $serializer->serialize([ 'username' => 'phapi' ]));
    }

    public function testException()
    {
        $serializer = new Json();
        $this->setExpectedException('\Phapi\Exception\InternalServerError', 'Could not serialize content to Json');
        $serializer->serialize([ 'key' => "\xB1\x31"]);
    }

}