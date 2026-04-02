<?php


namespace App\Http\Traits;


use App\Http\Traits\AuroraAuthenticationService;

trait StellaAuthorizesServiceRequests
{
    /**
     * Resolves the elements to send when authorazing the request
     * @param  array &$queryParams
     * @param  array &$formParams
     * @param  array &$headers
     * @return void
     */
    public function resolveAuthorization(&$queryParams, &$formParams, &$headers, &$baseUri)
    {
        $accessToken = $this->resolveAccessToken($baseUri);
        $headers['Authorization'] = $accessToken;
    }

    public function resolveAccessToken($baseUri)
    {
        $authenticationService = new StelaAuthenticationService($baseUri);
        if (auth()->user()) {
            return $authenticationService->getAuthenticatedUserToken();
        }

        return $authenticationService->getClientCredentialsToken();
    }
}
