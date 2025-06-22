<?php

use Illuminate\Support\Facades\Route;
use Solutionplus\MicroService\Http\Controllers\MicroServiceMapController;

Route::group([
    'prefix' => 'api',
], function () {
    Route::apiResource('micro-services', MicroServiceMapController::class, ['only' => ['store', 'update']]);
});