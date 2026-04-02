<?php

namespace App\Http\Controllers;

use App\Currency;
use App\Http\Stella\StellaService;
use App\Http\Traits\Translations;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CustomerServiceController extends Controller
{
    protected $stellaService;

    public function __construct(StellaService $stellaService)
    {
        $this->stellaService = $stellaService;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
//    public function notifications(Request $request)
//    {
//        //
//        try
//        {
//            $date = $request->__get('date'); $params = ['date' => $date];
//            $items = $this->toArray($this->stellaService->customer_service_pendings($params));
//            $client = new \GuzzleHttp\Client(); $response = ''; $data = [];
//
//            $new_items = [];
//            if(!empty($items))
//            {
//                foreach($items as $item)
//                {
//                    $date_now = Carbon::now();
//                    $difference_in_days = Carbon::parse($item['fecha'])->diffInDays($date_now);
//
//                    if($difference_in_days == 7 || ($difference_in_days > 7 && $difference_in_days % 2 == 0))
//                    {
//                        $new_items[] = $item;
//                    }
//                }
//            }
//
//            $data = [
//                'accion' => 'notificationReminder',
//                'items' => $new_items,
//            ];
//
//            $url = config('services.aurora_extranet.domain') . '/backend/controllers/CustomerService.php';
//
//            $response = $client->request('POST',
//                $url, [
//                    "form_params" => $data
//                ]);
//            $response = json_decode($response->getBody()->getContents());
//
//            return response()->json([
//                'url' => $url,
//                'data' => $data,
//                'response' => $response,
//                'items' => $items,
//                'new_items' => $new_items,
//            ]);
//        }
//        catch(\Exception $ex)
//        {
//            dd($this->throwError($ex));
//        }
//    }

    public function notifications(Request $request)
    {
        try {
            $date = $request->__get('date');
            $params = ['date' => $date];
            $items = $this->toArray($this->stellaService->customer_service_pendings($params));
            $client = new \GuzzleHttp\Client();
            $response = null;

            $new_items = [];
            if (!empty($items)) {
                foreach ($items as $item) {
                    $date_now = Carbon::now();
                    $difference_in_days = Carbon::parse($item['fecha'])->diffInDays($date_now);

                    if ($difference_in_days == 7 || ($difference_in_days > 7 && $difference_in_days % 2 == 0)) {
                        $new_items[] = $item;
                    }
                }
            }

            // Adaptamos el payload a lo que espera Express
            $data = [
                'type'  => 'product',
                'items' => $new_items,
                'debug' => false
            ];

            $baseUrl = config('services.accountancy_onedb.domain');
            $url = rtrim($baseUrl, '/') . '/customer-service/reminders';

            // Solo hacemos la petición si hay items para evitar el error 400 de Express
            if (count($new_items) > 0) {
                $apiRequest = $client->post($url, [
                    'json' => $data,
                    'timeout' => 60,
                    'http_errors' => false
                ]);
                $response = json_decode($apiRequest->getBody()->getContents());
            } else {
                $response = ['success' => false, 'message' => 'No hay items para enviar'];
            }

            return response()->json([
                'url'       => $url,
                'data'      => $data,
                'response'  => $response,
                'items'     => $items,
                'new_items' => $new_items,
            ]);

        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Log::error("Error en notifications: " . $ex->getMessage());
            dd($this->throwError($ex));
        }
    }

}
