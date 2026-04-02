<?php

namespace Src\Modules\File\Infrastructure\ExternalServices\AviationStack;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use GuzzleHttp\Client;

class AviationStack
{
    /**
     * @var string|Repository|Application|\Illuminate\Foundation\Application|mixed
     */
    protected string $baseUri;

    /**
     * @var string|Repository|Application|\Illuminate\Foundation\Application|mixed
     */
    private string $accessToken;

    public function __construct()
    {
        $this->baseUri = config('services.aviation_stack.endpoint');
        $this->accessToken = config('services.aviation_stack.access_key');
    }


    /**
     * @throws GuzzleException
     */
    public function getFlightsFilter(array $filters)
    {
        $params = [
            'access_key' => $this->accessToken,
            'limit' => 1,
        ];

        $params = array_merge($params, $filters);

        return $this->makeRequest("{$this->baseUri}/v1/flights", 'GET', $params);
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $params
     * @param array $headers
     * @return mixed|null
     * @throws GuzzleException
     */
    private function makeRequest(string $url, string $method = 'GET', array $params = [], array $headers = []): mixed
    {
        $client = new Client();

        $options = [
            'headers' => $headers,
        ];

        if ($method == 'GET' && !empty($params)) {
            $options['query'] = $params;
        }

        if (in_array($method, ['POST', 'PUT']) && !empty($params)) {
            $options['form_params'] = $params;
        }

        try {
            $response = $client->request($method, $url, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            logger("Error {$method} endpoint aviationstack: " . $e->getMessage());
            return [];
        }
    }
}
