<?php

use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return response()->json(['version' => 'Aurora - Quotes v3.0.0'], 200);
});

Route::get('/login', function () {
    return response()->json(['code' => 401, 'error' => 'Not authorized.'], 401);
})->name('login');


Route::get('quote/{quote_id}/export/ranges', [ExportController::class, 'rangesExport'])->where('quote_id', '[0-9]+');
Route::get('quote/{quote_id}/export/passengers', [ExportController::class, 'passengersExport'])->where('quote_id', '[0-9]+');
