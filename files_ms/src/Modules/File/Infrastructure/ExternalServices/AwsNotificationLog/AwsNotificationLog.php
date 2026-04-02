<?php


namespace Src\Modules\File\Infrastructure\ExternalServices\AwsNotificationLog;

use App\Http\Traits\AuthorizesServiceRequests;
use App\Http\Traits\ConsumesAwsNotificacionLogs;
use App\Http\Traits\InteractsWithServiceResponses;

class AwsNotificationLog
{
    use ConsumesAwsNotificacionLogs, AuthorizesServiceRequests, InteractsWithServiceResponses;

    /**
     * The url from which send the requests
     * @var string
     */
    protected mixed $baseUri;
    protected string $serviceName;

    public function __construct()
    {
        $this->serviceName = 'aws_notification_logs';
        $this->baseUri = config('services.aws_notification_logs.endpoint');
    }

    public function getLogs($code)
    {
        return $this->makeRequest('GET', "notifications/filter?object_id=" . $code, [], [],
            ['Content-Type' => 'application/json'], true, false);
    }

    

}
