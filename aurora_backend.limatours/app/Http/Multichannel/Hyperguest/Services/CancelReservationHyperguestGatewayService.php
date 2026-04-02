<?php

namespace App\Http\Multichannel\Hyperguest\Services;

use Exception;

class CancelReservationHyperguestGatewayService
{
    // Desde canal Aurora
    public function cancelReservationLegacy(array $params): ?array
    {
        $paramsCreateReservation = [
            "file" => $params["file"],
            "channelReservationId" => (string)$params["channelReservationId"],
        ];

        $responseChannel = $this->cancelReservationGateway($paramsCreateReservation);

        return $responseChannel;
    }

    // Cancelar reserva en canal Hyperguest
    private function cancelReservationGateway(array $params): ?array
    {
        [
            "file" => $fileCode,
            "channelReservationId" => $channelReservationId,
        ] = $params;

        $channelIntegration = [
            'channelIntegration' => [
                'channel' => 'hyperguest',
                'type' => 'PULL',
                'version' => 'v1',
                'isActive' => true
            ]
        ];
        $paramsCancelReservation = [
            "legacyReservationId" => $fileCode,
            "channelReservationId" => $channelReservationId,
            "reason" => "Cancelado por team LimaTours",
        ];

        $payload = array_merge($channelIntegration, $paramsCancelReservation);

        $gatewayService = new HyperguestGatewayService();
        $response = $gatewayService->cancelReservation($payload);

        if (!$response['success']) {
            throw new Exception($response['error']);
        }

        return $response;
    }
}
