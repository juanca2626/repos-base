<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Log;
use App\Http\Traits\Amazon;

trait SqsNotification
{
    use Amazon;

    public function send_notification($params)
    {
        $template = isset($params['template']) ? $params['template'] : 'reservation';
        $module = isset($params['module']) ? $params['module'] : '';
        $submodule = isset($params['submodule']) ? $params['submodule'] : '';
        $object_id = isset($params['object_id']) ? $params['object_id'] : '';
        $to = isset($params['to']) ? $params['to'] : [];
        $cc = isset($params['cc']) ? $params['cc'] : [];
        $bcc = isset($params['bcc']) ? $params['bcc'] : [];
        $subject = isset($params['subject']) ? $params['subject'] : '';
        $body = isset($params['body']) ? $params['body'] : '';
        $replyTo = isset($params['replyTo']) ? $params['replyTo'] : [];
        $attachments = isset($params['attachments']) ? $params['attachments'] : [];
        $data_info = isset($params['data']) ? $params['data'] : [];
        $user = isset($params['user']) ? $params['user'] : 'admin';

        $return = false;

        $response = [];

        $env = config('services.aurora_files.env');

        if (!empty($env)) {

            try {
                $data = [
                    'queueName' => config('services.amazon.env'), // Cola dónde será dirigido el mensaje
                    'metadata' => [
                        'origin' => 'A2', // De qué servicio / sistema es enviado el mensaje
                        'destination' => 'A3-Informix', // A qué servicio se dirige el mensaje
                        'user' => $user , // Usuario que envía el mensaje
                        'service' => 'aurora back', // De qué servicio / sistema es enviado el mensaje
                        'notify' => [
                            'lsv@limatours.com.pe'
                        ]
                    ],
                    'payload' => [
                        (object)[
                            'flag_send' => false,
                            'template' => $template,
                            'data' => json_encode($data_info) ,
                            'module' => $module,
                            'submodule' => $submodule,
                            'object_id' => $object_id,
                            'to' => $to,
                            'cc' => $cc,
                            'bcc' => $bcc,
                            'subject' => $subject,
                            'body' => $body,
                            'replyTo' => $replyTo,
                            'attachments' => $attachments

                        ]
                    ]
                ];

                // file_put_contents("reservation_sqs.txt", json_encode($data));


                $response = $this->publish_sqs($data);

            } catch (\Exception $ex) {
                $response = [
                    'error' => $ex->getMessage(),
                    'line' => $ex->getLine(),
                    'file' => $ex->getFile(),
                ];
            } finally {
                if ($return) {
                    return $response;
                }
            }
        }

    }

    public function createFileOTSA3($reservation_data, $executive, $return = false)
    {
        $response = [];

        try {
            $data = [
                'queueName' => 'sqs-files-dev', // Cola dónde será dirigido el mensaje
                'metadata' => [
                    'origin' => 'A2-OTS', // De qué servicio / sistema es enviado el mensaje
                    'destination' => 'A3-Informix', // A qué servicio se dirige el mensaje
                    'user' => $executive, // Usuario que envía el mensaje
                    'service' => 'files', // De qué servicio / sistema es enviado el mensaje
                    'notify' => [
                        'lsv@limatours.com.pe'
                    ]
                ],
                'payload' => [
                    (object)[
                        'type' => 'ots',
                        'endpoint' => config('services.aurora_files.domain') . '/api/v1/files',
                        'data' => $reservation_data,
                    ]
                ]
            ];
            // $response = $this->publish_sqs($data);
        } catch (\Exception $ex) {
            $response = [
                'error' => $ex->getMessage(),
                'line' => $ex->getLine(),
                'file' => $ex->getFile(),
            ];
        } finally {
            if ($return) {
                return $response;
            }
        }
    }
}
