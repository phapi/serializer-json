<?php

namespace Phapi\Tests\Middleware\Serializer\Json;

use Phapi\Http\Request;
use Phapi\Http\Response;
use Phapi\Middleware\Serializer\Json\Json;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Phapi\Middleware\Serializer\Json
 */
class JsonTest extends TestCase {

    public function testMimeTypeWithAttribute()
    {
        $request = new Request();
        $response = new Response();
        $response = $response->withHeader('Content-Type', 'application/json;charset=utf-8');

        $response = $response->withUnserializedBody([ 'key' => 'value', 'another key' => 'second value' ]);

        $serializer = new Json(['text/html']);
        $response = $serializer(
            $request,
            $response,
            function($request, $response)
            {
                return $response;
            }
        );

        $this->assertEquals('{"key":"value","another key":"second value"}', (string) $response->getBody());
    }

    public function testNotSuitableAcceptHeader()
    {
        $request = new Request();
        $response = new Response();

        $response = $response->withUnserializedBody([ 'key' => 'value', 'another key' => 'second value' ]);

        $serializer = new Json(['text/html']);
        $response = $serializer(
            $request,
            $response,
            function($request, $response)
            {
                return $response;
            }
        );

        $this->assertEquals('', (string) $response->getBody());
    }

    public function testNoHeaderNorAttribute()
    {
        $request = new Request();

        $response = new Response();
        $response = $response->withUnserializedBody([ 'key' => 'value', 'another key' => 'second value' ]);

        $serializer = new Json(['text/html']);
        $response = $serializer(
            $request,
            $response,
            function($request, $response)
            {
                return $response;
            }
        );

        $this->assertEquals('', (string) $response->getBody());
    }

    public function testNotImplementingContractWithUnserializedBodyMethodRaisesException()
    {
        $request = new Request(['http_accept' => 'application/json']);

        $response = \Mockery::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('hasHeader')->with('Content-Type')->andReturn(true);
        $response->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('application/json;charset=utf-8');

        $serializer = new Json(['text/html']);
        $this->setExpectedException('\RuntimeException', 'Json Serializer could not retrieve unserialized body');
        $response = $serializer(
            $request,
            $response,
            function($request, $response)
            {
                return $response;
            }
        );
    }

    public function testSerializationFail()
    {
        $request = new Request();

        $response = new Response();
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withUnserializedBody([ 'key' => "\xB1\x31"]);

        $serializer = new Json(['text/html']);
        $this->setExpectedException('\Phapi\Exception\InternalServerError', 'Could not serialize content to Json');
        $response = $serializer(
            $request,
            $response,
            function($request, $response)
            {
                return $response;
            }
        );

        $this->assertEquals('', (string) $response->getBody());
    }

    public function testRegisterMimeTypes()
    {
        $mockContainer = \Mockery::mock('Phapi\Contract\Di\Container');
        $mockContainer->shouldReceive('offsetSet')->with('acceptTypes', ['application/json', 'text/json']);
        $mockContainer->shouldReceive('offsetGet')->andReturn([]);

        $deserializer = new Json();
        $deserializer->setContainer($mockContainer);
        $deserializer->registerMimeTypes();
    }
}