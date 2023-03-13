<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => [
        'micro-service-establish-connection',
    ]
], function () {
    Route::apiResource('micro-services', MicroServiceMapController::class, ['only' => ['store']]);
});