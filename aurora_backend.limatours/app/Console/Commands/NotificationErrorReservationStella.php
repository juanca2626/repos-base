<?php

namespace App\Console\Commands;

use App\ChannelsLogs;
use App\Mail\NotificationErrorStella;
use App\Reservation;
use App\Http\Traits\ChannelLogs;
use App\Mail\NotificationErrorUsersStella;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotificationErrorReservationStella extends Command
{

    use ChannelLogs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:notification_error_reservation_stella';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia una notificaciones cuando hay un error al crear un proceso en el api de stella';

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
        $reservation = Reservation::where('status_cron_job_error', Reservation::STATUS_CRONJOB_ERROR_TRUE)
            ->with([
                'client' => function ($query) {
                    $query->select(['id', 'code', 'name']);
                }
            ])
            ->with([
                'executive' => function ($query) {
                    $query->select(['id', 'email','code', 'name']);
                }
            ])
            ->first([
                'id',
                'file_code',
                'client_id',
                'executive_id',
                'reservator_type',
                'status_cron_job_reservation_stella'
            ]);
        if ($reservation) {
            $logs = ChannelsLogs::where('reservation_id', $reservation->id)
                ->where(function ($query) {
                    $query->where('method_name', 'CreandoFile');
                    $query->orWhere('method_name', 'CreandoCliente');
                })
                ->limit(10)
                ->orderBy('id', 'desc')
                ->first([
                    'id',
                    'reservation_id',
                    'log_directory',
                    'type_data',
                    'log_request',
                    'log_response',
                    'created_at'
                ]);

            if ($logs) {
                $response = $this->getLogResponse($logs->log_directory, 'json');
                $logs->response_data = json_decode($response, true);
                $logs->response = $logs->log_directory.'response.json';
                $logs->request = $logs->log_directory.'request.json';
//                Mail::to('jgq@limatours.com.pe')->send(new NotificationErrorStella($reservation, $logs));
                Mail::to('devmonitoreo@limatours.com.pe')->send(new NotificationErrorStella($reservation, $logs));
                if(isset($reservation['executive']['email'])){
                    Mail::to($reservation['executive']['email'])->bcc('devmonitoreo@limatours.com.pe')->send(new NotificationErrorUsersStella($reservation, $logs));
                }else{
                    Mail::to('devmonitoreo@limatours.com.pe')->send(new NotificationErrorUsersStella($reservation, $logs));
                }

                $reservation->status_cron_job_error = Reservation::STATUS_CRONJOB_SEND_ERROR_NOTIFICATION;
                $reservation->save();
            }
        }
    }
}
