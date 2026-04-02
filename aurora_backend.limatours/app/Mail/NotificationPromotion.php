<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationPromotion extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $type;
    public $lang;
    public $types = ['hotel' => 'new_hotel', 'rate' => 'new_rate'];
    public $subjects = ['hotel' => 'Hotel Nuevo', 'rate' => 'Tarifa Promocional Nueva'];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $type = 'hotel', $lang = 'en')
    {
        $this->lang = $lang;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.promotions.'  . $this->lang . '.' . $this->types[$this->type])
            ->subject('🔔 ' . $this->subjects[$this->type])
            ->bcc('kluizsv@gmail.com')
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with([
                'type' => $this->type,
                'data' => $this->data,
            ]);
    }
}
