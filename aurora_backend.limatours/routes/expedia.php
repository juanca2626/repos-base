<?php


/*
|--------------------------------------------------------------------------
| Tourcms Routes
|--------------------------------------------------------------------------
*/
Route::post('booking', 'ExpediaController@booking');
Route::post('reserve', 'ExpediaController@reserve');
Route::get('list', 'ExpediaController@index');
Route::post('{extension_expedia_service_id}/status', 'ExpediaController@updateStatus');
Route::post('{extension_expedia_service_id}/status_external', 'ExpediaController@updateStatusExternal');
Route::post('{extension_expedia_service_id}/service', 'ExpediaController@updateService');
