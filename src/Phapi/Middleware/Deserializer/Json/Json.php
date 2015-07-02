<?php

namespace Phapi\Middleware\Deserializer\Json;

use Phapi\Serializer\Deserializer;
use Phapi\Exception\BadRequest;

/**
 * Class Deserialize Json
 *
 * Middleware that deserializes a request with a JSON body.
 *
 * @category Phapi
 * @package  Phapi\Middleware\Deserializer\Json
 * @author   Peter Ahinko <peter@ahinko.se>
 * @license  MIT (http://opensource.org/licenses/MIT)
 * @link     https://github.com/phapi/serializer-json
 */
class Json extends Deserializer
{

    /**
     * Valid mime types
     *
     * @var array
     */
    protected $mimeTypes = [
        'application/json',
        'text/json'
    ];

    /**
     * Deserialize the body
     *
     * @param $body
     * @return array
     * @throws BadRequest
     */
    public function deserialize($body)
    {
        // Try to decode
        if (null === $array = json_decode($body, true)) {
            // Throw error if we are unable to decode body
            throw new BadRequest('Could not deserialize body (Json)');
        }

        return $array;
    }
}
