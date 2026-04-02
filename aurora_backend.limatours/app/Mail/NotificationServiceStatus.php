<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationServiceStatus extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $entity, $template;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($entity, $template, $data)
    {
        $this->data = $data;
        $this->entity = $entity;
        $this->template = $template;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.service.notification_coincidences_'.$this->template)
            ->subject('🔔 Notificación Servicios - ' . $this->entity . ' - A punto de ser Desactivado')
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with([
                'data' => $this->data,
            ]);
    }
}
