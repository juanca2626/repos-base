<?php

use Illuminate\Support\Facades\Route;

Route::apiResource('headers', 'SerieHeaderController');
Route::apiResource('departures', 'SerieDepartureController');
Route::apiResource('programs', 'SerieProgramController');
Route::apiResource('departure-programs', 'SerieDepartureProgramController');
Route::get(
    'tracking-controls/client',
    'SerieTrackingControlController@getTrackingByClient'
)->name('tracking-controls.by-client');
Route::apiResource('tracking-controls', 'SerieTrackingControlController');


