<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationSharedQuote extends Mailable
{
    use Queueable;
    use SerializesModels;
    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.quotes.notification_shared_quote')
            ->subject('🔔 '.trans('quote.notification').' '.trans('quote.quotation').' '.$this->data['quote_id'].' -  '. $this->data['user_from'] .' '.trans('quote.has_shared_a_quote_with_you'))
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with([
                'data' => $this->data,
            ]);
    }
}
