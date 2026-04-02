<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $link;
    public $time;
    public $lang;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($link, $time,$lang)
    {
        $this->link = $link;
        $this->time = $time;
        $this->lang = $lang;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.user.resetpassword')
            ->subject('Aurora - '. trans('password.forgot_password.subject', [], $this->lang))
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with([
                'link' => $this->link,
                'time' => $this->time,
                'lang' => $this->lang
            ]);
    }
}
