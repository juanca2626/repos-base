<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendReviewTripAdvisor extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.aurora.trip_advisor')
            ->subject('🔔 Limatours - TripAdvisor')
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->with([
                'data' => $this->data,
            ]);
    }
}
