<?php

namespace App\Exports;

use App\Language;
use App\Service;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;


class ServiceTranslationsExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * @return \Illuminate\Support\Collection
     */


    public function sheets(): array
    {
        $language = Language::where('state', 1)->get();
        $language_ids = $language->pluck('id');
        $language_isos = $language->pluck('iso');
        $data = [
            "data" => []
        ];

        $services = Service::where('status', 1)->with([
            'service_translations' => function ($query) use ($language_ids) {
                $query->select('id', 'language_id', 'service_id', 'name', 'description', 'itinerary',
                    'summary')
                    ->whereIn('language_id', $language_ids)
                    ->orderBy('language_id', 'asc');
            }
        ])->with('service_rate.service_rate_plans.policy')
            ->with(['inclusions.inclusions.translations'=>function($query) use($language_ids){
            $query->where('language_id', 1);
        }])->with('operability')->get(['id', 'name', 'aurora_code','notes']);

        foreach ($language_isos as $key_lang => $language_iso) {
            foreach ($services as $key => $service) {
                $inclusions = "";
                foreach ($service["inclusions"] as $inclusion)
                {
                    $inclusions.=$inclusion["inclusions"]["translations"][0]["value"].",";
                }
                $operations= "";
                foreach ($service["operability"] as $operability)
                {
                    $operations.="Dia".$operability["day"]." ".$operability["start_time"]." ".$operability["shifts_available"].",";
                }
                foreach ($service->service_translations as $column => $translation) {
                    $data['data'][$key_lang][$language_iso][$key]['id'] = $service->id;
                    $data['data'][$key_lang][$language_iso][$key]['code'] = $service->aurora_code;
                    $data['data'][$key_lang][$language_iso][$key]['notes'] = $service->notes;
                    $data['data'][$key_lang][$language_iso][$key]['name'] = (isset($service->service_translations[$key_lang]->name))?$service->service_translations[$key_lang]->name:'';
                    $data['data'][$key_lang][$language_iso][$key]['police'] = (isset($service->service_rate[0]["service_rate_plans"][0]["policy"]["name"]))?$service->service_rate[0]["service_rate_plans"][0]["policy"]["name"]:'';
                    $data['data'][$key_lang][$language_iso][$key]['description'] = (isset($service->service_translations[$key_lang]->description))?$service->service_translations[$key_lang]->description:'';
                    $data['data'][$key_lang][$language_iso][$key]['itinerary'] =(isset($service->service_translations[$key_lang]->itinerary))?$service->service_translations[$key_lang]->itinerary:'';
                    $data['data'][$key_lang][$language_iso][$key]['summary'] = (isset($service->service_translations[$key_lang]->summary))?$service->service_translations[$key_lang]->summary:'';
                    $data['data'][$key_lang][$language_iso][$key]['inclusions'] = $inclusions;
                    $data['data'][$key_lang][$language_iso][$key]['operations'] = $operations;
                }
            }

        }

        $sheets = [];

        foreach ($data["data"] as $key => $translation) {
            $sheets[] = new ServiceTextExport($translation);
        }


        return $sheets;
    }
}
