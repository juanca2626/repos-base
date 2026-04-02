<?php

use Illuminate\Support\Facades\Route;
use Src\Modules\Catalogs\Http\Controllers\CatalogController;

Route::prefix('catalogs')
    ->middleware(['api', 'cognito.auth'])
    ->group(function () {

        Route::get('executives', [CatalogController::class, 'executives']);
        Route::get('cities', [CatalogController::class, 'cities']); 
    });