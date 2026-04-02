<?php

namespace App\Exports\RequestAurora;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class RequestHotelRoomsWithOutImages implements FromView
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
            view('exports.reports_aurora.hotels_rooms_with_out_images', [
                'hotels' => $this->hotels,
            ]);
    }
}
