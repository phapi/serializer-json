<?php

namespace Phapi\Tests\Middleware\Deserializer\Json;

use Phapi\Middleware\Deserializer\Json\Json;
use PHPUnit_Framework_TestCase as TestCase;

/**
* @coversDefaultClass \Phapi\Middleware\Deserializer\Json
*/
class JsonTest extends TestCase {

    public function testConstruct()
    {
        $deserializer = new Json();
        $this->assertEquals([ 'username' => 'phapi' ], $deserializer->deserialize("{ 'username': 'phapi' }"));
    }
}