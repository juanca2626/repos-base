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

class SendNotificationHyperguest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Reservation */
    public $emails;
    public $reservations;
    public $log_request;
    public $log_response;

    /**
     * Create a new job instance.
     *
     * @param  Quote  $quote
     * @param  $client_id
     */
    public function __construct($emails, $reservations, string $request, string $response )
    {
        $this->emails = $emails;
        $this->reservations = $reservations;
        $this->log_request =  $request;
        $this->log_response = $response; 
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function handle()
    {
        Mail::to($this->emails)->send(new \App\Mail\NotificationHyperguestError($this->reservations,$this->log_request, $this->log_response));
    }

   
}
