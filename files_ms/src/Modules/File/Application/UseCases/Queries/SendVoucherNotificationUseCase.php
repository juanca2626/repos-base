<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Repositories\FileServiceCompositionRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Aws\Sqs\SqsClient;

class SendVoucherNotificationUseCase
{
   
     public function execute(array $data)
    {
        // Extraer los datos necesarios del array recibido
        $message = $this->buildMessage($data);

        // Enviar el mensaje a SQS
        $this->sendToSqs($message);
    }

     // Método para construir el mensaje a enviar a SQS
    private function buildMessage(array $data)
    {
        return [
            'queueName' => 'sqs-notifications-dev',  // Nombre de la cola
            'metadata' => [
                'origin' => 'A3-Front',
                'destination' => 'A3 && Informix',
                'user' => $data['user'] ?? '',
                'service' => 'files',
                'notify' => $data['notify'] ?? ['lsv@limatours.com.pe'],
            ],
            'payload' => [
                [
                    'template' => 'vouchers',
                    'data' => json_encode(['name' => 'Pedro Alegria']),
                    'module' => '1111111111111',
                    'submodule' => 'vouchers',
                    'object_id' => '44444',
                    'to' => $data['to'] ?? ['pedro.alegria@gmail.com'],
                    'cc' => $data['cc'] ?? [],
                    'bcc' => $data['bcc'] ?? [],
                    'subject' => 'Notification Voucher',
                    'body' => '<b>Orden de compra N°: 2111212</b>',
                    'replyTo' => $data['replyTo'] ?? [],
                    'attachments' => $data['attachments'] ?? [],
                ],
            ],
        ];
    }

     // Método para enviar el mensaje a SQS
    private function sendToSqs(array $message)
    {
        // Realizamos la solicitud HTTP POST a la URL de SQS
        $response = Http::post('https://0sbx0xebr4.execute-api.us-east-1.amazonaws.com/sqs/publish', [
            'message' => json_encode($message),
        ]);

        // Verificamos si la solicitud fue exitosa
        if ($response->successful()) {
            \Log::info('Notification sent to SQS successfully', [
                'response' => $response->json(),
            ]);
        } else {
            \Log::error('Failed to send notification to SQS', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
        }
    }
}