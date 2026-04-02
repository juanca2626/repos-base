<?php

namespace App\Http\Aurora;

use App\Services\Auth\CognitoAuthService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ClientException;
use App\Http\Aurora\Traits\InteractsWithServiceResponses;
const TYPE_JSON = "application/json";

class ApiGatewayExternal
{
    use InteractsWithServiceResponses;
    protected $baseUriApiGateway;
    protected $cognitoAuthService;
    protected $defaultHeaders;
    protected $baseUriStella;
    protected $baseUriCognito;

    public function __construct()
    {
        $this->cognitoAuthService = app()->make(CognitoAuthService::class);
        $this->baseUriApiGateway = config('services.api_gateway.endpoint');
        $this->baseUriStella = config('services.stella.endpoint');
        $this->baseUriCognito = config('services.cognito.endpoint');
        $this->defaultHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Realiza una petición HTTP con autenticación Cognito
     *
     * @param string $method
     * @param string $endpoint
     * @param array $queryParams
     * @param array $bodyParams
     * @param array $additionalHeaders
     * @param bool $isJsonRequest
     * @return mixed
     * @throws GuzzleException
     */
    public function makeAuthenticatedRequest(
        string $method,
        string $endpoint,
        array $queryParams = [],
        array $bodyParams = [],
        array $additionalHeaders = [],
        bool $isJsonRequest = true,
        bool $responseAll = false,
        string $baseUri = null,
    ) {
        // Obtener el token de Cognito
        $authHeaders = $this->cognitoAuthService->getAuthorizationHeaders();
 
        // Combinar headers
        $headers = array_merge(
            $this->defaultHeaders,
            $additionalHeaders,
            $authHeaders
        );

        // Configurar cliente HTTP
        $client = new Client([
            'base_uri' => $baseUri,
            'verify' => false, // Solo para desarrollo, en producción debería ser true
        ]);

        // Opciones de la petición
        $options = [
            'headers' => $headers,
            'query' => $queryParams,
        ];

        // Añadir body según el tipo de petición
        if ($isJsonRequest && !empty($bodyParams)) {
            $options['json'] = $bodyParams;
        } elseif (!empty($bodyParams)) {
            $options['form_params'] = $bodyParams;
        }
 
        // Realizar la petición
        $response = $client->request($method, $endpoint, $options);

        // Procesar la respuesta
        $response = $response->getBody()->getContents();

        if (method_exists($this, 'decodeResponse')) {
            $response = $this->decodeResponse($response, $responseAll);
        }

        if (method_exists($this, 'checkIfErrorResponse')) {
            $this->checkIfErrorResponse($response);
        }

        return $response;
    }

    /**
     * Ejemplo de método para obtener ciudades por ISO
     *
     * @param array $cities
     * @return mixed
     * @throws GuzzleException
     */
    public function getCitiesIso(array $cities)
    {
        $params = [
            'city_isos' => $cities
        ];

        if (empty($this->baseUriApiGateway)) {
            throw new \Exception("API Gateway base URI not configured");
        }

        try {
            return $this->makeAuthenticatedRequest(
                'POST',
                "api/v1/countries/cities/isos",
                [],
                $params,
                [],
                true,
                false,
                $this->baseUriApiGateway
            );
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody(), true);
            throw new \Exception("Failed to fetch getCitiesIso, status code({$statusCode}): " . ($errorBody['message'] ?? 'Unknown error'));
        }

    }
 
    
    public function searchHotelsHyperguest($formParams)
    {     
   
        return $this->makeAuthenticatedRequest('POST', "services/hotels/available",
        [], $formParams, ['Content-Type' => TYPE_JSON], true, false,$this->baseUriApiGateway);
    }

 
}
