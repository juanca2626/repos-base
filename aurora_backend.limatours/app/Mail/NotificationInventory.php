<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationInventory extends Mailable
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
    public function __construct($lang, $data)
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
        return $this->markdown('emails.reminder.' . $this->lang . '.notification_inventory')
            ->subject($this->subjects[$this->lang])
            ->bcc("luis.shepherd@tui.com")
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with($this->data);
    }
}
