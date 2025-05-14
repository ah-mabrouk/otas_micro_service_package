<?php

use Illuminate\Support\Facades\Route;
use Solutionplus\MicroService\Http\Controllers\MicroServiceMapController;
use Solutionplus\MicroService\Http\Controllers\UpdateMicroserviceSecretController;

Route::group([
    'prefix' => 'api',
], function () {
    Route::apiResource('micro-services', MicroServiceMapController::class, ['only' => ['store']])->middleware('micro-service-establish-connection');

    Route::post('update-microservice-secret', UpdateMicroserviceSecretController::class)->name('update-microservice-secret')->middleware('micro-service');
});