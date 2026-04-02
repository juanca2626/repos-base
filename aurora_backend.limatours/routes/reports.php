<?php


/*
|--------------------------------------------------------------------------
| Reports Aurora
|--------------------------------------------------------------------------
*/

//OTS
Route::get('hotels/rooms', 'RequestReportAuroraController@listHotelRooms');
Route::get('hotels/rate_plans', 'RequestReportAuroraController@listHotelRatePlans');
Route::get('hotels/rate_plan/notes', 'RequestReportAuroraController@listHotelRatePlanNotes');

//NEG
Route::get('services/rate_protection/{year}', 'RequestReportAuroraController@servicesWithRateProtectionByYear');
Route::get('hotels/rate_protection/{year}', 'RequestReportAuroraController@hotelsWithRateProtectionByYear');

//SERVICIOS
//Exportar textos de servicios activos
Route::get('services/texts_export/{lang}', 'RequestReportAuroraController@servicesExportTextByLang');
Route::get('services/without_images', 'RequestReportAuroraController@servicesExportWithOutImages');

//HOTELES
Route::get('hotels/without_images', 'RequestReportAuroraController@hotelsExportWithOutImages');
Route::get('hotels/rooms/without_images', 'RequestReportAuroraController@hotelsRoomExportWithOutImages');

//PERMISOS DEL SISTEMA
Route::get('aurora/permissions', 'RequestReportAuroraController@permissionsAndRolesExport');

