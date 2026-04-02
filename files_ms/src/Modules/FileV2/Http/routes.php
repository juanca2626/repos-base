<?php

use Illuminate\Support\Facades\Route;
use Src\Modules\FileV2\Http\Controllers\FileController;

Route::prefix('filesv2')
    ->middleware(['api', 'cognito.auth'])
    ->group(function () {
        Route::get('/', [FileController::class, 'index']);        
        Route::post('/', [FileController::class, 'store']);
        Route::get('/{file_id}', [FileController::class, 'show']);
    });