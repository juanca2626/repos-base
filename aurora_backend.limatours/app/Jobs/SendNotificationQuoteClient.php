<?php

namespace App\Jobs;

use App\Client;
use App\ClientExecutive;
use App\Mail\NotificationClientQuoteExecutive;
use App\Quote;
use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class SendNotificationQuoteClient implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Reservation */
    public $quote;

    public $tries = 2;

    public $client_id;

    /**
     * Create a new job instance.
     *
     * @param  Quote  $quote
     * @param  $client_id
     */
    public function __construct(Quote $quote, $client_id)
    {
        $this->quote = $quote;
        $this->connection = 'hotel_reservations_emails';
        $this->queue = 'email_dispatcher';
        $this->client_id = $client_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function handle()
    {
        $mailsData = $this->mailDataQuote();
        $this->send($mailsData);
    }

    /**
     * @param  array  $mailsData
     * @throws \ReflectionException
     */
    public function send(array $mailsData)
    {
        foreach ($mailsData as $mailData) {
            /** @var Mailable $mailable */
            $mailable = new $mailData['mail_class']($mailData['mail_data']);
            $mailable->subject($mailData['subject']);
            if (App::environment('production')) {
                //Todo Produccion
                $email = Mail::to($mailData['to']);
                if ($mailData['cc']) {
                    $email->cc($mailData['cc']);
                }
            } else {
                //Todo Dev test
                $email = Mail::to('jgq@limatours.com.pe');
            }

            $email->send($mailable);
        }
    }

    public function mailDataQuote()
    {
        $quote = $this->quote;
        $clientExecutives = $this->get_client_executives($this->client_id);
        $mailsData = [];
        if(count($clientExecutives) > 0) {
            $client = Client::find($this->client_id);
            $mailsData[] = [
                'to' => $clientExecutives,
                'cc' => [],
                'mail_class' => NotificationClientQuoteExecutive::class,
                'mail_data' => [
                    'quote' => $quote->toArray(),
                    'client' => $client->toArray()
                ],
                'subject' => 'El cliente '. $client->code .' ha creado una cotización N° '.$quote->id,
            ];
        }


        return $mailsData;
    }
    public function get_client_executives($client_id)
    {
        $client_executives = ClientExecutive::where('client_id', $client_id)
            ->where('status', 1)
            ->where('use_email_reserve', 1)
            ->with('user')
            ->get();
        $client_executives_emails = [];
        foreach ($client_executives as $client_executive) {
            if (isset($client_executive->user) and $client_executive->user->user_type_id == 3) {
                array_push($client_executives_emails, $client_executive->user->email);
            }
        }
        return $client_executives_emails;
    }

}
