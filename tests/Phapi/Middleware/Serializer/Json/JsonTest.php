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
        $request = new Request([
            'http_accept' => 'application/json'
        ]);
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

        $this->assertEquals('{"key":"value","another key":"second value"}', (string) $response->getBody());
    }

    public function testNotSuitableAcceptHeader()
    {
        $request = new Request([
            'http_accept' => 'application/xml'
        ]);
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

    public function testNegotiatedAttribute()
    {
        $request = new Request();
        $request = $request->withAttribute('Accept', 'text/json');

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

        $this->assertEquals('{"key":"value","another key":"second value"}', (string) $response->getBody());
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
        $request = new Request(['http_accept' => 'application/json']);

        $response = new Response();
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
}