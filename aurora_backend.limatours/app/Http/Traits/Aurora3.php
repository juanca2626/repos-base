<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Log;
use App\Http\Traits\Amazon;
use App\User;

trait Aurora3
{
    use Amazon;

    public function createFileA3($reservation, $return = false)
    {
        $executive = User::where('id', '=', $reservation->executive_id)->first();

        $response = [];
        $env = config('services.aurora_files.env');

        if (!empty($env)) {

            try {

                $data = [
                    'queueName' => config('services.aurora_files.env'), // Cola dónde será dirigido el mensaje
                    'metadata' => [
                        'origin' => 'A2', // De qué servicio / sistema es enviado el mensaje
                        'destination' => 'A3-Informix', // A qué servicio se dirige el mensaje
                        'user' => @$executive->code, // Usuario que envía el mensaje
                        'service' => 'files', // De qué servicio / sistema es enviado el mensaje
                        'notify' => [
                            'lsv@limatours.com.pe'
                        ]
                    ],
                    'payload' => [
                        (object)[
                            'type' => 'reservation',
                            'endpoint' => config('services.aurora_files.domain') . '/api/v1/files',
                            'reservation_id' => $reservation->id,
                        ]
                    ]
                ];

                $response = $this->publish_sqs($data);
            } catch (\Exception $ex) {
                $response = json_encode([
                    'error' => $ex->getMessage(),
                    'line' => $ex->getLine(),
                    'file' => $ex->getFile(),
                ]);

            } finally {
                if ($return) {
                    return $response;
                }
            }
        }

        return true;
    }

    public function createFileOTSA3($reservation_data, $executive, $return = false)
    {
        $response = [];
        $env = config('services.aurora_files.env');

        if (!empty($env)) {
            try {
                $data = [
                    'queueName' => config('services.aurora_files.env'), // Cola dónde será dirigido el mensaje
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
                        (object) [
                            'type' => 'ots',
                            'data' => $reservation_data,
                        ]
                    ]
                ];

                $response = $this->publish_sqs($data);
            } catch (\Exception $ex) {
                $response = json_encode([
                    'error' => $ex->getMessage(),
                    'line' => $ex->getLine(),
                    'file' => $ex->getFile(),
                ]);

            } finally {
                if ($return) {
                    return $response;
                }
            }
        }

        return true;
    }


    public function getToken()
    {

        $endpoint = config('services.cognito.endpoint') . 'api/v1/auth/login';
        $client = new \GuzzleHttp\Client();
        $response_sqs = $client->request(
            'POST',
            $endpoint,
            [
                "json" => [
                    'username' => config('services.cognito.user'),
                    'password' => config('services.cognito.password'),
                ],
                "headers" => [
                    'Content-Type' => 'application/json'
                ]
            ]
        );
        $response = $response_sqs->getBody()->getContents();
        $response = json_decode($response);

        if ($response->success) {
            return $response->access_token;
        }

        return false;
    }
}
