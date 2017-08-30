Goodreads Provider for OAuth1 Client
=========================

This package provides Goodreads OAuth 1.0 support for the PHP League's [OAuth 1.0 Client](https://github.com/thephpleague/oauth1-client).

# Installation

To install, use [Composer](https://getcomposer.org/):

```sh
composer require fbng/oauth1-goodreads
```

# Usage

Usage is the same as The League's OAuth client, using NetGalley\OAuth1\Client\Server\Goodreads as the provider.

```php
$client = new \NetGalley\OAuth1\Client\Server\Goodreads(array(
    'identifier'   => 'your-client-id',
    'secret'       => 'your-client-secret',
    'callback_uri' => 'http://callback.url/callback',
));
```

# Documentation

See the Goodreads API documentation:

* [API Documentation](https://www.goodreads.com/api/documentation)
* [API Reference](https://www.goodreads.com/api/index)
