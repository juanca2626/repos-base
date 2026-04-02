<?php

namespace App\Jobs;

use App\GenerateRatesInCalendar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Traits\GenerateRatesCalendar;

class RateCalendaries implements ShouldQueue
{
    use GenerateRatesCalendar, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $generate_rates_in_calendar_id;
    public $tries = 2;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($generate_rates_in_calendar_id)
    {
        $this->connection = 'clone_hotel_rate_protection';
        $this->queue = 'rate_protection';
        $this->generate_rates_in_calendar_id = $generate_rates_in_calendar_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $this->processRates($this->generate_rates_in_calendar_id);

    }

    public function failed($exception)
    {
        $generate_rates_in_calendar =  GenerateRatesInCalendar::find($this->generate_rates_in_calendar_id);
        $generate_rates_in_calendar->status = 4;
        $generate_rates_in_calendar->status_message = $exception->getMessage();
        $generate_rates_in_calendar->save();

    }

}
