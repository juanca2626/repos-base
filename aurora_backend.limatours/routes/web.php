<?php

use App\Imports\InclusionImport;
use App\Imports\HotelTranslationImport;
use App\Imports\OperabilityImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Ixudra\Curl\Facades\Curl;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return view('index');
});

Route::get('/account', function (Request $request) {
    $url = $request->fullUrl();
    $url = str_replace(['backend', 'auroraback'], ['aurora', 'aurorafront'], $url);
    return redirect()->to($url);
});

Route::get('/executives_c1_c2', 'Controller@executives_c1_c2');
Route::get('/update_amounts_quote', 'QuotesController@update_amounts_quote');
Route::get('/reminders_frontend_day', 'Controller@reminders_frontend_day');
Route::get('/reminders_frontend_week', 'Controller@reminders_frontend_week');

Route::get('images/{filename}', function ($filename) {
    $path = public_path('images/galeries/' . $filename);

    if (!File::exists($path)) {

        abort(404);

    }



    $file = File::get($path);

    $type = File::mimeType($path);



    $response = Response::make($file, 200);

    $response->header("Content-Type", $type);



    return $response;
});
Route::get('quote/{quote_id}/export/passengers/example', 'QuotesController@exportExampleTable')->where('quote_id', '[0-9]+');
Route::get('/notify_reminders', 'ReminderController@notify');
Route::get('token_search/{token_search}', 'ManageSearchHotelsController@index');
Route::get('prices', 'ManageSearchHotelsController@prices');
Route::get('destinations/update', 'ManageSearchHotelsController@destinations');
Route::get('cotizaciones/update', 'ScriptsController@updateCotizaciones');
Route::get('update/translations/language', 'ScriptsController@updateTranslationsFrontend');
Route::get('package/status', 'ScriptsController@updateStatusPackages');

// Hoteles - Servicios que no tienen galería de fotos..
Route::get('galleries_export', 'ExportController@galleriesExport');
Route::get('notes_supplements_hotels_export', 'ExportController@getNotesSupplementsHotelsExport');

// Rates Stela con tarifas sin sincerar..
Route::get('export_rates_stela', 'ExportController@getRatesStelaExport');

// Hoteles y su ubigeo..
Route::get('hotels_location_export', 'ExportController@hotelsLocationExport');
// Servicios y su ubigeo..
Route::get('services_location_export', 'ExportController@servicesLocationExport');
// Hotels que no tienen puntos de interés..
Route::get('hotels_points_export', 'ExportController@hotelsPointsExport');

// Route::get('quote/{quote_id}/export/ranges', 'ExportController@rangesExport')->where('quote_id', '[0-9]+');
// Route::get('quote/{quote_id}/export/passengers', 'ExportController@passengersExport')->where('quote_id', '[0-9]+');
Route::get('translations/{module_id}/export', 'ExportController@translationExport')->where('module_id', '[0-9]+');
Route::get('translations/inclusions/export', 'ExportController@translationInclusionExport');
Route::get('translations/operativities/export', 'ExportController@translationOperativityExport');
Route::get('translations/amenities/export', 'ExportController@translationAmenitiesExport');
Route::get('translations/remarks/export', 'ExportController@translationRemarksExport');
Route::get('hotels/export/{hotel_year}', 'ExportController@hotelExportYear')->where('hotel_year', '[0-9]+');
Route::get('storage_extension_error', 'ExportController@storage_extension_error');

// Reporte de Protección de markups afectados
Route::get('translations/config_markups/{type}/{category_markup}/export', 'ExportController@translationConfigMarkupsExport')
    ->where('category_markup', '[0-9]+');


Route::get('package/rates/sales/markup/recalculate', 'PackagesController@recalculateMarkupPackageRateSale');

Route::get('update-inventario', 'BagsController@updateReservatiosRate');

Route::get('quote_passengers_frontend', 'QuotesFrontendController@passengers');
Route::get('quote_ranges_frontend', 'QuotesFrontendController@ranges');
Route::get('reservation_hotel_frontend', 'FileController@reservation_hotel_frontend');
Route::get('reservation_hotel_frontend_id', 'FileController@reservation_hotel_frontend_id');

//Servicios
Route::get('translations/texts/export', 'ExportController@serviceTranslationsExport');
Route::post('translations/texts/import', 'ImportController@serviceTranslationsImport');

Route::get('update/password/admin', 'UsersController@updatePasswordAdmin');
Route::get('update/packages/permissions', 'ScriptsController@updatePermissionsPackage');
//Route::get('update/policies/cancellation', 'ScriptsController@updatePoliciesCancellation');
//Route::get('duplicate/rates', 'ScriptsController@duplicateRates');

Route::get('services/inventories/update', 'ScriptsController@updateServiceInventories');
Route::get('quotes/logs/debug', 'ScriptsController@debugQuoteLogs');

// Imports Operabilities
Route::get('/translations/operability/update', function () {
    Excel::import(new OperabilityImport, storage_path().'/imports/'.'operativities.xlsx');
});
// Imports translations inclusions
Route::get('/translations/inclusions/update', function () {
    Excel::import(new InclusionImport, storage_path().'/imports/'.'inclusions.xlsx');
});

Route::get('/translations/operability/refactor', function () {
    Excel::import(new OperabilityImport('refactor'), storage_path().'/imports/'.'operativities.xlsx');
});
// Imports translations inclusions
Route::get('/translations/inclusions/refactor', function () {
    Excel::import(new InclusionImport('refactor'), storage_path().'/imports/'.'inclusions.xlsx');
});

// Imports translations hotel descriptions
Route::get('/translations/hotel/update', function () {
    //Excel::import(new HotelTranslationImport(), storage_path().'/imports/'.'hotel_translations.xlsx');
    Excel::import(new HotelTranslationImport(), public_path('hotel_translations.xlsx'));
});
//Services Translations
Route::get('/translations/services', function () {
    Excel::import(new \App\Imports\ServiceTranslationsImport(), storage_path().'/imports/'.'service_translations.xlsx');
});
// MASI Stadistics
Route::get('/masi/stadistics', 'ScriptsController@masiStadistics');
Route::get('import_detail_file', 'MasiController@import_detail_file');
Route::post('update_detail_file', 'MasiController@update_detail_file');
Route::get('/masi/report_all_logs', 'MasiController@report_all_logs');

Route::get('/debug-sentry', function () {
    throw new Exception('My first Sentry error Aurora 2 backend!');
});

Route::post('extranet/itinerary', 'ItineraryExtranetController@index');
Route::get('extranet/service/{service_code}/rates_ots', 'ServiceRatesOtsController@getRatesByServiceCode');
Route::post('extranet/master_service_test', 'MasterServiceTestStella@response_test');
Route::post('extranet/services/equivalences', 'ServiceController@create_or_update_equivalence');
Route::post('extranet/services/equivalences/composition', 'ServiceController@cud_equivalence_services');
Route::post('extranet/master_service', 'MasterServicesController@cud_master_service');
Route::post('extranet/reservations/hotel/room/status', 'ReservationsHotelsRatesPlansRoomsController@update_status_room');
Route::post('extranet/sync/services/update/rates', 'ServiceController@syncUpdateRates');

Route::get('/clients/stats', 'ClientsStatsController@search');

Route::get('clients/export', 'ExportController@clientsListExport');
Route::get('reservation/clients/export', 'ExportController@reservationClientsExport');
Route::get('hotels/rates/error_associations', 'ExportController@getRatesConfigurationError');
Route::get('hotels/notes/export', 'ExportController@getHotelsNotes');
Route::get('hotels/descriptions/export', 'ExportController@getHotelsDescriptions');
Route::get('rooms/notes/export', 'ExportController@getRoomsNotes');
Route::get('services/notes/export', 'ExportController@getServicesNotes');
Route::get('services/itineraries/export/{year?}', 'ExportController@getServicesItineraries');

Route::post('/webhook-sendinblue', 'Controller@webhook_sendinblue');
Route::post('/webhook-twilio', 'Controller@webhook_twilio');

Route::get('migrate_orders', 'Controller@migrate_orders');
Route::get('export_services_mapi', 'ExportController@services_mapi');

Route::get('reservations/{id}', 'ReservationController@executiveUpdate');

Route::get('/export_surveys', 'ExportController@export_surveys')->name('export_surveys');
Route::post('/translations/services/notes', 'ImportController@services_notes');

// AMAZON SES NOTIFICATION..
Route::post('/ses/notification', 'AmazonController@send_notification');
Route::post('/ses/webhook', 'AmazonController@webhook');
Route::get('/testing', 'Controller@testing');

// CE - INDICATORS..
Route::get('ce_indicators', 'ExportController@ce_indicators');
Route::post('hotels_preferred/notes/export', 'ExportController@getHotelsPreferredNotes');

Route::get('hotels/rate_notes', 'HotelsController@hotels_rate_notes');

Route::get('passengers-export', 'ExportController@passenger_export');

Route::get('hotel-markups/download-excel', 'ConfigMarkupsController@getHotelMarkups');

Route::get('roles/{id}/users', 'RolesController@getUsers')->where('id', '[0-9]+');

Route::get('/users/{id}', 'UsersController@show');
