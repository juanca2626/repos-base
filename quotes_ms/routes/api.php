<?php

use App\Http\Controllers\ClientServiceController;
use App\Http\Controllers\Pentagrama\PentagramaController;
use App\Http\Controllers\MergeQuotesController;
use App\Http\Controllers\QuotesController;
use App\Http\Controllers\QuoteCategoriesController;
use App\Http\Controllers\QuoteServiceRoomsController;
use App\Http\Controllers\PackagesController;
use App\Http\Controllers\QuoteHistoryLogsController;
use App\Http\Controllers\QuotesRateHPullController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:jwt'])->group(function () {
    Route::post('quote/pentagrama/generate', [PentagramaController::class, 'generateQuote']);
    Route::get('quote/{quote_id}/get-rate-hyperguest-pull', [QuotesRateHPullController::class, 'getRateHyperguestPull']);
    Route::post('quote/{quote_id}/update-rate-hyperguest-pull', [QuotesRateHPullController::class, 'updateRateHyperguestPull']);
    Route::post('quote/{quote_id}/clear-rate-hyperguest-pull', [QuotesRateHPullController::class, 'clearRateHyperguestPull']);
    Route::get('quote/byUserStatus/{status}', [QuotesController::class, 'searchByUserStatus']);
    Route::get('quote/searchById', [QuotesController::class, 'searchById']);
    Route::post('package/copy/quote', [QuotesController::class, 'copyPackageToQuote']);
    Route::put('quote/me', [QuotesController::class, 'quoteMe']);
    Route::post('quote/{quote_id}/categories/service', [QuoteCategoriesController::class, 'storeService'])->where('quote_id', '[0-9]+');
    Route::put('quote/update/date_in/services', [QuotesController::class, 'updateDateInService']);
    Route::put('quote/nights/service', [QuotesController::class, 'updateNightsService']);
    Route::post('quote/categories/update/on_request_multiple', [QuoteCategoriesController::class, 'updateOnRequestMultiple']);
    Route::post('quote/service/{quote_service_id}/rooms/replace', [QuoteServiceRoomsController::class, 'replace'])->where('quote_service_id', '[0-9]+');
    Route::put('quote/update/name', [QuotesController::class, 'updateNameQuote']);
    Route::post('quote/save_as', [QuotesController::class, 'saveAs']);
    Route::delete('quote/{quote_id}/forcefullyDestroy', [QuotesController::class, 'forcefullyDestroy'])->where('quote_id', '[0-9]+');
    Route::put('quote/update/date_in', [QuotesController::class, 'updateDateInQuote']);
    Route::put('quote/people', [QuotesController::class, 'updatePeople']);
    Route::post('quote/update-quote-assignment', [QuotesController::class, 'updateQuoteAssignment']);
    Route::post('quotes/{quote_id}/services', [QuotesController::class, 'deleteServices']);
    Route::delete('quote/passengers/{passenger_id}', [QuotesController::class, 'deletePassenger'])->where('passenger_id', '[0-9]+');
    Route::post('quote/ranges/save', [QuotesController::class, 'saveRange']);
    Route::put('quote/age_child', [QuotesController::class, 'updateAgeChild']);
    Route::post('quote/create_or_delete/category', [QuotesController::class, 'createOrDeleteCategory']);
    Route::get('quote/service/occupation_paseengers_hotel', [QuotesController::class, 'occupationPassengersHotel']);
    Route::post('quote/service/occupation_paseengers_hotel', [QuotesController::class, 'storeOccupationPassengersHotel']);
    Route::get('quote/service/occupation_paseengers_hotel_client', [QuotesController::class, 'occupationPassengersHotelClient']);
    Route::put('quote/service/occupation_hotel/general', [QuotesController::class, 'updateOccupationHotelGeneral']);
    Route::post('quote/service/{quote_service_id}/rooms/addFromHeader', [QuoteServiceRoomsController::class, 'addFromHeader'])->where('quote_service_id', '[0-9]+');
    Route::post('quote/service/passenger', [QuotesController::class, 'saveQuoteServicePassenger']);
    Route::post('quote/update/services/passengers', [QuotesController::class, 'updatePassengersService']);
    Route::put('quote/services/{quote_service_id}/hour_in', [QuotesController::class, 'update_hour_in'])->where('quote_service_id', '[0-9]+');
    Route::resource('quotes', QuotesController::class);
    Route::put('quote/optional', [QuotesController::class, 'updateOptional']);
    Route::post('quote/update_order_and_date/services', [QuotesController::class, 'updateDateAndOrderServices']);
    Route::get('quote/{quote_id}/category/{category_id}/skeleton', [QuoteCategoriesController::class, 'wordSkeleton'])->where('quote_id', '[0-9]+')->where('category_id', '[0-9]+');
    Route::get('quote/{quote_id}/category/{category_id}/itinerary', [QuoteCategoriesController::class, 'wordItinerary'])->where('quote_id', '[0-9]+')->where('category_id', '[0-9]+');
    Route::get('quote/imageCreate', [QuoteCategoriesController::class, 'imageCreate']);
    Route::post('quote/imageCreatePackage', [QuoteCategoriesController::class, 'imageCreatePackage']);
    Route::get('quote/existByUserStatus/{status}', [QuotesController::class, 'searchExistByUserStatus'])->where('status', '[0-9]+');
    Route::post('quote/extensions', [PackagesController::class, 'getExtensionsQuote']);
    Route::get('quote/{quote_id}/price-by-ranges', [QuotesController::class, 'priceByRanges'])->where('quote_id', '[0-9]+');
    Route::get('quote/{quote_id}/price-by-passengers', [QuotesController::class, 'priceByPassengers'])->where('quote_id', '[0-9]+');
    Route::post('quote_client/extension', [QuotesController::class, 'add_client_extension']);
    Route::post('quote/{quote}/statements', [QuotesController::class, 'statements']);
    Route::get('quote/ubigeo/selectbox/destinations', [QuotesController::class, 'getDestinations']);
    Route::get('quote/{quote_id}/categories/services', [QuoteCategoriesController::class, 'searchAllServices']);
    Route::resource('quotes/{id}/history_logs', QuoteHistoryLogsController::class);
    Route::post('quote/{quote_id}/share/quote', [QuotesController::class, 'share']);
    Route::get('quote/check_editing/{quote_id}', [QuotesController::class, 'checkEditing']);
    Route::post('quote/{quote_id}/replaceQuoteInFront', [QuotesController::class, 'replaceQuoteInFront']);
    Route::post('quote/{quote_id}/copy/quote', [QuotesController::class, 'copy']);
    Route::post('quote/categories/copy', [QuoteCategoriesController::class, 'copy']);
    Route::put('quotes/update_notes/{quote_id}', [QuotesController::class, 'update_notes']);

    // news
    Route::put('quotes/showInPopup/{value}', [QuotesController::class, 'updateShowInPopup']);
    Route::post('quote/{quote_id}/discountPermissionMail', [QuotesController::class, 'discountPermissionMail']);
    Route::post('quote/order/relate', [QuotesController::class, 'relateOrder']);
    Route::post('quote/{quote_id}/discount', [QuotesController::class, 'updateDiscount']);
    Route::post('quote/{quote_id}/convertToPackage', [QuotesController::class, 'convertToPackage']);
    Route::post('quote/{quote_id}/categories/flight', [QuoteCategoriesController::class, 'storeFlight'])->where('quote_id', '[0-9]+');
    Route::post('quote/notes', [QuotesController::class, 'createNote']);
    Route::patch('quote/notes/{note_id}', [QuotesController::class, 'updateNote'])->where('note_id', '[0-9]+');
    Route::post('quote/notes/responses', [QuotesController::class, 'createResponse']);
    Route::put('quote/copy_first_passenger_data', [QuotesController::class, 'copyFirstPassengerData']);
    Route::put('quote/replace/service', [QuotesController::class, 'replaceService']);
    Route::delete('quote/service/rooms/{id}/rate_plan_room', [QuoteServiceRoomsController::class, 'destroy']);
    Route::put('update/quote/markup', [QuotesController::class, 'updateMarkup']);
    Route::post('quote/restore', [QuotesController::class, 'restore']);
    Route::post('quote/{quote_id}/category/{category_id}/replaceMultiple', [QuoteCategoriesController::class, 'replaceMultiple']);
    Route::post('quote/{quote_id}/category/{category_id}/add', [QuoteCategoriesController::class, 'add']);
    Route::get('quote/{quote_id}/check/services_amounts', [QuoteCategoriesController::class, 'checkQuoteServicesAmounts']);

    Route::put('quote/extension-update', [QuotesController::class, 'update_extension']);
    Route::put('quote/services/{quote_service_id}/schedule', [QuotesController::class, 'update_schedule']);

    Route::post('quotes/reverse-engineering', [QuotesController::class, 'reverseEngineering']);
    Route::put('quote/finish-clone-file/{file_id}', [QuotesController::class, 'finishCloneFile']);

    // Clients
    Route::post('client/cover-image-creation', [ClientServiceController::class, 'createCoverImage']);
    Route::post('quote/merge', [MergeQuotesController::class, 'merge']);
    Route::post('quote/{quote_id}/unmerge', [MergeQuotesController::class, 'unmerge'])->where('quote_id', '[0-9]+');
});

Route::post('public/quote/imageCreatePackage', [QuoteCategoriesController::class, 'imageCreatePackage']);
