<?php

namespace App\Exports\RequestAurora;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class RequestRoomsByHotelList implements FromView
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
            view('exports.reports_aurora.hotels_rooms', [
                'hotels' => $this->hotels,
            ]);
    }
}
