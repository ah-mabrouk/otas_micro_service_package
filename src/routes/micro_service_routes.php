<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => [
        'micro-service',
    ]
], function () {
    Route::apiResource('permission-groups', PermissionGroupController::class);
    Route::apiResource('permissions', PermissionController::class, ['except', ['store', 'destroy']]);
});

Route::apiResource('roles', RoleController::class)->middleware('micro-service-establish-connection');