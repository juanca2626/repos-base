<?php

namespace App\Http\Traits;

use App\Room;
use App\Hotel;
use App\Quote;
use App\Markup;
use App\Service;
use App\QuoteLog;
use App\RoomType;
use App\QuoteNote;
use Carbon\Carbon;
use App\QuoteRange;
use App\RatesPlans;
use App\QuotePeople;
use App\ServiceRate;
use App\QuoteService;
use App\ServiceChild;
use App\MarkupService;
use App\QuoteAgeChild;
use App\QuoteCategory;
use App\QuotePassenger;
use App\RatesPlansRooms;
use App\ServiceRatePlan;
use App\QuoteServiceRate;
use App\QuoteServiceRoom;
use App\ServiceInventory;
use App\QuoteDistribution;
use App\QuoteDynamicPrice;
use App\QuoteAccommodation;
use App\QuoteServiceAmount;
use App\Http\Traits\Services;
use App\QuoteDynamicSaleRate;
use App\RatesPlansCalendarys;
use App\QuoteServicePassenger;
use App\RatePlanRoomDateRange;
use Illuminate\Support\Facades\DB;
use App\QuoteDistributionPassenger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

trait Quotes
{
    use Services;

    private $object_id;
    private $quote;

    private function setObjectId($object_id)
    {
        $this->object_id = $object_id;
    }

    private function getObjectId()
    {
        return $this->object_id;
    }

    private function newQuote(
        $name,
        $date,
        $date_estimated,
        $nights,
        $service_type_id,
        $user_id,
        $categories,
        $ranges,
        $notes,
        $passengers,
        $people,
        $operation,
        $status,
        $markup,
        $discount,
        $discount_detail,
        $order_related,
        $order_position
    ) {
        DB::transaction(function () use (
            $name,
            $date,
            $date_estimated,
            $nights,
            $service_type_id,
            $user_id,
            $categories,
            $ranges,
            $notes,
            $passengers,
            $people,
            $operation,
            $status,
            $markup,
            $discount,
            $discount_detail,
            $order_related,
            $order_position
        ) {

            $today = date("Y-m-d H:i:s");
            $quote_id = DB::table('quotes')->insertGetId([
                'name' => $name,
                'date_in' => $date,
                'estimated_travel_date' => $date_estimated,
                'nights' => $nights,
                'service_type_id' => $service_type_id,
                'user_id' => $user_id,
                'markup' => $markup,
                'status' => $status,
                'discount' => $discount,
                'discount_detail' => $discount_detail,
                'order_related' => $order_related,
                'order_position' => $order_position,
                'operation' => $operation,
                'created_at' => $today
            ]);

            if ($operation == "ranges") {
                foreach ($ranges as $range) {
                    DB::table('quote_ranges')->insert([
                        'from' => $range["from"],
                        'to' => $range["to"],
                        'quote_id' => $quote_id,
                        "created_at" => \Carbon\Carbon::now(),
                        "updated_at" => \Carbon\Carbon::now()
                    ]);
                }
            }

            if ($operation == "passengers") {
                if (count($passengers) > 0) {
                    DB::table('quote_people')->insert([
                        'adults' => $people["adults"],
                        'child' => $people["child"],
                        'quote_id' => $quote_id,
                        "created_at" => \Carbon\Carbon::now()
                    ]);
                    foreach ($passengers as $passenger) {
                        DB::table('quote_passengers')->insert([
                            'first_name' => $passenger["first_name"],
                            'last_name' => $passenger["last_name"],
                            'gender' => $passenger["gender"],
                            'birthday' => $passenger["birthday"],
                            'document_number' => $passenger["document_number"],
                            'doctype_iso' => $passenger["doctype_iso"],
                            'country_iso' => $passenger["country_iso"],
                            'email' => $passenger["email"],
                            'phone' => $passenger["phone"],
                            'notes' => $passenger["notes"],
                            'type' => $passenger["type"],
                            'quote_id' => $quote_id,
                            "created_at" => \Carbon\Carbon::now()
                        ]);
                    }
                }
            }

            if (count($notes) > 0) {
                foreach ($notes as $note) {
                    $parent_note_id = DB::table('quote_notes')->insertGetId([
                        'comment' => $note["comment"],
                        'status' => $note["status"],
                        'quote_id' => $quote_id,
                        'user_id' => $note["user_id"],
                        "created_at" => \Carbon\Carbon::now(),
                    ]);

                    if (count($note["responses"]) > 0) {
                        foreach ($note["responses"] as $response) {
                            DB::table('quote_notes')->insert([
                                'parent_note_id' => $parent_note_id,
                                'comment' => $response["comment"],
                                'status' => $response["status"],
                                'quote_id' => $quote_id,
                                'user_id' => $response["user_id"],
                                "created_at" => \Carbon\Carbon::now(),
                            ]);
                        }
                    }
                }
            }

            foreach ($categories as $c) {
                DB::table('quote_categories')->insert([
                    'quote_id' => $quote_id,
                    'type_class_id' => $c,
                    'created_at' => $today
                ]);
            }

            if ($status == 2) {
                DB::table('quote_logs')->insert([
                    'quote_id' => $quote_id,
                    'type' => 'editing_quote',
                    'object_id' => $this->object_id,
                    'user_id' => Auth::id(),
                    'created_at' => $today
                ]);
            }

            $this->object_id = $quote_id;

        });
    }

    private function newQuoteInFront(
        $name,
        $date,
        $date_estimated,
        $service_type_id,
        $user_id,
        $categories,
        $ranges,
        $notes,
        $passengers,
        $people,
        $operation,
        $markup
    ) {

        // Creando el original
        $this->newQuote($name, $date, $date_estimated, 0, $service_type_id, $user_id, $categories, $ranges, $notes,
            $passengers,
            $people, $operation, 1, $markup, 0, '', null, null);

        // Creando el quote_open con el log "editing_quote"
        $this->newQuote($name, $date, $date_estimated, 0, $service_type_id, $user_id, $categories, $ranges, $notes,
            $passengers,
            $people, $operation, 2, $markup, 0, '', null, null);

        return true;
    }

    private function updateQuote(
        $name,
        $date,
        $service_type_id,
        $categories,
        $quote_id,
        $ranges,
        $notes,
        $passengers,
        $people,
        $operation
    ) {

        DB::transaction(function () use (
            $name,
            $date,
            $service_type_id,
            $categories,
            $quote_id,
            $ranges,
            $notes,
            $passengers,
            $people,
            $operation
        ) {
            $today = date("Y-m-d H:i:s");
            DB::table('quotes')
                ->where('id', $quote_id)
                ->update([
                    'name' => $name,
                    'date_in' => $date,
                    'service_type_id' => $service_type_id,
                    'operation' => $operation
                ]);
            DB::table('quote_people')
                ->where('id', $quote_id)
                ->update([
                    'adults' => $people["adults"],
                    'child' => $people["child"],
                ]);

            if ($operation == "ranges") {
                foreach ($ranges as $range) {
                    if ($range["id"] != null) {
                        $range_id = (int)$range["id"];
                        DB::table('quote_ranges')
                            ->where('id', $range_id)
                            ->update([
                                'from' => $range["from"],
                                'to' => $range["to"],
                                "updated_at" => \Carbon\Carbon::now()
                            ]);

                    } else {
                        DB::table('quote_ranges')->insert([
                            'from' => $range["from"],
                            'to' => $range["to"],
                            'quote_id' => $quote_id,
                            "created_at" => \Carbon\Carbon::now(),
                            "updated_at" => \Carbon\Carbon::now()
                        ]);
                    }
                }
            }
            if ($operation == "passengers") {
                foreach ($passengers as $passenger) {
                    if ($passenger["id"] != null) {
                        DB::table('quote_passengers')->where('quote_id', $quote_id)->where('id',
                            $passenger["id"])->update([
                            'first_name' => $passenger["first_name"],
                            'last_name' => $passenger["last_name"],
                            'gender' => $passenger["gender"],
                            'birthday' => $passenger["birthday"],
                            'document_number' => $passenger["document_number"],
                            'doctype_iso' => $passenger["doctype_iso"],
                            'country_iso' => $passenger["country_iso"],
                            'email' => $passenger["email"],
                            'phone' => $passenger["phone"],
                            'notes' => $passenger["notes"],
                            'type' => $passenger["type"],
                            'quote_id' => $quote_id,
                            "updated_at" => \Carbon\Carbon::now()
                        ]);
                    } else {
                        DB::table('quote_passengers')->insert([
                            'first_name' => $passenger["first_name"],
                            'last_name' => $passenger["last_name"],
                            'gender' => $passenger["gender"],
                            'birthday' => $passenger["birthday"],
                            'document_number' => $passenger["document_number"],
                            'doctype_iso' => $passenger["doctype_iso"],
                            'country_iso' => $passenger["country_iso"],
                            'email' => $passenger["email"],
                            'phone' => $passenger["phone"],
                            'notes' => $passenger["notes"],
                            'type' => $passenger["type"],
                            'quote_id' => $quote_id,
                            "created_at" => \Carbon\Carbon::now()
                        ]);
                    }
                }
            }
            if (count($notes) > 0) {
                foreach ($notes as $note) {
                    if ($note["id"] != null) {

                        DB::table('quote_notes')
                            ->where('id', $note["id"])
                            ->update([
                                'comment' => $note["comment"],
                                'status' => $note["status"],
                                "updated_at" => \Carbon\Carbon::now(),
                            ]);
                        if (count($note["responses"]) > 0) {
                            foreach ($note["responses"] as $response) {
                                if ($response["id"] != null) {
                                    DB::table('quote_notes')
                                        ->where('id', $response["id"])
                                        ->update([
                                            'comment' => $response["comment"],
                                            'status' => $response["status"],
                                            "updated_at" => \Carbon\Carbon::now(),
                                        ]);
                                } else {
                                    DB::table('quote_notes')->insert([
                                        'parent_note_id' => $note["id"],
                                        'comment' => $response["comment"],
                                        'status' => $response["status"],
                                        'quote_id' => $quote_id,
                                        'user_id' => $response["user_id"],
                                        "created_at" => \Carbon\Carbon::now(),
                                    ]);
                                }
                            }
                        }
                    } else {
                        $parent_note_id = DB::table('quote_notes')->insertGetId([
                            'comment' => $note["comment"],
                            'status' => $note["status"],
                            'quote_id' => $quote_id,
                            'user_id' => $note["user_id"],
                            "created_at" => \Carbon\Carbon::now(),
                        ]);

                        if (count($note["responses"]) > 0) {
                            foreach ($note["responses"] as $response) {
                                DB::table('quote_notes')->insert([
                                    'parent_note_id' => $parent_note_id,
                                    'comment' => $response["comment"],
                                    'status' => $response["status"],
                                    'quote_id' => $quote_id,
                                    'user_id' => $response["user_id"],
                                    "created_at" => \Carbon\Carbon::now(),
                                ]);
                            }
                        }
                    }
                }
            }
            $_categories = DB::table('quote_categories')->where('quote_id', $quote_id)->get();
            foreach ($categories as $c) {
                $exist = 0;
                foreach ($_categories as $_c) {
                    if ($_c->type_class_id == $c) {
                        $exist++;
                        break;
                    }
                }
                if ($exist == 0) {
                    DB::table('quote_categories')->insert([
                        'quote_id' => $quote_id,
                        'type_class_id' => $c,
                        'created_at' => $today
                    ]);
                }
            }

            // Eliminamos categs excedentes de bd
            foreach ($_categories as $_c) {
                $exist = 0;
                foreach ($categories as $c) {
                    if ($_c->type_class_id == $c) {
                        $exist++;
                        break;
                    }
                }
                if ($exist == 0) {
                    // PRIMERO BORRAR SERVICES
                    DB::table('quote_services')->where('quote_category_id', $_c->id)->delete();
                    DB::table('quote_categories')->where('id', $_c->id)->delete();
                }
            }
        });

        $quote = Quote::where('id', $quote_id)
            ->with(['logs', 'categories.type_class.translations'])
            ->first();

        return $quote;
    }

    /**
     * @param $quote_id_original //id de la cotizacion draft
     * @param $quote_id //Id de la cotizacion a actualizar | draft
     * @param bool $save_as //Variable si biene del metodo guardar como
     */
    private function replaceQuote($quote_id_original, $quote_id, $save_as = false)
    {
        try {
            $quote_original = $this->getQuote($quote_id_original, true);
            if ($quote_original) {
                $this->saveQuoteServices($quote_id, $quote_original, $save_as);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    // No funciona bien, en un punto chanca el date-in y los repite a todos revisar
    private function updateOrderAndDateServices($services, $date_pivot_new = "", $option = null)
    {
        DB::transaction(function () use ($services) {
            $index_service = 1;
            $date_pivot = "";
            foreach ($services as $service) {
                if($service["type"] == 'group_header'){
                    continue;
                }
                if ($date_pivot == "") {
                    $date_pivot = Carbon::createFromFormat('d/m/Y', $service["date_in"])->format('Y-m-d');
                    DB::table('quote_services')->where('id', $service["id"])->update([
                        'order' => $index_service
                    ]);
                } else {
                    $service_date_in = Carbon::createFromFormat('d/m/Y', $service["date_in"])->format('Y-m-d');

                    if ($date_pivot > $service_date_in) {

                        if ($service["extension_id"] != null) {
                            $this->updateDateInExtension($service["id"],
                                Carbon::createFromFormat('d/m/Y', $date_pivot)->format('Y-m-d'), $index_service);
                        } else {

                            if (($service["type"] == "service" || $service["type"] == "flight")) {
                                if ($service["locked"] == false) {
                                    DB::table('quote_services')->where('id', $service["id"])->update([
                                        'order' => $index_service,
                                        'date_in' => $date_pivot
                                    ]);
                                } else {
                                    DB::table('quote_services')->where('id', $service["id"])->update([
                                        'order' => $index_service,
                                    ]);
                                }

                            }
                            if ($service["type"] == "hotel") {
                                if ($service["locked"] == false) {
                                    DB::table('quote_services')->where('id', $service["id"])->update([
                                        'order' => $index_service,
                                        'date_in' => $date_pivot,
                                        'date_out' => Carbon::parse(Carbon::createFromFormat('Y-m-d', $date_pivot)->format('Y-m-d'))->addDays($service["nights"])
                                    ]);
                                } else {
                                    DB::table('quote_services')->where('id', $service["id"])->update([
                                        'order' => $index_service
                                    ]);
                                }

                            }
                        }
                    } else {
                        if ($date_pivot < $service_date_in) {
                            $date_pivot = $service_date_in;
                        }
                        DB::table('quote_services')->where('id', $service["id"])->update([
                            'order' => $index_service
                        ]);
                    }
                }

                $index_service++;
            }
        });

    }

    private function updateDateInServicesInAllCategories($quote_id)
    {
        DB::transaction(function () use ($quote_id) {
            $date_in = DB::table('quotes')->where('id', $quote_id)->first()->date_in;
            $quote_categories = DB::table('quote_categories')->where('quote_id', $quote_id)->get();
            foreach ($quote_categories as $category) {
                $services = DB::table('quote_services')->where('quote_category_id',
                    $category->id)->where('parent_service_id', null)->orderBy('date_in')->get();
                if ($services->count() > 0) {
                    $service_initial = DB::table('quote_services')->where('id', $services[0]->id)->first();
                    $date_initial = $service_initial->date_in;
                    for ($i = 0; $i < count($services); $i++) {
                        if ($i == 0) {
                            if ($services[$i]->extension_id != null) {
                                $this->updateDateInExtension($services[$i]->id, $date_in, $services[$i]->order);
                            } else {
                                if ($services[$i]->type == "service" || $services[$i]->type == "flight") {
                                    DB::table('quote_services')->where('id', $services[$i]->id)->update([
                                        'date_in' => $date_in,
                                        'date_out' => $date_in
                                    ]);
                                }
                                if ($services[$i]->type == "hotel") {
                                    DB::table('quote_services')->where('id', $services[$i]->id)->update([
                                        'date_in' => $date_in,
                                        'date_out' => Carbon::parse($date_in)->addDays($services[$i]->nights)->format('Y-m-d')
                                    ]);
                                }
                            }
                        } else {
                            $date_out_service = DB::table('quote_services')->where('id',
                                $services[$i]->id)->first()->date_in;
                            $days_difference = Carbon::parse($date_initial)->diffInDays(Carbon::parse($date_out_service));
                            $date_in_service = Carbon::parse($date_in)->addDays($days_difference)->format('Y-m-d');
                            if ($services[$i]->extension_id != null) {
                                $this->updateDateInExtension($services[$i]->id, $date_in_service, $services[$i]->order);
                            } else {
                                if ($services[$i]->type == "service" || $services[$i]->type == "flight") {
                                    DB::table('quote_services')->where('id', $services[$i]->id)->update([
                                        'date_in' => $date_in_service,
                                        'date_out' => $date_in_service
                                    ]);
                                }
                                if ($services[$i]->type == "hotel") {
                                    DB::table('quote_services')->where('id', $services[$i]->id)->update([
                                        'date_in' => $date_in_service,
                                        'date_out' => Carbon::parse($date_in_service)->addDays($services[$i]->nights)->format('Y-m-d')
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        });
    }

    private function add_years($fecha, $nAnios)
    {
        $days = $nAnios * 365;
        $nuevafecha = strtotime('+' . $days . 'day', strtotime($fecha));
        return date('Y-m-j', $nuevafecha);
    }

    private function updateNightsAndCities($quote_id)
    {

        DB::transaction(function () use ($quote_id) {

            $quote_categories = DB::table('quote_categories')->where('quote_id', $quote_id)->get();

            foreach ($quote_categories as $category) {
                $services = DB::table('quote_services')
                    ->where('quote_category_id', $category->id);

                $total_services = $services->count();

                if ($total_services > 0) {
                    $_services = $services->orderBy('date_in', 'asc')
                        ->orderBy('order', 'asc')->get();

                    $states = [];
                    $nights =
                        Carbon::parse($_services[0]->date_in)
                            ->diffInDays(Carbon::parse($_services[$total_services - 1]->date_out));
                    $update_quote = Quote::find($quote_id);
                    $update_quote->date_in = $_services[0]->date_in;
                    $update_quote->nights = $nights;
                    $update_quote->save();

                    foreach ($_services as $_serv) {

                        if ($_serv->type == 'service') {
                            $object = DB::table('service_destinations')
                                ->where('service_id', $_serv->object_id)->first();
                        } else {
                            $object = DB::table('hotels')->find($_serv->object_id);
                        }

                        if ($object) {
                            if (count($states) == 0) {
                                $states[0] = $object->state_id;
                            } else {
                                if ($states[count($states) - 1] != $object->state_id) {
                                    $states[count($states)] = $object->state_id;
                                }
                            }
                        }
                    }

                    if (count($states) > 0) {
                        DB::table('quote_destinations')->where('quote_id', $quote_id)->delete();

                        foreach ($states as $state_id) {
                            DB::table('quote_destinations')->insert([
                                'quote_id' => $quote_id,
                                'state_id' => $state_id
                            ]);
                        }

                    }

                    break;
                }
            }
        });

        return true;
    }

    private function updateAmountService($quote_service_id, $client_id, $quote_id)
    {
        DB::transaction(function () use ($quote_service_id, $client_id, $quote_id) {
            $quote_service = QuoteService::find($quote_service_id);
            if ($quote_service) {
                QuoteServiceAmount::where('quote_service_id', $quote_service->id)->forceDelete();
                if ($quote_service->type == 'service' and isset($quote_service->service_rate)) {
                    $available = ServiceInventory::where('service_rate_id',
                        $quote_service->service_rate->service_rate_id)
                        ->where('date', '>=',
                            Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->format('Y-m-d'))
                        ->where('date', '<=',
                            Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->format('Y-m-d'))
                        ->where('locked', '=', 0)
                        ->where('inventory_num', '>', 1)->get();

                    if ($available->count() == 0) {
                        QuoteService::where('id', $quote_service->id)->update([
                            'on_request' => 1,
                        ]);
                    } else {
                        QuoteService::where('id', $quote_service->id)->update([
                            'on_request' => 0,
                        ]);
                    }
                    // $markup = 0;

                    // $markup_rate_client = MarkupService::where('client_id', $client_id)
                    //     ->where('service_id', $quote_service->object_id)
                    //     ->where('period', Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->year)
                    //     ->first();

                    // if ($markup_rate_client == null) {
                    //     $_year = Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->year;
                    //     $markup_general = Markup::where('client_id', $client_id)->where('period', $_year)->first();
                    //     if ($markup_general) {
                    //         $markup = $markup_general->service;
                    //     } else {
                    //         throw new \Exception(trans('validations.quotes.client_does_not_have_markup_for_year', ['year' => $_year]), 404);
                    //     }
                    // } else {
                    //     $markup = $markup_rate_client->markup;
                    // }

                    // dd($markup_rate_client,$markup,$client_id);

                    $markup = Quote::where('id', $quote_id)->first()->markup;


                    $pax_amount = ServiceRatePlan::with('service_rate.service')->where('service_rate_id',
                        $quote_service->service_rate->service_rate_id)
                        ->where('date_from', '<=',
                            Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->format('Y-m-d'))
                        ->where('date_to', '>=',
                            Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->format('Y-m-d'))
                        ->where('pax_from', '<=', (int)$quote_service->adult + (int)$quote_service->child)
                        ->where('pax_to', '>=', (int)$quote_service->adult + (int)$quote_service->child)
                        ->first();


                    if ($pax_amount != null) {

                        $affected_markup = $pax_amount->service_rate->service->affected_markup;
                        if($affected_markup != "1"){
                            $markup = 0;
                        }

                        //Update Quote Price Dynamic                        
                        $price_adult_without_markup = $pax_amount->price_adult;
                        $price_adult = $pax_amount->price_adult + ($pax_amount->price_adult * ($markup / 100));


                        /*if ($quote_service->type == "service" || $quote_service->type == "hotel") {
                            $service = ($quote_service->type == "service") ? Service::find($quote_service->object_id) : Hotel::find($quote_service->object_id);
                        
                            if ($service && $service->price_dynamic == 1) {
                                $quote_dynamic_price = QuoteDynamicPrice::where('object_id', $quote_service->object_id)
                                    ->where('quote_id', $quote_id)
                                    ->where('client_id', $client_id)
                                    ->first();
                        
                                if ($quote_dynamic_price) {
                                    $price_adult_without_markup = $quote_dynamic_price->price_adl;
                                    $price_adult = $quote_dynamic_price->price_adl + ($quote_dynamic_price->price_adl * ($quote_dynamic_price->markup / 100));
                                } else {
                                    $price_adult_without_markup = 0;
                                    $price_adult = 0;
                                }
                            }
                        }*/


                        if($quote_service->type == "service"){
                            $flag_dynamic = 0;
                            $service_rates = ServiceRate::where('service_id', $quote_service->object_id)->get();
                            foreach ($service_rates as $service_rate) {
                                if ($service_rate->price_dynamic == 1) {
                                    $flag_dynamic = 1;
                                }                           
                            }
                            if($flag_dynamic == 1){
                                $quote_dynamic_price = QuoteDynamicPrice::where('object_id', $quote_service->object_id)->where('quote_id', $quote_id)->where('client_id', $client_id)->first();
                                if($quote_dynamic_price){
                                    $price_adult_without_markup = $quote_dynamic_price->price_adl;
                                    $price_adult = $quote_dynamic_price->price_adl + ($quote_dynamic_price->price_adl * ($quote_dynamic_price->markup / 100));
                                }else{
                                    $price_adult_without_markup = 0;
                                    $price_adult = 0;
                                }
                            }
                        }
                        

                        QuoteServiceAmount::insert([
                            'quote_service_id' => $quote_service->id,
                            'date_service' => $quote_service->date_in,
                            'price_per_night' => 0,
                            'price_per_night_without_markup' => 0,
                            'price_adult_without_markup' => $price_adult_without_markup,
                            'price_adult' => $price_adult,
                            'price_child_without_markup' => $pax_amount->price_child,
                            'price_child' => $pax_amount->price_child + ($pax_amount->price_child * ($markup / 100)),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    } else {
                        QuoteServiceAmount::insert([
                            'quote_service_id' => $quote_service->id,
                            'date_service' => $quote_service->date_in,
                            'price_per_night' => 0,
                            'price_per_night_without_markup' => 0,
                            'price_adult_without_markup' => 0,
                            'price_adult' => 0,
                            'price_child_without_markup' => 0,
                            'price_child' => 0,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    }
                }
            }
        });
    }

    /**
     * @param $quote_id integer
     * @param $client_id integer
     * @param $get_all_rates_services boolean
     */


    private function updateAmountAllServices($quote_id, $client_id, $get_all_rates_services = false)
    {
        $response = [];
        $quote = $this->getQuote($quote_id, true, $get_all_rates_services);
        // dd($quote->toArray());
        if ($quote) {
            if ($quote->operation == 'passengers') {
                foreach ($quote->categories as $category) {
                    foreach ($category->services as $quote_service) {
                        if ($quote_service->type == 'service') {
                            DB::table('quote_service_amounts')->where('quote_service_id', $quote_service->id)->delete();
                            $this->updateAmountService($quote_service->id, $client_id, $quote_id);
                        }
                        if ($quote_service->type == 'hotel') {
                           
                            DB::table('quote_service_amounts')->where('quote_service_id', $quote_service->id)->delete();

                            $quote_service_passengers_ids = QuoteServicePassenger::where('quote_service_id',
                                $quote_service->id)->pluck('quote_passenger_id');

                            $quote_service_passengers_quantity = QuotePassenger::where('quote_id',
                                $quote_id)->whereIn('type', ['ADL', 'CHD'])->whereIn('id',
                                $quote_service_passengers_ids)->get()->count();
                            $quote_service_passengers_quantity = $quote_service_passengers_quantity == 0 ? 1 : $quote_service_passengers_quantity;


                            $service_rate_plan_room = QuoteServiceRoom::where('quote_service_id',
                                $quote_service->id)->first();

                            if ($service_rate_plan_room != null and $service_rate_plan_room != '' and $quote_service_passengers_quantity > 0) {
                                $rate_plan_room = RatesPlansRooms::with('room.room_type')->where('id',
                                    $service_rate_plan_room->rate_plan_room_id)->first();

                                $rate_plan_room_date_range = null;
                                if ($rate_plan_room->channel_id == 6) {
                                    $rate_plan_room_date_range = RatePlanRoomDateRange::where('rate_plan_room_id', $rate_plan_room->id)
                                                ->whereDate('date_from', '<=', Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->format('Y-m-d') )
                                                ->whereDate('date_to', '>=', Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->format('Y-m-d'))
                                                ->first();
                                }
                                

                                $markup = Quote::where('id', $quote_id)->first()->markup;

                                if ($rate_plan_room != null and $rate_plan_room != '') {
                                    $rate_plan_calendars = RatesPlansCalendarys::where('rates_plans_room_id',
                                        $rate_plan_room->id)
                                        ->with('rate')
                                        ->where('date', '>=',
                                            Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->format('Y-m-d'))
                                        ->where('date', '<=', Carbon::createFromFormat('d/m/Y',
                                            $quote_service->date_out)->subDays(1)->format('Y-m-d'))
                                        ->get()->toArray();
                                    if (count($rate_plan_calendars) == $quote_service->nights and $quote_service_passengers_quantity > 0) {
                                        foreach ($rate_plan_calendars as $rate_plan_calendar) {

                                            if ($rate_plan_room->channel_id == 6) {
                                                $rate_channel_selected = '';
                                                //    dd($rate_plan_calendar["rate"]);
                                                //buscamos si encontramos tarifas por ocupacion
                                                foreach ($rate_plan_calendar["rate"] as $rates) {
                                                    if ($rates['num_adult'] == $rate_plan_room->room->room_type->occupation) {
                                                        $rate_channel_selected = $rates;
                                                        break;
                                                    }
                                                }

                                                if (!$rate_channel_selected) {
                                                    //buscamos si encontramos tarifas por habitacion
                                                    if (count($rate_plan_calendar["rate"]) > 0) {
                                                        $rate_channel_selected = $rate_plan_calendar["rate"][0];
                                                        $rate_channel_selected["price_adult"] = $rate_channel_selected["price_total"];
                                                    }
                                                }

                                                if ($rate_channel_selected) {
                                                    $price_per_night_without_markup = $rate_channel_selected["price_adult"];
                                                    $price_per_night_with_markup = ($price_per_night_without_markup * ($markup / 100)) + $price_per_night_without_markup;
                                                    // $price_child_without_markup = $rate_plan_room->channel_child_price;
                                                    // $price_teenagers_without_markup = $rate_plan_room->channel_infant_price;
                                                } else {
                                                    $price_per_night_without_markup = 0;
                                                    $price_per_night_with_markup = 0;
                                                    // $price_child_without_markup = $rate_plan_room->channel_child_price;
                                                    // $price_teenagers_without_markup = $rate_plan_room->channel_infant_price;
                                                }

                                                $price_child_without_markup = ($rate_plan_room_date_range) ? $rate_plan_room_date_range->price_child : 0;
                                                $price_teenagers_without_markup = ($rate_plan_room_date_range) ? $rate_plan_room_date_range->price_infant : 0;

                                            } else {
                                                $price_per_night_without_markup = ($rate_plan_calendar["rate"][0]["price_adult"] + $rate_plan_calendar["rate"][0]["price_extra"]);
                                                $price_per_night_with_markup = ($price_per_night_without_markup * ($markup / 100)) + $price_per_night_without_markup;

                                                $price_child_without_markup = $rate_plan_calendar["rate"][0]["price_child"];
                                                $price_teenagers_without_markup = $rate_plan_calendar["rate"][0]["price_infant"];
                                            }

                                            
                                            if($quote_service->type == "hotel"){           
                                                $flag_dynamic = 0;    
                                                $service_rates = RatesPlans::where('hotel_id', $quote_service->object_id)->get();
                                                foreach ($service_rates as $service_rate) {
                                                    if ($service_rate->price_dynamic == 1) {
                                                        $flag_dynamic = 1;
                                                    }                           
                                                }

                                                if($flag_dynamic == 1){     
                                                    $quote_dynamic_price = QuoteDynamicPrice::where('object_id', $quote_service->object_id)->where('quote_id', $quote_id)->where('client_id', $client_id)->first();
                                                        
                                                    if($quote_dynamic_price){
                                                        $price_per_night_without_markup = $quote_dynamic_price->price_adl;
                                                        $price_per_night_with_markup = ($quote_dynamic_price->price_adl * ($quote_dynamic_price->markup / 100)) + $quote_dynamic_price->price_adl;
                                                    }else{
                                                        $price_per_night_without_markup = 0;
                                                        $price_per_night_with_markup = 0;
                                                    }
                                                }
                                            }


                                            QuoteServiceAmount::insert([
                                                'quote_service_id' => $quote_service->id,
                                                'date_service' => Carbon::parse($rate_plan_calendar["date"])->format('d/m/Y'),
                                                'price_per_night_without_markup' => $price_per_night_without_markup,
                                                'price_per_night' => $price_per_night_with_markup,
                                                'price_adult_without_markup' => $price_per_night_without_markup / $quote_service_passengers_quantity,
                                                'price_adult' => $price_per_night_with_markup / $quote_service_passengers_quantity,
                                                'price_child_without_markup' => $price_child_without_markup,
                                                'price_child' => $price_child_without_markup + ($price_child_without_markup * ($markup / 100)),
                                                'price_teenagers_without_markup' => $price_teenagers_without_markup,
                                                'price_teenagers' => $price_teenagers_without_markup + ($price_teenagers_without_markup * ($markup / 100)),
                                                'created_at' => Carbon::now(),
                                                'updated_at' => Carbon::now()
                                            ]);

                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($quote->operation == "ranges") {
                $ranges = $quote->ranges;
                foreach ($quote->categories as $category) {
                    DB::table('quote_dynamic_sale_rates')->where('quote_category_id', $category->id)->delete();

                    $hotels_groups = [];
                    foreach ($category->services as $service) {

                        if ($service->type == "hotel") {
                            $ocuppations = $service->single + $service->double + $service->triple;
                            if ($ocuppations == 0) {
                                continue;
                            }
                            $hotel_amounts = $this->getAmountServiceHotel($service->id, $client_id, $quote_id, false);
                            $hotels_groups[$service->quote_category_id . "_" . $service->type . "_" . $service->object_id . "_" . $service->date_in]["quote_service_id"] = $service->id;
                            $hotels_groups[$service->quote_category_id . "_" . $service->type . "_" . $service->object_id . "_" . $service->date_in]["nights"] = $service->nights;
                            $hotels_groups[$service->quote_category_id . "_" . $service->type . "_" . $service->object_id . "_" . $service->date_in]["date_in"] = $service->date_in;
                            $hotels_groups[$service->quote_category_id . "_" . $service->type . "_" . $service->object_id . "_" . $service->date_in]["room_types"][$hotel_amounts["room_type"]] = $hotel_amounts["room_type"];
                            $hotels_groups[$service->quote_category_id . "_" . $service->type . "_" . $service->object_id . "_" . $service->date_in]["rate_meals"][$hotel_amounts["rate_meal"]] = $hotel_amounts["rate_meal"];
                            if ($service->single == "1") {
                                $hotels_groups[$service->quote_category_id . "_" . $service->type . "_" . $service->object_id . "_" . $service->date_in]["single"] = $hotel_amounts["simple"];
                            } elseif ($service->double == "1") {
                                $hotels_groups[$service->quote_category_id . "_" . $service->type . "_" . $service->object_id . "_" . $service->date_in]["double"] = $hotel_amounts["double"];
                            } elseif ($service->triple == "1") {
                                $hotels_groups[$service->quote_category_id . "_" . $service->type . "_" . $service->object_id . "_" . $service->date_in]["triple"] = $hotel_amounts["triple"];
                            }
                        }

                        foreach ($ranges as $range) {
                            if ($service->type == "hotel") {

                            }
                            if ($service->type == "service") {

                                $service_amount = $this->getAmountServiceService($service->id, $client_id, $range->from,
                                    $range->to, $quote_id, false);

                                if ($range->from == 1 && $range->to == 1) {
                                    $simple = $quote->accommodation->single == 1 ? $service_amount : 0;
                                    $double = 0;
                                    $triple = 0;
                                } else {
                                    $simple = $quote->accommodation->single == 1 ? $service_amount : 0;
                                    $double = $quote->accommodation->double == 1 ? $service_amount : 0;
                                    $triple = $quote->accommodation->triple == 1 ? $service_amount : 0;
                                }

                                $dynamic_rate_id = DB::table('quote_dynamic_sale_rates')->insertGetId([
                                    'date_service' => convertDate($service->date_in, '/', '-', 1),
                                    'quote_service_id' => $service->id,
                                    'pax_from' => $range->from,
                                    'pax_to' => $range->to,
                                    'simple' => $simple,
                                    'double' => $double,
                                    'triple' => $triple,
                                    'created_at' => Carbon::now(),
                                    'quote_category_id' => $category->id
                                ]);


                            }
                        }
                    }

                    // dd($hotels_groups);

                    foreach ($ranges as $range) {
                        foreach ($hotels_groups as $index => $hotel) {

                            $date_in = convertDate($hotel['date_in'], '/', '-', 1);

                            if ($range->from == 1 && $range->to == 1) {
                                $simple = isset($hotel['single']) ? $hotel['single'] : 0;
                                $double = 0;
                                $triple = 0;
                            } else {
                                $simple = isset($hotel['single']) ? $hotel['single'] : 0;
                                $double = isset($hotel['double']) ? $hotel['double'] / 2 : 0;
                                $triple = isset($hotel['triple']) ? $hotel['triple'] / 3 : 0;
                            }


                            for ($i = 0; $i < $hotel['nights']; $i++) {

                                $date_service = Carbon::parse($date_in)->addDays($i);

                                $dynamic_rate_id = DB::table('quote_dynamic_sale_rates')->insertGetId([
                                    'date_service' => $date_service,
                                    'quote_service_id' => $hotel['quote_service_id'],
                                    'pax_from' => $range->from,
                                    'pax_to' => $range->to,
                                    'simple' => $simple,
                                    'double' => $double,
                                    'triple' => $triple,
                                    'created_at' => Carbon::now(),
                                    'quote_category_id' => $category->id,
                                    'room_types' => implode(",", $hotel['room_types']),
                                    'rate_meals' => implode(",", $hotel['rate_meals']),
                                ]);

                            }

                        }
                    }


                }
            }
        }


        return $response;
    }

    public function getAmountServiceHotel($service_id, $client_id, $quote_id, $apply_round = true)
    {

        //Todo Obtengo el hotel de la cotizacion
        $service = DB::table('quote_services')->where('id', $service_id)->first();
        $prices = [
            "simple" => 0,
            "double" => 0,
            "triple" => 0,
            "room_type" => "",
            "rate_meal" => "",
        ];

        if ($service) {
            //Todo Obtengo las tarifas de la habitacion en la cotizacion
            $service_rate_plan_rooms_ids = DB::table('quote_service_rooms')->where('quote_service_id',
                $service->id)->pluck('rate_plan_room_id');

            //Todo Obtengo las tarifas a partir de $service_rate_plan_rooms_ids
            $rate_plan_rooms = DB::table('rates_plans_rooms')
                ->whereIn('rates_plans_rooms.id', $service_rate_plan_rooms_ids)
                ->get();

            $on_request = 0;

            //Todo Recorro las tarifas de las habitaciones y verifico el estado (on request) de la habitacion
            foreach ($rate_plan_rooms as $index => $rate_plan_room) {
                $inventories = DB::table('inventories')
                    ->where('rate_plan_rooms_id', $rate_plan_room->id)
                    ->where('date', '>=', $service->date_in)
                    ->where('date', '<=', Carbon::parse($service->date_out)->subDays(1)->format('Y-m-d'))
                    ->get();

                foreach ($inventories as $inventory) {
                    $real_inventory = $inventory->inventory_num - $inventory->total_booking;

                    if ($inventory->locked == 1 || $real_inventory <= 0) {
                        $on_request = 1;
                        DB::table('quote_services')->where('id', $service_id)->update([
                            'on_request' => 1,
                        ]);
                    }
                }
                if ($inventories->count() < $service->nights) {
                    $on_request = 1;
                    DB::table('quote_services')->where('id', $service_id)->update([
                        'on_request' => $on_request,
                    ]);
                }
                if ($on_request == 0) {
                    DB::table('quote_services')->where('id', $service_id)->update([
                        'on_request' => $on_request,
                    ]);
                }
                $markup = DB::table('quotes')->where('id', $quote_id)->first()->markup;
                $rate_plan_rooms[$index]->markup = $markup;
            }
            $simple = 0;
            $double = 0;
            $triple = 0;

            //Todo: Calculo el precio para la acomodacion en Simple
            foreach ($rate_plan_rooms as $rate_plan_room) {
                $room_type_id = DB::table('rooms')->where('id', $rate_plan_room->room_id)->first()->room_type_id;

                $occupation = DB::table('room_types')->where('id', $room_type_id)->first()->occupation;

                if ($occupation == 1) {
                    $rate_plan_room_calendars = DB::table('rates_plans_calendarys')
                        ->whereNull('deleted_at')
                        ->where('rates_plans_room_id', $rate_plan_room->id)
                        ->where('date', '>=', $service->date_in)
                        ->where('date', '<=', Carbon::parse($service->date_out)->subDays(1)->format('Y-m-d'))
                        ->get();

                    foreach ($rate_plan_room_calendars as $calendar) {

                        if ($rate_plan_room->channel_id == '6') {
                            $rates = DB::table('rates')->whereNull('deleted_at')->where('rates_plans_calendarys_id',
                                $calendar->id)->get();
                            $rate_channel_selected = '';
                            foreach ($rates as $rate) {
                                if ($rate->num_adult == $occupation) {
                                    $rate_channel_selected = $rate;
                                    break;
                                }
                            }

                            if (!$rate_channel_selected) {
                                //buscamos si encontramos tarifas por habitacion
                                if (count($rates) > 0) {
                                    $rate_channel_selected = $rates[0];
                                    $rate_channel_selected->price_adult = $rate_channel_selected->price_total;
                                }
                            }

                            if(isset($rate_channel_selected->price_adult)){
                               $simple += $rate_channel_selected->price_adult;
                            }

                            break;  // solo nos  importa la primera noche
                        } else {
                            $rate = DB::table('rates')->whereNull('deleted_at')->where('rates_plans_calendarys_id',
                                $calendar->id)->first();
                            $simple += $rate->price_adult;
                            break;  // solo nos  importa la primera noche

                        }
                    }
                    $simple = $simple + ($simple * ($rate_plan_room->markup / 100));
                }
            }

            //Todo: Calculo el precio para la acomodacion en Doble
            foreach ($rate_plan_rooms as $rate_plan_room) {
                $room_type_id = DB::table('rooms')->where('id', $rate_plan_room->room_id)->first()->room_type_id;

                $occupation = DB::table('room_types')->where('id', $room_type_id)->first()->occupation;

                if ($occupation == 2) {
                    $rate_plan_room_calendars = DB::table('rates_plans_calendarys')
                        ->whereNull('deleted_at')
                        ->where('rates_plans_room_id', $rate_plan_room->id)
                        ->where('date', '>=', $service->date_in)
                        ->where('date', '<=', Carbon::parse($service->date_out)->subDays(1)->format('Y-m-d'))
                        ->get();

                    foreach ($rate_plan_room_calendars as $calendar) {
                        if ($rate_plan_room->channel_id == '6') {

                            $rates = DB::table('rates')->whereNull('deleted_at')->where('rates_plans_calendarys_id',
                                $calendar->id)->get();
                            $rate_channel_selected = '';
                            foreach ($rates as $rate) {
                                if ($rate->num_adult == $occupation) {
                                    $rate_channel_selected = $rate;
                                    break;
                                }
                            }

                            if (!$rate_channel_selected) {
                                //buscamos si encontramos tarifas por habitacion
                                if (count($rates) > 0) {
                                    $rate_channel_selected = $rates[0];
                                    $rate_channel_selected->price_adult = $rate_channel_selected->price_total;
                                }
                            }                            
 

                            if(isset($rate_channel_selected->price_adult)){
                               $double += $rate_channel_selected->price_adult;
                            }
                            break;  // solo nos  importa la primera noche


                        } else {
                            $rate = DB::table('rates')
                                ->whereNull('deleted_at')
                                ->where('rates_plans_calendarys_id', $calendar->id)->first();
                            $double += $rate->price_adult;
                            break; // solo nos  importa la primera noche
                        }
                    }
                    $double = $double + ($double * ($rate_plan_room->markup / 100));

                }
            }

            //Todo: Calculo el precio para la acomodacion en Triple
            // si no cuenta con tarifa el costo es 0
            $exist_triple = false;
            foreach ($rate_plan_rooms as $rate_plan_room) {
                $room_type_id = DB::table('rooms')->where('id', $rate_plan_room->room_id)->first()->room_type_id;

                $occupation = DB::table('room_types')->where('id', $room_type_id)->first()->occupation;
                if ($occupation == 3) {
                    $exist_triple = true;
                    $rate_plan_room_calendars = DB::table('rates_plans_calendarys')
                        ->whereNull('deleted_at')
                        ->where('rates_plans_room_id', $rate_plan_room->id)
                        ->where('date', '>=', $service->date_in)
                        ->where('date', '<=', Carbon::parse($service->date_out)->subDays(1)->format('Y-m-d'))
                        ->get();

                    foreach ($rate_plan_room_calendars as $calendar) {
                        if ($rate_plan_room->channel_id == '6') {

                            $rates = DB::table('rates')->whereNull('deleted_at')->where('rates_plans_calendarys_id',
                                $calendar->id)->get();
                            $rate_channel_selected = '';
                            foreach ($rates as $rate) {
                                if ($rate->num_adult == $occupation) {
                                    $rate_channel_selected = $rate;
                                    break;
                                }
                            }

                            if (!$rate_channel_selected) {
                                //buscamos si encontramos tarifas por habitacion
                                if (count($rates) > 0) {
                                    $rate_channel_selected = $rates[0];
                                    $rate_channel_selected->price_adult = $rate_channel_selected->price_total;
                                }
                            }                            
                            
                            if (!$rate_channel_selected) {
                                //buscamos si encontramos tarifas por habitacion
                                if (count($rates) > 0) {
                                    $rate_channel_selected = $rates[0];
                                    $rate_channel_selected->price_adult = $rate_channel_selected->price_total;
                                }
                            }

                            if(isset($rate_channel_selected->price_adult)){
                               $triple += $rate_channel_selected->price_adult;
                            }
                            break;  // solo nos  importa la primera noche

                        } else {
                            $rate = DB::table('rates')
                                ->whereNull('deleted_at')
                                ->where('rates_plans_calendarys_id', $calendar->id)->first();

                            $triple += $rate->price_adult + $rate->price_extra;
                            break; // solo nos  importa la primera noche
                        }

                    }
                    $triple = $triple + ($triple * ($rate_plan_room->markup / 100));
                }
            }

            $rate_plan_room = RatesPlansRooms::with([
                'room' => function ($query) {
                    $query->with([
                        'room_type' => function ($query) {
                            $query->with([
                                'type_room' => function ($query) {
                                    $query->with([
                                        'translations' => function ($query) {
                                            $query->select(['object_id', 'value']);
                                            $query->where('type', 'typeroom');
                                            $query->where('language_id', 2);
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ]);
                },
                'rate_plan' => function ($query) {
                    $query->with([
                        'meal' => function ($query) {
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['object_id', 'value']);
                                    $query->where('type', 'meal');
                                    $query->where('language_id', 2);
                                }
                            ]);
                        },
                    ]);
                }

            ])->find($service_rate_plan_rooms_ids)->first();

            if ($apply_round) {
                $prices = [
                    "simple" => roundLito($simple),
                    "double" => roundLito($double),
                    "triple" => roundLito($triple)
                ];
            } else {
                $prices = ["simple" => $simple, "double" => $double, "triple" => $triple];
            }
            $prices["room_type"] = $rate_plan_room->room->room_type->type_room->translations[0]->value;
            $prices["rate_meal"] = $rate_plan_room->rate_plan->meal->translations[0]->value;
        }
        return $prices;
    }

    public function getAmountServiceService($service_id, $client_id, $from, $to, $quote_id, $apply_round = true)
    {
        $service = DB::table('quote_services')->where('id', $service_id)->first();

        $service_rate_id = DB::table('quote_service_rates')->where('quote_service_id',
            $service_id)->first()->service_rate_id;

        $markup = DB::table('quotes')->where('id', $quote_id)->first()->markup;

        // $pax_amounts = DB::table('service_rate_plans')
        //     ->where('service_rate_id', $service_rate_id)
        //     ->where('date_from', '<=', $service->date_in)
        //     ->where('date_to', '>=', $service->date_in)
        //     ->where('pax_from', '>=', $from)
        //     ->where('pax_to', '<=', $to)
        //     ->whereNull('deleted_at')
        //     ->get();

        $pax_amounts = ServiceRatePlan::with('service_rate.service')
                ->where('service_rate_id',$service_rate_id)
                ->where('date_from', '<=', $service->date_in)
                ->where('date_to', '>=', $service->date_in)
                ->where('pax_from', '>=', (int)$from)
                ->where('pax_to', '<=', (int)$to)
                ->get();
 

        $total_amount = 0;

        foreach ($pax_amounts as $pax_amount) {

            $affected_markup = $pax_amount->service_rate->service->affected_markup;
            if($affected_markup != "1"){
                $markup = 0;
            }

            // Log::debug(json_encode($pax_amount));
            $total_amount += ($pax_amount->price_adult) + ($pax_amount->price_adult * ($markup / 100));
        }

        if ($pax_amounts->count() > 0) { 
            $total_amount = $total_amount / $pax_amounts->count();
        } else { 
            // $pax_amounts = DB::table('service_rate_plans')
            //     ->where('service_rate_id', $service_rate_id)
            //     ->where('date_from', '<=', $service->date_in)
            //     ->where('date_to', '>=', $service->date_in)
            //     ->whereNull('deleted_at')
            //     ->get();
            
            $pax_amounts = ServiceRatePlan::with('service_rate.service')
                ->where('service_rate_id',$service_rate_id)
                ->where('date_from', '<=', $service->date_in)
                ->where('date_to', '>=', $service->date_in) 
                ->get();                

            if ($pax_amounts->count() > 0) {
                foreach ($pax_amounts as $index_pax_amount => $pax_amount) {
                    if ($pax_amount->pax_from <= $from && $pax_amount->pax_to >= $to) {
                        
                        $affected_markup = $pax_amount->service_rate->service->affected_markup;
                        if($affected_markup != "1"){
                            $markup = 0;
                        }                        
                        $total_amount = ($pax_amount->price_adult) + ($pax_amount->price_adult * ($markup / 100));
                        break;
                    }
                }
            }
        }

        if ($apply_round) {
            $total_amount = roundLito($total_amount);
        }

        return $total_amount;
    }

    /**
     * @param $service
     * @param $date_in_service
     * @param $category_id
     * @param $adults
     * @param $child
     * @param $extension_id
     * @param $parent_service_id
     * @return int //Parent Service Id
     */
    public function addServiceFromExtension(
        $service,
        $date_in_service,
        $category_id,
        $adults,
        $child,
        $extension_id,
        $parent_service_id
    ) {
        $service_id = DB::table('quote_services')->insertGetId([
            'quote_category_id' => $category_id,
            'type' => $service->type,
            'object_id' => $service->object_id,
            'date_in' => $date_in_service,
            'date_out' => $date_in_service,
            'adult' => $adults,
            'child' => $child,
            'new_extension_id' => $extension_id,
            // 'parent_service_id' => $parent_service_id
        ]);
        $service_rate_id = DB::table('package_service_rates')
            ->where('package_service_id', $service->id)
            ->whereNull('deleted_at')
            ->first()->service_rate_id;

        DB::table('quote_service_rates')->insert([
            'service_rate_id' => $service_rate_id,
            'quote_service_id' => $service_id
        ]);
        return $service_id;
    }

    /**
     * @param $service
     * @param $category_id
     * @param $date_in
     * @param $date_out
     * @param $adults
     * @param $child
     * @param $extension_id
     * @param $parent_service_id
     * @return int //Parent Service Id
     */
    public function addHotelFromExtension(
        $service,
        $category_id,
        $date_in,
        $date_out,
        $adults,
        $child,
        $extension_id,
        $parent_service_id
    ) {
        $service_id = DB::table('quote_services')->insertGetId([
            'quote_category_id' => $category_id,
            'type' => $service->type,
            'object_id' => $service->object_id,
            'date_in' => $date_in,
            'date_out' => $date_out,
            'nights' => Carbon::parse($date_in)->diffInDays(Carbon::parse($date_out)),
            'single' => 0,
            'double' => 1,
            'triple' => 0,
            'adult' => $adults,
            'child' => $child,
            'new_extension_id' => $extension_id,
            // 'parent_service_id' => $parent_service_id,
        ]);
        $rates = DB::table('package_service_rooms')
            ->whereNull('deleted_at')
            ->where('package_service_id', $service->id)
            ->get();

        foreach ($rates as $rate) {
            DB::table('quote_service_rooms')->insert([
                'rate_plan_room_id' => $rate->rate_plan_room_id,
                'quote_service_id' => $service_id
            ]);
        }

        return $service_id;
    }

    /**
     * @param $service_id
     * @param $date_in
     * @param $order
     */
    public function updateDateInExtension($service_id, $date_in, $order)
    {
        $service = DB::table('quote_services')->where('id', $service_id)->first();

        $pivot_date_extension = $service->date_in;

        $service_extensions = DB::table('quote_services')
            ->where('parent_service_id', $service->id)
            ->where('locked', 0)
            ->orderBy('date_in')->get();

        for ($j = 0; $j < $service_extensions->count(); $j++) {
            $days_difference_service_extension = Carbon::parse($pivot_date_extension)->diffInDays(Carbon::parse($service_extensions[$j]->date_in));
            if ($service->type == 'service' or $service->type == 'flight') {
                DB::table('quote_services')->where('id', $service_extensions[$j]->id)->update([
                    'order' => $order,
                    'date_in' => Carbon::parse($date_in)->addDays($days_difference_service_extension)->format('Y-m-d'),
                    'date_out' => Carbon::parse($date_in)->addDays($days_difference_service_extension)->format('Y-m-d')
                ]);
            }
            if ($service->type == 'hotel') {
                DB::table('quote_services')->where('id', $service->id)->update([
                    'order' => $order,
                    'date_in' => Carbon::parse($date_in)->addDays($days_difference_service_extension)->format('Y-m-d'),
                    'date_out' => Carbon::parse(Carbon::parse($date_in)->addDays($days_difference_service_extension)->format('Y-m-d'))->addDays($service->nights)->format('Y-m-d')
                ]);
            }
        }
        if ($service->type == 'service' or $service->type == 'flight') {
            DB::table('quote_services')->where('id', $service->id)->update([
                'order' => $order,
                'date_in' => $date_in,
                'date_out' => $date_in
            ]);
        }
        if ($service->type == 'hotel') {
            DB::table('quote_services')->where('id', $service->id)->update([
                'order' => $order,
                'date_in' => $date_in,
                'date_out' => Carbon::parse($date_in)->addDays($service->nights)->format('Y-m-d')
            ]);
        }
    }

    public function exportTableAmounts($quote_id, $category_id, $get_all_rates_services = false, $language_id = 1)
    {
        $quote = Quote::where('id', $quote_id)->first();
        $categories = [
            "passengers" => []
        ];
        if ($quote->operation == "passengers") {

            $data = [
                'quote_name' => "",
                'client_code' => "",
                'client_name' => "",
                'lang' => "",
                'passengers' => [],
                'passengers_optional' => [],
                'categories' => [],
                'categories_optional' => []
            ];

            $multiplePassengers = false;
            $occupation_name = "";
            $category = QuoteCategory::where('id', $category_id)->where('quote_id', $quote_id)
                ->with([
                    'type_class' => function ($query) use ($language_id) {
                        $query->select('id');
                        $query->with([
                            'translations' => function ($query) use ($language_id) {
                                $query->select(['object_id', 'value']);
                                $query->where('type', 'typeclass');
                                $query->where('language_id', $language_id);
                            }
                        ]);
                    }
                ])->first();
            $data['passengers'] = QuotePassenger::where('quote_id', $quote_id)->get()->toArray();
            $data['passengers_optional'] = QuotePassenger::where('quote_id', $quote_id)->get()->toArray();

            array_push($data["categories"], [
                'category' => $category['type_class']["translations"][0]['value'],
                'services' => []
            ]);
            array_push($data["categories_optional"], [
                'category' => $category['type_class']["translations"][0]['value'],
                'services_optional' => []
            ]);
            $quote_services = QuoteService::where('quote_category_id', $category["id"])->where('optional', 0);
            if (!$get_all_rates_services) {
                $quote_services = $quote_services->where('locked', 0);
            }
            $quote_services = $quote_services->with('service')
                ->with('hotel.channel')
                ->orderBy('date_in')
                ->get();

            $quote_services_optional = QuoteService::where('quote_category_id', $category["id"])->where('optional', 1);
            if (!$get_all_rates_services) {
                $quote_services_optional = $quote_services_optional->where('locked', 0);
            }
            $quote_services_optional = $quote_services_optional->with('service')
                ->with('hotel.channel')
                ->orderBy('date_in')
                ->get();
            $quote_people_initial = QuotePeople::where('quote_id', $quote_id)->first();
            $quote_ages_child = QuoteAgeChild::where('quote_id', $quote_id)->get()->toArray();

            //var_export($quote_people_initial);

            $data['all_passengers'] = $data['passengers'];
            $data['all_passengers_optional'] = $data['passengers_optional'];
            $data['quote_ages_child'] = $quote_ages_child;

            foreach ($data["passengers"] as $index_passenger => $passenger) {
                $data["passengers"][$index_passenger]["total"] = 0;
                $data["passengers"][$index_passenger]["total_adult"] = 0;
                $data["passengers"][$index_passenger]["total_child"] = 0;
            }
            foreach ($data["passengers_optional"] as $index_passenger => $passenger) {
                $data["passengers_optional"][$index_passenger]["total"] = 0;
                $data["passengers_optional"][$index_passenger]["total_adult"] = 0;
                $data["passengers_optional"][$index_passenger]["total_child"] = 0;
            }
            foreach ($quote_services as $quote_service) {
                $quote_people = QuotePeople::where('quote_id', $quote_id)->first();
                if ($quote_service["type"] == "service") {
                    $service = QuoteServiceAmount::where('quote_service_id',
                        $quote_service["id"])->first();
                    $children = ServiceChild::where('service_id', $quote_service['object_id'])->first();

                    $max_age_child = 17;
                    $min_age_child = 0;
                    if ($children != null) {
                        if ($children->max_age != null and $children->max_age > 0) {
                            $max_age_child = $children->max_age;
                        }

                        if ($children->min_age != null and $children->min_age > 0) {
                            $min_age_child = $children->min_age;
                        }
                    }

                    $data['children_service'][] = ['max_age' => $max_age_child, 'min_age' => $min_age_child];

                    // Calculo de adultos siempre y cuando los niños no estén dentro del rango.. 11, 7
                    $all_childs = 0;
                    foreach ($data["passengers"] as $index_passenger => $passenger) {
                        if ($quote_people_initial['child'] > 0 and $all_childs < $quote_people_initial['child']) {
                            if ($quote_ages_child[$all_childs]['age'] > $max_age_child) {
                                $quote_people['adults'] += 1;
                                $all_childs++;
                            }
                        }
                    }

                    $passengers = [];
                    $passengers_adults = [];
                    $passengers_childs = [];
                    $adults = 0;
                    $childs = 0; // $ignore = [];
                    foreach ($data["passengers"] as $index_passenger => $passenger) {

                        if ($quote_people_initial["adults"] == $quote_service["adult"] && $quote_people_initial["child"] == $quote_service["child"]) {

                            array_push($passengers,
                                roundLito(number_format(((float)($service->price_adult)), 2,
                                    '.',
                                    '')));

                            if ($quote_people_initial['adults'] > 0) {
                                array_push($passengers_adults,
                                    roundLito(number_format(((float)($service->price_adult)),
                                        2, '.',
                                        '')));
                            }

                            if ($quote_people_initial['child'] > 0) {
                                if ($all_childs > 0) {
                                    $quote_people['child'] = $quote_people_initial['child'] - $all_childs;
                                }

                                if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                    array_push($passengers_childs,
                                        roundLito(number_format(((float)(0)),
                                            2, '.', '')));
                                } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child and $quote_ages_child[$childs]['age'] > $min_age_child) {
                                    array_push($passengers_childs,
                                        roundLito(number_format(((float)($service->price_child)),
                                            2, '.',
                                            '')));
                                } else {
                                    array_push($passengers_childs,
                                        roundLito(number_format(((float)($service->price_adult)),
                                            2, '.',
                                            '')));
                                }

                                if ($index_passenger >= $quote_people_initial['adults']) {
                                    $childs++;
                                }
                            }
                            // }
//                        $data["passengers"][$index_passenger]["total"] += number_format(($service_amount / count($data["passengers"])),2, '.', '');
                        } else {
                            $service_amount = $service->price_adult;

                            $multiplePassengers = true;
                            $quote_service_passenger = QuoteServicePassenger::where('quote_service_id',
                                $quote_service["id"])->where('quote_passenger_id', $passenger["id"])->get()->count();

                            if ($quote_service_passenger > 0) {
                                array_push($passengers,
                                    roundLito(number_format(((float)($service_amount / $quote_service_passenger)), 2,
                                        '.', '')));
//                            $data["passengers"][$index_passenger]["total"] += number_format(($service_amount / count($data["passengers"])),2, '.', '');
                            } else {
                                array_push($passengers, 0);
                            }
                        }
                    }

                    array_push($data["categories"][count($data["categories"]) - 1]["services"], [
                        'date_service' => $quote_service["date_in"],
                        'service_code' => $quote_service["service"]["aurora_code"],
                        'service_name' => $quote_service["service"]["name"],
                        'passengers' => $passengers,
                        'passengers_adults' => $passengers_adults,
                        'passengers_childs' => $passengers_childs,
                    ]);
                }
                if ($quote_service["type"] == "hotel") {
                    $service_amount = QuoteServiceAmount::where('quote_service_id', $quote_service["id"])->first();
                    if ($quote_service["single"] > 0 && ($quote_service["double"] > 0 || $quote_service["triple"] > 0)) {
                        $multiplePassengers = true;
                    }
                    if ($quote_service["double"] > 0 && ($quote_service["single"] > 0 || $quote_service["triple"] > 0)) {
                        $multiplePassengers = true;
                    }
                    if ($quote_service["triple"] > 0 && ($quote_service["double"] > 0 || $quote_service["single"] > 0)) {
                        $multiplePassengers = true;
                    }

                    $children = Hotel::where('id', $quote_service['object_id'])->first();

                    $max_age_child = 17;
                    $min_age_child = 0;
                    if ($children != null) {
                        if ($children->max_age != null and $children->max_age > 0) {
                            $max_age_child = $children->max_age;
                        }

                        if ($children->min_age != null and $children->min_age > 0) {
                            $min_age_child = $children->min_age;
                        }
                    }

                    $data['children_service'][] = ['max_age' => $max_age_child, 'min_age' => $min_age_child];

                    // Calculo de adultos siempre y cuando los niños no estén dentro del rango.. 11, 7
                    $all_childs = 0;
                    foreach ($data["passengers"] as $index_passenger => $passenger) {
                        if ($quote_people_initial['child'] > 0 and $all_childs < $quote_people_initial['child']) {
                            if ($quote_ages_child[$all_childs]['age'] > $max_age_child) {
                                $quote_people['adults'] += 1;
                                $all_childs++;
                            }
                        }
                    }

                    $passengers = [];
                    $passengers_adults = [];
                    $passengers_childs = [];
                    $pivot_index_passenger = null;
                    $quantity_people = $quote_service["adult"] + $quote_service["child"];
                    if ($quote_service["single"] > 0) {
                        $amount_for_room_single = roundLito(number_format(((float)($service_amount["price_per_night"])),
                            2, '.', ''));
                        $amount_adult_for_room_single = roundLito(number_format(((float)($service_amount["price_adult"])),
                            2, '.', ''));
                        $amount_child_for_room_single = roundLito(number_format(((float)($service_amount["price_child"])),
                            2, '.', ''));
                        $quantity_rooms_single = $quote_service["single"];

                        $childs = 0;
                        foreach ($data["passengers"] as $index_passenger => $passenger) {
                            array_push($passengers,
                                roundLito(number_format(((float)($amount_for_room_single)),
                                    2, '.', '')));

                            if ($quote_people_initial['adults'] > 0) {
                                array_push($passengers_adults,
                                    roundLito(number_format(((float)($amount_adult_for_room_single)),
                                        2, '.', '')));
                            }

                            if ($quote_people_initial['child'] > 0) {
                                if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                    array_push($passengers_childs,
                                        roundLito(number_format(((float)(0)),
                                            2, '.', '')));
                                } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child and $quote_ages_child[$childs]['age'] > $min_age_child) {
                                    array_push($passengers_childs,
                                        roundLito(number_format(((float)($amount_child_for_room_single)),
                                            2, '.', '')));
                                } else {
                                    array_push($passengers_childs,
                                        roundLito(number_format(((float)($amount_adult_for_room_single)),
                                            2, '.',
                                            '')));
                                }

                                if ($index_passenger >= $quote_people_initial['adults']) {
                                    $childs++;
                                }
                            }

                            $quantity_people -= 1;
                            $quantity_rooms_single -= 1;
                            $occupation_name = " - SGL";
                            if (strpos($data["passengers"][$index_passenger]["last_name"], 'SGL') === false) {
                                $data["passengers"][$index_passenger]["last_name"] = $data["passengers"][$index_passenger]["last_name"] . ' - SGL';
                            }
                            if ($quantity_people == 0 || $quantity_rooms_single == 0) {
                                $pivot_index_passenger = $index_passenger;
                                break;
                            }
                        }
                    }

                    if ($quote_service["double"] > 0) {
                        $amount_for_room_double_for_person = roundLito(number_format(((float)(($service_amount["price_per_night"]))),
                            2, '.', ''));

                        $amount_adult_for_room_double_for_person = roundLito(number_format(((float)(($service_amount["price_adult"]))),
                            2, '.', ''));
                        $amount_child_for_room_double_for_person = roundLito(number_format(((float)(($service_amount["price_child"]))),
                            2, '.', ''));
                        $quantity_rooms_double = $quote_service["double"];
                        $quantity_persons = 2 * $quantity_rooms_double;
                        $childs = 0;
                        foreach ($data["passengers"] as $index_passenger2 => $passenger) {
                            if ($index_passenger2 > $pivot_index_passenger || is_null($pivot_index_passenger)) {
                                array_push($passengers,
                                    roundLito(number_format(((float)($amount_for_room_double_for_person)),
                                        2, '.', '')));

                                if ($quote_people_initial['adults'] > 0) {
                                    array_push($passengers_adults,
                                        roundLito(number_format(((float)($amount_adult_for_room_double_for_person)),
                                            2, '.', '')));
                                }

                                if ($quote_people_initial['child'] > 0) {
                                    if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                        array_push($passengers_childs,
                                            roundLito(number_format(((float)(0)),
                                                2, '.', '')));
                                    } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child and $quote_ages_child[$childs]['age'] > $min_age_child) {
                                        array_push($passengers_childs,
                                            roundLito(number_format(((float)($amount_child_for_room_double_for_person)),
                                                2, '.', '')));
                                    } else {
                                        array_push($passengers_childs,
                                            roundLito(number_format(((float)($amount_adult_for_room_double_for_person)),
                                                2, '.',
                                                '')));
                                    }

                                    if ($index_passenger2 >= $quote_people_initial['adults']) {
                                        $childs++;
                                    }
                                }

                                $quantity_people -= 1;
                                $quantity_persons -= 1;
                                $occupation_name = " - DBL";
                                if (strpos($data["passengers"][$index_passenger2]["last_name"], 'DBL') === false) {
                                    $data["passengers"][$index_passenger2]["last_name"] = $data["passengers"][$index_passenger2]["last_name"] . ' - DBL';
                                }

                                if ($quantity_persons == 0) {
                                    $quantity_rooms_double = 0;
                                }
                                if ($quantity_people == 0 || $quantity_rooms_double == 0) {

                                    $pivot_index_passenger = $index_passenger2;
                                    break;
                                }
                            }
                        }
                    }

                    if ($quote_service["triple"] > 0) {
                        $amount_for_room_triple_for_person = roundLito(number_format(((float)(($service_amount["price_per_night"]))),
                            2, '.', ''));
                        $amount_adult_for_room_triple_for_person = roundLito(number_format(((float)(($service_amount["price_adult"]))),
                            2, '.', ''));
                        $amount_child_for_room_triple_for_person = roundLito(number_format(((float)(($service_amount["price_child"]))),
                            2, '.', ''));
                        $quantity_rooms_triple = $quote_service["triple"];
                        $quantity_persons = 3 * $quantity_rooms_triple;
                        // $quantity_persons = 0;
                        $childs = 0;
                        foreach ($data["passengers"] as $index_passenger3 => $passenger) {

                            if ($index_passenger3 > $pivot_index_passenger || is_null($pivot_index_passenger)) {
                                array_push($passengers,
                                    roundLito(number_format(((float)($amount_for_room_triple_for_person)),
                                        2, '.', '')));

                                if ($quote_people_initial['adults'] > 0) {
                                    array_push($passengers_adults,
                                        roundLito(number_format(((float)($amount_adult_for_room_triple_for_person)),
                                            2, '.', '')));
                                }

                                if ($quote_people_initial['child'] > 0) {
                                    if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                        array_push($passengers_childs,
                                            roundLito(number_format(((float)(0)),
                                                2, '.', '')));
                                    } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child and $quote_ages_child[$childs]['age'] > $min_age_child) {
                                        array_push($passengers_childs,
                                            roundLito(number_format(((float)($amount_child_for_room_triple_for_person)),
                                                2, '.', '')));
                                    } else {
                                        array_push($passengers_childs,
                                            roundLito(number_format(((float)($amount_adult_for_room_triple_for_person)),
                                                2, '.',
                                                '')));
                                    }

                                    if ($index_passenger3 >= $quote_people_initial['adults']) {
                                        $childs++;
                                    }
                                }

                                $quantity_people -= 1;
                                // $quantity_persons += 1;
                                $quantity_persons -= 1;
                                $occupation_name = " - TPL";
                                if (strpos($data["passengers"][$index_passenger3]["last_name"], 'TPL') === false) {
                                    $data["passengers"][$index_passenger3]["last_name"] = $data["passengers"][$index_passenger3]["last_name"] . ' - TPL';
                                }

                                if ($quantity_persons == 0) {
                                    $quantity_rooms_triple = 0;
                                }

                                if ($quantity_people == 0 || $quantity_rooms_triple == 0) {

                                    $pivot_index_passenger = $index_passenger3;
                                    break;
                                }
                            }
                        }
                    }

                    for ($i = 0; $i < $quote_service["nights"]; $i++) {
                        array_push($data["categories"][count($data["categories"]) - 1]["services"], [
                            'date_service' => Carbon::parse(Carbon::createFromFormat('d/m/Y',
                                $quote_service["date_in"])->format('Y-m-d'))->addDays($i)->format('d/m/Y'),
                            'service_code' => $quote_service["hotel"]["channel"][0]["code"],
                            'service_name' => $quote_service["hotel"]["name"],
                            'passengers' => $passengers,
                            'passengers_adults' => $passengers_adults,
                            'passengers_childs' => $passengers_childs
                        ]);
                    }
                }

                $data['quote_people'][] = $quote_people;
                $data['quote_service'][] = $quote_service;
                $data['all_childs'][] = @$all_childs;
            }

            foreach ($quote_services_optional as $quote_service) {
                $quote_people = QuotePeople::where('quote_id', $quote_id)->first();
                if ($quote_service["type"] == "service") {
                    $service = QuoteServiceAmount::where('quote_service_id',
                        $quote_service["id"])->first();
                    $children = ServiceChild::where('service_id', $quote_service['object_id'])->first();

                    $max_age_child = 17;
                    $min_age_child = 0;
                    if ($children != null) {
                        if ($children->max_age != null and $children->max_age > 0) {
                            $max_age_child = $children->max_age;
                        }

                        if ($children->min_age != null and $children->min_age > 0) {
                            $min_age_child = $children->min_age;
                        }
                    }

                    $data['optional_children_service'][] = ['max_age' => $max_age_child, 'min_age' => $min_age_child];

                    // Calculo de adultos siempre y cuando los niños no estén dentro del rango..
                    $all_childs = 0;
                    foreach ($data["passengers_optional"] as $index_passenger => $passenger) {
                        if ($quote_people_initial['child'] > 0 and $all_childs < $quote_people_initial['child']) {
                            if ($quote_ages_child[$all_childs]['age'] > $max_age_child) {
                                $quote_people['adults'] += 1;
                                $all_childs++;
                            }
                        }
                    }

                    $passengers_optional = [];
                    $passengers_optional_adults = [];
                    $passengers_optional_childs = [];
                    $adults = 0;
                    $childs = 0; // $ignore = [];
                    foreach ($data["passengers_optional"] as $index_passenger => $passenger) {

                        if ($quote_people_initial["adults"] == $quote_service["adult"] && $quote_people_initial["child"] == $quote_service["child"]) {

                            array_push($passengers_optional,
                                roundLito(number_format(((float)($service->price_adult)),
                                    2,
                                    '.',
                                    '')));

                            if ($quote_people['adults'] > 0) {
                                array_push($passengers_optional_adults,
                                    roundLito(number_format(((float)($service->price_adult)),
                                        2, '.',
                                        '')));
                            }

                            if ($quote_people_initial['child'] > 0) {
                                if ($all_childs > 0) {
                                    $quote_people['child'] = $quote_people_initial['child'] - $all_childs;
                                }

                                if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                    array_push($passengers_optional_childs,
                                        roundLito(number_format(((float)(0)),
                                            2, '.',
                                            '')));
                                } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child and $quote_ages_child[$childs]['age'] > $min_age_child) {
                                    array_push($passengers_optional_childs,
                                        roundLito(number_format(((float)($service->price_child)),
                                            2, '.',
                                            '')));
                                } else {
                                    array_push($passengers_optional_childs,
                                        roundLito(number_format(((float)($service->price_adult)),
                                            2, '.',
                                            '')));
                                }

                                if ($index_passenger >= $quote_people_initial['adults']) {
                                    $childs++;
                                }
                            }
                        } else {
                            $service_amount = $service->price_adult;

                            $multiplePassengers = true;
                            $quote_service_passenger = QuoteServicePassenger::where('quote_service_id',
                                $quote_service["id"])->where('quote_passenger_id', $passenger["id"])->get()->count();

                            if ($quote_service_passenger > 0) {
                                array_push($passengers_optional,
                                    roundLito(number_format(((float)($service_amount / $quote_service_passenger)), 2,
                                        '.', '')));
                            } else {
                                array_push($passengers_optional, 0);
                            }
                        }
                    }

                    array_push($data["categories_optional"][count($data["categories_optional"]) - 1]["services_optional"],
                        [
                            'date_service' => $quote_service["date_in"],
                            'service_code' => $quote_service["service"]["aurora_code"],
                            'service_name' => $quote_service["service"]["name"],
                            'passengers_optional' => $passengers_optional,
                            'passengers_optional_adults' => $passengers_optional_adults,
                            'passengers_optional_childs' => $passengers_optional_childs
                        ]);
                }
                if ($quote_service["type"] == "hotel") {
                    $service_amount = QuoteServiceAmount::where('quote_service_id', $quote_service["id"])->first();

                    if ($quote_service["single"] > 0 && ($quote_service["double"] > 0 || $quote_service["triple"] > 0)) {
                        $multiplePassengers = true;
                    }
                    if ($quote_service["double"] > 0 && ($quote_service["single"] > 0 || $quote_service["triple"] > 0)) {
                        $multiplePassengers = true;
                    }
                    if ($quote_service["triple"] > 0 && ($quote_service["double"] > 0 || $quote_service["single"] > 0)) {
                        $multiplePassengers = true;
                    }

                    $children = Hotel::where('id', $quote_service['object_id'])->first();

                    $max_age_child = 17;
                    $min_age_child = 0;
                    if ($children != null) {
                        if ($children->max_age != null and $children->max_age > 0) {
                            $max_age_child = $children->max_age;
                        }

                        if ($children->min_age != null and $children->min_age > 0) {
                            $min_age_child = $children->min_age;
                        }
                    }

                    $data['optional_children_service'][] = ['max_age' => $max_age_child, 'min_age' => $min_age_child];

                    // Calculo de adultos siempre y cuando los niños no estén dentro del rango..
                    $all_childs = 0;
                    foreach ($data["passengers_optional"] as $index_passenger => $passenger) {
                        if ($quote_people_initial['child'] > 0 and $all_childs < $quote_people_initial['child']) {
                            if ($quote_ages_child[$all_childs]['age'] > $max_age_child) {
                                $quote_people['adults'] += 1;
                                $all_childs++;
                            }
                        }
                    }

                    $passengers_optional = [];
                    $passengers_optional_adults = [];
                    $passengers_optional_childs = [];
                    $pivot_index_passenger = null;
                    $quantity_people = $quote_service["adult"] + $quote_service["child"];
                    if ($quote_service["single"] > 0) {
                        $amount_for_room_single = roundLito(number_format(((float)($service_amount["price_per_night"])),
                            2, '.', ''));
                        $amount_adult_for_room_single = roundLito(number_format(((float)($service_amount["price_adult"])),
                            2, '.', ''));
                        $amount_child_for_room_single = roundLito(number_format(((float)($service_amount["price_child"])),
                            2, '.', ''));
                        $quantity_rooms_single = $quote_service["single"];
                        $childs = 0;
                        foreach ($data["passengers_optional"] as $index_passenger => $passenger) {
                            array_push($passengers_optional,
                                roundLito(number_format(((float)($amount_for_room_single)),
                                    2, '.', '')));

                            if ($quote_people_initial['adults'] > 0) {
                                array_push($passengers_optional_adults,
                                    roundLito(number_format(((float)($amount_adult_for_room_single)),
                                        2, '.', '')));
                            }

                            if ($quote_people_initial['child'] > 0) {
                                if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                    array_push($passengers_optional_childs,
                                        roundLito(number_format(((float)(0)),
                                            2, '.', '')));
                                } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child and $quote_ages_child[$childs]['age'] > $min_age_child) {
                                    array_push($passengers_optional_childs,
                                        roundLito(number_format(((float)($amount_child_for_room_single)),
                                            2, '.', '')));
                                } else {
                                    array_push($passengers_optional_childs,
                                        roundLito(number_format(((float)($amount_adult_for_room_single)),
                                            2, '.',
                                            '')));
                                }

                                if ($index_passenger >= $quote_people_initial['adults']) {
                                    $childs++;
                                }
                            }

                            $quantity_people -= 1;
                            $quantity_rooms_single -= 1;
                            $occupation_name = " - SGL";
                            if (strpos($data["passengers_optional"][$index_passenger]["last_name"], 'SGL') === false) {
                                $data["passengers_optional"][$index_passenger]["last_name"] = $data["passengers_optional"][$index_passenger]["last_name"] . ' - SGL';
                            }
                            if ($quantity_people == 0 || $quantity_rooms_single == 0) {
                                $pivot_index_passenger = $index_passenger;
                                break;
                            }
                        }
                    }

                    if ($quote_service["double"] > 0) {
                        $amount_for_room_double_for_person = roundLito(number_format(((float)(($service_amount["price_per_night"]))),
                            2, '.', ''));
                        $amount_adult_for_room_double_for_person = roundLito(number_format(((float)(($service_amount["price_adult"]))),
                            2, '.', ''));
                        $amount_child_for_room_double_for_person = roundLito(number_format(((float)(($service_amount["price_child"]))),
                            2, '.', ''));
                        $quantity_rooms_double = $quote_service["double"];
                        $quantity_persons = 2 * $quantity_rooms_double;
                        $childs = 0;
                        foreach ($data["passengers_optional"] as $index_passenger2 => $passenger) {
                            if ($index_passenger2 > $pivot_index_passenger || is_null($pivot_index_passenger)) {
                                array_push($passengers_optional,
                                    roundLito(number_format(((float)($amount_for_room_double_for_person)),
                                        2, '.', '')));

                                if ($quote_people_initial['adults'] > 0) {
                                    array_push($passengers_optional_adults,
                                        roundLito(number_format(((float)($amount_adult_for_room_double_for_person)),
                                            2, '.', '')));
                                }

                                if ($quote_people_initial['child'] > 0) {
                                    if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                        array_push($passengers_optional_childs,
                                            roundLito(number_format(((float)(0)),
                                                2, '.', '')));
                                    } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child and $quote_ages_child[$childs]['age'] > $min_age_child) {
                                        array_push($passengers_optional_childs,
                                            roundLito(number_format(((float)($amount_child_for_room_double_for_person)),
                                                2, '.', '')));
                                    } else {
                                        array_push($passengers_optional_childs,
                                            roundLito(number_format(((float)($amount_for_room_double_for_person)),
                                                2, '.',
                                                '')));
                                    }

                                    if ($index_passenger2 >= $quote_people_initial['adults']) {
                                        $childs++;
                                    }
                                }

//                            $data["passengers"][$index_passenger]["total"] += number_format($amount_for_room_double_for_person,2, '.', '');
                                $quantity_people -= 1;
                                $quantity_persons -= 1;
                                $occupation_name = " - DBL";
                                if (strpos($data["passengers_optional"][$index_passenger2]["last_name"],
                                        'DBL') === false) {
                                    $data["passengers_optional"][$index_passenger2]["last_name"] = $data["passengers_optional"][$index_passenger2]["last_name"] . ' - DBL';
                                }

                                if ($quantity_persons == 0) {
                                    $quantity_rooms_double = 0;
                                }
                                if ($quantity_people == 0 || $quantity_rooms_double == 0) {

                                    $pivot_index_passenger = $index_passenger2;
                                    break;
                                }
                            }
                        }
                    }

                    if ($quote_service["triple"] > 0) {
                        $amount_for_room_triple_for_person = roundLito(number_format(((float)(($service_amount["price_per_night"]))),
                            2, '.', ''));
                        $amount_adult_for_room_triple_for_person = roundLito(number_format(((float)(($service_amount["price_adult"]))),
                            2, '.', ''));
                        $amount_child_for_room_triple_for_person = roundLito(number_format(((float)(($service_amount["price_child"]))),
                            2, '.', ''));
                        $quantity_rooms_triple = $quote_service["triple"];
                        $quantity_persons = 3 * $quantity_rooms_triple;
                        // $quantity_persons = 0;
                        $childs = 0;
                        foreach ($data["passengers_optional"] as $index_passenger3 => $passenger) {

                            if ($index_passenger3 > $pivot_index_passenger || is_null($pivot_index_passenger)) {
                                array_push($passengers_optional,
                                    roundLito(number_format(((float)($amount_for_room_triple_for_person)),
                                        2, '.', '')));

                                if ($quote_people_initial['adults'] > 0) {
                                    array_push($passengers_optional_adults,
                                        roundLito(number_format(((float)($amount_adult_for_room_triple_for_person)),
                                            2, '.', '')));
                                }

                                if ($quote_people_initial['child'] > 0) {
                                    if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                        array_push($passengers_optional_childs,
                                            roundLito(number_format(((float)(0)),
                                                2, '.', '')));
                                    } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child and $quote_ages_child[$childs]['age'] > $min_age_child) {
                                        array_push($passengers_optional_childs,
                                            roundLito(number_format(((float)($amount_child_for_room_triple_for_person)),
                                                2, '.', '')));
                                    } else {
                                        array_push($passengers_optional_childs,
                                            roundLito(number_format(((float)($amount_adult_for_room_triple_for_person)),
                                                2, '.',
                                                '')));
                                    }

                                    if ($index_passenger3 >= $quote_people_initial['adults']) {
                                        $childs++;
                                    }
                                }

//                            $data["passengers"][$index_passenger]["total"] += number_format($amount_for_room_triple_for_person, 2, '.', '');
                                $quantity_people -= 1;
                                //$quantity_persons += 1;
                                $quantity_persons -= 1;
                                $occupation_name = " - TPL";
                                if (strpos($data["passengers_optional"][$index_passenger3]["last_name"],
                                        'TPL') === false) {
                                    $data["passengers_optional"][$index_passenger3]["last_name"] = $data["passengers_optional"][$index_passenger3]["last_name"] . ' - TPL';
                                }

                                if ($quantity_persons == 0) {
                                    $quantity_rooms_triple = 0;
                                }

                                if ($quantity_people == 0 || $quantity_rooms_triple == 0) {

                                    $pivot_index_passenger = $index_passenger3;
                                    break;
                                }

                            }
                        }
                    }
                    for ($i = 0; $i < $quote_service["nights"]; $i++) {
                        array_push($data["categories_optional"][count($data["categories_optional"]) - 1]["services_optional"],
                            [
                                'date_service' => Carbon::parse(Carbon::createFromFormat('d/m/Y',
                                    $quote_service["date_in"])->format('Y-m-d'))->addDays($i)->format('d/m/Y'),
                                'service_code' => $quote_service["hotel"]["channel"][0]["code"],
                                'service_name' => $quote_service["hotel"]["name"],
                                'passengers_optional' => $passengers_optional,
                                'passengers_optional_adults' => $passengers_optional_adults,
                                'passengers_optional_childs' => $passengers_optional_childs
                            ]);
                    }
                }

                $data['optional_quote_people'][] = $quote_people;
                $data['optional_quote_service'][] = $quote_service;
                $data['optional_all_childs'][] = @$all_childs;
            }

            if (!$multiplePassengers) {
                $total = $data["passengers"][0]["total"];
                $total_adult = $data["passengers"][0]["total_adult"];
                $total_child = $data["passengers"][0]["total_child"];
                $data["passengers"] = [
                    [
                        "first_name" => "PAX" . $occupation_name,
                        "last_name" => "",
                        "total" => roundLito((float)$total),
                        "total_adult" => roundLito((float)$total_adult),
                        "total_child" => roundLito((float)$total_child)
                    ]
                ];

                foreach ($data["categories"][0]["services"] as $index_service => $service) {
                    array_splice($data["categories"][0]["services"][$index_service]["passengers"], 1);
                }

                $total_optional = $data["passengers_optional"][0]["total"];
                $total_optional_adult = $data["passengers_optional"][0]["total_adult"];
                $total_optional_child = $data["passengers_optional"][0]["total_child"];
                $data["passengers_optional"] = [
                    [
                        "first_name" => "PAX" . $occupation_name,
                        "last_name" => "",
                        "total" => roundLito((float)$total_optional),
                        "total_adult" => roundLito((float)$total_optional_adult),
                        "total_child" => roundLito((float)$total_optional_child)
                    ]
                ];

                foreach ($data["categories_optional"][0]["services_optional"] as $index_service => $service) {
                    array_splice($data["categories_optional"][0]["services_optional"][$index_service]["passengers_optional"],
                        1);
                }

            }

            foreach ($data["passengers"] as $index_passenger => $passenger) {
                foreach ($data["categories"][0]["services"] as $service) {
                    $service_pax_total = (isset($service["passengers"][$index_passenger])) ? $service["passengers"][$index_passenger] : 0;
                    $service_pax_adult = (isset($service["passengers_adults"][$index_passenger])) ? $service["passengers_adults"][$index_passenger] : 0;
                    $service_pax_child = (isset($service["passengers_childs"][$index_passenger])) ? $service["passengers_childs"][$index_passenger] : 0;
                    $data["passengers"][$index_passenger]["total"] += number_format(((float)$service_pax_total), 2, '.',
                        '');
                    $data["passengers"][$index_passenger]["total_adult"] += number_format(((float)$service_pax_adult),
                        2, '.',
                        '');
                    $data["passengers"][$index_passenger]["total_child"] += number_format(((float)$service_pax_child),
                        2, '.',
                        '');
                }
            }
//            dd($data);
            foreach ($data["passengers_optional"] as $index_passenger => $passenger) {
                foreach ($data["categories_optional"][0]["services_optional"] as $service) {
                    $service_pax_total = (isset($service["passengers_optional"][$index_passenger])) ? $service["passengers_optional"][$index_passenger] : 0;
                    $service_pax_adult = (isset($service["passengers_optional_adults"][$index_passenger])) ? $service["passengers_optional_adults"][$index_passenger] : 0;
                    $service_pax_child = (isset($service["passengers_optional_childs"][$index_passenger])) ? $service["passengers_optional_childs"][$index_passenger] : 0;
                    $data["passengers_optional"][$index_passenger]["total"] += number_format(((float)$service_pax_total),
                        2, '.',
                        '');
                    $data["passengers_optional"][$index_passenger]["total_adult"] += number_format(((float)$service_pax_adult),
                        2, '.',
                        '');
                    $data["passengers_optional"][$index_passenger]["total_child"] += number_format(((float)$service_pax_child),
                        2, '.',
                        '');
                }
            }

            $categories = QuoteCategory::where('quote_id', $quote_id)->get()->toArray();
            foreach ($categories as $index_category => $category) {
                if ($category_id == $category["id"]) {

                    $flags = [];
                    $flags['_passengers'] = $data["all_passengers"];
                    $flags['_passengers_optional'] = $data["all_passengers_optional"];
                    $flags['_quote_people_initial'] = $quote_people_initial;
                    $flags['_quote_people'][] = @$data['quote_people'];
                    $flags['_quote_service'][] = @$data['quote_service'];
                    $flags['_all_childs'][] = @$data['all_childs'];
                    $flags['_optional_quote_people'][] = @$data['optional_quote_people'];
                    $flags['_optional_quote_service'][] = @$data['optional_quote_service'];
                    $flags['_optional_all_childs'][] = @$data['optional_all_childs'];
                    $flags['_quote_ages_child'] = @$data["quote_ages_child"];
                    $flags['_children_service'] = @$data['children_service'];
                    $flags['_optional_children_service'] = @$data['optional_children_service'];
                    $flags['multiple_passengers'] = (int)$multiplePassengers;
                    $categories[$index_category]['flags'] = $flags;

                    $categories[$index_category]["passengers"] = [];
                    foreach ($data["passengers"] as $passenger) {
                        array_push($categories[$index_category]["passengers"], [
//                            "passenger_id" => $passenger["id"],
                            "passenger_name" => $passenger["first_name"] . " " . $passenger["last_name"],
                            "amount" => roundLito((float)$passenger["total"]),
                            "amount_adult" => roundLito((float)$passenger["total_adult"]),
                            "amount_child" => roundLito((float)$passenger["total_child"])
                        ]);
                    }
                }

            }
            //Funcionalidad de opcional
            foreach ($categories as $index_category => $category) {
                if ($category_id == $category["id"]) {
                    $categories[$index_category]["passengers_optional"] = [];
                    foreach ($data["passengers_optional"] as $passenger) {
                        array_push($categories[$index_category]["passengers_optional"], [
//                            "passenger_id" => $passenger["id"],
                            "passenger_name" => $passenger["first_name"] . " " . $passenger["last_name"],
                            "amount" => roundLito((float)$passenger["total"]),
                            "amount_adult" => roundLito((float)$passenger["total_adult"]),
                            "amount_child" => roundLito((float)$passenger["total_child"])
                        ]);
                    }
                }
            }
        }

        if ($quote->operation == "ranges") {

            $categories = QuoteCategory::where('quote_id', $quote_id)->get()->toArray();
            $ranges_quote = QuoteRange::where('quote_id', $quote_id)->get();
            foreach ($categories as $index_category => $category) {
                $categories[$index_category]["ranges"] = [];
                $categories[$index_category]["ranges_optional"] = [];
                foreach ($ranges_quote as $range_quote) {
                    $quote_service_ids = QuoteService::where('quote_category_id', $category["id"])
                        ->where('optional', 0);
                    if (!$get_all_rates_services) {
                        $quote_service_ids = $quote_service_ids->where('locked', 0);
                    }
                    $quote_service_ids = $quote_service_ids->pluck('id');

                    $amount_range = QuoteDynamicSaleRate::where('quote_category_id',
                        $category["id"])->whereIn('quote_service_id', $quote_service_ids)->where('pax_from',
                        $range_quote["from"])->where('pax_to', $range_quote["to"])->sum('simple');
                    array_push($categories[$index_category]["ranges"], [
                        'from' => $range_quote["from"],
                        'to' => $range_quote["to"],
                        'amount' => $amount_range
                    ]);
                }
                foreach ($ranges_quote as $range_quote) {
                    $quote_service_ids = QuoteService::where('quote_category_id', $category["id"])->where('optional',
                        1);
                    if (!$get_all_rates_services) {
                        $quote_service_ids = $quote_service_ids->where('locked', 0);
                    }
                    $quote_service_ids = $quote_service_ids->pluck('id');
                    $amount_range = QuoteDynamicSaleRate::where('quote_category_id',
                        $category["id"])->whereIn('quote_service_id', $quote_service_ids)->where('pax_from',
                        $range_quote["from"])->where('pax_to', $range_quote["to"])->sum('simple');

                    array_push($categories[$index_category]["ranges_optional"], [
                        'from' => $range_quote["from"],
                        'to' => $range_quote["to"],
                        'amount' => $amount_range
                    ]);
                }
            }
        }

        return $categories;
    }

    public function addServiceFromExtensionToQuote(
        $service,
        $date_in_service,
        $category_id,
        $adults,
        $child,
        $extension_id,
        $parent_service_id
    ) {

        $service_id = DB::table('quote_services')->insertGetId([
            'quote_category_id' => $category_id,
            'type' => $service['type'],
            'object_id' => $service['object_id'],
            'date_in' => $date_in_service,
            'date_out' => $date_in_service,
            'adult' => $adults,
            'child' => $child,
            'extension_id' => $extension_id,
            'parent_service_id' => $parent_service_id
        ]);

        DB::table('quote_service_rates')->insert([
            'service_rate_id' => $service['service_rates'][0]['service_rate_id'],
            'quote_service_id' => $service_id
        ]);

        return $service_id;
    }

    public function addHotelFromExtensionToQuote(
        $service,
        $category_id,
        $date_in,
        $date_out,
        $adults,
        $child,
        $extension_id,
        $parent_service_id
    ) {

        $service_id = DB::table('quote_services')->insertGetId([
            'quote_category_id' => $category_id,
            'type' => $service['type'],
            'object_id' => $service['object_id'],
            'date_in' => $date_in,
            'date_out' => $date_out,
            'nights' => Carbon::parse($date_in)->diffInDays(Carbon::parse($date_out)),
            'single' => 0,
            'double' => 1,
            'triple' => 0,
            'adult' => $adults,
            'child' => $child,
            'extension_id' => $extension_id,
            'parent_service_id' => $parent_service_id,
        ]);

        foreach ($service['service_rooms'] as $rate) {
            DB::table('quote_service_rooms')->insert([
                'rate_plan_room_id' => $rate['rate_plan_room_id'],
                'quote_service_id' => $service_id
            ]);
        }

        return $service_id;
    }


    /**
     * @param $quote_id_original //id de la cotizacion base
     * @param $quote_id //Id de la cotizacion de actualizar
     */
    private function replacePackageToQuote($quote_log)
    {
        DB::transaction(function () use ($quote_log) {

            $quote_id_editing = $quote_log->quote_id;
            $package_id = $quote_log->object_id;
//            throw new \Exception($quote_log->id);
            $quote_for_copy = Quote::where('id', $quote_id_editing)
                ->with([
                    'categories.services.service_rate',
                    'categories.services.service_rooms',
                    'ranges',
                    'notes',
                    'passengers',
                    'people',
                    'destinations'
                ])->first();

            $quote_for_copy_name = $quote_for_copy->name;
            $date_in = $quote_for_copy->date_in;
            $estimated_travel_date = $quote_for_copy->estimated_travel_date;
            $user_id = $quote_for_copy->user_id;
            $markup = $quote_for_copy->markup;
            $nights = $quote_for_copy->nights;
            $service_type_id = $quote_for_copy->service_type_id;

            $new_object_id = DB::table('quotes')->insertGetId([
                'name' => $quote_for_copy_name,
                'date_in' => $date_in,
                'estimated_travel_date' => $estimated_travel_date,
                'nights' => $nights,
                'service_type_id' => $service_type_id,
                'user_id' => $user_id,
                'markup' => $markup,
                'status' => 1,
                'operation' => $quote_for_copy->operation,
                'package_id' => $quote_for_copy->package_id,
                'created_at' => Carbon::now()
            ]);

            foreach ($quote_for_copy->categories as $c) {
                $new_category_id = DB::table('quote_categories')->insertGetId([
                    'quote_id' => $new_object_id,
                    'type_class_id' => $c->type_class_id,
                    'created_at' => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
                foreach ($c->services as $s) {
                    $new_date_in = \DateTime::createFromFormat('d/m/Y', $s->date_in);
                    $new_date_out = \DateTime::createFromFormat('d/m/Y', $s->date_out);

                    $new_service_id = DB::table('quote_services')->insertGetId([
                        'quote_category_id' => $new_category_id,
                        'type' => $s->type,
                        'object_id' => $s->object_id,
                        'order' => $s->order,
                        'date_in' => $new_date_in->format('Y-m-d'),
                        'date_out' => $new_date_out->format('Y-m-d'),
                        'nights' => $s->nights,
                        'adult' => $s->adult,
                        'child' => $s->child,
                        'infant' => $s->infant,
                        'single' => $s->single,
                        'double' => $s->double,
                        'triple' => $s->triple,
                        'triple_active' => $s->triple_active,
                        'optional' => (int)$s->optional,
                        'created_at' => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                    if ($s->type == 'service' and isset($s->service_rate->id)) {
                        DB::table('quote_service_rates')->insert([
                            'quote_service_id' => $new_service_id,
                            'service_rate_id' => $s->service_rate->service_rate_id,
                            'created_at' => Carbon::now(),
                            "updated_at" => Carbon::now()
                        ]);
                    }
                    if ($s->type == 'hotel') {
                        foreach ($s->service_rooms as $r) {
                            DB::table('quote_service_rooms')->insert([
                                'quote_service_id' => $new_service_id,
                                'rate_plan_room_id' => $r->rate_plan_room_id,
                                'created_at' => Carbon::now(),
                                "updated_at" => Carbon::now()
                            ]);
                        }
                    }
                }
            }


            if ($quote_for_copy->operation == 'passengers') {
                foreach ($quote_for_copy->people as $people) {
                    DB::table('quote_people')->insert([
                        'adults' => $people->adults,
                        'child' => $people->child,
                        'quote_id' => $new_object_id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                }
                foreach ($quote_for_copy->passengers as $passenger) {
                    DB::table('quote_passengers')->insert([
                        'first_name' => $passenger->first_name,
                        'last_name' => $passenger->last_name,
                        'gender' => $passenger->gender,
                        'birthday' => $passenger->birthday,
                        'document_number' => $passenger->document_number,
                        'doctype_iso' => $passenger->doctype_iso,
                        'country_iso' => $passenger->country_iso,
                        'email' => $passenger->email,
                        'phone' => $passenger->phone,
                        'notes' => $passenger->notes,
                        'type' => $passenger->type,
                        'quote_id' => $new_object_id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                }
            }

            foreach ($quote_for_copy->notes as $note) {
                DB::table('quote_notes')->insert([
                    'parent_note_id' => $note->parent_note_id,
                    'comment' => $note->comment,
                    'status' => $note->status,
                    'quote_id' => $new_object_id,
                    'user_id' => $note->user_id,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
            }

            foreach ($quote_for_copy->destinations as $destiny) {
                DB::table('quote_destinations')->insert([
                    'quote_id' => $new_object_id,
                    'state_id' => $destiny->state_id,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
            }

            //creo el log de paquete
            DB::table('quote_logs')->insert([
                'quote_id' => $new_object_id,
                'type' => 'from_package',
                'object_id' => $package_id,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);

            //creo el log nuevo de la cotizacion
            DB::table('quote_logs')->insert([
                'quote_id' => $quote_id_editing,
                'type' => 'editing_quote',
                'object_id' => $new_object_id,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);

            DB::table('quote_logs')
                ->where('id', $quote_log->id)
                ->where('type', '!=', 'from_package')
                ->delete();

        });
    }

    /**
     * @param $service
     * @param $date_in_service
     * @param $category_id
     * @param $adults
     * @param $child
     * @param $extension_id
     * @param $parent_service_id
     * @return int //Parent Service Id
     */
    public function addFlightFromExtension(
        $service,
        $date_in_service,
        $category_id,
        $adults,
        $child,
        $extension_id,
        $parent_service_id
    ) {
        return DB::table('quote_services')->insertGetId([
            'quote_category_id' => $category_id,
            'type' => $service->type,
            'object_id' => $service->object_id,
            'date_in' => $date_in_service,
            'date_out' => $date_in_service,
            'adult' => $adults,
            'child' => $child,
            'new_extension_id' => $extension_id,
            // 'parent_service_id' => $parent_service_id,
            'code_flight' => $service->code_flight,
            'origin' => $service->origin,
            'destiny' => $service->destiny,
        ]);
    }

    public function getMinDateQuoteServices($quote_service_id, $quote_category_id, $date_in_compare)
    {
        $date_in_min = null;
        $date_min_service = DB::table('quote_services')
            ->where('id', '!=', $quote_service_id)
            ->where('quote_category_id', $quote_category_id)
            ->orderBy('date_in')
            ->first(['date_in']);

        if ($date_min_service) {
            $date_in_service_ = Carbon::parse($date_min_service->date_in)->format('Y-m-d');
            $date_in = Carbon::parse($date_in_compare)->format('Y-m-d');
            $date_in_min = $date_in;
            if ($date_in_service_ < $date_in) {
                $date_in_min = $date_in_service_;
            }
        }

        return $date_in_min;
    }

    public function updateListServicePassengersGeneral($quote_id, $quote_adult_general, $quote_child_general)
    {
        $quote_passengers = QuotePassenger::where('quote_id', $quote_id)
            ->orderBy('id')
            ->get(['id', 'type']);
        $quote_categories = QuoteCategory::where('quote_id', $quote_id)
            ->with([
                'services' => function ($query) {
                    $query->select([
                        'id',
                        'quote_category_id',
                        'date_in',
                        'type',
                        'nights',
                        'object_id',
                        'adult',
                        'child',
                        'single',
                        'double',
                        'double_child',
                        'triple',
                        'triple_child',
                        'locked'
                    ]);
                    $query->with([
                        'passengers' => function ($query) {
                            $query->select(['id', 'quote_service_id', 'quote_passenger_id']);
                            $query->with([
                                'passenger' => function ($query) {
                                    $query->select(['id', 'type']);
                                }
                            ]);
                        }
                    ]);
                    $query->where('locked', 0);
                }
            ])
            ->get(['id', 'quote_id']);

        $hotels_groups = collect();
        foreach ($quote_categories as $category) {
            foreach ($category->services as $service) {
                if ($service->type == 'service' or $service->type == 'flight') {
                    $this->assignPassengerToService($service, $quote_passengers, $quote_adult_general,
                        $quote_child_general);
                }

                if ($service->type == 'hotel') {
                    $total_accommodations = (int)$service->single + (int)$service->double + (int)$service->triple + (int)$service->double_child + (int)$service->triple_child;
                    if ($total_accommodations > 0) {

                        $hotels_groups->add($service);
                    }
                }
            }
        }


        // la actualizacion se ara por una funcion general

        // if ($hotels_groups->count() > 0) {
        //     $hotels_groups = $hotels_groups->groupBy(function ($item, $key) {
        //         $locked = ($item['locked']) ? 1 : 0;
        //         $date = convertDate($item["date_in"], '/', '-', 1);
        //         return $item['quote_category_id'] . '|' . $date . '|' . $item['nights'] . '|' . $item['object_id'] . '|' . $locked;
        //     });

        //     $this->assignPassengerToRoomsHotels($hotels_groups, $quote_passengers);
        // }
    }

    public function updateListServicePassengers_backup($quote_id, $quote_service_id, $adult, $child)
    {
        $service_ = DB::table('quote_services')->where('id', $quote_service_id)->first();
        // $adult = $service_->adult; $child = $service_->child;

        $search = DB::table('quote_services')
            ->where('quote_category_id', $service_->quote_category_id)
            ->where('type', $service_->type)->where('object_id', $service_->object_id)
            ->where('date_in', $service_->date_in)->where('date_out', $service_->date_out)
            ->where('id', '<>', $quote_service_id)->orderBy('id', 'ASC')->first();

        $prev_quote_service_id = 0;

        if ($search != null and $search != '') {
            $prev_quote_service_id = $quote_service_id;
            $quote_service_id = $search->id;
        }

        $quote_service_ = QuoteServicePassenger::where('quote_service_id', $quote_service_id)
            ->with([
                'passenger' => function ($query) {
                    $query->select(['id', 'type']);
                }
            ])->orderBy('quote_passenger_id', 'ASC')->get();

        //Todo Contamos cuantos adultos tenemos en la cotizacion actualmente
        $adult_count = $quote_service_->filter(function ($passenger) {
            return $passenger['passenger']['type'] === 'ADL';
        })->count();

        //Todo Contamos cuantos niños tenemos en la cotizacion actualmente
        $child_count = $quote_service_->filter(function ($passenger) {
            return $passenger['passenger']['type'] === 'CHD';
        })->count();

        if ($prev_quote_service_id > 0) {
            $quote_service_id = $prev_quote_service_id;
        }

        /*
         * Todo Si la cantidad de adultos que viene es mayor a la cantidad actual en la cotizacion entonces agregamos
         *  los adultos que faltan
         */
        if ($adult > $adult_count) {
            $qty_passenger_add = $adult - $adult_count;
            $service_adults = $quote_service_->filter(function ($passenger) {
                return $passenger['passenger']['type'] === 'ADL';
            }); // 3

            $ignore = [];

            foreach ($service_adults as $service_adult) {
                $ignore[] = $service_adult['quote_passenger_id'];
            }

            if ($qty_passenger_add > 0) {
                $_count = 0;
                $quote_passengers_adults = QuotePassenger::where('quote_id', $quote_id)
                    ->where('type', 'ADL')
                    ->orderBy('id')
                    ->get(['id', 'type']); // 5

                if ($service_adults->count() === 0) {
                    foreach ($quote_passengers_adults as $passengers_adult) {
                        if (!in_array($passengers_adult['id'], $ignore)) {
                            if (($qty_passenger_add > $_count and $service_->adult > $_count)) { // OR ($prev_quote_service_id > 0 AND $qty_passenger_add >= $_count)) {
                                $newAdult = new QuoteServicePassenger();
                                $newAdult->quote_service_id = $quote_service_id;
                                $newAdult->quote_passenger_id = $passengers_adult['id'];
                                $newAdult->save();
                                $_count++;
                            }
                        }
                    }
                } else {

                    foreach ($quote_passengers_adults as $passengers_adult) { // 3
                        if (!in_array($passengers_adult['id'], $ignore)) {
                            if (($qty_passenger_add > $_count and $service_->adult > $_count)) { // OR ($prev_quote_service_id > 0 AND $qty_passenger_add >= $_count)) {
                                $newAdult = new QuoteServicePassenger();
                                $newAdult->quote_service_id = $quote_service_id;
                                $newAdult->quote_passenger_id = $passengers_adult['id'];
                                $newAdult->save();
                                $_count++;
                            }
                        }
                    }
                }
            }
        }

        /*
        * Todo Si la cantidad de adultos que biene es menor a la cantidad actual en la cotizacion entonces eliminanos
        *  los ultimos agregados
        */
        if ($adult < $adult_count) {
            $qty_delete = $adult_count - $adult; // 3 - 1
            $service_adults = $quote_service_->filter(function ($passenger) {
                return $passenger['passenger']['type'] === 'ADL';
            })->reverse();
            $_count = 0;

            if ($qty_delete > 0) {
                foreach ($service_adults as $service_adult) {
                    if ($qty_delete > $_count) {
                        $delete_adult = QuoteServicePassenger::find($service_adult['id']);
                        if ($delete_adult) {
                            $delete_adult->delete();
                            $_count++;
                        }
                    }
                }
            }
        }

        /*
        * Todo Si la cantidad de niños que biene es mayor a la cantidad actual en la cotizacion entonces agregamos
        *  los niños que faltan, si no eliminamos
        */
        if ($child > $child_count) {
            $qty_passenger_add = $child - $child_count;
            $service_children = $quote_service_->filter(function ($passenger) {
                return $passenger['passenger']['type'] === 'CHD';
            });

            $ignore = [];

            foreach ($service_children as $service_child) {
                $ignore[] = $service_child['id'];
            }

            $_count = 0;

            if ($qty_passenger_add > 0) {
                $quote_passengers_children = QuotePassenger::where('quote_id', $quote_id)
                    ->where('type', 'CHD')
                    ->orderBy('id')
                    ->get(['id', 'type']);
                if ($service_children->count() === 0) {
                    foreach ($quote_passengers_children as $passengers_child) {
                        if (!in_array($passengers_child['quote_passenger_id'], $ignore)) {
                            if (($qty_passenger_add > $_count and $service_->child > $_count)) {
                                // OR ($prev_quote_service_id > 0 AND $qty_passenger_add >= $_count)) {
                                $newAdult = new QuoteServicePassenger();
                                $newAdult->quote_service_id = $quote_service_id;
                                $newAdult->quote_passenger_id = $passengers_child['id'];
                                $newAdult->save();
                                $_count++;
                            }
                        }
                    }
                } else {

                    foreach ($quote_passengers_children as $passengers_child) {
                        if (!in_array($passengers_child['quote_passenger_id'], $ignore)) {
                            if (($qty_passenger_add > $_count and $service_->child > $_count)) { // OR ($prev_quote_service_id > 0 AND $qty_passenger_add >= $_count)) {
                                $newAdult = new QuoteServicePassenger();
                                $newAdult->quote_service_id = $quote_service_id;
                                $newAdult->quote_passenger_id = $passengers_child['id'];
                                $newAdult->save();
                                $_count++;
                            }
                        }
                    }
                }
            }

        }

        /*
        * Todo Si la cantidad de niños que biene es menor a la cantidad actual en la cotizacion entonces eliminamos
        *  los ultimos agregados
        */
        if ($child < $child_count) {
            $qty_delete = $child_count - $child;
            $service_children = $quote_service_->filter(function ($passenger) {
                return $passenger['passenger']['type'] === 'CHD';
            })->reverse();

            if ($qty_delete > 0) {
                $_count = 0;
                foreach ($service_children as $service_child) {
                    if ($qty_delete > $_count) {
                        $delete_child = QuoteServicePassenger::find($service_child['id']);
                        if ($delete_child) {
                            $delete_child->delete();
                            $_count++;
                        }
                    }
                }
            }
        }
    }

    public function setAccommodationNotExist()
    {
        $quote = Quote::where('user_id', Auth::user()->id)
            ->where('status', 2)
            ->with('categories')
            ->with('accommodation')
            ->first();
        if ($quote and $quote->accommodation === null) {
            $_hotels_accommodation = collect();
            $services_original = DB::table('quote_services')
                ->where('quote_category_id', $quote->categories->first()->id)
                ->orderBy('date_in', 'asc')
                ->orderBy('order', 'asc')
                ->get();

            //Todo Si no tiene acomodacion guardad se crea a partir de los hoteles
            foreach ($services_original as $s) {
                if ($s->type == "hotel") {
                    $_hotels_accommodation->add([
                        'key' => $s->object_id . '|' . $s->date_in . '|' . $s->nights,
                        'single' => $s->single,
                        'double' => $s->double,
                        'double_child' => $s->double_child,
                        'triple' => $s->triple,
                        'triple_child' => $s->triple_child
                    ]);
                }
            }
            $_hotels_accommodation = $_hotels_accommodation->groupBy('key');
            $single = 0;
            $double = 0;
            $double_child = 0;
            $triple = 0;
            $triple_child = 0;
            if ($_hotels_accommodation->count() > 0) {
                $first_accommodation = $_hotels_accommodation->first();
                //Todo Buscamos acomodacion en simple
                $q_single = $first_accommodation->where('single', '>', 0)->first();
                if ($q_single) {
                    $single = $q_single['single'];
                }
                //Todo Buscamos acomodacion en doble
                $q_double = $first_accommodation->where('double', '>', 0)->first();
                if ($q_double) {
                    $double = $q_double['double'];
                }
                //Todo Buscamos acomodacion en niño doble
                $q_double_child = $first_accommodation->where('double_child', '>', 0)->first();
                if ($q_double_child) {
                    $double_child = $q_double_child['double_child'];
                }
                //Todo Buscamos acomodacion en triple
                $q_triple = $first_accommodation->where('triple', '>', 0)->first();
                if ($triple) {
                    $double = $q_triple['triple'];
                }
                //Todo Buscamos acomodacion en triple
                $q_triple_child = $first_accommodation->where('triple_child', '>', 0)->first();
                if ($q_triple_child) {
                    $triple_child = $q_triple_child['triple_child'];
                }
            }

            $quote_accommodations = new QuoteAccommodation();
            $quote_accommodations->quote_id = $quote->id;
            $quote_accommodations->single = $single;
            $quote_accommodations->double = $double;
            $quote_accommodations->double_child = $double_child;
            $quote_accommodations->triple = $triple;
            $quote_accommodations->triple_child = $triple_child;
            $quote_accommodations->save();
        }


    }

    public function saveQuoteServices($quote_id, $quote_original, $save_as)
    {

        try {
            DB::beginTransaction();

            //Todo Elimino los categorias
            DB::table('quote_distributions')->where('quote_id', $quote_id)->delete();

            //Todo Elimino los categorias
            DB::table('quote_accommodations')->where('quote_id', $quote_id)->delete();

            //Todo Elimino los categorias
            DB::table('quote_categories')->where('quote_id', $quote_id)->delete();

            //Todo Elimino la acomodacion de adultos + niños
            DB::table('quote_people')->where('quote_id', $quote_id)->delete();

            //Todo Elimino las notas
            DB::table('quote_notes')->where('quote_id', $quote_id)->delete();

            //Todo Elimino los rangos
            if ($quote_original->operation == "ranges") {
                DB::table('quote_ranges')->where('quote_id', $quote_id)->delete();
            }

            //Todo Elimino los pasajeros
            // if ($quote_original->operation == "passengers") {
            //     DB::table('quote_passengers')->where('quote_id', $quote_id)->delete();
            // }
            DB::table('quote_passengers')->where('quote_id', $quote_id)->delete();
            DB::table('quote_age_child')->where('quote_id', $quote_id)->delete();

            //Todo Traigo los logs
            $logs = $this->getLogs($quote_original->id);
            //Todo Elimino los logs
            DB::table('quote_logs')->where('quote_id', $quote_id)->delete();

            $this->quote = Quote::find($quote_id);
            $this->quote->code = $quote_original->code;
            $this->quote->name = $quote_original->name;
            $this->quote->date_in = $quote_original->date_in;
            $this->quote->estimated_travel_date = $quote_original->estimated_travel_date;
            $this->quote->markup = $quote_original->markup;
            $this->quote->discount = $quote_original->discount;
            $this->quote->discount_detail = $quote_original->discount_detail;
            $this->quote->order_related = $quote_original->order_related;
            $this->quote->order_position = $quote_original->order_position;
            $this->quote->cities = $quote_original->cities;
            $this->quote->nights = $quote_original->nights;
            $this->quote->operation = $quote_original->operation;
            $this->quote->language_id = $quote_original->language_id;
            $this->quote->package_id = $quote_original->package_id;
            $single_max = [];
            $double_max = [];
            $triple_max = [];

            foreach ($quote_original->categories as $cate_index => $category) {
                // if($cate_index != 0) continue;
                $QC = new QuoteCategory();
                $QC->quote_id = $quote_id;
                $QC->type_class_id = $category->type_class_id;

                foreach ($category->services as $key_service => $s) {
                    if ($s->type == "hotel") {
                        if ($cate_index == 0) {
                            if ($s->single > 0 or $s->double > 0 or $s->triple > 0) {
                                if (!isset($single_max[$s->object_id . "-" . $s->date_in])) {
                                    $single_max[$s->object_id . "-" . $s->date_in] = 0;
                                    $double_max[$s->object_id . "-" . $s->date_in] = 0;
                                    $triple_max[$s->object_id . "-" . $s->date_in] = 0;
                                }
                                $single_max[$s->object_id . "-" . $s->date_in] = $single_max[$s->object_id . "-" . $s->date_in] + $s->single;
                                $double_max[$s->object_id . "-" . $s->date_in] = $double_max[$s->object_id . "-" . $s->date_in] + $s->double;
                                $triple_max[$s->object_id . "-" . $s->date_in] = $triple_max[$s->object_id . "-" . $s->date_in] + $s->triple;
                            }

                        }
                    }
                }

                foreach ($category->services as $key_service => $s) {

                    $_date_in = convertDate($s->date_in, '/', '-', 1);
                    $_date_out = convertDate($s->date_out, '/', '-', 1);

                    $QCS = new QuoteService();
                    $QCS->type = $s->type;
                    $QCS->object_id = $s->object_id;
                    $QCS->order = $s->order;
                    $QCS->date_in = $_date_in;
                    $QCS->date_out = $_date_out;
                    $QCS->hour_in = $s->hour_in;
                    $QCS->nights = $s->nights;
                    $QCS->adult = $s->adult;
                    $QCS->child = $s->child;
                    $QCS->infant = $s->infant;
                    $QCS->single = $s->single;
                    $QCS->double = $s->double;
                    $QCS->triple = $s->triple;
                    $QCS->double_child = $s->double_child;
                    $QCS->triple_child = $s->triple_child;
                    $QCS->triple_active = $s->triple_active;
                    $QCS->locked = ($save_as) ? 0 : $s->locked;
                    $QCS->on_request = $s->on_request;
                    $QCS->optional = (int)$s->optional;
                    $QCS->code_flight = $s->code_flight;
                    $QCS->origin = $s->origin;
                    $QCS->destiny = $s->destiny;
                    $QCS->extension_id = $s->extension_id;
                    $QCS->parent_service_id = $s->parent_service_id;
                    $QCS->schedule_id = $s->schedule_id;
                    $QCS->notes = $s->notes;
                    if ($save_as and $s->locked) {
                        $QCS->locked = 0;
                    }

                    if ($s->type == "hotel") {
                        foreach ($s->service_rooms as $service_room) {
                            $QHR = new QuoteServiceRoom();
                            $QHR->rate_plan_room_id = $service_room->rate_plan_room_id;
                            $QCS->service_rooms->add($QHR);
                        }
                    } else {
                        if (isset($s->service_rate->service_rate_id)) {
                            $QSR = new QuoteServiceRate();
                            $QSR->service_rate_id = $s->service_rate->service_rate_id;
                            $QCS->setRelation('service_rate', $QSR);
                        }
                    }


                    foreach ($s->passengers as $passenger) {
                        $QCSP = new QuoteServicePassenger();
                        $QCSP->quote_service_id = $passenger->quote_service_id;
                        $QCSP->quote_passenger_id = $passenger->quote_passenger_id;
                        $QCS->passengers->add($QCSP);
                    }


                    $QC->services->add($QCS);

                }
                $this->quote->categories->add($QC);
            }


            // Log::debug(json_encode([
            // 'simple' => $single_max,
            // 'doble' => $double_max,
            // 'triple' => $triple_max,
            // 'double_child' => $child_double_max,
            // 'triple_child' => $child_triple_max,
            // 'QC' => $QC
            // ]));

            //Todo Agrego la acomocacion adulto + niños


            if ($quote_original->people->count() > 0) {
                $quote_people = $quote_original->people->first();
                $QP = new QuotePeople();
                $QP->adults = $quote_people->adults;
                $QP->child = $quote_people->child;
                $QP->quote_id = $quote_id;
                $this->quote->people->add($QP);

                //Todo Agrego la edades de los niños
                if ($quote_people->child > 0) {
                    foreach ($quote_original->age_child as $quote_age_child) {
                        $QPCH = new QuoteAgeChild();
                        $QPCH->age = $quote_age_child->age;
                        $QPCH->quote_id = $quote_id;
                        $QPCH->quote_age_child_id = $quote_age_child->id;
                        $this->quote->age_child->add($QPCH);
                    }
                }
            }

            //Todo Agrego las notas
            if ($quote_original->notes->count() > 0) {
                foreach ($quote_original->notes as $note) {
                    $QN = new QuoteNote();
                    $QN->parent_note_id = $note->parent_note_id;
                    $QN->comment = $note->comment;
                    $QN->status = $note->status;
                    $QN->quote_id = $quote_id;
                    $QN->user_id = $note->user_id;
                    $this->quote->notes->add($QN);
                }
            }

            //Todo Agrego los rangos
            if ($quote_original->operation == "ranges") {
                foreach ($quote_original->ranges as $range) {
                    $QR = new QuoteRange();
                    $QR->from = $range->from;
                    $QR->to = $range->to;
                    $QR->quote_id = $quote_id;
                    $this->quote->ranges->add($QR);
                }
            }

            //Todo Agrego los pasajeros
            if ($quote_original->operation == "passengers") {
                foreach ($quote_original->passengers as $passenger) {
                    $QP = new QuotePassenger();
                    $QP->first_name = $passenger->first_name;
                    $QP->last_name = $passenger->last_name;
                    $QP->gender = $passenger->gender;
                    $QP->birthday = $passenger->birthday;
                    $QP->document_number = $passenger->document_number;
                    $QP->doctype_iso = $passenger->doctype_iso;
                    $QP->country_iso = $passenger->country_iso;
                    $QP->type = !empty($passenger->type) ? $passenger->type : 'ADL';
                    $QP->email = $passenger->email;
                    $QP->phone = $passenger->phone;
                    $QP->notes = $passenger->notes;
                    $QP->quote_age_child_id = $passenger->quote_age_child_id;
                    $QP->quote_passenger_id = $passenger->id;
                    $QP->quote_id = $quote_id;
                    $this->quote->passengers->add($QP);
                }
            }

            //Todo Agrego los logs
            foreach ($logs as $log) {
                if ($log !== null) {
                    $QL = new QuoteLog();
                    $QL->quote_id = $quote_id;
                    $QL->type = $log->type;
                    $QL->object_id = $log->object_id;
                    $QL->user_id = Auth::id();
                    $this->quote->logs->add($QL);
                }

            }

            //Agregamos la distributions
            if ($quote_original->operation == "passengers") {
                foreach ($quote_original->distributions as $distribution) {
                    $Dist = new QuoteDistribution();
                    $Dist->type_room = $distribution->type_room;
                    $Dist->type_room_name = $distribution->type_room_name;
                    $Dist->occupation = $distribution->occupation;
                    $Dist->single = $distribution->single;
                    $Dist->double = $distribution->double;
                    $Dist->triple = $distribution->triple;
                    $Dist->adult = $distribution->adult;
                    $Dist->child = $distribution->child;
                    $Dist->order = $distribution->order;
                    $Dist->quote_id = $distribution->quote_id;

                    foreach ($distribution->passengers as $passenger) {
                        $Passe = new QuoteDistributionPassenger();
                        $Passe->quote_distribution_id = $passenger->quote_distribution_id;
                        $Passe->quote_passenger_id = $passenger->quote_passenger_id;
                        $Dist->passengers->add($Passe);
                    }
                    $this->quote->distributions->add($Dist);
                    // $this->quote->setRelation('distributions', $Dist);
                }
            }

            //Todo Agrega la acomodacion
            if (!empty($quote_original->accommodation)) {
                $QA = new QuoteAccommodation();
                $QA->single = (int)$quote_original->accommodation->single;
                $QA->double = (int)$quote_original->accommodation->double;
                $QA->double_child = (int)$quote_original->accommodation->double_child;
                $QA->triple = (int)$quote_original->accommodation->triple;
                $QA->triple_child = (int)$quote_original->accommodation->triple_child;
                $this->quote->setRelation('accommodation', $QA);
            } else {

                // hay un bag aqui, no puede haber cotizaciones que no tengan registro en quote_accommodations
                $single_max = reset($single_max);
                $double_max = reset($double_max);
                $triple_max = reset($triple_max);

                // dd((int)$single_max,(int)$double_max,(int)$triple_max);
                // dd($single_max,$double_max,$triple_max); 

                $QA = new QuoteAccommodation();
                $QA->single = (int)$single_max;
                $QA->double = (int)$double_max;
                $QA->triple = (int)$triple_max;
                $QA->double_child = 0;
                $QA->triple_child = 0;
                $this->quote->setRelation('accommodation', $QA);
            }

            $quote_log_editing_quote = DB::table('quote_logs')->where('quote_id', $quote_original->id)
                ->where('type', 'editing_quote')->where('object_id', $quote_id)->first();

            $quote_log_created_editing_package = false;


            $quote_log_from_package_original = DB::table('quote_logs')->where('quote_id',
                $quote_original->id)->where('type', 'editing_package')->first();

            $quote_log_from_package = DB::table('quote_logs')->where('quote_id', $quote_id)
                ->where('type', 'editing_package')->first();


            if ($quote_log_from_package == null && $quote_log_from_package_original != null) {
                $quote_log_created_editing_package = true;

                DB::table('quote_logs')->insert([
                    'quote_id' => $quote_id,
                    'type' => 'editing_package',
                    'object_id' => $quote_log_from_package["object_id"],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            if (!$quote_log_created_editing_package && $quote_log_from_package_original != null) {
                DB::table('quote_logs')->where('quote_id', $quote_id)->where('type', 'editing_package')->update([
                    'object_id' => $quote_log_from_package["object_id"]
                ]);
            }

            if ($quote_log_editing_quote == null) {
                DB::table('quote_logs')->insert([
                    'quote_id' => $quote_id,
                    'type' => 'editing_quote',
                    'object_id' => $quote_original->id,
                    'user_id' => Auth::id(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }


            //Todo Guardamos la cotizacion
            $this->quote->save();
            //Todo guardamos las categorias
            $this->quote->categories()->saveMany($this->quote->categories);
            //Todo Guaramos los rangos
            if ($this->quote->ranges->count() > 0) {
                $this->quote->ranges()->saveMany($this->quote->ranges);
            }

            //Todo Guardamos las edades de niños
            $this->quote->age_child()->saveMany($this->quote->age_child);

            //Todo Guardamos los pasajeros
            if ($this->quote->passengers->count() > 0) {

                foreach ($this->quote->passengers as $id => $passanger) {
                    if ($passanger->type == 'CHD') {
                        foreach ($this->quote->age_child as $age_child) {
                            if ($passanger->quote_age_child_id == $age_child->quote_age_child_id) {
                                $this->quote->passengers[$id]->quote_age_child_id = $age_child->id;
                            }
                        }
                    }
                }

                $this->quote->passengers()->saveMany($this->quote->passengers);
            }


            $this->quote->distributions()->saveMany($this->quote->distributions);

            foreach ($this->quote->distributions as $distribution) {

                foreach ($distribution->passengers as $id => $passenger) {

                    foreach ($this->quote->passengers as $quote_passenger) {
                        if ($passenger->quote_passenger_id == $quote_passenger->quote_passenger_id) {
                            $distribution->passengers[$id]->quote_passenger_id = $quote_passenger->id;
                            break;
                        }
                    }
                }
                $distribution->passengers()->saveMany($distribution->passengers);
            }


            //Todo Guardamos la acomodacion
            $this->quote->accommodation()->save($this->quote->accommodation);

            //Todo Guardamos la cantidad adultos + niños
            $this->quote->people()->saveMany($this->quote->people);

            //Todo Guardamos las notas
            $this->quote->notes()->saveMany($this->quote->notes);
            //Todo Guardamos los logs de la cotizacion
            $this->quote->logs()->saveMany($this->quote->logs);
            //Todo Recorremos los servicios y sus tarifas y guardamos


            foreach ($this->quote->categories as $category) {
                //Todo Guardamos los servicios/hoteles de la categoria
                $category->services()->saveMany($category->services);
                foreach ($category->services as $service) {
                    //Todo Guardamos las tarifas de los servicios
                    if ($service->amount->count() > 0) {
                        $service->amount()->saveMany($service->amount);
                    }
                    if ($service->type == "service") {
                        if (isset($service->service_rate)) {
                            $service->service_rate()->save($service->service_rate);
                        }
                    }
                    //Todo Guardamos las tarifas de las habitaciones
                    if ($service->type == "hotel") {
                        $service->service_rooms()->saveMany($service->service_rooms);
                    }
                }
            }

            //Todo Si en la cotizacion hay extensiones agregamos a la cotizacion editada
            $this->updateParentIdHasExtensions($this->quote->categories);
            if ($quote_original->operation == "passengers") {
                $this->createOrUpdateServicePassengers($quote_id, $quote_original->id, $this->quote->categories,
                    $this->quote->passengers);
            }

            DB::commit();

        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }

    }

    public function getLogs($quote_id)
    {
        try {
            $logs = collect();
            //Todo Desde una cotizacion
            $_logs_from_quote = DB::table('quote_logs')
                ->where('quote_id', $quote_id)
                ->where('type', 'from_quote')
                ->orderBy('created_at', 'desc')
                ->first();
            if ($_logs_from_quote) {
                $logs->add($_logs_from_quote);
            }

            $editing_quote = $this->getEditQuote($quote_id);

            if ($editing_quote) {
                //Todo from_package
                $_logs_from_package = DB::table('quote_logs')
                    ->where('quote_id', $editing_quote->object_id)
                    ->where('type', 'from_package')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($_logs_from_package) {
                    $logs->add($_logs_from_package);
                }
                //Todo copy_self
                $_logs_copy_self = DB::table('quote_logs')
                    ->where('quote_id', $editing_quote->object_id)
                    ->where('type', 'copy_self')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if (!$_logs_copy_self) {
                    $logs->add($_logs_copy_self);
                }
            }


            //Todo Desde una extension
            $_logs_from_extension = DB::table('quote_logs')
                ->where('quote_id', $quote_id)
                ->where('type', 'from_extension')
                ->orderBy('created_at', 'desc')
                ->first();
            if ($_logs_from_extension) {
                $logs->add($_logs_from_extension);
            }

            $_logs_quote_added = DB::table('quote_logs')
                ->where('quote_id', $quote_id)
                ->where('type', 'quote_added')
                ->orderBy('created_at', 'desc')
                ->first();
            if ($_logs_quote_added) {
                $logs->add($_logs_quote_added);
            }

            $_logs_extension_added = DB::table('quote_logs')
                ->where('quote_id', $quote_id)
                ->where('type', 'extension_added')
                ->orderBy('created_at', 'desc')
                ->first();
            if ($_logs_extension_added) {
                $logs->add($_logs_extension_added);
            }

            return $logs;

        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());

        }
    }

    /**
     *
     * @param $categories Collection
     */
    public function updateParentIdHasExtensions($categories)
    {
        foreach ($categories as $category) {
            $services_parents_extension = $category->services->where('extension_id', '!=', null)
                ->where('parent_service_id', null)
                ->where('extension_id', '!=', null)
                ->sortBy(['id'])->values();

            $services_children_extension = $category->services->where('parent_service_id', '!=', null)
                ->where('extension_id', null)
                ->sortBy(['parent_service_id'])
                ->groupBy('parent_service_id')->values();
            foreach ($services_parents_extension as $key => $parent_extension) {
                foreach ($services_parents_extension as $key => $parent_extension) {
                    foreach ($services_children_extension as $key_group => $parent_children_extension) {
                        if (isset($services_children_extension[$key]) and $services_children_extension[$key]->count() > 0) {
                            QuoteService::whereIn('id', $services_children_extension[$key]->pluck('id'))->update([
                                'parent_service_id' => $parent_extension->id
                            ]);
                        }
                        continue;
                    }
                }
            }
        }
    }

    /**
     * @param $quote_id
     * @param bool $withRelationShip | True => Trae la cotizacion con sus relaciones
     * @param bool $servicesLooked | True => Trae todos los servicios asi estan bloqueados
     * @return mixed
     */
    public function getQuote($quote_id, $withRelationShip = false, $servicesLooked = false)
    {
        $quote = Quote::where('id', $quote_id);
        if ($withRelationShip) {
            $quote = $quote->with([
                'categories' => function ($query) use ($servicesLooked) {
                    $query->select(['id', 'quote_id', 'type_class_id', 'created_at', 'updated_at']);
                    $query->with([
                        'services' => function ($query) use ($servicesLooked) {
                            $query->select([
                                'id',
                                'quote_category_id',
                                'type',
                                'object_id',
                                'order',
                                'date_in',
                                'date_out',
                                'hour_in',
                                'nights',
                                'adult',
                                'child',
                                'infant',
                                'single',
                                'double',
                                'double_child',
                                'triple',
                                'triple_child',
                                'triple_active',
                                'locked',
                                'on_request',
                                'extension_id',
                                'parent_service_id',
                                'optional',
                                'code_flight',
                                'origin',
                                'destiny',
                                'date_flight',
                                'notes',
                                'schedule_id'
                            ]);
                            if ($servicesLooked) {
                                $query->where('locked', 0);
                            }
                            $query->orderBy('date_in');
                            $query->orderBy('order');
                            $query->with([
                                'passengers' => function ($query) {
                                    $query->select(['id', 'quote_service_id', 'quote_passenger_id']);
                                }
                            ]);
                            $query->with([
                                'amount' => function ($query) {
                                    $query->select([
                                        'id',
                                        'quote_service_id',
                                        'date_service',
                                        'price_per_night_without_markup',
                                        'price_per_night',
                                        'price_adult_without_markup',
                                        'price_adult',
                                        'price_child_without_markup',
                                        'price_child'
                                    ]);
                                }
                            ]);
                        }
                    ]);

                }
            ])
                ->with([
                    'people' => function ($query) {
                        $query->select(['id', 'adults', 'child', 'quote_id']);
                    }
                ])
                ->with([
                    'age_child' => function ($query) {
                        $query->select(['id', 'age', 'quote_id']);
                    }
                ])
                ->with([
                    'notes' => function ($query) {
                        $query->select(['id', 'parent_note_id', 'comment', 'status', 'quote_id', 'user_id']);
                    }
                ])
                ->with([
                    'ranges' => function ($query) {
                        $query->select(['id', 'from', 'to', 'quote_id']);
                    }
                ])
                ->with([
                    'accommodation' => function ($query) {
                        $query->select([
                            'id',
                            'quote_id',
                            'single',
                            'double',
                            'double_child',
                            'triple',
                            'triple_child'
                        ]);
                    }
                ])
                ->with('distributions.passengers')
                ->with([
                    'passengers' => function ($query) {
                        $query->select([
                            'id',
                            'first_name',
                            'last_name',
                            'gender',
                            'birthday',
                            'document_number',
                            'doctype_iso',
                            'country_iso',
                            'city_ifx_iso',
                            'email',
                            'phone',
                            'notes',
                            'quote_id',
                            'address',
                            'dietary_restrictions',
                            'medical_restrictions',
                            'type',
                            'is_direct_client',
                            'quote_age_child_id'
                        ]);
                    }
                ]);
        }
        $quote = $quote->first([
            'id',
            'code',
            'name',
            'date_in',
            'cities',
            'nights',
            'estimated_price',
            'user_id',
            'service_type_id',
            'status',
            'discount',
            'discount_detail',
            'discount_user_permission',
            'order_related',
            'order_position',
            'show_in_popup',
            'created_at',
            'updated_at',
            'operation',
            'markup',
            'shared',
            'estimated_travel_date',
            'language_id',
            'package_id'
        ]);

        return $quote;
    }

    public function getEditQuote($quote_id)
    {
        $editing_quote = DB::table('quote_logs')->where('quote_id', $quote_id)
            ->where('type', 'editing_quote')->orderBy('created_at', 'desc')
            ->first();
        return $editing_quote;
    }

    public function createOrUpdateServicePassengers($quote_id, $quote_original, $categories, $passengers)
    {
        $quote_passengers_draft = QuotePassenger::where('quote_id', $quote_original)->orderBy('id')->get(['id']);
        $quote_passengers_original = QuotePassenger::where('quote_id',
            $quote_id)->orderBy('id')->get(['id'])->toArray();
        foreach ($categories as $category) {
            foreach ($category->services as $service) {
                if ($service->passengers->count() === 0) {
                    foreach ($passengers as $passenger) {
                        $new_passenger_service = new QuoteServicePassenger();
                        $new_passenger_service->quote_service_id = $service->id;
                        $new_passenger_service->quote_passenger_id = $passenger->id;
                        $new_passenger_service->save();
                    }
                }
                if ($service->passengers->count() > 0) {
                    foreach ($service->passengers as $passenger) {
                        $key_pax = array_search($passenger->quote_passenger_id,
                            $quote_passengers_draft->pluck('id')->toArray());
                        if ($key_pax !== false) {
                            if (isset($quote_passengers_original[$key_pax])) {
                                $new_passenger_service = new QuoteServicePassenger();
                                $new_passenger_service->quote_service_id = $service->id;
                                $new_passenger_service->quote_passenger_id = $quote_passengers_original[$key_pax]['id'];
                                $new_passenger_service->save();
                            }

                        }
                    }
                }
            }
        }
    }

    public function copyServicePassengersFromQuote($quote_id, $quote_original)
    {
        $quote_passengers_draft = QuotePassenger::where('quote_id', $quote_id)->orderBy('id')->get(['id']);
        $quote_passengers_original = QuotePassenger::where('quote_id',
            $quote_original)->orderBy('id')->get(['id'])->toArray();

        $categories = QuoteCategory::where('quote_id', $quote_original)
            ->with([
                'services' => function ($query) {
                    $query->select(['id', 'quote_category_id']);
                    $query->with([
                        'passengers' => function ($query) {
                            $query->select(['id', 'quote_service_id', 'quote_passenger_id']);
                        }
                    ]);
                }
            ])->get();

        foreach ($categories as $category) {
            foreach ($category->services as $service) {
                if ($service->passengers->count() === 0) {
                    foreach ($quote_passengers_original as $passenger) {
                        $new_passenger_service = new QuoteServicePassenger();
                        $new_passenger_service->quote_service_id = $service->id;
                        $new_passenger_service->quote_passenger_id = $passenger['id'];
                        $new_passenger_service->save();
                    }
                }

                if ($service->passengers->count() > 0) {
                    foreach ($service->passengers as $passenger) {
                        $key_pax = array_search($passenger->quote_passenger_id,
                            $quote_passengers_draft->pluck('id')->toArray());
                        if ($key_pax !== false) {
                            if (isset($quote_passengers_original[$key_pax])) {
                                $find_passenger_service = QuoteServicePassenger::find($passenger->id);
                                if ($find_passenger_service) {
                                    $find_passenger_service->quote_passenger_id = $quote_passengers_original[$key_pax]['id'];
                                    $find_passenger_service->save();
                                }
                            }

                        }
                    }
                }
            }
        }
    }

    public function assignPassengerToService($service, $quote_passengers, $quote_adult_general, $quote_child_general)
    {
        //Todo Contamos cuantos adultos tenemos asignados en el servicio
        $service_assign_adult_count = $service->passengers->filter(function ($passenger) {
            return $passenger['passenger']['type'] === 'ADL';
        })->count();

        //Todo Contamos cuantos niños tenemos asignados en el servicio
        $service_assign_child_count = $service->passengers->filter(function ($passenger) {
            return $passenger['passenger']['type'] === 'CHD';
        })->count();

        //Todo Obtenemos los adultos de la lista de pasajeros de la cotizacion
        $quote_passengers_adults = $quote_passengers->where('type', 'ADL');
        //Todo Obtenemos los niños de la lista de pasajeros de la cotizacion
        $quote_passengers_children = $quote_passengers->where('type', 'CHD');

        /*
         * Todo Si la cantidad de adultos que viene es mayor a la cantidad actual en la cotizacion entonces agregamos
         *  los adultos que faltan
         */

        if ($quote_adult_general > $service_assign_adult_count) {
            //Todo Buscamos los pasajeros que ya se encuentran asignados para no agregarlos nuevamente
            $passenger_ignored = $service->passengers->filter(function ($passenger) {
                return $passenger['passenger']['type'] === 'ADL';
            });

            //Todo si ya hay pasajeros asignados
            if ($passenger_ignored->count() > 0) {

                $passengers_not_assigned = $quote_passengers_adults->whereNotIn('id',
                    $passenger_ignored->pluck('quote_passenger_id'));
                $passengers_not_assigned = $passengers_not_assigned->values();
                foreach ($passengers_not_assigned as $passenger) {
                    $newAdultAssign = new QuoteServicePassenger();
                    $newAdultAssign->quote_service_id = $service->id;
                    $newAdultAssign->quote_passenger_id = $passenger->id;
                    $newAdultAssign->save();
                }
            } else {
                foreach ($quote_passengers_adults as $passenger) {
                    $newAdultAssign = new QuoteServicePassenger();
                    $newAdultAssign->quote_service_id = $service->id;
                    $newAdultAssign->quote_passenger_id = $passenger->id;
                    $newAdultAssign->save();
                }
            }
        }

        /*
        * Todo Si la cantidad de adultos que biene es menor a la cantidad actual en la cotizacion entonces eliminanos
        *  los ultimos agregados
        */
        if ($quote_adult_general < $service_assign_adult_count) {
            $qty_delete = $service_assign_adult_count - $quote_adult_general; // 3 - 1
            $adults_removed = $service->passengers->filter(function ($passenger) {
                return $passenger['passenger']['type'] === 'ADL';
            })->reverse()->take($qty_delete);
            if ($qty_delete > 0) {
                foreach ($adults_removed as $service_adult) {
                    $delete_adult = QuoteServicePassenger::find($service_adult->id);
                    if ($delete_adult) {
                        $delete_adult->delete();
                    }
                }
            }
        }

        /*
        * Todo Si la cantidad de niños que biene es mayor a la cantidad actual en la cotizacion entonces agregamos
        *  los niños que faltan, si no eliminamos
        */
        if ($quote_child_general > $service_assign_child_count) {
            //Todo Buscamos los pasajeros que ya se encuentran asignados para no agregarlos nuevamente
            $passenger_ignored = $service->passengers->filter(function ($passenger) {
                return $passenger['passenger']['type'] === 'CHD';
            });

            //Todo si ya hay pasajeros asignados solo los que no se han agregado
            if ($passenger_ignored->count() > 0) {


                $passengers_not_assigned = $quote_passengers_children->whereNotIn('id',
                    $passenger_ignored->pluck('quote_passenger_id'));
                $passengers_not_assigned = $passengers_not_assigned->values();

                foreach ($passengers_not_assigned as $passenger) {
                    $newAdultAssign = new QuoteServicePassenger();
                    $newAdultAssign->quote_service_id = $service->id;
                    $newAdultAssign->quote_passenger_id = $passenger->id;
                    $newAdultAssign->save();
                }
            } else {
                foreach ($quote_passengers_children as $passenger) {
                    $newAdultAssign = new QuoteServicePassenger();
                    $newAdultAssign->quote_service_id = $service->id;
                    $newAdultAssign->quote_passenger_id = $passenger->id;
                    $newAdultAssign->save();
                }
            }
        }

        /*
        * Todo Si la cantidad de niños que biene es menor a la cantidad actual en la cotizacion entonces eliminamos
        *  los ultimos agregados
        */

        if ($quote_child_general < $service_assign_child_count) {
            $qty_delete = $service_assign_child_count - $quote_child_general; // 3 - 1
            $children_removed = $service->passengers->filter(function ($passenger) {
                return $passenger['passenger']['type'] === 'CHD';
            })->reverse()->take($qty_delete);
            if ($qty_delete > 0) {
                foreach ($children_removed as $service_child) {
                    $delete_child = QuoteServicePassenger::find($service_child->id);
                    if ($delete_child) {
                        $delete_child->delete();
                    }
                }
            }
        }


    }

    public function assignPassengerToRoomsHotels(
        $hotel_groups,
        $quote_passengers
    ) {
        // foreach ($hotel_groups as $hotel_group) {
        //     $_quote_passengers = $quote_passengers->transform(function ($item) {
        //         $item->check = false;
        //         return $item;
        //     });

        //     foreach ($hotel_group as $key => $hotel_room) {
        //         $inserts = collect();
        //         QuoteServicePassenger::where('quote_service_id', $hotel_room['id'])->delete();

        //         $_quote_passengers_hotel = $_quote_passengers->where('check', false)
        //             ->where('type', 'ADL')
        //             ->take($hotel_room['adult']);

        //         foreach ($_quote_passengers_hotel as $passenger) {
        //             $inserts->add([
        //                 'hotel_id' => $hotel_room['object_id'],
        //                 'type_pax' => $passenger['type'],
        //                 'quote_service_id' => $hotel_room['id'],
        //                 'quote_passenger_id' => $passenger['id'],
        //             ]);
        //         }

        //         foreach ($_quote_passengers as $passenger) {
        //             foreach ($_quote_passengers_hotel as $passenger_hotel) {
        //                 if ($passenger['id'] === $passenger_hotel['id']) {
        //                     $passenger['check'] = true;
        //                 }
        //             }
        //         }


        //         if ($hotel_room['child'] > 0) {
        //             $_quote_passengers_hotel = $_quote_passengers->where('check', false)
        //                 ->where('type', 'CHD')
        //                 ->take($hotel_room['child']);

        //             foreach ($_quote_passengers_hotel as $passenger) {
        //                 $inserts->add([
        //                     'hotel_id' => $hotel_room['object_id'],
        //                     'type_pax' => $passenger['type'],
        //                     'quote_service_id' => $hotel_room['id'],
        //                     'quote_passenger_id' => $passenger['id'],
        //                 ]);
        //             }

        //             foreach ($_quote_passengers as $passenger) {
        //                 foreach ($_quote_passengers_hotel as $passenger_hotel) {
        //                     if ($passenger['id'] === $passenger_hotel['id']) {
        //                         $passenger['check'] = true;
        //                     }
        //                 }
        //             }
        //         }


        //         //Todo Insertamos los pasajeros en la acomodacion del hotel
        //         if ($inserts->count() > 0) {
        //             foreach ($inserts as $insert) {
        //                 DB::table('quote_service_passengers')->insert([
        //                     "quote_service_id" => $insert['quote_service_id'],
        //                     "quote_passenger_id" => $insert['quote_passenger_id'],
        //                     "created_at" => \Carbon\Carbon::now(),
        //                     "updated_at" => \Carbon\Carbon::now()
        //                 ]);
        //             }
        //         }
        //     }
        // }
    }

    /**
     * Permite asignar pasajeros a los servicios
     * @param $quote_id
     * @param $quote_service_id
     * @param $adult
     * @param $child
     */
    public function updateAssignPassengerService($quote_id, $quote_service_id, $adult, $child)
    {
        $quote_passengers = QuotePassenger::where('quote_id', $quote_id)
            ->orderBy('id')
            ->get(['id', 'type']);

        $quote_service = QuoteService::where('id', $quote_service_id)
            ->with([
                'passengers' => function ($query) {
                    $query->select(['id', 'quote_service_id', 'quote_passenger_id']);
                    $query->with([
                        'passenger' => function ($query) {
                            $query->select(['id', 'type']);
                        }
                    ]);
                }
            ])
            ->where('locked', 0)
            ->get([
                'id',
                'quote_category_id',
                'date_in',
                'type',
                'nights',
                'object_id',
                'adult',
                'child',
                'single',
                'double',
                'double_child',
                'triple',
                'triple_child',
                'locked'
            ]);

        foreach ($quote_service as $service) {
            $this->assignPassengerToService($service, $quote_passengers, $adult, $child);
        }
    }

    public function getQuantityAndEnableRoomsByTypeRoom($quote_category_id, $hotel_id, $date_in, $nights, $occupation)
    {
        $quantity_hotels_disabled = 0;
        $quantity_hotels_disabled_find = collect();
        //Todo Buscamos las habitaciones que estan deshabilitadas
        $hotels_rooms_disabled = DB::table('quote_services')->where('quote_category_id', $quote_category_id)
            ->where('type', 'hotel')
            ->where('object_id', $hotel_id)
            ->where('date_in', $date_in)
            ->where('nights', $nights)
            ->where('single', 0)
            ->where('double', 0)
            ->where('triple', 0)
            ->where('locked', 0)
            ->get();

        //Todo Si hay habitaciones deshabilitadas buscamos si hay en la ocupacion que se necesita
        if ($hotels_rooms_disabled->count() > 0) {
            foreach ($hotels_rooms_disabled as $hotel) {
                $service_room = DB::table('quote_service_rooms')->select('rate_plan_room_id')->where('quote_service_id',
                    $hotel->id)->first();
                $rate_plan_room = DB::table('rates_plans_rooms')->select('room_id')->where('id',
                    $service_room->rate_plan_room_id)->first();
                $room = DB::table('rooms')->select('room_type_id')->where('id', $rate_plan_room->room_id)->first();
                $room_type = DB::table('room_types')->select('occupation')->where('id', $room->room_type_id)->first();

                if ($room_type->occupation == $occupation) {
                    // $quantity_hotels_disabled_find->add($hotels_rooms_disabled->first());
                    $quantity_hotels_disabled_find->add($hotel);
                    $quantity_hotels_disabled = 1;
                }
            }
        }

        // dd($quantity_hotels_disabled_find);

        if ($quantity_hotels_disabled_find->count() > 0) {
            $quantity_hotels_disabled_find = $quantity_hotels_disabled_find->first();
            $quote_service = QuoteService::find($quantity_hotels_disabled_find->id);
            if ($quote_service) {
                if ($occupation === 1) {
                    $quote_service->adult = 1;
                    $quote_service->single = 1;
                }
                if ($occupation === 2) {
                    $quote_service->adult = 2;
                    $quote_service->double = 1;
                    $quote_service->double_child = $quantity_hotels_disabled_find->double_child;
                }
                if ($occupation === 3) {
                    $quote_service->adult = 3;
                    $quote_service->triple = 1;
                    $quote_service->triple_child = $quantity_hotels_disabled_find->triple_child;
                }
                $quote_service->save();
            }
        }

        return $quantity_hotels_disabled;
    }

    /**
     * Permite obtener la cantidad de habitaciones
     * @param $quote_category_id
     * @param $hotel_id
     * @param $date_in
     * @param $nights
     * @param $occupation
     * @param bool $count_rows
     * @return \Illuminate\Support\Collection|int
     */
    public function getQuantityOrQueryRoomsByTypeRoom(
        $quote_category_id,
        $hotel_id,
        $date_in,
        $nights,
        $occupation,
        $count_rows = true
    ) {

        $field = 'single';
        if ($occupation == 2) {
            $field = 'double';
        }

        if ($occupation == 3) {
            $field = 'triple';
        }

        if ($count_rows) {
            $quote_services = DB::table('quote_services')
                ->where('quote_category_id', $quote_category_id)
                ->where('type', 'hotel')
                ->where('object_id', $hotel_id)
                ->where('date_in', $date_in)
                ->where('nights', $nights)
                ->where('locked', 0)
                ->where($field, 1)->count();
        } else {
            $quote_services = DB::table('quote_services')
                ->where('quote_category_id', $quote_category_id)
                ->where('type', 'hotel')
                ->where('object_id', $hotel_id)
                ->where('date_in', $date_in)
                ->where('nights', $nights)
                ->where('locked', 0)
                ->where($field, 1)->orderBy('id', 'desc')->get();
        }

        return $quote_services;
    }


    /**
     * @param $quote_id
     * @param $quote_service_id
     * @param $adult
     * @param $child
     */
    public function updateListServicePassengers($quote_id, $quote_service_id, $adult, $child)
    {
        $service_ = DB::table('quote_services')->where('id', $quote_service_id)->first();
        // $adult = $service_->adult; $child = $service_->child;

        $search = DB::table('quote_services')
            ->where('quote_category_id', $service_->quote_category_id)
            ->where('type', $service_->type)->where('object_id', $service_->object_id)
            ->where('date_in', $service_->date_in)->where('date_out', $service_->date_out)
            ->where('id', '<>', $quote_service_id)->orderBy('id', 'ASC')
            ->where('locked', 0)
            ->first();

        $prev_quote_service_id = 0;

        if ($search != null and $search != '') {
            $prev_quote_service_id = $quote_service_id;
            $quote_service_id = $search->id;
        }

        $quote_service_ = QuoteServicePassenger::where('quote_service_id', $quote_service_id)
            ->with([
                'passenger' => function ($query) {
                    $query->select(['id', 'type']);
                }
            ])->orderBy('quote_passenger_id')->get();

        //Todo Contamos cuantos adultos tenemos en la cotizacion actualmente
        $adult_count = $quote_service_->filter(function ($passenger) {
            return $passenger['passenger']['type'] === 'ADL';
        })->count();

        //Todo Contamos cuantos niños tenemos en la cotizacion actualmente
        $child_count = $quote_service_->filter(function ($passenger) {
            return $passenger['passenger']['type'] === 'CHD';
        })->count();

        if ($prev_quote_service_id > 0) {
            $quote_service_id = $prev_quote_service_id;
        }

        /*
         * Todo Si la cantidad de adultos que viene es mayor a la cantidad actual en la cotizacion entonces agregamos
         *  los adultos que faltan
         */
        if ($adult > $adult_count) {
            $qty_passenger_add = $adult - $adult_count;
            $service_adults = $quote_service_->filter(function ($passenger) {
                return $passenger['passenger']['type'] === 'ADL';
            }); // 3

            if ($qty_passenger_add > 0) {
                $_count = 0;
                $quote_passengers_adults = QuotePassenger::where('quote_id', $quote_id)
                    ->where('type', 'ADL')
                    ->orderBy('id')
                    ->get(['id', 'type']); // 5

                if ($service_adults->count() === 0) {
                    foreach ($quote_passengers_adults as $passengers_adult) {
                        if (($qty_passenger_add > $_count and $service_->adult > $_count)) { // OR ($prev_quote_service_id > 0 AND $qty_passenger_add >= $_count)) {
                            $newAdult = new QuoteServicePassenger();
                            $newAdult->quote_service_id = $quote_service_id;
                            $newAdult->quote_passenger_id = $passengers_adult['id'];
                            $newAdult->save();
                            $_count++;
                        }
                    }
                } else {
                    $ignore = [];

                    foreach ($service_adults as $service_adult) {
                        $ignore[] = $service_adult['quote_passenger_id'];
                    }

                    foreach ($quote_passengers_adults as $passengers_adult) { // 3
                        if (!in_array($passengers_adult['id'], $ignore)) {
                            if (($qty_passenger_add > $_count and $service_->adult > $_count)) { // OR ($prev_quote_service_id > 0 AND $qty_passenger_add >= $_count)) {
                                $newAdult = new QuoteServicePassenger();
                                $newAdult->quote_service_id = $quote_service_id;
                                $newAdult->quote_passenger_id = $passengers_adult['id'];
                                $newAdult->save();
                                $_count++;
                            }
                        }
                    }
                }
            }
        }

        /*
            * Todo Si la cantidad de adultos que biene es menor a la cantidad actual en la cotizacion entonces eliminanos
            *  los ultimos agregados
            */
        if ($adult < $adult_count) {
            $qty_delete = $adult_count - $adult; // 3 - 1
            $service_adults = $quote_service_->filter(function ($passenger) {
                return $passenger['passenger']['type'] === 'ADL';
            })->reverse();
            $_count = 0;

            if ($qty_delete > 0) {
                foreach ($service_adults as $service_adult) {
                    if ($qty_delete > $_count) {
                        $delete_adult = QuoteServicePassenger::find($service_adult['id']);
                        if ($delete_adult) {
                            $delete_adult->delete();
                            $_count++;
                        }
                    }
                }
            }
        }

        /*
            * Todo Si la cantidad de niños que biene es mayor a la cantidad actual en la cotizacion entonces agregamos
            *  los niños que faltan, si no eliminamos
            */
        if ($child > $child_count) {
            $qty_passenger_add = $child - $child_count;
            $service_children = $quote_service_->filter(function ($passenger) {
                return $passenger['passenger']['type'] === 'CHD';
            });
            $_count = 0;

            if ($qty_passenger_add > 0) {
                $quote_passengers_children = QuotePassenger::where('quote_id', $quote_id)
                    ->where('type', 'CHD')
                    ->orderBy('id')
                    ->get(['id', 'type']);
                if ($service_children->count() === 0) {
                    foreach ($quote_passengers_children as $passengers_child) {
                        if (($qty_passenger_add > $_count and $service_->child > $_count)) {
                            // OR ($prev_quote_service_id > 0 AND $qty_passenger_add >= $_count)) {
                            $newAdult = new QuoteServicePassenger();
                            $newAdult->quote_service_id = $quote_service_id;
                            $newAdult->quote_passenger_id = $passengers_child['id'];
                            $newAdult->save();
                            $_count++;
                        }
                    }
                } else {

                    $ignore = [];

                    foreach ($service_children as $service_child) {
                        $ignore[] = $service_child['id'];
                    }

                    foreach ($quote_passengers_children as $passengers_child) {
                        if (!in_array($passengers_child['quote_passenger_id'], $ignore)) {
                            if (($qty_passenger_add > $_count and $service_->child > $_count)) { // OR ($prev_quote_service_id > 0 AND $qty_passenger_add >= $_count)) {
                                $newAdult = new QuoteServicePassenger();
                                $newAdult->quote_service_id = $quote_service_id;
                                $newAdult->quote_passenger_id = $passengers_child['id'];
                                $newAdult->save();
                                $_count++;
                            }
                        }
                    }
                }
            }

        }

        /*
            * Todo Si la cantidad de niños que biene es menor a la cantidad actual en la cotizacion entonces eliminamos
            *  los ultimos agregados
            */
        if ($child < $child_count) {
            $qty_delete = $child_count - $child;
            $service_children = $quote_service_->filter(function ($passenger) {
                return $passenger['passenger']['type'] === 'CHD';
            })->reverse();

            if ($qty_delete > 0) {
                $_count = 0;
                foreach ($service_children as $service_child) {
                    if ($qty_delete > $_count) {
                        $delete_child = QuoteServicePassenger::find($service_child['id']);
                        if ($delete_child) {
                            $delete_child->delete();
                            $_count++;
                        }
                    }
                }
            }
        }
    }


    /**
     * funcion permite eliminar las habitaciones que sen encuentran sin utilizar
     * @param $quote_service
     * @param $occupation
     */
    public function destroyRoomsDisabledByOccupationsAddFromHeader($quote_service, $occupation)
    {
        $hotels_rooms_disabled = DB::table('quote_services')->where('quote_category_id',
            $quote_service->quote_category_id)
            ->where('type', 'hotel')
            ->where('object_id', $quote_service->object_id)
            ->where('date_in', $quote_service->date_in)
            ->where('nights', $quote_service->nights)
            ->where('single', 0)
            ->where('double', 0)
            ->where('triple', 0)
            ->where('locked', 0)
            ->get();

        foreach ($hotels_rooms_disabled as $hotel) {
            $service_room = DB::table('quote_service_rooms')->select('rate_plan_room_id')->where('quote_service_id',
                $hotel->id)->first();
            $rate_plan_room = DB::table('rates_plans_rooms')->select('room_id')->where('id',
                $service_room->rate_plan_room_id)->first();
            $room = DB::table('rooms')->select('room_type_id')->where('id',
                $rate_plan_room->room_id)->first();
            $room_type = DB::table('room_types')->select('occupation')->where('id',
                $room->room_type_id)->first();
            if ($room_type->occupation == $occupation) {
                // $delete = QuoteService::find($quote_service->id);
                $delete = QuoteService::find($hotel->id);
                $delete->delete();
            }
        }

    }


    /**
     * funcion permite eliminar las habitaciones que sen encuentran sin utilizar
     * @param $quote_service
     * @param $occupation
     */
    public function destroyRoomsDisabledByOccupations($quote_service, $occupation)
    {
        $hotels_rooms_disabled = DB::table('quote_services')->where('quote_category_id',
            $quote_service->quote_category_id)
            ->where('type', 'hotel')
            ->where('object_id', $quote_service->object_id)
            ->where('date_in', $quote_service->date_in)
            ->where('nights', $quote_service->nights)
            ->where('single', 0)
            ->where('double', 0)
            ->where('triple', 0)
            ->where('locked', 0)
            ->get();

        foreach ($hotels_rooms_disabled as $hotel) {
            $service_room = DB::table('quote_service_rooms')->select('rate_plan_room_id')->where('quote_service_id',
                $hotel->id)->first();
            $rate_plan_room = DB::table('rates_plans_rooms')->select('room_id')->where('id',
                $service_room->rate_plan_room_id)->first();
            $room = DB::table('rooms')->select('room_type_id')->where('id',
                $rate_plan_room->room_id)->first();
            $room_type = DB::table('room_types')->select('occupation')->where('id',
                $room->room_type_id)->first();
            if ($room_type->occupation == $occupation) {
                $delete = QuoteService::find($quote_service->id);
                $delete->delete();
            }
        }

    }

    /**
     * Funcion permite asignar los pasajeros de un hotel en especifico
     * @param $quote_service_id
     * @param $quote_id
     */
    public function updateListPassengersRoomsHotel(
        $quote_service_id,
        $quote_id
    ) {
        $quote_passengers = QuotePassenger::where('quote_id', $quote_id)
            ->orderBy('id')
            ->get(['id', 'type']);
        $quote_service = DB::table('quote_services')->where('id', $quote_service_id)->first();
        if ($quote_service) {
            $quote_services = QuoteService::where('type', 'hotel')
                ->where('quote_category_id', $quote_service->quote_category_id)
                ->where('object_id', $quote_service->object_id)
                ->where('date_in', $quote_service->date_in)
                ->where('nights', $quote_service->nights)
                ->where('locked', 0)
                ->with([
                    'passengers' => function ($query) {
                        $query->select(['id', 'quote_service_id', 'quote_passenger_id']);
                        $query->with([
                            'passenger' => function ($query) {
                                $query->select(['id', 'type']);
                            }
                        ]);
                    }
                ])->get([
                    'id',
                    'quote_category_id',
                    'date_in',
                    'type',
                    'nights',
                    'object_id',
                    'adult',
                    'child',
                    'single',
                    'double',
                    'double_child',
                    'triple',
                    'triple_child',
                    'locked'
                ]);

            $hotels_groups = collect();
            foreach ($quote_services as $service) {
                $total_accommodations = (int)$service->single + (int)$service->double + (int)$service->triple + (int)$service->double_child + (int)$service->triple_child;
                if ($total_accommodations > 0) {
                    $hotels_groups->add($service);
                }
            }

            if ($hotels_groups->count() > 0) {
                $hotels_groups = $hotels_groups->groupBy(function ($item, $key) {
                    $locked = ($item['locked']) ? 1 : 0;
                    $date = convertDate($item["date_in"], '/', '-', 1);
                    return $date . '|' . $item['nights'] . '|' . $item['object_id'] . '|' . $locked;
                });
                $this->assignPassengerToRoomsHotels($hotels_groups, $quote_passengers);
            }
        }
    }

    /**
     * Funcion permite actualizar la acomodacion general de la cotizacion
     * @param $quote_id
     */
    public function updateOccupationRoomHotelByQuote($quote_id)
    {
        $categories = QuoteCategory::where('quote_id', $quote_id)
            ->with([
                'services' => function ($query) {
                    $query->select([
                        'id',
                        'quote_category_id',
                        'date_in',
                        'type',
                        'nights',
                        'object_id',
                        'adult',
                        'child',
                        'single',
                        'double',
                        'triple',
                        'locked'
                    ]);
                    $query->where('type', 'hotel');
                }
            ])->get();
        $hotels_groups = collect();
        foreach ($categories as $category) {
            foreach ($category->services as $service) {
                $total_accommodations = (int)$service->single + (int)$service->double + (int)$service->triple + (int)$service->double_child + (int)$service->triple_child;
                if ($total_accommodations > 0) {
                    $hotels_groups->add($service);
                }

            }
        }

        if ($hotels_groups->count() > 0) {
            $hotels_groups = $hotels_groups->groupBy(function ($item, $key) {
                $locked = ($item['locked']) ? 1 : 0;
                $date = convertDate($item["date_in"], '/', '-', 1);
                return $date . '|' . $item['nights'] . '|' . $item['object_id'] . '|' . $locked;
            });

            $single_rooms = collect();
            $double_rooms = collect();
            $triple_rooms = collect();
            foreach ($hotels_groups as $hotels_group) {
                $single_rooms->add($hotels_group->sum('single'));
                $double_rooms->add($hotels_group->sum('double'));
                $triple_rooms->add($hotels_group->sum('triple'));
            }
            $single = 0;
            $double = 0;
            $triple = 0;
            if ($single_rooms->count() > 0) {
                $single = $single_rooms->max();
            }
            if ($double_rooms->count() > 0) {
                $double = $double_rooms->max();
            }
            if ($triple_rooms->count() > 0) {
                $triple = $triple_rooms->max();
            }

            QuoteAccommodation::where('quote_id', $quote_id)->update([
                'single' => $single,
                'double' => $double,
                'triple' => $triple,
            ]);

        }

    }

    /**
     * Funcion permite agregar habitaciones segun el tablero
     * @param $occupation
     * @param $quote_service
     * @param $rate_plan_room_id
     * @param $simple
     * @param $double
     * @param $triple
     * @param $double_child
     * @param $triple_child
     */
    public function addRoomsToHotel(
        $occupation,
        $quote_service,
        $rate_plan_room_id,
        $simple,
        $double,
        $triple,
        $double_child,
        $triple_child
    ) {
        //Todo Simple
        if ($occupation == 1) {
            //Todo Busco la cantidad de habitaciones en simples
            $quantity_hotels = $this->getQuantityOrQueryRoomsByTypeRoom($quote_service->quote_category_id,
                $quote_service->object_id, $quote_service->date_in, $quote_service->nights,
                $occupation);
            if ((int)$simple > $quantity_hotels) {
                //Todo busco la cantidad de habitaciones simples deshabilitadas
                $qtyDisabledRoom = $this->getQuantityAndEnableRoomsByTypeRoom($quote_service->quote_category_id,
                    $quote_service->object_id, $quote_service->date_in, $quote_service->nights,
                    $occupation);
                // dd($qtyDisabledRoom);
                $total_rooms_created = (int)$simple - ($quantity_hotels + $qtyDisabledRoom);
                if ($total_rooms_created > 0) {
                    for ($i = 0; $i < $total_rooms_created; $i++) {
                        $quote_service_id = DB::table('quote_services')->insertGetId([
                            'quote_category_id' => $quote_service->quote_category_id,
                            'type' => 'hotel',
                            'object_id' => $quote_service->object_id,
                            'order' => $quote_service->order,
                            'date_in' => $quote_service->date_in,
                            'date_out' => $quote_service->date_out,
                            'nights' => $quote_service->nights,
                            'adult' => 1,
                            'child' => 0,
                            'infant' => 0,
                            'single' => 1,
                            'double' => 0,
                            'double_child' => 0,
                            'triple' => 0,
                            'triple_child' => 0,
                            'triple_active' => 0,
                            'locked' => $quote_service->locked,
                            'created_at' => $quote_service->created_at,
                            'updated_at' => $quote_service->updated_at,
                            'on_request' => $quote_service->on_request,
                            'optional' => $quote_service->optional,
                        ]);
                        DB::table('quote_service_rooms')->insert([
                            'quote_service_id' => $quote_service_id,
                            'rate_plan_room_id' => $rate_plan_room_id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    }
                }
            }
            if ((int)$simple < $quantity_hotels && $simple > 0) {
                $quantity_hotels_deleted = $quantity_hotels - $simple;
                $quote_services_deleted = $this->getQuantityOrQueryRoomsByTypeRoom($quote_service->quote_category_id,
                    $quote_service->object_id, $quote_service->date_in, $quote_service->nights,
                    $occupation, false);
                foreach ($quote_services_deleted as $quote_service) {
                    if ($quantity_hotels_deleted > 0) {
                        DB::table('quote_service_passengers')->where('quote_service_id',
                            $quote_service->id)->delete();
                        DB::table('quote_service_rooms')->where('quote_service_id',
                            $quote_service->id)->delete();
                        DB::table('quote_services')->where('id', $quote_service->id)->delete();
                        $quantity_hotels_deleted--;
                    }
                }
            }

            if ((int)$simple == 0) {
                $quote_services_deleted = $this->getQuantityOrQueryRoomsByTypeRoom($quote_service->quote_category_id,
                    $quote_service->object_id, $quote_service->date_in, $quote_service->nights,
                    $occupation, false);
                foreach ($quote_services_deleted as $key => $quote_service) {
                    if ($key == 0) {
                        DB::table('quote_services')->where('id', $quote_service->id)->update([
                            'single' => 0,
                            'double' => 0,
                            'triple' => 0,
                            'double_child' => 0,
                            'triple_child' => 0,
                        ]);
                    } else {
                        DB::table('quote_service_passengers')->where('quote_service_id',
                            $quote_service->id)->delete();
                        DB::table('quote_service_rooms')->where('quote_service_id',
                            $quote_service->id)->delete();
                        DB::table('quote_services')->where('id', $quote_service->id)->delete();
                    }
                }
            }

        }
        //Todo Doble
        if ($occupation == 2) {
            //Todo Busco la cantidad de habitaciones en doble
            $quantity_hotels = $this->getQuantityOrQueryRoomsByTypeRoom($quote_service->quote_category_id,
                $quote_service->object_id, $quote_service->date_in, $quote_service->nights, $occupation);
            if ((int)$double > $quantity_hotels) {
                //Todo busco la cantidad de habitaciones dobles deshabilitadas
                $qtyDisabledRoom = $this->getQuantityAndEnableRoomsByTypeRoom($quote_service->quote_category_id,
                    $quote_service->object_id, $quote_service->date_in, $quote_service->nights,
                    $occupation);
                $total_rooms_created = (int)$double - ($quantity_hotels + $qtyDisabledRoom);
                if ($total_rooms_created > 0) {
                    for ($i = 0; $i < $total_rooms_created; $i++) {
                        $quote_service_id = DB::table('quote_services')->insertGetId([
                            'quote_category_id' => $quote_service->quote_category_id,
                            'type' => 'hotel',
                            'object_id' => $quote_service->object_id,
                            'order' => $quote_service->order,
                            'date_in' => $quote_service->date_in,
                            'date_out' => $quote_service->date_out,
                            'nights' => $quote_service->nights,
                            'adult' => 2,
                            'child' => 0,
                            'infant' => 0,
                            'single' => 0,
                            'double' => 1,
                            'double_child' => $double_child,
                            'triple' => 0,
                            'triple_child' => 0,
                            'triple_active' => 0,
                            'locked' => $quote_service->locked,
                            'created_at' => $quote_service->created_at,
                            'updated_at' => $quote_service->updated_at,
                            'on_request' => $quote_service->on_request,
                            'optional' => $quote_service->optional,
                        ]);
                        DB::table('quote_service_rooms')->insert([
                            'quote_service_id' => $quote_service_id,
                            'rate_plan_room_id' => $rate_plan_room_id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    }
                }
            }

            if ($double < $quantity_hotels && $double > 0) {
                $quantity_hotels_deleted = $quantity_hotels - $double;
                $quote_services_deleted = $this->getQuantityOrQueryRoomsByTypeRoom($quote_service->quote_category_id,
                    $quote_service->object_id, $quote_service->date_in, $quote_service->nights, $occupation, false);
                foreach ($quote_services_deleted as $quote_service) {
                    if ($quantity_hotels_deleted > 0) {
                        DB::table('quote_service_passengers')->where('quote_service_id',
                            $quote_service->id)->delete();
                        DB::table('quote_service_rooms')->where('quote_service_id',
                            $quote_service->id)->delete();
                        DB::table('quote_services')->where('id', $quote_service->id)->delete();
                        $quantity_hotels_deleted--;
                    }
                }
            }

            if ($double == 0) {
                $quote_services_deleted = $this->getQuantityOrQueryRoomsByTypeRoom($quote_service->quote_category_id,
                    $quote_service->object_id, $quote_service->date_in, $quote_service->nights, $occupation, false);
                foreach ($quote_services_deleted as $key => $quote_service) {
                    if ($key == 0) {
                        DB::table('quote_services')->where('id', $quote_service->id)->update([
                            'single' => 0,
                            'double' => 0,
                            'triple' => 0,
                            'double_child' => 0,
                            'triple_child' => 0,
                        ]);
                    } else {
                        $QSP = QuoteServicePassenger::where('quote_service_id', $quote_service->id)->get(['id']);
                        QuoteServicePassenger::destroy($QSP->toArray());

                        $QSR = QuoteServiceRoom::where('quote_service_id', $quote_service->id)->get(['id']);
                        QuoteServiceRoom::destroy($QSR->toArray());

                        $QS = QuoteService::find($quote_service->id);
                        $QS->delete();
                    }
                }
            }

        }
        //Todo Triple
        if ($occupation == 3) {
            //Todo Busco la cantidad de habitaciones en triples
            $quantity_hotels = $this->getQuantityOrQueryRoomsByTypeRoom($quote_service->quote_category_id,
                $quote_service->object_id, $quote_service->date_in, $quote_service->nights, $occupation);
            if ((int)$triple > $quantity_hotels) {
                //Todo busco la cantidad de habitaciones triples deshabilitadas
                $qtyDisabledRoom = $this->getQuantityAndEnableRoomsByTypeRoom($quote_service->quote_category_id,
                    $quote_service->object_id, $quote_service->date_in, $quote_service->nights, $occupation);
                $total_rooms_created = (int)$triple - ($quantity_hotels + $qtyDisabledRoom);
                if ($total_rooms_created > 0) {
                    for ($i = 0; $i < $total_rooms_created; $i++) {
                        $quote_service_id = DB::table('quote_services')->insertGetId([
                            'quote_category_id' => $quote_service->quote_category_id,
                            'type' => 'hotel',
                            'object_id' => $quote_service->object_id,
                            'order' => $quote_service->order,
                            'date_in' => $quote_service->date_in,
                            'date_out' => $quote_service->date_out,
                            'nights' => $quote_service->nights,
                            'adult' => 3, // $quote_service->adult,
                            'child' => 0, // $quote_service->child,
                            'infant' => $quote_service->infant,
                            'single' => 0,
                            'double' => 0,
                            'double_child' => 0,
                            'triple' => 1,
                            'triple_child' => $triple_child,
                            'triple_active' => $quote_service->triple_active,
                            'locked' => $quote_service->locked,
                            'created_at' => $quote_service->created_at,
                            'updated_at' => $quote_service->updated_at,
                            'on_request' => $quote_service->on_request,
                            'optional' => $quote_service->optional,
                        ]);
                        DB::table('quote_service_rooms')->insert([
                            'quote_service_id' => $quote_service_id,
                            'rate_plan_room_id' => $rate_plan_room_id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    }
                }
            }
            if ($triple < $quantity_hotels && $triple > 0) {
                $quantity_hotels_deleted = $quantity_hotels - $triple;

                $quote_services_deleted = $this->getQuantityOrQueryRoomsByTypeRoom($quote_service->quote_category_id,
                    $quote_service->object_id, $quote_service->date_in, $quote_service->nights, $occupation, false);

                foreach ($quote_services_deleted as $quote_service) {
                    if ($quantity_hotels_deleted > 0) {
                        $QSP = QuoteServicePassenger::where('quote_service_id', $quote_service->id)->get(['id']);
                        QuoteServicePassenger::destroy($QSP->toArray());

                        $QSR = QuoteServiceRoom::where('quote_service_id', $quote_service->id)->get(['id']);
                        QuoteServiceRoom::destroy($QSR->toArray());

                        $QS = QuoteService::find($quote_service->id);
                        $QS->delete();
                        $quantity_hotels_deleted--;
                    }
                }
            }
            if ($triple == 0) {
                $quote_services_deleted = $this->getQuantityOrQueryRoomsByTypeRoom($quote_service->quote_category_id,
                    $quote_service->object_id, $quote_service->date_in, $quote_service->nights, $occupation, false);
                // dd($quote_services_deleted);
                foreach ($quote_services_deleted as $key => $quote_service) {
                    if ($key == 0) {
                        DB::table('quote_services')->where('id', $quote_service->id)->update([
                            'single' => 0,
                            'double' => 0,
                            'triple' => 0,
                            'double_child' => 0,
                            'triple_child' => 0,
                        ]);
                    } else {
                        $QSP = QuoteServicePassenger::where('quote_service_id', $quote_service->id)->get(['id']);
                        QuoteServicePassenger::destroy($QSP->toArray());

                        $QSR = QuoteServiceRoom::where('quote_service_id', $quote_service->id)->get(['id']);
                        QuoteServiceRoom::destroy($QSR->toArray());

                        $QS = QuoteService::find($quote_service->id);
                        $QS->delete();
                    }
                }
            }
        }
    }

    public function setAccommodationInHotels($quote_categories, $adults, $children, $quoteDistributions)
    {

        foreach ($quote_categories as $category) {


            $quote_services = DB::table('quote_services')
                ->leftJoin('quote_service_rooms', 'quote_service_rooms.quote_service_id', '=', 'quote_services.id')
                ->leftJoin('rates_plans_rooms', 'quote_service_rooms.rate_plan_room_id', '=', 'rates_plans_rooms.id')
                ->leftJoin('rooms', 'rates_plans_rooms.room_id', '=', 'rooms.id')
                ->leftJoin('room_types', 'rooms.room_type_id', '=', 'room_types.id')
                ->select('quote_services.*', 'quote_service_rooms.rate_plan_room_id', 'room_types.occupation')
                ->where('quote_category_id', $category->id)
                ->orderBy('date_in')
                ->get();
            $hotels_collection = collect();
            $order = 1;
            foreach ($quote_services as $quote_service) {
                if ($quote_service->type == 'hotel') {
                    // solo usamos las habitaciones que no han sido ocultados.
                    $total_accommodations = (int)$quote_service->single + (int)$quote_service->double + (int)$quote_service->triple + (int)$quote_service->double_child + (int)$quote_service->triple_child;
                    if ($total_accommodations > 0) {
                        $hotels_collection->add($quote_service);
                    }
                    // $hotels_collection->add($quote_service);
                } else {
                    DB::table('quote_services')->where('id', $quote_service->id)->update([
                        'adult' => $adults,
                        'child' => $children,
                        'order' => $order,
                    ]);
                    $order++;
                }
            }

            if ($hotels_collection->count() > 0) {
                $hotels_collection = $hotels_collection->groupBy(function ($service) {
                    return str_replace('-', '',
                            $service->date_in) . '_' . $service->nights . '_' . $service->object_id . '_' . $service->locked;
                });

                foreach ($hotels_collection as $hotel_group) {

                    $order = $this->updatePaxAcomodacion($hotel_group, $adults, $children, $order, $quoteDistributions);

                }

            }

        }

    }

    public function updatePaxAcomodacion($hotel_group, $adults, $children, $order, $quoteDistributions)
    {

        // $_quote_passengers = $quoteDistributions->transform(function ($item) {
        //     $item->check = false;
        //     return $item;
        // });

        $hotel_group = $hotel_group->transform(function ($item) {
            $item->check = false;
            return $item;
        });

        $hotel_group = $hotel_group->sortBy('occupation');

        foreach ($quoteDistributions as $distribution) {

            foreach ($hotel_group as $index => $hotel) {
                if ($hotel->rate_plan_room_id) {

                    if ($distribution->single == $hotel->single and $distribution->double == $hotel->double and $distribution->triple == $hotel->triple) {

                        if ($hotel->check != false) {
                            continue;
                        }

                        DB::table('quote_services')->where('id', $hotel->id)->update([
                            'adult' => $distribution->adult,
                            'child' => $distribution->child,
                            'order' => $order,
                        ]);
                        $order++;
                        // $hotel->check = true;
                        $hotel_group[$index]->check = $distribution->passengers->toArray();
                        QuoteServicePassenger::where('quote_service_id', $hotel->id)->delete();

                        foreach ($distribution->passengers as $passenger) {
                            $quoteServicePassenger = new QuoteServicePassenger();
                            $quoteServicePassenger->quote_service_id = $hotel->id;
                            $quoteServicePassenger->quote_passenger_id = $passenger->quote_passenger_id;
                            $quoteServicePassenger->save();
                        }
                        break;
                    }
                }

            }

        }

        // dd($quoteDistributions->toArray(),$hotel_group);


        return $order;

    }


    public function updatePaxAcomodacion_bk($hotel_group, $adults, $children, $order)
    {

        $totalPaxAdultoTomado = 0;
        $totalPaxChildrenTomado = 0;
        $allAduls = false;
        $x = 1;
        $hotel_group = $hotel_group->sortBy('occupation');
        foreach ($hotel_group as $hotel) {
            $adult = 0;
            $child = 0;
            if ($hotel->rate_plan_room_id) {

                if (!$allAduls) {

                    if ($hotel->occupation == 1) {

                        if (count($hotel_group) == $x) { // es el ultimo registro
                            $adult = $adults - $totalPaxAdultoTomado;
                            $child = $children - $totalPaxChildrenTomado;
                        } else {

                            if (($totalPaxAdultoTomado + 1) <= $adults) {
                                $totalPaxAdultoTomado = $totalPaxAdultoTomado + 1;
                                $adult = 1;
                                $child = 0;
                            } else {

                                $adult = $adults - $totalPaxAdultoTomado;
                                $child = 0;
                                if ($children > 0) {

                                    $childTomados = 1 - $adult;
                                    if ($childTomados <= $children) {
                                        $child = $childTomados;
                                    } else {
                                        $child = $children;
                                    }

                                }
                                $totalPaxChildrenTomado = $child;

                                $allAduls = true;
                            }
                        }

                    }

                    if ($hotel->occupation == 2) {

                        if (count($hotel_group) == $x) { // es el ultimo registro
                            $adult = $adults - $totalPaxAdultoTomado;
                            $child = $children - $totalPaxChildrenTomado;
                        } else {

                            if (($totalPaxAdultoTomado + 2) <= $adults) {
                                $totalPaxAdultoTomado = $totalPaxAdultoTomado + 2;
                                $adult = 2;
                                $child = 0;
                            } else {

                                $adult = $adults - $totalPaxAdultoTomado;
                                $child = 0;
                                if ($children > 0) {

                                    $childTomados = 2 - $adult;
                                    if ($childTomados <= $children) {
                                        $child = $childTomados;
                                    } else {
                                        $child = $children;
                                    }

                                }
                                $totalPaxChildrenTomado = $child;

                                $allAduls = true;
                            }
                        }

                    }

                    if ($hotel->occupation == 3) {

                        if (count($hotel_group) == $x) { // es el ultimo registro
                            $adult = $adults - $totalPaxAdultoTomado;
                            $child = $children - $totalPaxChildrenTomado;
                        } else {

                            if (($totalPaxAdultoTomado + 3) <= $adults) {
                                $totalPaxAdultoTomado = $totalPaxAdultoTomado + 3;
                                $adult = 3;
                                $child = 0;
                            } else {

                                $adult = $adults - $totalPaxAdultoTomado;
                                $child = 0;
                                if ($children > 0) {

                                    $childTomados = 3 - $adult;
                                    if ($childTomados <= $children) {
                                        $child = $childTomados;
                                    } else {
                                        $child = $children;
                                    }

                                }
                                $totalPaxChildrenTomado = $child;
                                $allAduls = true;

                            }
                        }
                    }

                } else {

                    if ($hotel->occupation == 1) {

                        $adult = 0;
                        if (count($hotel_group) == $x) { // es el ultimo registro
                            $child = $children - $totalPaxChildrenTomado;
                        } else {

                            if (($totalPaxChildrenTomado + 1) <= $children) {
                                $totalPaxChildrenTomado = $totalPaxChildrenTomado + 1;
                                $child = 1;
                            } else {
                                $child = $children - $totalPaxChildrenTomado;
                                $totalPaxChildrenTomado = $totalPaxChildrenTomado + $child;
                            }
                        }

                    }

                    if ($hotel->occupation == 2) {

                        $adult = 0;
                        if (count($hotel_group) == $x) { // es el ultimo registro
                            $child = $children - $totalPaxChildrenTomado;
                        } else {

                            if (($totalPaxChildrenTomado + 2) <= $children) {
                                $totalPaxChildrenTomado = $totalPaxChildrenTomado + 2;
                                $child = 2;
                            } else {
                                $child = $children - $totalPaxChildrenTomado;
                                $totalPaxChildrenTomado = $totalPaxChildrenTomado + $child;
                            }
                        }
                    }

                    if ($hotel->occupation == 3) {

                        $adult = 0;
                        if (count($hotel_group) == $x) { // es el ultimo registro
                            $child = $children - $totalPaxChildrenTomado;
                        } else {

                            if (($totalPaxChildrenTomado + 3) <= $children) {
                                $totalPaxChildrenTomado = $totalPaxChildrenTomado + 3;
                                $child = 3;
                            } else {
                                $child = $children - $totalPaxChildrenTomado;
                                $totalPaxChildrenTomado = $totalPaxChildrenTomado + $child;
                            }
                        }
                    }


                }

                DB::table('quote_services')->where('id', $hotel->id)->update([
                    'adult' => $adult,
                    'child' => $child,
                    'order' => $order,
                ]);
                $order++;
                $x++;
            }

        }

        return $order;

    }


    public function updatePaxAcomodacionGeneral($hotel_group, $adults, $children, $order)
    {

        $totalPaxAdultoTomado = 0;
        $totalPaxChildrenTomado = 0;
        $allAduls = false;
        $x = 1;
        foreach ($hotel_group as $px => $hotel) {
            $adult = 0;
            $child = 0;

            if (!$allAduls) {

                if ($hotel['type_room'] == 'single') {

                    if (count($hotel_group) == $x) { // es el ultimo registro
                        $adult = $adults - $totalPaxAdultoTomado;
                        $child = $children - $totalPaxChildrenTomado;
                    } else {

                        if (($totalPaxAdultoTomado + 1) <= $adults) {
                            $totalPaxAdultoTomado = $totalPaxAdultoTomado + 1;
                            $adult = 1;
                            $child = 0;
                        } else {

                            $adult = $adults - $totalPaxAdultoTomado;
                            $child = 0;
                            if ($children > 0) {

                                $childTomados = 1 - $adult;
                                if ($childTomados <= $children) {
                                    $child = $childTomados;
                                } else {
                                    $child = $children;
                                }

                            }
                            $totalPaxChildrenTomado = $child;

                            $allAduls = true;
                        }
                    }

                }

                if ($hotel['type_room'] == 'double') {

                    if (count($hotel_group) == $x) { // es el ultimo registro
                        $adult = $adults - $totalPaxAdultoTomado;
                        $child = $children - $totalPaxChildrenTomado;
                    } else {

                        if (($totalPaxAdultoTomado + 2) <= $adults) {
                            $totalPaxAdultoTomado = $totalPaxAdultoTomado + 2;
                            $adult = 2;
                            $child = 0;
                        } else {

                            $adult = $adults - $totalPaxAdultoTomado;
                            $child = 0;
                            if ($children > 0) {

                                $childTomados = 2 - $adult;
                                if ($childTomados <= $children) {
                                    $child = $childTomados;
                                } else {
                                    $child = $children;
                                }

                            }
                            $totalPaxChildrenTomado = $child;

                            $allAduls = true;
                        }
                    }

                }

                if ($hotel['type_room'] == 'triple') {

                    if (count($hotel_group) == $x) { // es el ultimo registro
                        $adult = $adults - $totalPaxAdultoTomado;
                        $child = $children - $totalPaxChildrenTomado;
                    } else {

                        if (($totalPaxAdultoTomado + 3) <= $adults) {
                            $totalPaxAdultoTomado = $totalPaxAdultoTomado + 3;
                            $adult = 3;
                            $child = 0;
                        } else {

                            $adult = $adults - $totalPaxAdultoTomado;
                            $child = 0;
                            if ($children > 0) {

                                $childTomados = 3 - $adult;
                                if ($childTomados <= $children) {
                                    $child = $childTomados;
                                } else {
                                    $child = $children;
                                }

                            }
                            $totalPaxChildrenTomado = $child;
                            $allAduls = true;

                        }
                    }
                }

            } else {

                if ($hotel['type_room'] == 'single') {

                    $adult = 0;
                    if (count($hotel_group) == $x) { // es el ultimo registro
                        $child = $children - $totalPaxChildrenTomado;
                    } else {

                        if (($totalPaxChildrenTomado + 1) <= $children) {
                            $totalPaxChildrenTomado = $totalPaxChildrenTomado + 1;
                            $child = 1;
                        } else {
                            $child = $children - $totalPaxChildrenTomado;
                            $totalPaxChildrenTomado = $totalPaxChildrenTomado + $child;
                        }
                    }

                }

                if ($hotel['type_room'] == 'double') {

                    $adult = 0;
                    if (count($hotel_group) == $x) { // es el ultimo registro
                        $child = $children - $totalPaxChildrenTomado;
                    } else {

                        if (($totalPaxChildrenTomado + 2) <= $children) {
                            $totalPaxChildrenTomado = $totalPaxChildrenTomado + 2;
                            $child = 2;
                        } else {
                            $child = $children - $totalPaxChildrenTomado;
                            $totalPaxChildrenTomado = $totalPaxChildrenTomado + $child;
                        }
                    }
                }

                if ($hotel['type_room'] == 'triple') {

                    $adult = 0;
                    if (count($hotel_group) == $x) { // es el ultimo registro
                        $child = $children - $totalPaxChildrenTomado;
                    } else {

                        if (($totalPaxChildrenTomado + 3) <= $children) {
                            $totalPaxChildrenTomado = $totalPaxChildrenTomado + 3;
                            $child = 3;
                        } else {
                            $child = $children - $totalPaxChildrenTomado;
                            $totalPaxChildrenTomado = $totalPaxChildrenTomado + $child;
                        }
                    }
                }


            }

            $hotel_group[$px]['adult'] = $adult;
            $hotel_group[$px]['child'] = $child;
            // $hotel_group[$px]['order'] = $order;

            $order++;
            $x++;


        }

        return $hotel_group;

    }


    public function setAccommodationInHotelsForQuoteService(
        $quote_category_id,
        $hotel_id,
        $date_in,
        $adults,
        $children,
        $quoteDistributions
    ) {


        $quote_services = DB::table('quote_services')
            ->leftJoin('quote_service_rooms', 'quote_service_rooms.quote_service_id', '=', 'quote_services.id')
            ->leftJoin('rates_plans_rooms', 'quote_service_rooms.rate_plan_room_id', '=', 'rates_plans_rooms.id')
            ->leftJoin('rooms', 'rates_plans_rooms.room_id', '=', 'rooms.id')
            ->leftJoin('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->select('quote_services.*', 'quote_service_rooms.rate_plan_room_id', 'room_types.occupation')
            ->where('quote_category_id', $quote_category_id)
            ->where('type', 'hotel')
            ->where('object_id', $hotel_id)
            ->where('date_in', $date_in)
            ->orderBy('room_types.occupation')
            ->get();
        $hotels_collection = collect();
        $order = 1;
        foreach ($quote_services as $quote_service) {
            // solo usamos las habitaciones que no han sido ocultados.
            $total_accommodations = (int)$quote_service->single + (int)$quote_service->double + (int)$quote_service->triple + (int)$quote_service->double_child + (int)$quote_service->triple_child;
            if ($total_accommodations > 0) {
                $hotels_collection->add($quote_service);
            }
        }
        if ($hotels_collection->count() > 0) {

            $hotels_collection = $hotels_collection->groupBy(function ($service) {
                return str_replace('-', '',
                        $service->date_in) . '_' . $service->nights . '_' . $service->object_id . '_' . $service->locked;
            });

            // print_r($hotels_collection->toArray());
            // die('pax = '.$adults.'-'.$children);
            foreach ($hotels_collection as $hotel_group) {

                $this->updatePaxAcomodacion($hotel_group, $adults, $children, $order, $quoteDistributions);
            }

        }


    }


    public function setUpdatePassengerInHotelsForQuoteService(
        $quote_id,
        $client_id,
        $quote_category_id,
        $hotel_id,
        $date_in,
        $adults,
        $children
    ) {


        $quote_services = DB::table('quote_services')
            ->leftJoin('quote_service_rooms', 'quote_service_rooms.quote_service_id', '=', 'quote_services.id')
            ->leftJoin('rates_plans_rooms', 'quote_service_rooms.rate_plan_room_id', '=', 'rates_plans_rooms.id')
            ->leftJoin('rooms', 'rates_plans_rooms.room_id', '=', 'rooms.id')
            ->leftJoin('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->select('quote_services.*', 'quote_service_rooms.rate_plan_room_id', 'room_types.occupation')
            ->where('quote_category_id', $quote_category_id)
            ->where('type', 'hotel')
            ->where('object_id', $hotel_id)
            ->where('date_in', $date_in)
            ->orderBy('room_types.occupation')
            ->get();
        $hotels_collection = collect();
        $order = 1;
        foreach ($quote_services as $quote_service) {
            // solo usamos las habitaciones que no han sido ocultados.
            $total_accommodations = (int)$quote_service->single + (int)$quote_service->double + (int)$quote_service->triple + (int)$quote_service->double_child + (int)$quote_service->triple_child;
            if ($total_accommodations > 0) {
                $hotels_collection->add($quote_service);
            }
        }
        if ($hotels_collection->count() > 0) {

            $hotels_collection = $hotels_collection->groupBy(function ($service) {
                return str_replace('-', '',
                        $service->date_in) . '_' . $service->nights . '_' . $service->object_id . '_' . $service->locked;
            });


            foreach ($hotels_collection as $hotel_group) {
                foreach ($hotel_group as $quote_services) {
                    // $this->updateListPassengersRoomsHotel($quote_services->id, $quote_id);
                    $this->updateAmountServiceNewService($quote_services->id, $client_id);
                }
            }

        }


    }


    public function setAccommodationInHotelsBk($quote_categories, $adults, $children)
    {

        foreach ($quote_categories as $category) {
            $quote_services = DB::table('quote_services')
                ->where('quote_category_id', $category->id)
                ->orderBy('date_in')
                ->orderBy('order')
                ->get();
            $hotels_collection = collect();
            foreach ($quote_services as $quote_service) {
                if ($quote_service->type == 'hotel') {
                    $total_accommodations = (int)$quote_service->single + (int)$quote_service->double + (int)$quote_service->triple + (int)$quote_service->double_child + (int)$quote_service->triple_child;
                    if ($total_accommodations > 0) {
                        $hotels_collection->add($quote_service);
                    }
                } else {
                    DB::table('quote_services')->where('id', $quote_service->id)->update([
                        'adult' => $adults,
                        'child' => $children
                    ]);
                }
            }
            if ($hotels_collection->count() > 0) {
                $hotels_collection = $hotels_collection->groupBy(function ($service) {
                    return str_replace('-', '',
                            $service->date_in) . '_' . $service->nights . '_' . $service->object_id . '_' . $service->locked;
                });

                foreach ($hotels_collection as $hotel_group) {
                    $_children = $children;
                    foreach ($hotel_group as $hotel) {
                        $service_room = DB::table('quote_service_rooms')->select('rate_plan_room_id')->where('quote_service_id',
                            $hotel->id)->first();
                        if ($service_room != null) {
                            $rate_plan_room = DB::table('rates_plans_rooms')->select('room_id')->where('id',
                                $service_room->rate_plan_room_id)->first();
                            $room = DB::table('rooms')->select('room_type_id')->where('id',
                                $rate_plan_room->room_id)->first();
                            $room_type = DB::table('room_types')->select('occupation')->where('id',
                                $room->room_type_id)->first();
                            if ($room_type->occupation == 1) {
                                DB::table('quote_services')->where('id', $hotel->id)->update([
                                    'adult' => 1,
                                    'child' => 0,
                                    'single' => 1,
                                    'double' => 0,
                                    'triple' => 0,
                                    'double_child' => 0,
                                    'triple_child' => 0,
                                ]);
                            }
                            if ($room_type->occupation == 2) {

                                if ($_children > 0) {
                                    $adult = 1;
                                    $child = 1;
                                    $_children--;
                                } else {
                                    $adult = 2;
                                    $child = 0;
                                }

                                DB::table('quote_services')->where('id', $hotel->id)->update([
                                    'adult' => $adult,
                                    'child' => $child,
                                    'single' => 0,
                                    'double' => 1,
                                    'triple' => 0,
                                    'double_child' => 0,
                                    'triple_child' => 0,
                                ]);
                            }
                            if ($room_type->occupation == 3) {

                                if ($_children > 0) {
                                    $adult = 2;
                                    $child = 1;
                                    $_children--;
                                } else {
                                    $adult = 3;
                                    $child = 0;
                                }

                                DB::table('quote_services')->where('id', $hotel->id)->update([
                                    'adult' => $adult,
                                    'child' => $child,
                                    'single' => 0,
                                    'double' => 0,
                                    'triple' => 1,
                                    'double_child' => 0,
                                    'triple_child' => 0,
                                ]);
                            }
                        }
                    }

                }

            }
        }

    }

    public function hasHotelRoom($s, $occupation)
    {
        $hasRoom = false;
        foreach ($s['service_rooms'] as $r) {
            $room = RatesPlansRooms::with('room.room_type')
                ->where('id', $r['rate_plan_room_id'])
                ->first();
            if ($room and $room["room"]["room_type"]["occupation"] == $occupation) {
                $hasRoom = true;
                break;
            }
        }

        return $hasRoom;
    }

    private function calculatePriceHotelRoom($service, $passengers)
    {
        foreach ($passengers as $index_passenger => $passenger) {
            $passengers[$index_passenger]["amount"] = 0;
        }


        foreach ($service["amount"] as $index_amount => $amount) {
            $service["amount"][$index_amount]["passengers"] = $passengers;
        }

        // $max_capacity = $service['service_rooms'][0]['rate_plan_room']['room']['max_capacity'];

        $adult = $service['adult'];
        $child = $service['child'];
        $capacity = 0;
        if ($service['single'] > 0) {
            $capacity = 1;
        } elseif ($service['double'] > 0) {
            $capacity = 2;
        } else {
            $capacity = 3;
        }

        $allows_child = $service["hotel"]["allows_child"];
        $allows_teenagers = $service["hotel"]["allows_teenagers"];

        $min_age_child = $service["hotel"]["min_age_child"] ? $service["hotel"]["min_age_child"] : 0;
        $max_age_child = $service["hotel"]["max_age_child"] ? $service["hotel"]["max_age_child"] : 0;
        $min_age_teenager = $service["hotel"]["min_age_teenagers"] ? $service["hotel"]["min_age_teenagers"] : 0;
        $max_age_teenager = $service["hotel"]["max_age_teenagers"] ? $service["hotel"]["max_age_teenagers"] : 0;


        if ($child > 0) {

            $totalAdult = 0;
            foreach ($service["passengers"] as $index => $passenger) {

                if ($passenger['passenger']['type'] == "CHD") {

                    // if(!isset($passenger['passenger']['age_child']))dd($service["passengers"]);

                    $age = isset($passenger['passenger']['age_child']) ? $passenger['passenger']['age_child']['age'] : 1;

                    if ($allows_teenagers and ($age >= $min_age_teenager && $age <= $max_age_teenager)) {
                        $service["passengers"][$index]['type_pax'] = 'teenager';
                        $service["passengers"][$index]['type_pax_order'] = 3;
                        $service["passengers"][$index]['age'] = $age;
                    } elseif ($allows_child and ($age >= $min_age_child && $age <= $max_age_child)) {
                        $service["passengers"][$index]['type_pax'] = 'child';
                        $service["passengers"][$index]['type_pax_order'] = 2;
                        $service["passengers"][$index]['age'] = $age;
                    } else {
                        $service["passengers"][$index]['type_pax'] = 'adult';
                        $service["passengers"][$index]['type_pax_order'] = 1;
                        $service["passengers"][$index]['age'] = $age;
                        $totalAdult++;
                    }
                } else {
                    $service["passengers"][$index]['type_pax'] = 'adult';
                    $service["passengers"][$index]['type_pax_order'] = 1;
                    $service["passengers"][$index]['age'] = 28;
                    $totalAdult++;
                }
            }

            $type_pax_order = array_column($service["passengers"], 'type_pax_order');
            $age = array_column($service["passengers"], 'age');
            array_multisort($type_pax_order, SORT_ASC, $age, SORT_DESC, $service["passengers"]);


        } else {
            foreach ($service["passengers"] as $index => $passenger) {

                $service["passengers"][$index]['type_pax'] = 'adult';
                $service["passengers"][$index]['type_pax_order'] = 1;
                $service["passengers"][$index]['age'] = 28;

            }
        }

        $totalPax = count($service["passengers"]);
        if ($capacity <= $totalPax) {
            $totalAdult = $capacity;
        } else {
            $totalAdult = $totalPax;
        }

        $totalAdult = $totalAdult ? $totalAdult : 1;
        $errorTarifa = false;

        // if($service['id'] == 10726026 )
        //     dd($service["passengers"]);

        foreach ($service["amount"] as $index_amount => $amount) { // cantidad de dias reservados

            $precioAdulto = roundLito($amount["price_per_night"] / $totalAdult);
            $totalSeleccionado = 1;
            foreach ($service["passengers"] as $index => $passenger) {
                if ($totalSeleccionado <= $totalAdult) {
                    $service["passengers"][$index]['price'] = $precioAdulto;
                } else {
                    if ($passenger['type_pax'] == "child") {
                        $service["passengers"][$index]['price'] = roundLito($amount["price_child"]);
                    } elseif ($passenger['type_pax'] == "teenager") {
                        $service["passengers"][$index]['price'] = roundLito($amount["price_teenagers"]);
                    } else {
                        // debe de ser otro adulto y se pasa la capciadad de adultos permitos en la habitacion.
                        $service["passengers"][$index]['price'] = -1;
                        $errorTarifa = true;

                    }
                    // dd($amount);
                }
                $totalSeleccionado++;
            }

            foreach ($amount["passengers"] as $index_amount_passenger => $amount_passenger) {  // todos los pasajeros (5)

                foreach ($service["passengers"] as $passenger) {  // todos los pasajeros asignados al quote_service SGL, DBL, TPL  ejemplos (1 adulto) = 1 fila, (2 adultos) 2 filas, (1 adulto + 2 niños)3 filas

                    if ($amount_passenger["id"] == $passenger["passenger"]["id"]) {

                        $amount["passengers"][$index_amount_passenger]["amount"] = $passenger['price'];
                    }
                }

            }
            $service["amount"][$index_amount]["passengers"] = $amount["passengers"];

        }

        if ($errorTarifa) {
            $service["price"] = 'Error';
            $service["price_error"] = trans('validations.quotes.hotels.hotel_room_max_adults',
                ['max_adults' => $totalAdult]);
        } else {
            $priceRoom = 0;
            foreach ($service["amount"] as $index_amount => $amount) {
                foreach ($amount["passengers"] as $index_amount_passenger => $amount_passenger) {
                    $priceRoom = $priceRoom + $amount_passenger["amount"];
                }
            }
            $service["price"] = $priceRoom;
        }


        return $service;
    }


}
