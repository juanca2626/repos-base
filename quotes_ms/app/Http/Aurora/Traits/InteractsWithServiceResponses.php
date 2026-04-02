<?php


namespace App\Http\Aurora\Traits;


trait InteractsWithServiceResponses
{
    /**
     * Decode correspondingly the response
     * @param  array $response
     * @return stdClass
     */
    public function decodeResponse($response, $responseAll = false)
    {
        $decodedResponse = json_decode($response, $responseAll);
        if ($responseAll) {
            return $decodedResponse;
        }
        return $decodedResponse->data ?? $decodedResponse;
    }

    /**
     * Resolve if the request to the service failed
     * @param array $response
     * @return void
     * @throws \Exception
     */
    public function checkIfErrorResponse($response)
    {   
        if (isset($response->error)) {
            $error = '';
            if(is_object($response->error)){
                $error = $response->error->message;
              
            }else{
                $error = $response->error;
               
            }
           
            throw new \Exception("Something failed: {$error}");
        }
    }
}
