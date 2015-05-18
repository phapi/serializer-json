<?php

namespace Phapi\Middleware\Serializer\Json;

use Phapi\Contract\Di\Container;
use Phapi\Contract\Middleware\SerializerMiddleware;
use Phapi\Exception\InternalServerError;
use Phapi\Http\Response;
use Phapi\Http\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class Json
 *
 * Middleware that serializes the response body to JSON
 *
 * @category Phapi
 * @package  Phapi\Middleware\Serializer
 * @author   Peter Ahinko <peter@ahinko.se>
 * @license  MIT (http://opensource.org/licenses/MIT)
 * @link     https://github.com/phapi/serializer-json
 */
class Json implements SerializerMiddleware
{

    /**
     * Valid mime types
     *
     * @var array
     */
    private $mimeTypes = [
        'application/json',
        'text/json'
    ];

    /**
     * Dependency injection container
     *
     * @var Container
     */
    private $container;

    /**
     * Create serializer.
     *
     * Pass additional mime types that the serializer should accept
     * as valid JSON.
     *
     * @param null|array $mimeTypes
     */
    public function __construct($mimeTypes = null)
    {
        $this->mimeTypes = ($mimeTypes === null) ? $this->mimeTypes : array_merge($this->mimeTypes, $mimeTypes);
    }

    /**
     * Set the dependency injection container
     *
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register supported mime types to the container
     */
    public function registerMimeTypes()
    {
        $this->container['acceptTypes'] = array_merge($this->container['acceptTypes'], $this->mimeTypes);
    }

    /**
     * Serializes the body to a JSON string if the attribute "Accept" or if
     * an attribute does not exists and the "Accept" header matches one of
     * the mime types configured in the serializer.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     * @throws InternalServerError
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        // Call next middleware
        $response = $next($request, $response, $next);

        // Get response content type
        $contentType = $this->getContentType($response);

        // Check if the accept header matches this serializers mime types
        if (!in_array($contentType, $this->mimeTypes)) {
            // This serializer does not handle this mime type so there is nothing
            // left to do. Return response.
            return $response;
        }

        // Check if the response has a method for getting the unserialized body since
        // it's not part of the default PSR-7 implementation.
        if (!method_exists($response, 'getUnserializedBody')) {
            throw new \RuntimeException('Json Serializer could not retrieve unserialized body');
        }
        $unserializedBody = $response->getUnserializedBody();

        // Check if the body is an array and not empty
        if (is_array($unserializedBody) && !empty($unserializedBody)) {
            // Try and encode the array to json
            $json = $this->serialize($unserializedBody);

            // Create a new body with the serialized content
            $body = new Stream('php://memory', 'w+');
            $body->write($json);

            // Add the body to the response
            $response = $response->withBody($body);
        }

        // Return the response
        return $response;
    }

    /**
     * Serialize body to json
     *
     * @param array $unserializedBody
     * @return string
     * @throws InternalServerError
     */
    private function serialize(array $unserializedBody = [])
    {
        if (false === $json = json_encode($unserializedBody)) {
            // Encode failed, throw error
            throw new InternalServerError('Could not serialize content to Json');
        }
        return $json;
    }

    /**
     * Check if the request has an attribute set with a mime type that should
     * be used. This is typically a result of content negotiation. If no
     * attribute exists, check for an accept header instead.
     *
     * @param ResponseInterface $response
     * @return mixed|string
     */
    private function getContentType(ResponseInterface $response)
    {
        // Check for an accept header
        if ($response->hasHeader('Content-Type')) {
            // Get the first part of the header, for example: exclude charset=utf-8

            $header = $response->getHeaderLine('Content-Type');
            $parts = explode(';', $header);
            return trim($parts[0]);
        }

        return null;
    }
}