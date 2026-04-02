<?php

namespace Src\Modules\Catalogs\Infrastructure\Readers;

use Src\Modules\Catalogs\Domain\Readers\CityReader;
use Src\Shared\Infrastructure\Integrations\ApiGateway\Client\ApiGatewayHttpClient;

class ApiGwCityReaderHttp implements CityReader
{
    public function __construct(
        private ApiGatewayHttpClient $client
    ) {}

    public function all(array $city_isos): array
    {
        $response = $this->client->post('api/v1/countries/cities/isos', [
            'city_isos' => $city_isos
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Unable to fetch cities from ApiGateway');
        }

        return $response->json();
    }
}