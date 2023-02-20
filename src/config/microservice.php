<?php

return [
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

    'project_secret' => env('GLOBAL_PROJECT_SECRET', ''),

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
    */

    'local_secret' => env('LOCAL_SECRET', ''),

];
