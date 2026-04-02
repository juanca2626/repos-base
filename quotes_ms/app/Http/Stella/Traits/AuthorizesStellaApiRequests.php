<?php

namespace App\Http\Stella\Traits;

use App\Services\MarketAuthenticationService;

trait AuthorizesStellaApiRequests
{
    /**
     * Resolves the elements to send when authorazing the request
     * @param  array &$queryParams
     * @param  array &$formParams
     * @param  array &$headers
     * @return void
     */
    //    public function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    //    {
    //        $accessToken = $this->resolveAccessToken();
    //        $headers['Authorization'] = $accessToken;
    //    }

    //    public function resolveAccessToken()
    //    {
    //        $authenticationService = resolve(MarketAuthenticationService::class);
    //        try{
    //            $authenticationService->getStatusConnectionAurora();
    //
    //            return $authenticationService->getAuthenticatedUserToken();
    //
    //        }catch (\Exception $e){
    //            return $authenticationService->getClientCredentialsToken(1);
    //        }
    //    }
}
