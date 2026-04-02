<?php

namespace App\Console\Commands;

use App\Http\Stella\StellaService;
use App\Reservation;
use App\ReservationBilling;
use App\Http\Traits\ChannelLogs;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

//use App\Http\Stella\Traits\AuthorizesStellaApiRequests;

class CreateClientStellaService extends Command
{
    use ChannelLogs;
    protected $stellaService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stella:create-client';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea un nuevo cliente en Stella';

    /**
     * Create a new command instance.
     *
     * @param  StellaService  $stellaService
     */
    public function __construct(StellaService $stellaService)
    {
        $this->stellaService = $stellaService;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $reservations = Reservation::with([
                'billing' => function ($query) {
                    $query->select([
                        'id',
                        'client_stella_code',
                        'name',
                        'surnames',
                        'phone',
                        'email',
                        'document_type_id',
                        'document_number',
                        'business_name',
                        'address',
                        'country_id',
                        'state_id',
                        'state_ifx_iso',
                    ]);
                    $query->with('document_type');
                    $query->with('country');
                    $query->with('state');
                },
                'client' => function ($query) {
                    $query->select(['id', 'code']);
                },
                'executive' => function ($query) {
                    $query->select(['id', 'code']);
                }
            ])->where('status_cron_job_reservation_stella', Reservation::STATUS_CRONJOB_CREATE_BILLING_DATA)
                ->where('status_cron_job_error', Reservation::STATUS_CRONJOB_ERROR_FALSE)
                ->first();
            if (isset($reservations->billing)) {
                if (isset($reservations->billing) and $reservations->billing != null and $reservations->billing->client_stella_code === null) {
                    $forms = [
                        'client_code' => $reservations->client->code,
                        'selling_code' => $reservations->executive->code,
                        'type_document' => $reservations->billing->document_type->iso,
                        'number_document' => $reservations->billing->document_number,
                        'address' => $reservations->billing->address,
                        'name' => $reservations->billing->name,
                        'surnames' => $reservations->billing->surnames,
                        'country_iso' => $reservations->billing->country->iso,
                        'state_iso' => $reservations->billing->state_ifx_iso ,
                        'phone' => $reservations->billing->phone,
                        'type_passenger' => 'VP',
                    ];

                    $client = $this->stellaService->store_client($forms);
                    $result_confirm = (isset($client->success) and $client->success == true) ? true : false;
                    //Todo Guardamos el log del request de creacion de cliente
                    $this->putXmlLogAurora(
                        config('services.stella.domain'),
                        json_encode($forms), json_encode($client),
                        '', '',
                        'CreandoCliente', $reservations->id, $result_confirm, 'json');
                    if ($result_confirm) {
                        $billing = ReservationBilling::find($reservations->reservation_billing_id);
                        $billing->client_stella_code = $client->data->code_client;
                        $billing->save();
                        $reservations->status_cron_job_reservation_stella = Reservation::STATUS_CRONJOB_CREATE_FILE;
                        $reservations->save();
                    }else{
                        $reservations->status_cron_job_error = Reservation::STATUS_CRONJOB_ERROR_TRUE;
                        $reservations->save();
                    }
                } else {
                    $reservations->status_cron_job_reservation_stella = Reservation::STATUS_CRONJOB_CREATE_FILE;
                    $reservations->save();
                }
            }
        } catch (\Exception $exception) {
            $data = json_encode([
                'message' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);
        }
    }
}
