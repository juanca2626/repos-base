<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationWSFallen extends Mailable
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
        return $this->markdown('emails.web_services.notification_ws_fallen')
            ->subject('🚨 Notificación - WS NO RESPONDEN 🚨')
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with([
                'data' => $this->data,
            ]);
    }
}
