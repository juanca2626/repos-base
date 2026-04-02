<?php

namespace App\Exports\RequestAurora;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class RequestRatePlansByHotelList implements FromView
{
    use Exportable;

    protected $hotels;

    public function __construct($hotels)
    {
        $this->hotels = $hotels;
    }

    public function view(): View
    {
        return
            view('exports.reports_aurora.hotels_rate_plans', [
                'hotels' => $this->hotels,
            ]);
    }
}
