<?php


namespace App\Http\Traits;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

trait ConsumesAwsNotificacionLogs
{
    /**
     * Send a request to any service
     * @param string $method
     * @param string $requestUrl
     * @param array $queryParams
     * @param array $formParams
     * @param array $headers
     * @param bool $isJsonRequest
     * @param bool $responseAll
     * @return string
     * @throws GuzzleException
     * @throws \Exception
     */
    public function makeRequest(
        string $method,
        string $requestUrl,
        array $queryParams = [],
        array $formParams = [],
        array $headers = [],
        bool $isJsonRequest = false,
        bool $responseAll = false, 
        string $baseUri = null
    ) {
        $client = new Client([
            "verify" => false,
            'base_uri' => $this->baseUri,
        ]);
// dd($this->baseUri, $requestUrl);
        $response = $client->request($method, $requestUrl);

        $response = $response->getBody()->getContents();
 
        if (method_exists($this, 'decodeResponse')) {
            $response = $this->decodeResponse($response, $responseAll);
        }

        if (method_exists($this, 'checkIfErrorResponse')) {
            $this->checkIfErrorResponse($response);
        }

        return $response;
    }
}
