<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class NotificationErrorStella extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $logs;
    public $attachments = [

    ];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reservation, $logs)
    {
        $this->reservation = $reservation;
        $this->logs = $logs;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $mail = $this->markdown('emails.web_services.notification_error_stella')
            ->subject('🚨 Notificación Error - API STELLA 🚨 FILE: '.$this->reservation->file_code)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->with([
                'reservation' => $this->reservation,
                'logs' => $this->logs,
            ]);

        if (Storage::disk('conector')->exists($this->logs->response)) {
            $mail = $mail->attachFromStorageDisk('conector', $this->logs->response, 'response.json', [
                'mime' => 'application/json',
            ]);
        }
        if (Storage::disk('conector')->exists($this->logs->request)) {
            $mail = $mail->attachFromStorageDisk('conector', $this->logs->request, 'request.json', [
                'mime' => 'application/json',
            ]);
        }

        return $mail;
    }
}
