<?php

namespace App\Console\Commands;

use App\Reservation;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class CreateRelationFileOrderStella extends Command
{


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stella:create-relationship-file-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea la relacion de un file con un numero de orden en stella';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //Todo Buscamos las reservas que estan en estado 1 para la creacion de file
        $reservation = Reservation::where('status_cron_job_reservation_stella',
            Reservation::STATUS_CRONJOB_CLOSE_PROCESS)
            ->where('status_cron_job_order_stella', Reservation::STATUS_CRONJOB_CREATE_RELATIONSHIP_ORDER)
            ->whereNotNull('order_number')
            ->whereNotNull('order_number_sort')
            ->first();
        if ($reservation) {
            set_time_limit(0);

            $nroref = $reservation->file_code;
            $nroped = $reservation->order_number;
            $nroord = $reservation->order_number_sort;

            $this->info("Procesando relación para File: {$nroref} | Pedido: {$nroped}");
//            $this->info($reservation);

            // ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣
//            $aurora1 = new \App\Http\Stella\Reservation();
//            $params = [
//                'nroref' => $nroref,
//                'nroped' => $nroped,
//                'nroord' => $nroord
//            ];
//            $response = $aurora1->updateRelationShipFileOrder($params);
//            $response = (array)json_decode($response);
//            if (isset($response["response"]) and $response["response"] == "success") {
//                $reservation->status_cron_jon_order_stella = Reservation::STATUS_CRONJOB_CLOSE_RELATIONSHIP_ORDER_PROCESS;
//                $reservation->save();
//            }
//            $this->info($response);
            // ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣
            // ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣
            $baseUrl = config('services.files_onedb.domain');
            $endpoint = rtrim($baseUrl, '/') . "/files/{$nroref}/orders/relaci";

            try {
                $client = new Client();
                $apiResponse = $client->post($endpoint, [
                    'json' => [
                        'nroped' => $nroped,
                        'nroord' => $nroord
                    ],
                    'timeout' => 60
                ]);

                // Decodificamos el JSON de la respuesta de Express
                $response = json_decode($apiResponse->getBody()->getContents(), true);

                if (isset($response["response"]) && $response["response"] == "success") {
                    $reservation->status_cron_job_order_stella = Reservation::STATUS_CRONJOB_CLOSE_RELATIONSHIP_ORDER_PROCESS;
                    $reservation->save();

                    $this->info("Relación creada y guardada exitosamente en BD local.");
                } else {
                    $this->error("La API de Express devolvió un error o no retornó success.");
                }

            } catch (\Exception $e) {
                $this->error("Error de conexión con el servicio Express: " . $e->getMessage());
            }
            // ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣ ♣

        } else {
            $this->info("No hay reservas pendientes con los estados requeridos para crear relación.");
        }

    }


}
