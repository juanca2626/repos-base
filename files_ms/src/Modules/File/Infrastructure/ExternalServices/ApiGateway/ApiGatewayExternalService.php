<?php

namespace Src\Modules\File\Infrastructure\ExternalServices\ApiGateway;

use App\Http\Traits\AuthorizesServiceRequests;
use App\Http\Traits\ConsumesExternalServices;
use App\Http\Traits\InteractsWithServiceResponses;
const TYPE_JSON = "application/json";
use GuzzleHttp\Client;

class ApiGatewayExternalService {
    use ConsumesExternalServices, AuthorizesServiceRequests, InteractsWithServiceResponses;
    /**
     * The url from which send the requests
     * @var string
     */
    protected mixed $baseUri;
    protected string $serviceName;

    public function __construct($bearer = '')
    {
        $this->serviceName = 'api_gateway';
        $this->baseUri = config('services.api_gateway.endpoint');
    }

    public function getCitiesIso(array $cities){
        $params = [
            'city_isos' => $cities
        ];

        $headers = [
            'Content-Type' => "application/json",
            'Accept' => "application/json",
        ];

        return $this->makeRequest(
            'POST',
            "api/v1/countries/cities/isos",
            [],
            $params,
            $headers,
            true,
            true,
            $this->baseUri
        );
    }
}
