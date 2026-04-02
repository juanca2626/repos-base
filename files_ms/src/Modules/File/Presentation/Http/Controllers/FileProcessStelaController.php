<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse; 
use Illuminate\Http\Request;
use Src\Modules\File\Application\UseCases\Commands\CreateFileServicesStelaCommand; 
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileProcessStelaController extends Controller
{
    use ApiResponse;

    public function process_import_file_stela(Request $request, int $file_id)
    {
        try { 
            
            (new CreateFileServicesStelaCommand($file_id, $request->input()))->execute();
            
            return $this->successResponse(ResponseAlias::HTTP_OK, true);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } 
        
    }
  

    
    

}
