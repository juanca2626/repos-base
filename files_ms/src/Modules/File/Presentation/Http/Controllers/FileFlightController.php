<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Modules\File\Infrastructure\ExternalServices\AviationStack\AviationStack;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileFlightController extends Controller
{
    use ApiResponse;

    public function __construct(public readonly AviationStack $aviationStack)
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function informationFlight(Request $request): JsonResponse
    {
        $flightNumber = $request->post('flight_number');

        $flight = $this->aviationStack->getFlightsFilter(['flight_iata' => $flightNumber]);

        $flight = collect($flight['data'] ?? [])->first();

        $response = [
            'status' => $flight['flight_status'] ?? null,
            'airline' => isset($flight['airline']) ? [
                'name' => $flight['airline']['name'],
                'iata' => $flight['airline']['iata'],
            ] : [],
            'flight' => isset($flight['flight']) ? [
                'number' => $flight['flight']['number'],
                'iata' => $flight['flight']['iata'],
            ] : [],
            'date' => [
                'departure_estimated' => $flight['departure']['estimated'] ?? null,
                'arrival_estimated' => $flight['arrival']['estimated'] ?? null,
            ],
        ];

        return $this->successResponse(ResponseAlias::HTTP_OK, $response);
    }
}
