<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyUserOfCompletedExportServicesRates extends Mailable
{
    use Queueable, SerializesModels;

    public $user_from;
    public $year;
    public $excel_path;
    public $lang;

    /**
     * Create a new message instance.
     *
     * @param $user_from
     * @param $year
     * @param $excel_path
     * @param  string  $lang
     */
    public function __construct($user_from, $year, $excel_path = null, $lang = 'es')
    {
        $this->user_from = $user_from;
        $this->year = $year;
        $this->excel_path = $excel_path;
        $this->lang = $lang;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.service.notification_export_service_rates')
            ->subject('🔔 '. trans('mails.export_services_rates.application_services_rates', [], $this->lang).' '.$this->year)
            ->attach(storage_path('app/'.$this->excel_path.'.xlsx'),
                ['as' => trans('mails.export_services_rates.services', [], $this->lang).'_'.$this->year.'.xlsx'])
            ->from('noreply@notify.limatours.com.pe', 'Aurora')
            ->with([
                'user_from' => $this->user_from,
                'year' => $this->year,
                'lang' => $this->lang,
            ]);
    }
}
