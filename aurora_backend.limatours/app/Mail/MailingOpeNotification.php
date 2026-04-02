<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App;

class MailingOpeNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $type;
    public $content;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($type, $subject, $content)
    {
        $this->type = $type;
        $this->content = $content;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.ope.' . $this->type)
            ->subject($this->subject)
            ->from('hola@lito.pe', 'LITO')
            ->with([
                'content' => $this->content,
                'subject' => $this->subject,
            ]);
    }
}
