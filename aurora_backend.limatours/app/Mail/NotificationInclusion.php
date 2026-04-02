<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationInclusion extends Mailable
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
        if ($this->data['action'] === 'create') {
            $subject_action = 'Se agrego una nueva inclusion';
        } else {
            $subject_action = 'Se modifico una inclusion';
        }
        return $this->markdown('emails.service.notification_inclusion')
            ->subject('🔔 Notificación Inclusion - ' . $subject_action)
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with([
                'data' => $this->data,
            ]);
    }
}
