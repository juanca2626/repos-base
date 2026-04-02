<?php


namespace Src\Shared\Infrastructure\ExternalServices\Aurora;

use App\Http\Traits\AuthorizesServiceRequests;
use App\Http\Traits\ConsumesExternalServices;
use App\Http\Traits\InteractsWithServiceResponses;

class AuroraExternalApiService
{
    use ConsumesExternalServices, AuthorizesServiceRequests, InteractsWithServiceResponses;

    /**
     * The url from which send the requests
     * @var string
     */
    protected mixed $baseUri;
    protected string $serviceName;

    public function __construct()
    {
        $this->serviceName = 'aurora';
        $this->baseUri = config('services.aurora.endpoint'); // auroraback
    }

    /**
     * Trae los estados por un pais en especifico
     * @param $lang 'es|en|pt'
     * @param $country_id 89 = Perú
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function importFile($file)
    {
        return $this->makeRequest('GET', "api/files/" . $file, [], [], ['Content-Type' => 'application/json'], true);
    }

    public function importFiles($formParams)
    {
        return $this->makeRequest('GET', "api/files", $formParams, [], ['Content-Type' => 'application/json'], true);
    }

    public function searchExecutives($formParams)
    {
        return $this->makeRequest('GET', "api/a3/executives", $formParams, [],
            ['Content-Type' => 'application/json'], true);
    }

    public function searchClients($formParams)
    {
        return $this->makeRequest('GET', "api/a3/clients", $formParams, [], ['Content-Type' => 'application/json'], true);
    }

    public function searchBoss($formParams)
    {
        return $this->makeRequest('GET', "api/a3/boss_executives", $formParams, [], ['Content-Type' => 'application/json'], true);
    }

    public function searchQuoteDetail($file, $lang = 'es')
    {
        return $this->makeRequest('GET', "api/reservations/" . $file . "/itinerary", ['lang' => $lang], [], ['Content-Type' => 'application/json'], true);
    }

    public function searchMarkupClient($client_id)
    {
        return $this->makeRequest('GET', "api/client/" . $client_id . "/markup", [], [], ['Content-Type' => 'application/json'], true);
    }

    public function getReservationsServicesByReservationId($reservation_id, $responseAll = false)
    {
        return $this->makeRequest('GET', "api/reservations/{$reservation_id}/services", [], [], ['Content-Type' => 'application/json'], true, $responseAll);
    }

    public function searchPassengers($file_number)
    {
        return $this->makeRequest('POST', "api/search_passengers", [], [
            'type' => 'file', 'file' => $file_number
        ], ['Content-Type' => 'application/json'], true, false);
    }

    public function searchServicesByCodes($codes)
    {
        return $this->makeRequest('POST', "api/search_services_by_codes", [], [
            'codes' => $codes
        ], ['Content-Type' => 'application/json'], true, false);
    }

}
