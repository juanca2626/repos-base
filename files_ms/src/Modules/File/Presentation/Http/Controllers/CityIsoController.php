<?php

namespace Src\Modules\File\Presentation\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;

class CityIsoController extends Controller{
    use ApiResponse;

    public function validateInternationalFlight(Request $request): JsonResponse{
        try{
            $api_gate_way   = new ApiGatewayExternal();
            $response       = $api_gate_way->getCitiesIso($request->get('city_isos'));
            $response       = json_decode(json_encode($response), true);
            // return response()->json($response);
            $paises         = array_count_values(array_column($response,'codpais'));
            $peCount        = $paises["PE"] ?? 0;
            $valid          = true;

            if($peCount > 1){
                $valid = false;
            }
            // return response()->json($valid);
            return $this->successResponse(ResponseAlias::HTTP_OK, ['validation'=>$valid]);
        }catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
