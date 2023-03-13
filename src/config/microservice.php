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

    'project_secret' => env('GLOBAL_PROJECT_SECRET'),

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

    'local_secret' => env('LOCAL_SECRET'),

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

    'secure_requests_only' => env('SECURE_REQUESTS_ONLY'),

];
