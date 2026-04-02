<?php

use Illuminate\Support\Facades\Route;
use Src\Modules\Catalogs\Http\Controllers\CatalogController;
use Src\Modules\File\Presentation\Http\Controllers\VipController;
use Src\Modules\File\Presentation\Http\Controllers\FileController;
use Src\Modules\File\Presentation\Http\Resources\FileNoteResource;
use Src\Modules\File\Presentation\Http\Controllers\EventController;
use Src\Shared\Presentation\Http\Controllers\CityController;
use Src\Modules\File\Presentation\Http\Controllers\CityIsoController;
use Src\Modules\File\Presentation\Http\Controllers\FileVipController;
use Src\Modules\File\Presentation\Http\Controllers\VoucherController;
use Src\Modules\File\Presentation\Http\Controllers\CategoryController;
use Src\Modules\File\Presentation\Http\Controllers\FileNoteController;
use Src\Shared\Presentation\Http\Controllers\CountryController;
use Src\Modules\File\Presentation\Http\Controllers\FileFlightController;
use Src\Modules\File\Presentation\Http\Controllers\FileReportController;
use Src\Shared\Presentation\Http\Controllers\CurrencyController;
use Src\Shared\Presentation\Http\Controllers\LanguageController;
use Src\Shared\Presentation\Http\Controllers\SupplierController;
use Src\Modules\File\Presentation\Http\Controllers\FileBalanceController;
use Src\Modules\File\Presentation\Http\Controllers\FileNoteOpeController;
use Src\Modules\File\Presentation\Http\Controllers\FileServiceController;
use Src\Modules\File\Presentation\Http\Controllers\ServiceZeroController;
use Src\Modules\File\Presentation\Http\Controllers\FileCategoryController;
use Src\Modules\File\Presentation\Http\Controllers\StatusReasonController;
use Src\Modules\File\Presentation\Http\Controllers\FileDebitNoteController;
use Src\Modules\File\Presentation\Http\Controllers\FileHotelRoomController;
use Src\Modules\File\Presentation\Http\Controllers\FileItineraryController;
use Src\Modules\File\Presentation\Http\Controllers\FilePassengerController;
use Src\Modules\File\Presentation\Http\Controllers\FileStatementController;
use Src\Modules\File\Presentation\Http\Controllers\MasterServiceController;
use Src\Shared\Presentation\Http\Controllers\ServiceTimeController;
use Src\Shared\Presentation\Http\Controllers\TypeServiceController;
use Src\Modules\File\Presentation\Http\Controllers\FileCreditNoteController;
use Src\Shared\Presentation\Http\Controllers\NotificationController;
use Src\Modules\File\Presentation\Http\Controllers\FileNoteGeneralController;
use Src\Modules\File\Presentation\Http\Controllers\FileServiceUnitController;
use Src\Modules\File\Presentation\Http\Controllers\FileServiceZeroController;

//use Src\Modules\File\Presentation\Http\Controllers\SupplierController;
use Src\Modules\File\Presentation\Http\Controllers\FileAmountReasonController;
use Src\Modules\File\Presentation\Http\Controllers\FileProcessStelaController;
use Src\Modules\File\Presentation\Http\Controllers\FileHotelRoomUnitController;
use Src\Modules\File\Presentation\Http\Controllers\FileNoteItineraryController;
use Src\Modules\File\Presentation\Http\Controllers\FileAmountTypeFlagController;
use Src\Modules\File\Presentation\Http\Controllers\FileItineraryFlightController;
use Src\Modules\File\Presentation\Http\Controllers\FileReasonStatementController;
use Src\Modules\File\Presentation\Http\Controllers\Download\SkeletonPdfController;
use Src\Modules\File\Presentation\Http\Controllers\FileTemporaryServiceController;
use Src\Modules\File\Presentation\Http\Controllers\Download\ItineraryPdfController;
use Src\Modules\File\Presentation\Http\Controllers\Download\ItineraryWordController;
use Src\Modules\File\Presentation\Http\Controllers\FileNoteClassificationController;
use Src\Modules\File\Presentation\Http\Controllers\FilePassengerModifyPaxController;
use Src\Modules\File\Presentation\Http\Controllers\FileServiceCompositionController;
use Src\Modules\File\Presentation\Http\Controllers\FileNoteExternalHousingController;
use Src\Shared\Presentation\Http\Controllers\ServiceClassificationController;
use Src\Modules\File\Presentation\Http\Controllers\ExecuteProcessesManuallyController;
use Src\Modules\File\Presentation\Http\Controllers\ImportMasterServicesStelaController;
use Src\Modules\File\Presentation\Http\Controllers\Download\CreateDebitNotePdfController;
use Src\Modules\File\Presentation\Http\Controllers\Download\CreateItineraryPdfController;
use Src\Modules\File\Presentation\Http\Controllers\Download\CreateStatementPdfController;
use Src\Modules\File\Presentation\Http\Controllers\ServiceCompositionSuppliersController;

use Src\Modules\File\Presentation\Http\Controllers\Download\CreateCreditNotePdfController;
use Src\Modules\File\Presentation\Http\Controllers\FileItineraryTemporaryServiceController;
use Src\Modules\File\Presentation\Http\Controllers\Download\DownloadStatementsPdfController;
use Src\Modules\File\Presentation\Http\Controllers\FileCommunicationNewReservationController;
use Src\Modules\File\Presentation\Http\Controllers\FileStatementReasonsModificationController;
use Src\Modules\File\Presentation\Http\Controllers\FileCommunicationModifyReservationController;
use Src\Modules\File\Presentation\Http\Controllers\FileReservationProviderInformationController;
use Src\Modules\File\Presentation\Http\Controllers\LogController;
use Src\Modules\File\Presentation\Http\Controllers\OpeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

require base_path('src/Modules/Catalogs/Http/routes.php');
require base_path('src/Modules/FileV2/Http/routes.php');
require base_path('src/Modules/Notes/Http/routes.php');

Route::get('/files/list-note-classification', [FileNoteClassificationController::class, 'index']);
Route::post('/files/validation/flight/countries/isos', [CityIsoController::class, 'validateInternationalFlight']);

Route::get('/direct/files/number/{file_number}', [FileController::class, 'show_file_number']);
Route::get('/direct/files/basic/{file_number}', [FileController::class, 'show_file_number_basic_info']);
Route::get('/direct/files/{file_id}/reports/{hotel_code}', [FileReportController::class, 'search_hotel']);
Route::put('/direct/files/{file_id}/passengers-all', [FilePassengerController::class, 'update_all']);
Route::post('/direct/files/reservation/providers', [FileReservationProviderInformationController::class, 'index']);
Route::get('/direct/files/{file_id}/itineraries/{itinerary}', [FileItineraryController::class,'show']);
Route::post('/direct/files/{file_id}/itineraries', [FileItineraryController::class,'store']);
Route::put('/direct/files/{file_id}/itineraries/{itinerary}', [FileItineraryController::class,'update']);

Route::get('/execute-processes', [ExecuteProcessesManuallyController::class, 'services_compositions']);
Route::get('/execute-processes/update-send-communication', [ExecuteProcessesManuallyController::class, 'update_send_communication']);
Route::get('/execute-processes/add-reason-statement', [ExecuteProcessesManuallyController::class, 'add_reason_statement']);
Route::get('/execute-processes/add-statement-reasons-modification', [ExecuteProcessesManuallyController::class, 'add_statement_reasons_modification']);
Route::get('/execute-processes/change-channel-code-hyperguest', [ExecuteProcessesManuallyController::class, 'change_channel_code_hyperguest']);
Route::get('/execute-processes/update-confirmation-code-hyperguest', [ExecuteProcessesManuallyController::class, 'update_confirmation_code_hyperguest']);
Route::get('/execute-processes/update-suggested-accommodation', [ExecuteProcessesManuallyController::class, 'update_suggested_accommodation']);
Route::get('/execute-processes/update-date-files', [ExecuteProcessesManuallyController::class, 'update_date_files']);
Route::get('/execute-processes/update-country-flight', [ExecuteProcessesManuallyController::class, 'update_country_flight']);
Route::get('/execute-processes/update-date-service-paquete', [ExecuteProcessesManuallyController::class, 'update_date_service_paquete']);
Route::get('/execute-processes/update-deadline-statement', [ExecuteProcessesManuallyController::class, 'update_deadline_statement']);
Route::get('/execute-processes/update-pass-to-open', [ExecuteProcessesManuallyController::class, 'update_pass_to_open']);
Route::get('/execute-processes/activate-file-services', [ExecuteProcessesManuallyController::class, 'activate_file_services']);
Route::get('/execute-processes/cancel-itineraries', [ExecuteProcessesManuallyController::class, 'cancel_itineraries']);
Route::get('/execute-processes/delete-itineraries', [ExecuteProcessesManuallyController::class, 'delete_itinerary_service']);
Route::get('/execute-processes/clear-file-service-accommodations', [ExecuteProcessesManuallyController::class, 'clear_file_service_accommodations']);

Route::get('/execute-processes/{file_id}/import-master-services-stela', ImportMasterServicesStelaController::class);

Route::get('/direct/languages', [LanguageController::class, 'index']);
Route::get('/languages', [LanguageController::class, 'index']);
Route::post('/notifications', [NotificationController::class, 'store']);

Route::post('/files/list-files-stela', [FileController::class, 'list_files_stela']);
Route::post('/files/save-files-stela', [FileController::class, 'save_files_stela']);
Route::post('/files/save-files-stela-all', [FileController::class, 'save_files_stela_all']);

Route::get('/files/categories', [CategoryController::class, 'index']);
Route::get('/files/reason-statement', [FileReasonStatementController::class, 'index']);
Route::get('/files/test', [FileController::class, 'test_aurora']);
Route::post('/files/search-hotel-rates', [FileController::class, 'search_hotel_rates']);
Route::post('/files/update-hotel-rates', [FileController::class, 'update_hotel_rates']);
Route::get('/files/{file_id}/generate-master-services', [FileController::class, 'generate_master_services']);
Route::get('/files/{file_id}/process-import-file-stela', [FileProcessStelaController::class, 'process_import_file_stela']);
Route::put('/files/{file_id}/view-protected-rate', [FileController::class, 'view_protected_rate']);
Route::put('/files/{file_id}/itineraries/{itinerary_id}/view-protected-rate', [FileItineraryController::class, 'view_protected_rate']);
Route::get('/files/{file_id}/itinerary-details', [FileController::class, 'itinerary_details']);
Route::get('/files/{file_id}/itinerary-download-word', ItineraryWordController::class);
Route::get('/files/{file_id}/itinerary-download-pdf', ItineraryPdfController::class);
Route::get('/files/{file_id}/show-changes-statement', [FileController::class, 'show_changes_statement']);
Route::post('/files/{file_id}/update-itinerary-add-statement', [FileItineraryController::class, 'update_itinerary_add_statement']);


Route::get('/files/{file_id}/skeleton', [FileController::class, 'skeleton']);
// Route::get('/files/{file_id}/skeleton-download-word', FileController::class);
Route::get('/files/{file_id}/skeleton-pdf', SkeletonPdfController::class);

Route::get('/files/{file_id}/hotel-list', [FileController::class, 'hotel_list']);
Route::get('/files/{file_id}/romming-list', [FileController::class, 'romming_list']);
Route::get('/files/{file_id}/romming-list-excel', [FileController::class, 'romming_list_excel']);

Route::get('/files/statement/concepts', [FileStatementController::class, 'concepts']);
Route::post('/files/statement/blocked', [FileStatementController::class, 'blocked_list']);
Route::post('/files/statement/des-blocked', [FileStatementController::class, 'des_blocked']);
Route::get('/files/statement/reasons-for-modification', [FileStatementReasonsModificationController::class, 'index']);
Route::get('/files/{file_id}/statement', [FileStatementController::class, 'report']);
Route::put('/files/{file_id}/statement', [FileStatementController::class, 'update']);
Route::post('/files/{file_id}/statement', [FileStatementController::class, 'store']);
Route::get('/files/{file_id}/statement/details', [FileStatementController::class, 'show']);
Route::get('/files/{file_id}/statement/download', CreateStatementPdfController::class);
Route::get('/files/{file_id}/statements/download-multiple', DownloadStatementsPdfController::class);

Route::get('/files/{file_id}/credit-note', [FileCreditNoteController::class, 'index']);
Route::post('/files/{file_id}/credit-note', [FileCreditNoteController::class, 'store']);
Route::put('/files/{file_id}/credit-note/{credit_note_id}', [FileCreditNoteController::class, 'update']);
Route::delete('/files/{file_id}/credit-note/{credit_note_id}', [FileCreditNoteController::class, 'destroy']);
Route::get('/files/{file_id}/credit-note/download', CreateCreditNotePdfController::class);

Route::get('/files/{file_id}/debit-note', [FileDebitNoteController::class, 'index']);
Route::post('/files/{file_id}/debit-note', [FileDebitNoteController::class, 'store']);
Route::put('/files/{file_id}/debit-note/{debit_note_id}', [FileDebitNoteController::class, 'update']);
Route::delete('/files/{file_id}/debit-note/{debit_note_id}', [FileDebitNoteController::class, 'destroy']);
Route::get('/files/{file_id}/debit-note/download', CreateDebitNotePdfController::class);


Route::get('/files/{file_id}/notification-aws-logs', [FileController::class, 'notificationAwsLogs']);
Route::post('/files/{file_id}/notification-forward', [FileController::class, 'notificationForward']);
Route::post('/files/{file_id}/categories', [FileCategoryController::class, 'store']);

//Auth::user() solo funcionanra si hay un middleware en la ruta
Route::middleware(['cognito.auth'])->group(function () {
    Route::post('/files/{file_id}/reopened', [FileController::class, 'status_reopened']);
    Route::post('/files/{file_id}/canceled', [FileController::class, 'status_canceled']);
    Route::post('/files/{file_id}/blocked', [FileController::class, 'status_blocked']);
    Route::post('/files/{file_id}/unlock', [FileController::class, 'status_unlock'])->where(['file_id' => '[0-9]+']);;
    Route::post('/files/unlock/{file_number}', [FileController::class, 'status_unlock_file_number']);
    Route::post('/files/{file_id}/closed', [FileController::class, 'status_closed']);
    Route::post('/files/{file_id}/by-invoiced', [FileController::class, 'status_by_invoiced']);
    Route::post('/files/{file_id}/invoiced-notinvoiced', [FileController::class, 'invoiced_notinvoiced']);
    Route::post('/files/{file_id}/status-in-ope', [FileController::class, 'status_in_ope']);
});

Route::post('/files/{file_id}/processing', [FileController::class, 'processing']);


Route::get('/files/list', [FileController::class, 'list']);
Route::get('/files/statistics', [FileController::class, 'statistics']);
Route::get('/files/download', [FileController::class, 'download']);
Route::get('/files/status', [FileController::class, 'all_status']);
Route::get('/files/revision-stages', [FileController::class, 'all_stages']);
Route::get('/files/have-invoice', [FileController::class, 'find_have_invoice']);
Route::get('/files/{file_id}/itinerary', [FileController::class, 'itinerary']);
// Route::get('/files/executives', [FileController::class, 'executives']); 
Route::get('/files/boss', [FileController::class, 'boss']);
Route::get('/files/clients', [FileController::class, 'clients']);
Route::get('/files/import/{file_id}', [FileController::class, 'import']);
// Route::put('/files/{file_id}/passengers', [FileController::class, 'update_passengers']);
// Route::put('/files/{file_id}/accommodations', [FileController::class, 'update_accommodations']);
Route::get('/files/file_itinerary/{itinerary_id}', [FileController::class, 'file_itinerary']);
Route::resource('/files/{file_id}/vips', FileVipController::class);
Route::delete('/files/{file_id}/vips/{vip_id}', [FileVipController::class, 'destroy']);
// Route::put('/files/{file_id}/serie', [FileController::class, 'add_serie']);
// Route::delete('/files/{file_id}/serie', [FileController::class, 'remove_serie']);
Route::resource('files', FileController::class)->only([
    'index', 'store', 'update', 'show'
])->parameters([
    'files' => 'id'
])->where(['id' => '[0-9]+']);

Route::post('/files/create-basic-file', [FileController::class, 'create_basic_file']);
Route::put('/files/{id}/update-basic-file', [FileController::class, 'update_basic_file']);
Route::delete('/files/{id}/basic-file', [FileController::class, 'delete_basic_file']);
Route::post('/files/{id}/clone-file', [FileController::class, 'clone_file']);

Route::get('/files/number/{file_number}', [FileController::class, 'show_file_number']);
Route::get('/files/basic/{id}', [FileController::class, 'show_basic_info']);

// Route::resource('/vips', VipController::class)->only([
//     'index', 'store', 'show', 'update', 'destroy'
// ])->parameters([
//     'vips' => 'id'
// ]);

Route::resource('/vips', VipController::class, [
    'names' => [
        'index' => 'vipss.index',
        'store' => 'vipss.store',
        'show' => 'vipss.show',
        'update' => 'vipss.update',
        'destroy' => 'vipss.destroy'
    ],
])->only([
    'index', 'store', 'show', 'update', 'destroy'
])->parameters([
    'vips' => 'id'
]);


Route::put('/files/{id}/activate', [FileController::class, 'activate']);

Route::resource('/status-rate', FileAmountTypeFlagController::class)->only(['index']);
Route::resource('/reasons-rate', FileAmountReasonController::class)->only(['index']);

Route::put('/files/itineraries/{id}/hotel-on-request', [FileItineraryController::class, 'hotel_on_request']);
Route::put('/files/itineraries/{id}/schedule', [FileItineraryController::class, 'update_schedule']);
Route::put('/files/services/{id}/date', [FileServiceController::class, 'update_date']);
Route::put('/files/services/{id}/schedule', [FileServiceController::class, 'update_schedule']);
Route::get('/files/services/{id}/schedule', [FileServiceController::class, 'index']);
Route::put('/files/services/compositions/{id}/schedule', [FileServiceCompositionController::class, 'update_schedule']);
Route::put('/files/services/compositions/{id}/update_notification', [FileServiceCompositionController::class, 'update_notification']);
Route::put('/files/services/{id}/amount', [FileServiceController::class, 'update_amount']);
Route::resource('/files/itineraries/{id}/services', FileServiceController::class);


Route::resource('/files/services/{id}/compositions', FileServiceCompositionController::class);
//nueva
Route::get('/files/hotel-rooms/{id}/rooming', [FileHotelRoomController::class, 'index']);

Route::put('/files/hotel-rooms/{id}/amount', [FileHotelRoomController::class, 'update_amount']);

Route::put('/files/itineraries/{id}/passengers', [FileItineraryController::class, 'update_passengers']);
Route::put('/files/itineraries/{id}/number-of-passengers', [FileItineraryController::class, 'update_number_of_passengers']);
Route::put('/files/hotel-rooms/{id}/passengers', [FileHotelRoomController::class, 'update_passengers']);
Route::put('/files/hotel-rooms/units/{id}/passengers', [FileHotelRoomUnitController::class, 'update_passengers']);


Route::get('/files/itineraries/service-temporary', [FileItineraryController::class, 'search_itinerary_service_temporary']);
Route::get('/files/itineraries/{id}', [FileItineraryController::class, 'show']);
Route::put('/files/itineraries/{id}/amount-sale', [FileItineraryController::class, 'update_amount_sale']);

Route::delete('/files/itineraries/{id}/rooms', [FileItineraryController::class, 'cancel_hotels']);
Route::delete('/files/itineraries/{id}/services-validate', [FileItineraryController::class, 'cancel_services_validate']);
Route::delete('/files/itineraries/{id}/services', [FileItineraryController::class, 'cancel_services']);

Route::post('/files/itineraries/{id}/communication-hotel-cancellation', [FileItineraryController::class, 'communication_hotel_cancellation']);
// Route::post('/files/itineraries/{id}/communication-service-cancellation', [FileItineraryController::class, 'communication_service_cancellation']);

Route::middleware(['cognito.auth'])->group(function () {
    Route::post('/files/quote/board', [FileController::class, 'quote_from_file']); // Verificación..
    Route::post('/files/{id}/quote/board', [FileController::class, 'send_quote_to_board']); // Redirección..
    Route::get('/files/{id}/quote/merge-reverse-engineering', [FileController::class, 'merge_reverse_engineering']); // Redirección..
});

Route::post('/files/{id}/communication-hotel-new', [FileController::class, 'communication_hotel_new']);
Route::post('/files/itineraries/{id}/communication-hotel-modification', [FileItineraryController::class, 'communication_hotel_modification']);

Route::post('/files/{id}/communication-hotel-new-reserva', FileCommunicationNewReservationController::class);
Route::post('/files/itineraries/{id}/communication-hotel-modification-reserva', FileCommunicationModifyReservationController::class);

// Route::post('/files/{file_id}/itineraries/communication-new-service', [FileController::class, 'communication_new_service']);
// Route::post('/files/{file_id}/itineraries/{id}/communication-update-service', [FileItineraryController::class, 'communication_update_service']);
// Route::post('/files/{file_id}/itineraries/{id}/communication-cancellation-service', [FileItineraryController::class, 'communication_cancellation_service']);

Route::resource('/status-reasons', StatusReasonController::class)->only(['index']);
Route::get('/reasons-for-cancellation',  [StatusReasonController::class, 'reasons_for_cancellation']);
Route::resource('/files/{file_id}/passenger-modify-paxs', FilePassengerModifyPaxController::class);
Route::get('/files/{id}/cancel-notification', [FileController::class, 'cancelNotification']);
Route::resource('/files/{file_id}/itineraries', FileItineraryController::class);
Route::get('/files/latest-itineraries', [FileItineraryController::class, 'latest_itineraries']);
Route::get('/files/{file_id}/itinerary_flights', [FileItineraryController::class, 'itinerary_flights']);
Route::put('/files/{file_id}/itineraries/{file_itinerary_id}/flight/city-iso', [FileItineraryFlightController::class , 'updateCityIso']);
Route::resource('/files/{file_id}/itineraries/{file_itinerary_id}/flight', FileItineraryFlightController::class);
Route::resource('/direct/files/{file_id}/passengers', FilePassengerController::class);
Route::put('/files/{file_id}/passengers-all', [FilePassengerController::class, 'update_all']);
Route::put('/files/{file_id}/passengers-accommodations', [FilePassengerController::class, 'accommodations']);
Route::get('/files/{file_id}/passenger-download', [FilePassengerController::class, 'download']);
Route::get('/files/{file_id}/passenger-download-amadeus', [FilePassengerController::class, 'download_amadeus']);
Route::get('/files/{file_id}/passenger-download-perurail', [FilePassengerController::class, 'download_perurail']);


Route::post('/files/pass-to-ope-confirmed', [OpeController::class, 'passToOpe']);
// Route::post('/files/send-to-ope', [OpeController::class, 'sendToOpe']);
Route::post('/files/send-to-ope-history', [OpeController::class, 'historyPassToOpe']);

Route::post('/temporary-service', [FileTemporaryServiceController::class, 'store']);
Route::get('/temporary-service', [FileTemporaryServiceController::class, 'search_services']);
Route::get('/temporary-service/{id}', [FileTemporaryServiceController::class, 'show']);

Route::post('/files/{file_id}/itineraries/temporary-service', [FileItineraryTemporaryServiceController::class, 'store']);
Route::post('/files/{file_id}/itineraries/{id}/associate-temporary-service', [FileItineraryTemporaryServiceController::class, 'associate']);
Route::post('/files/{file_id}/itineraries/{id}/communication-temporary-service', [FileItineraryTemporaryServiceController::class, 'communication']);
Route::post('/files/{file_id}/itineraries/{id}/communication-temporary-service-type', [FileItineraryTemporaryServiceController::class, 'communication_by_type']);

Route::post('/files/{file_id}/itineraries/communication-service-news', [FileItineraryTemporaryServiceController::class, 'communication']);
Route::post('/files/{file_id}/itineraries/communication-service-news-type', [FileItineraryTemporaryServiceController::class, 'communication_by_type']);

Route::post('/files/{file_id}/itineraries/{id}/communication-service-modify', [FileItineraryTemporaryServiceController::class, 'communication']);
Route::post('/files/{file_id}/itineraries/{id}/communication-service-modify-type', [FileItineraryTemporaryServiceController::class, 'communication_by_type']);

Route::post('/files/{file_id}/itineraries/{id}/communication-service-cancellation', [FileItineraryTemporaryServiceController::class, 'communication']);
Route::post('/files/{file_id}/itineraries/{id}/communication-service-cancellation-type', [FileItineraryTemporaryServiceController::class, 'communication_by_type']);


Route::post('/files/{file_id}/itineraries/{id}/communication-service-after-booking', [FileItineraryTemporaryServiceController::class, 'communication_after_booking']);



//Route::get('/suppliers', [SupplierController::class, 'index']);
Route::get('/master-services', [MasterServiceController::class, 'index']);

Route::get('/event', [EventController::class, 'sendEvent']);
Route::get('/test', [EventController::class, 'test']);
Route::get('/user', [EventController::class, 'user']);
Route::get('/sqs', [EventController::class, 'sqs']);
Route::get('/send-pass-to-ope', [EventController::class, 'sendToOpe']);

// Route::get('/update-send-communication', [EventController::class, 'update_send_communication']);

Route::resource('/cities', CityController::class)->only(['index','show','store']);
Route::resource('/service-time', ServiceTimeController::class)->only(['index']);

Route::resource('/countries', CountryController::class)->only(['index','show','store']);
Route::resource('/currency', CurrencyController::class)->only(['index','show','store']);
Route::resource('/service-zero', ServiceZeroController::class)->only(['index','store']);
Route::get('/service-zero/filter', [ServiceZeroController::class, 'filter']);

Route::resource('/services-classification', ServiceClassificationController::class)->only(['index','show','store']);
Route::put('/services-classification/{id}', [ServiceClassificationController::class, 'update']);

Route::resource('/type-services', TypeServiceController::class)->only(['index','show','store']);

Route::get('/suppliers/{code}', [SupplierController::class, 'getSupplierData']);

//Route::resource('/files/{file_id}/itineraries', FileItineraryController::class);

Route::resource('/files/{file_id}/service-zero', FileServiceZeroController::class);

Route::post('/service-compositions/{id}/suppliers', [ServiceCompositionSuppliersController::class, 'updateOrCreateSupplier']);




//*** export section *****/

Route::get('/files/export', [FileController::class, 'download']);
Route::get('/files/{file_id}/passenger-export', [FilePassengerController::class, 'download']);
Route::post('/files/{file_id}/passenger-import', [FilePassengerController::class, 'store']);
Route::get('/files/{file_id}/passenger-export-amadeus', [FilePassengerController::class, 'download_amadeus']);
Route::get('/files/{file_id}/passenger-export-perurail', [FilePassengerController::class, 'download_perurail']);

Route::get('/files/{file_id}/export-itinerary-flights', [FileItineraryController::class, 'exportItineraryFlights']);

// Route::get('/files/{id}/service-export-schedule', [FileServiceController::class, 'exportToExcel']);

// Route::get('/files/hotel-list/{id}/export', [FileHotelRoomController::class, 'exportToExcel']);


Route::get('/files/{file_id}/reports', [FileReportController::class, 'list']);
Route::get('/files/{file_id}/reports/{hotel_code}', [FileReportController::class, 'search_hotel']);
Route::put('/files/hotel-rooms/{id}/confirmation-code', [FileHotelRoomController::class, 'confirmation_code']);
Route::put('/files/hotel-rooms/units/{id}/confirmation-code', [FileHotelRoomUnitController::class, 'confirmation_code']);
Route::put('/files/hotel-rooms/units/{id}/changes-rq-wl', [FileHotelRoomUnitController::class, 'changes_rq_wl']);
Route::put('/files/hotel-rooms/units/{id}/changes-wl-code', [FileHotelRoomUnitController::class, 'changes_wl_code']);

Route::put('/files/services/compositions/{id}/confirmation-code', [FileServiceCompositionController::class, 'confirmation_code']);
Route::put('/files/services/compositions/units/{id}/confirmation-code', [FileServiceUnitController::class, 'confirmation_code']);
Route::put('/files/services/compositions/units/{id}/changes-rq-wl', [FileServiceUnitController::class, 'changes_rq_wl']);
Route::put('/files/services/compositions/units/{id}/changes-wl-code', [FileServiceUnitController::class, 'changes_wl_code']);

// Route::put('/file-service/{file_itinerary_id}/rate', [FileItineraryController::class, 'update_status_rate']);
// Route::put('/file-service/rate/neg-locked', [FileItineraryController::class, 'update_status_rate_neg_locked']);

// Route::post('/files/trigger-job-voucher', [VoucherController::class, 'triggerJob']);
// Route::get('/sqs-aws-log', [VoucherController::class, 'notificationAwsLogs']);
// Route::get('/search-provider-code', [SupplierController::class, 'getProviderByCode']);
// Route::get('/files/provider/voucher/{fileId}', [VoucherController::class, 'getAllServicesByFile']);

Route::post('/files/flight/information', [FileFlightController::class, 'informationFlight']);

Route::prefix('/files/{file_id}')->group(function () {
    Route::get('note/all', [FileNoteController::class,'list_note_all']);
    Route::get('note/all/requirement', [FileNoteController::class,'list_note_all_requirement']);
    Route::apiResource('note', FileNoteController::class)->except(['create','show']);
    Route::apiResource('note/external/housing', FileNoteExternalHousingController::class)->only([
        'index',   // GET (listar)
        'store',   // POST (crear)
        'show',    // GET (SHOW)
        'update',  // PUT/PATCH (actualizar)
        'destroy'  // DELETE (eliminar)
    ]);

    Route::get('/list-note-itinerary/{itinerary_id}', [FileNoteItineraryController::class, 'list_note_itinerary']);
    Route::post('/create-note-itinerary/{itinerary_id}', [FileNoteItineraryController::class, 'create_note_itinerary']);
    Route::put('/update-note-itinerary/{itinerary_id}/{note_id}', [FileNoteItineraryController::class, 'update_note_itinerary']);
    Route::delete('/delete-note-itinerary/{note_id}', [FileNoteItineraryController::class, 'delete_note_itinerary']);

    Route::get('/notes/general',[FileNoteGeneralController::class,'index']);
    Route::post('/notes/general',[FileNoteGeneralController::class,'create']);
    Route::put('/notes/general/{note_id}',[FileNoteGeneralController::class,'update']);

});

Route::post('/files/flight/information', [FileFlightController::class, 'informationFlight']);
Route::put('/files/{file_id}/itinerary/{id}/flight/date', [FileItineraryFlightController::class, 'updateDate']);
Route::post('/files/note/{id}/status/ope', [FileNoteController::class,'update_note_status_ope']);
Route::get('/files/{file_number}/note/ope',[FileNoteOpeController::class,'index']);
Route::get('/files/balance', [FileBalanceController::class, 'index']);
Route::get('/files/balance/download', [FileBalanceController::class, 'download']);
Route::get('/logs/{date?}',[LogController::class, 'downloadLog'])->where('date', '\d{4}-\d{2}-\d{2}');
Route::delete('/files/itineraries/{itinerary_id}/delete-note', [FileNoteItineraryController::class, 'delete_note_itinerary_service']);


