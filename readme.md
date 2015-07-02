# JSON Serializer
The JSON Serializer package contains two middleware, one for serialization and one for deserialization. The two works the same the only difference is that the serializer takes an array and returns JSON and the deserializer does the opposite.

The serializer reacts if the <code>Accept</code> header matches one of the supported mime types and the deserializer reacts if the <code>Content-Type</code> matches the list of supported mime types.

By default the supported mime types are: <code>application/json</code> and <code>text/json</code>. It is possible to add more mime types by passing an array to the constructor.

## Installation
This middleware is by default included in the [Phapi Framework](https://github.com/phapi/phapi) but if you need to install it it's available to install via [Packagist](https://packagist.org) and [Composer](https://getcomposer.org).

```shell
$ php composer.phar require phapi/serializer-json:1.*
```

## Configuration
Both the serializer and deserializer has one configuration option, it's possible to add more mime types that should trigger the serializer/deserializer.

```php
<?php
use Phapi\Middleware\Serializer\Json\Json;

$pipeline->pipe(new Json(['text/html']));
```

Note that the array passed to the constructor will be merged with the default settings.

The above instructions applies to the deserializer as well.

See the [configuration documentation](http://phapi.github.io/started/configuration/) for more information about how to configure the integration with the Phapi Framework.

## Phapi
This middleware is a Phapi package used by the [Phapi Framework](https://github.com/phapi/phapi). The middleware are also [PSR-7](https://github.com/php-fig/http-message) compliant and implements the [Phapi Middleware Contract](https://github.com/phapi/contract).

## License
Serializer JSON is licensed under the MIT License - see the [license.md](https://github.com/phapi/serializer-json/blob/master/license.md) file for details

## Contribute
Contribution, bug fixes etc are [always welcome](https://github.com/phapi/serializer-json/issues/new).
