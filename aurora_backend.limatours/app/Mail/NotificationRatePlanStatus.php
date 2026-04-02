<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationRatePlanStatus extends Mailable
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
        return $this->markdown('emails.rate_plan.notification_status')
            ->subject('🔔 Notificación Hoteles - Tarifa a punto de ser Desactivada')
            ->from('donoreply@communications.limatours.com.pe', 'Aurora')
            ->with([
                'data' => $this->data,
            ]);
    }
}
