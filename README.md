# Solutionplus/MicroService

solutionplus/microservice is a Laravel package for dealing with specific case of micro-services connection so, it doesn't suit all needs or handle all connection cases.

## Table of Content
[Installation](#Installation)

[License](#License)

## Installation

You can install the package using composer

```bash
# cli commands

composer install solutionplus/microservice

php artisan ms:install

php artisan migrate
```

* Then apply `micro-service` middleware on the routes group you will create to communicate with other microservices

```php
# In micro-service project routes file

Route::group([
    'middleware' => [
        'micro-service',
    ]
], function () {
    // routes communicates with other microservices goes here
});
```

#### Note:
> UNDER CONSTRUCTION.

## License

Solutionplus/MicroService package is limited and proprietary software belongs to Solutionplus.net company.
