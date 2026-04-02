<?php


/*
|--------------------------------------------------------------------------
| Tourcms Routes
|--------------------------------------------------------------------------
*/
Route::post('booking', 'GYGController@booking');
Route::post('reserve', 'GYGController@reserve');
Route::get('list', 'GYGController@index');
Route::post('{extension_expedia_service_id}/status', 'GYGController@updateStatus');
Route::post('{extension_expedia_service_id}/status_external', 'GYGController@updateStatusExternal');
Route::post('{extension_expedia_service_id}/service', 'GYGController@updateService');
