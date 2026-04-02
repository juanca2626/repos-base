<?php

namespace App\Console\Commands;

use App\Mail\MailPassengers;
use App\Notification;
use App\User;
use App\UserNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class PendingPassengers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pending:passengers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar una notificación de pasajeros pendientes por file';

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
        // Código para implementar las notificaciones..
        $wsdl = 'http://genero.limatours.com.pe:8201/WS_TableroFile?wsdl';
        $client = new \SoapClient($wsdl, [
            'encoding' => 'UTF-8',
            'trace' => true
        ]);

        $wsdl_passengers = 'http://genero.limatours.com.pe:8203/WS_RegistroPasajero?wsdl';
        $client_passengers = new \SoapClient($wsdl_passengers, [
            'encoding' => 'UTF-8',
            'trace' => true
        ]);

        $hoy = date("d/m/Y");
        $siete_dias = date("d/m/Y", strtotime("+7 days", strtotime(date("Y-m-d"))));

        $array = array('identi' => 6, 'fecini' => $hoy, 'fecfin' => $siete_dias, 'codigo' => 'E');
        $response = $client->__call("FileSearch", $array);

        $files = [];

        foreach($response['nroref'] as $key => $value)
        {
            if($response['modulo'][$key] == 'E')
            {
                $array = [
                    'modulo' => 1,
                    'nrofile' => $value
                ];
                $_response = (array) $client_passengers->__call("estadocantidadpax", $array);

                $_key = array_search($value, $response['nroref']);
                $files[] = ['file' => $value, 'diain' => $_response['diain'], 'razon' => $_response['razon'], 'codusu' => $response['operad'][$_key], 'nomeje' => $_response['nomeje'], 'paxs' => $_response['canadl'] + $_response['canchd'] + $_response['caninf']];
            }
        }

        foreach($files as $key => $value)
        {
            $this->sendMail($value['file'], $value['diain'], $value['razon'], $value['codusu'], $value['nomeje'], $value['paxs']);
        }
    }

    protected function sendMail($file, $diain, $razon, $user, $nomeje, $paxs) // Funcion para las notificaciones tambien..
    {
        /*
        $notification = new Notification;
        $notification->title = "Pasajeros Pendientes";
        $notification->content = "Hay pasajeros pendientes en el file: " . $file . " - La lista es de " . $paxs . " pasajeros.";
        $notification->target = 1;
        $notification->type = 2;
        $notification->url = 'showPassengers';
        $notification->user = $user;
        $notification->status = 1;
        $notification->data = json_encode(['file' => $file, 'paxs' => $paxs]);
        $notification->created_by = 'ADMIN';
        $notification->updated_by = 'ADMIN';
        $notification->module = 'board';
        $notification->save();

        $data = [
            'nombre_cliente' => $razon,
            'datein' => $diain,
            'nomeje' => $nomeje,
            'file' => $file,
            'paxs' => $paxs
        ];

        Mail::to('kluizsv@gmail.com')->send(new MailPassengers($data));
        Mail::to($notification->user . '@limatours.com.pe')->send(new MailPassengers($data));

        $pushNoti = (object) ['user' => $notification->user, 'title' => $notification->title, 'body' => $notification->content, 'click_action' => URL::to('/board')];
        $this->sendPushNotification($pushNoti);
        */
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

        if($user)
        {
            $tokens = UserNotification::where('user_id', '=', $user->id);
            $cantidad = $tokens->count();
            $tokens = $tokens->get();

            $response = []; $err = [];

            if($cantidad > 0)
            {
                foreach($tokens as $key => $value)
                {
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
        else
        {
            return response()->json(['message' => [], 'errors' => ['El usuario no existe']]);
        }

    }
}
