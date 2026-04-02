<?php

/*
|--------------------------------------------------------------------------
| Tourcms Routes
|--------------------------------------------------------------------------
 */
Route::get('bookings', 'TourcmsController@searchBookings');
Route::get('booking/{booking_id}', 'TourcmsController@showBooking');
Route::post('booking', 'TourcmsController@sendBooking');
Route::get('bookings/agents', 'TourcmsController@filterAgents');
Route::get('integration', 'TourcmsController@downloadBookings');
Route::get('integration2', 'TourcmsController@downloadBookings2');
Route::post('{tourcms_header_id}/status', 'TourcmsController@updateStatus');
Route::post('{tourcms_header_id}/status_external', 'TourcmsController@updateStatusExternal');
