<?php


namespace App\Http\Aurora\Traits;


use App\Http\Aurora\Traits\ConsumesExternalServices;
use App\Http\Aurora\Traits\InteractsWithServiceResponses;
use Illuminate\Support\Facades\Cache;

class AuroraAuthenticationService
{
    /**
     * The url from which send the requests
     * @var string
     */
    protected $baseUri;

    protected $serviceName = 'aurora';
    /**
     * The client_id to identify the client in the API
     * @var string
     */
    protected $code;
    /**
     * The client_secret to identify the client in the API
     * @var string
     */
    protected $password;
    /**
     * The client_id to identify the password client in the API
     * @var string
     */
    use ConsumesExternalServices, InteractsWithServiceResponses;

    public function __construct($baseUri)
    {
        $this->baseUri = $baseUri; // config('services.aurora.endpoint');
        $this->code = config('services.aurora.user');
        $this->password = config('services.aurora.password');
        $this->serviceName = 'aurora';
    }

    public function getClientCredentialsToken()
    {
        if ($token = $this->existingValidClientCredentialsToken()) {
            return $token;
        }
        
        $formParams = [
            'code' => $this->code,
            'password' => $this->password,
        ];

        $tokenData = $this->makeRequest('POST', 'api/login', [], $formParams,['Content-Type' => 'application/x-www-form-urlencoded']);
  
        $this->storeValidToken($tokenData);

        return $tokenData->access_token;
    }

    /**
     * Generate a url for the authorization button
     * @return string
     */
    public function resolveAuthorizationUrl()
    {
        $query = http_build_query([
            'code' => $this->code,
            'password' => $this->password,
        ]);
        return "{$this->baseUri}/login?{$query}";
    }

    /**
     * Obtains an access token from a given code
     * @param string $token
     * @return stdClass
     */
    public function getCodeToken($code)
    {
        $formParams = [
            'code' => $this->code,
            'password' => $this->password,
        ];
        $tokenData = $this->makeRequest('POST', 'login', [], $formParams);
        $this->storeValidToken($tokenData);

        return $tokenData;
    }

    /**
     * Obtains an access token from user credentials
     * @param string $username
     * @param string $password
     * @return String $tokenData
     */
    public function getPasswordToken($username, $password)
    {
        $formParams = [
            'code' => $username,
            'password' => $password,
        ];
        $tokenData = $this->makeRequest('POST', 'login', [], $formParams);

        $this->storeValidToken($tokenData);

        return $tokenData;
    }

    /**
     * Obtains an access token from the authenticated user
     * @return string
     */
    public function getAuthenticatedUserToken()
    {
        $aurora_token = Cache::get('aurora_token');
        if (now()->lt($aurora_token->expires_in)) {
            return $aurora_token->access_token;
        }

        return $this->refreshAuthenticatedUserToken($aurora_token);
    }

    public function refreshAuthenticatedUserToken($aurora_token)
    {

        $tokenData = $this->makeRequest('GET', 'api/user/refresh');
        $this->storeValidToken($tokenData);

        return Cache::get('aurora_token')->access_token;
    }

    /**
     * Stores a valid token
     * @param string $tokenData
     * @return string
     */
    public function storeValidToken($tokenData)
    {
        $tokenData->expires_in = now()->addSeconds($tokenData->expires_in - 5);
        $tokenData->access_token = "{$tokenData->token_type} {$tokenData->access_token}";
        Cache::forever('aurora_token', $tokenData);
    }

    /**
     * Verifies if any token exsiting
     * @return string|boolean
     */
    public function existingValidClientCredentialsToken()
    {
        if (Cache::has('aurora_token')) {
            $tokenData = Cache::get('aurora_token');
            if (now()->lt($tokenData->expires_in)) {
                return $tokenData->access_token;
            }
        }
        return false;
    }

    public function getStatusConnectionAurora()
    {
        $user = $this->makeRequest('GET', 'api/me');
        return ($user);
    }
}
