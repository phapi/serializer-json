<?php

namespace Phapi\Tests\Middleware\Deserializer\Json;

use Phapi\Http\Request;
use Phapi\Http\Response;
use Phapi\Http\Stream;
use Phapi\Middleware\Deserializer\Json\Json;
use PHPUnit_Framework_TestCase as TestCase;

/**
* @coversDefaultClass \Phapi\Middleware\Deserializer\Json
*/
class JsonTest extends TestCase {

    public function testConstruct()
    {
        $request = new Request([
            'content_type' => 'application/json'
        ]);
        $body = new Stream('php://memory', 'wb+');
        $body->write('{"key":"value","another key":"second value"}');
        $request = $request->withBody($body);

        $response = new Response();

        $deserializer = new Json();
        $response = $deserializer(
            $request,
            $response,
            function ($request, $response)
            {
                $this->assertEquals([ 'key' => 'value', 'another key' => 'second value' ], $request->getParsedBody());
                return $response;
            }
        );
    }

    public function testDecodeFail()
    {
        $request = new Request([
            'content_type' => 'application/json'
        ]);
        $body = new Stream('php://memory', 'wb+');
        $body->write('{"key":"value","another key""second value"}');
        $request = $request->withBody($body);

        $response = new Response();

        $deserializer = new Json();
        $this->setExpectedException('\Phapi\Exception\BadRequest', 'Could not deserialize body (Json)');
        $response = $deserializer(
            $request,
            $response,
            function ($request, $response)
            {
                $this->assertEquals([ 'key' => 'value', 'another key' => 'second value' ], $request->getParsedBody());
                return $response;
            }
        );
    }

    public function testAttributeNegotiatedContentType()
    {
        $request = new Request();
        $request = $request->withAttribute('Content-Type', 'application/json');
        $body = new Stream('php://memory', 'wb+');
        $body->write('{"key":"value","another key":"second value"}');
        $request = $request->withBody($body);

        $response = new Response();

        $deserializer = new Json();
        $response = $deserializer(
            $request,
            $response,
            function ($request, $response)
            {
                $this->assertEquals([ 'key' => 'value', 'another key' => 'second value' ], $request->getParsedBody());
                return $response;
            }
        );
    }

    public function testNotJsonHeader()
    {
        $request = new Request();
        $request = $request->withAttribute('Content-Type', 'application/xml');
        $body = new Stream('php://memory', 'wb+');
        $body->write('{"key":"value","another key":"second value"}');
        $request = $request->withBody($body);

        $response = new Response();

        $deserializer = new Json();
        $response = $deserializer(
            $request,
            $response,
            function ($request, $response)
            {
                $this->assertEquals('', $request->getParsedBody());
                return $response;
            }
        );
    }

    public function testNoHeaderNorAttribute()
    {
        $request = new Request();
        $body = new Stream('php://memory', 'wb+');
        $body->write('{"key":"value","another key":"second value"}');
        $request = $request->withBody($body);

        $response = new Response();

        $deserializer = new Json();
        $response = $deserializer(
            $request,
            $response,
            function ($request, $response)
            {
                $this->assertEquals('', $request->getParsedBody());
                return $response;
            }
        );
    }

    public function testRegisterMimeTypes()
    {
        $mockContainer = \Mockery::mock('Phapi\Contract\Di\Container');
        $mockContainer->shouldReceive('offsetSet')->with('contentTypes', ['application/json', 'text/json']);
        $mockContainer->shouldReceive('offsetGet')->andReturn([]);

        $deserializer = new Json();
        $deserializer->setContainer($mockContainer);
        $deserializer->registerMimeTypes();
    }
}