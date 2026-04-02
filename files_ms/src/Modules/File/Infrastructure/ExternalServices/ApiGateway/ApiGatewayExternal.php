<?php

namespace Src\Modules\File\Infrastructure\ExternalServices\ApiGateway;

use App\Services\Auth\CognitoAuthService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ClientException;
use App\Http\Traits\InteractsWithServiceResponses;

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

    public function getMasterServices($equivalences)
    {
        try{
            return $this->makeAuthenticatedRequest(
                'POST',
                "api/v1/commercial/ifx/services/codes",
                [],
                $equivalences,
                ['Content-Type' => 'application/json'],
                true,
                false,
                $this->baseUriStella
            );
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody(), true);
            throw new \Exception("Failed to fetch getMasterServices, status code ({$statusCode}): " . ($errorBody['message'] ?? 'Unknown error'));
        }
    }

    public function getHotelSuppliers($equivalences)
    {
        try{
            return $this->makeAuthenticatedRequest(
                'POST',
                "api/v1/commercial/ifx/services/providers/codes",
                [],
                $equivalences,
                ['Content-Type' => 'application/json'],
                true,
                false,
                $this->baseUriStella
            );
        }catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody(), true);
            throw new \Exception("Failed to fetch getHotelSuppliers, status code ({$statusCode}): " . ($errorBody['message'] ?? 'Unknown error'));
        }
    }

    public function getSuppliers($code)
    {
        try{
            return $this->makeAuthenticatedRequest(
                'GET',
                "api/v1/commercial/ifx/providers/" . $code,
                [],
                [],
                ['Content-Type' => 'application/json'],
                true,
                false,
                $this->baseUriStella
            );
        }catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody(), true);
            throw new \Exception("Failed to fetch getSuppliers, status code ({$statusCode}): " . ($errorBody['message'] ?? 'Unknown error'));
        }
    }

    public function getNoteClassification(){
        try{
            return $this->makeAuthenticatedRequest(
                'GET',
                "api/v1/commercial/ifx/services/notes-classification",
                [],
                [],
                ['Content-Type' => 'application/json'],
                true,
                false,
                $this->baseUriStella
            );
        }catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody(), true);
            throw new \Exception("Failed to fetch getNoteClassification, status code ({$statusCode}): " . ($errorBody['message'] ?? 'Unknown error'));
        }
    }

    public function search_file_services($nrofile, $clasif, $include_xl, $nroites_not_in, $confirmation)
    {
        try{
            $params['clasif'] = $clasif; // all
            $params['include_xl'] = ($include_xl) ? true : false;
            $params['nroites_not_in'] = $nroites_not_in;
            $params['confirmation'] = $confirmation;

            return $this->makeAuthenticatedRequest(
                'GET',
                'api/v1/files/'.$nrofile.'/services'
                ,
                $params,
                [],
                ['Content-Type' => 'application/json'],
                true,
                false,
                $this->baseUriApiGateway
            );
        }catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody(), true);
            throw new \Exception("Failed to fetch search_file_services, status code ({$statusCode}): " . ($errorBody['message'] ?? 'Unknown error'));
        }
    }

    public function search_file_stela($params)
    {
        try{
            return $this->makeAuthenticatedRequest(
                'POST',
                'api/v1/commercial/ifx/files/search',
                [],
                $params,
                ['Content-Type' => 'application/json'],
                true,
                false,
                $this->baseUriStella
            );
        }catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody(), true);
            throw new \Exception("Failed to fetch search_file_stela, status code ({$statusCode}): " . ($errorBody['message'] ?? 'Unknown error'));
        }
    }

    public function search_file_services_stela($nrofile)
    {
        try{
            return $this->makeAuthenticatedRequest(
                'GET',
                'api/v1/commercial/ifx/files/'.$nrofile.'/services',
                [],
                [],
                ['Content-Type' => 'application/json'],
                true,
                false,
                $this->baseUriStella
            );
        }catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody(), true);
            throw new \Exception("Failed to fetch search_file_services_stela, status code ({$statusCode}): " . ($errorBody['message'] ?? 'Unknown error'));
        }
    }

    public function search_file_service_compositions_requires_confirmation()
    {
        try{
            return $this->makeAuthenticatedRequest(
                'GET',
                'api/v1/commercial/ifx/services/requires-confirmation',
                [],
                [],
                ['Content-Type' => 'application/json'],
                true,
                false,
                $this->baseUriStella
            );
        }catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody(), true);
            throw new \Exception("Failed to fetch search_file_service_compositions_requires_confirmation, status code ({$statusCode}): " . ($errorBody['message'] ?? 'Unknown error'));
        }
    }

    public function statement_payments_received($nrofile)
    {
        try{
            return $this->makeAuthenticatedRequest(
                'GET',
                'api/v1/commercial/ifx/files/'.$nrofile.'/statements/payments',
                [],
                [],
                ['Content-Type' => 'application/json'],
                true,
                false,
                $this->baseUriStella
            );
        }catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody(), true);
            throw new \Exception("Failed to fetch statement_payments_received, status code ({$statusCode}): " . ($errorBody['message'] ?? 'Unknown error'));
        }
    }

    public function statement_array_payments_received(array $nrofile)
    {
        try{
            return $this->makeAuthenticatedRequest(
                'POST',
                'api/v1/commercial/ifx/statements/files',
                [],
                $nrofile,
                ['Content-Type' => 'application/json'],
                true,
                false,
                $this->baseUriStella
            );
        }catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody(), true);
            throw new \Exception("Failed to fetch statement_array_payments_received, status code ({$statusCode}): " . ($errorBody['message'] ?? 'Unknown error'));
        }
    }

    public function delete_master_services_validate(string $fileNumber, array $params)
    {
        try{
            return $this->makeAuthenticatedRequest(
                'DELETE',
                'api/v1/commercial/ifx/files/'.$fileNumber.'/master_services/validate',
                [],
                $params,
                ['Content-Type' => 'application/json'],
                true,
                false,
                $this->baseUriStella
            );

        }catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody(), true);
            throw new \Exception("Failed to fetch delete_master_services_validate, status code ({$statusCode}): " . ($errorBody['message'] ?? 'Unknown error'));
        }
    }

    public function save_confirmation_codes($nrofile, $services)
    {
        try{
            return $this->makeAuthenticatedRequest(
                'POST',
                'api/v1/services/'.$nrofile.'/codcfm',
                [],
                ['services' => $services],
                ['Content-Type' => 'application/json'],
                true,
                false,
                $this->baseUriApiGateway
            );
        }catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody(), true);
            throw new \Exception("Failed to fetch save_confirmation_codes, status code ({$statusCode}): " . ($errorBody['message'] ?? 'Unknown error'));
        }
    }

    public function getSuppliersByCode($code)
    {
        try{
            return $this->makeAuthenticatedRequest(
                'GET',
                "api/v1/commercial/ifx/providers/" . $code,
                [],
                [],
                ['Content-Type' => 'application/json'],
                true,
                false,
                $this->baseUriStella
            );
        }catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody(), true);
            throw new \Exception("Failed to fetch getSuppliersByCode, status code ({$statusCode}): " . ($errorBody['message'] ?? 'Unknown error'));
        }
    }

    public function me()
    {
        return $this->makeAuthenticatedRequest(
            'GET',
            "api/v1/auth/me" ,
            [],
            [],
            ['Content-Type' => 'application/json'],
            true,
            false,
            $this->baseUriCognito
        );
    }

    public function getExecutive(string $code)
    {
        try{
            $params = ["code" => $code];
            return $this->makeAuthenticatedRequest(
                "GET",
                "api/v1/commercial/ifx/public/executives/selectBox",
                $params,
                [],
                ['Content-Type' => 'application/json'],
                true,
                false,
                $this->baseUriStella
            );

        }catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody(), true);
            throw new \Exception("Failed to fetch getExecutive, status code ({$statusCode}): " . ($errorBody['message'] ?? 'Unknown error'));
        }
    }
}
