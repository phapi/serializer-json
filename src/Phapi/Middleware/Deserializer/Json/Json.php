<?php

namespace Phapi\Middleware\Deserializer\Json;

use Phapi\Contract\Di\Container;
use Phapi\Contract\Middleware\SerializerMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Phapi\Exception\BadRequest;

/**
 * Class Deserialize Json
 *
 * Middleware that deserializes a request with a JSON body.
 *
 * @category Phapi
 * @package  Phapi\Middleware\Unserializer
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
     * Create deserializer
     *
     * Pass additional mime types that the deserializer should accept
     * as valid JSON.
     *
     * @param null|array $mimeTypes
     */
    public function __construct($mimeTypes = null)
    {
        $this->mimeTypes = ($mimeTypes === null) ? $this->mimeTypes : $mimeTypes;
    }

    /**
     * Set dependency injection container
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
        $this->container['contentTypes'] = array_merge($this->container['contentTypes'], $this->mimeTypes);
    }

    /**
     * Deserialize the body to an array if the attribute "Content-Type" or if
     * an attribute does not exists and the "Content-Type" header matches one of
     * the mime types configured in the deserializer.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return mixed
     * @throws BadRequest
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        // Get content mime type
        $contentType = $this->getContentType($request);

        // Check if the content type header matches this serializers mime types
        if (in_array($contentType, $this->mimeTypes)) {
            // Get the body
            $body = (string) $request->getBody();

            // Make sure body is a string and not empty
            if (is_string($body) && !empty($body)) {
                // Try to decode
                if (null === $array = json_decode($body, true)) {
                    // Throw error if we are unable to decode body
                    throw new BadRequest('Could not deserialize body (Json)');
                }

                // Save the deserialized body to the request
                $request = $request->withParsedBody($array);
            }
        }

        // Call next middleware and return the response
        return $next($request, $response, $next);
    }

    /**
     * Get content type from request. First check for an attribute. An attribute
     * is usually set if content negotiation are done.
     *
     * If no attribute can be found, use the content type header.
     *
     * @param ServerRequestInterface $request
     * @return mixed|null|string
     */
    private function getContentType(ServerRequestInterface $request)
    {
        // Check for an attribute
        if (null !== $accept = $request->getAttribute('Content-Type', null)) {
            return $accept;
        }

        // Check for an accept header
        if ($request->hasHeader('Content-Type')) {
            return $request->getHeaderLine('Content-Type');
        }

        return null;
    }
}