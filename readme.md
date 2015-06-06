# JSON Serializer
Phapi JSON Serializer is a middleware based serializer that converts arrays to JSON. The middleware are [PSR-7](https://github.com/php-fig/http-message) compliant and a package developed for and used by the [Phapi Framework](https://github.com/phapi/phapi).

The JSON Serializer package includes two middleware: <code>Phapi\Middleware\Deserializer\Json</code> and <code>Phapi\Middleware\Serializer\Json</code>. By default they react if the Content Type (Deserializer) and Accept (Serializer) headers are set to either <code>application/json</code> or <code>text/json</code>. It is possible to add more mime types by passing an array to the constructor:

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

Note that the array passed to the constructor will **replace** the default settings. To keep the default use:

```php
<?php
use Phapi\Middleware\Serializer\Json\Json;

$pipeline->pipe(new Json(['application/json', 'text/json', 'text/html']));
```

The above instructions applies to the deserializer as well.

## Phapi
This middleware is a Phapi package used by the [Phapi Framework](https://github.com/phapi/phapi). The middleware are also [PSR-7](https://github.com/php-fig/http-message) compliant and implements the [Phapi Middleware Contract](https://github.com/phapi/contract).

## License
Serializer JSON is licensed under the MIT License - see the [license.md](https://github.com/phapi/serializer-json/blob/master/license.md) file for details

## Contribute
Contribution, bug fixes etc are [always welcome](https://github.com/phapi/serializer-json/issues/new).
