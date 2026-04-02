<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Exports\PassengersExport;
use Maatwebsite\Excel\Facades\Excel;

//Route::get('/quote/{quote_id}/passengers/excel', function ($quote_id) {
//
//    $passengers = \App\QuotePassengerExample::where('quote_id', $quote_id)->get();
//
//    return Excel::download(new  PassengersExport($passengers), 'passengers.xlsx');
//
//})->where(['quote_id' => '[0-9]+']);

Route::match(['get', 'post'], 'operability', 'HomeController@operability');
Route::post('/toggle_view_hotels', 'FileController@toggle_view_hotels');
Route::get('/filter_hotels_file', 'FileController@filter_hotels_file');

Route::get('/quote/passengers/excel/example', function () {

    $passengers = \App\QuotePassengerExample::where('quote_id', 1)->get();

    return Excel::download(new  PassengersExport($passengers), 'passengers.xlsx');
});

Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::guest()) {
        return view('auth.login');
    } else {
        return redirect('home');
    }
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::post('/logincross', 'Auth\LoginController@loginCross');
Route::get('/logincross', 'Auth\LoginController@loginCrossView');


Route::get('/hotels', function () {
    $refererEjecute = 1;
    $referer = request()->headers->get('referer');
    if ($referer and str_contains($referer, 'cart_details/view')) {
        $refererEjecute = 2;
    }
    return view('hotels.hotel', ['refererEjecute' => $refererEjecute]);
})->name('hotels')->middleware(['auth']);


/*  Route AURORA CLIENTE */
Route::get('/paquetes', function () {
    return view('aurora_client.home.index');
});


/*  End AURORA CLIENTE  */


/*  Route Services */
Route::get('/services', function () {

    $refererEjecute = 1;
    $referer = request()->headers->get('referer');
    if ($referer and str_contains($referer, 'cart_details/view')) {
        $refererEjecute = 2;
    }
    return view('services.services', ['refererEjecute' => $refererEjecute]);
})->name('services')->middleware(['auth']);
/*  End Services */
/*  Route shopping */

Route::get('/shopping', function () {
    return view('cart_details.shopping');
});

Route::get('/package/reservation/details', function () {
    return view('packages.client.reservation_details');
});
//    ->middleware(['auth'])->name('reservation_details');

Route::get('/client/passengers', function () {
    return view('packages.client.pending_data');
})->middleware(['auth'])->name('pending_data');

Route::get('/package/booking/{file_code}', function ($file_code) {
    return view('packages.client.booking_confirmation', compact('file_code'));
})->middleware(['auth'])->name('booking_confirmation');

Route::get('/package-details', function () {
    return view('packages.client.packages_details');
})->middleware(['auth'])->name('packages_details');

Route::get('/multimedia', function () {
    return view('multimedia');
})->middleware(['auth'])->name('multimedia');

Route::get('/cancellation-policies', function () {
    return view('cancellation_policies');
})->middleware(['auth'])->name('cancellation_policies');

Route::get('/biosafety-protocols', function () {
    return view('biosafety_protocols');
})->middleware(['auth'])->name('biosafety-protocols');

Route::get('/travel-recommendations', function () {
    return view('travel_recommendations');
})->middleware(['auth'])->name('travel-recommendations');

Route::get('/contact-us', function () {
    return view('contact_us');
})->middleware(['auth'])->name('contact-us');

/*---- Paquetes modificables ----*/
Route::get('/package-modify', function () {
    return view('packages.client.package_modify');
})->middleware(['auth']);
/*---- End paquetes modificables ----*/


/*  End Shopping */

/*  Route shopping */

Route::get('/shopping', function () {
    return view('cart_details.shopping');
});


Route::get('/customers', function () {
    return view('menu.customers');
})->name('menu.customers')->middleware(['auth']);

Route::get('/meridian', function () {
    return view('menu.meridian');
})->name('menu.meridian')->middleware(['auth']);

Route::get('/central_bookings/tourcms', function () {
    return view('central_bookings.tourcms');
})->name('central_bookings.tourcms')->middleware(['auth']);

Route::get('/central_bookings/expedia', function () {
    return view('central_bookings.expedia');
})->name('central_bookings.expedia')->middleware(['auth']);

Route::get('/central_bookings/despegar', function () {
    return view('central_bookings.despegar');
})->name('central_bookings.despegar')->middleware(['auth']);

Route::get('/central_bookings/pentagrama', function () {
    return view('central_bookings.pentagrama');
})->name('central_bookings.pentagrama')->middleware(['auth']);

Route::get('/central_bookings/get-your-guide', function () {
    return view('central_bookings.gyg');
})->name('central_bookings.gyg')->middleware(['auth']);

Route::get('/central_bookings/otas_generic', function () {
    return view('central_bookings.otas_generic');
})->name('central_bookings.otas_generic')->middleware(['auth']);

Route::get('/central_bookings/report_otas', function () {
    return view('central_bookings.report_otas');
})->name('central_bookings.report_otas')->middleware(['auth']);

Route::get('/central_bookings/all', function () {
    return view('central_bookings.all');
})->name('central_bookings.all')->middleware(['auth']);

Route::get('/monitoreo', function () {
    return view('menu.monitoreo');
})->name('menu.monitoreo')->middleware(['auth']);

Route::get('/producto-no-conforme', function () {
    return view('menu.producto_no_conforme');
})->name('menu.producto-no-conforme')->middleware(['auth']);

Route::get('/gestion_file', function () {
    return view('menu.gestion_file');
})->name('menu.gestion_file')->middleware(['auth']);

// Route::get('/masi_mailing', function () {
//     return view('menu.masi_mailing');
// })->name('menu.masi_mailing');

Route::get('masi_mailing', 'MasiMailingController')->name('masi_mailing');
Route::get('masi_statistics', 'MasiStatisticsController')->name('masi_statistics');

Route::get('/masi_logs', 'MasiController@index')->name('masi_logs');

Route::get('/masi_reportes', function () {
    return view('menu.masi_reportes');
})->name('menu.masi_reportes')->middleware(['auth']);

Route::get('/programacion', function () {
    return view('menu.programacion');
})->name('menu.programacion')->middleware(['auth']);

Route::get('/lista_confirmacion', function () {
    return view('menu.lista_confirmacion');
})->name('menu.lista_confirmacion')->middleware(['auth']);

Route::get('/apps', function () {
    return view('menu.apps');
})->name('menu.apps')->middleware(['auth']);

Route::get('/videos', function () {
    return view('menu.videos');
})->name('menu.videos')->middleware(['auth']);

Route::get('/fotos', function () {
    return view('multimedia');
})->name('menu.fotos')->middleware(['auth']);

Route::get('/noticias', function () {
    return view('menu.noticias');
})->name('menu.noticias')->middleware(['auth']);

//Route::get('/trains', function () {
//    return view('trains.index');
//})->name('trains.index')->middleware(['auth']);

Route::get('/photos', function () {
    return view('multimedia.photos');
})->name('multimedia.photos')->middleware(['auth']);

Route::get('/multimedia/hotels/report', function () {
    return view('multimedia.hotels');
})->name('multimedia.hotels_report')->middleware(['auth']);

/*
Route::get('/dashboard', function () {
    return view('menu.dashboard');
})->name('menu.dashboard')->middleware(['auth']);
*/

Route::get('/register_codcfm/{nrofile}', 'ReminderController@register_codcfm')->name('register_codcfm_external');
Route::get('/register_paxs/{nrofile}', 'ReminderController@register_paxs')->name('register_paxs_external');
Route::get('/register_flights/{nrofile}', 'ReminderController@register_flights')->name('register_flights_external');
Route::get('/register_file/{nrofile}', 'ReminderController@register_file')->name('register_file_external');
Route::post('/search_hotels', 'ReminderController@search_hotels')->name('search_hotels_external');

Route::post('search_markets', 'HomeController@search_markets');
Route::get('/reminders', 'ReminderController@index')->name('reminders');
Route::match(['get', 'post'], '/export_excel', 'Controller@export_excel')->name('export_excel');
Route::match(['get', 'post'], '/export_pdf', 'Controller@export_pdf')->name('export_pdf');
Route::match(['get', 'post'], '/export_docx', 'Controller@export_docx')->name('export_docx');

Route::get('/customers/card', 'CustomerController@card')->name('customers.card');
Route::post('/customers/search_all', 'CustomerController@search_customers')->name('customers.search_all');
Route::post('/customers/{format}', 'CustomerController@filter_data')->name('customers.filter_data');
Route::post('/register_push_notification', 'HomeController@register_push')->name('register_push_notification');
Route::post('/search_productivity', 'BoardController@search_productivity')->name('search_productivity');
Route::get('/board', 'BoardController@index')->name('board.view');
Route::post('/board/dashboard', 'BoardController@dashboard')->name('board.dashboard');
Route::post('/board/executives', 'BoardController@executives')->name('board.executives');
Route::post('/board/files', 'BoardController@files')->name('board.files');
Route::post('/board/file', 'BoardController@file')->name('board.file');
Route::post('/board/notes', 'BoardController@notes')->name('board.notes');
Route::post('/board/save_note', 'BoardController@save_note')->name('board.save_note');
Route::post('/board/delete_note', 'BoardController@delete_note')->name('board.delete_note');
Route::get('/board/paxs', 'BoardController@pending_paxs')->name('board.pending_paxs');
Route::post('/notifications', 'HomeController@search_notifications')->name('notifications');
Route::post('/update_notification', 'HomeController@update_notification')->name('update_notification');
Route::post('/search_passengers', 'ReminderController@search_passengers')->name('reminder.search_passengers');
Route::post('/save_passengers', 'ReminderController@save_passengers')->name('reminder.save_passengers');
Route::post('/load_passengers', 'ReminderController@load_passengers')->name('reminder.load_passengers');
Route::post('/search_flights', 'ReminderController@search_flights')->name('reminder.search_flights');

// Route::post('/import_passenger', 'ReminderController@import_passenger')->name('board.import_passenger');
// Route::post('/{type}_passenger', 'ReminderController@passenger')->name('board.passenger');
Route::post('/board/searchBillings', 'BoardController@detail_billing')->name('board.search_billings');
Route::get('/report_orders', 'BoardController@report_orders')->name('board.report_orders');
Route::post('/search_budget', 'BoardController@search_budget')->name('board.search_budget');
Route::get('/billing_report', 'BoardController@billing_report')->name('board.billing_report');
Route::get('/productivity_report', 'BoardController@productivity_report')->name('board.productivity_report');
Route::post('/board/orders', 'BoardController@orders')->name('board.orders');
Route::post('/board/area_report', 'BoardController@order_area_report')->name('board.area_report');
Route::post('/board/customer_report', 'BoardController@order_customer_report')->name('board.customer_report');
Route::post('/board/ranking_report', 'BoardController@order_ranking_report')->name('board.ranking_report');
Route::post('/board/teams', 'BoardController@teams')->name('board.teams');
Route::post('/board/kams', 'BoardController@kams')->name('board.kams');
Route::post('/board/products', 'BoardController@all_products')->name('board.products');
Route::post('/board/all_regions', 'BoardController@all_regions')->name('board.regions');
Route::post('/board/all_sectors', 'BoardController@all_sectors')->name('board.sectors');
Route::post('/board/all_executives', 'BoardController@all_executives')->name('board.executives');
Route::post('/board/executives_user', 'BoardController@executives_user')->name('board.executives');
Route::post('/board/all_clients', 'BoardController@all_clients')->name('board.all_clients');
Route::post('/board/all_countries', 'ReminderController@all_countries')->name('board.all_countries');
Route::post('/board/all_countries_iso', 'ReminderController@all_countries_iso')->name('board.all_countries_iso');
Route::post('/board/all_doctypes', 'ReminderController@all_doctypes')->name('board.all_doctypes');
Route::post('/board/all_packages', 'BoardController@all_packages')->name('board.all_packages');
Route::post('/board/organize_orders', 'BoardController@organize_orders')->name('board.organize_orders');
Route::match(['get', 'post'], '/search_reminders', 'HomeController@search_reminders')->name('search_reminders');
Route::post('/save_reminder', 'HomeController@save_reminder')->name('save_reminder');
Route::post('/{type}_reminder', 'HomeController@reminder')->name('reminder');
Route::post('/search_users', 'HomeController@search_users')->name('search_users');
Route::post('/count_reminders', 'HomeController@count_reminders')->name('count_reminders');
Route::match(['get', 'post'], '/board/test', 'BoardController@test')->name('board.test');
Route::post('board/payment', 'BoardController@payment')->name('board.payment');
Route::get('board/payment/cancel', 'BoardController@status')->name('board.payment.cancel');
Route::get('board/payment/success', 'BoardController@status')->name('board.payment.success');
Route::get('reports', 'ReportController@dashboard')->name('reports');
Route::post(
    '/reports/dashboard/files_concretados',
    'ReportController@reports_dashboard_files_concretados'
)->name('reports_dashboard_files_concretados');
Route::post(
    '/reports/dashboard/cotizaciones_realizadas',
    'ReportController@reports_dashboard_cotizaciones_realizadas'
)->name('reports_dashboard_cotizaciones_realizadas');
Route::post(
    '/reports/dashboard/report_files',
    'ReportController@reports_dashboard_report_files'
)->name('reports_dashboard_report_files');
Route::get('/reports/excel', 'ReportController@excel')->name('reports.excel');
Route::get('/reports/orders', 'ReportController@orders')->name('reports.orders');
Route::get('/reports/files', 'ReportController@files')->name('reports.files');
Route::get('/reports/files/operations', 'ReportController@files_operations')->name('reports.files.operations');
Route::get('/reports/files/statements', 'ReportController@files_statements')->name('reports.files.statements');
Route::post('/reports/files/search', 'ReportController@search_files')->name('search_files');
Route::post('/files/filter_dashboard', 'ReportController@filter_files_dashboard')->name('filter_files_dashboard');

Route::get('/reports/cosig', 'ReportController@cosig')->name('reports.cosig');
Route::post('/reports/cosig/access', 'ReportController@cosig_access')->name('reports.cosig_access');
Route::post('/reports/cosig/clients', 'ReportController@cosig_clients')->name('reports.cosig_clients');

Route::get('account', 'HomeController@account')->name('home.account');
Route::post('account/change_password', 'HomeController@change_password')->name('home.change_password');
Route::get('account/find_photo', 'HomeController@find_photo')->name('home.find_photo');
Route::post('account/change_photo', 'HomeController@change_photo')->name('home.change_photo');
Route::post('account/delete_photo', 'HomeController@delete_photo')->name('home.delete_photo');
Route::post('account/upload_file', 'HomeController@upload_file')->name('home.upload_file');

// Validación por permisos...
// TOM
Route::get('/users', 'UserController@index')->name('users');
Route::post('/users/searchTOM', 'UserController@users_tom')->name('users.searchTOM');
Route::post('/users/exportTOM', 'UserController@export_tom')->name('users.exportTOM');
Route::post('/users/addTOM', 'UserController@add_tom')->name('users.addTOM');
Route::post('/users/updateTOM', 'UserController@update_tom')->name('users.updateTOM');
Route::post('/users/updateStateTOM', 'UserController@update_state_tom')->name('users.updateStateTOM');
Route::post('/users/vacationsTOM', 'UserController@vacations_tom')->name('users.searchTOM');
Route::post('/users/customersTOM', 'UserController@customersTOM')->name('users.customersTOM');
Route::post('/users/addCustomerTOM', 'UserController@addCustomerTOM')->name('users.addCustomerTOM');
Route::post('/users/removeCustomerTOM', 'UserController@removeCustomerTOM')->name('users.removeCustomerTOM');
Route::post('/users/countriesTOM', 'UserController@countriesTOM')->name('users.countriesTOM');
Route::post('/users/addCountryTOM', 'UserController@addCountryTOM')->name('users.addCountryTOM');
Route::post('/users/removeCountryTOM', 'UserController@removeCountryTOM')->name('users.removeCountryTOM');

Route::get('/orders', 'OrderController@list')->name('orders.index');
Route::post('/orders/search_labels', 'OrderController@search_labels')->name('orders.search_labels');
Route::post('/orders/save_label', 'OrderController@save_label')->name('orders.save_label');
Route::post('/orders/active_label', 'OrderController@active_label')->name('orders.active_label');
Route::post('/orders/remove_label', 'OrderController@remove_label')->name('orders.remove_label');
Route::post('/orders/search_tracings', 'OrderController@search_tracings')->name('orders.search_tracings');
Route::post('/orders/search_emails', 'OrderController@search_emails')->name('orders.search_emails');
Route::post('/orders/reassign', 'OrderController@reassign')->name('orders.reassign');
Route::post('/orders/update', 'OrderController@update')->name('orders.update');
Route::post('/orders/update_response', 'OrderController@update_response')->name('orders.update_response');
Route::post('/orders/update_obs', 'OrderController@update_obs')->name('orders.update_obs');
Route::post('/orders/remove', 'OrderController@remove')->name('orders.remove');
Route::post('/orders/search_status_tracing', 'OrderController@search_status_tracing')->name('search_status_tracing');
Route::post('/orders/search_media_tracing', 'OrderController@search_media_tracing')->name('search_media_tracing');
Route::post('/orders/save_tracing', 'OrderController@save_tracing')->name('save_tracing');
Route::post('/orders/find_file_tracing', 'OrderController@find_file_tracing')->name('orders.search_tracings');
Route::post(
    '/orders/find_content_tracing',
    'OrderController@find_content_tracing'
)->name('orders.find_content_tracings');
Route::get('/dashboard', 'OrderController@index')->name('orders.index');
Route::post('/total_orders', 'OrderController@total')->name('orders.total');
Route::post('/search_orders', 'OrderController@search')->name('orders.search');
Route::post('/dashboard', 'OrderController@dashboard')->name('orders.dashboard');
Route::post('/report_by_executive', 'OrderController@report_by_executive')->name('orders.report_by_executive');
Route::get('/orders/client/{code}', 'OrderController@searchPendByClient')->name('orders.searchPendByClient');
Route::post('/order/relate', 'OrderController@relate')->name('orders.relate');

Route::get('/board/details/{file}', 'BoardController@details')->name('details');
Route::get('/board/detailsWS/{file}', 'BoardController@detailsWS')->name('detailsWS');

// Prototipo FILE
//Route::get('/files/dashboard/{file}', 'FileController@dashboard')->name('files.dashboard');
Route::get('/files/dashboard/{file}', function ($file) {
    if (\Illuminate\Support\Facades\Auth::guest()) {
        return view('auth.login');
    }
    $data = ['nrofile' => $file];
    return view('files.dashboard')->with('data', $data);
})
    ->name('files.dashboard')->middleware(['guest']);
Route::post('/order_services_file', 'FileController@order_services_file')->name('files.order_services_file');

/* CALENDARIO INCA */
Route::get('/calendario_inca', function () {
    if (\Illuminate\Support\Facades\Auth::guest()) {
        return view('auth.login');
    }
    return view('menu.inca_calendar');
})->name('calendario_inca')->middleware(['auth']);


/* CONSULTA DE FILES */
Route::post('filter_files', 'TrackingController@filter')->name('filter_files');
Route::get('/consulta_files', 'TrackingController@index')->name('consulta_files');
Route::post('/orders/find', 'OrderController@find')->name('orders_find');
Route::get(
    '/consulta_files/searchByDates/{codCli}/{fecIni}/{fecOut}',
    'TrackingController@searchByDates'
)->name('searchByDates');
Route::get('/consulta_files/searchByDescription/{codCli}/{description}', 'TrackingController@searchByDescription')->name('searchByDescription');
Route::get('/consulta_files/searchByLocator/{codCli}/{locator}', 'TrackingController@searchByLocator')->name('searchByLocator');
Route::get('/consulta_files/searchByFile/{codCli}/{nroRef}', 'TrackingController@searchByFile')->name('searchByFile');
Route::get(
    '/consulta_files/searchByOrder/{codCli}/{nroRes}',
    'TrackingController@searchByOrder'
)->name('searchByOrder');
Route::get('/consulta_files/getServices/{nroRef}/{lang}', 'TrackingController@getServices')->name('getServices');
Route::get(
    '/consulta_files/getSkeleton/{codCli}/{nroRef}/{nroLoc}/{lang}',
    'TrackingController@getSkeleton'
)->name('getSkeleton');
Route::get(
    '/consulta_files/getScheduled/{codCli}/{nroRef}/{lang}',
    'TrackingController@getScheduled'
)->name('getScheduled');
Route::get('/consulta_files/getPaxs/{nroRef}', 'TrackingController@getPaxs')->name('getPaxs');
Route::get('/consulta_files/getHotels/{codCli}/{nroRef}', 'TrackingController@getHotels')->name('getHotels');
Route::get('/consulta_files/getInvoice/{codCli}/{nroRef}', 'TrackingController@getInvoice')->name('getInvoice');
Route::get('/consulta_files/getPdfInvoice/{codCli}/{nroRef}', 'TrackingController@getPdfInvoice')->name('getPdf');
Route::post('/consulta_files/getPortadas/{lang}', 'TrackingController@getPortadas')->name('getPortadas');
// Route::get('/consulta_files/getItinerary/{nroRef}/{imgCod}/{lang}', 'TrackingController@getItinerary')->name('getItinerary');
Route::get(
    '/consulta_files/getItinerary/{nroRef}/{codCli}/{withHeader}/{withClientLogo}/{imgCod}/{lang}/{portada}',
    'TrackingController@getItinerary'
)->name('getItinerary');
Route::get('/consulta_files/getGuides/{nroRef}/{lang}', 'TrackingController@getGuides')->name('getGuides');
Route::get('/consulta_files/getClient/{id}', 'TrackingController@getClient')->name('getClient');
Route::get('/consulta_files/getClientcart/add/{id}', 'TrackingController@getClient')->name('getClient');
Route::get('/consulta_files/getFlights/{nroRef}', 'TrackingController@getFlights')->name('getFlights');
Route::get('/consulta_files/getFlightsData', 'TrackingController@getFlightsData')->name('getFlightsData');
Route::post('/consulta_files/saveFlight', 'TrackingController@saveFlight')->name('saveFlight');
Route::post('/consulta_files/removeFlight', 'TrackingController@removeFlight')->name('removeFlight');
Route::get('/consulta_files/test', 'TrackingController@test')->name('test');

//
/*
Route::get('/consulta_files', function (){
    return view('menu.consulta_files');
})->name('menu.consulta_files')->middleware(['auth']);
*/

/*
Route::get('/board/details', function (){
    return view('board.details');
})->name('details')->middleware(['auth']);
*/
/*  End Executive-board  */

Route::get('/app_mobile', 'AppMobileController@index')->name('app_mobile')->middleware(['auth']);

/*
Route::get('/orders', function () {
    return view('menu.orders');
})->name('menu.orders')->middleware(['auth']);
*/

/*
Route::get('/reports', function () {
    return view('menu.reports');
})->name('reports')->middleware(['auth']);
*/

Route::get('/bokun', function () {
    return view('menu.bokun');
})->name('bokun')->middleware(['auth']);

Route::get('/cart_details/view', 'CartDetailController@index')->name('cart_details')->middleware(['auth']);

Route::get(
    '/cart_details/service',
    'CartController@getCartContentFrontEnd'
)->name('cart_details.service')->middleware(['auth']);

Route::post('cart/update/item', 'CartController@updateItemCart')->name('cart.get_item');
Route::post('cart/services/supplement/add', 'CartController@addSupplementServiceCart')->name('cart.add_supplement');

Route::post('cart/delete/item', 'CartController@deleteSupplementItemCart')->name('cart.delete_supplement_item');
Route::post(
    'cart/services/supplement',
    'CartController@deleteSupplementServiceItemCart'
)->name('cart.delete_supplement_service_item');

Route::get('cart', 'CartController@getCart')->name('cart');

Route::put(
    'cart/multiservice/removed',
    'CartController@update_multiservice_removed'
)->name('update_multiservice_removed');

Route::put(
    'cart/service/reservation_time',
    'CartController@update_service_reservation_time'
)->name('update_service_reservation_time');

Route::put(
    'cart/multiservice/substitute',
    'CartController@update_multiservice_substitute'
)->name('update_multiservice_substitute');

Route::delete('cart/{id_item}', 'CartController@deleteItemCart')->name('cart.delete');

Route::post('cart/add', 'CartController@addCart')->name('cart.add');

Route::post('cart/add_best_option', 'CartController@addBestOption')->name('cart.add_best_option');

Route::post('cart/cancel/rates', 'CartController@deleteItemsCart')->name('cart.cancel_rates');
Route::post('cart/cancel/clear', 'CartController@clearItemsCart')->name('cart.cancel_rates');

Route::delete('cart/content/delete', 'CartController@destroyCart')->name('cart.destroy');

Route::post('cart/content/change/item', 'CartController@change_item')->name('cart.change.item');

//Route::get('/reservation/personal-data', 'ReservationController@personaData')->name('reservcation.personal_data');
Route::get('lang/{lang}', function ($lang) {
    \App::setLocale($lang);
    session(['lang' => $lang]);
})->where([
    'lang' => 'en|es|pt'
]);
Route::get('/reservations/personal-data', function () {
    return view('reservations.personal-data');
})->name('reservations.personal_data')->middleware(['auth']);

Route::get('/reservations/{file_code}', function () {
    session()->forget('passengers_list');
    return view('reservations.confirmation');
})->name('reservations.show')->middleware(['auth']);

Route::get('/reportes-reservas', function () {
    return view('reports.index');
})->name('reportes_reservas')->middleware(['auth']);

Route::get('/report-reservations', function () {
    return view('help_desk.reservations');
})->name('report-reservations')->middleware(['auth']);

Route::get('/packages', 'PackageController@index')->name('packages')->middleware(['auth']);

Route::get('/packages/details', 'PackageController@details')->name('packages.details')->middleware(['auth']);

Route::get('/packages/cotizacion', 'PackageController@cotizacion')->name('packages.cotizacion')->middleware(['auth']);
Route::get('/packages/demo', 'PackageController@demo');


Route::get(
    '/packages/details/reserve',
    'PackageController@details_reserve'
)->name('packages.details_reserve')->middleware(['auth']);

Route::get('/modules', 'ModuleController@index')->name('modules')->middleware(['auth']);
Route::post('/modules', 'ModuleController@store')->name('modules.store')->middleware(['auth']);
Route::get(
    '/modules/{module_id}/translations',
    'ModuleController@translations'
)->name('modules.translations')->where(['module_id' => '[0-9]+'])->middleware('auth');
Route::get(
    '/modules/{module_id}/translations/create',
    'ModuleController@translations_create'
)->name('modules.translations_create')->where(['module_id' => '[0-9]+'])->middleware('auth');
Route::get(
    '/translations/{module_id}',
    'TranslationFrontendController@index'
)->name('translations')->where(['module_id' => '[0-9]+'])->middleware('auth');

Route::get(
    '/translation/{lang}/slug/{slug}',
    'TranslationFrontendController@getBySlug'
)->name('translations.getBySlug')->defaults('lang', 'es')->defaults('slug', 'global');

Route::post(
    '/translations/{module_id}',
    'TranslationFrontendController@store'
)->name('translations.store')->where(['module_id' => '[0-9]+'])->middleware('auth');
Route::put(
    '/translations/{module_id}',
    'TranslationFrontendController@update'
)->name('translations.update')->where(['module_id' => '[0-9]+'])->middleware('auth');


Route::get(
    '/packages/micotizacion',
    'PackageController@micotizacion'
)->name('packages.micotizacion')->middleware(['auth']);

Route::get('/quotes/permissions', 'QuotesController@permissions')->name('quotes.permissions');

Route::get('/contents', 'ContentsController@index')->name('contents')->middleware(['auth']);

Route::get('/test', function () {
    return view('customers.card');
})->name('test');

Route::get('/testing', function () {
    return view('testing');
});

Route::get('/error', function () {
    return view('error');
})->name('error');

Route::get('/stats', 'StatsController@index');
Route::get('/stats/login', 'StatsController@login');
Route::post('/search_stats_clients', 'StatsController@search');

//----------------------------------INICIO RUTAS PARA CLIENTE---------------------------

//----------------------------------FIN RUTAS PARA CLIENTE---------------------------
