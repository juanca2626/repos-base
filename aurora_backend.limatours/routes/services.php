<?php


/*
|--------------------------------------------------------------------------
| Services Routes
|--------------------------------------------------------------------------
*/

use App\Http\Files\Controller\FilesController;
use App\Http\Services\Controllers\ClientHotelsController;
use App\Http\Services\Controllers\ClientPackagesController;
use App\Http\Services\Controllers\HotelsUpSellingController;
use App\Http\Services\Controllers\ReservesController;
use App\Http\Services\Controllers\ServiceController;
use App\Http\Services\Controllers\ServiceControllerAvailable;
use App\Http\Services\Controllers\HotelsReservationsController;
use App\Http\Services\Controllers\SearchTokenHotelController;
use App\Http\Services\Controllers\ServicesReservationsController;
use App\Http\Services\Controllers\VistaController;
use Barryvdh\Cors\HandleCors;
use Illuminate\Support\Facades\Route;


// hotels
Route::middleware([HandleCors::class, 'hotels.api'])->group(function () {
//    Route::post('/test/available', [ServiceController::class,'hotels_new']);
    Route::post('hotels/list/packages', [ServiceController::class, 'hotel_list_packages']);
    // misc
    Route::any('executives', [HotelsReservationsController::class, 'executives']);
    Route::any('typeclass', [ClientHotelsController::class, 'classHotel']);

    // avail
    Route::get('hotels/destinations', [ServiceController::class, 'destinations']);
    Route::get('hotels/quotes/destinations', [ServiceController::class, 'destinationsQuote']);
    Route::post('hotels/available/readonly', [ServiceController::class, 'hotels_readonly']);
    Route::post('hotels/available', [ServiceControllerAvailable::class, 'hotels_channels']);
    Route::post('hotels/available/quote', [ServiceControllerAvailable::class, 'hotels_channels']);
    Route::get('hotel/{id}', [ServiceController::class, 'hotel']);
    Route::match(["GET", "POST"], 'hotels/list', [ServiceController::class, 'hotelsList']);
    Route::post('hotels/services', [ServiceController::class, 'hotels_services']);
    Route::post('hotel/services', [ServiceController::class, 'hotel_services']);
    Route::post('calculate/rate/total_amount', [ServiceController::class, 'calculateRateTotalAmount']);
    Route::post('calculate/selection/rate/total_amount', [ServiceController::class, 'calculate_selection_rate_total_amount']);
    // Route::get('check/token_search/{token_search}', [ServiceController::class, 'checkTokenSearchExternal']);
    Route::get('check/token_search/{token_search}', [ServiceController::class, 'checkTokenSearchExternal']);

    // upSelling
    Route::post('hotels/up-selling', [HotelsUpSellingController::class, 'list']);

    // reservations
    Route::post('hotels/reservation', [HotelsReservationsController::class, 'showSelection']);
    Route::post('hotels/reservation/add', [HotelsReservationsController::class, 'store']);

    Route::get('hotels/reservation/{nro_file}', [HotelsReservationsController::class, 'show']);
    Route::post('reservations/hotels/list', [HotelsReservationsController::class, 'list']);
    Route::post('reservations/list/pending', [ReservesController::class, 'getReservationPending']);
    Route::post('reservations/hotels/cancellation', [HotelsReservationsController::class, 'cancellation']);
    Route::post('hotels/reservation/add/confirmation', [HotelsReservationsController::class, 'storeNroConfirmation']);

    Route::get('check_token_service/{token_search}', [HotelsReservationsController::class, 'searchTokenService']);

    // resrvation modification
    Route::post('reservations/hotels/modification', [ServiceController::class, 'reservation_modification']);
    Route::post('reservations/hotels/add-room', [HotelsReservationsController::class, 'add_hotel_room']);
    Route::post('reservations/hotels/cancel-room', [HotelsReservationsController::class, 'cancel_hotel_room']);
    Route::post('reservations/hotels/dates-update', [HotelsReservationsController::class, 'change_hotel_dates']);
    // Reservation Package
    Route::post('reservations/package', [ServiceController::class, 'reservation_package']);
    //Reservation Quote
    Route::post('reservations/quote', [ServiceController::class, 'reservation_quote']);
    // Reservation General
    Route::post('reservation', [ReservesController::class, 'reserve']);
    Route::post('external/reservation', [ReservesController::class, 'saveReservationExternalClient']);

    //Relacion de un file con un pedido de stella
    Route::post('reservations/order/relate', [HotelsReservationsController::class, 'relateOrder']);
    Route::get('file/{fileNumber}', [\App\Http\Controllers\ReservationController::class, 'getFileNumber']);

    Route::get('hotels/allotment/destinations', [ServiceController::class, 'destinationsHotels']);

});

//Servicios
Route::middleware([HandleCors::class, 'services.api'])->group(function () {

    Route::any('/service_types', [ServicesReservationsController::class, 'service_types']);
    Route::any('/type_class', [ServicesReservationsController::class, 'type_class']);
    Route::any('/categories', [ServicesReservationsController::class, 'categories']);
    Route::any('/experiences', [ServicesReservationsController::class, 'experiences']);
    Route::any('/classifications', [ServicesReservationsController::class, 'classifications']);

    Route::get('/destination_services', [ServiceController::class, 'destinations_services']);
    Route::get('/destination_masi', [ServiceController::class, 'destinations_masi']);
    Route::post('/experiences_masi', [ServiceController::class, 'experiences_masi']);
    Route::post('/available', [ServiceController::class, 'services']);
    Route::post('/supplement/add', [ServiceController::class, 'addSupplementToService']);
    Route::post('/details', [ServiceController::class, 'services_details']);

    Route::post('reservations/services/cancellation', [ServiceController::class, 'cancellation_services']);
    Route::post('reservations/service/cancellation', [ServiceController::class, 'cancellation_service_file']);

    Route::post('reservations/packages/cancellation', [ServiceController::class, 'cancellation_packages']);

    //--------------------------------------------Vista------------------------------------------------
    Route::post('/destination/search/all', [VistaController::class, 'searchDestinationsAll']);
    Route::get('/client/{client_id}/contact/ecommerce', [VistaController::class, 'getContactEcommerceByClient']);
    //------Servicios
    Route::get('/destination/count/state', [ServiceController::class, 'destinationsCountByState']);
    Route::get('/services/recommendations', [ServiceController::class, 'serviceRecommendations']);
    //------Paquetes
    Route::post('/packages/available', [ServiceController::class, 'packages']);
    Route::get('/destinations/packages', [ServiceController::class, 'destinations_packages']);
    Route::post('/packages/details', [ServiceController::class, 'packages_details']);


    //--------------------------------------------Paquetes Clientes------------------------------------------------

    Route::get('/clients/parameter_search', [ClientPackagesController::class, 'parameter_search']);
    Route::post('/client/packages/best_sellers', [ClientPackagesController::class, 'getPackageBestSellers']);
    Route::post('/client/packages', [ClientPackagesController::class, 'getPackageClient']);
    Route::get('/client/packages', [ClientPackagesController::class, 'getPackageClientGet']);
//    Route::get('/packages/cache', [ClientPackagesController::class, 'getPackageCache']);
    Route::post('/client/cache-package-select', [ClientPackagesController::class, 'getCachePackageSelect']);
    Route::post('/client/packages/details', [ClientPackagesController::class, 'getPackageDetail']);
    Route::post('/client/reservation/package', [ServiceController::class, 'reservationPackageClient']);
    Route::get('/client/reservation/{file_code}/package', [ClientPackagesController::class, 'getReservationPackage']);
    Route::post('/client/reservation/send', [ClientPackagesController::class, 'sendReservation']);
    Route::post('/client/package/itinerary', [ClientPackagesController::class, 'createItinerary']);

    Route::post('/search-token-hotels', [SearchTokenHotelController::class, 'index']);
    Route::post('/search-file-hotel-rates', [FilesController::class, 'search_hotel_rates']);
});


