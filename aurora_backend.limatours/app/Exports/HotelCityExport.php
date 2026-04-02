<?php

namespace App\Exports;

use App\Hotel;
use App\HotelCategory;
use App\Language;
use App\Markup;
use App\RatesHistory;
use App\RatesPlansCalendarys;
use App\Service;
use App\ServiceCategory;
use App\ServiceType;
use App\State;
use App\TranslationFrontend;
use App\TypeClass;
use Carbon\Carbon;
use App\Client;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class HotelCityExport implements WithMultipleSheets
{
    use Exportable;

    protected $service_year;
    protected $lang;
    protected $client_id;

    public function __construct($service_year = null, $lang = null, $client_id = null)
    {
        $this->service_year = $service_year;

        $this->lang = $lang;

        $this->client_id = $client_id;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $cities = Cache::get('excel_hotels');

        $sheets = [];
        if(is_array($cities["cities"]) and count($cities["cities"]) > 0){
            foreach ($cities["cities"] as $city) {
                $sheets[] = new HotelYearExport($city, $this->service_year);
            }
        }

        $sheets[] = new ServiceTermsAndConditions($this->lang,$this->service_year);


        return $sheets;
    }
}
