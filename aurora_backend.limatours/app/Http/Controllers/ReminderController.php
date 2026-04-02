<?php

namespace App\Http\Controllers;

use App\Client;
use App\Mail\MailReminder;
use App\Mail\NotificationReminder;
use App\Notification;
use App\Reminder; use App\CategoryReminder; use App\TimeReminder;
use App\TypeReminder;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReminderController extends Controller
{
    public function notify(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $baseUrlExtra = config('services.aurora_extranet.domain');

        $reminders = Reminder::where('status', '=', 1)
            ->with(['categories', 'categories.times', 'categories.times.type'])
            ->get();

        $all_types = []; $files = []; $clients = []; $times = [];

//        $request = $client->get($baseUrlExtra.'/api/orders/api.php?method=allSectors');
//        $response = json_decode($request->getBody()->getContents(), true);
        $response = $client->request('GET',
            config('services.files_onedb.domain')  . 'orders/sectors');
        $response = json_decode($response->getBody()->getContents(), true);

        $categories = [];

        foreach($response["data"] as $value)
        {
            if(trim($value['codgru']) != "3"){
                $categories[trim($value['codgru'])] = trim($value['descri']);
            }
        }

        // dd($categories);

        foreach($reminders as $reminder)
        {
            foreach($reminder->categories as $key => $value)
            {
                $time = $value->times()->max('time'); $fecfin = date("Y-m-d", strtotime("+" . $time . " days"));

                foreach($value->times as $k => $v)
                {
                    if(!in_array($v->time, (array) @$times[$value->title]))
                    {
                        $times[$reminder->id][$value->id][] = $v->time;
                    }
                }

                // SE DESACTIVA PORQUE SE ENCONTRÓ QUE DESDE EXTRANET DEVUELVE FALSE DESDE HACE COMO 2 AÑOS
//                if($reminder->content == 'paxs')
//                {
//                    $data = [
//                        'fecfin' => $fecfin,
//                    ];
//
//                    $params = http_build_query($data);
//                    $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=searchPaxsPendings&'. $params);
//                    $response = json_decode($request->getBody()->getContents(), true);
//
//                    foreach($response['files'] as $file)
//                    {
//                        if($reminder->content !== @$files[$file['NROREF']])
//                        {
//                            $files[$file['NROREF']][] = ((isset($files[$file['NROREF']]) AND $files[$file['NROREF']] != '') ? 'paxs-flights' : $reminder->content);
//                            $detalles[$file['NROREF']] = $file;
//                        }
//                    }
//                }

                if($reminder->content == 'flights')
                {
                    $data = [
                        'fecfin' => $fecfin,
                    ];

                    $params = http_build_query($data);
//                    $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=searchFlightsPendings&'. $params);
//                    $response = json_decode($request->getBody()->getContents(), true);
                    $response = $client->request('GET',
                        config('services.files_onedb.domain')  . 'flights/pendings?' . $params);
                    $response = json_decode($response->getBody()->getContents(), true);

                    foreach($response['flights'] as $flight)
                    {
                        if($reminder->content !== @$files[$flight['NROREF']])
                        {
                            if(!(isset($files[$flight['NROREF']]) AND $files[$flight['NROREF']] != ''))
                            {
                                $files[$flight['NROREF']][] = $reminder->content;
                                $detalles[$flight['NROREF']] = $flight;
                            }
                            else
                            {
                                $files[$flight['NROREF']][] = 'paxs-flights';
                            }
                        }
                    }
                }
            }
        }

        // dd($files);
        ksort($times); // Ordenando para que primero muestre las notificaciones más cercanas..
        // dd($times);

        $notifications = []; $_items = CategoryReminder::all(); $items = []; $uniques = [];
        $_all_times = TimeReminder::all(); $all_times = [];

        foreach($_items as $item)
        {
            $items[$item->id] = $item->title;
        }

        foreach($_all_times as $time)
        {
            $all_times[$time->id] = $time->time;
        }

        foreach($times as $key_reminder => $reminder)
        {
            foreach($reminder as $key_category => $category)
            {
                foreach($category as $key => $value)
                {
                    foreach($files as $k => $v)
                    {
                        $fecini = date("Y-m-d", strtotime($detalles[$k]['DIAIN']));
                        $fecfin = date("Y-m-d");
                        $client = trim($detalles[$k]['CODCLI']);

                        if(strlen($detalles[$k]['CODSEC']) > 3)
                        {
                            $detalles[$k]['CODSEC'] = substr($detalles[$k]['CODSEC'], 3, 1);
                        }
                        $detalles[$k]['CODSEC'] = trim($detalles[$k]['CODSEC']);

                        $__recordatorio = [];
                        if($detalles[$k]['REPAFP'] != '')
                        {
                            $recordatorios_cliente = (array) json_decode($detalles[$k]['REPAFP']);
                            $_recordatorio = (array) @$recordatorios_cliente[$key_reminder];
                            $__recordatorio = (array) @$_recordatorio[$key_category];
                            // dd($recordatorios_cliente[$key_reminder][$key_category]);
                        }

                        $__times_cliente = [];

                        if(is_array($__recordatorio) AND count($__recordatorio) > 0)
                        {
                            foreach($__recordatorio as $__time => $types)
                            {
                                foreach($types as $t => $type)
                                {
                                    if($type == 1)
                                    {
                                        $__times_cliente[] = $all_times[$__time];
                                        $all_types[$client][] = $t;
                                    }
                                }
                            }
                        }
                        else
                        {
                            $all_types[$client][] = 1;
                        }

                        //$recordatorios[$key_reminder][$key_category]

                        $days = $this->checkDates($fecfin, $fecini, 'day');

                        // Si el día restado cumple con la limitante (3, 5, 7, etc días)
                        // Cambiar $days <= $value POR $days == $value..
                        if(((in_array($days, $__times_cliente) OR count($__times_cliente) == 0) AND $days == $value) AND
                            (@$categories[$detalles[$k]['CODSEC']] != '' AND
                                (strpos($categories[$detalles[$k]['CODSEC']], $items[$key_category]) !== FALSE OR
                                    strpos($items[$key_category], $categories[$detalles[$k]['CODSEC']]) !== FALSE)
                            ) AND
                            (!in_array($detalles[$k]['NROREF'], $uniques))
                        )
                        {
                            // Enviar notificación
                            $uniques[] = $detalles[$k]['NROREF'];
                            foreach($v as $notification)
                            {
                                $notifications[$client][$value][$notification] = $detalles[$k];
                            }

                            unset($files[$k]);
                            unset($detalles[$k]);
                        }
                    }
                }
            }
        }
        // dd($all_types);

        foreach($notifications as $key => $value) // client_code => array
        {
            foreach($value as $k => $v) // 3 => array
            {
                foreach($v as $a => $b) // paxs - flights => file
                {
                    if(isset($all_types[$key]))
                    {
                        foreach($all_types[$key] as $at => $_all_type)
                        {
                            if($_all_type == 1)
                            {
                                // Buscar la ficha de cliente.. para traer el correo o el correo que cesar tiene que definir en otro campo..
                                $email = $b['EMAILCLI'];
                                // $email = 'kluizsv@gmail.com, snb@limatours.com.pe';

                                if($email == '' OR $email == null)
                                {
                                    $email = $b['EMAILNOTI'];
                                }

                                $emails = explode(",", $email);

                                $mail = Mail::to($emails[0]);

                                foreach($emails as $key => $value)
                                {
                                    if($key > 0)
                                    {
                                        $mail->cc($value);
                                    }
                                }

                                $user = (object) ['nombres' => $key, 'file' => $b];
                                $mail->send(new NotificationReminder($a, 'es', (array) $user));
                            }
                            if($_all_type == 2) // Whatsapp..
                            {

                            }
                            if($_all_type == 3) // push Notification..
                            {
                                // Notificación a la especialista..
                                $title = 'Recordatorio de ' . $a;
                                $content = 'Se envío una alerta al cliente para que pueda completar los datos pendientes.';
                                $user = trim($b['CODCLI']);

                                // Enviar un push notification a una especialista..
                                $notification = new Notification;
                                $notification->title = $title;
                                $notification->content = $content;
                                $notification->target = 1;
                                $notification->type = 1;
                                $notification->url = '';
                                $notification->user = $user;
                                $notification->status = 1;
                                $notification->data = '';
                                $notification->created_by = 'ADMIN';
                                $notification->updated_by = 'ADMIN';
                                $notification->module = '';
                                $notification->save();

                                $pushNoti = (object) ['user' => $notification->user, 'title' => $notification->title, 'body' => $notification->content, 'click_action' => '/'];
                                $this->sendPushNotification($pushNoti);
                            }

                            // Notificación a la especialista..
                            $title = 'Recordatorio de ' . $a . ' para el cliente ' . $b['CODCLI'] . '(' . $b['RAZONCLI'] . ')';
                            $content = 'Se envío una alerta al cliente para que pueda completar los datos pendientes.';
                            $user = trim($b['CODVEN']);

                            // Enviar un push notification a una especialista..
                            $notification = new Notification;
                            $notification->title = $title;
                            $notification->content = $content;
                            $notification->target = 1;
                            $notification->type = 1;
                            $notification->url = '';
                            $notification->user = $user;
                            $notification->status = 1;
                            $notification->data = '';
                            $notification->created_by = 'ADMIN';
                            $notification->updated_by = 'ADMIN';
                            $notification->module = '';
                            $notification->save();

                            $pushNoti = (object) ['user' => $notification->user, 'title' => $notification->title, 'body' => $notification->content, 'click_action' => '/'];
                            $this->sendPushNotification($pushNoti);
                        }
                    }
                }
            }
        }

        dd([
            'notifications' => $notifications,
            'times' => $times
        ]);
    }

    public function search_all(Request $request)
    {
        $reminders = Reminder::where('status', '=', 1)
            ->with(['categories', 'categories.times', 'categories.times.type'])
            ->get();
        $types = TypeReminder::all();

        return response()->json(['reminders' => $reminders, 'types' => $types]);
    }

//    public function search_ifx(Request $request)
//    {
//
//        $customer = Client::find($request->input('client_id'));
//
//        if( !($customer) ){
//            return response()->json(["success"=>false, "response"=>"Cliente no existe"]);
//        }
//
//        $data = [
//            'customer' => $customer->code
//        ];
//
//        $params = http_build_query($data);
//
//        $client = new \GuzzleHttp\Client();
//        $baseUrlExtra = config('services.aurora_extranet.domain');
//        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=recordatorios&'. $params);
//        $response = json_decode($request->getBody()->getContents(), true);
//
//        return response()->json(["success"=>true, "response"=>$response]);
//    }

    public function search_ifx(Request $request)
    {
        $customer = Client::find($request->input('client_id'));

        if (!($customer)) {
            return response()->json(["success" => false, "response" => "Cliente no existe"]);
        }

        $baseUrl = config('services.files_onedb.domain');
        $endpoint = rtrim($baseUrl, '/') . '/customers/reminders';

        try {
            $client = new \GuzzleHttp\Client();

            // Guzzle maneja automáticamente la construcción de la URL con los parámetros
            // al pasarlos dentro del arreglo 'query'
            $apiResponse = $client->get($endpoint, [
                'query' => [
                    'customer' => $customer->code
                ],
                'timeout' => 60
            ]);

            $response = json_decode($apiResponse->getBody()->getContents(), true);

            return response()->json(["success" => true, "response" => $response]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al conectar con Express (customers/reminders): " . $e->getMessage());

            return response()->json([
                "success" => false,
                "response" => "Error interno al consultar los recordatorios."
            ], 500);
        }
    }

//    public function update_ifx(Request $request)
//    {
//        $__params = $request->input('params');
//        $type = $request->input('type');
//
//        $customer = Client::find($request->input('client_id'));
//
//        if( !($customer) ){
//            return response()->json(["success"=>false, "response"=>"Cliente no existe"]);
//        }
//
//        $data = [
//            '__params' => $__params,
//            'type' => "recordatorios",
//            'customer' => $customer->code
//        ];
//
//        $params = http_build_query($data);
//
//        $client = new \GuzzleHttp\Client();
//        $baseUrlExtra = config('services.aurora_extranet.domain');
//        $request = $client->get($baseUrlExtra.'/api/customers/api.php?method=update&'. $params);
//        $response = json_decode($request->getBody()->getContents(), true);
//
//        return response()->json($response);
//    }

    public function update_ifx(Request $request)
    {
        $__params = $request->input('params');
        // Tomamos el type del request, o aplicamos 'recordatorios' por defecto como en el legacy
        $type = $request->input('type', 'recordatorios');

        $customer = Client::find($request->input('client_id'));

        if (!($customer)) {
            return response()->json(["success" => false, "response" => "Cliente no existe"]);
        }

        $baseUrl = config('services.files_onedb.domain');
        $endpoint = rtrim($baseUrl, '/') . '/customers/update';

        try {
            $client = new \GuzzleHttp\Client();

            // Hacemos un POST y pasamos las variables dentro del arreglo 'json'
            $apiResponse = $client->post($endpoint, [
                'json' => [
                    'customer' => $customer->code,
                    'type'     => $type,
                    '__params'   => $__params
                ],
                'timeout' => 60
            ]);

            $response = json_decode($apiResponse->getBody()->getContents(), true);

            return response()->json($response);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al conectar con Express (customers/update): " . $e->getMessage());

            return response()->json([
                "success" => false,
                "response" => "Error interno al actualizar la información del cliente."
            ], 500);
        }
    }

}
