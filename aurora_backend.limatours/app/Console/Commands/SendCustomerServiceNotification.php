<?php

namespace App\Console\Commands;

use App\Http\Stella\StellaService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SendCustomerServiceNotification extends Command
{
    protected $stellaService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer_service:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios de Customer Service conectando con Express';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(StellaService $stellaService)
    {
        parent::__construct();
        $this->stellaService = $stellaService;
    }

    /**
     * Calcula la diferencia en semanas
     */
    public function diffWeeks($days)
    {
        $diff = ($days > 7) ? ($days - 7) : $days;

        if ($diff > 7) {
            $diff = $this->diffWeeks($diff);
        }

        return $diff;
    }

    /**
     * Execute the console command.
     * CADA 24 Hrs
     * @return mixed
     */
    public function handle()
    {
        $this->info('Iniciando proceso de envío de recordatorios...'); // Imprime en consola (Verde)

        $date = date("d/m/Y");
        $params = ['date' => $date];
        $debug = false;

        $formats = [
            'customer_service_pendings',
            'customer_service_occurrences_pendings'
        ];

        $types = [
            'product',
            'occurrence'
        ];

        $baseUrl = config('services.accountancy_onedb.domain');
        $endpoint = rtrim($baseUrl, '/') . '/customer-service/reminders';
        $client = new Client();

        $finalResponses = [];

        foreach ($formats as $f => $format) {
            $this->line("Consultando formato: {$format}");

            $items = $this->toArray($this->stellaService->$format($params));
            $new_items = [];

            if (!empty($items)) {
                foreach ($items as $item) {
                    $date_now = Carbon::now();
                    $day = $date_now->dayOfWeek;
                    $difference_in_days = Carbon::parse($item['fecha'])->diffInDays($date_now);
                    $weeks = $this->diffWeeks($difference_in_days);

                    if (((strtoupper(trim($item['codusu'])) != 'LTM' && $weeks > 3) ||
                            strtoupper(trim($item['codusu'])) == 'LTM') &&
                        (($difference_in_days == 7 ||
                                ($difference_in_days > 7 && $weeks % 2 == 0)
                            ) && $day > 0 && $day < 6) && $day % 2 == 1)
                    {
                        $new_items[] = $item;
                    }
                }
            }

            if (!empty($new_items)) {
                $permitted = ['CODUSU', 'COMMENT', 'CODREF', 'PREFAC'];

                foreach ($new_items as $key => $value) {
                    $_value = [];
                    foreach ($value as $k => $v) {
                        $k = strtoupper($k);
                        if (count($permitted) == 0 || in_array($k, $permitted)) {
                            $_value[$k] = trim($v);
                        }
                    }
                    $new_items[$key] = (object) $_value;
                }

                $data = [
                    'type' => $types[$f],
                    'items' => $new_items, // Express leerá esto como Array
                    'debug' => $debug
                ];

                $this->info("Enviando " . count($new_items) . " items de tipo '{$types[$f]}' a Express...");

                try {
                    $request = $client->post($endpoint, [
                        'json' => $data,
                        'timeout' => 60
                    ]);

                    $response = json_decode($request->getBody()->getContents(), true);
                    $finalResponses[$types[$f]] = $response;

                    $this->info("Respuesta exitosa de Express para {$types[$f]}.");

                } catch (\Exception $e) {
                    $this->error("Error conectando con Express para {$types[$f]}: " . $e->getMessage()); // Imprime en consola (Rojo)
                    Log::error("Error en customer_service:reminders (Express): " . $e->getMessage());

                    $finalResponses[$types[$f]] = [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                }
            } else {
                $this->line("No se encontraron items para enviar en {$types[$f]}.");
            }
        }

        $this->info('Proceso finalizado. Resumen de respuestas:');
        // Usamos dump para imprimir el arreglo estructurado completo en la consola
        dump($finalResponses);
    }

    public function toArray($object = [])
    {
        if (is_object($object) || is_array($object)) {
            $array = [];
            foreach ($object as $key => $value) {
                if (is_object($value) || is_array($value)) {
                    $value = $this->toArray($value);
                }
                $array[$key] = $value;
            }
            return $array;
        } else {
            return $object;
        }
    }

    public function throwError($ex)
    {
        return [
            'file' => $ex->getFile(),
            'line' => $ex->getLine(),
            'detail' => $ex->getMessage(),
            'message' => $ex->getMessage(),
            'type' => 'error',
            'success' => false,
            'process' => false,
            'response' => 'ERR',
        ];
    }
}

//
//namespace App\Console\Commands;
//
//use App\Http\Stella\StellaService;
//use Carbon\Carbon;
//use Illuminate\Console\Command;
//
//class SendCustomerServiceNotification extends Command
//{
//    protected $stellaService;
//    /**
//     * The name and signature of the console command.
//     *
//     * @var string
//     */
//    protected $signature = 'customer_service:reminders';
//
//    /**
//     * The console command description.
//     *
//     * @var string
//     */
//    protected $description = 'Command description';
//
//    /**
//     * Create a new command instance.
//     *
//     * @return void
//     */
//    public function __construct(StellaService $stellaService)
//    {
//        parent::__construct();
//        $this->stellaService = $stellaService;
//    }
//
//    /**
//     * Execute the console command.
//     * CADA 24 Hrs
//     * @return mixed
//     */
//    public function diffWeeks($days)
//    {
//        $diff = ($days > 7) ? ($days - 7) : $days;
//
//        if($diff > 7)
//        {
//            $diff = $this->diffWeeks($diff);
//        }
//
//        return $diff;
//    }
//
//    public function handle() // Una vez al dia deberia ejecutarse
//    {
//
//        $date = date("d/m/Y"); $params = ['date' => $date]; $debug = false;
//
//        $formats = [
//            'customer_service_pendings',
//            'customer_service_occurrences_pendings'
//        ];
//
//        $types = [
//            'product',
//            'occurrence'
//        ];
//
//        $response = '';
//
//        foreach($formats as $f => $format)
//        {
//            $items = $this->toArray($this->stellaService->$format($params));
//            $client = new \GuzzleHttp\Client(); $data = [];
//
//            $new_items = [];
//            if(!empty($items))
//            {
//                foreach($items as $item)
//                {
//                    $date_now = Carbon::now(); $day = $date_now->dayOfWeek;
//                    $difference_in_days = Carbon::parse($item['fecha'])->diffInDays($date_now); // 15
//                    $weeks = $this->diffWeeks($difference_in_days);
//
//                    if(((strtoupper(trim($item['codusu'])) != 'LTM' && $weeks > 3) ||
//                        strtoupper(trim($item['codusu'])) == 'LTM') &&
//                        (($difference_in_days == 7 ||
//                        ($difference_in_days > 7 && $weeks % 2 == 0)
//                        ) && $day > 0 && $day < 6) && $day % 2 == 1)
//                    {
//                        // Ignorar sábados y domingos..
//                        $new_items[] = $item;
//                    }
//                }
//            }
//
//            if(!empty($new_items))
//            {
//                $permitted = ['CODUSU', 'COMMENT', 'CODREF', 'PREFAC'];
//
//                foreach($new_items as $key => $value)
//                {
//                    $_value = [];
//
//                    foreach($value as $k => $v)
//                    {
//                        $k = strtoupper($k);
//
//                        if(count($permitted) == 0 || in_array($k, $permitted))
//                        {
//                            $_value[$k] = trim($v);
//                        }
//                    }
//
//                    $new_items[$key] = (object) $_value;
//                }
//
//                $data = [
//                    'accion' => 'notificationReminder',
//                    'items' => json_encode($new_items),
//                    'debug' => $debug,
//                    'type' => $types[$f],
//                ];
//
////                dump($data);
//
//                $url = config('services.aurora_extranet.domain') . '/api/customer-service/api.php';
//                $request = $client->request('POST',
//                    $url, [
//                        'json' => $data,
//                        'headers' => ['Content-Type' => 'application/json'],
//                    ]);
//                $response = json_decode($request->getBody()->getContents());
//            }
//        }
//
//        dd($response);
//    }
//
//    public function toArray($object = [])
//    {
//        if (is_object($object) || is_array($object)) {
//            $array = [];
//
//            foreach ($object as $key => $value) {
//                if (is_object($value) || is_array($value)) {
//                    $value = $this->toArray($value);
//                }
//
//                $array[$key] = $value;
//            }
//
//            return $array;
//        } else {
//            return $object;
//        }
//    }
//
//    public function throwError($ex)
//    {
//        return [
//            'file' => $ex->getFile(),
//            'line' => $ex->getLine(),
//            'detail' => $ex->getMessage(),
//            'message' => $ex->getMessage(),
//            'type' => 'error',
//            'success' => false,
//            'process' => false,
//            'response' => 'ERR',
//        ];
//    }
//
//}
