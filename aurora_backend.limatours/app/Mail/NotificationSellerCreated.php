<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationSellerCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $lang;

    public $subjects = [
        'en' => 'Access to the Aurora LimaTours Platform',
        'es' => 'Acceso a la Plataforma Aurora LimaTours',
        'pt' => 'Acesso à Plataforma Aurora LimaTours'
    ];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $lang)
    {
        $this->data = $data;
        $this->lang = $lang;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->markdown('emails.user.' . $this->lang . '.notificationsellercreated')
            ->subject($this->subjects[$this->lang])
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with([
                'data' => $this->data,
            ]);
    }
}
