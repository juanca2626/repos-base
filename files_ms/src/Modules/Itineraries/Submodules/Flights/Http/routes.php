<?php

use Illuminate\Support\Facades\Route;
use Src\Modules\Notes\Http\Controllers\NoteController;

Route::prefix('notes')
    ->middleware(['api'])
    ->group(function () {
 
        Route::get('classification', [NoteController::class, 'classification']);

    });