<?php

use App\Service;
use App\ServiceTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTranslationsTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_service_trans_es = File::get("database/data/service_translation_itinerary_es.json");
        $services_trans_es = json_decode($file_service_trans_es, true);
        $file_service_trans_en = File::get("database/data/service_translation_itinerary_en.json");
        $services_trans_en = json_decode($file_service_trans_en, true);
        $file_service_trans_pt = File::get("database/data/service_translation_itinerary_pt.json");
        $services_trans_pt = json_decode($file_service_trans_pt, true);
        $file_service_trans_it = File::get("database/data/service_translation_itinerary_it.json");
        $services_trans_it = json_decode($file_service_trans_it, true);
        // nuevos servicios
        $file_service_trans_new_es = File::get("database/data/service_translation_itinerary_new_es.json");
        $services_trans_new_es = json_decode($file_service_trans_new_es, true);
        $file_service_trans_new_en = File::get("database/data/service_translation_itinerary_new_en.json");
        $services_trans_new_en = json_decode($file_service_trans_new_en, true);
        $file_service_trans_new_pt = File::get("database/data/service_translation_itinerary_new_pt.json");
        $services_trans_new_pt = json_decode($file_service_trans_new_pt, true);
        $file_service_trans_new_it = File::get("database/data/service_translation_itinerary_new_it.json");
        $services_trans_new_it = json_decode($file_service_trans_new_it, true);
        //params
        $created_at = date("Y-m-d H:i:s");
        $lang_es = 1;
        $lang_en = 2;
        $lang_pt = 3;
        $lang_it = 4;

        //español
        DB::transaction(function () use (
            $services_trans_es,
            $created_at,
            $lang_es
        ) {
            foreach ($services_trans_es as $trans_es) {
                $service = Service::where('aurora_code', $trans_es['aurora_code'])->where('equivalence_aurora',
                    $trans_es['equivalence_aurora'])->first();
                if ($service) {
                    $service_translation = ServiceTranslation::where('service_id', $service->id)->where('language_id',
                        $lang_es)->first();
                    $itinerary = trim(strip_tags(html_entity_decode($trans_es["itinerary"])));
                    if ($service_translation) {
                        $service_translation->itinerary = $itinerary;
                        $service_translation->updated_at = $created_at;
                        $service_translation->save();
                    } else {
                        $service_translation_new = new ServiceTranslation();
                        $service_translation_new->itinerary = $itinerary;
                        $service_translation_new->service_id = $service->id;
                        $service_translation_new->language_id = $lang_es;
                        $service_translation_new->created_at = $created_at;
                        $service_translation_new->updated_at = $created_at;
                        $service_translation_new->save();
                    }
                }
            }
        });

        //ingles
        DB::transaction(function () use (
            $services_trans_en,
            $created_at,
            $lang_en
        ) {
            foreach ($services_trans_en as $trans_en) {
                $service = Service::where('aurora_code', $trans_en['aurora_code'])->where('equivalence_aurora',
                    $trans_en['equivalence_aurora'])->first();
                if ($service) {
                    $service_translation = ServiceTranslation::where('service_id', $service->id)->where('language_id',
                        $lang_en)->first();
                    $itinerary = trim(strip_tags(html_entity_decode($trans_en["itinerary"])));
                    if ($service_translation) {
                        $service_translation->itinerary = $itinerary;
                        $service_translation->updated_at = $created_at;
                        $service_translation->save();
                    } else {
                        $service_translation_new = new ServiceTranslation();
                        $service_translation_new->itinerary = $itinerary;
                        $service_translation_new->service_id = $service->id;
                        $service_translation_new->language_id = $lang_en;
                        $service_translation_new->created_at = $created_at;
                        $service_translation_new->updated_at = $created_at;
                        $service_translation_new->save();
                    }
                }
            }
        });

        //portugues
        DB::transaction(function () use (
            $services_trans_pt,
            $created_at,
            $lang_pt
        ) {
            foreach ($services_trans_pt as $trans_pt) {
                $service = Service::where('aurora_code', $trans_pt['aurora_code'])->where('equivalence_aurora',
                    $trans_pt['equivalence_aurora'])->first();
                if ($service) {
                    $service_translation = ServiceTranslation::where('service_id', $service->id)->where('language_id',
                        $lang_pt)->first();
                    $itinerary = trim(strip_tags(html_entity_decode($trans_pt["itinerary"])));
                    if ($service_translation) {
                        $service_translation->itinerary = $itinerary;
                        $service_translation->updated_at = $created_at;
                        $service_translation->save();
                    } else {
                        $service_translation_new = new ServiceTranslation();
                        $service_translation_new->itinerary = $itinerary;
                        $service_translation_new->service_id = $service->id;
                        $service_translation_new->language_id = $lang_pt;
                        $service_translation_new->created_at = $created_at;
                        $service_translation_new->updated_at = $created_at;
                        $service_translation_new->save();
                    }
                }
            }
        });

        //italiano
        DB::transaction(function () use (
            $services_trans_it,
            $created_at,
            $lang_it
        ) {
            foreach ($services_trans_it as $trans_it) {
                $service = Service::where('aurora_code', $trans_it['aurora_code'])->where('equivalence_aurora',
                    $trans_it['equivalence_aurora'])->first();
                if ($service) {
                    $service_translation = ServiceTranslation::where('service_id', $service->id)->where('language_id',
                        $lang_it)->first();
                    $itinerary = trim(strip_tags(html_entity_decode($trans_it["itinerary"])));
                    if ($service_translation) {
                        $service_translation->itinerary = $itinerary;
                        $service_translation->updated_at = $created_at;
                        $service_translation->save();
                    } else {
                        $service_translation_new = new ServiceTranslation();
                        $service_translation_new->itinerary = $itinerary;
                        $service_translation_new->service_id = $service->id;
                        $service_translation_new->language_id = $lang_it;
                        $service_translation_new->created_at = $created_at;
                        $service_translation_new->updated_at = $created_at;
                        $service_translation_new->save();
                    }
                }
            }
        });
        //------------------------------------------------

        //español nuevas traducciones
        DB::transaction(function () use (
            $services_trans_new_es,
            $created_at,
            $lang_es
        ) {
            foreach ($services_trans_new_es as $trans_es) {
                $service = Service::where('aurora_code', $trans_es['aurora_code'])->where('equivalence_aurora',
                    $trans_es['equivalence_aurora'])->first();
                if ($service) {
                    $service_translation = ServiceTranslation::where('service_id', $service->id)->where('language_id',
                        $lang_es)->first();
                    $itinerary = trim(strip_tags(html_entity_decode($trans_es["itinerary"])));
                    if ($service_translation) {
                        $service_translation->itinerary = $itinerary;
                        $service_translation->updated_at = $created_at;
                        $service_translation->save();
                    } else {
                        $service_translation_new = new ServiceTranslation();
                        $service_translation_new->itinerary = $itinerary;
                        $service_translation_new->service_id = $service->id;
                        $service_translation_new->language_id = $lang_es;
                        $service_translation_new->created_at = $created_at;
                        $service_translation_new->updated_at = $created_at;
                        $service_translation_new->save();
                    }
                }
            }
        });

        //ingles nuevas traducciones
        DB::transaction(function () use (
            $services_trans_new_en,
            $created_at,
            $lang_en
        ) {
            foreach ($services_trans_new_en as $trans_en) {
                $service = Service::where('aurora_code', $trans_en['aurora_code'])->where('equivalence_aurora',
                    $trans_en['equivalence_aurora'])->first();
                if ($service) {
                    $service_translation = ServiceTranslation::where('service_id', $service->id)->where('language_id',
                        $lang_en)->first();
                    $itinerary = trim(strip_tags(html_entity_decode($trans_en["itinerary"])));
                    if ($service_translation) {
                        $service_translation->itinerary = $itinerary;
                        $service_translation->updated_at = $created_at;
                        $service_translation->save();
                    } else {
                        $service_translation_new = new ServiceTranslation();
                        $service_translation_new->itinerary = $itinerary;
                        $service_translation_new->service_id = $service->id;
                        $service_translation_new->language_id = $lang_en;
                        $service_translation_new->created_at = $created_at;
                        $service_translation_new->updated_at = $created_at;
                        $service_translation_new->save();
                    }
                }
            }
        });

        //portug nuevas traducciones
        DB::transaction(function () use (
            $services_trans_new_pt,
            $created_at,
            $lang_pt
        ) {
            foreach ($services_trans_new_pt as $trans_pt) {
                $service = Service::where('aurora_code', $trans_pt['aurora_code'])->where('equivalence_aurora',
                    $trans_pt['equivalence_aurora'])->first();
                if ($service) {
                    $service_translation = ServiceTranslation::where('service_id', $service->id)->where('language_id',
                        $lang_pt)->first();
                    $itinerary = trim(strip_tags(html_entity_decode($trans_pt["itinerary"])));
                    if ($service_translation) {
                        $service_translation->itinerary = $itinerary;
                        $service_translation->updated_at = $created_at;
                        $service_translation->save();
                    } else {
                        $service_translation_new = new ServiceTranslation();
                        $service_translation_new->itinerary = $itinerary;
                        $service_translation_new->service_id = $service->id;
                        $service_translation_new->language_id = $lang_pt;
                        $service_translation_new->created_at = $created_at;
                        $service_translation_new->updated_at = $created_at;
                        $service_translation_new->save();
                    }
                }
            }
        });

        //italiano nuevas traducciones
        DB::transaction(function () use (
            $services_trans_new_it,
            $created_at,
            $lang_it
        ) {
            foreach ($services_trans_new_it as $trans_it) {
                $service = Service::where('aurora_code', $trans_it['aurora_code'])->where('equivalence_aurora',
                    $trans_it['equivalence_aurora'])->first();
                if ($service) {
                    $service_translation = ServiceTranslation::where('service_id', $service->id)->where('language_id',
                        $lang_it)->first();
                    $itinerary = trim(strip_tags(html_entity_decode($trans_it["itinerary"])));
                    if ($service_translation) {
                        $service_translation->itinerary = $itinerary;
                        $service_translation->updated_at = $created_at;
                        $service_translation->save();
                    } else {
                        $service_translation_new = new ServiceTranslation();
                        $service_translation_new->itinerary = $itinerary;
                        $service_translation_new->service_id = $service->id;
                        $service_translation_new->language_id = $lang_it;
                        $service_translation_new->created_at = $created_at;
                        $service_translation_new->updated_at = $created_at;
                        $service_translation_new->save();
                    }
                }
            }
        });
    }
}
