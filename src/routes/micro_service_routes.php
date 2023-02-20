<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => [
        'micro-service',
    ]
], function () {
    // require_once base_path('routes/api.php');
});

Route::group([
    'middleware' => [
        'micro-service-establish-connection',
    ]
], function () {
    Route::apiResource('micro-services', MicroServiceController::class, ['only' => ['store']]);
});