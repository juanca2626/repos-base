<?php

use Illuminate\Support\Facades\Route;
use Src\Modules\Catalogs\Http\Controllers\CatalogController;

Route::prefix('passengers')
    ->middleware(['api', 'cognito.auth'])
    ->group(function () {
        Route::put('/', [CatalogController::class, 'executives']); 
    });