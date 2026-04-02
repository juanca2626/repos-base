<?php


namespace App\Http\Aurora\Traits;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

trait ConsumesExternalServices
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
        if(!empty($baseUri))
        {
            $client = new Client([
                "verify" => false,
                'base_uri' => $baseUri,
            ]);
        }
        else
        {
            $client = new Client([
                "verify" => false,
                'base_uri' => $this->baseUri,
            ]);
        }

        if(empty($this->bearerToken))
        {       

            if ($this->serviceName === "aurora" && method_exists($this, 'resolveAuthorization')) {
                $this->resolveAuthorization($queryParams, $formParams, $headers, $this->baseUri);     
            }

            if ($this->serviceName === "stella" && method_exists($this, 'resolveAuthorization')) {
                $this->resolveAuthorization($queryParams, $formParams, $headers, $this->baseUriCognito);                
            }

        }
        else
        {  
            $headers['Authorization'] = $this->bearerToken;
        }
       
        $response = $client->request($method, $requestUrl, [
            $isJsonRequest ? 'json' : 'form_params' => $formParams,
            'headers' => $headers,
            'query' => count($queryParams)>0 ? $queryParams : null
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
