<?php

namespace App\Exports\RequestAurora;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class RequestServicesTextByLang implements FromView
{
    use Exportable;

    protected $services;
    protected $lang;

    public function __construct($services, $lang)
    {
        $this->services = $services;
        $this->lang = $lang;
    }

    public function view(): View
    {
        return view('exports.reports_aurora.services_text_by_lang', [
            'services' => $this->services,
            'lang' => $this->lang,
        ]);
    }
}
