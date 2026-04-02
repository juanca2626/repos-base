<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App;

class MailingMasiReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $type;
    public $data;
    public $lang;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($type, $lang, $subject, $data)
    {
        $this->type = $type;
        $this->data = $data;
        $this->lang = $lang;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        \App::setLocale($this->lang);
        return $this->markdown('emails.masi.mailing_' . $this->type)
            ->subject($this->subject)
            ->from('hola@lito.pe', 'LITO')
            ->with([
                'items' => $this->data,
            ]);
    }
}
