<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $type;
    public $data;
    public $lang;

    public $subjects = [
        'en' => 'Pending process reminder',
        'es' => 'Recordatorio de proceso pendiente',
        'pt' => 'Lembrete de processo pendente',
        'it' => 'Promemoria processo in sospeso'
    ];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($type, $lang, $data)
    {
        $this->type = $type;
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
        return $this->markdown('emails.reminder.' . $this->lang . '.' . $this->type)
            ->subject($this->subjects[$this->lang])
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with([
                'data' => $this->data,
            ]);
    }
}
