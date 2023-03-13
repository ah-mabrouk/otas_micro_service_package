# Solutionplus/MicroService

solutionplus/microservice is a Laravel package for dealing with specific case of micro-services connection so, it doesn't suit all needs or handle all connection cases.

## Table of Content
[Installation](#Installation)

[Usage](#Usage)

[License](#License)

## Installation

You can install the package using composer

```bash
# cli commands

composer require solutionplus/microservice

php artisan ms:install
```

#### Important:
> Don't forget to modify the added env key `GLOBAL_PROJECT_SECRET` to exactly match all other microservices projects `GLOBAL_PROJECT_SECRET` then run:

```bash
php artisan config:cache
```

* Then apply `micro-service` on the routes group you will create to communicate with other microservices

```php
Route::group([
    'middleware' => [
        'micro-service',
    ]
], function () {
    // routes communicates with other microservices goes here
});
```

## usage

There is some predefined methods that will help you communicate with other services shown below:


```php
// EX: $microserviceName = 'payment';
// EX: $uri = 'license-payments' || 'orders' ...etc;
// EX: $origin = 'payment.com';

// `$params` and `$data` should be ['key' => 'value'] pair array
// which represent query string in get requests or inputs in post requests

$response = MsHttp::get($microserviceName, $uri, $params = []);

$response = MsHttp::post($microserviceName, $uri, $data = []);

$response = MsHttp::put($microserviceName, $uri, $data = []);

$response = MsHttp::delete($microserviceName, $uri); // Not finished yet

$response = MsHttp::establish($microserviceName, $origin);
```

The above methods are must to be used if you need to encrypt requests between micro-services

#### Note:
> in case the middlewares are not auto discovered then follow the next steps:

* add both middlewares `micro-service` and `micro-service-establish-connection` to `$routeMiddleware` array inside `kernel.php` file like so:


```php
# In kernel.php file


/**
 * The application's route middleware.
 *
 * These middleware may be assigned to groups or used individually.
 *
 * @var array<string, class-string|string>
 */
protected $routeMiddleware = [
    //
    'micro-service' => \Solutionplus\MicroService\Http\Middleware\MicroServiceMiddleware::class,
    'micro-service-establish-connection' => \Solutionplus\MicroService\Http\Middleware\MicroServiceEstablishConnectionMiddleware::class,
];
```

Using `micro-service` middleware on micro-services routes group is a must as it's responsible about decoding requests in order to make you deal with it as you usually do. Otherwise it will lead to unexpected results.

#### Note:
> Don't use `micro-service-establish-connection` on any request yourself. It's already running on the predefined`micro-services` `store` route.

#### Note:
> UNDER CONSTRUCTION.

## License

Solutionplus/MicroService package is limited and proprietary software belongs to Solutionplus.net company.
