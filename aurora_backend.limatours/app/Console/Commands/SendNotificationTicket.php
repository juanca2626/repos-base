<?php

namespace App\Console\Commands;

use App\Client;
use App\Mail\NotificationTicket;
use App\Reservation;
use App\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class SendNotificationTicket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:send-notification-ticket {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia un email a la ejecutiva del ticket';

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
        $ticket = Ticket::find($this->argument('id'));
        $this->sendNotificationTicket($ticket);
    }

    public function sendNotificationTicket($ticket)
    {
        // Obtener el ticket con las relaciones necesarias
        $ticketData = Ticket::with(['service', 'hotel'])
            ->where('id', $ticket->id)
            ->first();

        if (!$ticketData) {
            return response()->json(['success' => false, 'message' => 'Ticket not found'], 404);
        }

        // Transformar el ticket con la información del cliente y la reserva
        $reservation = Reservation::where('file_code', $ticketData->file_code)->with('create_user')->first();
        $client = $reservation
            ? Client::where('id', $reservation->client_id)
                ->with('client_executives:name,email')
                ->first()
            : null;

        $ticketData->reservation = $reservation->toArray();
        $ticketData->client = $client->toArray();

        $ticketData = $this->utf8ize($ticketData); // Asegurar codificación UTF-8

        // Verificar si el cliente tiene ejecutivos asociados
        if ($client && $client->client_executives->isNotEmpty()) {
            // Obtener los correos electrónicos de los ejecutivos
            $emails = $client->client_executives->map(function ($executive) {
                return [
                    'name' => $executive->name,
                    'email' => $executive->email,
                ];
            })->toArray();

//            dd($ticketData->toArray());


            // Enviar correo dependiendo del entorno
            if (App::environment('local')) {
                $testEmails = [
                    ['name' => 'Jean Pierre Garay', 'email' => 'jgq@limatours.com.pe'],
                ];
                Mail::to($testEmails)->send(new NotificationTicket($ticketData->toArray()));
            } else {
                Mail::to($emails)->send(new NotificationTicket($ticketData->toArray()));
            }
        } else {
            return response()->json(['success' => false, 'message' => 'No executives found for this client'], 404);
        }
    }

    private function utf8ize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'utf8ize'], $data);
        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->{$key} = $this->utf8ize($value);
            }
            return $data;
        } else {
            return is_string($data) ? mb_convert_encoding($data, 'UTF-8', 'UTF-8') : $data;
        }
    }
}
