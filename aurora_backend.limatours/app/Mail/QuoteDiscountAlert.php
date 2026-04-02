<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuoteDiscountAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $user_from;
    public $quote_id;
    public $quote_name;
    public $quote_discount;
    public $quote_discount_detail;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user_from, $quote_id, $quote_name, $quote_discount, $quote_discount_detail)
    {
        $this->user_from = $user_from;
        $this->quote_id = $quote_id;
        $this->quote_name = $quote_name;
        $this->quote_discount = $quote_discount;
        $this->quote_discount_detail = $quote_discount_detail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.quotes.discountalert')
            ->subject('Modulo de Cotizaciones - Alerta de Descuento')
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with([
                'user_from' => $this->user_from,
                'quote_id' => $this->quote_id,
                'quote_name' => $this->quote_name,
                'quote_discount' => $this->quote_discount,
                'quote_discount_detail' => $this->quote_discount_detail
            ]);
    }
}
