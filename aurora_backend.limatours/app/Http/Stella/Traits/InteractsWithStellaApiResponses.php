<?php


namespace App\Http\Stella\Traits;


trait InteractsWithStellaApiResponses
{
    /**
     * Decode correspondingly the response
     * @param  array  $response
     * @return stdClass
     */
    public function decodeResponse($response, $responseAll = false)
    {
        $decodedResponse = json_decode($response);
        if ($responseAll) {
            return $decodedResponse;
        }
        return $decodedResponse->data ?? $decodedResponse;
    }

    /**
     * Resolve if the request to the service failed
     * @param  array  $response
     * @return void
     * @throws \Exception
     */
    public function checkIfErrorResponse($response)
    {
        if (isset($response->error)) {
            throw new \Exception("Something failed: {$response->error}");
        }
    }
}
