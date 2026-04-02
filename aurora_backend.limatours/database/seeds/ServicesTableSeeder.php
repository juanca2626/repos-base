<?php

use App\Galery;
use App\ProgressBar;
use App\ServiceCancellationPolicies;
use App\ServicePoliticsParameter;
use App\ServiceTranslation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_service = File::get("database/data/services_new.json");
        $services = json_decode($file_service, true);

        $file_service_trans_es = File::get("database/data/service_translation_itinerary_new_es.json");
        $services_trans_es = json_decode($file_service_trans_es, true);
        $file_service_trans_en = File::get("database/data/service_translation_itinerary_new_en.json");
        $services_trans_en = json_decode($file_service_trans_en, true);
        $file_service_trans_pt = File::get("database/data/service_translation_itinerary_new_pt.json");
        $services_trans_pt = json_decode($file_service_trans_pt, true);
        $file_service_trans_it = File::get("database/data/service_translation_itinerary_new_it.json");
        $services_trans_it = json_decode($file_service_trans_it, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use (
            $services,
            $services_trans_es,
            $services_trans_en,
            $services_trans_pt,
            $services_trans_it,
            $created_at
        ) {
            foreach ($services as $key => $service) {
                $serviceId = DB::table('services')->insertGetId([
                    "aurora_code" => $service["aurora_code"],
                    "name" => $service["name"],
                    "currency_id" => $service["currency_id"],
                    "latitude" => $service["latitude"],
                    "longitude" => $service["longitude"],
                    "qty_reserve" => $service["qty_reserve"],
                    "equivalence_aurora" => $service["equivalence_aurora"],
                    "affected_igv" => $service["affected_igv"],
                    "allow_guide" => $service["allow_guide"],
                    "allow_child" => $service["allow_child"],
                    "allow_infant" => $service["allow_infant"],
                    "limit_confirm_hours" => $service["limit_confirm_hours"],
                    "infant_min_age" => $service["infant_min_age"],
                    "infant_max_age" => $service["infant_max_age"],
                    "include_accommodation" => $service["include_accommodation"],
                    "unit_id" => $service["unit_id"],
                    "unit_duration_id" => $service["unit_duration_id"],
                    "service_type_id" => $service["service_type_id"],
                    "classification_id" => $service["classification_id"],
                    "service_sub_category_id" => $service["service_sub_category_id"],
                    "user_id" => $service["user_id"],
                    "date_solicitude" => $created_at,
                    "duration" => $service["duration"],
                    "pax_min" => $service["pax_min"],
                    "pax_max" => $service["pax_max"],
                    "min_age" => $service["min_age"],
                    "require_itinerary" => $service["require_itinerary"],
                    "require_image_itinerary" => $service["require_image_itinerary"],
                    "status" => $service["status"],
                    "created_at" => $created_at,
                    "updated_at" => $created_at,
                ]);

                //Datos generales (origen, destino, etc.)
                ProgressBar::updateOrCreate([
                    'slug' => 'service_progress_details',
                    'value' => 10,
                    'type' => 'service',
                    'object_id' => $serviceId
                ]);

                //Descripciones
                ProgressBar::updateOrCreate([
                    'slug' => 'service_progress_descriptions',
                    'value' => 10,
                    'type' => 'service',
                    'object_id' => $serviceId
                ]);

                // Progress localizacion
                if ($service["latitude"] != 0 and $service["longitude"] != 0) {
                    ProgressBar::updateOrCreate([
                        'slug' => 'service_progress_location',
                        'value' => 5,
                        'type' => 'service',
                        'object_id' => $serviceId
                    ]);
                }

                if ($service["experiences"] != "") {
                    $experiences = explode(',', $service['experiences']);
                    foreach ($experiences as $experience) {
                        DB::table('experience_service')->insert([
                            'service_id' => $serviceId,
                            'experience_id' => $experience,
                            'created_at' => $created_at,
                            'updated_at' => $created_at
                        ]);
                    }
                    // Progress experiencias
                    ProgressBar::updateOrCreate([
                        'slug' => 'service_progress_experiences',
                        'value' => 10,
                        'type' => 'service',
                        'object_id' => $serviceId
                    ]);
                }

                // Progress galeria
                if ($service["image"] != "") {
                    $galery = new Galery();
                    $galery->type = 'service';
                    $galery->object_id = $serviceId;
                    $galery->position = 1;
                    $galery->slug = 'service_gallery';
                    $galery->url = $service['image'];
                    $galery->state = 1;
                    $galery->save();

                    ProgressBar::updateOrCreate([
                        'slug' => 'service_progress_gallery',
                        'value' => 10,
                        'type' => 'service',
                        'object_id' => $serviceId
                    ]);
                }

                if (isset($services_trans_es[$key]["aurora_code"])) {
                    // Textos español
                    $name_commercial_es = trim(strip_tags(html_entity_decode($service["name_commercial_es"])));
                    $description_es = trim(strip_tags(html_entity_decode($service["description_es"])));
                    $summary_es = trim(strip_tags(html_entity_decode($service["summary_es"])));
                    $itinerary_es = trim(strip_tags(html_entity_decode($services_trans_es[$key]["itinerary"])));
                    $service_translations_es = new ServiceTranslation();
                    $service_translations_es->language_id = 1;
                    $service_translations_es->name_commercial = $name_commercial_es;
                    $service_translations_es->description = $description_es;
                    $service_translations_es->itinerary = $itinerary_es;
                    $service_translations_es->summary = $summary_es;
                    $service_translations_es->service_id = $serviceId;
                    $service_translations_es->created_at = $created_at;
                    $service_translations_es->updated_at = $created_at;
                    $service_translations_es->save();
                    // Textos ingles
                    $name_commercial_en = trim(strip_tags(html_entity_decode($service["name_commercial_en"])));
                    $description_en = trim(strip_tags(html_entity_decode($service["description_en"])));
                    $summary_en = trim(strip_tags(html_entity_decode($service["summary_en"])));
                    $itinerary_en = trim(strip_tags(html_entity_decode($services_trans_en[$key]["itinerary"])));
                    $service_translations_en = new ServiceTranslation();
                    $service_translations_en->language_id = 2;
                    $service_translations_en->name_commercial = $name_commercial_en;
                    $service_translations_en->description = $description_en;
                    $service_translations_en->itinerary = $itinerary_en;
                    $service_translations_en->summary = $summary_en;
                    $service_translations_en->service_id = $serviceId;
                    $service_translations_en->created_at = $created_at;
                    $service_translations_en->updated_at = $created_at;
                    $service_translations_en->save();
                    // Textos portugues
                    $name_commercial_pt = trim(strip_tags(html_entity_decode($service["name_commercial_pt"])));
                    $description_pt = trim(strip_tags(html_entity_decode($service["description_pt"])));
                    $summary_pt = trim(strip_tags(html_entity_decode($service["summary_pt"])));
                    $itinerary_pt = trim(strip_tags(html_entity_decode($services_trans_pt[$key]["itinerary"])));
                    $service_translations_pt = new ServiceTranslation();
                    $service_translations_pt->language_id = 3;
                    $service_translations_pt->name_commercial = $name_commercial_pt;
                    $service_translations_pt->description = $description_pt;
                    $service_translations_pt->itinerary = $itinerary_pt;
                    $service_translations_pt->summary = $summary_pt;
                    $service_translations_pt->service_id = $serviceId;
                    $service_translations_pt->created_at = $created_at;
                    $service_translations_pt->updated_at = $created_at;
                    $service_translations_pt->save();
                    // Textos italiano
                    $name_commercial_it = trim(strip_tags(html_entity_decode($service["name_commercial_it"])));
                    $description_it = trim(strip_tags(html_entity_decode($service["description_it"])));
                    $summary_it = trim(strip_tags(html_entity_decode($service["summary_it"])));
                    $itinerary_it = trim(strip_tags(html_entity_decode($services_trans_it[$key]["itinerary"])));
                    $service_translations_it = new ServiceTranslation();
                    $service_translations_it->language_id = 4;
                    $service_translations_it->name_commercial = $name_commercial_it;
                    $service_translations_it->description = $description_it;
                    $service_translations_it->itinerary = $itinerary_it;
                    $service_translations_it->summary = $summary_it;
                    $service_translations_it->service_id = $serviceId;
                    $service_translations_it->created_at = $created_at;
                    $service_translations_it->updated_at = $created_at;
                    $service_translations_it->save();
                }

                //Politicas de cancelación
                $newPolitic = new ServiceCancellationPolicies();
                $newPolitic->name = 'Politica';
                $newPolitic->service_id = $serviceId;
                $newPolitic->status = 1;
                $newPolitic->save();

                //Politicas de cancelación parametros
                $newPoliticParameter = new ServicePoliticsParameter();
                $newPoliticParameter->min_hour = 1;
                $newPoliticParameter->max_hour = 2;
                $newPoliticParameter->service_politics_id = $newPolitic->id;
                $newPoliticParameter->service_penalty_id = 2;
                $newPoliticParameter->amount = 50;
                $newPoliticParameter->tax = 0;
                $newPoliticParameter->service = 0;
                $newPoliticParameter->created_at = $created_at;
                $newPoliticParameter->updated_at = $created_at;
                $newPoliticParameter->save();

                // Progress politica de cancelación
                ProgressBar::updateOrCreate([
                    'slug' => 'service_progress_politics_cancellations',
                    'value' => 10,
                    'type' => 'service',
                    'object_id' => $serviceId
                ]);

                // Disponibilidad
                $date_from = Carbon::createFromFormat('d/m/Y', date('d/m/Y'))->setTimezone('America/Lima');
                $date_to = Carbon::createFromFormat('d/m/Y', '31/12/2020')->setTimezone('America/Lima');
                $difference_days = $date_from->diffInDays($date_to->addDay());
                for ($i = 0; $i <= $difference_days; $i++) {
                    $date = ($i === 0) ? $date_from : $date_from->addDay();
                    DB::table('service_inventories')->insert([
                        'day' => $date->day,
                        'date' => $date->format('Y-m-d'),
                        'inventory_num' => 100,
                        'total_booking' => 0,
                        'total_canceled' => 0,
                        'locked' => false,
                        'service_id' => $serviceId,
                        'created_at' => $created_at,
                        'updated_at' => $created_at
                    ]);
                }

                // Progress Disponibilidad
                ProgressBar::updateOrCreate([
                    'slug' => 'service_progress_availability',
                    'value' => 10,
                    'type' => 'service',
                    'object_id' => $serviceId
                ]);


            }
        });

    }
}
