<?php

namespace Src\Modules\File\Presentation\Http\Traits;

use Illuminate\Support\Facades\Log;
use Src\Modules\File\Presentation\Http\Traits\Amazon;

trait SqsNotification
{
    use Amazon;

    public function send_notification($params)
    {
        $return = false;
        $response = [];

        try {
            $data = [
                'queueName' => config('services.amazon.env'), // Cola dónde será dirigido el mensaje
                'metadata' => [
                    'origin' => 'Files_ms', // De qué servicio / sistema es enviado el mensaje
                    'destination' => 'Files_ms', // A qué servicio se dirige el mensaje
                    'user' => 1 , // Usuario que envía el mensaje
                    'service' => 'files_ms create file', // De qué servicio / sistema es enviado el mensaje
                    'notify' => [
                        'lsv@limatours.com.pe'
                    ]
                ],
                'payload' => [
                    (object)$params
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

    public function send_notification_master_service($params)
    {
        $return = false;
        $response = [];

        try {
            $data = [
                'queueName' => config('services.amazon.env_master_service'), // Cola dónde será dirigido el mensaje
                'metadata' => [
                    'origin' => 'Files_ms', // De qué servicio / sistema es enviado el mensaje
                    'destination' => 'Files_ms', // A qué servicio se dirige el mensaje
                    'user' => 1 , // Usuario que envía el mensaje
                    'service' => 'files_ms create master_service', // De qué servicio / sistema es enviado el mensaje
                    'notify' => [
                        'lsv@limatours.com.pe'
                    ]
                ],
                'payload' => [
                    (object)$params
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

    public function send_notification_status($params)
    {
        $return = false;
        $response = [];

        try {
            $data = [
                'queueName' => config('services.amazon.env_status'), // Cola dónde será dirigido el mensaje
                'metadata' => [
                    'origin' => 'Files_ms', // De qué servicio / sistema es enviado el mensaje
                    'destination' => 'Files_ms', // A qué servicio se dirige el mensaje
                    'user' => 1 , // Usuario que envía el mensaje
                    'service' => 'files_ms status', // De qué servicio / sistema es enviado el mensaje
                    'notify' => [
                        'lsv@limatours.com.pe'
                    ]
                ],
                'payload' => [
                    (object)$params
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

    public function send_update_file($params)
    {
        $return = false;
        $response = [];

        try {
            $data = [
                'queueName' => config('services.amazon.env_file_update'), // Cola dónde será dirigido el mensaje
                'metadata' => [
                    'origin' => 'Files_ms', // De qué servicio / sistema es enviado el mensaje
                    'destination' => 'Files_ms', // A qué servicio se dirige el mensaje
                    'user' => 1 , // Usuario que envía el mensaje
                    'service' => 'files_ms update file', // De qué servicio / sistema es enviado el mensaje
                    'notify' => [
                        'lsv@limatours.com.pe'
                    ]
                ],
                'payload' => [
                    (object)$params
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

    public function send_create_statement($params)
    {
        $return = false;
        $response = [];

        try {
            $data = [
                'queueName' => config('services.amazon.env_statement'), // Cola dónde será dirigido el mensaje
                'metadata' => [
                    'origin' => 'Files_ms', // De qué servicio / sistema es enviado el mensaje
                    'destination' => 'Files_ms', // A qué servicio se dirige el mensaje
                    'user' => 1 , // Usuario que envía el mensaje
                    'service' => 'files_ms create statement', // De qué servicio / sistema es enviado el mensaje
                    'notify' => [
                        'lsv@limatours.com.pe'
                    ]
                ],
                'payload' => [
                    (object)$params
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

            Log::debug($response);
        } finally {
            if ($return) {
                return $response;
            }
        }
    }

    public function send_update_transfer($params){
        $return = false;
        $response = [];

        try {
            $data = [
                'queueName' => config('services.amazon.env_update_accommodation'), // Cola dónde será dirigido el mensaje
                'metadata' => [
                    'origin' => 'Files_ms', // De qué servicio / sistema es enviado el mensaje
                    'destination' => 'Files_ms', // A qué servicio se dirige el mensaje
                    'user' => 1 , // Usuario que envía el mensaje
                    'service' => 'files', // De qué servicio / sistema es enviado el mensaje
                    'notify' => [
                        'lsv@limatours.com.pe'
                    ]
                ],
                'payload' => [
                    (object)$params
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
