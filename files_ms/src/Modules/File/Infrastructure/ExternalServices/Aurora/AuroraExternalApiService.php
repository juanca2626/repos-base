<?php


namespace Src\Modules\File\Infrastructure\ExternalServices\Aurora;

use App\Http\Traits\AuthorizesServiceRequests;
use App\Http\Traits\ConsumesExternalServices;
use App\Http\Traits\InteractsWithServiceResponses;
use Illuminate\Http\Request;

const TYPE_JSON = "application/json";

class AuroraExternalApiService
{
    use ConsumesExternalServices, AuthorizesServiceRequests, InteractsWithServiceResponses;

    /**
     * The url from which send the requests
     * @var string
     */
    protected mixed $baseUri;
    protected string $serviceName;
    protected string $bearerToken = '';

    public function __construct()
    {
        $this->serviceName = 'aurora';
        $this->baseUri = config('services.aurora.endpoint'); // auroraback
    }

    public function setAuthorization($bearer)
    {

        if(!empty($bearer))
        {
            $this->bearerToken = $bearer;
        }

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
        return $this->makeRequest('GET', "api/files/" . $file,
            [], [], ['Content-Type' => TYPE_JSON], true);
    }

    public function importFiles($formParams)
    {
        return $this->makeRequest('GET', "api/files",
            $formParams, [], ['Content-Type' => TYPE_JSON], true);
    }

    public function searchExecutives($formParams)
    {
        return $this->makeRequest('GET', "api/a3/executives",
            $formParams, [], ['Content-Type' => TYPE_JSON], true);
    }

    public function searchClients($formParams)
    {
        return $this->makeRequest('GET', "api/a3/clients",
            $formParams, [], ['Content-Type' => TYPE_JSON], true);
    }

    public function searchByCommunication($formParams)
    {
        return $this->makeRequest('GET', "api/a3/info-by-communication",
            $formParams, [], ['Content-Type' => TYPE_JSON], true);
    }

    public function searchChannelCodeHyperguest($formParams)
    {
        return $this->makeRequest('POST', "api/a3/channel-code-hyperguest",
        [], $formParams, ['Content-Type' => TYPE_JSON], true, false);
    }

    public function searchByCodeClients($formParams)
    {
        return $this->makeRequest('GET', "api/a3/info-client-by-code",
            $formParams, [], ['Content-Type' => TYPE_JSON], true);
    }

    public function searchServicesDetails($formParams)
    {
        return $this->makeRequest('POST', "api/a3/services-details",
            [], $formParams, ['Content-Type' => TYPE_JSON], true, false);
    }

    public function searchServicesDetailByStela($formParams)
    {
        return $this->makeRequest('POST', "api/a3/services-details-by-stela",
            [], $formParams, ['Content-Type' => TYPE_JSON], true, false);
    }

    public function searchTokenHotels($formParams)
    {
        return $this->makeRequest('POST', "services/search-token-hotels",
            [], $formParams, ['Content-Type' => TYPE_JSON], true);
    }

    public function searchBoss($formParams)
    {
        return $this->makeRequest('GET', "api/a3/boss_executives",
            $formParams, [], ['Content-Type' => TYPE_JSON], true);
    }

    public function searchQuoteDetail($reservation_id, $lang = 'es')
    {
        return $this->makeRequest('GET', "api/reservations/" . $reservation_id . "/itinerary",
            ['lang' => $lang], [], ['Content-Type' => TYPE_JSON], true);
    }

    public function searchMarkupClient($client_id)
    {
        return $this->makeRequest('GET', "api/client/" . $client_id . "/markup",
            [], [], ['Content-Type' => TYPE_JSON], true);
    }

    public function getReservationsServicesByReservationId($reservation_id, $responseAll = false)
    {
        return $this->makeRequest('GET', "api/reservations/{$reservation_id}/services",
            [], [], ['Content-Type' => TYPE_JSON], true, $responseAll);
    }

    public function searchPassengers($file_number)
    {
        return $this->makeRequest('POST', "api/search_passengers",
            [], ['type' => 'file', 'file' => $file_number], ['Content-Type' => TYPE_JSON], true, false);
    }

    public function searchServicesByCodes($codes)
    {
        return $this->makeRequest('POST', "api/search_services_by_codes",
            [], ['codes' => $codes], ['Content-Type' => TYPE_JSON], true, false);
    }

    public function validateOpenQuote()
    {
        return $this->makeRequest('GET', "api/quote/existByUserStatus/2",
            [], [], ['Content-Type' => TYPE_JSON], true, false, config('services.quotes_ms.endpoint'));
    }

    public function forcefullyDestroy($quote_id)
    {
        return $this->makeRequest('DELETE', "api/quote/" . $quote_id . "/forcefullyDestroy",
            [], [], ['Content-Type' => TYPE_JSON], true, false, config('services.quotes_ms.endpoint'));
    }

    public function sendQuote($params)
    {
        return $this->makeRequest('POST', "api/quotes/reverse-engineering",
            [], $params, ['Content-Type' => TYPE_JSON], true, false, config('services.quotes_ms.endpoint'));
    }

    public function searchOpenQuote()
    {
        return $this->makeRequest('GET', "api/quote/byUserStatus/2",
            ['lang' => 'en', 'client_id' => '15766'], [], ['Content-Type' => TYPE_JSON], true, false, config('services.quotes_ms.endpoint'));
    }

    public function searchCancellationPoliciesServicesSupplier($formParams)
    {
        return $this->makeRequest('GET', "api/service/cancellations_policies/supplier",
            $formParams, [], ['Content-Type' => TYPE_JSON], true);
    }

    public function searchMasterTable($formParams)
    {
        return $this->makeRequest('GET', "api/info-master-table",
            $formParams, [], ['Content-Type' => TYPE_JSON], true);
    }

    public function getServiceTypes($lang = 'es', $bearerToken)
    {
        $this->setAuthorization($bearerToken);
        return $this->makeRequest(
            'GET',
            "api/service/form",
            ['lang' => $lang],
            [],
            ['Content-Type' => TYPE_JSON, 'Authorization' => "Bearer {$this->bearerToken}"],
            true
        );
    }

    public function quotesMarkup(array $files = []){
        // return $files;
        return $this->makeRequest('POST','api/quotes/markup',[],[
            'files' => $files
        ],[
            'Content-Type' => TYPE_JSON,
            'Authorization' => "Bearer {$this->bearerToken}"
        ], true, false);
    }
}
