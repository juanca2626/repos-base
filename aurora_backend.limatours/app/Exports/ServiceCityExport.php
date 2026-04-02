<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Class ServiceCityExport
 * @package App\Exports
 */
class ServiceCityExport implements WithMultipleSheets
{
    use Exportable;

    protected $service_year;
    protected $lang;
    protected $client_id;
    protected $data;


    public function __construct($service_year = null, $lang = null, $client_id = null, $data = [])
    {
        $this->service_year = $service_year;

        $this->lang = $lang;

        $this->client_id = $client_id;

        $this->data = $data;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        foreach ($this->data["cities"] as $city) {
            $sheets[] = new ServiceYearExport($city, $this->lang, $this->service_year);
        }
        $sheets[] = new ServiceTermsAndConditions($this->lang, $this->service_year);

        return $sheets;
    }

    public static function sortByRate($a, $b)
    {
        $a = $a['rate_order'];
        $b = $b['rate_order'];

        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }

}
