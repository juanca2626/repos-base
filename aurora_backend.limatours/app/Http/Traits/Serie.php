<?php
namespace App\Http\Traits;

use App\SerieServicePrice;
use App\MasterSheet;
use App\MasterSheetDay;
use App\MasterSheetService;
use App\Message;
use App\SerieCategory;
use App\SerieNote;
use App\SerieRange;
use App\SerieService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait Serie
{
    /**
     * @param $service_codes
     * [
     *  [
            "id" => 605,
            "code" => 'LIMMAR',
            "parent_code" => "LIN401",
            "type_code_service" => "component",
            "item_number" => 2,
            "date" => "2020-09-14",
        ],
     * ]
     * @param $serie_range_id
     */
    private function get_and_store_prices($service_codes, $serie_range_id){

        $range = SerieRange::find($serie_range_id);

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST',
            config('services.stella.domain'). 'api/v1/services/services/codes/prices', [
                "json" => [
                    'codes' => $service_codes,
                    'min' => $range->min
                ]
            ]);
        $response = json_decode( $response->getBody()->getContents() );
//            return $response->data;
//            return response()->json( $response->data );

        foreach( $response->data as $service ){
            if( count( $service->prices ) > 0 ){
                foreach( $service->prices as $p => $price ){
                    $new_serie_service_price = new SerieServicePrice();
                    $new_serie_service_price->serie_service_id = $service->id;
                    $new_serie_service_price->serie_range_id = $serie_range_id;
                    $new_serie_service_price->date = $service->date;
                    $new_serie_service_price->base_code = $price->bastar;
                    $new_serie_service_price->base_name = $price->bastar_descri;
                    $new_serie_service_price->status = ($p===0);
                    $new_serie_service_price->amount = $price->valor;
                    $new_serie_service_price->amount_type = 'original';
                    $new_serie_service_price->amount_recalculated = false;
                    $new_serie_service_price->save();
               }
            } else {
                $new_serie_service_price = new SerieServicePrice();
                $new_serie_service_price->serie_service_id = $service->id;
                $new_serie_service_price->serie_range_id = $serie_range_id;
                $new_serie_service_price->date = $service->date;
                $new_serie_service_price->base_code = "";
                $new_serie_service_price->base_name = "";
                $new_serie_service_price->status = true;
                $new_serie_service_price->amount = 0;
                $new_serie_service_price->amount_type = 'manual';
                $new_serie_service_price->amount_recalculated = false;
                $new_serie_service_price->save();
            }
        }

        return true;

    }

    private function do_import_master_sheet( $master_sheet_id, $serie_id, $name, $categories, $include_messages ){

        $master_sheet_days_ids = MasterSheetDay::where('master_sheet_id', $master_sheet_id)
            ->orderBy('date_in')
            ->pluck('id');

        $master_sheet_services = MasterSheetService::whereIn('master_sheet_day_id', $master_sheet_days_ids)
            ->with(['day'])
            ->get();

        $service_parent_codes = [];
        foreach ( $master_sheet_services as $service ){
            if( $service->type_service === 'service' && $service->service_code_stela !== null
                && $service->service_code_stela !== '' ){
                array_push( $service_parent_codes, $service->service_code_stela );
            }
        }
        // $service_parent_codes : ["LIN111","LIN432","AQV144","LIMTU6","LIN241","LINP41","NAZTU3","NAZX10","PCSTU2",
        //"AQV411","AQVP09", "COL8AC","PUV528","PUV241","PUNTR1","CUZ141","CUZ419","CUZMBT","CUZ581","MPIT13","UR1310",
        //"UR1319", "UR1500","MPI8MP","MPI725","UR1726","CUZ211"]

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST',
            config('services.stella.domain'). 'api/v1/services/services/codes/components', [
                "json" => [
                    'codes' => $service_parent_codes
                ]
            ]);
        $response = json_decode( $response->getBody()->getContents() );
//            return $response->data;
//            return response()->json( $response->data );
        $array_services = [];
        foreach( $response->data as $service ){
            $array_services[$service->code]['components'] = $service->components;
            $array_services[$service->code]['type_code_service'] =
                ( count($service->components ) === 0 )
                    ? 'direct'
                    : 'parent';
        }

        DB::transaction(function () use ($master_sheet_id, $serie_id, $name, $master_sheet_services, $categories,
            $include_messages, $array_services) {

            $master_sheet = MasterSheet::where('id', $master_sheet_id)->with('messages')->first();

            if( $serie_id === null ) {
                $serie = new \App\Serie();
                $serie->name = $name;
                $serie->date_start = $master_sheet->date_out;
                $serie->user_id = Auth::user()->id;
                $serie->master_sheet_id = $master_sheet_id;
                $serie->see_previous_messages = $include_messages;
                $serie->save();
                $serie_id = $serie->id;
            } else {
                $serie = \App\Serie::find($serie_id);
                $serie->name = $name;
                $serie->master_sheet_id = $master_sheet_id;
                $serie->see_previous_messages = $include_messages;
                $serie->save();
            }

            foreach ( $categories as $category ){
                $new_serie_category = new SerieCategory();
                $new_serie_category->serie_id = $serie_id;
                $new_serie_category->type_class_id = $category;
                $new_serie_category->save();

                foreach ( $master_sheet_services as $service ){
                    $new_service = new SerieService();
                    $new_service->serie_category_id = $new_serie_category->id;
                    $new_service->code = $service->service_code_stela;
                    $new_service->description_ES = $service->description_ES;
                    $new_service->description_EN = $service->description_EN;
                    $new_service->description_PT = $service->description_PT;
                    $new_service->description_IT = $service->description_IT;

                    if( $service->type_service === 'service' &&
                        $service->service_code_stela !== null && $service->service_code_stela !== '' ){
                        $new_service->type_code_service = $array_services[$service->service_code_stela]['type_code_service'];
                        $new_service->date = $service->day->date_in;
                        $new_service->type_service = $service->type_service;
                        $new_service->status = 1;
                        $new_service->save();
                        if( $array_services[$service->service_code_stela]['type_code_service'] === 'parent' ){
                            foreach ( $array_services[$service->service_code_stela]['components'] as $component ){
                                $new_component = new SerieService();
                                $new_component->serie_category_id = $new_serie_category->id;
                                $new_component->code = $component->codsvs;
                                $new_component->item_number = $component->nroite;
                                $new_component->parent_id = $new_service->id;
                                $new_component->type_code_service = 'component';
                                $new_component->date = $service->day->date_in;
                                $new_component->type_service = $service->type_service;
                                $new_component->description_ES = $component->descri_es;
                                $new_component->description_EN = $component->descri_en;
                                $new_component->description_PT = $component->descri_pt;
                                $new_component->description_IT = $component->descri_it;
                                $new_component->status = 1;
                                $new_component->save();
                            }
                        }
                    }
                    else {
                        $new_service->date = $service->day->date_in;
                        $new_service->type_service = $service->type_service;
                        if( $service->type_service === 'hotel' ){
                            $new_service->status = $service->status;
                        } else {
                            $new_service->status = 1;
                        }
                        $new_service->save();
                    }

                }

            }

            Message::where('entity', 'serie')->where('object_id', $serie_id)
                ->where('created_at', '<=', $serie->created_at)
                ->delete();

            if( $include_messages ){
                $messages_new_ids = [];
                $_messages_new_ids = [];
                foreach ( $master_sheet->messages as $message ){
                    $new_message = new Message();
                    $new_message->user_id = $message->user_id;
                    $new_message->entity = "serie";
                    $new_message->object_id = $serie_id;
                    $new_message->message = $message->message;
                    $new_message->reply_id = $message->reply_id;
                    $new_message->attached = $message->attached;
                    $new_message->created_at = $message->created_at;
                    $new_message->updated_at = $message->updated_at;
                    $new_message->save();

                    array_push( $messages_new_ids, $new_message->id );
                    $_messages_new_ids[$message->id] = $new_message->id;
                }

                $_new_messages = Message::whereIn('id', $messages_new_ids)->get();
                foreach ( $_new_messages as $_message ){
                    if( $_message->reply_id !== '' && $_message->reply_id !== null ){
                        $_message->reply_id = $_messages_new_ids[$_message->reply_id];
                        $_message->save();
                    }
                }
            }

        });

        $data = [
            'success' => true
        ];

        return $data;

    }

    private function do_clone($serie_id_clone, $serie_id, $name, $categories, $inc_messages, $inc_notes, $inc_reminders){

        DB::transaction(function () use ($serie_id_clone, $serie_id, $name, $categories, $inc_messages, $inc_notes,
            $inc_reminders) {

            if( $serie_id === null ){
                $serie_clone = \App\Serie::find($serie_id_clone);

                $serie = new \App\Serie();
                $serie->name = $name;
                $serie->date_start = $serie_clone->date_start;
                $serie->user_id = Auth::user()->id;
                $serie->see_previous_messages = $inc_messages;
                $serie->save();

                $serie_id = $serie->id;
            } else {
                $serie = \App\Serie::find($serie_id);
                $serie->name = $name;
                $serie->see_previous_messages = $inc_messages;
                $serie->save();
            }

            $_categories = SerieCategory::whereIn('id', $categories)
                ->with(['services'])
                ->get();

            foreach ( $_categories as $category ){
                $new_serie_category = new SerieCategory();
                $new_serie_category->serie_id = $serie_id;
                $new_serie_category->type_class_id = $category->type_class_id;
                $new_serie_category->save();

                $services_new_ids = [];
                $_services_new_ids = [];
                foreach ( $category->services as $service ){
                    $new_service = new SerieService();
                    $new_service->serie_category_id = $new_serie_category->id;
                    $new_service->code = $service->code;
                    $new_service->type_code_service = $service->type_code_service;
                    $new_service->item_number = $service->item_number;
                    $new_service->parent_id = $service->parent_id;
                    $new_service->date = $service->date;
                    $new_service->type_service = $service->type_service;
                    $new_service->status = $service->status;
                    $new_service->description_ES = $service->description_ES;
                    $new_service->description_EN = $service->description_EN;
                    $new_service->description_PT = $service->description_PT;
                    $new_service->description_IT = $service->description_IT;
                    $new_service->save();

                    array_push( $services_new_ids, $new_service->id );
                    $_services_new_ids[$service->id] = $new_service->id;
                }

                $_new_services = SerieService::whereIn('id', $services_new_ids)->get();
                foreach ( $_new_services as $_service ){
                    if( $_service->parent_id !== '' && $_service->parent_id !== null ){
                        $_service->parent_id = $_services_new_ids[$_service->parent_id];
                        $_service->save();
                    }
                }
            }

            if( $inc_messages ){
                Message::where('entity', 'serie')->where('object_id', $serie_id)->delete();
                $_messages = Message::where('entity', 'serie')->where('object_id', $serie_id_clone)->get();

                $messages_new_ids = [];
                $_messages_new_ids = [];
                foreach ( $_messages as $message ){
                    $new_message = new Message();
                    $new_message->user_id = $message->user_id;
                    $new_message->entity = "serie";
                    $new_message->object_id = $serie_id;
                    $new_message->message = $message->message;
                    $new_message->reply_id = $message->reply_id;
                    $new_message->attached = $message->attached;
                    $new_message->created_at = $message->created_at;
                    $new_message->updated_at = $message->updated_at;
                    $new_message->save();

                    array_push( $messages_new_ids, $new_message->id );
                    $_messages_new_ids[$message->id] = $new_message->id;
                }

                $_new_messages = Message::whereIn('id', $messages_new_ids)->get();
                foreach ( $_new_messages as $_message ){
                    if( $_message->reply_id !== '' && $_message->reply_id !== null ){
                        $_message->reply_id = $_messages_new_ids[$_message->reply_id];
                        $_message->save();
                    }
                }
            }

            if( $inc_notes ){
                $_notes = SerieNote::where('serie_id', $serie_id_clone)->get();
                foreach ( $_notes as $note ){
                    $new_note = new SerieNote();
                    $new_note->serie_id = $serie_id;
                    $new_note->note_id = $note->note_id;
                    $new_note->save();
                }
            }

            if( $inc_reminders ){
//                $_reminders = SerieNote::where('serie_id', $serie_id_clone)->get();
//                foreach ( $_notes as $note ){
//                    $new_note = new SerieNote();
//                    $new_note->serie_id = $serie_id;
//                    $new_note->note_id = $note->note_id;
//                    $new_note->save();
//                }
            }

        });

        $data = [
            'success' => true
        ];

        return $data;
    }

}
