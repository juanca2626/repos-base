<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class HotelRatesAssociationsErrorExport implements FromView
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
            view('exports.hotels_rates_plans_associations', [
                'hotels_group' => $this->hotels,
            ]);
    }
}
