<?php


/*
|--------------------------------------------------------------------------
| Sitemonder Routes
|--------------------------------------------------------------------------
*/
Route::post('service/{version}', 'ErevmaxController@index');
Route::get('certification/{test_case}', function ($test_case) {
    return (new App\Http\Erevmax\Erevmax())->certificationsTests($test_case);
});
