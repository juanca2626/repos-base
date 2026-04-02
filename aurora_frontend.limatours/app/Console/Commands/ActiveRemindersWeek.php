<?php

namespace App\Console\Commands;

use App\Notification;
use App\User; use App\Reminder;
use App\Mail\MailReminder;
use App\UserNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class ActiveRemindersWeek extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'active:reminders:week';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar una notificación de recordatorios semanal activo';

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
        $date = date("Y-m-d");
        $reminders = Reminder::where('type', '=', 1)->where('fecini', '>=', $date)->where('fecfin', '<=', $date)->where('status', '=', 1)->get();

        foreach($reminders as $key => $value)
        {
            $users = (array) json_decode($value->users);

            foreach($users as $user)
            {
                $this->sendMail($value->title, $user, $value->content);
            }
        }
    }

    protected function sendMail($title, $user, $content) // Funcion para las notificaciones tambien..
    {
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

        $data = [
            'title' => $title,
            'content' => $content,
            'user' => $user
        ];

        Mail::to('kluizsv@gmail.com')->send(new MailReminder($data));
        $user = (object) User::where('code', '=', $user)->first();
        Mail::to($user->email)->send(new MailReminder($data));

        $pushNoti = (object) ['user' => $notification->user, 'title' => $notification->title, 'body' => $notification->content, 'click_action' => '/'];
        $this->sendPushNotification($pushNoti);
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
