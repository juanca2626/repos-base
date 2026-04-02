<?php


/*
|--------------------------------------------------------------------------
| Tourcms Routes
|--------------------------------------------------------------------------
*/
Route::post('booking', 'DespegarController@booking');
Route::post('reserve', 'DespegarController@reserve');
Route::get('list', 'DespegarController@index');
Route::get('homologations', 'DespegarController@homologations');
Route::post('homologations', 'DespegarController@storeHomologation');
Route::delete('homologation/{id}', 'DespegarController@destroyHomologation');
Route::post('{extension_despegar_service_id}/status', 'DespegarController@updateStatus');
Route::post('{extension_despegar_service_id}/status_external', 'DespegarController@updateStatusExternal');
