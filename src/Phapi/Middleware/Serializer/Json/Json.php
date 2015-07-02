<?php

namespace Phapi\Middleware\Serializer\Json;

use Phapi\Exception\InternalServerError;
use Phapi\Serializer\Serializer;


/**
 * Class Json
 *
 * Middleware that serializes the response body to JSON
 *
 * @category Phapi
 * @package  Phapi\Middleware\Serializer\Json
 * @author   Peter Ahinko <peter@ahinko.se>
 * @license  MIT (http://opensource.org/licenses/MIT)
 * @link     https://github.com/phapi/serializer-json
 */
class Json extends Serializer
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
     * Serialize body to json
     *
     * @param array $unserializedBody
     * @return string
     * @throws InternalServerError
     */
    public function serialize(array $unserializedBody = [])
    {
        if (false === $json = json_encode($unserializedBody)) {
            // Encode failed, throw error
            throw new InternalServerError('Could not serialize content to Json');
        }
        return $json;
    }
}
