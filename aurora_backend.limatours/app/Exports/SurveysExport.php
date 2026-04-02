<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class SurveysExport implements FromView
{
    use Exportable;

    public $surveys;
    public $packages;

    public function __construct($surveys, $packages)
    {
        $this->surveys = $surveys;
        $this->packages = $packages;
    }

    public function view(): View
    {
        return
            view('exports.surveys', [
                'surveys' => $this->surveys,
                'packages' => $this->packages,
            ]);
    }
}
