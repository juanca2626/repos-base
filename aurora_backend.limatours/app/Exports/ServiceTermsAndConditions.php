<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Class ServiceTermsAndConditions
 * @package App\Exports
 */
class ServiceTermsAndConditions implements FromView,WithTitle
{
    use Exportable;

    protected $lang;
    protected $year;

    public function __construct($lang,$year)
    {

        $this->lang = $lang;
        $this->year = $year;

    }

    public function view(): View
    {
        $years = (array) __('services_rate'); $year_view = $this->year;

        if(!isset($years[$this->year]))
        {
            krsort($years); $year_view = key($years);
        }

        return
            view('exports.service_terms_and_conditions_rates', [
                'year_view' => $year_view,
                'year' => $this->year,
                'lang' => $this->lang,
            ]);
    }

    public function title(): string
    {
        return ''.trans('export_excel.title_terms_and_conditions',[],$this->lang);
    }
}
