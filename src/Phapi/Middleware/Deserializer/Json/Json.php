<?php

namespace Phapi\Middleware\Deserializer\Json;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
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
     * Deserialize the body to an array if the attribute "http_content_type" or if
     * an attribute does not exists and the "http_content_type" header matches one of
     * the mime types configured in the deserializer.
     *
     * @param Request $request
     * @param Response $response
     * @param $next
     * @return mixed
     * @throws BadRequest
     */
    public function __invoke(Request $request, Response $response, $next)
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
     * @param Request $request
     * @return mixed|null|string
     */
    private function getContentType(Request $request)
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