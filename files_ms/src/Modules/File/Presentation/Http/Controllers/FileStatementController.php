<?php

namespace Src\Modules\File\Presentation\Http\Controllers;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Modules\File\Application\UseCases\Commands\CreateFileStatementCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateBlockedStatementCommand;
use Src\Modules\File\Application\UseCases\Commands\UpdateFileStatementNewCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileStatementDetailsQuery;
use Src\Modules\File\Application\UseCases\Queries\FindFileStatementReport;
use Src\Modules\File\Application\UseCases\Queries\FindFileStatementReportQuery;
use Src\Modules\File\Application\UseCases\Queries\SearchFileStatementBlockedQuery;
use Src\Modules\File\Presentation\Http\Resources\FileStatementDetailsResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Illuminate\Support\Facades\App;
use File;
use Src\Modules\File\Application\UseCases\Commands\CreateFileStatementNewCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileBasicInfoQuery;
use Src\Modules\File\Application\UseCases\Queries\FindFileStatementForStellaQuery;
use Src\Modules\File\Infrastructure\Jobs\ProcessFileCreateStatementJob;

class FileStatementController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $fileId)
    {


    }

    public function blocked_list(Request $request): JsonResponse
    {
        try {

            $statements = (new SearchFileStatementBlockedQuery())->handle();
            if(!$statements){
                throw new \DomainException('no records');
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $statements);

            return $this->successResponse(ResponseAlias::HTTP_OK, []);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function des_blocked(Request $request): JsonResponse
    {
        try {
            (new UpdateBlockedStatementCommand($request->input()))->execute();
            return $this->successResponse(ResponseAlias::HTTP_OK, true);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function report(Request $request, $file_id): JsonResponse
    {
        try {

            // (new CreateFileStatementCommand($file_id))->execute();
            $statements = (new FindFileStatementReportQuery($file_id))->handle();
            if(!$statements){
                throw new \DomainException('no records');
            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $statements);
        }catch (\Exception $e) {
            // $date = Carbon::now()->format('Y-m-d');
            // $content = "";
            // $logPath = storage_path("logs/laravel-{$date}.log");

            // if (!File::exists($logPath)) {
            //     $content = 'Archivo de log no encontrado';
            // }else{
            //     $content = File::get($logPath);
            // }

            // return $this->errorResponse([
            //     'error' =>$e->getMessage(),
            //     'menssage' => $content
            // ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);

            return $this->errorResponse([
                'error' =>$e->getMessage(),
                'menssage' => $e->getMessage()
            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);

            // $content = file_get_contents(storage_path("logs/laravel-{$date}.log"));
        } catch (\DomainException $domainException) {

            // $date = Carbon::now()->format('Y-m-d');

            // $content = "";
            // $logPath = storage_path("logs/laravel-{$date}.log");

            // if (!File::exists($logPath)) {
            //     $content = 'Archivo de log no encontrado';
            // }else{
            //     $content = File::get($logPath);
            // }

            // return $this->errorResponse([
            //     'error' => $domainException->getMessage(),
            //     'menssage' => $content
            // ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);

            return $this->errorResponse([
                'error' => $domainException->getMessage(),
                'menssage' => $domainException->getMessage()
            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);

            $content = file_get_contents(storage_path("logs/laravel-{$date}.log"));
        }
    }

    public function store(Request $request, $file_id): JsonResponse
    {

        // try {

            $response = [];
            $file = (new FindFileBasicInfoQuery($file_id))->handle();
            if($file)
            {
                $client_have_credit = $file['client_have_credit'] ? $file['client_have_credit'] : 0;
                $client_credit_line = $file['client_credit_line'] ? $file['client_credit_line'] : 0;
                ProcessFileCreateStatementJob::dispatchSync($file['id'], $client_have_credit, $client_credit_line);

                // $response = (new FindFileStatementForStellaQuery($file_id))->handle(); // cuando es nuevo no va a generar informacion en Stela, en una priemera presentacion

            }

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);

        // }catch (\Exception $e) {
        //     return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // } catch (\DomainException $domainException) {
        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // }

    }

    public function store_bk(Request $request, $file_id): JsonResponse
    {
        // try {

            $request->merge(["user_id" => $request->input('user_id')]);

            (new CreateFileStatementNewCommand($file_id, $request->input()))->execute();

            $response = (new FindFileStatementForStellaQuery($file_id))->handle();

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);

        // }catch (\Exception $e) {
        //     return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // } catch (\DomainException $domainException) {
        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // }
    }

    public function update(Request $request, $file_id): JsonResponse
    {
        // try {

 

            $request->merge(["user_id" => $request->input('user_id')]);

            (new UpdateFileStatementNewCommand($file_id, $request->input()))->execute();

            $response = (new FindFileStatementForStellaQuery($file_id))->handle();

            return $this->successResponse(ResponseAlias::HTTP_OK, $response);

        // }catch (\Exception $e) {
        //     return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // } catch (\DomainException $domainException) {
        //     return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        // }
    }

    public function show(Request $request, $file_id): JsonResponse | FileStatementDetailsResource
    {
        try {

            $statemet = (new FindFileStatementDetailsQuery($file_id))->handle();

            // if(!$statemet['statement']){
            //     throw new \DomainException('No statement was generated for this file');
            // }

            // if(isset($statemet['statement']['id']))
            // {
            return new FileStatementDetailsResource($statemet);
            // }

            return $this->successResponse(ResponseAlias::HTTP_OK, []);

        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function concepts(Request $request): JsonResponse
    {
        try {

            $lang = App::currentLocale();

            $dataLang = File::get(resource_path() . "/lang/" . $lang . "/statement.json");
            $trad = json_decode($dataLang, true);
            return $this->successResponse(ResponseAlias::HTTP_OK, $trad['concepts']);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

}
