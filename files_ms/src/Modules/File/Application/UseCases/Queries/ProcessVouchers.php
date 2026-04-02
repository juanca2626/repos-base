<?php
namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http; // Usamos Http de Laravel para hacer solicitudes

class ProcessVouchers
{
    protected $apiUrl;

    public function __construct()
    {
        // Obtener la URL del endpoint desde .env
        $this->apiUrl = env('AWS_SNS_SQS', 'https://0sbx0xebr4.execute-api.us-east-1.amazonaws.com/sqs/publish');
    }

    public function execute(array $data)
    {
      //  dd($data);
        // Si $data contiene varios elementos, lo procesamos de manera iterativa
        if (isset($data[0]) && is_array($data[0])) {
            // Procesar múltiples elementos
            foreach ($data as $item) {
                $this->processSingleVoucher($item);
            }
        } else {
            // Procesar un solo elemento
            $this->processSingleVoucher($data);
        }
    }

    // Función para procesar un solo "voucher"
    private function processSingleVoucher(array $data)
    {
        // Verificar si las claves necesarias existen en el array $data
        $user = isset($data['user']) ? $data['user'] : 'default_user';  // Valor predeterminado si falta 'user'
        $notify = isset($data['notify']) ? $data['notify'] : null;      // Si 'notify' no está, lo dejamos como null
        $name = isset($data['name']) ? $data['name'] : 'No name';       // Valor predeterminado para 'name'

        // Log para verificar que los datos están llegando correctamente
        \Log::info('Processing voucher with data: ', $data);

        // Construcción del mensaje para SQS
        $message = [
            'queueName' => 'sqs-notifications-dev',
            'metadata' => [
                'origin' => 'A3-Front',
                'destination' => 'A3 && Informix',
                'user' => $user,
                'service' => 'files',
                'notify' => $notify,
            ],
            'payload' => [
                [
                    'template' => 'vouchers',
                    'data' => json_encode(['name' => $name]),
                    'module' => '1111111111111',
                    'submodule' => 'vouchers',
                    'object_id' => $data['object_id'] ?? 'default_object_id', // Usamos un valor predeterminado si falta 'object_id'
                    'to' => $data['to'] ?? 'pedro.alegria@gmail.com', // Usuario de destino por defecto
                    'cc' => $data['cc'] ?? [],
                    'bcc' => $data['bcc'] ?? [],
                    'subject' => 'Notification Voucher',
                    'body' => '<b>Orden de compra N°: ' . ($data['order_number'] ?? 'N/A') . '</b>', // En caso de que 'order_number' no esté
                    'replyTo' => $data['replyTo'] ?? [],
                    'attachments' => $data['attachments'] ?? [],
                ],
            ],
        ];

        try {
            // Enviar el mensaje a través de API 
            $response = Http::post($this->apiUrl, [
                'MessageBody' => json_encode($message), // Cuerpo del mensaje
            ]);

            if ($response->successful()) {
                \Log::info('Message successfully sent to API ', ['response' => $response->body()]);
            } else {
                \Log::error('Failed to send message to API ', [
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                ]);
            }

        } catch (\Exception $e) {
            // Si hay un error al enviar la solicitud, lo registramos
            \Log::error('Error sending message to API', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
        }
    }
}