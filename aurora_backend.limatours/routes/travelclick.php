<?php


/*
|--------------------------------------------------------------------------
| Sitemonder Routes
|--------------------------------------------------------------------------
*/
Route::post('service/{version}', 'TravelclickController@index');
Route::get('certification/{test_case}', function ($test_case) {
    return (new App\Http\Travelclick\TravelClick())->certificationsTests($test_case);
});
