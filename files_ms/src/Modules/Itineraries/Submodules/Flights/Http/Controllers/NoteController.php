<?php

namespace Src\Modules\Notes\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Src\Shared\Logging\Traits\LogsDomainEvents;
use Src\Shared\Presentation\Http\Traits\ApiResponse;  
use Illuminate\Http\Request;
use Src\Modules\Notes\Application\UseCases\GetNoteClassifications;

class NoteController extends Controller
{
    use ApiResponse,LogsDomainEvents;

    public function classification(GetNoteClassifications $useCase): JsonResponse
    {   
        $data = $useCase->execute();        
        return $this->successResponse(200, $data);         
    }     
}