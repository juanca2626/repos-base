<?php
namespace App\Http\Traits;

use App\Client;
use App\Country;
use App\Currency;
use App\Language;
use App\Markup;
use App\ProgressBar;
use App\Service;
use App\EquivalenceService;
use App\MasterService;
use App\ServiceOrigin;
use App\ServiceDestination;
use App\ServiceTranslation;
use App\ServiceClient;
use App\State;
use App\Translation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait Equivalence
{
    /**
     * @param $equivalences
     * @param $reimport | boolean | Si es chancar la composicion si existe o solo nuevos
     * @return array
     */
    private function insert_equivalences($equivalences, $reimport){

        $errors = [];
        $created_at = date("Y-m-d H:i:s");

        $langs = Language::where('state', 1)->get();
        $currency = Currency::where('iso', 'USD')->first();
        $country_id = Country::where('iso', 'PE')->first()->id;
        $state_id = State::where('iso', 'LIM')->where('country_id', $country_id)->first()->id;
        $error_equivalence = 0;
        $message = "";

        foreach ($equivalences as $equivalence) {

            foreach ($equivalence->composition as $composition) {
                $master_service = MasterService::where('code', $composition->codsvs)->first();
                if( $master_service ) {
                    $composition->master_service_id = $master_service->id;
                } else {
                    $composition->master_service_id = "";
                    $error_equivalence++;
                }
            }

            if( $error_equivalence > 0 ){
                array_push( $errors, $equivalence );
                $message = "Los servicios maestros del nodo 'errors', no existen";
                break;
            }
            else{
                $message = "Importación correcta";
                $new_ = false;
                $service = Service::where('equivalence_aurora', $equivalence->nroref)->withTrashed()->first();
                if( !$service ){
                    $new_ = true;

                    $equivalence->descri = (($equivalence->descri) === null) ? "Texto de prueba" : $equivalence->descri;

                    $service = new Service();
                    $service->aurora_code = $equivalence->codcli;
                    $service->name = $equivalence->descri;
                    $service->currency_id = $currency->id;
                    $service->latitude = 0;
                    $service->longitude = 0;
                    $service->qty_reserve = 1;
                    $service->equivalence_aurora = $equivalence->nroref;
                    $service->affected_igv = 1;
                    $service->allow_guide = false;
                    $service->allow_child = false;
                    $service->allow_infant = false;
                    $service->limit_confirm_hours = 0;
                    $service->unit_duration_limit_confirmation = 1;
                    $service->infant_min_age = 1;
                    $service->infant_max_age = 1;
                    $service->include_accommodation = 0;
                    $service->unit_id = 1;
                    $service->unit_duration_id = 1;
                    $service->service_type_id = 3;
                    $service->classification_id = 1;
                    $service->service_sub_category_id = 1;
                    $service->duration = 1;
                    $service->pax_min = 1;
                    $service->pax_max = $equivalence->nropax;
                    $service->min_age = 1;
                    $service->status = 0;
                    $service->user_id = 1;
//                    $service->service_group_id = 1;
                    $service->date_solicitude = Carbon::now();
                }

                $service->status_ifx = $equivalence->status;
                $service->pax_max_ifx = $equivalence->nropax;
                $service->language_iso_ifx = $equivalence->idioma;
                $service->description_ifx = $equivalence->descri;
                $service->type_ifx = trim($equivalence->grupo);
                $service->save();

                if( $new_ ){
                    //Origin
                    $serviceOrigin = new ServiceOrigin();
                    $serviceOrigin->service_id = $service->id;
                    $serviceOrigin->country_id = $country_id;
                    $serviceOrigin->state_id = $state_id;
                    $serviceOrigin->city_id = null;
                    $serviceOrigin->zone_id = null;
                    $serviceOrigin->save();
                    //Destino
                    $serviceDestiny = new ServiceDestination();
                    $serviceDestiny->service_id = $service->id;
                    $serviceDestiny->country_id = $country_id;
                    $serviceDestiny->state_id = $state_id;
                    $serviceDestiny->city_id = null;
                    $serviceDestiny->zone_id = null;
                    $serviceDestiny->save();

                    foreach ($langs as $lang) {
                        $service_translation = new ServiceTranslation();
                        $service_translation->language_id = $lang->id;
                        $service_translation->service_id = $service->id;
                        $service_translation->name = $equivalence->descri;
                        $service_translation->name_commercial = "";
                        $service_translation->description = "";
                        $service_translation->description_commercial = "";
                        $service_translation->summary = "";
                        $service_translation->summary_commercial = "";
                        $service_translation->itinerary = "";
                        $service_translation->itinerary_commercial = "";
                        $service_translation->save();
                    }
                    // Progress detalles
                    ProgressBar::firstOrCreate(
                        [
                            'slug' => 'service_progress_details',
                            'value' => 10,
                            'type' => 'service',
                            'object_id' => $service->id
                        ]
                    );
                    // Progress descripcion
                    ProgressBar::firstOrCreate(
                        [
                            'slug' => 'service_progress_descriptions',
                            'value' => 10,
                            'type' => 'service',
                            'object_id' => $service->id
                        ]
                    );
                    // Progress localizacion
                    ProgressBar::firstOrCreate(
                        [
                            'slug' => 'service_progress_location',
                            'value' => 5,
                            'type' => 'service',
                            'object_id' => $service->id
                        ]
                    );
                    $this->serviceLockClientEcommerce($service->id);
                }

                if( $new_ || $reimport === 1 ) {
                    EquivalenceService::where('service_id', $service->id)->delete();
                    foreach ($equivalence->composition as $composition) {
                        EquivalenceService::insert([
                            "service_id" => $service->id,
                            "master_service_id" => $composition->master_service_id,
                            "incremental" => $composition->nroite,
                            "date_in" => $composition->fecin,
                            "date_out" => $composition->fecout,
                            "status_ifx" => $composition->estado,
                            "nights" => $composition->cansvs,
                            "created_at" => $created_at,
                            "updated_at" => $created_at,
                        ]);
                    }
                }

                // Poner los textos siempre y cuando encuentre un unico svs en su composición
                if( count($equivalence->composition) == 1 ){

                    foreach ($langs as $lang) {
                        $service_translation = ServiceTranslation::where('language_id', $lang->id)
                            ->where('service_id', $service->id)->first();
                        if( $service_translation ) {
                            ///////////////
                            $translation_itinerary_master_service = Translation::where('type', 'master_service')
                                ->where('slug', 'itinerary')
                                ->where('object_id', $equivalence->composition[0]->master_service_id)
                                ->where('language_id', $lang->id)
                                ->first();
                            if ($translation_itinerary_master_service) {
                                if ($service_translation->itinerary === null || trim($service_translation->itinerary) === "") {
                                    $service_translation->itinerary = $translation_itinerary_master_service->value;
                                }
                                if ($service_translation->itinerary_commercial === null || $service_translation->itinerary_commercial == "") {
                                    $service_translation->itinerary_commercial = $translation_itinerary_master_service->value;
                                }
                            }
                            /////////////
                            $translation_skeleton_master_service = Translation::where('type', 'master_service')
                                ->where('slug', 'skeleton')
                                ->where('object_id', $equivalence->composition[0]->master_service_id)
                                ->where('language_id', $lang->id)
                                ->first();
                            if ($translation_skeleton_master_service) {
                                if ($service_translation->summary === null || $service_translation->summary == "") {
                                    $service_translation->summary = $translation_skeleton_master_service->value;
                                }
                                if ($service_translation->summary_commercial === null || $service_translation->summary_commercial == "") {
                                    $service_translation->summary_commercial = $translation_skeleton_master_service->value;
                                }
                            }

                            $service_translation->save();
                        }

                    }
                }

            }
        }

        return ["success"=>!($error_equivalence > 0), "data"=>$equivalences, "errors"=>$errors, "message"=>$message];

    }

    public function update_equivalence_services($equivalence_aurora, $equivalence_services){

        $success = true;
        $errors = [];
        $created_at = date("Y-m-d H:i:s");
        $message = "Guardado correctamente";

        $equivalence = Service::where('equivalence_aurora', $equivalence_aurora)->first();

        if($equivalence){

            $error_equivalence = 0;
            foreach ($equivalence_services as $composition) {
                $master_service = MasterService::where('code', $composition->codsvs)->first();
                if( $master_service ) {
                    $composition->master_service_id = $master_service->id;
                } else {
                    array_push( $errors, $composition->codsvs );
                    $composition->master_service_id = "";
                    $error_equivalence++;
                }
            }

            if( $error_equivalence > 0 ){
                $success = false;
                $message = "Los servicios maestros del nodo 'errors', no existen";
            } else{
                EquivalenceService::where('service_id', $equivalence->id)->delete();
                foreach ($equivalence_services as $composition) {
                    EquivalenceService::insert([
                        "service_id" => $equivalence->id,
                        "master_service_id" => $composition->master_service_id,
                        "incremental" => $composition->nroite,
                        "date_in" => $composition->fecin,
                        "date_out" => $composition->fecout,
                        "status_ifx" => $composition->estado,
                        "nights" => $composition->cansvs,
                        "created_at" => $created_at,
                        "updated_at" => $created_at,
                    ]);
                }
            }
        } else {
            $success = false;
            $message = "Equivalencia no existente";
        }

        return ["success"=>$success, "data"=>["nroref"=>$equivalence_aurora, "composition"=>$equivalence_services],
            "errors"=>$errors, "message"=>$message];
    }

    public function serviceLockClientEcommerce($service_id)
    {
        try {
            DB::transaction(function () use ($service_id) {
                $client_ecommerce = Client::where('ecommerce', 1)->get(['id']);
                foreach ($client_ecommerce as $client) {
                    $client_periods = Markup::where('period', '>=', Carbon::now()->format('Y'))->where('client_id',
                        $client->id)->get(['period']);
                    foreach ($client_periods as $period) {
                        $service_client = new ServiceClient();
                        $service_client->period = $period->period;
                        $service_client->client_id = $client->id;
                        $service_client->service_id = $service_id;
                        $service_client->save();
                    }
                }
            });
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function search_composition($service_id, $with_components){

        try{
            $equivalence_services = EquivalenceService::where('service_id', $service_id)
                ->with(['master_service'])
                ->get();

            $service_parent_codes = [];
            foreach ($equivalence_services as $equivalence_service){
                array_push( $service_parent_codes, $equivalence_service->master_service->code );
            }

            if( $with_components && count($equivalence_services) > 0 ){
                $client = new \GuzzleHttp\Client(["verify"=>false]);
                $response = $client->request('POST',
                    config('services.stella.domain') . 'api/v1/services/services/codes/components', [
                        "json" => [
                            'codes' => $service_parent_codes
                        ]
                    ]);
                $response = json_decode( $response->getBody()->getContents() );

                foreach( $response->data as $service ){
                    foreach ($equivalence_services as $equivalence_service){
                        if( $service->code == $equivalence_service->master_service->code ){
                            $equivalence_service->components = $service->components;
                        }
                    }
                }
            }
            return $equivalence_services;

        }catch (\Exception $exception){
            return ['success' => false, 'error' => $exception->getMessage()];
        }

    }

}
