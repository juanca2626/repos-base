<?php
namespace App\Http\Traits;

use App\Language;
use App\MasterService as MasterServiceModel;
use App\Translation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait MasterService
{

    private function insert_master_services($services){

        DB::transaction(function () use (
            $services
        ) {

            $languages_isos_array = [];

            foreach ($services as $service) {

                $ms_ = MasterServiceModel::where('code', $service["code"])->first();

                if($ms_){
                    $ms = $ms_;
                } else{
                    $ms = new MasterServiceModel();
                    $ms->code = $service["code"];
                }
                $ms->classification = $service["classification"];
                $ms->city_in_iso = $service["city_in_iso"];
                $ms->city_out_iso = $service["city_out_iso"];
                $ms->description = $service["description"];
                $ms->description_large = $service["description_large"];
                $ms->type_iso = $service["type_iso"];
                $ms->country_iso = $service["country_iso"];
                $ms->provider_code_request = $service["provider_code_request"];
                $ms->provider_code_bill = $service["provider_code_bill"];
                $ms->provider_code_voucher = $service["provider_code_voucher"];
                $ms->unit = $service["unit"];
                $ms->pricing_code_time = $service["pricing_code_time"];
                $ms->pricing_code_sale = $service["pricing_code_sale"];
                $ms->allow_provider_email = $service["allow_provider_email"];
                $ms->allow_voucher = $service["allow_voucher"];
                $ms->allow_itinerary = $service["allow_itinerary"];
                $ms->assignable = $service["assignable"];
                $ms->nights = (int)($service["nights"]);
                $ms->allow_markup = $service["allow_markup"];
                $ms->accounting_account_sale = $service["accounting_account_sale"];
                $ms->accounting_account_cost = $service["accounting_account_cost"];
                $ms->intermediation = $service["intermediation"];
                $ms->status_ifx = $service["status_ifx"];
                $ms->status = (trim( $service["status_ifx"]) === "RETIRA") ? 0 : 1;
                $ms->codaux = $service["codaux"];
                $ms->allow_export = $service["allow_export"];
                $ms->save();

                if( isset($service["itinerary"]) ){

                    $itinerary_in_languages_array = [];
                    $itinerary_in_languages = [];

                    if( count($service["itinerary"] ) > 0 ){
                        Translation::where('type', 'master_service')
                            ->where("object_id", $ms->id)
                            ->where("slug", "itinerary")
                            ->delete();
                    }

                    foreach ($service["itinerary"] as $itinerary) {
                        $itinerary = (array)$itinerary;
                        if( isset($itinerary_in_languages_array[$itinerary['idioma']]) ){
                            $itinerary_in_languages[$itinerary_in_languages_array[$itinerary['idioma']]]["value"] .= " " .
                                $itinerary["texto"];
                        } else {

                            if( isset($languages_isos_array[$itinerary['idioma']]) ){
                                $language_id_ = $languages_isos_array[$itinerary['idioma']];
                            } else{
                                $language_ = Language::where('iso', strtolower($itinerary['idioma']))->first();
                                $languages_isos_array[$itinerary['idioma']] = $language_->id;
                                $language_id_ = $language_->id;
                            }

                            $itinerary_in_languages_array[$itinerary['idioma']] = count($itinerary_in_languages);
                            array_push( $itinerary_in_languages, [
                                "language_iso" => $itinerary['idioma'],
                                "language_id" => $language_id_,
                                "value" => $itinerary["texto"]
                            ]);
                        }
                    }

                    foreach ($itinerary_in_languages as $itinerary) {
                        $newTranslation = new Translation();
                        $newTranslation->type = "master_service";
                        $newTranslation->object_id = $ms->id;
                        $newTranslation->slug = "itinerary";
                        $newTranslation->value = $itinerary["value"];
                        $newTranslation->language_id = $itinerary["language_id"];
                        $newTranslation->save();
                    }

//                    $service['itinerary'] = $itinerary_in_languages;

                }

                if( isset($service["skeleton"]) ){

                    $skeleton_in_languages_array = [];
                    $skeleton_in_languages = [];

                    if( count($service["skeleton"] ) > 0 ){
                        Translation::where('type', 'master_service')
                            ->where("object_id", $ms->id)
                            ->where("slug", "skeleton")
                            ->delete();
                    }

                    foreach ($service["skeleton"] as $skeleton) {
                        $skeleton = (array)$skeleton;
                        if( isset($skeleton_in_languages_array[$skeleton['idioma']]) ){
                            $skeleton_in_languages[$skeleton_in_languages_array[$skeleton['idioma']]]["value"] .= " " .
                                $skeleton["texto"];
                        } else {

                            if( isset($languages_isos_array[$skeleton['idioma']]) ){
                                $language_id_ = $languages_isos_array[$skeleton['idioma']];
                            } else{
                                $language_ = Language::where('iso', strtolower($skeleton['idioma']))->first();
//                                if(!$language_){
//                                    throw new \Exception($skeleton['idioma']);
//                                }
                                $languages_isos_array[$skeleton['idioma']] = $language_->id;
                                $language_id_ = $language_->id;
                            }

                            $skeleton_in_languages_array[$skeleton['idioma']] = count($skeleton_in_languages);
                            array_push( $skeleton_in_languages, [
                                "language_iso" => $skeleton['idioma'],
                                "language_id" => $language_id_,
                                "value" => $skeleton["texto"]
                            ]);
                        }
                    }

                    foreach ($skeleton_in_languages as $skeleton) {
                        $newTranslation = new Translation();
                        $newTranslation->type = "master_service";
                        $newTranslation->object_id = $ms->id;
                        $newTranslation->slug = "skeleton";
                        $newTranslation->value = ($skeleton["value"]===null) ? "" : $skeleton["value"];
                        $newTranslation->language_id = $skeleton["language_id"];
                        $newTranslation->save();
                    }
                }

            }
        });

        return ["success"=>true, "data"=>$services];

    }

}
