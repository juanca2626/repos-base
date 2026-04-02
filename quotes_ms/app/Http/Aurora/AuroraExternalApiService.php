<?php



namespace App\Http\Aurora;
use App\Http\Aurora\Traits\AuthorizesServiceRequests;
use App\Http\Aurora\Traits\ConsumesExternalServices;
use App\Http\Aurora\Traits\InteractsWithServiceResponses;
use Illuminate\Http\Request;

    const TYPE_JSON = "application/json";

class AuroraExternalApiService
{
    use ConsumesExternalServices, AuthorizesServiceRequests, InteractsWithServiceResponses;

    /**
     * The url from which send the requests
     * @var string
     */
    protected mixed $baseUri;
    protected string $serviceName;
    protected string $bearerToken = '';

    public function __construct()
    {
        $this->serviceName = 'aurora';
        $this->baseUri = config('services.aurora.domain'); // auroraback
    }

    public function setAuthorization($bearer)
    {

        if(!empty($bearer))
        {
            $this->bearerToken = $bearer;
        }

    }
    
    public function getAuthorization()
    {
        return $this->bearerToken;
    }


    public function searchHotelsHyperguest($formParams)
    {
        try {
            return $this->makeRequest('POST', "services/hotels/available",
            [], $formParams, ['Content-Type' => TYPE_JSON], true, false, $this->baseUri);
        } catch (\Exception $e) {
            throw new \Exception("Failed to search hotels hyperguest: " . $e->getMessage());
        }
    }


}
