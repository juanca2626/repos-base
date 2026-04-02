<?php


namespace Src\Modules\File\Infrastructure\ExternalServices\Ope;

use App\Http\Traits\AuthorizesServiceRequests;
use App\Http\Traits\ConsumesExternalServices;
use App\Http\Traits\InteractsWithServiceResponses;


class OpeExternalApiService
{
    use ConsumesExternalServices, AuthorizesServiceRequests, InteractsWithServiceResponses;

    /**
     * The url from which send the requests
     * @var string
     */
    protected mixed $baseUri;
    protected string $serviceName; 

    public function __construct()
    {
        $this->serviceName = 'ope';
        $this->baseUri = config('services.ope.endpoint');  
    }
 

    public function searchZones()
    {
        return $this->makeRequest('GET', "zones" , [], [],
            ['Content-Type' => 'application/json'], false, false);
    }

}
