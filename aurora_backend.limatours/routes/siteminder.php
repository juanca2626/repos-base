<?php


/*
|--------------------------------------------------------------------------
| Sitemonder Routes
|--------------------------------------------------------------------------
*/
//Route::post('service/{version}', 'SiteminderController@soap');
Route::get('certification/{test_case}', function ($test_case) {
    return (new App\Http\Siteminder\Siteminder())->certificationsTests($test_case);
});
