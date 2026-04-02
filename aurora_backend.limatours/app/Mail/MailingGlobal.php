<?php

namespace App\Mail;

use finfo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailingGlobal extends Mailable
{
    use Queueable, SerializesModels;

    public $reply_to = [
        'noreply@notify.limatours.com.pe', 'Aurora'
    ];
    public $subject;
    public $content;
    public $files = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reply_to, $subject, $content, $files)
    {
        if(!empty($reply_to) && is_array($reply_to))
        {
            $this->reply_to = $reply_to;
        }

        $this->subject = $subject;
        $this->content = $content;
        $this->files = $files;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $name_reply = isset($this->reply_to[1]) ? $this->reply_to[1] : 'Aurora';

        $email = $this->view('emails.global')
            ->subject($this->subject)
            ->replyTo($this->reply_to[0], $this->reply_to[0])
            ->from('noreply@notify.limatours.com.pe', $name_reply)
            ->with([
                'content' => $this->content,
            ]);

        if(!empty($this->files))
        {
            foreach ($this->files as $file)
            {
                if(!empty($file) && is_string($file))
                {
                    // Obtener el contenido del archivo desde la URL
                    $response = file_get_contents($file);

                    if ($response)
                    {
                        $name = basename($file);
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mime = $finfo->buffer($response);

                        // Enviar el correo
                        if (is_string($response) && is_string($name) && is_string($mime))
                        {
                            // Enviar el correo con el archivo adjunto
                            $email->attachData($response, $name, [
                                'mime' => $mime,
                            ]);
                        }
                    }
                }
            }
        }

        return $email;
    }
}
