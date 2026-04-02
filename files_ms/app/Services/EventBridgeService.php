<?php

namespace App\Services;

use Aws\EventBridge\EventBridgeClient;
use Aws\Exception\AwsException;

class EventBridgeService
{
    protected $client;

    public function __construct()
    {
        $this->client = new EventBridgeClient([
            'region' => config('aws.region'),
            'version' => config('aws.version')
        ]);
    }

    public function putEvent(array $detail, string $source, string $detailType, string $eventBusName)
    {
        try {
            $result = $this->client->putEvents([
                'Entries' => [
                    [
                        'Detail' => json_encode($detail),
                        'DetailType' => $detailType,
                        'Source' => $source,
                        'EventBusName' => $eventBusName,
                    ],
                ],
            ]);

            return $result;
        } catch (AwsException $e) {
            // Maneja el error según sea necesario
            return $e->getMessage();
        }
    }
}
