<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\RatesCostsController;
use App\Http\Services\Controllers\HotelsReservationsController;
use Barryvdh\Cors\HandleCors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('translations/json', 'TranslationsController@getJson');

Route::get('/board', 'BoardController@search_board');

// Modal Passengers..
Route::post('/search_passengers', 'PaxsController@search_passengers');
Route::post('/import_passengers', 'PaxsController@import_passengers');
Route::get('/passengers-countries', 'PaxsController@getCountries');
Route::get('/country/{iso}/cities_ifx', 'PaxsController@getCitiesByIsoCountry');
Route::post('/{type}_passenger', 'PaxsController@passenger');
// Modal Flights..
Route::post('/search_flights/{nrofile}', 'FlightController@search_flights');
Route::post('/add_flight/{nrofile}', 'FlightController@add_flight');
Route::post('/save_flight/{nrofile}/{nroite}', 'FlightController@save_flight');
Route::get('flights/origins/{type}', 'FlightController@destinations');
Route::get('flights/destinations/{type}', 'FlightController@destinations');
Route::get('flights/airlines', 'FlightController@airlines');
Route::post('flights', 'FlightController@save_flight');
Route::delete('flights/{flight_id}', 'FlightController@remove_flight');

Route::post('/files/quote', 'FileController@detail_file_quote');
Route::post('/files/{nrofile}/passengers', 'FileController@save_passengers_file');
Route::post('/files/{nrofile}/passengers/service/{nroite}', 'FileController@save_service_passengers_file');
Route::post('/files/{nrofile}/service', 'FileController@save_service_file');
Route::post('/files/{nrofile}/service/{nroite}', 'FileController@update_service_file');
Route::delete('/files/{nrofile}/service/{nroite}', 'FileController@delete_service_file');
Route::get('/files', 'FileController@search');
Route::get('/files/{nrofile}', 'FileController@show');
Route::post('/files/{nrofile}', 'FileController@update');

Route::post('/files/{nrofile}/accommodations', 'FileAccommodationsController@store');
Route::post('/files/{nrofile}/accommodations/general', 'FileAccommodationsController@store_general');
Route::get('/files/{nrofile}/accommodations/service', 'FileAccommodationsController@search_by_service');
Route::get('/files/services/{file_service_id}/accommodations/room/{number_room}', 'FileAccommodationsController@search_room');

Route::get('/files/{nrofile}/passengers', 'ReservationPassengersController@search_by_file_number');

Route::get('/files/{nrofile}/services', 'FileServicesController@search_by_file_number');
Route::get('/files/{nrofile}/services/code/{codsvs}', 'FileServicesController@search_by_service_code');
Route::get('/files/{nrofile}/services/code/{codsvs}/force', 'FileServicesController@search_by_service_code_backup');
Route::post('/files/{nrofile}/services/confirmation_codes', 'FileServicesController@save_confirmation_codes');
Route::post('/files/services/confirmation_codes/notification', 'FileServicesController@send_notification_confirmation_codes');
Route::get('/files/{nrofile}/services/{nroite}/components', 'FileServicesController@search_components');

Route::post('/flights/origins', 'PackageServicesController@search_destinations');
Route::post('/flights/airlines', 'PackageServicesController@search_airlines');
Route::match(['get', 'post'], '/flight_stats', 'FileController@flight_stats');
Route::match(['get', 'post'], '/flight_info', 'FileController@flight_info');
Route::match(['get', 'post'], '/flight_info_dos', 'FileController@flight_info_dos');
Route::post('/toggle_view_hotels', 'FileController@toggle_view_hotels');

Route::post('/cancel_hotel_files', 'FileController@cancel_hotel');
Route::post('/search_hotel_files', 'FileController@search_hotel');

Route::post('/masi/update_time_notification', 'MasiController@update_time_notification');
Route::post('/masi/search_time_notification', 'MasiController@search_time_notification');
Route::post('/masi/search_logs', 'MasiController@search_logs');
Route::post('/masi/search_all_logs', 'MasiController@search_all_logs');
Route::post('/masi/search_paxs_by_file', 'MasiController@search_paxs_by_file');
Route::post('/masi/mailing_preview', 'MasiController@preview_message_wsp');
Route::post('/masi/mailing', 'MasiController@mailing');
Route::get('/masi/test/{type}', 'MasiController@test_mailing');
Route::post('masi/quantity_people', 'MasiStatisticsController@quantity_people');
Route::get('/masi/link_itinerary', 'MasiController@getLinkItinerary');
Route::post('/sync/skeleton', 'MasiController@sync_skeleton')->name('sync_skeleton');

Route::get('/public_link/itinerary', 'Controller@wordItineraryPublic');

Route::middleware('auth:api')->group(function () {
    Route::post('/search_services_by_types', 'FileController@search_services_by_types');
    Route::post('/search_services_component', 'FileController@search_services_component');
    Route::post('/search_rates_services', 'FileController@search_rates_services');
    Route::post('/save_component', 'FileController@save_component');
    Route::post('/delete_component', 'FileController@delete_component');
    Route::post('/services/{code}/bastar', 'FileController@search_bastar');
    Route::post('/services/{code}/remarks', 'FileController@search_remarks');
    Route::post('/services/{code}/restrictions', 'FileController@search_restrictions');
    Route::post('/update_passengers_file', 'FileController@update_passengers_file');
    Route::post('/reminders', 'ReminderController@search_all');
    Route::post('/save_reminder', 'ReminderController@save_reminder');
    Route::post('/delete_reminder', 'ReminderController@delete_reminder');
    Route::get('/reminders/ifx', 'ReminderController@search_ifx');
    Route::put('/reminders/ifx', 'ReminderController@update_ifx');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('password', 'AuthController@password');
    Route::get('me', 'AuthController@me');
    Route::post('logout', 'AuthController@logout');
    Route::post('/a3/channel-code-hyperguest', 'ProcessesForA3Files@channel_code_hyperguest');
    Route::get('/a3/executives', 'BoardController@search_executives');
    Route::get('/a3/clients', 'BoardController@search_clients');
    Route::get('/a3/boss_executives', 'BoardController@search_boss_executives');
    Route::get('/a3/equivalence_services', 'BoardController@search_equivalence_services');
    Route::get('/a3/master_services', 'BoardController@search_master_services');
    Route::get('/a3/info-by-communication', 'BoardController@infoByCommunication');
    Route::get('/a3/info-client-by-code', 'BoardController@infoClientByCode');
    Route::post('/a3/services-details', 'BoardController@servicesDetails');
    Route::post('user/search/executive', 'UsersController@userExecutive');
    Route::post('user/search/executiveAndSeller', 'UsersController@userExecutiveSeller');
    Route::get('user/search/executive/filter', 'UsersController@searchExecutives');
    Route::get('team/{team_id}/executives', 'UsersController@getExecutivesByTeam');
    Route::get('user/refresh', 'AuthController@refresh');
    Route::resource('users', 'UsersController');
    Route::put('user/{id}/status', 'UsersController@updateStatus');
    Route::get('user/code/{user_code}', 'UsersController@getByCode');

    Route::post('user/notification/token', 'UserNotificationsController@store');
    Route::get('user/notification/token', 'UserNotificationsController@search');

    Route::get('user/permissions', 'AuthController@permissions');
    Route::get('user/details', 'UsersController@getUsersDetails');

    Route::get('country/selectbox', 'CountriesController@selectBox');
    Route::resource('countries', 'CountriesController');

    Route::get('state/galleries/{id}', 'GaleriesController@indexStateGallery');
    Route::post('state/gallery/max/position', 'GaleriesController@maxPositionStateGallery');
    Route::get('state/getstates/{id}/{lang}', 'StatesController@getStates');
    Route::get('state/selectbox', 'StatesController@selectBox');
    Route::resource('states', 'StatesController');


    Route::get('city/getcities/{id}/{lang}', 'CitiesController@getCities');
    Route::get('cities/peru/vue_select', 'CitiesController@getCitiesPeruByVueSelect');
    Route::get('cities/informix', 'CitiesController@from_informix');
    Route::get('city/selectbox', 'CitiesController@selectBox');
    Route::get('cities/orders/services/rate', 'CitiesController@service_orders_rate');
    Route::put('cities/orders/services/rate/update', 'CitiesController@update_service_order_city');
    Route::get('cities/orders/hotels/rate', 'CitiesController@hotel_orders_rate');
    Route::put('cities/orders/hotels/rate/update', 'CitiesController@update_hotel_order_city');
    Route::resource('cities', 'CitiesController');

    Route::get('district/getdistricts/{id}/{lang}', 'DistrictsController@getDistricts');
    Route::resource('districts', 'DistrictsController');

    Route::get('zone/getzones/{id}/{lang}', 'ZonesController@getZones');
    Route::get('zone/states/{id}/{lang}', 'ZonesController@getZonesState');
    Route::resource('zones', 'ZonesController');

    Route::get('currency/selectbox', 'CurrenciesController@selectBox');
    Route::resource('currencies', 'CurrenciesController');

    Route::get('languages/array', 'LanguagesController@getArrayLanguages');
    Route::post('languages/image', 'LanguagesController@uploadImage');
    Route::delete('languages/image/{id}', 'LanguagesController@removeImage');
    Route::get('language/selectbox', 'LanguagesController@selectBox');
    Route::put('language/update/{id}/state', 'LanguagesController@updateStatus');
    Route::get('languages/all', 'LanguagesController@allLanguage');
    Route::resource('languages', 'LanguagesController');

    Route::get('hotelcategory/selectbox', 'HotelCategoriesController@selectBox');
    Route::resource('hotelcategories', 'HotelCategoriesController');
    Route::get('typeclass/allotment', 'TypesClassController@selectBoxForAllotment');
    Route::get('typeclass/selectbox', 'TypesClassController@selectBox');
    Route::get('typeclass/quotes/selectbox', 'TypesClassController@selectBoxQuote');
    Route::get('type_classes_hotel/selectbox2', 'TypesClassController@selectBox2');
    Route::resource('typesclass', 'TypesClassController');

    Route::put('chain/update/{id}/state', 'ChainsController@updateStatus');
    Route::get('chain/selectbox', 'ChainsController@selectBox');
    Route::resource('chains', 'ChainsController');

    Route::resource('currencies', 'CurrenciesController');

    Route::get('meals/selectBox', 'MealsController@selectBox');
    Route::resource('meals', 'MealsController');

    Route::put('amenities/gallery', 'GaleriesController@galleryAmenity');
    Route::delete('/amenities/image/{id}', 'GaleriesController@removeImageAmenity');
    Route::get('amenities/selectbox', 'AmenitiesController@selectBox');
    Route::put('amenities/update/{id}/state', 'AmenitiesController@updateStatus');
    Route::post('amenities/import', 'AmenitiesController@import');
    Route::resource('amenities', 'AmenitiesController');

    Route::put('channels/update/{id}/state', 'ChannelsController@updateStatus');
    Route::get('channels/by/rate_plan/{id}', 'ChannelsController@getChannelIdByRatePlan');
    Route::get('channels/selectBox', 'ChannelsController@selectBox');
    Route::get('channels/selected', 'ChannelsController@selected');
    Route::get('channels/selectHotelBox', 'ChannelsController@selectHotelsBox');
    Route::get('channels/inventory', 'ChannelsController@getChannelsInventory');
    Route::resource('channels', 'ChannelsController');

    Route::put('facilities/gallery', 'GaleriesController@galleryFacilities');
    Route::delete('facilities/image/{id}', 'GaleriesController@removeImageFacility');
    Route::put('facility/update/{id}/state', 'FacilitiesController@changeStatus');
    Route::resource('facilities', 'FacilitiesController');

    Route::get('hotel_type/selectbox', 'HotelTypesController@selectBox');
    Route::resource('hotel_types', 'HotelTypesController');

    Route::get('room_types/selectBox', 'RoomTypesController@selectBox');
    Route::get('room_types/allotments', 'RoomTypesController@allotments');

    Route::resource('room_types', 'RoomTypesController');


    Route::resource('taxes', 'TaxesController');

    Route::resource('translations', 'TranslationsController');

    Route::resource('galeries', 'GaleriesController');
    Route::post('galeries/image', 'GaleriesController@uploadImage');
    Route::delete('galeries/image/{id}', 'GaleriesController@removeImage');
    Route::put('galeries/update/image/{id}/state', 'GaleriesController@updateState');
    Route::put('galeries/update/image/positions', 'GaleriesController@updatePositions');
    Route::post('galeries/max/position', 'GaleriesController@maxPosition');
    Route::post('galeries/add/urls', 'GaleriesController@addUrls');
    Route::put('galeries/update/{id}/status', 'GaleriesController@changeStatus');
    Route::resource('galeries', 'GaleriesController');

    Route::post('rooms/{id}/uses/report', 'RoomsController@report_uses');
    Route::post('rooms/by/hotel/releases', 'RoomsController@roomsByHotelRelease');
    Route::put('rooms/{room_id}/order', 'RoomsController@update_orders');
    Route::put('rooms/update/{id}/state', 'RoomsController@updateState');
    Route::put('rooms/update/{id}/see_in_rates', 'RoomsController@updateSeeInRates');
    Route::get('rooms/selectBox', 'RoomsController@selectBox');
    Route::post('rooms/with/rates', 'RoomsController@roomsWithRates');
    Route::post('rooms/by/hotel', 'RoomsController@roomsByHotel');
    Route::resource('rooms', 'RoomsController');

    //Modulo de Releases
    Route::resource('releases', 'ReleaseController');

    Route::post('inventory/hotel', 'InventoriesController@index');
    Route::post('inventory/add', 'InventoriesController@store');
    Route::post('inventory/locked/days', 'InventoriesController@lockedDays');
    Route::post('inventory/enabled/days', 'InventoriesController@enabledDays');
    Route::post('inventory/process/range/days', 'InventoriesController@storeInventoryByDateRange');
    Route::post('inventory/blocked/range/days', 'InventoriesController@blockedInventoryByDateRange');
    Route::post('inventory/history', 'InventoriesController@history');
    Route::post('inventory/by/channels', 'InventoriesController@inventoryByChannels');

    Route::post('roles/{id}/permissions', 'RolesController@permissions')->where('id', '[0-9]+');
    Route::get('roles/list', 'RolesController@search');
    Route::get('roles/selectBox', 'RolesController@selectBox');
    Route::put('role/{id}/status', 'RolesController@updateStatus');
    Route::get('reports/roles-permissions', 'RolesController@exportAllRolesPermissions')
        ->middleware('permission:roles.read');

    Route::get('reports/roles-permissions/{id}', 'RolesController@exportRolePermissions')
        ->where('id', '[0-9]+')
        ->middleware('permission:roles.read');
    Route::get('reports/roles-users', 'RolesController@exportAllRolesUsers');
    Route::resource('roles', 'RolesController');

    Route::get('permissions/selectBox', 'PermissionsController@selectBox');
    Route::get('permissions/treeView', 'PermissionsController@treeView');
    Route::get('permissions/fromRole/{id}', 'PermissionsController@fromRole')->where('id', '[0-9]+');
    Route::get('permissions/name/{name}', 'PermissionRolesController@like_name');
    Route::resource('permissions', 'PermissionsController');
    Route::put('hotels/update/favorite', 'HotelsController@updateFavorite');
    Route::get('hotel/{id}/rooms', 'RoomsController@roomsHotel');
    Route::put('update/check_inventory/room', 'RoomsController@updateCheckInventory');
    Route::get('hotel/selectbox', 'HotelsController@selectBox');

    Route::put('hotel/{hotel_id}/packages/rates', 'HotelsController@updateRatesInPackages');

    Route::post('hotel/search/client', 'HotelsController@hotelClient');

    Route::post('hotel/search/up_selling', 'HotelsController@hotelUpSelling');
    Route::delete('hotel/logo/image/{id}', 'GaleriesController@removeImageLogo');
    Route::post('hotel/gallery/max/position', 'GaleriesController@maxPositionHotelGallery');
    Route::put('hotel/gallery/logo/', 'GaleriesController@galleryHotelLogo');
    Route::delete('hotel/gallery/image/{id}', 'GaleriesController@removeImage');

    Route::get('hotels/generate_array/{hotel_year}', 'ExportController@hotelGenerateArray')->where('hotel_year', '[0-9]+');
    Route::get('hotels/multimedia/report', 'HotelsController@getHotelsMultimediaReport');
    Route::put('hotels/{id}/status', 'HotelsController@updateStatus');
    Route::get('hotels/galleries/{id}', 'GaleriesController@indexHotelGallery');
    Route::put('hotels/{id}/status', 'HotelsController@updateStatus');
    Route::post('hotels/{id}/name', 'HotelsController@getHotelName');
    Route::get('hotels/filter', 'HotelsController@access');
    Route::get('hotels/{id}/uses', 'HotelsController@get_uses');
    Route::post('hotels/{id}/uses/report', 'HotelsController@report_uses');
    Route::post('hotels/notify_new_hotel/{id}', 'HotelsController@notify_new_hotel');

    Route::get('hotel/ubigeo/selectbox/{lang}', 'HotelsController@getUbigeoHotel');
    Route::get('hotels/orders/rate', 'HotelsController@orders_rate');
    Route::put('hotels/orders/rate/update', 'HotelsController@update_order');
    Route::post('hotels/search-import', 'HyperguestHotelsController@search');
    Route::post('hotels/import-from-hyperguest', 'HyperguestHotelsController@importHotels');
    Route::get('hotels/import-batches', 'HyperguestHotelsController@getBatches');
    Route::get('hotels/import-batch/{id}/status', 'HyperguestHotelsController@getBatchStatus');
    Route::put('hotels/import-batch/{id}/viewed', 'HyperguestHotelsController@markAsViewed');
    Route::resource('hotels', 'HotelsController');
    Route::get('hotels/{id}/channels', 'HotelsController@getChannels');

    Route::put('hotel_users/update/{id}/state', 'HotelUsersController@updateStatus');
    Route::resource('hotel_users', 'HotelUsersController');

    Route::post('packages/selected/change', 'PackagesController@changePackagesSelected');
    Route::post('extensions', 'PackagesController@getExtensions');
    Route::post('packages/active', 'PackagesController@getActive');
    Route::post('packages/best_sellers', 'PackagesController@best_sellers');
    Route::post('packages/selected', 'PackagesController@savePackagesSelected');
    Route::get('packages/services', 'PackagesController@get_services');
    Route::get('packages/selected', 'PackagesController@getPackagesSelected');
    Route::post('packages/save/reserve_details', 'PackagesController@savePackageReserveDetails');
    Route::get('packages/save/reserve_details', 'PackagesController@getPackageReserveDetails');

    Route::get('packages/groups', 'TagGroupController@index');
    Route::post('packages/groups', 'TagGroupController@store');
    Route::delete('packages/groups/{group_id}', 'TagGroupController@destroy')->where(['group_id' => '[0-9]+']);
    Route::put('packages/groups/{group_id}', 'TagGroupController@update')->where(['group_id' => '[0-9]+']);
    Route::get('packages/groups/{group_id}', 'TagGroupController@show')->where(['group_id' => '[0-9]+']);

    Route::get('packages/groups/{group_id}/tags', 'TagsController@index')->where(['group_id' => '[0-9]+']);
    Route::post('packages/groups/{group_id}/tags', 'TagsController@store')->where(['group_id' => '[0-9]+']);
    Route::delete('packages/groups/{group_id}/tags/{tag_id}', 'TagsController@destroy')->where([
        'group_id' => '[0-9]+',
        'tag_id' => '[0-9]+'
    ]);
    Route::put('packages/groups/{group_id}/tags/{tag_id}', 'TagsController@update')->where([
        'group_id' => '[0-9]+',
        'tag_id' => '[0-9]+'
    ]);
    Route::get('packages/groups/{group_id}/tags/{tag_id}', 'TagsController@show')->where([
        'group_id' => '[0-9]+',
        'tag_id' => '[0-9]+'
    ]);
    Route::get('package/{package_id}/fixed_outputs', 'FixedOutputsController@index');
    Route::post('package/{package_id}/fixed_outputs', 'FixedOutputsController@store');
    Route::put('package/{package_id}/fixed_outputs/{fixed_output_id}/state', 'FixedOutputsController@updateState');
    Route::delete('package/{package_id}/fixed_outputs/{fixed_output_id}', 'FixedOutputsController@destroy');
    Route::post('package/{package_id}/extensions/unassigned', 'PackagesController@packageExtensions');

    Route::post('package/{package_id}/extensions/assigned', 'PackageExtensionsController@index');
    Route::post('package/{package_id}/extensions', 'PackageExtensionsController@store');
    Route::post('package/{package_id}/extensions/all', 'PackageExtensionsController@storeAll');
    Route::post('package/{package_id}/extensions/inverse', 'PackageExtensionsController@storeInverse');
    Route::post('package/{package_id}/extensions/inverse/all', 'PackageExtensionsController@storeInverseAll');

    Route::get('package/{package_id}/schedules', 'PackageSchedulesController@index');
    Route::post('package/{package_id}/schedules', 'PackageSchedulesController@store');
    Route::put('package/{package_id}/schedules/{schedule_id}/state', 'PackageSchedulesController@updateState');
    Route::delete('package/{package_id}/schedules/{schedule_id}', 'PackageSchedulesController@destroy');

    Route::get('providers/selectBox', 'ProvidersController@selectBox');
    Route::resource('suppliers', 'ProvidersController');
    Route::put('suppliers/{id}/status', 'ProvidersController@updateStatus');

    Route::post('package/gallery/max/position', 'GaleriesController@maxPositionPackageGallery');

    Route::get(
        'packages/{id}/category/{category_id}/services/equivalences',
        'PackagesController@getServicesEquivalences'
    );
    Route::get('packages/galleries/{id}', 'GaleriesController@indexPackageGallery');
    Route::put('packages/{id}/status', 'PackagesController@updateStatus');
    Route::put('packages/{id}/recommended', 'PackagesController@updateRecommended');
    Route::put('packages/{id}/free_sale', 'PackagesController@updateFreeSale');
    Route::put('packages/{id}/modify', 'PackagesController@updateAllowModify');
    Route::put('packages/{id}/fixed_prices', 'PackagesController@enabledFixedPrices');
    Route::get('packages/{id}/configurations', 'PackagesController@getConfigurations');
    Route::put('packages/{id}/configurations', 'PackagesController@updateConfigurations');
    Route::post('package/{id}/taxes', 'PackagesController@updateTax');
    Route::post('package/{id}/duplicate', 'PackagesController@duplicatePackage');
    Route::get('package/{id}/duplication-info', 'PackagesController@duplicatationInfo');
    Route::resource('packages', 'PackagesController');

    Route::get('package/{package_id}/rates', 'PackageRatesController@index');
    Route::post('package/{package_id}/rates', 'PackageRatesController@store');
    Route::put('package/{package_id}/rates/{rate_id}', 'PackageRatesController@update');
    Route::delete('package/{package_id}/rates/{rate_id}', 'PackageRatesController@destroy');
    Route::get('package/{package_id}/rates/{rate_id}', 'PackageRatesController@show');

    Route::get('package/{package_id}/children', 'PackageChildrenController@index');
    Route::post('package/{package_id}/children', 'PackageChildrenController@store');
    Route::put('package/{package_id}/children/{child_id}/status', 'PackageChildrenController@updateStatus');
    Route::put('package/children/{id}/percentage', 'PackageChildrenController@updatePercentage');
    Route::delete('package/{package_id}/children/{child_id}', 'PackageChildrenController@destroy');

    Route::get('package/{package_id}/translations', 'PackageTranslationsController@index');
    Route::put('package/{package_id}/translations/{translation_id}', 'PackageTranslationsController@update');

    Route::get('package/{package_id}/customers', 'PackageCustomersController@index');
    Route::post('package/{package_id}/customers', 'PackageCustomersController@store');
    Route::put('package/{package_id}/customers/{customer_id}/status', 'PackageCustomersController@updateStatus');
    Route::delete('package/{package_id}/customers/{customer_id}', 'PackageCustomersController@destroy');

    Route::get('package/categories/{plan_rate_id}', 'PackagePlanRatesController@getPlanRatesCategories');
    Route::get('package/type_service/{package_id}', 'PackagePlanRatesController@getPlanRatesTypeService');
    Route::get('plan_rates/export/list', 'PackagePlanRatesController@searchForExport');

    Route::get('package/{package_id}/plan_rates/selectBox', 'PackagePlanRatesController@selectBox');
    Route::get('package/{package_id}/plan_rates/', 'PackagePlanRatesController@index');
    Route::post('package/{package_id}/plan_rates/copy', 'PackagePlanRatesController@copy');
    Route::get('package/plan_rates/{plan_rate_id}', 'PackagePlanRatesController@show');
    Route::get('package/plan_rates/{plan_rate_id}/excel/{service_type_id}', 'PackagePlanRatesController@dataExcel');
    Route::put('package/plan_rates/{plan_rate_id}/status', 'PackagePlanRatesController@updateStatus');
    Route::put('package/plan_rates/{plan_rate_id}/', 'PackagePlanRatesController@update');
    Route::post('package/plan_rates/', 'PackagePlanRatesController@store');
    Route::post('package/plan_rates/categories/rates', 'PackagePlanRatesController@searchRates');
    Route::post('package/plan_rates/categories/copy', 'PackagePlanRatesController@copyCategories');
    Route::delete(
        'package/package_plan_rate_category/{plan_rate_category_id}',
        'PackagePlanRatesController@destroyCategory'
    );
    Route::get('package/package_dynamic_rates/{category_id}/', 'PackageDynamicRatesController@index');
    Route::post('package/package_dynamic_rates/', 'PackageDynamicRatesController@store');
    Route::delete('package/package_dynamic_rates/{id}', 'PackageDynamicRatesController@destroy');

    Route::get('package/package_dynamic_sale_rates/{category_id}/', 'PackageDynamicSaleRatesController@index');
    Route::post('package/package_dynamic_sale_rates/', 'PackageDynamicSaleRatesController@store');
    Route::put('package/package_dynamic_sale_rates', 'PackageDynamicSaleRatesController@update');

    Route::get(
        'package/package_plan_rate_category/{plan_rate_category_id}/export/passengers',
        'PackageServicesController@passengersExport'
    )->where('quote_id', '[0-9]+');
    Route::post(
        'package/package_plan_rate_category/{plan_rate_category_id}/hotel/room',
        'PackageServicesController@storeHotelRoom'
    );
    Route::delete(
        'package/package_plan_rate_category/{plan_rate_category_id}/hotel/room',
        'PackageServicesController@destroyHotelRoom'
    );
    Route::post(
        'package/package_plan_rate_category/flight/rate',
        'PackageServicesController@storeFlight'
    );
    Route::delete(
        'package/package_plan_rate_category/{plan_rate_category_id}/flight',
        'PackageServicesController@destroyFlight'
    );
    Route::post(
        'package/package_plan_rate_category/hotel/searchByCategories',
        'PackageServicesController@searchHotelsByCategories'
    );
    Route::post(
        'package/package_plan_rate_category/flight/searchByCategories',
        'PackageServicesController@searchFlightsByCategories'
    );
    Route::post('package/package_plan_rate_category/hotel/share', 'PackageServicesController@shareHotel');
    Route::post('package/package_plan_rate_category/flight/share', 'PackageServicesController@shareFlight');
    Route::post('package/package_plan_rate_category/updateRates', 'PackageServicesController@updateRates');
    Route::get('package/package_plan_rate_category/{plan_rate_category_id}', 'PackageServicesController@searchByCategory');
    Route::get('package/package_plan_rate_category/{plan_rate_category_id}/hotels/rates/errors', 'PackageServicesController@verify_errors_rates_hotels_per_pages');
    Route::delete('package/service/{id}', 'PackageServicesController@destroy');
    Route::post('package/service/orders', 'PackageServicesController@newOrders');
    Route::post('package/service/calculation_included', 'PackageServicesController@calculation_included');
    Route::post('package/service/rate/selected', 'PackageServicesController@saveServiceRateSelectedInPackage'); //
    Route::post('package/service/change_service', 'PackageServicesController@changeService');
    Route::post('package/service/date_in', 'PackageServicesController@changeDateIn');
    Route::post('package/package_plan_rate_category/service/rate', 'PackageServicesController@storeServiceRate');
    Route::delete('package/package_plan_rate_category/service/rates', 'PackageServicesController@destroyServiceRates');
    Route::delete('package/delete/service_room', 'PackageServicesController@deleteServiceRoom');
    Route::put('package/package_plan_rate_category/{package_plan_rate_category_id}/services/date_in', 'PackageServicesController@update_date_in');


    Route::get('usertypes/selectBox', 'UserTypesController@selectBox');
    Route::get('usertypes/companions', 'UserTypesController@get_companions');

    Route::put('contacts/update/{id}/state', 'ContactsController@updateStatus');
    Route::resource('contacts', 'ContactsController');

    Route::get('rates/cost/{rate_plan_id}/associate_rate', [RatesCostsController::class, 'getAssociateRate']);
    Route::get('rates/cost/selectBox', [RatesCostsController::class, 'selectBox']);
    Route::get('rates/cost/{hotel_id}/calendar', [RatesCostsController::class, 'calendar']);
    Route::put('rates/cost/{hotel_id}/calendar', [RatesCostsController::class, 'calendarUpdate']);
    Route::delete('rates/cost/{hotel_id}/calendar', [RatesCostsController::class, 'calendarDeleteAll']);
    Route::delete('rates/cost/{hotechannelsl_id}/calendar/{calendar_id}', [RatesCostsController::class, 'calendarDelete'])
        ->where([
            'hotel_id' => '.*',
            'rate_id' => '[0-9]+'
        ]);
    Route::delete('rates/cost/{hotel_id}/calendar/{calendar_id}', [RatesCostsController::class, 'calendarDelete']);
    Route::get('rates/cost/{hotel_id}/{rate_id}/history', [RatesCostsController::class, 'history']);
    Route::post('rates/cost/{hotel_id}/{rate_id}/rooms', [RatesCostsController::class, 'storeRooms']);
    Route::put('rates/cost/{hotel_id}/{rate_id}/rooms', [RatesCostsController::class, 'updateRooms']);
    Route::get('rates/cost/{hotel_id}/{rate_id}', [RatesCostsController::class, 'show'])
        ->where([
            'hotel_id' => '.*',
            'rate_id' => '[0-9]+'
        ]);
    //Date ranges hotel
    Route::get('date_ranges/hotels/rate_plan/{rate_plan_id}', [RatesCostsController::class, 'getDateRangesHotel'])->where('rate_plan_id', '[0-9]+');
    Route::post('date_ranges/hotels/rate_plan/{rate_plan_id}', [RatesCostsController::class, 'setDateRangesHotel'])->where('rate_plan_id', '[0-9]+');
    Route::delete('date_ranges/hotels/{date_range_id}', [RatesCostsController::class, 'deleteDateRangeHotel'])->where('date_range_id', '[0-9]+');
    Route::put('date_ranges/hotels/group', [RatesCostsController::class, 'updateDateRangeHotelGroup']);

    //End date ranges hotel
    Route::post('rates/cost/{rate_plan_id}/uses/report', [RatesCostsController::class, 'report_uses']);
    Route::post('rates/cost/{hotel_id}/{rate_id}/channels', [RatesCostsController::class, 'storeChannels']);
    Route::put('rates/cost/{hotel_id}/{rate_id}/channels', [RatesCostsController::class, 'storeChannels']);
    Route::get('rates/cost/{hotel_id}/{rate_id}/channels', [RatesCostsController::class, 'getChannels']);
    Route::get('rates/cost/{hotel_id}', [RatesCostsController::class, 'index'])->where('hotel_id', '.*');
    Route::post('update/status/rate_plan', [RatesCostsController::class, 'updateStatus'])->name('update_status_rate_plan');
    Route::put('rates/cost/{hotel_id}/{rate_id}', [RatesCostsController::class, 'update'])->where('hotel_id', '.*');
    Route::post('rates/cost/{hotel_id}/{rate_id}/associate_rate', [RatesCostsController::class, 'storeAssociateRate']);
    Route::post('rates/cost/history/{hotel_id}/{rate_id}', [RatesCostsController::class, 'ratePlanHistory'])->where('hotel_id', '.*');
    Route::delete('rates/cost/{hotel_id}/{rate_id}/{price_id}', [RatesCostsController::class, 'destroy_date_range'])
        ->where([
            'hotel_id' => '.*',
            'rate_id' => '[0-9]+',
            'price_id' => '[0-9]+',
        ]);
    Route::delete('rates/cost/{hotel_id}/{rate_id}', [RatesCostsController::class, 'destroy'])
        ->where([
            'hotel_id' => '.*',
            'rate_id' => '[0-9]+'
        ]);
    Route::post('rates/cost/{hotel_id}/{rate_id}/clone', [RatesCostsController::class, 'clonarTarifa'])
        ->where([
            'hotel_id' => '.*',
            'rate_id' => '[0-9]+'
        ]);
    Route::post('rates/cost/{hotel_id}/notify_new_rate/{rate_id}', [RatesCostsController::class, 'notify_new_rate'])
        ->where([
            'hotel_id' => '.*',
            'rate_id' => '[0-9]+'
        ]);
    Route::post('rates/cost/{hotel_id}', [RatesCostsController::class, 'store'])->where('hotel_id', '.*');
    Route::post('rates/merge/{hotel_id}', [RatesCostsController::class, 'merge'])->where('hotel_id', '.*');

    Route::get('rates/sale/{hotel_id}/calendar', 'ClientRatePlanController@calendar');
    Route::get('service/rates/sale/{service_id}/calendar', 'ServiceClientRatePlansController@calendar');

    Route::post('rates/search', 'RatesPlansController@search');
    Route::post('rates/by/hotel', 'RatesPlansController@ratesByHotel');
    Route::post('rates/plans/store/clients/', 'RatesPlansController@storeClientRatePlan');
    Route::get('rates/plans/by/hotel/', 'RatesPlansController@ratesPlansByHotel');
    Route::post('rates/plans/by/channels', 'RatesPlansController@ratesPlansByChannel');

    Route::get('rates/bags', 'BagsController@getBags');
    Route::delete('rates/bags/{bag_id}', 'BagsController@deleteBag')->where('bag_id', '.*');
    Route::get('rates/bags/{bag_id}', 'BagsController@getBag')->where('bag_id', '.*');
    Route::post('rates/bag_rates', 'BagsController@getBagRates');
    Route::post('rates/bags/store', 'BagsController@store');
    Route::put('rates/bags/update', 'BagsController@update');
    Route::put('rates/bags/update/{bag_id}/status', 'BagsController@updateStatus');
    Route::post('rates/bags/update_status_by_rates', 'BagsController@updateStatusByRates');
    Route::post('rates/bags/rate/store', 'BagsController@storeRate');
    Route::post('rates/bags/rate/inverse', 'BagsController@inverseRate');
    Route::post('rates/bags/rate/store/all', 'BagsController@storeRates');
    Route::post('rates/bags/rate/inverse/all', 'BagsController@inverseRates');
    Route::put('update/rooms/bag', 'BagsController@updateRoomsBag');
    Route::get('generate-rates-in-calendar', 'GenerateRatesInCalendarController@getListRates');
    Route::post('generate-rates-in-calendar', 'GenerateRatesInCalendarController@process');
    Route::get('generate-rates-in-calendar-status', 'GenerateRatesInCalendarController@status');

    Route::get('ratesplanstypes/selectBox', 'RatesPlansTypesController@selectBox');

    Route::resource('minor_policies', 'MinorPolicyController');

    Route::resource('hotel_taxes', 'HotelTaxesController');

    Route::get('penalties/selectBox', 'PenaltiesController@selectBox');

    Route::delete('policies_cancelations/delete-parameters/{id}', 'PolicyCancelationsController@destroyParameters');
    Route::put('policies_cancelations/update/{id}/state', 'PolicyCancelationsController@updateStatus');
    Route::get('policies_cancelations/parameters', 'PolicyCancelationsController@searchParameters');
    Route::get('policies_cancelations/selectBox', 'PolicyCancelationsController@selectBox');
    Route::resource('policies_cancelations', 'PolicyCancelationsController');

    Route::resource('cancellation_policies', 'CancellationPolicyController');

    Route::post('service/search/client', 'ServiceController@serviceClient');
    Route::get('service/search/client', 'ServiceController@serviceClient');
    Route::get('service/search/client/rated', 'ServiceController@serviceClientRated');

    Route::resource('services/components/{component_id}/substitutes', 'ComponentSubstitutesController');

    Route::post('services/{id}/equivalences', 'ServiceController@get_equivalences');
    Route::post('services/equivalences/import', 'ServiceController@import_more_equivalences');

    Route::post('services/search/cross_selling', 'ServiceController@serviceCrossSelling');
    Route::get('services/ubigeo/selectbox/originFormat/{lang}', 'ServiceController@getCitiesOriginFormat');
    Route::get('services/ubigeo/selectbox/origin/{lang}', 'ServiceController@getCitiesOrigin');
    Route::get('services/ubigeo/selectbox/destination/{lang}', 'ServiceController@getCitiesDestination');
    Route::get('services/selectBox', 'ServiceController@selectBox');
    Route::get('services/configuration', 'ServiceController@selectBox');
    Route::get('service/{service_id}/moreDetails', 'ServiceController@searchDetails');
    Route::get('services/orders/rate', 'ServiceController@ordersRate');
    Route::put('services/orders/rate/update', 'ServiceController@updateOrderService');
    Route::get('services/packages', 'ServiceController@getPackagesByService');
    Route::resource('services', 'ServiceController');

    Route::get('/deactivatable/entity', 'DeactivatableEntitiesController@search');

    Route::delete('/classification/image/{id}', 'GaleriesController@removeImageClassification');
    Route::put('classification/gallery', 'GaleriesController@galleryClassification');
    Route::put('service_group/gallery', 'GaleriesController@galleryServiceGroup');
    Route::delete('service_group/image/{id}', 'GaleriesController@removeImageServiceGroup');
    Route::put('services/{id}/status', 'ServiceController@updateStatus');
    Route::get('services/{id}/uses', 'ServiceController@get_uses');
    Route::post('services/{id}/uses/report', 'ServiceController@report_uses');
    Route::get('classifications/selectBox', 'ClassificationController@selectBox');
    Route::resource('classifications', 'ClassificationController');
    Route::post('inclusions/translations', 'InclusionController@translations_update');
    Route::get('inclusions/selectBox', 'InclusionController@selectBox');
    Route::get('inclusions/filter', 'InclusionController@selectBoxFilter');
    Route::put('inclusions/{id}/operability', 'InclusionController@updateOperability');
    Route::resource('inclusions', 'InclusionController');
    Route::get('experiences/selectBox', 'ExperienceController@selectBox');
    Route::resource('experiences', 'ExperienceController');
    Route::get('requirements/selectBox', 'RequirementController@selectBox');
    Route::get('requirements/list', 'RequirementController@selectBoxList');
    Route::resource('requirements', 'RequirementController');
    Route::get('units/selectBox', 'UnitController@selectBox');
    Route::resource('units', 'UnitController');
    Route::get('unitdurations/selectBox', 'UnitDurationController@selectBox');
    Route::get('unitdurations/list', 'UnitDurationController@getUnitDurations');
    Route::resource('unitdurations', 'UnitDurationController');
    Route::get('restrictions/selectBox', 'RestrictionController@selectBox');
    Route::get('restrictions/list', 'RestrictionController@selectBoxList');
    Route::resource('restrictions', 'RestrictionController');
    Route::get('service_categories/selectBox', 'ServiceCategoriesController@selectBox');
    Route::get('service_categories/selectBoxGroup', 'ServiceCategoriesController@selectBoxGroup');
    Route::get('service_types/selectBox', 'ServiceTypeController@selectBox');
    Route::resource('service_types', 'ServiceTypeController');
    Route::get('service_categories/{id}/{lang}/subcategory', 'ServiceCategoriesController@getSubCategories');
    Route::resource('service_categories', 'ServiceCategoriesController');
    Route::resource('service_sub_categories', 'ServiceSubCategoriesController');

    Route::get('service/rates/types/selectBox', 'ServiceTypeRatesController@selectBox');

    Route::post('service/rates/cost/duplicate', 'ServiceRateCostsController@duplicate');
    Route::get('service/rates/cost/{service_id}/{rate_id}', 'ServiceRateCostsController@show')
        ->where([
            'service_id' => '.*',
            'rate_id' => '[0-9]+'
        ]);
    Route::get('service/rates/cost/{service_id}', 'ServiceRateCostsController@index')->where('service_id', '.*');
    Route::put('service/rates/cost/{service_id}/{rate_id}', 'ServiceRateCostsController@update')->where(
        'service_id',
        '.*'
    );
    Route::delete('service/rates/cost/{service_id}/{rate_id}', 'ServiceRateCostsController@destroy')
        ->where([
            'service_id' => '.*',
            'rate_id' => '[0-9]+'
        ]);
    Route::post('service/rates/cost/{service_id}', 'ServiceRateCostsController@store')->where('service_id', '.*');

    Route::post('service/rates/sale', 'ServiceRatePlansController@ratesByService');
    Route::post('service/{service_id}/rates/sale/duplicate', 'ServiceRatePlansController@duplicateSaleRate');

    Route::get('service/rates/{service_rate_id}/plans/{year?}', 'ServiceRatePlansController@index');
    Route::post('service/rates/plans', 'ServiceRatePlansController@store');
    Route::delete('service/rates/plans/{service_rate_plan_id}', 'ServiceRatePlansController@destroy');

    Route::get('service/rates/plans/by/service', 'ServiceRatePlansController@ratesPlansByService');
    Route::post('service/rates/plans/clients', 'ServiceRatePlansController@storeClientRatePlan');

    Route::get('suplements/hotel', 'SuplementsController@getSelectByHotel');
    Route::get('suplements/hotel/table', 'SuplementsController@getByHotelId');
    Route::post('suplements/hotel/add', 'SuplementsController@setSupplementHotel');

    Route::get('suplements/hotel/table_options', 'SuplementsController@getByHotelIdSupplementId');

    Route::get('suplements/hotel/calendaries', 'SuplementsController@getCalendaryByHotelIdSupplementId');
    Route::get('suplements/hotel/calendaries_fecha', 'SuplementsController@getCalendaryByHotelIdSupplementIdFecha');

    Route::get('suplements/rate', 'SuplementsController@getSelectByRate');
    Route::get('suplements/rate/table', 'SuplementsController@getByRatePlanId');
    Route::post('suplements/rate/add', 'SuplementsController@setSupplementRate');
    Route::delete('suplements/rate/delete', 'SuplementsController@deleteSupplementRate');

    Route::post('suplements/hotel/add/per_room', 'SuplementsController@setSupplementHotelPerRoom');
    Route::post('suplements/hotel/add/per_person', 'SuplementsController@setSupplementHotelPerPerson');
    Route::post('suplements/hotel/update/per_room', 'SuplementsController@setUpdateSupplementHotelPerPerson');
    Route::delete('suplements/hotel/delete/per_room', 'SuplementsController@deleteSupplementHotelPerPerson');

    Route::delete('suplements/hotel/delete', 'SuplementsController@deleteSupplementHotel');
    Route::resource('suplements', 'SuplementsController');

    Route::put('policies_rates/update/{id}/state', 'PolicyRatesController@updateStatus');
    Route::get('policies_rates/selectBox', 'PolicyRatesController@selectBox');
    Route::resource('policies_rates', 'PolicyRatesController');

    Route::get('physical_intensities/selectBox', 'PhysicalIntensitiesController@selectBox');
    Route::get('physical_intensities/list', 'PhysicalIntensitiesController@selectBoxList');
    Route::resource('physical_intensities', 'PhysicalIntensitiesController');

    Route::get('tags/selectBox', 'TagsController@selectBox');

    Route::post('up_selling/frontend', 'UpSellingController@upsellingFrontend');
    Route::post('up_selling', 'UpSellingController@index');
    Route::post('up_selling/store', 'UpSellingController@store');
    Route::post('up_selling/store/all', 'UpSellingController@storeAll');
    Route::post('up_selling/inverse', 'UpSellingController@inverse');
    Route::post('up_selling/inverse/all', 'UpSellingController@inverseAll');

    Route::post('cross_selling', 'CrossSellingController@index');
    Route::post('cross_selling/store', 'CrossSellingController@store');
    Route::post('cross_selling/store/all', 'CrossSellingController@storeAll');
    Route::post('cross_selling/inverse', 'CrossSellingController@inverse');
    Route::post('cross_selling/inverse/all', 'CrossSellingController@inverseAll');

    Route::put('market/update/{id}/state', 'MarketsController@updateStatus');
    Route::get('markets/selectbox', 'MarketsController@selectBox');
    Route::post('markets/countries', 'MarketsController@getCountries');
    //obtener mercados que no tiene un usuario
    Route::get('markets/modal/share', 'MarketsController@selectMarketsModalShare');
    //obtener usuarios de un mercado
    Route::post('get/clients/market', 'MarketsController@getClientsMarket');
    Route::resource('markets', 'MarketsController');

    Route::put('client/gallery/logo/', 'GaleriesController@galleryClientLogo');
    Route::delete('client/logo/image/{id}', 'GaleriesController@removeImageLogo');
    Route::put('clients/update/{id}/state', 'ClientsController@updateStatus');
    Route::get('clients/selectBox', 'ClientsController@selectBox');
    Route::get('clients/exist', 'ClientsController@verifyExist');
    Route::get('clients/selectBox/by/name', 'ClientsController@selectBoxByName');
    Route::get('clients/selectBox/by/services', 'ClientsController@selectBoxByServices');
    Route::get('clients/selectBox/by/trains', 'ClientsController@selectBoxByTrains');
    Route::get('clients/by/executive', 'ClientsController@clientsByExecutive');
    Route::post('clients/executive', 'ClientsController@associateExecutives');
    Route::get('clients/selectBox/by/executive', 'ClientsController@clientsByExecutiveFront');

    Route::post('client/search/hotel', 'ClientsController@clientHotel');
    Route::post('client/search/service', 'ClientsController@clientService');
    Route::post('client/search/train', 'ClientsController@clientTrain');
    Route::get('client/search', 'ClientsController@search');
    Route::get('client/{client_id}/markup', 'ClientsController@markup');

    Route::post('clients/markets', 'ClientsController@getClientsByMarkets');
    Route::post('clients_groups', 'ClientsController@getClientsByIds');
    //    Route::post('client/test_job', 'ClientsController@test_job');

    Route::resource('clients', 'ClientsController');
    Route::get('/masi_mailing', 'ClientMailingController@index');
    Route::get('/masi_mailing_files', 'MasiController@files_locked');
    Route::post('/masi_mailing_files', 'MasiController@save_file_locked');
    Route::delete('/masi_mailing_files/{file_id}', 'MasiController@delete_file_locked');
    Route::get('/masi_mailing/update/{clients_id}', 'ClientMailingController@update');
    Route::get('/masi_statistics/regions', 'MasiStatisticsController@getRegions');
    Route::get('/masi_statistics/regions/{region_id}/countries', 'MasiStatisticsController@getCountriesByRegion');
    Route::get(
        '/masi_statistics/regions/{region_id}/countries/{country_name}/clients',
        'MasiStatisticsController@getClientsByCountryRegion'
    );
    // Route::get('/masi_statistics/chatbot', 'MasiStatisticsController@getChatbotStatistics');
    Route::get('/masi_statistics/general', 'MasiStatisticsController@search_general_notification');
    Route::get('/masi_statistics/chatbot', 'MasiStatisticsController@search_whatsapp_notification');
    Route::get('/masi_statistics/mailing', 'MasiStatisticsController@search_mailing_notification');
    // Route::get('/masi_statistics/mailing', 'MasiStatisticsController@getNotificationsStatistics');
    Route::get('/masi_statistics/files', 'MasiStatisticsController@getFileStatistics');
    Route::get('/masi_statistics/files/export', 'MasiStatisticsController@exportFileStatistics');
    Route::post('/masi/downloadExcel', 'MasiStatisticsController@downloadExcel');

    // Suele variar dependiendo los tickets solicitados..
    Route::post('/masi/downloadExcel/{reference_id}', 'MasiStatisticsController@downloadExcelMKT');
    Route::post('/masi/downloadExcelCanada', 'MasiStatisticsController@downloadExcelCanada');


    // Route::resource('client_mailing', 'ClientMailingController');
    Route::get('markups/selectPeriod', 'MarkupsController@buildDates');
    Route::get('markups/selectBox', 'MarkupsController@selectBox');
    Route::get('markup/byClient/{client_id}', 'MarkupsController@searchByClientId');
    Route::put('markups/update/{id}/state', 'MarkupsController@updateStatus');
    Route::resource('markups', 'MarkupsController');

    Route::put('service/{service_id}/schedule/', 'ServiceScheduleController@updateAffectedSchedule');
    Route::delete('service_schedules/{id}/{service_id}/', 'ServiceScheduleController@destroy');
    Route::resource('service_schedules', 'ServiceScheduleController');

    Route::post('services/schedules/group', 'ServiceController@schedules_group');

    Route::put('seller/gallery/logo/', 'GaleriesController@gallerySellerLogo');
    Route::delete('seller/logo/image/{id}', 'GaleriesController@removeImageLogo');
    Route::get('type_operability/selectBox', 'ServiceTypeActivityController@selectBox');
    Route::get('type_operability/filter', 'ServiceTypeActivityController@selectBoxFilter');
    Route::resource('type_operability', 'ServiceTypeActivityController');
    Route::resource('service_operation', 'ServiceOperationController');
    Route::resource('service_operation_activity', 'ServiceOperationActivityController');
    Route::get('services/galleries/{id}', 'GaleriesController@indexServiceGallery');
    Route::post('service/gallery/max/position', 'GaleriesController@maxPositionServiceGallery');

    Route::get('sellers/more', 'ClientSellersController@get_more_sellers');
    Route::put('sellers/update/{id}/state', 'ClientSellersController@updateStatus');
    Route::put('sellers/update/{id}/flag_reservation', 'ClientSellersController@updateReservation');
    Route::resource('sellers', 'ClientSellersController');

    Route::resource('central_bookings', 'CentralBookingsController');

    Route::post('client_services/clientLocked', 'ClientServicesController@clientLocked');
    Route::get('client_services/selectPeriod', 'ClientServicesController@selectPeriod');
    Route::post('client_services/store', 'ClientServicesController@store');
    Route::post('client_services/store/service/filter', 'ClientServicesController@storeByFilter');
    Route::post('client_services/store/all', 'ClientServicesController@storeAll');
    Route::post('client_services/inverse', 'ClientServicesController@inverse');
    Route::post('client_services/inverse/all', 'ClientServicesController@inverseAll');
    Route::put('client_services/update', 'ClientServicesController@update');
    Route::put('client_services/update/all', 'ClientServicesController@updateAllClients');
    Route::post('client_services/store/clientAll', 'ClientServicesController@storeClientAll');
    Route::post('client_services/inverse/clientAll', 'ClientServicesController@inverseClientAll');
    Route::post('client_services', 'ClientServicesController@index');

    //    Route::put('client_services/update/all', 'ClientServicesController@updateAll');
    //    Route::get('clients/by/service', 'ClientServicesController@clientsByService');

    Route::post('client_services/by/period', 'ClientServicesController@getClientsByPeriod');

    Route::get('client_hotels/selectPeriod', 'ClientHotelsController@selectPeriod');
    Route::post('client_hotels', 'ClientHotelsController@index');
    Route::post('client_hotels/clientLocked', 'ClientHotelsController@clientLocked');
    Route::post('client_hotels/store', 'ClientHotelsController@store');
    Route::post('client_hotels/store/all', 'ClientHotelsController@storeAll');
    Route::post('client_hotels/store/clientAll', 'ClientHotelsController@storeClientAll');
    Route::post('client_hotels/inverse', 'ClientHotelsController@inverse');
    Route::post('client_hotels/inverse/all', 'ClientHotelsController@inverseAll');
    Route::post('client_hotels/inverse/clientAll', 'ClientHotelsController@inverseClientAll');
    Route::put('client_hotels/update', 'ClientHotelsController@update');
    Route::put('client_hotels/update/all', 'ClientHotelsController@updateAll');
    Route::get('clients/by/hotel', 'ClientHotelsController@clientsByHotel');
    Route::get('client_hotels/classes', 'ClientHotelsController@classHotelByClient');


    Route::post('client_hotels/by/period', 'ClientHotelsController@getClientsByPeriod');

    Route::post('client_executive', 'ClientExecutivesController@index');
    Route::put('client_executive/{id}/use_email_reserve', 'ClientExecutivesController@update_use_email');
    Route::get('client_executive/executive/{user_id}', 'ClientExecutivesController@byExecutive');
    Route::post('client_executive/store', 'ClientExecutivesController@store');
    Route::post('client_executive/store/all', 'ClientExecutivesController@storeAll');
    Route::post('client_executive/inverse', 'ClientExecutivesController@inverse');
    Route::post('client_executive/inverse/all', 'ClientExecutivesController@inverseAll');
    Route::put('client_executive/update', 'ClientExecutivesController@update');
    Route::put('client_executive/update/all', 'ClientExecutivesController@updateAll');

    Route::post('client_rate_plans', 'ClientRatePlansController@index');
    Route::post('client_rate_plans/delete', 'ClientRatePlansController@destroy');
    Route::post('client_rate_plans/selected', 'ClientRatePlansController@consultSelected');
    Route::post('client_rate_plans/store', 'ClientRatePlansController@store');
    Route::post('client_rate_plans/store/all', 'ClientRatePlansController@storeAll');
    Route::post('client_rate_plans/inverse', 'ClientRatePlansController@inverse');
    Route::post('client_rate_plans/inverse/all', 'ClientRatePlansController@inverseAll');
    Route::put('client_rate_plans/update', 'ClientRatePlansController@update');
    // Route::resource('client_rate_plans', 'ClientRatePlansController');

    Route::post('service_client_rate_plans', 'ServiceClientRatePlansController@index');
    Route::post('service_client_rate_plans/delete', 'ServiceClientRatePlansController@destroy');
    Route::post('service_client_rate_plans/selected', 'ServiceClientRatePlansController@consultSelected');
    Route::post('service_client_rate_plans/store', 'ServiceClientRatePlansController@store');
    Route::post('service_client_rate_plans/store/all', 'ServiceClientRatePlansController@storeAll');
    Route::put('service_client_rate_plans/update', 'ServiceClientRatePlansController@update');
    Route::post('service_client_rate_plans/inverse', 'ServiceClientRatePlansController@inverse');
    Route::post('service_client_rate_plans/inverse/all', 'ServiceClientRatePlansController@inverseAll');

    Route::resource('service_taxes', 'ServiceTaxController');
    Route::get('service/{service_id}/children', 'ServiceChildrenController@index');
    Route::post('service/{service_id}/children', 'ServiceChildrenController@store');
    Route::put('service/{service_id}/children/{child_id}/status', 'ServiceChildrenController@updateStatus');
    Route::delete('service/{service_id}/children/{child_id}', 'ServiceChildrenController@destroy');
    Route::put('service/{id}/configurations', 'ServiceController@updateConfigurations');
    Route::get('service/{id}/configuration', 'ServiceController@getConfiguration');
    Route::put('service/inclusion/up_order', 'ServiceInclusionController@upService')->name('up_service_inclusion');
    Route::put('service/inclusion/down_order', 'ServiceInclusionController@downService')->name('down_service_inclusion');
    Route::get('service/{service_id}/inclusions', 'ServiceInclusionController@index');
    Route::put('service/inclusions/{id}/status', 'ServiceInclusionController@updateStatus');
    Route::put('service/inclusions/{id}/see_client', 'ServiceInclusionController@updateStatusSeeClient');
    Route::post('service/inclusions', 'ServiceInclusionController@store');
    Route::delete('service/inclusions/{id}', 'ServiceInclusionController@destroy');
    Route::put('allotments/update/num_days_blocked', 'AllotmentsController@updateNumDaysBlocked');

    Route::get('quantity/hotel/selectbox', 'ChainsMultiplePropertyController@selectBox');
    Route::get('chain/{chain_id}/hotels', 'ChainsMultiplePropertyController@getHotels');
    Route::post('multiple_property/chains/add/property', 'ChainsMultiplePropertyController@addPropertyRate');
    Route::delete('multiple_property/chains/property/rates', 'ChainsMultiplePropertyController@destroyRates');
    Route::delete('multiple_property/chains/property/rate/{property_rate_id}', 'ChainsMultiplePropertyController@destroyRate');
    Route::delete('multiple_property/chains/property/rate/room/{room_id}', 'ChainsMultiplePropertyController@destroyRateRoom');
    Route::resource('multiple_property/chains', 'ChainsMultiplePropertyController');

    Route::post('services/component/{component_id}/client', 'ServiceComponentsController@store_component_client');
    Route::put('service/component/{component_id}', 'ServiceComponentsController@update');
    Route::post('service/{service_id}/component/{component_id}/copy', 'ServiceComponentsController@copy_component');
    Route::get('service/{service_id}/components/max_nights', 'ServiceComponentsController@get_max_nights');
    Route::get('service/{service_id}/components', 'ServiceComponentsController@index');
    Route::post('service/{service_id}/components', 'ServiceComponentsController@store');
    Route::delete('service/{service_id}/components/{component_id}', 'ServiceComponentsController@destroy');

    Route::put('channel_users/update/{id}/state', 'ChannelUsersController@updateStatus');
    Route::resource('channel_users', 'ChannelUsersController');

    Route::get('service/penalties/selectBox', 'ServicePenaltyController@selectBox');
    Route::get('service/cancellations_policies/supplier', 'ServiceCancellationPoliciesController@supplier');
    Route::get('service/cancellations_policies/selectBox', 'ServiceCancellationPoliciesController@selectBox');
    Route::put(
        'service/cancellations_policies/update/{id}/state',
        'ServiceCancellationPoliciesController@updateStatus'
    );
    Route::put('services/cancellations_policies/{id}/ranges', 'ServiceRatePlansController@update_ranges');
    Route::delete(
        'service/cancellations_policies/parameters/{id}',
        'ServiceCancellationPoliciesController@destroyParameters'
    );
    Route::get('service/cancellations_policies/parameters', 'ServiceCancellationPoliciesController@searchParameters');
    Route::resource('service/cancellations_policies', 'ServiceCancellationPoliciesController');

    Route::post('package/inventory', 'PackageInventoryController@index');
    Route::post('package/inventory/add', 'PackageInventoryController@store');
    Route::post('package/inventory/locked/days', 'PackageInventoryController@lockedDays');
    Route::post('package/inventory/enabled/days', 'PackageInventoryController@enabledDays');
    Route::post('package/inventory/store/range/days', 'PackageInventoryController@storeInventoryByDateRange');
    Route::post('package/inventory/blocked/range/days', 'PackageInventoryController@blockedInventoryByDateRange');

    //Requerimientos de paquetes Anthony
    Route::get('package/rates/cost', 'PackagesController@getPackageRatesCost');
    Route::get('package/rates/sales/markups', 'PackagesController@getSalesRateMarkups');
    Route::post('package/rates/sales/markups/add', 'PackagesController@addPackageRateSaleMarkup');
    Route::put('package/rates/sales/markups/update', 'PackagesController@updateStatusPackageRateSaleMarkup');
    Route::put('package/rates/sales/markup/update', 'PackagesController@updateMarkupPackageRateSale');
    Route::put('package/rates/sales/markup/update/general', 'PackagesController@updateMarkups');
    Route::get('package/rates/sales/markup/recalculate', 'PackagesController@recalculateMarkupPackageRateSale');
    Route::delete('package/rates/sales/markups/{id}', 'PackagesController@deletePackageRateSaleMarkup');

    Route::resource('virtualclass', 'VirtualClassNameController');
    //fin

    Route::get('service/{service_id}/plan_rates/selectBox', 'ServiceRateCostsController@selectBox');

    Route::put('service/{service_id}/packages/rates', 'ServiceController@updateRatesInPackages');

    Route::post('service/inventory/add', 'ServiceInventoriesController@store');
    Route::post('service/inventory/locked/days', 'ServiceInventoriesController@lockedDays');
    Route::post('service/inventory/enabled/days', 'ServiceInventoriesController@enabledDays');
    Route::post('service/inventory/store/range/days', 'ServiceInventoriesController@storeInventoryByDateRange');
    Route::post('service/inventory/blocked/range/days', 'ServiceInventoriesController@blockedInventoryByDateRange');
    Route::post('service/inventory', 'ServiceInventoriesController@index');

    Route::get('channels-logs/{channel_id}', 'ChannelsLogsController@index');
    Route::get('channels-logs/{log_id}/show/{id}', 'ChannelsLogsController@show');

    Route::post('services/search', 'ServiceController@service_search');

    Route::get('service/{service_id}/equivalence_associations', 'ServiceEquivalenceAssociationController@index');
    Route::post('service/{service_id}/equivalence_associations', 'ServiceEquivalenceAssociationController@store');
    Route::delete(
        'service/equivalence_associations/{equivalence_association_id}',
        'ServiceEquivalenceAssociationController@destroy'
    );
    Route::get('service/form', 'ServiceController@form_services');

    // Route::get('quote/ubigeo/selectbox/destinations', 'QuotesController@getDestinations');
    // Route::post('quote/extension/replace', 'QuotesController@replaceExtension');
    // Route::post('quote_client/extension', 'QuotesController@add_client_extension');
    // Route::post('quote/extension/add', 'QuotesController@addExtension');
    // Route::post('quote/extensions', 'PackagesController@getExtensionsQuote');
    // Route::put('update/quote/markup', 'QuotesController@updateMarkup');
    // Route::put('quote/me', 'QuotesController@quoteMe');
    // Route::put('quote/nights/service', 'QuotesController@updateNightsService');
    // Route::get('quote/service/occupation_paseengers_hotel', 'QuotesController@occupationPassengersHotel');
    // Route::post('quote/service/occupation_paseengers_hotel', 'QuotesController@storeOccupationPassengersHotel');
    // Route::get('quote/service/occupation_paseengers_hotel_client', 'QuotesController@occupationPassengersHotelClient');
    // Route::post('quote/service/occupation_paseengers_hotel_client', 'QuotesController@storeOccupationPassengersHotelClient');
    // Route::put('quote/service/occupation_hotel', 'QuotesController@updateOccupationHotel');
    // Route::put('quote/service/occupation_hotel/general', 'QuotesController@updateOccupationHotelGeneral');
    // Route::post('quote/service/passenger', 'QuotesController@saveQuoteServicePassenger');
    // Route::post('quote/services/passengers', 'QuotesController@getServicesPassengers');
    // Route::post('quote/service/{quote_service_id}/rooms/replace', 'QuoteServiceRoomsController@replace');
    // Route::post('quote/service/{quote_service_id}/rooms/addFromHeader', 'QuoteServiceRoomsController@addFromHeader');
    // Route::delete('quote/service/rooms/{id}/rate_plan_room', 'QuoteServiceRoomsController@destroy');
    // Route::post('quote/save_as', 'QuotesController@saveAs');
    // Route::post('quote/update_order_and_date/services', 'QuotesController@updateDateAndOrderServices');
    // Route::post('quote/update/services/passengers', 'QuotesController@updatePassengersService');
    // Route::post('quote/create_or_delete/category', 'QuotesController@createOrDeleteCategory');
    // Route::post('quote/restore', 'QuotesController@restore');
    // Route::put('quote/update/date_in', 'QuotesController@updateDateInQuote');
    // Route::put('quote/update/name', 'QuotesController@updateNameQuote');
    // Route::put('quote/update/date_in/services', 'QuotesController@updateDateInService');
    // Route::post('quotes/{quote_id}/services', 'QuotesController@deleteServices');
    // Route::put('quote/replace/service', 'QuotesController@replaceService');
    // Route::put('quote/people', 'QuotesController@updatePeople');
    // Route::put('quote/age_child', 'QuotesController@updateAgeChild');
    // Route::put('quote/optional', 'QuotesController@updateOptional');
    // Route::put('quote/services/{quote_service_id}/schedule', 'QuotesController@update_schedule');
    // Route::put('quote/services/{quote_service_id}/schedule', 'QuotesController@update_schedule');
    // Route::put('quote/services/{quote_service_id}/hour_in', 'QuotesController@update_hour_in');
    // Route::post('quote/passengers', 'QuotesController@saveOrUpdatePassengers');
    // Route::put('quote/copy_first_passenger_data', 'QuotesController@copyFirstPassengerData');
    // Route::delete('quote/passengers/{passenger_id}', 'QuotesController@deletePassenger')->where('passenger_id',
    //     '[0-9]+');
    // Route::post('quote/notes', 'QuotesController@createNote');
    // Route::patch('quote/notes/{note_id}', 'QuotesController@updateNote')->where('note_id', '[0-9]+');
    // Route::patch('quote/ranges/{range_id}', 'QuotesController@updateRange')->where('range_id', '[0-9]+');
    // Route::delete('quote/ranges/{range_id}', 'QuotesController@deleteRange')->where('range_id', '[0-9]+');
    // Route::post('quote/ranges', 'QuotesController@createRange');
    // Route::post('quote/ranges/save', 'QuotesController@saveRange');
    // Route::post('quote/notes/responses', 'QuotesController@createResponse');

    // Route::get('quote/{quote_id}/price-by-ranges', 'QuotesController@priceByRanges')->where('quote_id', '[0-9]+');
    // Route::get('quote/{quote_id}/price-by-passengers', 'QuotesController@priceByPassengers')->where('quote_id', '[0-9]+');

    // Route::resource('quotes/{id}/history_logs', 'QuoteHistoryLogsController');

    // Route::resource('quotes', 'QuotesController');
    // Route::put('quotes/update_notes/{quote_id}', 'QuotesController@update_notes');
    // Route::put('quotes/showInPopup/{value}', 'QuotesController@updateShowInPopup');
    Route::post('quote/{quote_id}/import', 'QuotesController@import');
    Route::get('quotes/orders/byExecutive', 'QuotesController@filterInformixOrders');
    // Route::post('quote/order/verifyTie', 'QuotesController@verifyTie');
    // Route::post('quote/order/relate', 'QuotesController@relateOrder');
    Route::get('quotes/import/headers', 'QuotesController@filterInformixHeaders');
    Route::post('quotes/import/header', 'QuotesController@importHeader');
    // Route::get('quote/byUserStatus/{status}', 'QuotesController@searchByUserStatus');
    // Route::post('quote/import/file', 'QuotesController@importFromFile');
    // Route::get('quote/existByUserStatus/{status}', 'QuotesController@searchExistByUserStatus');
    // Route::get('quote/check_editing/{quote_id}', 'QuotesController@checkEditing');
    // Route::post('quote/{quote_id}/replaceQuoteInFront', 'QuotesController@replaceQuoteInFront');
    // Route::post('quote/{quote_id}/copy/quote', 'QuotesController@copy');
    Route::post('quote/{quote_id}/share/quote', 'QuotesController@share');
    // Route::post('package/copy/quote', 'QuotesController@copyPackageToQuote');
    // Route::delete('quote/{quote_id}/forcefullyDestroy', 'QuotesController@forcefullyDestroy');
    // Route::post('quote/{quote_id}/convertToPackage', 'QuotesController@convertToPackage');
    // Route::post('quote/{quote_id}/discount', 'QuotesController@updateDiscount');
    // Route::post('quote/{quote_id}/discountPermissionMail', 'QuotesController@discountPermissionMail');
    // Route::post('quote/categories/copy', 'QuoteCategoriesController@copy');
    // Route::get('quote/{quote_id}/categories/services', 'QuoteCategoriesController@searchAllServices');
    // Route::post('quote/{quote_id}/category/{category_id}/replaceMultiple', 'QuoteCategoriesController@replaceMultiple');
    // Route::post('quote/{quote_id}/category/{category_id}/add', 'QuoteCategoriesController@add');
    // Route::get('quote/{quote_id}/category/{category_id}/skeleton', 'QuoteCategoriesController@wordSkeleton');
    // Route::get('quote/{quote_id}/category/{category_id}/itinerary', 'QuoteCategoriesController@wordItinerary');
    // Route::post('quote/{quote_id}/categories/service', 'QuoteCategoriesController@storeService')->where('quote_id',
    //     '[0-9]+');
    // Route::post('quote/{quote_id}/categories/flight', 'QuoteCategoriesController@storeFlight')->where('quote_id',
    //     '[0-9]+');
    // Route::post('quote/categories/update/on_request', 'QuoteCategoriesController@updateOnRequest');
    // Route::post('quote/categories/update/on_request_multiple', 'QuoteCategoriesController@updateOnRequestMultiple');
    // Route::get('quote/{quote_id}/check/services_amounts', 'QuoteCategoriesController@checkQuoteServicesAmounts');
    Route::get('quote/imageCreate', 'QuoteCategoriesController@imageCreate');
    Route::get('quote/rq_hotels', 'QuotesController@getRqHotels');
    Route::post('quote-dynamic-price', 'QuoteDynamicPriceController@store');

    //Cloudinary
    Route::post('/upload/clientlogo', 'CloudinaryController@uploadClientLogo');
    Route::get('/folders/multimedia', 'CloudinaryController@getFolders');
    Route::get('/folder/highlights', 'CloudinaryController@getImagesHighlights');
    Route::get('/tags/multimedia', 'CloudinaryController@getTags');
    Route::post('/destinations/search', 'CloudinaryController@search');

    //Sincronizacion servicios
    Route::put('sync/services/{code}/{equivalence}/texts', 'ServiceController@syncUpdateTexts');
    Route::put('sync/services/{code}/{equivalence}/status', 'ServiceController@syncUpdateStatus');
    Route::put('sync/services/{code}/{equivalence}/geo', 'ServiceController@syncUpdateGeo');
    Route::put('sync/services/{code}/{equivalence}/update', 'ServiceController@syncUpdate');
    Route::post('sync/services/add', 'ServiceController@syncStore');
    Route::post('sync/services/update/rates', 'ServiceController@syncUpdateRates');
    Route::post('sync/clients/add', 'ClientsController@syncStore');
    Route::post('sync/executive/add', 'UsersController@syncStoreExecutive');
    Route::put('sync/executive/{code}/update', 'UsersController@syncUpdateExecutive');
    //Masi
    Route::get('sync/clientes/masi/{type}', 'ClientMailingController@getClientsMasiByEmailType');

    Route::resource('train_templates', 'TrainTemplatesController');
    Route::get('train_templates/galleries/{id}', 'GaleriesController@indexTrainGallery');
    Route::post('train_templates/gallery/max/position', 'GaleriesController@maxPositionTrainGallery');

    Route::put('train_template/{train_template_id}/status', 'TrainTemplatesController@updateStatus');
    Route::get('train_template/{train_template_id}/translations', 'TrainTemplatesController@getTranslations');
    Route::put(
        'train_template/{train_template_id}/translations/{language_id}',
        'TrainTemplatesController@updateTranslations'
    );
    Route::get('train_template/{train_template_id}/amenities', 'TrainTemplatesController@getAmenities');
    Route::put('train_template/{train_template_id}/amenities', 'TrainTemplatesController@updateAmenities');


    Route::put('train_template/{train_template_id}/configurations', 'TrainTemplatesController@updateConfigurations');
    Route::post('train_template/{train_template_id}/taxes', 'TrainTemplatesController@updateTax');

    Route::get('train_template/import/service/{service_id}', 'TrainTemplatesController@importService');

    Route::get('train_template/{train_template_id}/children', 'TrainChildrenController@index');
    Route::post('train_template/{train_template_id}/children', 'TrainChildrenController@store');
    Route::put('train_template/{train_template_id}/children/{child_id}/status', 'TrainChildrenController@updateStatus');
    Route::delete('train_template/{train_template_id}/children/{child_id}', 'TrainChildrenController@destroy');

    Route::resource('trains', 'TrainsController');
    Route::get('train/{id}/routes', 'TrainsController@routes');
    Route::get('train/{id}/classes', 'TrainsController@classes');

    Route::delete(
        'train_cancellation_policies/parameters/{id}',
        'TrainCancellationPoliciesController@destroyParameter'
    );
    Route::put('train_cancellation_policies/{id}/state', 'TrainCancellationPoliciesController@updateStatus');
    Route::get('train_cancellation_policies/parameters', 'TrainCancellationPoliciesController@searchParameters');
    Route::get('train_cancellation_policies/selectBox', 'TrainCancellationPoliciesController@selectBox');
    Route::resource('train_cancellation_policies', 'TrainCancellationPoliciesController');

    Route::put('train_policy_rates/{id}/state', 'TrainPolicyRatesController@updateStatus');
    Route::get('train_policy_rates/selectBox', 'TrainPolicyRatesController@selectBox');
    Route::resource('train_policy_rates', 'TrainPolicyRatesController');

    Route::get('train_template/{train_template_id}/rates', 'TrainRatesController@index');
    Route::post('train_template/{train_template_id}/rate', 'TrainRatesController@store');
    Route::put('train_template/rate/{train_rate_id}', 'TrainRatesController@update');
    Route::get('train_template/rate/{train_rate_id}', 'TrainRatesController@show');
    Route::delete('train_template/rate/{train_rate_id}', 'TrainRatesController@destroy');
    //    Route::post('service/rates/sale', 'ServiceRatePlansController@ratesByService');

    Route::get('train_template/{train_template_id}/rates/selectBox', 'TrainRatesController@selectBox');

    Route::get('train_template/rate/{train_rate_id}/plans/{year}', 'TrainRatePlansController@index');
    Route::put('train_template/rate/{train_rate_id}/plans/train_type_id', 'TrainRatePlansController@updateTrainTypeId');
    Route::post('train_template/rate/plans', 'TrainRatePlansController@store');
    Route::delete('train_template/rate/plan/{train_rate_plan_id}', 'TrainRatePlansController@destroy');

    Route::get('train_template/rates/plans/by/train', 'TrainRatePlansController@ratesPlansByTrain');

    Route::post('client_trains/clientLocked', 'ClientTrainsController@clientLocked');
    //    Route::get('client_services/selectPeriod', 'ClientServicesController@selectPeriod');
    Route::post('client_trains', 'ClientTrainsController@index');
    Route::post('client_trains/store', 'ClientTrainsController@store');
    //    Route::post('client_services/store/all', 'ClientServicesController@storeAll');
    Route::post('client_trains/inverse', 'ClientTrainsController@inverse');
    //    Route::post('client_services/inverse/all', 'ClientServicesController@inverseAll');
    Route::put('client_trains/update', 'ClientTrainsController@update');
    Route::put('client_trains/update/all', 'ClientTrainsController@updateAllClients');
    Route::post('client_trains/store/clientAll', 'ClientTrainsController@storeClientAll');
    Route::post('client_trains/inverse/clientAll', 'ClientTrainsController@inverseClientAll');
    //
    //    Route::post('client_services/by/period', 'ClientServicesController@getClientsByPeriod');

    Route::post('train_client_rate_plans', 'TrainClientRatePlansController@index');
    //    Route::post('service_client_rate_plans/delete', 'ServiceClientRatePlansController@destroy');
    Route::post('train_client_rate_plans/selected', 'TrainClientRatePlansController@consultSelected');
    Route::post('train_client_rate_plans/store', 'TrainClientRatePlansController@store');
    Route::post('train_client_rate_plans/store/all', 'TrainClientRatePlansController@storeAll');
    Route::put('train_client_rate_plans/update', 'TrainClientRatePlansController@update');
    Route::post('train_client_rate_plans/inverse', 'TrainClientRatePlansController@inverse');
    Route::post('train_client_rate_plans/inverse/all', 'TrainClientRatePlansController@inverseAll');


    Route::post('train_template/inventory/add', 'TrainInventoriesController@store');
    Route::post('train_template/inventory/locked/days', 'TrainInventoriesController@lockedDays');
    Route::post('train_template/inventory/enabled/days', 'TrainInventoriesController@enabledDays');
    Route::post('train_template/inventory/store/range/days', 'TrainInventoriesController@storeInventoryByDateRange');
    Route::post(
        'train_template/inventory/blocked/range/days',
        'TrainInventoriesController@blockedInventoryByDateRange'
    );
    Route::post('train_template/inventory', 'TrainInventoriesController@index');

    Route::resource('rail_routes', 'RailRoutesController');
    Route::put('rail_route/{rail_route_id}/status', 'RailRoutesController@updateStatus');

    Route::get('train_rail_routes/train/{train_id}', 'TrainRailRoutesController@getByTrain');
    Route::post('train_rail_routes', 'TrainRailRoutesController@store');
    Route::put('train_rail_routes/{train_rail_route_id}', 'TrainRailRoutesController@update');
    Route::delete('train_rail_routes/{train_rail_route_id}', 'TrainRailRoutesController@destroy');

    Route::resource('train_classes', 'TrainClassesController');
    Route::put('train_class/{train_class_id}/status', 'TrainClassesController@updateStatus');

    Route::get('train_train_classes/train/{train_id}', 'TrainTrainClassesController@getByTrain');
    Route::post('train_train_classes', 'TrainTrainClassesController@store');
    Route::put('train_train_classes/{train_train_route_id}', 'TrainTrainClassesController@update');
    Route::delete('train_train_classes/{train_train_route_id}', 'TrainTrainClassesController@destroy');

    Route::get('users/notification/service', 'UsersController@getUserNotifyService');
    //Vista
    Route::get('vista/{client_id}/client', 'VistaController@getInfoWeb');
    Route::get('vista/{vista_id}/', 'VistaController@show');
    Route::put('vista/{vista_id}/', 'VistaController@edit');
    Route::post('vista/', 'VistaController@store');
    Route::get('vista/{client_id}/info_page', 'VistaController@showInfo');

    Route::get('train_types', 'TrainTypesController@index');

    Route::get('train_users/train/{train_id}', 'TrainUsersController@index');
    Route::post('train_users', 'TrainUsersController@store');
    Route::delete('train_users/{train_user_id}', 'TrainUsersController@destroy');

    //Multimedia
    Route::put('multimedia/{id}/status', 'MultimediaController@changeStatus');
    Route::get('multimedia/destinations', 'MultimediaController@getDestinations');

    //Tag multimedia
    Route::get('multimedia/filters', 'MultimediaController@getFilters');
    Route::get('multimedia/{id}/filter', 'MultimediaController@showFilter');
    Route::post('multimedia/filter', 'MultimediaController@store_filter');
    Route::put('multimedia/{id}/filter', 'MultimediaController@update_filter');
    Route::put('multimedia/{id}/filter_status', 'MultimediaController@changeStatusFilter');
    Route::delete('multimedia/{id}/filter', 'MultimediaController@destroy_filter');
    Route::resource('multimedia', 'MultimediaController');

    //Tickets
    Route::put('ticket/{id}/status', 'TicketController@update_status');
    Route::resource('ticket', 'TicketController');

    Route::get('train/{train_template_id}/inclusions', 'TrainInclusionsController@index');
    Route::put('train/inclusions/{id}/status', 'TrainInclusionsController@updateStatus');
    Route::put('train/inclusions/{id}/see_client', 'TrainInclusionsController@updateStatusSeeClient');
    Route::post('train/{train_template_id}/inclusions', 'TrainInclusionsController@store');
    Route::delete('train/inclusions/{id}', 'TrainInclusionsController@destroy');

    Route::post('package/itinerary', 'PackagesController@wordItinerary');
    Route::get('package/permissions/', 'PackagesController@packagePermissionsList');
    Route::post('package/permissions/', 'PackagesController@storePermission');
    Route::delete('package/permissions/{id}', 'PackagesController@destroyPermission');

    //Audit
    Route::get('audit/package', 'AuditController@auditPackage');
    Route::get('audit/client', 'AuditController@auditClient');
    Route::post('audit/restore', 'AuditController@auditRestore');
    Route::get('package/search', 'PackagesController@search');
    Route::get('service/search', 'ServiceController@selectBox');
    Route::get('audit/service', 'AuditController@auditService');

    Route::get('substitute', 'ExecutiveSubstitutesController@index');
    Route::post('substitute', 'ExecutiveSubstitutesController@store');
    Route::delete('substitute/{id}', 'ExecutiveSubstitutesController@delete');
    Route::get('substitute/users/executive/{executive_id}', 'ExecutiveSubstitutesController@getUsersForSupplant');
    //Oferta servicios
    Route::get('client/service/offers', 'ClientServiceOffersController@index');
    Route::post('client/service/offer', 'ClientServiceOffersController@store');
    Route::put('client/service/offer', 'ClientServiceOffersController@update');
    Route::put('client/service/offer/{id}/status', 'ClientServiceOffersController@updateStatus');
    Route::put('client/service/offer/{id}/offer', 'ClientServiceOffersController@updateOffer');
    Route::delete('client/service/offer/{id}', 'ClientServiceOffersController@destroy');
    //Valoracion de servicios x cliente
    Route::post('client/service/rated', 'ClientServiceRatedController@store');
    Route::post('client/service/store/filter', 'ClientServiceRatedController@storeByFilters');
    //Oferta servicios
    Route::get('client/package/offers', 'ClientPackageOfferController@index');
    Route::post('client/package/offer', 'ClientPackageOfferController@store');
    Route::put('client/package/offer', 'ClientPackageOfferController@update');
    Route::put('client/package/offer/{id}/status', 'ClientPackageOfferController@updateStatus');
    Route::put('client/package/offer/{id}/offer', 'ClientPackageOfferController@updateOffer');
    Route::delete('client/package/offer/{id}', 'ClientPackageOfferController@destroy');
    //Valoracion de paquetes x cliente
    Route::match(['get', 'post'], 'client/packages', 'ClientPackagesController@index');
    Route::get('client/packages/rated', 'ClientPackagesController@index');
    Route::post('client/packages/rated', 'ClientPackageRatedController@store');
    //Configuracion de servicios x cliente
    Route::post('client/service/setting/reserve', 'ClientServiceSettingController@store_reserve');
    Route::post('client/service/setting', 'ClientServiceSettingController@index');

    // Destacados
    Route::get('featured/selectBox', 'InformationImportantServiceController@selectBox');
    Route::resource('featured', 'InformationImportantServiceController');

    //Servicio destacados
    Route::get('service/{service_id}/featured', 'ServiceInformationImportantController@index');
    Route::post('service/featured', 'ServiceInformationImportantController@store');
    Route::delete('service/featured/{id}', 'ServiceInformationImportantController@destroy');

    // Instrucciones
    Route::get('instructions/selectBox', 'InstructionController@selectBox');
    Route::resource('instructions', 'InstructionController');

    //Servicio instrucciones
    Route::get('service/{service_id}/instructions', 'ServiceInstructionController@index');
    Route::post('service/instruction', 'ServiceInstructionController@store');
    Route::delete('service/instruction/{id}', 'ServiceInstructionController@destroy');

    Route::resource('master_sheet', 'MasterSheetsController');
    Route::get('master_sheet/{master_sheet_id}/users', 'MasterSheetsController@get_users');
    Route::get('master_sheet/{master_sheet_id}/days', 'MasterSheetsController@get_days');
    Route::get('master_sheet/{master_sheet_id}/days/services/comments', 'MasterSheetsController@get_all_comments');
    Route::get(
        'master_sheet/{master_sheet_id}/days/services/comments/total',
        'MasterSheetsController@get_total_comments'
    );
    Route::put('master_sheet/{master_sheet_id}/day/{master_sheet_day_id}/day', 'MasterSheetsController@move_day');
    Route::post('master_sheet/{master_sheet_id}/clone/master_sheet', 'MasterSheetsController@clone_master_sheet');
    Route::post(
        'master_sheet/{master_sheet_id}/clone/package_services_stela',
        'MasterSheetsController@clone_package_services_stela'
    );
    Route::post('master_sheet/{master_sheet_id}/clone/file', 'MasterSheetsController@clone_file');

    Route::resource('master_sheet/user', 'MasterSheetUsersController');
    Route::resource('master_sheet/day', 'MasterSheetDaysController');
    Route::post('master_sheet/day/{master_sheet_day_id}/services', 'MasterSheetDaysController@update_services');

    Route::put(
        'master_sheet/day/service/{master_sheet_service_id}/comment',
        'MasterSheetServicesController@update_comment'
    );

    Route::resource('message', 'MessagesController');

    //Paquetes inclusiones
    Route::get('package/{package_id}/inclusions', 'PackageInclusionController@index');
    Route::put('package/inclusions/{id}/status', 'PackageInclusionController@updateStatus');
    Route::put('package/inclusions/{id}/see_client', 'PackageInclusionController@updateStatusSeeClient');
    Route::post('package/inclusions', 'PackageInclusionController@store');
    Route::delete('package/inclusions/{id}', 'PackageInclusionController@destroy');

    Route::get('notes/{entity}/{entity_id}', 'NotesController@index');
    Route::post('notes/{entity}/{entity_id}', 'NotesController@store');
    Route::delete('notes/{entity}/{entity_id}', 'NotesController@destroy');
    Route::get('notes/{entity}/{entity_id}/total', 'NotesController@get_total');
    Route::put('notes/{id}', 'NotesController@update');


    Route::get('pay_modes', 'PayModesController@index');

    //Configuracion de paquete x cliente
    Route::post('client/package/setting/reserve', 'ClientPackageSettingController@store_reserve');
    Route::post('client/package/setting', 'ClientPackageSettingController@index');

    //Categoria de preguntas
    Route::put('question_categories/gallery', 'GaleriesController@galleryQuestionCategory');
    Route::delete('question_categories/image/{id}', 'GaleriesController@removeImageQuestionCategory');
    Route::get('question_categories/selectBox', 'QuestionCategoryController@selectBox');
    Route::resource('question_categories', 'QuestionCategoryController');

    //Preguntas frecuentes
    Route::get('frequent_questions/selectBox', 'FrequentQuestionsController@selectBox');
    Route::resource('frequent_questions', 'FrequentQuestionsController');

    //----------------------------------------Client Ecommerce
    Route::get('client/{client_id}/ecommerce', 'ClientEcommerceController@index');
    Route::post('client/ecommerce', 'ClientEcommerceController@store');
    Route::put('client/ecommerce/{id}', 'ClientEcommerceController@update');

    Route::get('client/{client_id}/ecommerce/question/group', 'ClientEcommerceQuestionController@getGroupByCategory');
    Route::get('client/{client_id}/ecommerce/question', 'ClientEcommerceQuestionController@index');
    Route::post('client/ecommerce/question', 'ClientEcommerceQuestionController@store');
    Route::delete('client/ecommerce/question/{id}', 'ClientEcommerceQuestionController@destroy');

    //Cliente - Politicas de privacidad
    Route::get('client/{client_id}/ecommerce/privacy_policies', 'ClientEcommercePrivacyPoliciesController@index');
    Route::get(
        'client/{client_id}/ecommerce/privacy_policies/information',
        'ClientEcommercePrivacyPoliciesController@getPrivacyPolicies'
    );
    Route::post('client/ecommerce/privacy_policies/', 'ClientEcommercePrivacyPoliciesController@store');
    Route::get('client/{client_id}/ecommerce/privacy_policies/{id}', 'ClientEcommercePrivacyPoliciesController@show');
    Route::put('client/ecommerce/privacy_policies/{id}', 'ClientEcommercePrivacyPoliciesController@update');
    Route::delete(
        'client/{client_id}/ecommerce/privacy_policies/{id}',
        'ClientEcommercePrivacyPoliciesController@destroy'
    );

    //Cliente - Términos y condiciones
    Route::get('client/{client_id}/ecommerce/terms', 'ClientEcommerceTermsController@index');
    Route::get('client/{client_id}/ecommerce/terms/information', 'ClientEcommerceTermsController@getPrivacyPolicies');
    Route::post('client/ecommerce/terms/', 'ClientEcommerceTermsController@store');
    Route::get('client/{client_id}/ecommerce/terms/{id}', 'ClientEcommerceTermsController@show');
    Route::put('client/ecommerce/terms/{id}', 'ClientEcommerceTermsController@update');
    Route::delete('client/{client_id}/ecommerce/terms/{id}', 'ClientEcommerceTermsController@destroy');
    //----------------------------------------

    //Paquetes hoteles opcionales
    Route::post(
        'package/package_plan_rate_category/hotel/searchByCategories/optional',
        'PackageServiceOptionalController@searchHotelsByCategories'
    );
    Route::get(
        'package/package_plan_rate_category_optional/{plan_rate_category_id}',
        'PackageServiceOptionalController@searchByCategory'
    );
    Route::post(
        'package/package_plan_rate_category_optional/{plan_rate_category_id}/hotel/room',
        'PackageServiceOptionalController@storeHotelRoom'
    );
    Route::delete(
        'package/package_plan_rate_category_optional/{plan_rate_category_id}/hotel/room',
        'PackageServiceOptionalController@destroyHotelRoom'
    );
    Route::delete('package/service/optional/service_room', 'PackageServiceOptionalController@deleteServiceRoom');
    Route::delete('package/service/optional/{id}', 'PackageServiceOptionalController@destroy');
    Route::post(
        'package/package_plan_rate_category/hotel/share/optional',
        'PackageServiceOptionalController@shareHotel'
    );
    Route::post('package/service/optional/orders', 'PackageServiceOptionalController@newOrders');

    //Servicios suplementos
    Route::get('service/{id}/supplements', 'ServiceSupplementController@index');
    Route::post('service/supplements', 'ServiceSupplementController@store');
    Route::put('service/supplements/{id}/type', 'ServiceSupplementController@update');
    Route::put('service/supplements/{id}/charge_all_pax', 'ServiceSupplementController@updateChargeAllPax');
    Route::put('service/supplements/{id}/days_to_charge', 'ServiceSupplementController@updateDaysToCharge');
    Route::delete('service/supplements', 'ServiceSupplementController@destroy');


    //Reservas
    Route::get('reservations', 'ReservationController@index');
    Route::get('reservations/{id}/itinerary', 'ReservationController@showItinerary');
    Route::get('reservations/{file_code}/file', 'ReservationController@showDetail');
    Route::get('reservations/{id}/logs', 'ReservationController@getLogs');
    Route::delete('reservations/{id}', 'ReservationController@destroy');
    Route::put('reservations/{id}', 'ReservationController@update');
    Route::put('reservations/{id}/resend_emails', 'ReservationController@resendEmailReservations');
    Route::put('reservations/{id}/executive', 'ReservationController@updateExecutive');
    Route::put('reservations/{id}/client', 'ReservationController@updateClient');
    Route::get('reservations/details', 'ReservationController@getReservationByCode');
    Route::resource('reservations/{id}/reminders', 'ReservationRemindersController');
    Route::delete('reservations/{id}/reminders', 'ReservationRemindersController@destroy');

    //Relacion de una orden con el file
    Route::post('order/relate/reservation', 'ReservationController@relateOrder');

    Route::put('client_contacts/{client_id}/see_in_operations', 'ClientContactsController@updateSeeInOperations');
    Route::resource('client_contacts', 'ClientContactsController');

    //Aurora Clientes
    Route::post('aurora/contact_us', 'AuroraContactUsController@store');
    Route::get('aurora/executives/{client_id}', 'AuroraContactUsController@getExecutivesClient');
    //Highlights - Paquetes
    Route::resource('image_highlights', 'ImageHighlightController');
    Route::post('highlights/upload', 'ImageHighlightController@storeUpload');
    Route::put('image_highlights/{id}/status', 'ImageHighlightController@updateStatus');
    Route::get('image_highlights/{id}/packages', 'ImageHighlightController@getUsedPackage');
    //Paquetes - Highlights
    Route::get('packages/{id}/highlights', 'PackageHighlightController@index');
    Route::post('packages/highlights', 'PackagesController@storeAllHighlights');
    Route::delete('packages/highlights/{id}', 'PackageHighlightController@destroy');
    Route::put('packages/highlights/up_order', 'PackageHighlightController@upHighlight')->name('up_package_highlight');
    Route::put('packages/highlights/down_order', 'PackageHighlightController@downHighlight')->name('down_package_highlight');

    //Paquetes - Politicas de cancelacion
    Route::resource('package/cancellation_policies', 'PackageCancellationPolicyController');

    //Hotel - Liberados
    Route::resource('hotel_released', 'HotelRatePlanReleasedController');
    Route::post('hotel_released/range_released', 'HotelRatePlanReleasedController@storeRangeReleased');
    Route::post('hotel_released/rates/rooms', 'HotelRatePlanReleasedController@getRoomsByRatePlan');
    Route::post('hotel_released/rates/rooms/byRange', 'HotelRatePlanReleasedController@getRoomsByRangeId');
    Route::post('hotel_released/duplicate/period', 'HotelRatePlanReleasedController@duplicatePeriod');
    Route::post('hotel_released/range_released_param', 'HotelRatePlanReleasedController@storeRangeReleasedParam');
    Route::delete('hotel_released/rate/{id}', 'HotelRatePlanReleasedController@removeByRate');
    Route::delete('hotel_released/range/{id}', 'HotelRatePlanReleasedController@removeByRange');

    // Servicios Grupo - Categorias
    Route::get('service_group/selectBox', 'ServiceGroupController@selectBox');
    Route::resource('service_group', 'ServiceGroupController');


    //Services - Liberados
    Route::resource('master_service_released', 'MasterServiceReleasedController');
    Route::delete('master_service_released/{id}', 'MasterServiceReleasedController@destroy');
    Route::put('master_service_released/{id}', 'MasterServiceReleasedController@update');
    Route::post('master_service_released/duplicate/period', 'MasterServiceReleasedController@duplicatePeriod');
    Route::post('/migrate-flags', 'RequestReportAuroraController@updateFlags');

    // Master Services
    Route::get('master_services/{id}', 'MasterServicesController@show');
    Route::post('master_services/import_more', 'MasterServicesController@import_more');
    Route::resource('master_services', 'MasterServicesController');

    Route::post('services/equivalences', 'ServiceController@create_or_update_equivalence');
    Route::post('services/equivalences/composition', 'ServiceController@cud_equivalence_services');
    Route::post('master_service', 'MasterServicesController@cud_master_service');

    Route::post('search_services_by_codes', 'BoardController@search_services_by_codes');

    //hotel - inclusion niños
    Route::get('hotel/{hotel_id}/children/inclusions', 'HotelInclusionChildrenController@index');
    Route::post('hotel/children/inclusions', 'HotelInclusionChildrenController@store');
    Route::put('hotel/children/inclusion/up_order', 'HotelInclusionChildrenController@upService');
    Route::put('hotel/children/inclusion/down_order', 'HotelInclusionChildrenController@downService');
    Route::delete('hotel/children/inclusions/{id}', 'HotelInclusionChildrenController@destroy');
    Route::put('hotel/children/inclusions/{id}/status', 'HotelInclusionChildrenController@updateStatus');

    //hotel - inclusion niños
    Route::get('hotel/{hotel_id}/infant/inclusions', 'HotelInclusionInfantController@index');
    Route::post('hotel/infant/inclusions', 'HotelInclusionInfantController@store');
    Route::put('hotel/infant/inclusion/up_order', 'HotelInclusionInfantController@upService');
    Route::put('hotel/infant/inclusion/down_order', 'HotelInclusionInfantController@downService');
    Route::delete('hotel/infant/inclusions/{id}', 'HotelInclusionInfantController@destroy');
    Route::put('hotel/infant/inclusions/{id}/status', 'HotelInclusionInfantController@updateStatus');

    //Servicios - ots tarifas
    Route::get('service/rates_ots/{service_id}', 'ServiceRatesOtsController@index');
    Route::post('service/rates_ots', 'ServiceRatesOtsController@store');
    Route::delete('service/rates_ots/{rate_id}', 'ServiceRatesOtsController@destroy');

    //Paquetes - tarifas fijas
    Route::get('package/plan_rates/{package_plan_rate_id}/fixed', 'PackageFixedSaleRateController@index');
    Route::post('package/plan_rates/fixed', 'PackageFixedSaleRateController@store');
    Route::post('package/plan_rates/fixed_all', 'PackageFixedSaleRateController@storeAll');

    Route::get('services/{service_year}/export', 'ExportController@serviceExportYear')->where('service_year', '[0-9]+');
    Route::get('services/{service_year}/export_test', 'ExportController@serviceExportYearTest')->where('service_year', '[0-9]+');

    Route::get('service/rate/cost/{service_rate_id}/associate_rate', 'ServiceRateAssociationController@index');
    Route::post('service/rate/cost/{service_rate_id}/associate_rate', 'ServiceRateAssociationController@store');

    Route::get('hotel/{id}/configurations', 'HotelsController@getConfigurations');

    Route::get('regions', 'RegionsController@getRegionsByMarket');
    Route::get('client/validation/ruc', 'ClientValidateConfigurationsController@index');
    Route::post('client/validation/ruc', 'ClientValidateConfigurationsController@store');

    Route::post('regions/markets', 'RegionsController@getRegionsByMarket');
    Route::post('markets/regions', 'RegionsController@getMarketsByRegions');

    Route::get('sheets/teams', 'DepartmentController@getTeamsSheetsAmor');
    Route::get('departments/teams', 'DepartmentController@getTeamsByDepartments');
    Route::get('departments/{department_id}/teams', 'DepartmentTeamController@getTeamsByIdDepartment');
    Route::resource('positions', 'PositionController');
    Route::resource('departments', 'DepartmentController');
    Route::resource('department_team', 'DepartmentTeamController');

    Route::get('menu', 'PermissionsController@menu');

    Route::get('doctypes', 'DoctypeController@list');
    Route::post('passengers-import', 'ExportController@passenger_import');

    Route::get('/cloudinary', 'Controller@update_cloudinary');

    Route::post('/users/unlock/{id}', 'AuthController@unlockAccount');

    Route::get('/routes/commercial-p', 'Controller@getRouteCommercialP');

    Route::get('/stars', 'StartController@list');
});

Route::get('/download_services_latam', 'Controller@download_services_latam');
Route::post('login', 'AuthController@login')->name('login');
Route::post('refresh', 'AuthController@refresh');
Route::post('sso/generate-url', 'AuthController@generateSsoSupportDesk');

Route::post('email-service', 'EmailServiceController@handle');

Route::get('services/hotels/destinations', 'ServiceDemoController@destinations')->middleware(HandleCors::class);
Route::get('services/hotels/available', 'ServiceDemoController@hotels')->middleware(HandleCors::class);
Route::post('services/hotels/available', 'ServiceDemoController@hotels')->middleware(HandleCors::class);
Route::get('services/hotels/reservation', 'ServiceDemoController@reservation')->middleware(HandleCors::class);
Route::get('services/hotels/cancelations', 'ServiceDemoController@cancelations')->middleware(HandleCors::class);

//    Route::get('services/services/clients', 'ServiceServicesController@clients')->middleware(HandleCors::class);
//    Route::get('services/services/cities', 'ServiceServicesController@cities')->middleware(HandleCors::class);
//    Route::get('services/services/categories', 'ServiceServicesController@categories')->middleware(HandleCors::class);
//    Route::get('services/services/types', 'ServiceServicesController@types')->middleware(HandleCors::class);
//    Route::get('services/services/selectBox', 'ServiceServicesController@selectBox')->middleware(HandleCors::class);
//    Route::get('services/services/available', 'ServiceServicesController@services')->middleware(HandleCors::class);
//    Route::get('services/services/{service_id}/schedules', 'ServiceServicesController@schedules')->middleware(HandleCors::class);

Route::post('reset-password', 'ResetPasswordController@sendEmail');
Route::post('reset/password', 'ResetPasswordController@callResetPassword');

Route::post(
    'search_destinies',
    'SearchDestinyController@index'
)->name('search_destinies')->middleware(HandleCors::class)->middleware('auth:api');
Route::post(
    'search_destinies/store',
    'SearchDestinyController@store'
)->name('search_destinies.store')->middleware(HandleCors::class)->middleware('auth:api');
Route::post(
    'search_destinies/by/token_search',
    'SearchDestinyController@getSearchDestiniesByTokenSearch'
)->name('search_destinies.token_search')->middleware(HandleCors::class)->middleware('auth:api');

Route::match(['get', 'post'], 'package/plan_rates/{plan_rate_id}/excel/{service_type_id}', 'PackagePlanRatesController@dataExcel');
Route::match(['get', 'post'], 'package/plan_rates/errors', 'PackagePlanRatesController@rate_errors');

//Duplicado de Tarifas
Route::post('duplicate/rates', 'ScriptsController@duplicateRates');
//validacion de superposicion de tarifas en el proceso de duplicacion de tarifas
Route::post('validate/super_position/duplicate/rates', 'ScriptsController@validateSuperPositionDuplicateRates');
//Duplicado de Clientes y tarifas bloqueadas
Route::post('duplicate/rates_and_clients/locked', 'ScriptsController@duplicateClientsAndHotelsLocked');
//validacion de superposicion  de tarifas
Route::post('validate/super_position_rate', [RatesCostsController::class, 'validateSuperPositionRate']);
//Tag de servicios
Route::resource('tagservices', 'TagServiceController');
//Agregar idioma de guia
Route::post('add/language/guide', 'LanguageGuideController@add');
Route::post('delete/language/guide', 'LanguageGuideController@delete');

// Add Rooms Additional
Route::post('/add/rooms/additional', 'ScriptsController@addRoomsAdditional');
Route::get('/packages/groups/services_limit', 'ScriptsController@get_packages_groups_services_limit');
Route::get('/hotels/query/customize_list', 'ScriptsController@get_hotels_customize_list');
Route::get('/hotels/query/rates_plans_rooms_with_inventories', 'ScriptsController@get_hotel_rates_plans_rooms_with_inventories');
Route::get('/emails/booking_confirmation', 'ScriptsController@get_email_booking_confirmation');
Route::get('/emails/booking_cancellation', 'ScriptsController@get_email_booking_cancellation');

//Update Permissions Client Rate Plans year
Route::get('/client_associations/{rate_plan_id}/{year}', [RatesCostsController::class, 'store_clients_associations'])->middleware('permission:update.permissions_rates');
Route::resource('config_markups', 'ConfigMarkupsController');
Route::post('/modal/paxs/update', 'PaxsController@modal_update');
Route::post('/modal/flights/update', 'FlightController@modal_update');
Route::get('/cosig/access', 'CosigController@access');
Route::get('/cosig/clients', 'CosigController@clients');

// Sprint 2 - FILES
// Route::post('/quote/{quote}/statements', 'QuotesController@statements');

// API V3
Route::post('/v3/login', 'v3\AuthController@login');
Route::post('/v3/verify-token', 'v3\AuthController@verify_token');

//Integration Hyperguest
Route::get('/subscriptions/hyperguest', 'SuscriptionHyperguestController@index');
Route::get('/subscriptions/status/hyperguest', 'SuscriptionHyperguestController@getSubscriptionDetails');
Route::get('/subscriptions/enable/hyperguest', 'SuscriptionHyperguestController@enableSubscription');
Route::get('/subscriptions/disable/hyperguest', 'SuscriptionHyperguestController@disableSubscription');
Route::get('/subscriptions/update/hyperguest', 'SuscriptionHyperguestController@updateSubscription');
Route::get('/subscriptions/full_ary_data_sync/hyperguest', 'SuscriptionHyperguestController@fullAriSync');

//Route::get('/services/test/itinerary', 'ServiceController@test_itinerary');


//Otas Generic
Route::get('otas', 'OtasController@index')->name('list_otas');
Route::post('otas', 'OtasController@store')->name('save_otas');
Route::post('otas/{id}', 'OtasController@update')->name('update_otas');
Route::delete('otas/{id}', 'OtasController@destroy')->name('delete_ota');

Route::post('generic_otas/booking', 'GenericOtaController@booking');
Route::post('generic_otas/reserve', 'GenericOtaController@reserve');
Route::get('generic_otas/list', 'GenericOtaController@index');
Route::post('{extension_generic_ota_service_id}/status', 'GenericOtaController@updateStatus');
Route::post('generic_otas/{extension_generic_ota_service_id}/status_external', 'GenericOtaController@updateStatusExternal');
Route::post('generic_otas/report', 'GenericOtaController@report');
Route::post('generic_otas/reservation/package', 'GenericOtaController@saveFileReservationFromPackage')->name('reservation_from_package');

Route::post('/user_access_report', 'Controller@user_access_report')->name('user_access_report');

// LITO Extension
Route::get('/file_types', 'LitoExtensionController@search_types');
Route::post('/passenger_files', 'LitoExtensionController@save_passenger_files');
Route::post('/passenger_files/recover/{file_passenger_id}', 'LitoExtensionController@recover_passenger_file');
Route::get('/passenger_files/trash/{nroref}', 'LitoExtensionController@search_passenger_files_trash');
Route::delete('/passenger_files/trash/{file_passenger_id}', 'LitoExtensionController@delete_passenger_files_trash');
Route::get('/passenger_files/{nroref}', 'LitoExtensionController@search_passenger_files');
Route::delete('/passenger_files/{file_passenger_id}', 'LitoExtensionController@delete_passenger_files');

Route::post('load_excel', 'Controller@load_excel')->name('load_excel');
Route::post('notifications/templates/import', 'Controller@import_templates_ope')->name('import_templates_ope');
Route::get('notifications/templates', 'Controller@search_templates_ope')->name('search_templates_ope');
Route::post('notifications/templates', 'Controller@save_template_ope')->name('save_template_ope');
Route::post('notifications', 'Controller@send_notification_ope')->name('send_notification_ope');
Route::post('notifications/schedule', 'Controller@send_notification_ope_schedule')->name('send_notification_ope_schedule');
Route::post('notifications/logs', 'Controller@search_logs_ope')->name('search_logs_ope');

Route::get('clients_brasil/export', 'ExportController@clientsBrasilExport');
Route::get('update_client_brasil', 'ClientsController@update_clients_brasil');
Route::get('update_executives_brasil', 'ClientExecutivesController@update_clients_brasil');

Route::get('/report-hyperguest/search', 'ReportsHyperguestController@getSearch');

Route::post('report-hyperguest/import', 'ReportsHyperguestController@upload');
Route::get('report-hyperguest/status', 'ReportsHyperguestController@checkStatus');
Route::get('report-hyperguest/recent', 'ReportsHyperguestController@recentReports');
Route::post('report-hyperguest/delete-all', 'ReportsHyperguestController@deleteAll');
Route::post('reportAdd', 'ReportsHyperguestController@addData');
Route::put('report-hyperguest/updateFee', 'ReportsHyperguestController@updateFee');
Route::post('report-hyperguest/send-mail', 'ReportsHyperguestController@send_Mail');
Route::get('report-hyperguest/list-email', 'ReportsHyperguestController@listEmail');
Route::put('report-hyperguest/add-mail/{id}', 'ReportsHyperguestController@updateIntegrationsHyperguest');


// MASI.PE - NOTIFICATIONS
Route::get('masi/is_time', 'MasiController@is_time');
Route::get('masi/get_clients_all/{type}', 'MasiController@getClientsAll');
Route::get('masi/get_logo/{client_code}', 'MasiController@getLogoGlobal');
Route::get('masi/get_text_skeleton_by_code/{code}/{lang}', 'MasiController@getTextSkeletonGlobal');
Route::post('masi/update_logs', 'MasiController@updateStoreLogs');
Route::post('masi/update_file_detail', 'MasiController@updateFileDetail');

Route::get('reservations/{id}/show_itinerary/{update?}', 'Controller@show_itinerary');
Route::get('reservations/{id}/update_itinerary', 'Controller@update_itinerary');
Route::match(['get', 'post'], 'response_endpoint_lambda', 'Controller@response_endpoint_lambda')->name('response_endpoint_lambda');
Route::get('/customer_service/notifications', 'CustomerServiceController@notifications');

Route::get('info-master-table', 'Controller@table_masters');
Route::get('reservations/{file_number}/process-error', 'Controller@process_error');
Route::get('executives/selectBox', 'Controller@executivesSelectBox');
Route::post('quotes/markup', 'Controller@quotesMarkup');

Route::post('/orders/all', 'Controller@search_orders_ifx');
Route::resource('business_region', 'BusinessRegionController');
Route::post('business_region/{businessRegion}/countries', 'BusinessRegionController@addCountry');
Route::delete('business_region/{businessRegion}/countries/{countryId}','BusinessRegionController@removeCountry');
Route::post('business_region/{businessRegion}/countries/{countryId}','BusinessRegionController@validateDestroyCountry');
Route::post('business_region/validate/{businessRegion}', 'BusinessRegionController@validateDestroy');
Route::get('clients/{client}/business_region', 'ClientBusinessRegionController@index');
Route::post('clients/{client}/business_region', 'ClientBusinessRegionController@store');
Route::get('users/{userId}/regions', 'UserRegionController@getUserRegions');

Route::post('quotes-migration', [HotelsReservationsController::class, 'show_from_quote']);
Route::post('clients/by/codes', 'ClientsController@byCodes');
Route::post('/chat_backgrounds', [Controller::class, 'chat_backgrounds']);

Route::post('/chatbot_master_services', 'Controller@chatbot_master_services');
Route::get('/update-iso-states', 'ImportUbigeoController@update_iso_states');

// Excel Processing Routes
Route::post('/excel/process', 'ExcelCodeAuroraController@processExcel');

Route::get('/extension/dev', function () {
    $response = [
        "status" => "success",
        "links" => [
            "APP_URL_ORDERS_MS" => "https://nzgj32ebhj.execute-api.us-east-1.amazonaws.com/api/v1/",
            "APP_URL_A2" => "https://auroraback.limatours.dev/api/",
            "APP_URL_A3" => "https://zt3igqatal.execute-api.us-east-1.amazonaws.com/api/v1/",
            "APP_URL_SQS" => "https://lxa0zwxqp8.execute-api.us-east-1.amazonaws.com/",
            "APP_URL_S3" => "https://ktzsgc2tza.execute-api.us-east-1.amazonaws.com/prod/s3/",
            "APP_URL_QUOTES_MS" => "https://quotes-ms.limatours.com.pe/api/",
            "APP_URL_NODEIFX" => "https://apigw.limatours.com.pe/api/v1/",
            "APP_URL_MSFILES" => "https://zt3igqatal.execute-api.us-east-1.amazonaws.com/api/v1/commercial/files-ms/",
            "APP_URL_FILES_ONEDB" => "https://zt3igqatal.execute-api.us-east-1.amazonaws.com/api/v1/commercial/ifx/"
        ]
    ];

    return response()->json($response, 200);
})->name('api.extension.dev');
Route::get('client/{client_id}/executives', 'ClientsController@executivesByClientId');
Route::post('quote_amounts', 'Controller@quote_amounts');


Route::post('auth/service-token', 'ServiceAuthController@serviceToken');
Route::get('ext/quotes/import/headers', 'QuotesController@filterInformixHeaders');
Route::post('ext/quote/{quote_id}/import', 'QuotesController@import');
