<?php

namespace App\Console\Commands;

use App\Http\Stella\StellaService;
use App\ReservationReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendReservationReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * CADA 24 Hrs
     * @return mixed
     */
    public function handle() // Una vez al dia deberia ejecutarse
    {
        DB::transaction(function () {

            $today = Carbon::now()->format('Y-m-d');

            $reminders = ReservationReminder::with(['reservation.client'])
                ->where('status', 1)
                ->where('date_reminder', '=', $today)
                ->get();

            $files = []; $ignore = [];

            $reminders->each(function ($reminder, $r) use (&$files) {
                $files[] = $reminder->reservation->file_code;
            });

            if(count($files) > 0)
            {
                $stellaService = new StellaService; $data = ['files' => implode(",", $files)];
                $response = (array) $stellaService->file_pay_status($data);

                foreach($response as $r)
                {
                    if($r->status == 'CE' OR $r->paid > 0)
                    {
                        $ignore[] = $r->nroref;
                    }
                }
            }

            // Notificar y cambiar el estado a 0
            foreach ( $reminders as $reminder ) {

                if(!in_array($reminder->reservation->file_code, $ignore))
                {
                    $reminder->status = 0;
                    $reminder->save();

                    $email_to = $reminder->email;
                    if(!$email_to){
                        $email_to = $reminder->email_alt;
                    }
                    if(!$email_to){
                        break;
                    }
                    $mail = mail::to($email_to);
                    if($reminder->email_alt){
                        $mail->cc($reminder->email_alt);
                    }
                    $mail->send(new \App\Mail\NotificationReservationReminders($reminder));
                }
            }

        });

    }

}
