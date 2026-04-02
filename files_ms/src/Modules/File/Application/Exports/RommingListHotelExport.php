<?php

namespace Src\Modules\File\Application\Exports;
 
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class RommingListHotelExport implements FromView, WithTitle
{
    use Exportable;

    protected $hotel; 
    protected $file_number; 
    protected $file_description; 


    public function __construct(
        $hotel,
        $file_number, 
        $file_description,
    ) {
        $this->hotel = $hotel; 
        $this->file_number = $file_number;
        $this->file_description = $file_description;
    }
 

    public function view(): View
    { 
        return
            view('exports.romming_list', [
                'hotel' => $this->hotel, 
                'file_number' => $this->file_number,
                'file_description' => $this->file_description,
            ]);

    }
 
    public function title(): string
    {
        return $this->hotel['hotel'];
    }
}
