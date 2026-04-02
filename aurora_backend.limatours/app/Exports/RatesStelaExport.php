<?php

namespace App\Exports;

use App\Inclusion;
use App\Language;
use App\Translation;
use App\TranslationFrontend;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Contracts\View\View;

class RatesStelaExport implements FromView
{
    protected $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return
            view('exports.rates_stela', [
                'data' => $this->data,
            ]);
    }
}
