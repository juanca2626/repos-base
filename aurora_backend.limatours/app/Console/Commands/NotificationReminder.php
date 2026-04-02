<?php

namespace App\Console\Commands;

use App\CategoryReminder;
use App\Notification;
use App\ProgressBar;
use App\Reminder;
use App\Service;
use App\TimeReminder;
use App\User;
use App\UserNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class NotificationReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera notificaciones de vuelos y pasajeros pendientes de ingreso x FILE';

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
    public function checkDates($fecini, $fecfin, $field)
    {
        $d_one = strtotime($fecini);
        $d_two = strtotime($fecfin);
        $segundos = $d_two - $d_one;
        $segundos = intval($segundos);

        $units = array(
            'month' => '30 / 24 / 60 / 60',
            'day' => '24 / 60 / 60',
            'hour' => '60 / 60',
            'minute' => '60',
            'second' => '1',
        );

        //calculo el año, mes, dia, hora, minutos, segundos
        foreach ($units as $unit => $val) {
            $_val = explode("/", $val);
            $_segundos = $segundos;

            foreach ($_val as $k => $v) {
                $value = floor($_segundos / (int) $v);
                $_segundos = $value;
            }

            $ret[$unit] = $value;
        }

        return ($field != '' and isset($ret[$field])) ? $ret[$field] : $ret;
    }

    /**
     * PUSH NOTIFICATION
     * @since 2019
     * @params Notification
     * @author KLuizSv
     */
    public function sendPushNotification($notification)
    {
        // Traer los navegadores registrados..
        $user = User::where('code', '=', $notification->user)->first();
        $tokens = UserNotification::where('user_id', '=', $user->id);
        $cantidad = $tokens->count();
        $tokens = $tokens->get();

        $response = [];
        $err = [];

        if ($cantidad > 0) {
            foreach ($tokens as $key => $value) {
                $curl = curl_init();

                $payload = json_encode(array('to' => $value->token, 'notification' => $notification));

                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $payload,
                    CURLOPT_HTTPHEADER => array(
                        "Authorization: key=AAAAfiUDU-g:APA91bGI-fdhLGFb9PrvB0OSVUOV2RzmFoKIPSL10df9U7u5J-K8t4hk-kPq1ZQRlGOENFBoGOHfRoELTVR--h1j4FD_O1eejbE5-TP7S9SM2TNtSbJdebrA-qgxxqFi9qfkQoT8pUPH",
                        'Content-Type: application/json',
                        "cache-control: no-cache"
                    ),
                ));

                $response[] = curl_exec($curl);
                $err[] = curl_error($curl);

                curl_close($curl);
            }
        }

        return response()->json(['message' => $response, 'errors' => $err]);
    }

    public function handle()
    {
        try {
            $client = new \GuzzleHttp\Client();
            $baseUrlExtra = config('services.aurora_extranet.domain');

            /*
            $reminders = Reminder::where('status', '=', 1)
                ->with(['categories', 'categories.times', 'categories.times.type'])
                ->get();
            */

            $reminders = [];

            $all_types = [];
            $files = [];
            $clients = [];
            $times = [];

            //            $request = $client->get($baseUrlExtra.'/api/orders/api.php?method=allSectors');
            //            $response = json_decode($request->getBody()->getContents(), true);
            $response = $client->request(
                'GET',
                config('services.files_onedb.domain')  . 'orders/sectors'
            );
            $response = json_decode($response->getBody()->getContents(), true);

            $categories = [];
            $detalles = [];

            foreach ($response["data"] as $value) {
                if (trim($value['codgru']) != "3") {
                    $categories[trim($value['codgru'])] = trim($value['descri']);
                }
            }

            //             dd($categories); die;

            foreach ($reminders as $reminder) {
                foreach ($reminder->categories as $key => $value) {
                    $time = $value->times()->max('time');
                    $fecfin = date("Y-m-d", strtotime("+" . $time . " days"));

                    foreach ($value->times as $k => $v) {
                        if (!in_array($v->time, (array) @$times[$value->title])) {
                            $times[$reminder->id][$value->id][] = $v->time;
                        }
                    }

                    if ($reminder->content == 'paxs') {
                        $data = [
                            'fecfin' => $fecfin,
                        ];

                        $params = http_build_query($data);
                        $request = $client->get($baseUrlExtra . '/api/customers/api.php?method=searchPaxsPendings&' . $params);
                        $response = json_decode($request->getBody()->getContents(), true);

                        foreach ($response['files'] as $file) {
                            if ($reminder->content !== @$files[$file['NROREF']]) {
                                $files[$file['NROREF']] = ((isset($files[$file['NROREF']]) and $files[$file['NROREF']] != '') ? 'paxs-flights' : $reminder->content);
                                $detalles[$file['NROREF']] = $file;
                            }
                        }
                    }

                    if ($reminder->content == 'flights') {
                        $data = [
                            'fecfin' => $fecfin,
                        ];

                        $params = http_build_query($data);
                        $response = $client->request(
                            'GET',
                            config('services.files_onedb.domain')  . 'flights/pendings?' . $params
                        );
                        $response = json_decode($response->getBody()->getContents(), true);

                        foreach ($response['flights'] as $flight) {
                            if ($reminder->content !== @$files[$flight['NROREF']]) {
                                if (!(isset($files[$flight['NROREF']]) and $files[$flight['NROREF']] != '')) {
                                    $files[$flight['NROREF']] = $reminder->content;
                                    $detalles[$flight['NROREF']] = $flight;
                                } else {
                                    $files[$flight['NROREF']] = 'paxs-flights';
                                }
                            }
                        }
                    }
                }
            }

            // dd($files);
            ksort($times); // Ordenando para que primero muestre las notificaciones más cercanas..
            // dd($times);

            $notifications = [];
            $_items = CategoryReminder::all();
            $items = [];
            $uniques = [];
            $_all_times = TimeReminder::all();
            $all_times = [];
            $__times_cliente = [];
            $_times_notification = [];

            foreach ($_items as $item) {
                $items[$item->id] = $item->title;
            }

            foreach ($_all_times as $time) {
                $all_times[$time->id] = $time->time;
            }

            foreach ($times as $key_reminder => $reminder) {
                foreach ($reminder as $key_category => $category) {
                    foreach ($category as $key => $value) {
                        foreach ($files as $k => $v) {
                            $fecini = date("Y-m-d", strtotime($detalles[$k]['DIAIN']));
                            $fecfin = date("Y-m-d");
                            $client = trim($detalles[$k]['CODCLI']);

                            if (strlen($detalles[$k]['CODSEC']) > 3) {
                                $detalles[$k]['CODSEC'] = substr($detalles[$k]['CODSEC'], 3, 1);
                            }
                            $detalles[$k]['CODSEC'] = trim($detalles[$k]['CODSEC']);

                            $__recordatorio = [];
                            if ($detalles[$k]['REPAFP'] != '') {
                                $recordatorios_cliente = (array) json_decode($detalles[$k]['REPAFP']);
                                $_recordatorio = (array) @$recordatorios_cliente[$key_reminder];
                                $__recordatorio = (array) @$_recordatorio[$key_category];
                            }

                            $__times_cliente[$client] = [];

                            if (is_array($__recordatorio) and count($__recordatorio) > 0) {
                                foreach ($__recordatorio as $__time => $types) {
                                    foreach ($types as $t => $type) {
                                        if ($type == 1) {
                                            $__times_cliente[$client][] = $all_times[$__time];
                                            $all_types[$client][$all_times[$__time]][$type] = $t;
                                        }
                                    }
                                }
                            }

                            //$recordatorios[$key_reminder][$key_category]

                            $days = (int) $this->checkDates($fecfin, $fecini, 'day');
                            $detalles[$k]['DAYS'] = $days;

                            // Si el día restado cumple con la limitante (3, 5, 7, etc días)
                            // Cambiar $days <= $value POR $days == $value..
                            if (((in_array($days, $__times_cliente[$client]) or
                                    count($__times_cliente[$client]) == 0) and $days == $value) and
                                (@$categories[$detalles[$k]['CODSEC']] != '' and
                                    (strpos($categories[$detalles[$k]['CODSEC']], $items[$key_category]) !== FALSE or
                                        strpos($items[$key_category], $categories[$detalles[$k]['CODSEC']]) !== FALSE)
                                )
                            ) {
                                // Enviar notificación
                                $notifications[$client][$value][$v] = $detalles[$k];
                            }

                            // unset($files[$k]);
                            // unset($detalles[$k]);
                        }
                    }
                }
            }

            foreach ($notifications as $key => $value) // client_code => array
            {
                foreach ($value as $k => $v) // 3 => array
                {
                    foreach ($v as $a => $b) // paxs - flights => file
                    {
                        if (isset($all_types[$key][$k])) {
                            foreach ($all_types[$key][$k] as $at => $_all_type) {
                                if ($_all_type == 1) {
                                    // Buscar la ficha de cliente.. para traer el correo o el correo que cesar tiene que definir en otro campo..
                                    $email = @$b['EMAILCLI'];
                                    // $email = 'kluizsv@gmail.com, snb@limatours.com.pe';

                                    if ($email == '' or $email == null) {
                                        $email = @$b['EMAILNOTI'];
                                    }

                                    $email = (!empty($email)) ? trim($email) : '';
                                    $emails = explode(",", $email);

                                    if (count($emails) > 0 and !empty($email)) {
                                        $mail = Mail::to(trim($emails[0]));

                                        foreach ($emails as $key => $value) {
                                            if ($key > 0) {
                                                $mail->cc(trim($value));
                                            }
                                        }

                                        $mail->bcc("kluizsv@gmail.com");

                                        $user = (object) ['nombres' => $key, 'file' => $b];
                                        $mail->send(new \App\Mail\NotificationReminder($a, strtolower($b['IDIOMA']), (array) $user));
                                    }
                                }
                                if ($_all_type == 2) // Whatsapp..
                                {
                                }
                                if ($_all_type == 3) // push Notification..
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
                        } else {
                            // Buscar la ficha de cliente.. para traer el correo o el correo que cesar tiene que definir en otro campo..
                            $email = @$b['EMAILCLI'];
                            // $email = 'kluizsv@gmail.com, snb@limatours.com.pe';

                            if ($email == '' or $email == null) {
                                $email = @$b['EMAILNOTI'];
                            }

                            $email = (!empty($email)) ? trim($email) : '';
                            $emails = explode(",", $email);

                            if (count($emails) > 0 and !empty($email)) {
                                $mail = Mail::to(trim($emails[0]));

                                foreach ($emails as $key => $value) {
                                    if ($key > 0) {
                                        $mail->cc(trim($value));
                                    }
                                }

                                $mail->bcc("kluizsv@gmail.com");

                                $user = (object) ['nombres' => $key, 'file' => $b];
                                $mail->send(new \App\Mail\NotificationReminder($a, strtolower($b['IDIOMA']), (array) $user));
                            }
                        }
                    }
                }
            }

            dd([
                'fecini' => $fecini,
                'fecfin' => $fecfin,
                'times' => $times,
                'times_clients' => $__times_cliente,
                'files' => $files,
                'detalles' => $detalles,
                'notifications' => $notifications,
            ]);
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}
