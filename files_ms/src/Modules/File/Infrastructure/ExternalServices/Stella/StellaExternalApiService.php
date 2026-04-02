<?php


namespace Src\Modules\File\Infrastructure\ExternalServices\Stella;

use App\Http\Traits\StellaAuthorizesServiceRequests;
use App\Http\Traits\ConsumesExternalServices;
use App\Http\Traits\InteractsWithServiceResponses;
const TYPE_JSON = "application/json";
use GuzzleHttp\Client;

class StellaExternalApiService_DELETE
{
    use ConsumesExternalServices, StellaAuthorizesServiceRequests, InteractsWithServiceResponses;

    protected string $bearerToken = '';

    /**
     * The url from which send the requests
     * @var string
     */
    protected mixed $baseUri;
    protected mixed $baseUriCognito;
    protected string $serviceName;

    public function __construct($bearer = '')
    {
        $this->serviceName = 'stella';
        $this->baseUri = config('services.stella.endpoint');
        $this->baseUriCognito = config('services.cognito.endpoint');
        $this->bearerToken = $bearer;
    }

    public function login()
    {
        $client = new Client();
        $urlCompleta = 'https://auroraback.limatours.dev/api/login';

        $username = env('AURORA_USER');
        $password = env('AURORA_PWD');

        $body = [
            'code' => $username,
            'password' => $password
        ];

        try {
            // Realiza la solicitud POST
            $response = $client->post($urlCompleta, [
                'json' => $body, // Pasamos el cuerpo como JSON
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            // Decodifica la respuesta JSON
            $responseData = json_decode($response->getBody(), true);

            // Verifica si el token de acceso está presente
            if (isset($responseData['access_token'])) {
                $this->bearerToken = $responseData['access_token'];
            } else {
                throw new \Exception('El token de acceso no fue encontrado en la respuesta.');
            }

            return $responseData['access_token'];
        } catch (\Exception $e) {
            throw new \Exception("Error al realizar el login: " . $e->getMessage());
        }
    }



     public function setAuthorization($bearer)
    {

        if(!empty($bearer))
        {
            $this->bearerToken = $bearer;
        }

    }

    public function getMasterServices($equivalences)
    {
        return $this->makeRequest('POST', "api/v1/commercial/ifx/services/codes", [], $equivalences,
            ['Content-Type' => 'application/json'], true, false);
    }

    public function getHotelSuppliers($equivalences)
    {
        return $this->makeRequest('POST', "api/v1/commercial/ifx/services/providers/codes", [], $equivalences,
            ['Content-Type' => 'application/json'], true, false);
    }

    public function getSuppliers($code)
    {
        return $this->makeRequest('GET', "api/v1/commercial/ifx/providers/" . $code, [], [],
            ['Content-Type' => 'application/json'], true, false);
    }

    public function getNoteClassification(){
        return $this->makeRequest('GET', "api/v1/commercial/ifx/services/notes-classification", [], [],
            ['Content-Type' => 'application/json'], true, false);
    }

    public function search_file_services($nrofile, $clasif, $include_xl, $nroites_not_in, $confirmation)
    {
        $params['clasif'] = $clasif; // all
        $params['include_xl'] = ($include_xl) ? true : false;
        $params['nroites_not_in'] = $nroites_not_in;
        $params['confirmation'] = $confirmation;

        return $this->makeRequest('GET',
            'api/v1/files/'.$nrofile.'/services', $params, [], ['Content-Type' => 'application/json'], true, false, "https://apidev.limatours.dev/");
    }

    public function search_file_stela($params)
    {

        $baseUrl = 'api/v1/commercial/ifx/files/search';
        // $url = $baseUrl . '?' . http_build_query($params);
        return $this->makeRequest('POST',
            $baseUrl, [], $params, ['Content-Type' => 'application/json'], true, false);
    }

    public function search_file_services_stela($nrofile)
    {
        return $this->makeRequest('GET',
            'api/v1/commercial/ifx/files/'.$nrofile.'/services', [], [], ['Content-Type' => 'application/json'], true, false);
    }

    public function search_file_service_compositions_requires_confirmation()
    {
        return $this->makeRequest('GET',
            'api/v1/commercial/ifx/services/requires-confirmation', [], [], ['Content-Type' => 'application/json'], true, false);
    }

    public function statement_payments_received($nrofile)
    {
        return $this->makeRequest('GET',
            'api/v1/commercial/ifx/files/'.$nrofile.'/statements', [], [], ['Content-Type' => 'application/json'], true, false);
    }

    public function statement_array_payments_received(array $nrofile)
    {
        return $this->makeRequest('POST',
            'api/v1/commercial/ifx/statements/files', [], $nrofile, ['Content-Type' => 'application/json'], true, false);
    }

    public function delete_master_services_validate(string $fileNumber, array $params)
    {
        //dd(json_encode($params));
        return $this->makeRequest('DELETE',
            'api/v1/commercial/ifx/files/'.$fileNumber.'/master_services/validate', [], $params, ['Content-Type' => 'application/json'], true, false);
    }
    
    // public function statement_payments_received($equivalences)
    // {
    //     return $this->makeRequest('POST', "api/v1/commercial/ifx/services/codes", [], $equivalences,
    //         ['Content-Type' => 'application/json'], true, false);
    // }

    public function save_confirmation_codes($nrofile, $services)
    {
        return $this->makeRequest('POST', 'api/v1/services/'.$nrofile.'/codcfm', [], ['services' => $services], ['Content-Type' => 'application/json'], true, false, "https://apidev.limatours.dev/");
    }


    public function getSuppliersByCode($code)
    {
        return $this->makeRequest('GET', "api/v1/commercial/ifx/providers/" . $code, [], [],
            ['Content-Type' => 'application/json'], true, false);
    }
 
    public function me()
    {
        return $this->makeRequest('GET', "api/v1/auth/me" , [], [],
            ['Content-Type' => 'application/json'], true, false, $this->baseUriCognito);
    }

}
