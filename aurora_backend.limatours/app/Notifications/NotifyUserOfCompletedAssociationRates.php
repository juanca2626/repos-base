<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class NotifyUserOfCompletedAssociationRates
 * @package App\Notifications
 */
class NotifyUserOfCompletedAssociationRates extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $year;
    public $rates_plans;
    public $data;

    /**
     * Create a new notification instance.
     *
     * @param $user
     * @param $year
     * @param $rates_plans
     * @param $data
     */
    public function __construct($user, $year, $rates_plans, $data)
    {
        $this->user = $user;
        $this->year = $year;
        $this->rates_plans = $rates_plans;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.hotel.notification_association_rate')
            ->subject('🔔 Hotel - Bloqueo de tarifa completado')
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with([
                'user_from' => $this->user,
                'year' => $this->year,
                'rates_plans' => $this->rates_plans,
                'lang' => 'es',
                'data' => $this->data,
            ]);
    }
}
