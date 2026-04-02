<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse; 
use Illuminate\Http\Request;
use Src\Modules\File\Application\UseCases\Commands\CreateFileCategoryCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery; 
use Src\Modules\File\Application\UseCases\Queries\SearchFileCategoryQuery;
use Src\Modules\File\Domain\ValueObjects\File\FileValidateStatus;
use Src\Modules\File\Presentation\Http\Resources\FileCategoriesResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileCategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)  
    {
        try { 
            $file_categories = (new SearchFileCategoryQuery($request->input()))->handle();
            $file_categories = FileCategoriesResource::collection($file_categories);
            return $this->successResponse(ResponseAlias::HTTP_OK, $file_categories);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
 
    public function store(Request $request, $id){
        
        (new FileValidateStatus(
            ((new FindFileByIdAllQuery($id))->handle())['status']                
        )); 
        
        $result = (new CreateFileCategoryCommand($id, $request->input()))->execute();
        return $this->successResponse(ResponseAlias::HTTP_OK, $result);
    }
}
