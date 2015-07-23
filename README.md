# CacheClient

This project providers a cache management service abstraction. It implements multiple providers, such as Memcached, Redis and process memory.

## Installation

The recommended way to install is through composer.

Just create a `composer.json` file for your project:

``` json
{
    "require": {
        "ebidtech/cache-client": "dev-master"
    }
}
```

And run these two commands to install it:

```bash
$ curl -sS https://getcomposer.org/installer | php
$ composer install
```

Now you can add the autoloader, and you will have access to the library:

```php
<?php

require 'vendor/autoload.php';
```

## Example

```php
// Create the provider instance (Predis for example).
$cache = new PredisProviderService($predisClient, $options);

// Set and get cached values.
$cache->set('my_key', 'my_value'); // CacheResponse(true, true, true, null)
$cache->get('my_key');             // CacheResponse('my_value', true, true, null);

// Failing to get a stored value.
$cache->get('my_other_key');       // CacheResponse(false, false, true, 'Resource not found.');
```
