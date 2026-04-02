<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationReservationReminders extends Mailable
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
        return $this->markdown('emails.reservations.reminder')
            ->subject('🔔 Booking ' . $this->data['reservation']['file_code'] . ' - ' . $this->data['reservation']['customer_name'])
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with([
                'reminder' => $this->data,
            ]);
    }
}
