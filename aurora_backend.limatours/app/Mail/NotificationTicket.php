<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationTicket extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
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
        return $this->markdown('emails.ticket.notification_ticket')
            ->subject('🔔 Notificación Ticket')
            ->bcc('jgq@limatours.com.pe')
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with(['data', $this->data]);
    }
}
