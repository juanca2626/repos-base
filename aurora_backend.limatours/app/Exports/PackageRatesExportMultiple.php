<?php

namespace App\Exports;

use App\Client;
use App\PackageRateSaleMarkup;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PackageRatesExportMultiple implements WithMultipleSheets
{
    use Exportable;

    protected $service_type_id;
    protected $categories;
    protected $title;
    protected $lang;
    protected $year;
    protected $plan_rate_id;
    protected $rate_sale_markups;

    public function __construct( $plan_rate_id, $service_type_id, $categories, $title ,$rate_sale_markups, $lang, $year)
    {
        $this->plan_rate_id = $plan_rate_id;
        $this->service_type_id = $service_type_id;
        $this->categories = $categories;
        $this->title = $title;
        $this->lang = $lang;
        $this->year = $year;
        $this->rate_sale_markups = $rate_sale_markups;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

//                return print_r( "hola" ); die;

        foreach ($this->rate_sale_markups as $k_r => $r_s_m) {
            $sheets[] =
                new PackageRatesExport( $this->plan_rate_id, $this->service_type_id, $this->categories, $this->title,
                    $r_s_m->id, $k_r, $r_s_m->markup, $r_s_m->seller->name,$this->lang, $this->year);
        }

        return $sheets;
    }
}
