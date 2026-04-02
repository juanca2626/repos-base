<?php
namespace Src\Modules\File\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileReservationProviderInformationController extends Controller{
    use ApiResponse;

    public function index(Request $request) : JsonResponse{
        try {
            $executive_code     = $request->get('executive_code');
            $hotel_id           = $request->get('hotel_id');
            $client_id          = $request->get('client_id');

            $aurora = new AuroraExternalApiService();
            $response = $aurora->searchByCommunication([
                'executive_code' => $executive_code,
                'hotel_id' => $hotel_id,
                'client_id' => $client_id
            ]);
            return $this->successResponse(ResponseAlias::HTTP_OK, $response);

        } catch (\DomainException $domainException) {

            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }
    }
}
