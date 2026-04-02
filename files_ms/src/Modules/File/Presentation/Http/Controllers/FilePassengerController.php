<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Modules\File\Application\Exports\PassengerExport;
use Src\Modules\File\Application\UseCases\Commands\ResetFilePassengerModifyPaxCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateAccommodationFileItineraryCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateAccommodationFilePassengerCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFilePassegerChangesCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFilePassengerCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFilePassengerIdQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchAccommodationFileItineraryByIdQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFilePassengerQuery;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TypeRoomDescription;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\AccommodationNew;
use Src\Modules\File\Presentation\Http\Resources\FilePassengerResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\File\Application\Exports\PassengerAmadeusExport;
use Src\Modules\File\Application\Exports\PassengerPeruRailExport;
use Src\Modules\File\Application\UseCases\Queries\SearchFileMaterTablesQuery;
use Src\Modules\File\Domain\ValueObjects\File\FileValidateStatus;
use Src\Modules\File\Presentation\Http\Traits\RemoveItemInArray;
use Illuminate\Support\Facades\Validator;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery;

class FilePassengerController extends Controller
{
    use ApiResponse, RemoveItemInArray;

    public function index(Request $request, int $fileId): JsonResponse
    {

        try {
            $file_passengers = (new SearchFilePassengerQuery($fileId))->handle();
            $file_passengers = FilePassengerResource::collection($file_passengers);
            return $this->successResponse(ResponseAlias::HTTP_OK, $file_passengers);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(Request $request, int $fileId): JsonResponse
    {

        try{

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function update_all(Request $request, int $fileId): JsonResponse
    {

        try {

            $params = $request->input();
            $flag_lambda = $params['flag_lambda'];
            $params = $this->removeItemInArray('flag_lambda', $params);

            (new FileValidateStatus(
                ((new FindFileByIdAllQuery($fileId))->handle())['status']
            ));

            $file_passengers = (new SearchFilePassengerQuery($fileId))->handle();

            $this->validate_passengers($file_passengers, $params);

            $statusChanges  = $this->passenger_changes($file_passengers, $params);

            (new UpdateFilePassengerCommand($fileId,$params))->execute();

            (new UpdateFilePassegerChangesCommand($fileId,$statusChanges))->execute();

            (new ResetFilePassengerModifyPaxCommand($fileId))->execute();

            $stela = [];
            foreach($params as $param){

                $file_passenger = (new FindFilePassengerIdQuery($param['id']))->handle();

                $phone_code = isset($param['phone_code']) ? $param['phone_code']: '';
                $phone = isset($param['phone']) ? $param['phone']: '';
                $telefono = $phone_code.$phone;

                array_push($stela, [
                    'nrosec' => $file_passenger['sequence_number'],
                    'secuencia' => $file_passenger['sequence_number'],
                    'nrofile' => $file_passenger['file']['file_number'],
                    'nombre' => $param['surnames'] . ', ' . $param['name'],
                    'tipo' => $param['type'],
                    'sexo' => $param['genre'],
                    'fecha' => $param['date_birth'],
                    'ciudad' => $file_passenger['city_iso'],
                    'pais' => $param['country_iso'],
                    'tipodoc' => $param['doctype_iso'],
                    'nrodoc' => $param['document_number'] ? $param['document_number'] : NULL,
                    'correo_electronico' => $param['email'],
                    'telefono' =>  $telefono,
                    'resmed' => $param['medical_restrictions'],
                    'resali' => $param['dietary_restrictions'],
                    // 'observ' => $param['notes'],
                    'estado' => "OK",
                    'tiphab' => (int) $param['room_type'],
                ]);
            }

            $params = ['datapax' => $stela];

            return $this->successResponse(ResponseAlias::HTTP_OK, ['stela' => $params]);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function validate_passengers($passengers, $passenger_updates): void
    {
        $passengers = collect($passengers);
        $passenger_updates = collect($passenger_updates);

        if($passengers->count() !== $passenger_updates->count()){
            throw new \DomainException("The number of passengers has changed, use the modify quantity option");
        }

    }

    public function passenger_changes($passengers, $passenger_updates): bool
    {
        $passenger_updates = collect($passenger_updates);

        foreach($passengers as $passenger){

            $passenger_change = $passenger_updates->firstWhere('id', $passenger['id']);
            if($passenger_change){
                if($passenger['suggested_room_type'] !== $passenger_change['room_type']){
                    return true;
                }
            }
        }

        return false;
    }

    public function accommodations(Request $request, int $fileId): JsonResponse
    {
        (new FileValidateStatus(
            ((new FindFileByIdAllQuery($fileId))->handle())['status']
        ));

        try{
            // Lista de pasajeros actual del file
            $file_passengers = (new SearchFilePassengerQuery($fileId))->handle();
            // extructura base del facil de un acomodo en un hotel
            $accomodation_passenger = (new SearchAccommodationFileItineraryByIdQuery($fileId))->handle();
            // nuevo extructura de acomodo propuesto
            $accomodation_new = (new AccommodationNew($request->input(), $accomodation_passenger, $file_passengers))->jsonSerialize();
            // actualizamos el nuevo acomodo en todos los acommodos de los hoteles
            (new UpdateAccommodationFileItineraryCommand($fileId,$accomodation_new))->execute();
            // actualizamos el nuevo acomodo en la tabla file_passengers para cada pasajero
            (new UpdateAccommodationFilePassengerCommand($fileId,$request->input()))->execute();
            // actualizamos el campo passenger_change de files
            (new UpdateFilePassegerChangesCommand($fileId,false))->execute();
            // actualizamos la tabla de acomodo general de los pasajeros
            (new ResetFilePassengerModifyPaxCommand($fileId))->execute();

            return $this->successResponse(ResponseAlias::HTTP_OK, ['accomodation'=> $accomodation_passenger, 'accomodation_new' => $accomodation_new]);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

    }



   public function store(Request $request, int $fileId): JsonResponse
    {
        // Validar que el archivo esté presente y sea un archivo de Excel
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Invalid file format.', ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {

            //Excel::import(new PassengerImport($fileId), $request->file('file'));

            return $this->successResponse(ResponseAlias::HTTP_OK, ['message' => 'File imported successfully.']);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function download(Request $request, $fileId): object
    {
        try {

            $file_passengers = (new SearchFilePassengerQuery($fileId))->handle();
            $master_tables = (new SearchFileMaterTablesQuery($request->input()))->handle();
            return Excel::download(new PassengerExport($file_passengers, $master_tables['countries'], $master_tables['dataDoctypes']), 'passengers.xlsx');
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function download_amadeus(Request $request, $fileId): object
    {
        try {
            $file_passengers = (new SearchFilePassengerQuery($fileId))->handle();
            return Excel::download(new PassengerAmadeusExport($file_passengers), 'passengers_amadeus.xlsx');
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function download_perurail(Request $request, $fileId): object
    {
        try {
            $file_passengers = (new SearchFilePassengerQuery($fileId))->handle();
            return Excel::download(new PassengerPeruRailExport($file_passengers), 'passengers_perurail.xlsx');
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
