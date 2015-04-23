<?php

namespace Phapi\Middleware\Serializer\Json;

use Phapi\Exception\InternalServerError;
use Phapi\Http\Stream;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


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
class Json {

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
     * Serializes the body to a JSON string if the attribute "http_accept" or if
     * an attribute does not exists and the "http_accept" header matches one of
     * the mime types configured in the serializer.
     *
     * @param Request $request
     * @param Response $response
     * @param $next
     * @return Response
     * @throws InternalServerError
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        // Call next middleware
        $response = $next($request, $response, $next);

        // Get accept mime type
        $accept = $this->getAcceptMimeType($request);

        // Check if the accept header matches this serializers mime types
        if (!in_array($accept, $this->mimeTypes)) {
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
            if (false === $json = json_encode($unserializedBody)) {
                // Encode failed, throw error
                throw new InternalServerError('Could not serialize content to Json');
            }

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
     * Check if the request has an attribute set with a mime type that should
     * be used. This is typically a result of content negotiation. If no
     * attribute exists, check for an accept header instead.
     *
     * @param Request $request
     * @return mixed|string
     */
    private function getAcceptMimeType(Request $request)
    {
        // Check for an attribute
        if (null !== $accept = $request->getAttribute('Accept', null)) {
            return $accept;
        }

        // Check for an accept header
        if ($request->hasHeader('Accept')) {
            return $request->getHeaderLine('Accept');
        }

        return null;
    }
}