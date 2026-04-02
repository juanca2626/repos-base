<?php

namespace App\Http\Stella\Traits;

use GuzzleHttp\Client;

trait ConsumesExternalStellaApi
{
    /**
     * Send a request to any service
     * @param $method  => [GET,POST,PUT,DELETE]
     * @param $requestUrl  => ENDPOINT
     * @param  array  $queryParams
     * @param  array  $formParams
     * @param  array  $headers
     * @param  bool  $isJsonRequest
     * @param  bool  $responseAll  => SI ES TRUE RETORNA EL RESPONSE COMPLETO, SI ES FALSE SOLO DEVUELVE LO QUE CONTENGA DATA
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function makeRequest(
        $method,
        $requestUrl,
        $queryParams = [],
        $formParams = [],
        $headers = [],
        $isJsonRequest = false,
        $responseAll = false
    ) {
        $client = new Client([
            "verify"   => false,
            'base_uri' => $this->baseUri
        ]);

        $response = $client->request($method, $requestUrl, [
            (($isJsonRequest) ? 'json' : 'form_params') => $formParams,
            'headers'                                   => $headers,
            'query'                                     => $queryParams,
        ]);

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
