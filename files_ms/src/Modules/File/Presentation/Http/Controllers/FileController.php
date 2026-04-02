<?php

namespace Src\Modules\File\Presentation\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\File\Application\Mappers\FileMapper;
use Src\Modules\File\Application\Exports\FilesExport;
use Src\Modules\File\Application\Exports\RommingListExport;
use Src\Modules\File\Domain\ValueObjects\File\MergeFileQuote;
use Src\Modules\File\Presentation\Http\Resources\FileResource;
use Src\Modules\File\Domain\ValueObjects\File\QuoteExtructureA2;
use Src\Modules\File\Domain\ValueObjects\File\FileValidateStatus;
use Src\Modules\File\Application\UseCases\Queries\RemoveFileSerie;
use Src\Modules\File\Application\UseCases\Queries\SearchAllStages;
use Src\Modules\File\Application\UseCases\Queries\SearchAllStatus;
use Src\Modules\File\Application\UseCases\Queries\UpdateFileSerie;
use Src\Modules\File\Domain\ValueObjects\File\FileSkeletonDetails;
use Src\Modules\File\Application\UseCases\Queries\SearchFilesQuery;
use Src\Modules\File\Domain\ValueObjects\File\FileHotelListDetails;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraryDetails;
use Src\Modules\File\Application\UseCases\Queries\SearchHaveInvoice;
use Src\Modules\File\Domain\ValueObjects\File\FileExtructureQuoteA2;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Application\UseCases\Commands\CreateFileCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileCommand;
use Src\Modules\File\Domain\ValueObjects\File\FileRommingListDetails;
use Src\Modules\File\Presentation\Http\Traits\ProcesStatementDetails;
use Src\Modules\File\Application\UseCases\Queries\FindFileExistsQuery;
use Src\Modules\File\Infrastructure\Jobs\ProcessFileServicesStellaJob;
use Src\Modules\File\Application\UseCases\Commands\FileActivateCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFilesPassToOpe;
use Src\Modules\File\Application\UseCases\Queries\SearchFileStelaQuery;
use Src\Modules\File\Presentation\Http\Requests\File\CreateFileRequest;
use Src\Modules\File\Presentation\Http\Resources\FileBasicInfoResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Src\Modules\File\Application\UseCases\Queries\FindFileByNumberQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFilesStatistics;
use Src\Modules\File\Application\UseCases\Queries\FindFileBasicInfoQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchTokenHotelsQuery;
use Src\Modules\File\Application\UseCases\Commands\CreateFileStelaCommand;
use Src\Modules\File\Application\UseCases\Commands\FileInOpeFinishCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdFieldsQuery;
use Src\Modules\File\Application\UseCases\Queries\SerachAuroraInformation;
use Src\Modules\File\Application\UseCases\Commands\FileStatusChangeCommand;
use Src\Modules\File\Presentation\Http\Resources\FileHotelRoomRateResource;
use Src\Modules\File\Presentation\Http\Traits\CommunicationServiceTemplate;
use Src\Modules\File\Application\UseCases\Queries\SearchHotelRateFilesQuery;
use Src\Modules\File\Presentation\Http\Requests\File\CreateCloneFileRequest;
use Src\Modules\File\Presentation\Http\Requests\File\CreateFileBasicRequest;
use Src\Modules\File\Presentation\Http\Requests\File\CreateFileToOpeRequest;
use Src\Modules\File\Application\UseCases\Commands\CreateFileStelaAllCommand;
use Src\Modules\File\Application\UseCases\Commands\FileChangeProcessingCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileItineraryByIdQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFileCreateExistQuery;
use Src\Modules\File\Application\UseCases\Queries\SerachFileCanceledServices;
use Src\Modules\File\Presentation\Http\Resources\FileNumberBasicInfoResource;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileStatementCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileNumberBasicInfoQuery;
use Src\Modules\File\Application\UseCases\Commands\FileViewProtectedRateCommand;
use Src\Modules\File\Application\UseCases\Queries\SearchInA2DetailsServiceQuery;
use Src\Modules\File\Application\UseCases\Commands\UpdateAmountFromAuroraCommand;
use Src\Modules\File\Application\UseCases\Queries\SearchFileLatestItineriesQuery;
use Src\Modules\File\Application\UseCases\Commands\FileFieldInvoicedChangeCommand;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraryServiceByCommunication;
use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;
use Src\Modules\File\Presentation\Http\Requests\File\UpdateStatusCanceledFileRequest;
use Src\Modules\File\Presentation\Http\Requests\File\UpdateStatusReopenedFileRequest;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryProfitabilityCommand;
use Src\Modules\File\Infrastructure\ExternalServices\AwsNotificationLog\AwsNotificationLog;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileItineraryTotalCostAmountCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdCompletQuery;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;

class FileController extends Controller
{
    use ApiResponse, CommunicationServiceTemplate;
    use ProcesStatementDetails;

    public function list(Request $request): JsonResponse
    {
        try {
            $params = FileMapper::fromRequestSearch($request);
            $files = (new SearchFilesQuery($params))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $files);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function list_files_stela(Request $request): JsonResponse
    {
        try {
            $params = FileMapper::fromRequestSearch($request);
            $files = (new SearchFileStelaQuery($params))->handle();

            return response()->json($files, 200);

            // return $this->successResponse(ResponseAlias::HTTP_OK, $files);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function save_files_stela(Request $request): JsonResponse
    {
        try {

            $result = (new CreateFileStelaCommand($request->input()))->execute();

            return response()->json($result, 200);

            // return $this->successResponse(ResponseAlias::HTTP_OK, $files);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function save_files_stela_all(Request $request): JsonResponse
    {
        try {

            $params = FileMapper::fromRequestSearch($request);
            $result = (new CreateFileStelaAllCommand($params))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $result);

        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $params = FileMapper::fromRequestSearch($request);
            $files = (new SearchFilesQuery($params))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $files);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function search_hotel_rates(Request $request): JsonResponse
    {
        try {

            $files = (new SearchHotelRateFilesQuery($request->input()))->handle();
            $rooms = [];
            foreach($files as $file){
                if(!in_array($file['file_hotel_room']['room_name'], $rooms)){
                    array_push($rooms, $file['file_hotel_room']['room_name']);
                }
            }

            $files = FileHotelRoomRateResource::collection($files);
            return $this->successResponse(ResponseAlias::HTTP_OK, [
                'files' => $files,
                'modify' => implode(",", $rooms)
            ]);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update_hotel_rates(Request $request): JsonResponse
    {
        try {

            // $file = 'people.txt';   // debe de estar creado en la carpeta public
            // $current = file_get_contents($file);
            // $current .= json_encode($request->input())."n";
            // file_put_contents($file, $current);


            $itineraries = (new UpdateAmountFromAuroraCommand($request->input()))->execute();

            foreach($itineraries as $itinerary_id => $file_id){

                // actualizamos el total_cost_amount itinerario
                (new UpdateFileItineraryTotalCostAmountCommand($itinerary_id))->execute();

                // actualizamos el profitability itinerario
                (new UpdateFileItineraryProfitabilityCommand($itinerary_id))->execute();

                // actualizamos el file statement
                (new UpdateFileStatementCommand($file_id))->execute();

            }


            return $this->successResponse(ResponseAlias::HTTP_OK, 'true');
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function store(CreateFileRequest $request): FileResource|JsonResponse
    {             
        // try {
            $file_db = (new FindFileExistsQuery(null, $request->input('file_code')))->handle();
            if($file_db !== null)
            {
                (new FileValidateStatus(
                    $file_db['status']
                ));

                $file_db = true;
            }

            $newFile = FileMapper::fromRequestCreate($request,null , $file_db);
            $file = (new CreateFileCommand($newFile))->execute();

            if($file_db === null)
            {
                // (new CreateFileStatementCommand($file->id->value()))->execute();

            }else{
                // aqui se esta actualizando el file, mirar si cambia el importe para crear un nuevo statement.
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $file);
            // return new FileResource($file); // se comento esta linea

        // }catch (\Exception $e) {
        //     return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // } catch (\DomainException $domainException) {
        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // }
    }

    public function create_basic_file(CreateFileBasicRequest $request): FileResource|JsonResponse
    {
        try {

            $newFile = FileMapper::fromRequestCreateBasic($request);

            $file = (new SearchFileCreateExistQuery($newFile))->handle();
            if($file === null){
                $file = (new CreateFileCommand($newFile))->execute();
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $file);
            // return new FileResource($file);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(Request $request, $id): FileResource|JsonResponse
    {

        // try {
            $file = (new FindFileByIdAllQuery($id))->handle();
            return new FileResource($file);
        // }catch (\Exception $e) {
        //     return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // } catch (\DomainException $domainException) {
        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // }
    }

    public function show_basic_info(Request $request, $id): FileBasicInfoResource|JsonResponse
    {

        // try {
            $file = (new FindFileBasicInfoQuery($id))->handle();
            return new FileBasicInfoResource($file);
        // }catch (\Exception $e) {
        //     return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // } catch (\DomainException $domainException) {
        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // }
    }

    public function show_file_number_basic_info(Request $request, $file_number): FileNumberBasicInfoResource|JsonResponse
    {
        // try {
            $file = (new FindFileNumberBasicInfoQuery($file_number,$request->input()))->handle();
            return new FileNumberBasicInfoResource($file);
        // }catch (\Exception $e) {
        //     return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // } catch (\DomainException $domainException) {
        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // }
    }

    public function show_file_number($file_number): FileResource|JsonResponse
    {

        try {
            $file = (new FindFileByNumberQuery($file_number))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $file);
            // return new FileResource($file);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function file_itinerary($itinerary_id): JsonResponse
    {
        try {
            $itinerary = (new FindFileItineraryByIdQuery($itinerary_id))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $itinerary);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($id))->handle())['status']
            ));

            $updatefile = FileMapper::fromRequestUpdate($request);

            $file = (new UpdateFileCommand($id, $updatefile))->execute();
            $update = true;

            // if(count($updatefile['passengers']) > 0)
            // {
            //     $passengers = (new UpdatePassengersCommand($id, $updatefile['passengers']))->execute();
            //     $accommodations = (new UpdateAccommodationsCommand($id, 'all', 0, $updatefile['passengers']))
            //         ->execute();
            // }

            $response = ($request->__get('flag_lambda')) ? $file : $update;

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // public function update_passengers(Request $request, $file_id): JsonResponse
    // {
    //     try {
    //         $passengers = (new UpdatePassengersCommand($file_id, $request->input('passengers')))->execute();
    //         $accommodations = (new UpdateAccommodationsCommand($file_id, 'all', 0, $request->input('passengers')))
    //             ->execute();

    //         return $this->successResponse(ResponseAlias::HTTP_OK, [
    //             'passengers' => $passengers,
    //             'accommodations' => $accommodations,
    //         ]);
    //     }
    //     catch(\DomainException $domainException)
    //     {
    //         return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
    //     }
    // }

    // public function update_accommodations(Request $request, $file_id): JsonResponse
    // {
    //     try {
    //         $type = (!@empty('type')) ? $request->__get('type') : 'all'; // all, service, hotel, room
    //         $type_id = $request->__get('type_id');

    //         $accommodations = (new UpdateAccommodationsCommand(
    //             $file_id, $type, $type_id, $request->input('passengers')
    //         ))->execute();

    //         return $this->successResponse(ResponseAlias::HTTP_OK, [
    //             'accommodations' => $accommodations,
    //         ]);
    //     }
    //     catch(\DomainException $domainException)
    //     {
    //         return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
    //     }
    // }

    public function statistics(): JsonResponse
    {
        try {
            $date = date("Y-m-d");
            $files = (new SearchFilesStatistics($date))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $files);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function download(Request $request): object
    {
        try {
            $params = FileMapper::fromRequestSearch($request);
            $files = (new SearchFilesQuery($params))->handle();
            $files = $files->map(function ($item) {
                return $item;
            })->toArray();
            return Excel::download(new FilesExport($files), 'files.xlsx');
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function add_serie(Request $request, $file_id): JsonResponse
    {
        try {
            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($file_id))->handle())['status']
            ));

            $params = FileMapper::fromRequestSerie($request);
            $file = (new UpdateFileSerie($params))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $file);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function remove_serie(Request $request, $file_id): JsonResponse
    {
        try {

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($file_id))->handle())['status']
            ));

            $params = FileMapper::fromRequestSerie($request);
            $file = (new RemoveFileSerie($params))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $file);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function all_status(Request $request): JsonResponse
    {
        try {
            $params = FileMapper::fromRequestStatus($request);
            $all_status = (new SearchAllStatus($params))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $all_status);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function all_stages(): JsonResponse
    {
        try {
            $all_stages = (new SearchAllStages())->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $all_stages);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function find_have_invoice(Request $request): JsonResponse
    {
        try {
            $params = FileMapper::fromRequestStatus($request);
            $have_invoices = (new SearchHaveInvoice($params))->handle();
            return $this->successResponse(ResponseAlias::HTTP_OK, $have_invoices);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function itinerary(Request $request, $id): JsonResponse
    {
        try {
            $aurora = new AuroraExternalApiService();
            // $authorization = $request->header('authorization');
            // $aurora->setAuthorization($authorization);

            $file = (new FindFileByIdFieldsQuery($id, ['reservation_id']))->handle();
            $response = $aurora->searchQuoteDetail($file['reservation_id']);
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function executives(Request $request): JsonResponse
    {
        try {
            $array_codes = $request->__get('array_codes');
            $search = $request->__get('search');
            $data = [
                'search' => strtoupper($search),
                'array_codes' => $array_codes,
            ];
            $aurora = new AuroraExternalApiService();
            $response = $aurora->searchExecutives($data);
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function boss(Request $request): JsonResponse
    {
        try {
            $array_codes = $request->__get('array_codes');
            $search = $request->__get('search');
            $data = [
                'array_codes' => $array_codes,
                'search' => strtoupper($search),
            ];
            $aurora = new AuroraExternalApiService();
            $response = $aurora->searchBoss($data);
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function clients(Request $request): JsonResponse
    {
        try {
            $search = $request->__get('search');
            $data = [
                'search' => strtoupper($search),
            ];
            $aurora = new AuroraExternalApiService();
            // $authorization = $request->header('authorization');
            // $aurora->setAuthorization($authorization);

            $response = $aurora->searchClients($data);
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function quote_from_file(Request $request): JsonResponse
    {
        try {
            // Verificar si existe cotización en el tablero..
            $aurora = new AuroraExternalApiService();
            // $authorization = $request->header('authorization');
            // $aurora->setAuthorization($authorization);
            $quote_aurora = $aurora->validateOpenQuote();
            $response = [];

            if($quote_aurora == false)
            {
                $response['flag_in_board'] = false;
            }
            else
            {
                $response['flag_in_board'] = $quote_aurora;
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function send_quote_to_board(Request $request, $id): JsonResponse
    {
        try {
            $force = $request->input('force', false);
            $aurora = new AuroraExternalApiService();
            // $authorization = $request->header('authorization');
            // $aurora->setAuthorization($authorization);
            $quote_aurora = $aurora->validateOpenQuote();

            if($quote_aurora->success == true)
            {
                if($force == false){
                    throw new \DomainException('You have an open quote, you must close it before continuing');
                }

                $quote_aurora = $aurora->forcefullyDestroy($quote_aurora->quote_open_id);

                if($quote_aurora->success == false)
                {
                    if($force == false){
                        throw new \DomainException('open quote could not be closed');
                    }
                }
            }

            $file = (new FindFileByIdCompletQuery($id))->handle();
            $fileQuoteA2 = (new FileExtructureQuoteA2($file))->jsonSerialize();
// return $this->successResponse(ResponseAlias::HTTP_OK, $fileQuoteA2);
            $quote_aurora = $aurora->sendQuote($fileQuoteA2);

            if($quote_aurora->success == false){
                throw new \DomainException($quote_aurora->message);
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $fileQuoteA2);


        }
        catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function  merge_reverse_engineering(Request $request, $id): JsonResponse
    {
        try {

            $aurora = new AuroraExternalApiService();
            // $authorization = $request->header('authorization');
            // $aurora->setAuthorization($authorization);


            $file = (new FindFileByIdAllQuery($id))->handle();
            $files = (new FileExtructureQuoteA2($file))->jsonSerialize();

            $quoteAurora = $aurora->searchOpenQuote();
            $quoteAurora = (array) $quoteAurora;

            if(count($quoteAurora) == 0){
                throw new \DomainException("The file does not exist in aurora2 or is not open");
            }


            $fileQuoteA2 = (new QuoteExtructureA2($quoteAurora))->jsonSerialize();

            if($files['file_number'] != $fileQuoteA2['file_number']){
                throw new \DomainException("The file does not exist in aurora2 or is not open");
            }

            $merge = (new MergeFileQuote($files, (array) $fileQuoteA2))->jsonSerialize();



            // dd($fileQuoteA2);

            return $this->successResponse(ResponseAlias::HTTP_OK, $merge);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }


    }

    // : JsonResponse
    public function communication_hotel_new(Request $request, $id)
    {
        try {

            $params = $request->input(); $subject = ""; $html = "";

            unset($params['reservation_delete']); // cuando estamos creando un reserva nueva no necesitamos validar files

            $file = (new FindFileByIdAllQuery($id))->handle();
            $dataSearchHotel = (new SearchTokenHotelsQuery($params, $file))->handle();
            if (!empty($dataSearchHotel)) {
                $subject = "Reserva confirmada [".$dataSearchHotel['file_number']."] - ".$dataSearchHotel['itineraries']['hotel_code']."]."."-".$dataSearchHotel['customer_name'];
                $view = 'emails.reservations.hotels.reservation.confirmation';
                if(!$dataSearchHotel['itineraries']['confirmation_status']){
                    $subject = "Solicitud de reserva [".$dataSearchHotel['file_number']."] - ".$dataSearchHotel['itineraries']['hotel_code']."]."."-".$dataSearchHotel['customer_name'];
                    $view = 'emails.reservations.hotels.reservation.solicitude';
                }

                // return view($view, ["reservation" => $dataSearchHotel, "notas" => $params['notas'], "attachments" => $params['attachments'], "type" => "sale"]);
                $html_hotel = view($view, ["reservation" => $dataSearchHotel, "notas" => $params['notas'], "attachments" => $params['attachments'], "type" => "cost"] )->render();
                $html_executive = view($view, ["reservation" => $dataSearchHotel, "notas" => $params['notas'], "attachments" => $params['attachments'], "type" => "sale"] )->render();
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, [
                'subject' => $subject,
                'html' => [
                    'hotel' => $html_hotel,
                    'client' => $html_executive,
                    'executive' => $html_executive
                ],
                'executive_email' => [$dataSearchHotel['executive_email']],
                'clients_email' => $dataSearchHotel['client_executives'],
                'hotel_contacts' =>  $dataSearchHotel['hotel_contacts']
            ]);

        } catch (\DomainException $domainException) {

            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }

    public function activate(Request $request, $id)
    {
        try
        {

            $status_reason_id = $request->input('status_reason_id');
            if(!isset($status_reason_id) or $status_reason_id == ''){
                throw new \DomainException('the status_reason_id is null');
            }

            $response = (new FileActivateCommand($id, $status_reason_id))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);

        } catch (\Exception $domainException) {

            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }


    }

    // Falta esto !! En la anulación de file se está considerando la notificación a las especialistas y sus jefes si es que el file es mayor a $20mil USD
    public function cancelNotification(Request $request, $id)
    {
        try {

            $fileCanceledServices = (
                new SerachFileCanceledServices($id)
            )->handle();

            $subject = "Anulación de file ".$fileCanceledServices['file']; $html = "";

            // if($fileCanceledServices['status'] == false) {
            //     throw new \DomainException('the file is not canceled');
            // }

            // return view('emails.files.cancellation', [
            //         "services" => $fileCanceledServices,
            //         "notas" => $request->input('notas'),
            //         "attachments" => $request->input('attachments')
            // ]);
            $html = view('emails.files.cancellation', [
                "services" => $fileCanceledServices,
            ])->render();


            $response = [
                'html' => $html,
                'subject' => $subject,
                'emails' => $fileCanceledServices['emails']
            ];

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }

    public function status_reopened(UpdateStatusReopenedFileRequest $request, $id)
    {
        try {

            $file = ((new FindFileByIdAllQuery($id))->handle());

            if($file['status'] == 'OK'){
                throw new \DomainException('the file is open');
            }

            $result = (new FileStatusChangeCommand($id, 'OK', $request->input('status_reason_id') ))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $result);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }

    public function status_canceled(UpdateStatusCanceledFileRequest $request, $id)
    {
        try {

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($id))->handle())['status']
            ));

            $result = (new FileStatusChangeCommand($id, 'XL', $request->input('status_reason_id')))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $result);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }

    public function status_blocked(Request $request, $id)
    {
        try {

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($id))->handle())['status']
            ));

            $result = (new FileStatusChangeCommand($id, 'BL', 15))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $result);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }

    public function status_unlock(Request $request, $id)
    {
        try {

            $file = ((new FindFileByIdAllQuery($id))->handle());

            // if($file['status'] != 'BL'){
            //     throw new \DomainException('the file not is blocked');
            // }

            $result = true;

            if($file)
            {
                $result = (new FileStatusChangeCommand($file['id'], 'OK', 15))->execute();
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $result);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }

    public function status_unlock_file_number(Request $request, $file_number)
    {
        try {

            $file = (new FindFileNumberBasicInfoQuery($file_number,$request->input()))->handle();

            $result = true;

            if($file)
            {
                if($file['status'] != 'BL'){
                    throw new \DomainException('the file not is blocked');
                }

                $result = (new FileStatusChangeCommand($file['id'], 'OK', 15))->execute();
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $result);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }

    public function status_closed(Request $request, $id)
    {
        try {

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($id))->handle())['status']
            ));

            $result = (new FileStatusChangeCommand($id, 'CE', 5))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $result);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }

    public function status_by_invoiced(Request $request, $id)
    {
        try {

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($id))->handle())['status']
            ));

            $result = (new FileStatusChangeCommand($id, 'PF', 14))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $result);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }

    public function invoiced_notinvoiced(Request $request, $id)
    {
        try {

            $result = (new FileFieldInvoicedChangeCommand($id, $request->invoiced))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $result);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }

    public function status_in_ope(CreateFileToOpeRequest $request, $id)
    {
        try {

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($id))->handle())['status']
            ));

            $result = (new FileInOpeFinishCommand($id, $request->status))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $result);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }

    public function processing(Request $request, $id)
    {
        try {

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($id))->handle())['status']
            ));

            $result = (new FileChangeProcessingCommand($id, 1))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $result);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

    }

    public function clone_file(CreateCloneFileRequest $request, $id)
    {
        try {
            $force = $request->input('force', false);
            $aurora = new AuroraExternalApiService();
            // $authorization = $request->header('authorization');
            // $aurora->setAuthorization($authorization);
            $quote_aurora = $aurora->validateOpenQuote();

            if($quote_aurora->success == true)
            {
                if($force == false){
                    throw new \DomainException('You have an open quote, you must close it before continuing');
                }

                $quote_aurora = $aurora->forcefullyDestroy($quote_aurora->quote_open_id);

                if($quote_aurora->success == false)
                {
                    if($force == false){
                        throw new \DomainException('open quote could not be closed');
                    }
                }
            }

            $file = (new FindFileByIdAllQuery($id))->handle();
            $fileQuoteA2 = (new FileExtructureQuoteA2($file, $request->input()))->jsonSerialize();

            $quote_aurora = $aurora->sendQuote($fileQuoteA2);

            if($quote_aurora->success == false){
                throw new \DomainException($quote_aurora->message);
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $fileQuoteA2);

        }
        catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function notificationAwsLogs(Request $request){
        $aws = new AwsNotificationLog();
        $masterServices = $aws->getLogs($request->input('object_id'));
        return $this->successResponse(ResponseAlias::HTTP_OK, $masterServices);
    }

    public function notificationForward(Request $request){
        $id = $request->input('id-aws');
        $aws = new AwsNotificationLog();
        $logs = $aws->getLogs($request->input('object_id'));

        try{
            $email = '';

            foreach($logs as $log){
                if($log->id == $id){
                    $email = $log->html;
                    break;
                }

            }

            if($email == ''){
                throw new \DomainException('id not found');
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, [
                "html" => $email
            ]);

        }
        catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function communication_new_service(Request $request, $file_id)
    {
        try
        {
            $adults = $request->input('total_adults') ;
            $children = $request->input('total_children');
            $services_news = $this->getServicesMaster($request->input());

            if(count($services_news) == 0){
                throw new \DomainException('the service does not exist');
            }

            $file = (new FindFileByIdAllQuery($file_id))->handle();

            $serachAuroraInformation = (new SerachAuroraInformation([
                'executive_code' => $file['executive_code'],
                'hotel_id' => '',
                'client_id' => $file['client_id']
            ]))->handle();

            $file_header = [
                'file_number' => $file['file_number'],
                'description' => $file['description'],
                'created_at' => date('Y-m-d'),
                'adults' => $adults,
                'children' => $children,
                'infants' => 0
            ];
            $file_header +=  $serachAuroraInformation;

            $results = (new FileItineraryServiceByCommunication($file , $services_news[0]['master_services'], [], []))->jsonSerialize();

            $results =  $this->communication_html($file_header, $results['news'], "emails.reservations.services.reservation_solicitude");

            return $this->successResponse(ResponseAlias::HTTP_OK, $results);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function view_protected_rate(Request $request, $id)
    {
        try
        {

            $response = (new FileViewProtectedRateCommand($id, false))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);

        } catch (\Exception $domainException) {

            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }


    }

    public function itinerary_details(Request $request, $file_id)
    {
        // try
        // {

            $lang = $request->input('lang');

            $file = (new FindFileByIdAllQuery($file_id))->handle();
            // return $this->successResponse(ResponseAlias::HTTP_OK, $file);
            $services_info_aurora = (new SearchInA2DetailsServiceQuery($this->getCodes($file)))->handle();

            // return $this->successResponse(ResponseAlias::HTTP_OK, $services_info_aurora);   //dd($services_info_aurora);

            $itineraries = (new FileItineraryDetails($file, $services_info_aurora, $lang))->jsonSerialize();

            return $this->successResponse(ResponseAlias::HTTP_OK, $itineraries);

        // } catch (\Exception $domainException) {

        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        // }
    }

    public function getCodes($file): array {

        $data = [
            'services' => [],
            'hotels' => [],
            'flights' => []
        ];

        foreach($file['itineraries'] as $itinerary){

            if(in_array($itinerary['entity'], ['service', 'service-temporary'])){
                array_push($data['services'], $itinerary['object_code']);
            }

            if($itinerary['entity'] == 'hotel'){
                array_push($data['hotels'], [
                    'itinerary_id' => $itinerary['id'],
                    'hotel_id' => $itinerary['object_id'],
                    'hotel_code' => $itinerary['object_code'],
                    'hotel_rate' => $itinerary['rooms'][0]['rate_plan_id']
                ] );
            }

            if($itinerary['entity'] == 'flight'){

                if($itinerary['city_in_iso'] and !in_array($itinerary['city_in_iso'], $data['flights'])){
                    array_push($data['flights'], $itinerary['city_in_iso']);
                }
                if($itinerary['city_out_iso'] and !in_array($itinerary['city_out_iso'], $data['flights'])){
                    array_push($data['flights'], $itinerary['city_out_iso']);
                }
            }

        }

        return $data ;
    }


    public function skeleton(Request $request, $file_id)
    {
        // try
        // {

            $lang = $request->input('lang', 'es');

            $file = (new FindFileByIdAllQuery($file_id))->handle();

            $services_info_aurora = (new SearchInA2DetailsServiceQuery($this->getCodes($file)))->handle();

            $skeletons = (new FileSkeletonDetails($file, $services_info_aurora, $lang))->jsonSerialize();

            return $this->successResponse(ResponseAlias::HTTP_OK, $skeletons);

        // } catch (\Exception $domainException) {

        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        // }
    }

    public function hotel_list(Request $request, $file_id)
    {
        try
        {


            $file = (new FindFileByIdAllQuery($file_id))->handle();

            $services_info_aurora = (new SearchInA2DetailsServiceQuery($this->getCodes($file)))->handle();

            $itineraries = (new FileHotelListDetails($file, $services_info_aurora))->jsonSerialize();

            return $this->successResponse(ResponseAlias::HTTP_OK, $itineraries);

        } catch (\Exception $domainException) {

            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }
    }

    public function romming_list(Request $request, $file_id)
    {
        try
        {


            $file = (new FindFileByIdAllQuery($file_id))->handle();

            $services_info_aurora = (new SearchInA2DetailsServiceQuery($this->getCodes($file)))->handle();

            $room_list = (new FileRommingListDetails($file, $services_info_aurora))->jsonSerialize();

            return $this->successResponse(ResponseAlias::HTTP_OK, $room_list);

        } catch (\Exception $domainException) {

            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }
    }

    public function romming_list_excel(Request $request, $file_id): object
    {
        try {

            $file = (new FindFileByIdAllQuery($file_id))->handle();

            $services_info_aurora = (new SearchInA2DetailsServiceQuery($this->getCodes($file)))->handle();

            $room_list = (new FileRommingListDetails($file, $services_info_aurora))->jsonSerialize();

            return Excel::download(new RommingListExport($room_list['hotels'], $file['file_number'], $file['description']), 'romming_list.xlsx');
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function generate_master_services($id): JsonResponse
    {
        // try {

            $file = (new FindFileByIdAllQuery($id))->handle();
            $fileItineraries = $file['itineraries'];
            $equivalences = [];
            $file_itinerary_ids = [];
            if (count($fileItineraries) > 0) {

                foreach ($fileItineraries as $itinerary) {

                    if ($itinerary['entity'] === 'service' and count($itinerary['services']) === 0) {

                        $totalAdults = $itinerary['total_adults'];
                        $totalChildren = $itinerary['total_children'];
                        $totalInfants = $itinerary['total_infants'];
                        $totalPassengers = $totalAdults + $totalChildren + $totalInfants;
                        $equivalences[] = [
                            "file_itinerary_id" => $itinerary['id'],
                            "code" => $itinerary['object_code'],
                            "date_in" => Carbon::parse($itinerary['date_in'])->format('d/m/Y'),
                            "total_passengers" => $totalPassengers,
                            "total_children" => $totalChildren,
                            "start_time" => $itinerary['start_time'],
                        ];
                        array_push($file_itinerary_ids, $itinerary['id']);
                    }
                }

                $equivalencesData = [
                    "equivalences" => $equivalences
                ];

                if(count($equivalences)>0){

                    // ProcessFileServicesStellaJob::dispatch($file['id'], $equivalencesData)->onQueue('stella_sequential');
                    dispatch(new ProcessFileServicesStellaJob($file['id'], $equivalencesData, $file['file_number'] ,$file_itinerary_ids))
                            ->onQueue('stella_sequential')
                            ->delay(now()->addSeconds(1));                    
                }

            }
            return $this->successResponse(ResponseAlias::HTTP_OK, $equivalences);


        // }catch (\Exception $e) {
        //     return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // } catch (\DomainException $domainException) {
        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // }
    }

    public function show_changes_statement(Request $request, $file_id): JsonResponse
    {

        // try {

            $file = FileEloquentModel::with(
                ['statement.details'],
                ['debit_notes.details'],
                ['itineraries'=> function ($query) {
                    $query->with([
                        'rooms.units.accommodations.filePassenger',
                        'rooms.units.nights',
                        'rooms.units.fileHotelRoom',
                        'accommodations',
                    ]);
                    $query->where('status', 1);
                }]
            )->findOrFail($file_id);

            if($file->statement)
            {
                $details =  $this->update_statement_detail($file);

            }else{
                $details = $this->create_statement_detail_new($file);
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $details);


        // }catch (\Exception $e) {
        //     return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // } catch (\DomainException $domainException) {
        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // }
    }

    public function test_aurora(Request $request){

        $file = $request->input('file');

        $files = FileEloquentModel::with(
            ['statement.details'],
            ['debit_notes.details'],
            ['itineraries'=> function ($query) {
                $query->with([
                    'rooms.units.accommodations.filePassenger',
                    'rooms.units.nights',
                    'rooms.units.fileHotelRoom',
                    'accommodations',
                ]);
            }]
        )->where('id', $file)->get();

        $details = null;
        foreach($files as $file)
        {
            $details =  $this->update_statement_detail($file);

            // ProcessFileCreateStatementJob::dispatchSync($file->id);
        }

        return $this->successResponse(ResponseAlias::HTTP_OK, $details);


        // $files = FileEloquentModel::with(
        //     ['statement.details'],
        //     ['itineraries']
        // )->where("status", 'OK')->get();

        // foreach($files as $file){
        //     $time = strtotime($file->itineraries->min('date_in'));
        //     $deadline = date('Y-m-d', strtotime('-2 days', $time));
        //     $file->statement->deadline = $deadline;
        //     $file->statement->save();
        // }

        // $aurora = new AuroraExternalApiService();
        // $cancellationPoliciesServices = $aurora->searchCancellationPoliciesServicesSupplier([
        //     'supplier' => "LIMRAP",//$newComponent->supplier->code_request_book,
        //     'persons' => 7 //$newComponent->total_adults + $component->total_children
        // ]);
        // dd($cancellationPoliciesServices);
    }


}
