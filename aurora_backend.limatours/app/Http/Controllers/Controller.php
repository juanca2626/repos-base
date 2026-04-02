<?php

namespace App\Http\Controllers;

use App\ClientSeller;
use App\Country;
use App\Doctype;
use App\Galery;
use App\Http\Stella\StellaService;
use App\Http\Traits\Mailing;
use App\Imports\ImportablesImport;
use App\Imports\TemplatesOpeImport;
use App\LogOrder;
use App\MasiActivityJobLogs;
use App\Notification;
use App\User;
use App\UserNotification;
use DateTime;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\SendSmtpEmail;
use Twilio\Rest\Client as TClient;
use Maatwebsite\Excel\Facades\Excel;
use App\Service;
use App\Hotel;
use App\LoginLog;
use App\MasiConfiguration;
use App\MasterService;
use App\OpeNotification;
use App\OpeNotificationLog;
use App\OpeTemplate;
use App\OpeTemplateContent;
use App\Quote;
use App\QuoteCategory;
use App\QuotePassenger;
use App\QuoteService;
use App\QuoteServicePassenger;
use App\Reservation;
use Carbon\Carbon;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;
use \App\Http\Traits\Package;
use App\ImageHighlight;
use App\Language;
use App\Package as AppPackage;
use App\TypeClass;
use Illuminate\Support\Facades\Cache;
use JD\Cloudder\Facades\Cloudder;

class Controller extends BaseController
{
    public $account_sid = '';
    public $auth_token = '';
    public $twilio_whatsapp_number = '';

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Mailing, Package;

    public function __construct()
    {
        $this->account_sid = config('services.twilio.account_sid');
        $this->auth_token = config('services.twilio.auth_token');
        $this->twilio_whatsapp_number = sprintf('whatsapp:%s', config('services.twilio.number'));
    }

    public function toArray($object = [])
    {
        if (is_object($object) or is_array($object)) {
            $array = [];

            foreach ($object as $key => $value) {
                if (is_object($value) or is_array($value)) {
                    $value = $this->toArray($value);
                }

                $array[$key] = $value;
            }

            return $array;
        } else {
            return $object;
        }
    }

    public function throwError($ex, $addon = [])
    {
        app('sentry')->captureException($ex);

        $response = [
            'file' => $ex->getFile(),
            'line' => $ex->getLine(),
            'detail' => $ex->getMessage(),
            'message' => $ex->getMessage(),
            'type' => 'error',
            'success' => false,
            'process' => false,
            'response' => 'ERR',
        ];

        foreach ($addon as $key => $value) {
            $response[$key] = $value;
        }

        return $response;
    }

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
                $value = floor($_segundos / (int)$v);
                $_segundos = $value;
            }

            $ret[$unit] = $value;
        }

        return ($field != '' and isset($ret[$field])) ? $ret[$field] : $ret;
    }

    public function reminders_frontend_day(Request $request)
    {
        $reminders = ReminderNotification::where('fecini', '<=', date("Y-m-d"))
            ->where('fecfin', '>=', date("Y-m-d"))
            ->where('time', 'like', date("h:i") . '%')
            ->where('type', '=', 1)
            ->where('status', '=', 1)
            ->get();

        foreach ($reminders as $key => $value) {
            $users = json_decode($value->users);

            foreach ($users as $k => $v) {
                // Notificación de Repuesta..
                $response_notification = new Notification;
                $response_notification->title = $value->title;
                $response_notification->content = $value->content;
                $response_notification->target = 1;
                $response_notification->type = 2;
                $response_notification->url = '';
                $response_notification->user = $v;
                $response_notification->status = 1;
                $response_notification->data = [];
                $response_notification->created_by = $value->created_by;
                $response_notification->updated_by = $value->created_by;
                $response_notification->module = '';
                $response_notification->save();

                $pushNoti = (object)[
                    'user' => $response_notification->user,
                    'title' => $response_notification->title,
                    'body' => $response_notification->content,
                    'click_action' => URL::to('/board')
                ];
                parent::sendPushNotification($pushNoti);
            }
        }
    }

    public function reminders_frontend_week(Request $request)
    {
        $reminders = ReminderNotification::where('fecini', '<=', date("Y-m-d"))
            ->where('fecfin', '>=', date("Y-m-d"))
            ->where('time', 'like', date("h:i") . '%')
            ->where('type', '=', 2)
            ->where('status', '=', 1)
            ->get();

        foreach ($reminders as $key => $value) {
            $users = json_decode($value->users);

            foreach ($users as $k => $v) {
                // Notificación de Repuesta..
                $response_notification = new Notification;
                $response_notification->title = $value->title;
                $response_notification->content = $value->content;
                $response_notification->target = 1;
                $response_notification->type = 2;
                $response_notification->url = '';
                $response_notification->user = $v;
                $response_notification->status = 1;
                $response_notification->data = [];
                $response_notification->created_by = $value->created_by;
                $response_notification->updated_by = $value->created_by;
                $response_notification->module = '';
                $response_notification->save();

                $pushNoti = (object)[
                    'user' => $response_notification->user,
                    'title' => $response_notification->title,
                    'body' => $response_notification->content,
                    'click_action' => URL::to('/board')
                ];
                parent::sendPushNotification($pushNoti);
            }
        }
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

    public function sendMailAPI($from, $body_html, $to, $cc, $bcc, $subject, $tag = [])
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey(
            'api-key',
            config('services.sendinblue.api_key_v3')
        );
        $apiInstance = new TransactionalEmailsApi(
            new \GuzzleHttp\Client(),
            $config
        );

        $send = [
            'code' => false,
            'message' => '',
            'message-id' => '',
        ];

        if (!empty($to)) {
            $sendSMTPEmail = new SendSmtpEmail();
            $sendSMTPEmail['subject'] = $subject;
            $sendSMTPEmail['htmlContent'] = $body_html;

            if (!empty($from) && is_array($from)) {
                $sendSMTPEmail['sender'] = array('name' => $from[1], 'email' => $from[0]);
            } else {
                $sendSMTPEmail['sender'] = ['name' => 'Aurora', 'email' => 'no-reply@notify.limatours.com.pe'];
            }

            $sendSMTPEmail['tags'] = $tag;
            $sendSMTPEmail['to'] = $to;

            if (!empty($cc)) {
                $sendSMTPEmail['cc'] = $cc;
            }

            if (!empty($bcc)) {
                $sendSMTPEmail['bcc'] = $bcc;
            }

            $sendSMTPEmail['headers'] = array("X-Mailin-Tag" => '', "Content-Type" => "text/html; charset=iso-8859-1");
            try {
                $sendEmail = $apiInstance->sendTransacEmail($sendSMTPEmail);
                $send = [
                    'code' => true,
                    'message' => 'Email sent successfully.',
                    'message-id' => $sendEmail['messageId'],
                ];
            } catch (\Exception $e) {
                $send = [
                    'code' => false,
                    'message' => $e->getMessage(),
                    'message-id' => '',
                ];
            }
        }
        return $send;
    }

    public function sendMail($body_html, $email_to, $subject, $tag = [])
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey(
            'api-key',
            config('services.sendinblue.masi_api_key_v3')
        );
        $apiInstance = new TransactionalEmailsApi(
            new \GuzzleHttp\Client(),
            $config
        );

        $send = [
            'code' => false,
            'message' => '',
            'message-id' => '',
        ];

        if (!empty($email_to)) {
            $sendSMTPEmail = new SendSmtpEmail();
            $sendSMTPEmail['subject'] = $subject;
            $sendSMTPEmail['htmlContent'] = $body_html;
            $sendSMTPEmail['sender'] = array('name' => 'LITO', 'email' => 'hola@lito.pe');
            $sendSMTPEmail['tags'] = $tag;
            $sendSMTPEmail['to'] = array(
                array('email' => $email_to, 'name' => $email_to)
            );
            /*
            $sendSMTPEmail['bcc'] = array(
                array('email' => 'jgq@limatours.com.pe', 'name' => 'jgq@limatours.com.pe'),
                array('email' => 'lap@limatours.com.pe', 'name' => 'lap@limatours.com.pe')
            );
            */

            $sendSMTPEmail['headers'] = array("X-Mailin-Tag" => '', "Content-Type" => "text/html; charset=iso-8859-1");
            try {
                $sendEmail = $apiInstance->sendTransacEmail($sendSMTPEmail);
                $send = [
                    'code' => true,
                    'message' => 'Email sent successfully.',
                    'message-id' => $sendEmail['messageId'],
                ];
            } catch (\Exception $e) {
                $send = [
                    'code' => false,
                    'message' => $e->getMessage(),
                    'message-id' => '',
                ];
            }
        }
        return $send;
    }

    /**
     * PUSH WSP
     * @since 2021
     * @params Notification
     * @author KLuizSv
     */
    public function sendWhatsApp($phone_number, $data_message)
    {
        $parameters = [];
        $from = $this->twilio_whatsapp_number;
        $recipient = sprintf('whatsapp:+%s', $phone_number);

        try {
            $twilio = new TClient($this->account_sid, $this->auth_token);

            $index = 1;
            foreach ($data_message['parameters'] as $key => $value) {
                $parameters[$index] = $value;
                $index++;
            }

            $message = $twilio->messages->create($recipient, [
                'from' => $from,
                // 'body' => $data_message['body'],
                'contentSid' => $data_message['template'],
                'contentVariables' => json_encode($parameters),
            ]);

            $data = [];
            $data['id'] = $message->sid;
            $data['status'] = $message->status;
            $data['sent'] = true;
            $data['report_error'] = [];
            $data['message'] = 'Sent to ' . $recipient;

            if (isset($message->error_code) and $message->error_code != '' and $message->error_code != null) {
                $data['sent'] = false;
                $data['message'] = 'Error Sending message to ' . $recipient;
            }

            return $data;
        } catch (\Exception $ex) {
            $data['id'] = '';
            $data['sent'] = false;
            $data['report_error'] = $this->throwError($ex, [
                'account_sid' => $this->account_sid,
                'auth_token' => $this->auth_token,
                'parameters' => $parameters,
            ]);
            $data['message'] = 'Error Sending message to ' . $recipient;

            return $data;
        }
    }

    public function getClientId($request_client_id)
    {
        $client_id = null;

        if (Auth::check()) {
            if (Auth::user()->user_type_id == 4) {
                $client_data = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
                $client_id = $client_data["client_id"];
            }
            if (Auth::user()->user_type_id == 3) {
                $client_id = $request_client_id->client_id;
            }
        } else {
            $client_id = $request_client_id->client_id;
        }

        return $client_id;
    }

    // Webhook Sendinblue
    public function webhook_sendinblue(Request $request)
    {
        $all_status = ['soft_bounce', 'hard_bounce', 'invalid_email', 'error', 'delivered', 'unique_opened', 'click'];

        try {
            $email = $request->__get('email');
            $event = $request->__get('event');
            $message_id = $request->__get('message-id');

            $status = @array_search($event, $all_status);

            if ($status >= 0) {
                MasiActivityJobLogs::where('data', 'like', '%' . $message_id . '%')
                    ->where('message', 'like', '%' . $email . '%')
                    ->where(function ($query) use ($status) {
                        $query->orWhere('status_email', '<', $status);
                        $query->orWhereNull('status_email');
                    })->update([
                        'status_email' => $status
                    ]);

                OpeNotificationLog::where('type', '=', 'email')
                    ->where('message_id', 'like', '%' . $message_id . '%')
                    ->where(function ($query) use ($status) {
                        $query->orWhere('status_notification', '<', $status);
                        $query->orWhereNull('status_notification');
                    })->update([
                        'status_notification' => $status
                    ]);

                return response()->json([
                    'type' => 'success',
                    'request' => $request->all(),
                    'status' => $status,
                    'message_id' => $message_id,
                    'event' => $event,
                ]);
            } else {
                return response()->json([
                    'type' => 'error',
                    'request' => $request->all(),
                    'message_id' => $message_id,
                    'event' => $event,
                ]);
            }
        } catch (\Exception $ex) {
            return response()->json(
                $this->throwError($ex)
            );
        }
    }

    // Webhook Twilio..
    public function webhook_twilio(Request $request)
    {
        $all_status = ['', '', '', 'failed', 'sent', 'delivered', 'read'];

        try {
            $message_id = $request->__get('MessageSid');
            $event = $request->__get('MessageStatus');

            $status = @array_search($event, $all_status);

            if ($status >= 0) {
                MasiActivityJobLogs::where('data', 'like', '%' . $message_id . '%')
                    ->where('message', 'like', '%WhatsApp%')
                    ->where(function ($query) use ($status) {
                        $query->orWhere('status_wsp', '<', $status);
                        $query->orWhereNull('status_wsp');
                    })->update([
                        'status_wsp' => $status
                    ]);

                OpeNotificationLog::where('type', '=', 'whatsapp')
                    ->where('message_id', '=', $message_id)
                    ->where(function ($query) use ($status) {
                        $query->orWhere('status_notification', '<', $status);
                        $query->orWhereNull('status_notification');
                    })->update([
                        'status_notification' => $status
                    ]);

                return response()->json([
                    'type' => 'success',
                    'request' => $request->all(),
                    'status' => $status,
                    'message_id' => $message_id,
                    'event' => $event,
                ]);
            } else {
                return response()->json([
                    'type' => 'error',
                    'request' => $request->all(),
                    'message_id' => $message_id,
                    'event' => $event,
                ]);
            }
        } catch (\Exception $ex) {
            return response()->json(
                $this->throwError($ex)
            );
        }
    }

    public function checkDateFormat($date1, $date2)
    {
        $date1 = strtotime($date1);
        $date2 = strtotime($date2);

        if ($date1 > $date2) {
            $tmp = $date1;
            $date1 = $date2;
            $date2 = $tmp;
            unset($tmp);
            $sign = -1;
        } else {
            $sign = 1;
        }
        if ($date1 == $date2) {
            return 0;
        }
        $days = 0;
        $working_days = array(1, 2, 3, 4, 5); // Monday-->Friday
        $working_hours = array(9, 18); // from 9:00(am) to 18
        $current_date = $date1;
        $beg_h = floor($working_hours[0]);
        $beg_m = 0;
        $end_h = floor($working_hours[1]);
        $end_m = 0;
        $seconds = 0;
        // setup the very next first working timestamp
        if (!in_array(date('w', $current_date), $working_days)) {
            // the current day is not a working day
            // the current timestamp is set at the begining of the working day
            $current_date = mktime(
                $beg_h,
                $beg_m,
                0,
                date('n', $current_date),
                date('j', $current_date),
                date('Y', $current_date)
            );
            // search for the next working day
            while (!in_array(date('w', $current_date), $working_days)) {
                $current_date += 24 * 3600;
            } // next day
        } else {
            // check if the current timestamp is inside working hours
            $date0 = mktime(
                $beg_h,
                $beg_m,
                0,
                date('n', $current_date),
                date('j', $current_date),
                date('Y', $current_date)
            );
            // it's before working hours, let's update it
            if ($current_date < $date0) {
                $current_date = $date0;
            }
            $date3 = mktime(
                $end_h,
                $end_m,
                0,
                date('n', $current_date),
                date('j', $current_date),
                date('Y', $current_date)
            );

            if ($date3 < $current_date) {
                // outch ! it's after working hours, let's find the next working day
                $current_date += 24 * 3600; // the day after // and set timestamp as the begining of the working day
                $current_date = mktime(
                    $beg_h,
                    $beg_m,
                    0,
                    date('n', $current_date),
                    date('j', $current_date),
                    date('Y', $current_date)
                );

                while (!in_array(date('w', $current_date), $working_days)) {
                    $current_date += 24 * 3600; // next day
                }
            }

            // so, $current_date is now the first working timestamp available...
            // calculate the number of seconds from current timestamp to the end of the working day
            $date0 = mktime(
                $end_h,
                $end_m,
                0,
                date('n', $current_date),
                date('j', $current_date),
                date('Y', $current_date)
            );

            if (date("Y-m-d", $date2) == date("Y-m-d", $current_date)) {
                $date0 = $date2;
            }

            $seconds = $date0 - $current_date;
            // printf("\nFrom %s To %s : %d hours\n",date('d/m/y H:i',$date1),date('d/m/y H:i',$date0),$seconds/3600);
            // calculate the number of days from the current day to the end day
            $date3 = mktime($beg_h, $beg_m, 0, date('n', $date2), date('j', $date2), date('Y', $date2));

            while ($current_date < $date3) {
                $current_date += 24 * 3600; // next day
                if (in_array(date('w', $current_date), $working_days)) {
                    $days++;
                }
                // it's a working day
            }
        }

        if ($days > 0) {
            $days--;
        } //because we've allready count the first day (in $seconds)
        // printf("\nFrom %s To %s : %d working days\n",date('d/m/y H:i',$date1),date('d/m/y H:i',$date3),$days);
        // check if end's timestamp is inside working hours
        $date0 = mktime($end_h, $end_m, 0, date('n', $date2), date('j', $date2), date('Y', $date2));

        if ($date2 < $date0) {
            // it's before, so nothing more !
        } else {
            $date3 = mktime($end_h, $end_m, 0, date('n', $date2), date('j', $date2), date('Y', $date2));
            if ($date2 >= $date3) {
                $date2 = $date3;
            }
            $date3 = mktime(
                $end_h,
                $end_m,
                0,
                date('n', $current_date),
                date('j', $current_date),
                date('Y', $current_date)
            );
            // calculate the number of seconds from current timestamp to the final timestamp
            $tmp = $date2 - $date3;
            $seconds += $tmp;
            // printf("\nFrom %s To %s : %d hours\n",date('d/m/y H:i',$date2),date('d/m/y H:i',$date3),$tmp/3600);
        }

        // calculate the working days in seconds
        $seconds += 3600 * ($working_hours[1] - $working_hours[0]) * $days;
        // printf("\nFrom %s To %s : %d hours\n",date('d/m/y H:i',$date1),date('d/m/y H:i',$date2),$seconds/3600);

        $horas = ceil($sign * $seconds / 3600); // to get hours

        $ret['day'] = ceil($horas / 8);
        $ret['hour'] = $horas;
        $ret['days'] = $days;
        $ret['date0'] = date("Y-m-d H:i:s", $date0);
        $ret['date1'] = date("Y-m-d H:i:s", $current_date);
        $ret['date2'] = date("Y-m-d H:i:s", $date2);
        if (isset($date3)) {
            $ret['date3'] = date("Y-m-d H:i:s", @$date3);
        }

        return $ret;
    }

    public function verifyQuotes($orders, $lang, $flag_report = 0)
    {
        try {
            $chkpro_desc = [
                'Ninguno',
                'Programación Cliente',
                'Programación LITO',
                'Programación LITO sin cambios',
                'Revisión de cotización hecha por cliente',
                'Sólo hoteles o servicios sueltos',
                'Programación exclusiva CLIENTE sin cambios'
            ];

            foreach ($orders as $key => $value) {
                $value = (array)$value;
                $_nroref = @$value['nroref'];

                $_codsec = trim($value['codsec']);
                $codsec = (strlen($_codsec) == 1) ? $_codsec : $_codsec[2];

                $_etiquetas = array();
                $etiquetas = json_decode($value['label']);

                if (count($etiquetas) > 0) {
                    foreach ($etiquetas as $a => $b) {
                        $_etiquetas[] = array(
                            'id' => $b->id,
                            'etiqueta' => $b->nombre,
                            'colbac' => $b->colbac,
                            'coltex' => $b->coltex
                        );
                    }
                }

                $orders[$key]['etiquetas'] = $_etiquetas;

                if (trim($value['fecres']) == '') {
                    $fecres = date("Y-m-d H:i:s");
                } else {
                    $fecres = trim($value['fecres']) . ' ' . trim($value['horres']);
                }

                $fecrec = trim($value['fecrec']) . ' ' . trim($value['horrec']);

                $dias = $this->checkDateFormat($fecrec, $fecres);
                $orders[$key]['days'] = $dias['days'];
                $orders[$key]['response_days'] = $dias;
                $orders[$key]['horas'] = ($dias['hour'] > 0) ? $dias['hour'] : 0;
                $orders[$key]['dias'] = ($dias['day'] > 0) ? $dias['day'] : 0;

                // Cambios en la validación del tiempo..
                $alerta = '';
                $_times = array(0, 12, 72, 0, 48, 120); // Tiempo por sectores..
                //
                $limite = $_times[$codsec];
                $horas = ($orders[$key]['horas'] > 0) ? $orders[$key]['horas'] : 0;

                if ($limite > 0) {
                    if ($horas <= $limite) {
                        $alerta = 'success';
                    } else {
                        if ($orders[$key]['estado'] == 'OK') {
                            $alerta = 'warning';
                        } else {
                            $alerta = 'danger';
                        }
                    }
                }

                $orders[$key]['horas'] = $horas;
                $orders[$key]['class'] = $alerta;

                $orders[$key]['chkpro_desc'] = '';

                if ($orders[$key]['chkpro'] > -1) {
                    $orders[$key]['chkpro_desc'] = $chkpro_desc[$orders[$key]['chkpro']];
                }

                // $orders[$key]['nompaq'] = OrderModel::searchPaq($orders[$key]['NROREF']);

                if ($value['nroref_identi'] == 'B') // Aurora 2
                {
                    $quote = DB::table('quotes')->where('id', '=', $_nroref)->first();

                    if ($quote == null || $quote == '') {
                        $log = DB::table('quote_logs')
                            ->where('object_id', '=', $_nroref)
                            ->where(function ($q) {
                                $q->orWhere('type', '=', 'editing_quote');
                            })
                            ->first();

                        if ($log != '' and $log != null) {
                            $orders[$key]['quote_log'] = $log;
                            $_nroref = $log->quote_id;
                        }

                        $quote = DB::table('quotes')->where('id', '=', $_nroref)->first();
                    }

                    $orders[$key]['nroref_nuevo'] = $_nroref;
                    $orders[$key]['quote'] = $quote;

                    if ($quote != '' and $quote != null) {
                        if ($quote->operation == 'passengers') {
                            $quote_people = DB::table('quote_people')
                                ->where('quote_id', '=', $quote->id)
                                ->first();

                            $orders[$key]['quote_people'] = $quote_people;

                            $client = new \GuzzleHttp\Client();
                            $baseUrlExtra = 'https://backend.limatours.com.pe';
                            // $baseUrlExtra = 'http://127.0.0.1:8000/';
                            $request = $client->get($baseUrlExtra . '/quote_passengers_frontend?quote_id=' . $quote->id . '&lang=' . $lang);
                            $response = (array)json_decode($request->getBody()->getContents(), true);
                            $orders[$key]['DATA_AURORA_2'] = $response;

                            $mount_total = null;
                            $count = 0;
                            $items = ['', 'SGL', 'DBL', 'TPL'];

                            if (isset($response['data'][0]['passengers'])) {
                                foreach ($response['data'][0]['passengers'] as $k => $v) {
                                    $_name = explode("-", $v['first_name']);
                                    $_key = array_search(trim(last($_name)), $items);

                                    if ($_key > 0) {
                                        $count += $_key;
                                    } else {
                                        $count += 1;
                                    }

                                    if ($mount_total == null) {
                                        $mount_total = $v['total'];
                                    } else {
                                        $mount_total += $v['total'];
                                    }
                                }
                            }

                            if (((float)$quote_people->adults + (float)$quote_people->child) > $count) {
                                if ($mount_total != null and $mount_total > 0) {
                                    $mount_total = $mount_total * $quote_people->adults;
                                }
                            }
                        }

                        if ($quote->operation == 'ranges') {
                            $client = new \GuzzleHttp\Client();
                            $baseUrlExtra = 'https://backend.limatours.com.pe';
                            // $baseUrlExtra = 'http://127.0.0.1:8000/';
                            $request = $client->get($baseUrlExtra . '/quote_ranges_frontend?quote_id=' . $quote->id . '&lang=' . $lang);
                            $response = (array)json_decode($request->getBody()->getContents(), true);
                            $orders[$key]['DATA_AURORA_2'] = $response;

                            $mount_total = null;

                            if (isset($response['ranges'])) {
                                foreach ($response['ranges'] as $k => $v) {
                                    if ($mount_total == null) {
                                        $mount_total = $v['promedio'];
                                    } else {
                                        if ($v['promedio'] <= $mount_total and $v['promedio'] > 0) {
                                            $mount_total = $v['promedio'];
                                        }
                                    }
                                }
                            }
                        }

                        $orders[$key]['price_estimated'] = number_format($mount_total, 2, ".", "");
                        $orders[$key]['fectravel'] = $quote->date_in;
                        $orders[$key]['fectravel_tca'] = $quote->estimated_travel_date;
                        // $orders[$key]['FECTRAVEL_ESTIMATED'] = ($quote->estimated_travel_date != '' AND $quote->estimated_travel_date != NULL) ? $quote->estimated_travel_date : $quote->date_in;
                    }

                    // $orders[$key]['price_estimated'] = 0;
                    // $orders[$key]['fectravel'] = '';
                    // $orders[$key]['fectravel_tca'] = '';

                    if (@$value['nompaq'] == '' or @$value['nompaq'] == null) {
                        $quote_log = DB::table('quote_logs')
                            ->where('quote_id', '=', $_nroref)
                            ->where('type', '=', 'from_package')
                            ->first();

                        $package = DB::table('package_translations')
                            ->where('package_id', '=', @$quote_log->object_id)
                            ->first();

                        $orders[$key]['nompaq'] = @$package->name;
                        $orders[$key]['data_log'] = $quote_log;
                        $orders[$key]['data_package'] = $package;
                    }
                }

                if (strlen(trim($value['horrec'])) == 3) {
                    $value['horrec'] = trim($value['horrec']) . '00';
                }

                $orders[$key]['horrec'] = date("H:i", strtotime($value['horrec']));

                if ($value['horres'] != '' and $value['horres'] != null) {
                    $orders[$key]['horres'] = date("H:i", strtotime($value['horres']));
                }
            }

            return $orders;
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function migrate_orders(Request $request)
    {
        set_time_limit(0);

        try {
            $stellaService = new stellaService();
            $response = [];
            $teams = $this->toArray($stellaService->search_teams());
            $fecini = '2021-10-01';
            $fecfin = '2022-10-02';

            $date1 = new DateTime($fecini);
            $date2 = new DateTime($fecfin);
            $diff = $date1->diff($date2);

            $months = 0;

            if ($diff->y > 0) {
                $months = 12 * $diff->y;
            }

            if ($diff->m > 0) {
                $months += $diff->m;
            }

            // $month = (int) date("m", strtotime($fecfin)); $year = (int) date("Y", strtotime($fecfin));
            $max_process = 5;

            foreach ($teams as $key => $value) {
                $team = $value['team'];
                $month_count = 0;
                $month_initial = (int)date("m", strtotime($fecini));
                $year_initial = (int)date("Y", strtotime($fecini));

                do {
                    if ($max_process > 0) {
                        $page = LogOrder::where('team', '=', $team)->where('month', '=', $month_initial)
                            ->where('year', '=', $year_initial)->max('page');
                        $page += 1;

                        $date_in = '01/' . str_pad($month_initial, 2, '0') . '/' . $year_initial;
                        $date_out = date(
                            't/m/Y',
                            strtotime($year_initial . '-' . str_pad($month_initial, 2, 0) . '-01')
                        );

                        $data = [
                            'executive' => 'TODOS',
                            'type' => 'E',
                            'region' => '',
                            'sector' => '', // tipo de producto..
                            'state' => 'ALL',
                            'fecini' => $date_in,
                            'fecfin' => $date_out,
                            'nroped' => '',
                            'seguimiento' => '',
                            'team' => $team,
                            'limit' => $page,
                            'kam' => ''
                        ];

                        $orders = $this->verifyQuotes($this->toArray($stellaService->search_orders($data)), 'en');

                        if (count($orders) > 0) {
                            $log = new LogOrder;
                            $log->team = $team;
                            $log->month = $month_initial;
                            $log->year = $year_initial;
                            $log->page = $page;
                            $log->data = json_encode($orders);
                            $log->save();

                            $max_process -= 1;
                        }

                        if (count($orders) == 0) {
                            if ($month_initial == 12) {
                                $month_initial = 1;
                                $year_initial += 1;
                            } else {
                                $month_initial += 1;
                            }

                            $log = "No hay pedidos..";
                        }

                        $response[$team] = [
                            'page' => $page,
                            'orders' => $orders,
                            'log' => $log,
                            'data' => $data,
                            'max_process' => $max_process,
                        ];
                    } else {
                        break 2;
                    }
                } while ($month_count < $months);
            }

            dd($response);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function export_services_mapi(Request $request) {}

    public function user_access_report(Request $request)
    {
        try {
            $date = ($request->has('date')) ? $request->__get('date') : date("d/m/Y");
            $date = explode("/", $date);
            $date = $date[2] . '-' . $date[1] . '-' . $date[0];

            $logs = LoginLog::with(['user'])->where('date', '=', $date)->get();

            /*
            return response()->json([
                'logs' => $logs,
            ]);
            */

            return Excel::download(new \App\Exports\UserAccessExport(['logs' => $logs]), 'user_access.xlsx');
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function load_excel(Request $request)
    {
        try {
            $file = $request->file;
            $array = (new ImportablesImport)->toArray($file);
            $array = $array[0];
            $headers = $array[0];
            $data = array_slice($array, 1);
            $items = [];

            foreach ($data as $key => $value) {
                if (!empty($value[0]) and !empty($value[1])) {
                    $date = (empty($value[0])) ? null : $value[0];

                    if (!empty($date)) {
                        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
                    }

                    $item = ['date' => $date, 'service' => $value[1], 'file' => $value[2]];
                    $items[] = $item;
                }
            }

            return response()->json([
                'items' => $items,
                'headers' => $headers,
                'type' => 'success'
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function import_templates_ope(Request $request)
    {
        set_time_limit(0);

        try {
            /*
            if(app('config.env') != 'production')
            {
                OpeNotificationLog::whereNotNull('created_at')->delete();
                OpeNotification::whereNotNull('created_at')->delete();
                OpeTemplateContent::whereNotNull('created_at')->delete();
                OpeTemplate::whereNotNull('created_at')->delete();
            }
            */

            $templates = Excel::import(new TemplatesOpeImport, $request->file('file'));

            return response()->json([
                'type' => 'success',
                'message' => 'Importación de plantillas correcto.',
                'templates' => $templates,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function save_template_ope(Request $request)
    {
        try {
            $_template = (object)$request->__get('template');
            $languages = (array)$request->__get('languages');

            if (@$_template->id > 0) {
                $template = OpeTemplate::where('id', '=', $_template->id)->first();
            } else {
                $template = new OpeTemplate;
            }

            $template->name = $_template->name;
            $template->save();

            foreach ($languages as $key => $value) {
                $_content_email = @$_template->content_email[$value['language_id']];
                $_content_wsp = @$_template->content_wsp[$value['language_id']];

                $content = OpeTemplateContent::where('language_id', '=', $value['language_id'])
                    ->where('ope_template_id', '=', $template->id)->first();

                if (!$content) {
                    $content = new OpeTemplateContent;
                    $content->language_id = $value['language_id'];
                    $content->ope_template_id = $template->id;
                }

                $content->content_email = (!empty($_content_email)) ? $_content_email : '';
                $content->content_wsp = (!empty($_content_wsp)) ? $_content_wsp : '';
                $content->save();
            }

            return response()->json([
                'type' => 'success',
                'template' => $template,
                'message' => 'Template saved correctly',
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function search_templates_ope(Request $request)
    {
        try {
            $templates = OpeTemplate::with(['contents', 'content_extension'])->get();

            return response()->json([
                'type' => 'success',
                'templates' => $templates,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function send_notification_ope(Request $request)
    {
        try {
            $template_id = $request->__get('template_id');
            $items = $request->__get('items');
            $tags = $request->__get('tags') ?? [];

            dd($items);

            // Proccess to notification..
            foreach ($items as $key => $value) {
                $notification = new OpeNotification;
                $notification->file = $value['file'];
                $notification->service = $value['service'];

                if (!empty($value['date'])) {
                    $notification->date = $value['date'];
                }

                $notification->tags = json_encode($tags);
                $notification->template_id = $template_id;
                $notification->status = 0; // Pendiente de procesar
                $notification->save();
            }

            return response()->json([
                'type' => 'success',
                'message' => 'Las notificaciones fueron agregadas a la cola de envíos.',
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function generate_notification_ope($time_actual, $service, $file)
    {
        $configuration = MasiConfiguration::where('status', '=', 1)->first();
        $time = '18:00';
        $date = '';
        $flag_schedule = 0;
        $flag_time = false;

        if (!empty(@$service['service']) && !empty(@$service['date']) && !empty(@$service['time'])) {
            $code = $service['service'];
            $date = $service['date'];
            $new_time = $service['time'];
            $template_id = 26; // Actualización de Horario..

            $count = MasterService::where('description', 'like', 'TRF%')
                ->where('status', '=', 1)
                ->where('code', $code)
                ->count();

            if ($count > 0) {
                if (!empty(@$configuration->time)) {
                    $flag_schedule = $configuration->flag_schedule;
                    $date = $configuration->date;

                    if ($flag_schedule == 1 || (!empty($date) && $date <= date("Y-m-d") && $flag_schedule == 0)) {
                        $time = $configuration->time;
                    }
                }

                if ($time_actual >= (date('Y-m-d ') . $time)) {
                    $flag_time = true;
                }

                if ($flag_time) // Se notifica..
                {
                    $tags = [
                        '{time}' => $new_time,
                    ];

                    // Proccess to notification..
                    $notification = new OpeNotification;
                    $notification->file = $file;
                    $notification->service = $code;
                    $notification->date = $date;

                    $notification->tags = json_encode($tags);
                    $notification->template_id = $template_id;
                    $notification->status = 0; // Pendiente de procesar
                    $notification->save();
                }
            }
        }

        return [
            'success' => true,
            'flag_time' => (int) $flag_time,
            'service' => $service,
            'file' => $file,
        ];
    }

    public function send_notification_ope_schedule(Request $request)
    {
        try {
            $services = $request->__get('services');
            $file = $request->__get('file');
            $response = [];
            $time_actual = date("Y-m-d H:i");

            if (empty($services)) {
                $service = [
                    'service' => $request->__get('service'),
                    'date' => $request->__get('date'),
                    'time' => $request->__get('time'),
                ];

                $response = $this->generate_notification_ope($time_actual, $service, $file);
            } else {
                foreach ($services as $service) {
                    $service = $this->toArray($service);
                    $response[] = $this->generate_notification_ope($time_actual, $service, $file);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $response,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function search_logs_ope(Request $request)
    {
        try {
            $filter = (object)$request->__get('filter');
            $take = $request->__get('take');
            $page = $request->__get('page');

            $logs = OpeNotificationLog::with(['notification']);

            if (!empty(@$filter->file)) {
                $logs = $logs->whereHas('notification', function ($query) use ($filter) {
                    $query->where('file', '=', $filter->file);
                });
            }

            if (!empty(@$filter->date)) {
                $logs = $logs->whereHas('notification', function ($query) use ($filter) {
                    $query->where('created_at', 'like', $filter->date . '%');
                });
            }

            $logs = $logs->where('status', '=', 1)
                ->orderBy('updated_at', 'DESC');

            $all_logs = $logs->count();

            if ($take > 0) {
                $logs = $logs->take($take)->skip($take * $page);
                $pages = ceil($all_logs / $take);
            }

            $logs = $logs->get();

            $all_status = [
                'whatsapp' => [
                    '',
                    '',
                    '',
                    'Fallido',
                    'Enviado',
                    'Recibido',
                    'Leído'
                ],
                'email' => [
                    'No Entregado',
                    'Rebotados',
                    'Email inválido',
                    'Fallido',
                    'Entregado',
                    'Leído',
                    'Click realizado'
                ]
            ];

            return response()->json([
                'all_status' => $all_status,
                'pages' => $pages,
                'logs' => $logs,
                'type' => 'success',
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function show_itinerary(Request $request, $id, $update = 0)
    {
        $data = [
            'success' => false,
            'data' => [],
        ];

        // $services = Service::getSevicesOriginDestinationInHotel();
        // dd($services->where('id', 2194)->first());


        DB::transaction(function () use ($update, $id, &$data) {
            $update = (int) $update;
            $reservation = Reservation::getReservations([
                'reservation_id' => $id,
                'process_aurora3' => 1
            ], true);

            // Actualizando los estados..
            /*
            if ($update === 1) {
                if(isset($reservation->reservationsFlight))
                {
                    $reservation->reservationsFlight->each(function ($flight) {
                        $flight->process_aurora3 = 0;
                        $flight->save();
                        return $flight;
                    });
                }

                if(isset($reservation->reservationsHotel))
                {
                    $reservation->reservationsHotel->each(function ($hotel) {
                        $hotel->process_aurora3 = 0;
                        $hotel->save();
                        return $hotel;
                    });
                }

                if(isset($reservation->reservationsService))
                {
                    $reservation->reservationsService->each(function ($service) {
                        $service->process_aurora3 = 0;
                        $service->save();
                        return $service;
                    });
                }
            }
            */

            if ($reservation) {

                $reservation->file_agency_code = null;

                if ($reservation->entity == 'Quote') {
                    $quote = Quote::where('id', '=', $reservation->object_id)->first();
                    $reservation->file_agency_code = @$quote->reference_code;

                    $quoteCategory = QuoteCategory::where('quote_id', '=', $reservation->object_id)->where('type_class_id', '=', $reservation->type_class_id)->first();
                    $quoteServices = QuoteService::with(['service_rooms', 'service_rate'])->where('quote_category_id', '=', $quoteCategory->id)->get();
                    $quotePassengers = QuotePassenger::where('quote_id', '=', $reservation->object_id)->get();

                    foreach ($quoteServices as $quoteService) {
                        $quoteService->process = false;
                    }
                    $quotePassengersForReservations = [];
                    foreach ($quotePassengers as $index => $passenger) {
                        $passenger->sequence_number = $index + 1;
                        $quotePassengersForReservations[$passenger->id] = $passenger;
                    }


                    foreach ($reservation->reservationsHotel as $index_h => $reservations_hotel) {
                        foreach ($reservations_hotel->reservationsHotelRooms as $index_r =>  $reservations_hotel_rooms) {
                            $entro[$reservations_hotel_rooms->id] = [];
                            foreach ($quoteServices as $index => $quoteService) {
                                $date_in = Carbon::createFromFormat('d/m/Y', $quoteService->date_in)->format('Y-m-d');
                                $date_out = Carbon::createFromFormat('d/m/Y', $quoteService->date_out)->format('Y-m-d');

                                if (
                                    $quoteService->process == false and $quoteService->type == 'hotel' and
                                    ($reservations_hotel_rooms->rates_plans_room_id == $quoteService->service_rooms[0]->rate_plan_room_id) and
                                    ($reservations_hotel_rooms->check_in == $date_in) and
                                    ($reservations_hotel_rooms->check_out == $date_out)
                                ) {

                                    $quoteServicePassengers = QuoteServicePassenger::where('quote_service_id', '=', $quoteService->id)->get();

                                    $reservationPassengers = [];
                                    foreach ($quoteServicePassengers as $quoteServicePassenger) {
                                        $passengerSquenceNumber = $quotePassengersForReservations[$quoteServicePassenger->quote_passenger_id]->sequence_number;
                                        foreach ($reservation->reservationsPassenger as $passenger) {
                                            if ($passenger->sequence_number == $passengerSquenceNumber) {
                                                array_push($reservationPassengers, [
                                                    'reservation_passenger_id' => $passenger->id,
                                                    'sequence_number' => $passenger->sequence_number,
                                                ]);
                                            }
                                        }
                                    }
                                    $reservations_hotel_rooms->passengers = $reservationPassengers;
                                    $quoteService->process = true;
                                    break;
                                }
                            }
                        }
                    }

                    foreach ($reservation->reservationsService as $index_h => $reservations_service) {

                        foreach ($quoteServices as $index => $quoteService) {
                            $date_in = Carbon::createFromFormat('d/m/Y', $quoteService->date_in)->format('Y-m-d');

                            if (
                                $quoteService->process == false and $quoteService->type == 'service' and
                                ($reservations_service->service_rate_id == $quoteService->service_rate->service_rate_id) and
                                ($reservations_service->date == $date_in)
                            ) {

                                $quoteServicePassengers = QuoteServicePassenger::where('quote_service_id', '=', $quoteService->id)->get();

                                $reservationPassengers = [];
                                foreach ($quoteServicePassengers as $quoteServicePassenger) {
                                    $passengerSquenceNumber = $quotePassengersForReservations[$quoteServicePassenger->quote_passenger_id]->sequence_number;
                                    foreach ($reservation->reservationsPassenger as $passenger) {
                                        if ($passenger->sequence_number == $passengerSquenceNumber) {
                                            array_push($reservationPassengers, [
                                                'reservation_passenger_id' => $passenger->id,
                                                'sequence_number' => $passenger->sequence_number,
                                            ]);
                                        }
                                    }
                                }
                                $reservations_service->passengers = $reservationPassengers;
                                $quoteService->process = true;
                                break;
                            }
                        }
                    }

                    $categories = QuoteCategory::with(['type_class' => function ($query) {
                        $query->select('id');
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['object_id', 'value']);
                                $query->where('type', 'typeclass');
                                $query->where('language_id', 1);
                            }
                        ]);
                    }])->select('type_class_id')->where('quote_id', '=', $reservation->object_id)->get();
                    $reservation->categories = $categories;
                    $reservation->markup_quote = $quote->markup;
                } else {  // tenemos que hacer la asignacion automatica de pasajeros.

                    foreach ($reservation->reservationsService as $reservations_service) {

                        $passenger_add = [];
                        if (!isset($reservations_service->file_associated_passenger) or !$reservations_service->file_associated_passenger) {   // no tiene asignado pasajeros el servicio, se tiene que hacer automatico

                            $reservationPassengers = [];
                            $adults = $reservations_service->adult_num;
                            $children = $reservations_service->child_num;

                            for ($i = 0; $i < $adults; $i++) {

                                foreach ($reservation->reservationsPassenger as $index_passgenger =>  $passenger) {

                                    if (!in_array($passenger->id, $passenger_add) and $passenger->type = 'ADL') {
                                        array_push($reservationPassengers, [
                                            'reservation_passenger_id' => $passenger->id,
                                            'sequence_number' => ($index_passgenger + 1),
                                        ]);
                                        array_push($passenger_add, $passenger->id);
                                        break;
                                    }
                                }
                            }

                            for ($i = 0; $i < $children; $i++) {

                                foreach ($reservation->reservationsPassenger as $index_passgenger =>  $passenger) {

                                    if (!in_array($passenger->id, $passenger_add) and $passenger->type = 'CHD') {
                                        array_push($reservationPassengers, [
                                            'reservation_passenger_id' => $passenger->id,
                                            'sequence_number' => ($index_passgenger + 1),
                                        ]);
                                    }
                                }
                            }

                            $reservations_service->passengers = $reservationPassengers;
                        } else {

                            $reservationPassengers = [];
                            $assigned_passengers = json_decode($reservations_service->file_associated_passenger);

                            if ($assigned_passengers and is_array($assigned_passengers)) {
                                foreach ($assigned_passengers as $associated) {
                                    foreach ($reservation->reservationsPassenger as $index_passgenger =>  $passenger) {

                                        if ($associated->sequence_number == $passenger['sequence_number']) {
                                            array_push($reservationPassengers, [
                                                'reservation_passenger_id' => $passenger->id,
                                                'sequence_number' => $passenger['sequence_number'],
                                            ]);;
                                            break;
                                        }
                                    }
                                }
                                $reservations_service->passengers = $reservationPassengers;
                            }
                        }
                    }

                    // $passenger_assign = $this->getReservationHotelAsign($reservation->reservationsHotel);
                    foreach ($reservation->reservationsHotel as $index_h => $reservations_hotel) {

                        if (!isset($reservations_hotel->files_ms_parameters) or !$reservations_hotel->files_ms_parameters) {
                            $passenger_add = [];
                            foreach ($reservations_hotel->reservationsHotelRooms as $index_r =>  $reservations_hotel_rooms) {
                                // asigna automaticamente
                                $reservationPassengers = [];
                                $occupation = $reservations_hotel_rooms->room_type->occupation;
                                $adults = $reservations_hotel_rooms->adult_num;
                                $children = $reservations_hotel_rooms->child_num;


                                for ($i = 0; $i < $adults; $i++) {

                                    foreach ($reservation->reservationsPassenger as $index_passgenger =>  $passenger) {

                                        if (!in_array($passenger->id, $passenger_add) and $passenger->type = 'ADL') {
                                            array_push($reservationPassengers, [
                                                'reservation_passenger_id' => $passenger->id,
                                                'sequence_number' => ($index_passgenger + 1),
                                            ]);
                                            array_push($passenger_add, $passenger->id);
                                            break;
                                        }
                                    }
                                }

                                for ($i = 0; $i < $children; $i++) {

                                    foreach ($reservation->reservationsPassenger as $index_passgenger =>  $passenger) {

                                        if (!in_array($passenger->id, $passenger_add) and $passenger->type = 'CHD') {
                                            array_push($reservationPassengers, [
                                                'reservation_passenger_id' => $passenger->id,
                                                'sequence_number' => ($index_passgenger + 1),
                                            ]);
                                        }
                                    }
                                }

                                $reservations_hotel_rooms->passengers = $reservationPassengers;
                            }
                        } else {
                            // hay un asignacion que viene de files_ms

                            foreach ($reservations_hotel->reservationsHotelRooms as $index_r =>  $reservations_hotel_rooms) {

                                $reservationPassengers = [];
                                $associated_passenger = json_decode($reservations_hotel_rooms->associated_passenger);
                                if ($associated_passenger and is_array($associated_passenger)) {
                                    foreach ($associated_passenger as $associated) {
                                        foreach ($reservation->reservationsPassenger as $index_passgenger =>  $passenger) {

                                            if ($associated->sequence_number == $passenger['sequence_number']) {
                                                array_push($reservationPassengers, [
                                                    'reservation_passenger_id' => $passenger->id,
                                                    'sequence_number' => $passenger['sequence_number'],
                                                ]);;
                                                break;
                                            }
                                        }
                                    }
                                }

                                $reservations_hotel_rooms->passengers = $reservationPassengers;
                            }
                        }
                    }
                }

                $reservation->reservations_service = Service::getOriginDestination($reservation->reservationsService);

                /* si la reserva no tiene un ty_class_id es porque no viene de una cotizacion, si no de una reserva de carrito, por defecto si no tiene un hotel reservado colocamos la clase 6/Turista
                porque necesitamos este valor cargado si es que se necesita hacer ing inversa de a3
                */
                if (!isset($reservation->type_class_id)) {
                    $type_class_id = 6;

                    if (isset($reservation->reservationsHotel) and count($reservation->reservationsHotel) > 0) {
                        $type_class_id = $reservation->reservationsHotel[0]->hotel->typeclass->id;
                    }

                    $reservation->type_class_id = $type_class_id;

                    $categories = [[
                        'type_class_id' => $type_class_id,
                        'type_class' => TypeClass::with([
                            'translations' => function ($query) {
                                $query->select(['object_id', 'value']);
                                $query->where('type', 'typeclass');
                                $query->where('language_id', 1);
                            }
                        ])->select('id')->find($type_class_id)
                    ]];
                    $reservation->categories2 = $categories;
                }

                $data = [
                    'success' => true,
                    'data' => $reservation,
                ];
            } else {
                $data = [
                    'success' => false,
                    'data' => "no existe reserva",
                ];
            }
        });

        return response()->json($data);
    }

    public function getReservationHotelAsign($reservationsHotel)
    {
        $passenger_assign = [
            'SGL' => [
                'cant' => 0,
                'asign' => 0,
            ],
            'DBL' => [
                'cant' => 0,
                'asign' => 0,
            ],
            'TPL' => [
                'cant' => 0,
                'asign' => 0,
            ],
        ];

        foreach ($reservationsHotel as $index_h => $reservations_hotel) {
            foreach ($reservations_hotel->reservationsHotelRooms as $index_r =>  $reservations_hotel_rooms) {
                $occupation = $reservations_hotel_rooms->room_type->occupation;
                if ($occupation == 1) {
                    $passenger_assign['SGL']['cant'] = $passenger_assign['SGL']['cant'] + $occupation;
                }
                if ($occupation == 2) {
                    $passenger_assign['DBL']['cant'] = $passenger_assign['DBL']['cant'] + $occupation;
                }
                if ($occupation == 3) {
                    $passenger_assign['TPL']['cant'] = $passenger_assign['TPL']['cant'] + $occupation;
                }
            }
        }

        return $passenger_assign;
    }

    public function update_itinerary(Request $request, $id)
    {
        $data = [
            'success' => false,
            'data' => [],
        ];

        $update = 1;

        DB::transaction(function () use ($update, $id, &$data) {
            $update = (int) $update;
            $reservation = Reservation::getReservations([
                'reservation_id' => $id,
                'process_aurora3' => 1
            ], true);

            // Actualizando los estados..
            if ($update === 1) {
                $reservation->reservationsFlight->each(function ($flight) {
                    $flight->process_aurora3 = 0;
                    $flight->save();
                    return $flight;
                });

                $reservation->reservationsHotel->each(function ($hotel) {
                    $hotel->process_aurora3 = 0;
                    $hotel->save();
                    return $hotel;
                });

                $reservation->reservationsService->each(function ($service) {
                    $service->process_aurora3 = 0;
                    $service->save();
                    return $service;
                });
            }

            $data = [
                'success' => true,
                'data' => json_encode($reservation),
            ];
        });

        return response()->json($data);
    }

    public function response_endpoint_lambda(Request $request)
    {
        try {
            return response()->json([
                'type' => 'success',
                'message' => 'Received successfull',
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function unicodeToString($unicode_string)
    {
        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($matches) {
            return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
        }, $unicode_string);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function table_masters(Request $request)
    {

        $response = [];

        $lang = $request->input("lang") ? $request->input("lang") : 'en';
        $countries = Country::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();

        $dataCountry = [];
        foreach ($countries as $country) {
            array_push($dataCountry, [
                'iso' => $country->iso,
                'country' => $country->translations[0]->value,
                'phone_code' => $country->phone_code,
            ]);
        }

        array_multisort(array_column($dataCountry, 'country'), $dataCountry);

        $doctypes = Doctype::with([
            'translations' => function ($query) use ($lang) {
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();

        $dataDoctypes = [];
        foreach ($doctypes as $doctype) {
            array_push($dataDoctypes, [
                'iso' => $doctype->iso,
                'doctype' => $doctype->translations[0]->value
            ]);
        }

        array_multisort(array_column($dataDoctypes, 'doctype'), $dataDoctypes);

        return response()->json([
            'countries' => $dataCountry,
            'dataDoctypes' => $dataDoctypes
        ]);
    }

    public function wordItineraryPublic(Request $request)
    {
        return $this->generateWordItineraryPublic($request);
    }

    public function update_cloudinary(Request $request)
    {
        $token = $request->__get('token');

        try {
            if ($token == 'KLu1zSv%') {
                $resources = [];

                $folders = $request->__get('folders') ?? "['services', 'hotels']";

                $folders = str_replace("'", '"', $folders);
                $folders = json_decode($folders);
                $galleries = [];
                $ignore = [];

                foreach ($folders as $folder) {
                    $nextCursor = null;

                    do {
                        // Hacemos la solicitud a Cloudinary con el cursor si está disponible
                        $params = [
                            'type' => 'upload',
                            'prefix' => $folder,
                            'max_results' => 1000, // Ajusta según necesidad
                        ];

                        if ($nextCursor) {
                            $params['next_cursor'] = $nextCursor;
                        }

                        $result = Cloudder::resources($params);

                        if (!empty($result['resources'])) {
                            $resources = array_merge($resources, $result['resources']);
                        }

                        // Verificamos si hay más resultados por cargar
                        $nextCursor = $result['next_cursor'] ?? null;
                    } while ($nextCursor);
                }

                foreach ($resources as $image) {
                    $folder = $image['folder'];

                    // No-production: Extraer información del folder según su formato
                    $chunks = explode("/", $folder);
                    $objectId = null;
                    $type = null;

                    if (count($chunks) === 4 || count($chunks) === 3) {
                        // Si el folder tiene tres partes, asumimos que es de tipo 'room'
                        $objectId = ($chunks[2] === 'rooms') ? $chunks[3] : $chunks[2];
                        $type = 'room';
                    } else if (count($chunks) === 2) {

                        if ($chunks[0] === 'hotels') {
                            $hotel = Hotel::whereHas('channel', function ($query) use ($chunks) {
                                $query->where('code', $chunks[1]);
                            })->first();

                            if ($hotel) {
                                $objectId = $hotel->id;
                                $type = 'hotel';
                            }
                        }

                        if ($chunks[0] === 'services') {
                            $service = Service::where('aurora_code', $chunks[1])->first();
                            if ($service) {
                                $objectId = $service->id;
                                $type = 'service';
                            }
                        }
                    }

                    // Si se determinó el objectId y el tipo, se buscan las galerías
                    if (!empty($objectId) && !is_null($type)) {

                        if (!in_array($objectId, $ignore)) {
                            Galery::where('type', '=', $type)
                                ->whereIn('slug', ['service_gallery', 'hotel_gallery', 'room_gallery'])
                                ->where('object_id', '=', $objectId)
                                ->delete();
                            $ignore[] = $objectId;
                        }

                        $gallery = new Galery;
                        $gallery->object_id = $objectId;
                        $gallery->type = $type;
                        $gallery->slug = $type . '_gallery';
                        $gallery->url = $image['secure_url'];
                        $gallery->save();

                        $galleries[] = $gallery;
                    }
                }

                return response()->json([
                    'success' => true,
                    'galleries' => $galleries,
                    'resources' => $resources,
                    'ignore' => $ignore,
                ]);
            }
        } catch (\Exception $ex) {
            return response()->json($this->throwError($ex));
        }
    }

    public function getRouteCommercialP()
    {
        return config('services.files_onedb.domain');
    }

    public function process_error(Request $request, $file_number)
    {
        $has_pending_processes = [
            'has_pending_processes' => false,
            'process' => ''
        ];
        $reservation = Reservation::with('reservationsHotel')->where('file_code', $file_number)->first();

        if ($reservation) {
            if ($reservation->status_cron_job_error == 1) {
                $has_pending_processes = [
                    'has_pending_processes' => true,
                    'process' => 'status_cron_job_reservation_stella',
                ];
            } else {

                $results = $reservation->reservationsHotel->filter(function ($item) {
                    return $item->process_aurora3 == 1;
                });

                if (count($results) > 0) {
                    $has_pending_processes = [
                        'has_pending_processes' => true,
                        'process' => 'process_aurora_3',
                    ];
                }
            }
        }
        return response()->json([
            'success' => true,
            'data' => $has_pending_processes,
        ]);
    }

    public function executivesSelectBox(Request $request)
    {
        $code = strtoupper($request->__get('code') ?? '');

        try {
            $client = new \GuzzleHttp\Client();
            $request_api = $client->get(
                config('services.files_onedb.domain') .
                    'executives/search-by-sector?boss=' . $code
            );
            $result_api = json_decode($request_api->getBody()->getContents(), true);
            $users = $result_api['data'] ?? [];

            $response = [];

            foreach ($users as $user) {
                if (!isset($response[$user['jefe_regional']])) {
                    $response[$user['jefe_regional']] = [
                        'users' => [],
                        'code' => $user['jefe_regional'],
                        'name' => $user['razon_jefe_regional'] ?? '',
                        'region' => $user['region'],
                    ];
                }

                if (!isset($response[$user['jefe_regional']]['users'][$user['jefe']])) {
                    $response[$user['jefe_regional']]['users'][$user['jefe']] = [
                        'users' => [],
                        'region' => $user['region'],
                        'code' => $user['jefe'],
                        'name' => $user['razon_jefe'] ?? '',
                    ];
                }

                $response[$user['jefe_regional']]['users'][$user['jefe']]['users'][] = [
                    'code' => $user['nomesp'],
                    'name' => $user['razon'],
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $response,
            ]);
        } catch (\Exception $ex) {
            return response()->json($this->throwError($ex));
        }
    }

    public function search_orders_ifx(Request $request)
    {
        try {
            $orders = $request->__get('orders');
            $client = new \GuzzleHttp\Client();
            $link = sprintf('%sapi/v1/orders/all', config('services.stella.domain'));

            $response = $client->post($link, [
                'form_params' => [
                    'orders' => $orders,
                ]
            ]);

            $data = $this->toArray(json_decode($response->getBody(), true));
            $quotes = [];

            foreach ($data['data'] as $order) {
                $quotes[] = Quote::select('order_related', 'order_position', 'date_received')
                    ->where('order_related', '=', $order['nroped'])
                    ->where('order_position', '=', $order['nroord'])
                    ->whereNull('date_received')
                    ->update([
                        'date_received' => $order['fecrec'],
                    ]);
            }

            return response()->json([
                'success' => true,
                'quotes' => $quotes,
            ]);
        } catch (\Exception $ex) {
            return response()->json($this->throwError($ex));
        }
    }

    public function quotesMarkup(Request $request)
    {
        $files = $request->__get('files') ?? [];

        try {
            $reservations = Reservation::select(['file_code', 'markup'])
                ->whereIn('file_code', $files)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $reservations,
            ]);
        } catch (\Exception $ex) {
            return response()->json($this->throwError($ex));
        }
    }

    public function chat_backgrounds(Request $request)
    {
        $response = [];

        try {
            $images = ImageHighlight::whereHas('package_highlights.packages', function ($query) {
                $query->where('country_id', '=', 89);
                $query->where('status', '=', 1);
                $query->where('recommended', '=', 1);
            })
                ->whereHas('package_highlights', function ($query) {
                    $query->where('order', '=', 1);
                })
                //->with(['package_highlights.packages'])
                ->take(5)
                ->inRandomOrder()
                ->get()
                ->pluck('url');

            $response = [
                'success' => true,
                'images' => $images,
            ];
        } catch (\Exception $ex) {
            $response = $this->throwError($ex);
        } finally {
            return response()->json($response);
        }
    }

    public function chatbot_master_services(Request $request)
    {
        try {
            $aurora_codes = $request->input('codes');

            $_services = MasterService::with(['translations' => function ($query) {
                $query->whereIn('slug', ['skeleton']);
            }])
                ->where('status', '=', 1)
                ->whereIn('code', $aurora_codes)
                ->get(['master_services.id', 'master_services.code'])
                ->toArray();

            foreach ($_services as $key => $value) {
                $services[$value['code']] = $value;
            }

            return response()->json($services);
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            return [];
        }
    }

    public function download_services_latam(Request $request)
    {
        try {
            // Obtener servicios que cumplen con los criterios:
            // 1. require_itinerary = 1
            // 2. No tienen descripción en service_translations
            // 3. Pertenecen a un package cuyo tag -> tag_group_id = 8
            // Relación: Package → PackagePlanRate → PackagePlanRateCategory → PackageService → Service

            $langs = Language::whereIn('iso', ['es', 'en'])->get();
            $dataExport = [];

            foreach ($langs as $lang) {
                $services = Service::select(
                    'services.id',
                    'services.aurora_code',
                    'services.name as name_original',
                    'services.require_itinerary',
                    'services.notes', // Desde tabla services
                    'service_translations.name',
                    'service_translations.description', // Desde tabla translations
                    'service_translations.itinerary',   // Desde tabla translations
                    'service_translations.summary'      // Desde tabla translations
                )
                    ->where('services.service_sub_category_id', '!=', 10)
                    ->where('services.status', 1)
                    // Importante: Filtra por locale para no duplicar filas si hay varios idiomas
                    ->leftJoin('service_translations', function ($join) use ($lang) {
                        $join->on('services.id', '=', 'service_translations.service_id')
                            ->where('service_translations.language_id', '=', $lang->id);
                    })
                    ->where(function ($query) {
                        $query->orWhereNull('service_translations.itinerary')
                            ->orWhere('service_translations.itinerary', '');

                        $query->orWhereNull('service_translations.summary')
                            ->orWhere('service_translations.summary', '');

                        $query->orWhereNull('service_translations.name')
                            ->orWhere('service_translations.name', '');

                        $query->orWhereNull('services.notes')
                            ->orWhere('services.notes', '');
                    })
                    ->join('package_services', function ($join) {
                        $join->on('services.id', '=', 'package_services.object_id')
                            ->where('package_services.type', '=', 'service');
                    })
                    ->join('package_plan_rate_categories', 'package_services.package_plan_rate_category_id', '=', 'package_plan_rate_categories.id')
                    ->join('package_plan_rates', 'package_plan_rate_categories.package_plan_rate_id', '=', 'package_plan_rates.id')
                    ->join('packages', 'package_plan_rates.package_id', '=', 'packages.id')
                    ->join('tags', 'packages.tag_id', '=', 'tags.id')
                    ->where('tags.tag_group_id', 8)
                    ->whereNull('services.deleted_at')
                    ->whereNull('package_services.deleted_at')
                    ->whereNull('package_plan_rate_categories.deleted_at')
                    ->whereNull('package_plan_rates.deleted_at')
                    ->whereNull('packages.deleted_at')
                    ->whereNull('tags.deleted_at')
                    ->distinct()
                    ->get()
                    ->map(function ($service) {
                        return [
                            'id'                => $service->id,
                            'aurora_code'       => $service->aurora_code,
                            'name'              => $service->name ?? $service->name_original,
                            'require_itinerary' => $service->require_itinerary,

                            // Detalle de disponibilidad de contenido (columnas booleanas o texto)
                            'has_name'          => !empty(trim($service->name)) ? 'SÍ' : 'NO',
                            'has_description'   => !empty(trim($service->description)) ? 'SÍ' : 'NO',
                            'has_itinerary'     => !empty(trim($service->itinerary)) ? 'SÍ' : 'NO',
                            'has_summary'       => !empty(trim($service->summary)) ? 'SÍ' : 'NO',
                            'has_notes'         => !empty(trim($service->notes)) ? 'SÍ' : 'NO',
                        ];
                    });

                $dataExport[$lang->iso] = $services;
            }

            // dd($dataExport);

            // Generar nombre del archivo con fecha actual
            $filename = 'servicios_latam_' . date('Y-m-d_His') . '.xlsx';

            // Descargar Excel
            return \Excel::download(
                new \App\Exports\ServicesLatamExport($dataExport),
                $filename
            );
        } catch (\Exception $ex) {
            return response()->json($this->throwError($ex));
        }
    }

    public function quote_amounts(Request $request)
    {
        $quoteIds = $request->input('quotes', []);

        $data = Quote::whereIn('id', $quoteIds)
            ->get(['id as quote_id', 'estimated_price']);

        return response()->json($data);
    }
}
