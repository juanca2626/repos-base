<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels; 

class NotificationHyperguestError extends Mailable
{
    use Queueable, SerializesModels;

    public $reservations;
    public $log_request;
    public $log_response;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reservations, string $request, string $response )
    {
        $this->reservations = $reservations;
        $this->log_request =  $request;
        $this->log_response = $response;        
    }
    
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.hyperguest.reservation_errors')
            ->subject('🔔 Reservation Error - hotel: ' . $this->reservations['hotel_name'])
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with([
                'reservations' => $this->reservations,
                'log_request' => $this->log_request,
                'log_response' => $this->log_response,
            ]);
    }
}
