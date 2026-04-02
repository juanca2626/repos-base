<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationHotel extends Mailable
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
        return $this->markdown('emails.hotel.notification_hotel')
            ->subject('🔔 Notificación Hotel -  Se modificó el hotel: ' . $this->data['hotel_name'])
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with([
                'data' => $this->data,
            ]);
    }
}
