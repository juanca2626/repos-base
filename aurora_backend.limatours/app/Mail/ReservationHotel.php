<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationHotel extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $lang;
    public $subjects = [
        'en' => 'Hotel Reservation',
        'es' => 'Reserva de Hotel',
        'pt' => 'Hotel Reservation',
        'it' => 'Hotel Reservation'
    ];
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($lang, $reservation)
    {
        $this->reservation = $reservation;
        $this->lang = $lang;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.reservations.hotel_join')
            ->subject($this->subjects[$this->lang])
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with([
                'reservation' => $this->reservation,
            ]);
    }
}
