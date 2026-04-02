<?php

use Illuminate\Support\Facades\Route;



/*
|--------------------------------------------------------------------------
| Tourcms Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {

    Route::get('/dashboard', 'DashboeardController@index');
    Route::get('/dashboard-details', 'DashboeardDetailsController@index');
    Route::get('/dashboard/{state_id}/details', 'DashboeardDetailsController@detailByState');
    Route::get('/calendar', 'CalenderController@index');
    Route::get('/hotels-rooms-list', 'HotelsRoomsListController@index');
    Route::get('/hotels-rooms-list-totals', 'HotelsRoomsListController@totales');
    Route::get('/calendar-details', 'CalenderDetailsController@index');
    Route::get('/chart', 'GraficController@index');
    Route::get('/chart-totals', 'GraficTotalController@index');
    Route::get('/export', 'HotelExportController@export');
});
