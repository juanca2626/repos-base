<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationClientQuoteExecutive extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $quote;
    public $tries = 3;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($quote)
    {
        $this->quote = $quote;

        $this->connection = 'hotel_reservations_emails';

        $this->queue = 'email';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.quotes.notification_create_client_quote');
    }
}
