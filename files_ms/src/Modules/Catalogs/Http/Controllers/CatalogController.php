<?php

namespace Src\Modules\Catalogs\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; 

use Src\Shared\Logging\Traits\LogsDomainEvents;
use Src\Shared\Presentation\Http\Traits\ApiResponse; 
use Src\Modules\Catalogs\Application\UseCases\GetExecutives;
use Src\Modules\Catalogs\Application\UseCases\GetCities;
use Src\Shared\Infrastructure\Auth\UserContext;

class CatalogController extends Controller
{
    use ApiResponse,LogsDomainEvents;

    public function executives(GetExecutives $useCase): JsonResponse
    {       
        $data = $useCase->execute(); 
        return $this->successResponse(200, $data);         
    }

    public function cities(Request $request,GetCities $useCase): JsonResponse
    {             
        $data = $useCase->execute($request->input('city_isos'));        
        return $this->successResponse(200, $data);
        
        //$user = UserContext::user();  obtenemos los datos del usuario conectado 
        
    }  
      
}