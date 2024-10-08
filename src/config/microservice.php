<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Micro-service name
    |--------------------------------------------------------------------------
    |
    | Here you must set the current service name which will be added
    | to other services communication list. You should be careful
    | and not use any special characters or spaces except
    | dashes or underscores only [-,_] otherwise it
    | may lead to unexpected behavior
    |
    | Ex: 'tourism_service'
    |
    */

    'micro_service_name' => '',

    /*
    |--------------------------------------------------------------------------
    | Project secret
    |--------------------------------------------------------------------------
    |
    | Here you should set the same key in all micro-services which will connect
    | to each other. This key is very important to check the request which
    | will be used to set the first value of the other micro-services
    | to register on the current service.
    |
    */

    'project_secret' => env('MS_GLOBAL_PROJECT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Local secret
    |--------------------------------------------------------------------------
    |
    | Here you should set a specific key for current micro-service and it should be
    | a unique "local_secret" of the current micro-service. This key is a very
    | important part of the in between micro-services requests life sycle.
    | and is highly recommended to not set its value to an empty string.
    |
    | NOTE: The env value is already auto generated after running
    | the command "php artisan ms:install"
    |
    */

    'local_secret' => env('MS_LOCAL_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | secure requests only
    |--------------------------------------------------------------------------
    |
    | Here you should set boolean value to "false" if you need to receive insecure
    | request which mean you will receive it as an (http) request. If this
    | value is set to "true" then you will receive only (https) requests.
    |
    | NOTE: The default value for this key is "true". you may need to
    | set this value to "false" if you need to test it locally.
    |
    */

    'secure_requests_only' => env('MS_SECURE_REQUESTS_ONLY'),

    /*
    |--------------------------------------------------------------------------
    | migration sub folder
    |--------------------------------------------------------------------------
    |
    | Here you should set string value to match the folder which will contain
    | the microservice migration files if it exists in nested directorey.
    | IF this value is set to an empty string then the microservice
    | migration file will exists in the same default migration
    | directory.
    |
    */

    # eg: 'migration_sub_folder' => 'microservice/',
    'migration_sub_folder' => 'microservice/',

    /*
    |--------------------------------------------------------------------------
    | DB connection name
    |--------------------------------------------------------------------------
    |
    | Here you should set string value to match the database name which will contain the microservice
    | data if you have multiple databases in your project. IF this value is set to an empty string
    | then the microservice database connection name will be set to config('database.default').
    |
    | NOTE: If you gave a custom string value here then you need to make sure that you
    | have the needed configuration set in laravel project "database" config file.
    |
    */

    'db_connection_name' => '',

    /*
    |--------------------------------------------------------------------------
    | Package Middleware
    |--------------------------------------------------------------------------
    |
    | Here you can set this value to be true in order to diable middleware effect while development only
    |
    | Note that if the application environment is set specifically to 'production' the this key
    | will be ignored
    |
    */

    'disable_package_middleware' => env('MS_DISABLE_PACKAGE_MIDDLEWARE'),

];
