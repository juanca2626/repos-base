<?php


namespace Src\Modules\File\Infrastructure\ExternalServices\Amazon;

use App\Http\Traits\AuthorizesServiceRequests;
use App\Http\Traits\ConsumesExternalServices;
use App\Http\Traits\InteractsWithServiceResponses;

class AmazonApiService
{
    use ConsumesExternalServices, AuthorizesServiceRequests, InteractsWithServiceResponses;

     /**
     * The base URI for the API
     * @var string
     */
    private string $baseUri;

    public function __construct()
    {
        $this->baseUri = config('services.aws_sns_sqs.endpoint');
    }

    public function sendNotification(array $data)
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        try {
            return $this->makeRequest('POST', $this->baseUri, [], $data, $headers, true);
        } catch (RequestException $e) {

            logger()->error('Amazon API Service Error:', ['error' => $e->getMessage()]);
            throw $e; // Re-throw the exception to handle it elsewhere
        }
    }
}