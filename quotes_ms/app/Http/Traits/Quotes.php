<?php

namespace App\Http\Traits;

use DateTime;
use Exception;
use Carbon\Carbon;
use App\Models\Room;
use App\Models\Hotel;
use App\Models\Quote;
use App\Models\Markup;
use App\Models\Service;
use App\Models\QuoteLog;
use App\Models\QuoteNote;
use App\Models\QuoteRange;
use App\Models\RatesPlans;
use App\Models\QuotePeople;
use App\Models\ServiceRate;
use App\Models\QuoteService;
use App\Models\ServiceChild;
use App\Models\MarkupService;
use App\Models\QuoteAgeChild;
use App\Models\QuoteCategory;
use App\Models\QuotePassenger;
use App\Models\RatesPlansRooms;
use App\Models\ServiceRatePlan;
use App\Models\QuoteServiceRate;
use App\Models\QuoteServiceRoom;
use App\Models\ServiceInventory;
use App\Models\QuoteDistribution;
use App\Models\QuoteDynamicPrice;
use App\Models\QuoteAccommodation;
use App\Models\QuoteServiceAmount;
use Illuminate\Support\Facades\DB;
use App\Models\RatePlanAssociation;
use Illuminate\Support\Facades\Log;
use App\Models\QuoteDynamicSaleRate;
use App\Models\RatesPlansCalendarys;
use Illuminate\Support\Facades\Auth;
use App\Models\QuoteServicePassenger;
use App\Models\RatePlanRoomDateRange;
use App\Models\ServiceRateAssociation;
use App\Models\QuoteDistributionPassenger;
use App\Models\QuoteServiceRoomHyperguest;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Aurora\AuroraExternalApiService;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Pool;
use Psy\Readline\Hoa\Console;

trait Quotes
{
    use Services;

    private int $object_id;

    private function setObjectId($object_id): void
    {
        $this->object_id = $object_id;
    }

    private function getObjectId(): int
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
        $order_position,
        $file_id = null,
        $file_number = null,
        $file_total_amount = null,
        $is_multiregion = 0
    ): void {
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
            $order_position,
            $file_id,
            $file_number,
            $file_total_amount,
            $is_multiregion
        ) {
            $today = date('Y-m-d H:i:s');
            $quote_id = DB::table('quotes')->insertGetId([
                'name'                  => $name,
                'date_in'               => $date,
                'estimated_travel_date' => $date_estimated,
                'nights'                => $nights,
                'service_type_id'       => $service_type_id,
                'user_id'               => $user_id,
                'markup'                => $markup,
                'status'                => $status,
                'discount'              => $discount,
                'discount_detail'       => $discount_detail,
                'order_related'         => $order_related,
                'order_position'        => $order_position,
                'operation'             => $operation,
                'file_id'               => $file_id,
                'file_number'           => $file_number,
                'file_total_amount'     => $file_total_amount,
                'is_multiregion'        => $is_multiregion,
                'created_at'            => $today,
            ]);

            if ($operation == 'ranges') {
                foreach ($ranges as $range) {
                    DB::table('quote_ranges')->insert([
                        'from'       => $range['from'],
                        'to'         => $range['to'],
                        'quote_id'   => $quote_id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }

            if ($operation == 'passengers') {
                if (count($passengers) > 0) {
                    DB::table('quote_people')->insert([
                        'adults'     => $people['adults'],
                        'child'      => $people['child'],
                        'quote_id'   => $quote_id,
                        'created_at' => Carbon::now(),
                    ]);
                    foreach ($passengers as $passenger) {
                        DB::table('quote_passengers')->insert([
                            'first_name'      => $passenger['first_name'],
                            'last_name'       => $passenger['last_name'],
                            'gender'          => $passenger['gender'] ? $passenger['gender'] : null,
                            'birthday'        => $passenger['birthday'] ? $passenger['birthday'] : null,
                            'document_number' => $passenger['document_number'],
                            'doctype_iso'     => $passenger['doctype_iso'],
                            'country_iso'     => $passenger['country_iso'],
                            'email'           => $passenger['email'],
                            'phone'           => $passenger['phone'],
                            'notes'           => $passenger['notes'],
                            'type'            => $passenger['type'],
                            'quote_id'        => $quote_id,
                            'created_at'      => Carbon::now(),
                        ]);
                    }
                }
            }

            if (count($notes) > 0) {
                foreach ($notes as $note) {
                    $parent_note_id = DB::table('quote_notes')->insertGetId([
                        'comment'    => $note['comment'],
                        'status'     => $note['status'],
                        'quote_id'   => $quote_id,
                        'user_id'    => $note['user_id'],
                        'created_at' => Carbon::now(),
                    ]);

                    if (count($note['responses']) > 0) {
                        foreach ($note['responses'] as $response) {
                            DB::table('quote_notes')->insert([
                                'parent_note_id' => $parent_note_id,
                                'comment'        => $response['comment'],
                                'status'         => $response['status'],
                                'quote_id'       => $quote_id,
                                'user_id'        => $response['user_id'],
                                'created_at'     => Carbon::now(),
                            ]);
                        }
                    }
                }
            }

            foreach ($categories as $c) {
                DB::table('quote_categories')->insert([
                    'quote_id'      => $quote_id,
                    'type_class_id' => $c,
                    'created_at'    => $today,
                ]);
            }

            if ($status == 2) {
                DB::table('quote_logs')->insert([
                    'quote_id'   => $quote_id,
                    'type'       => 'editing_quote',
                    'object_id'  => $this->object_id,
                    'user_id'    => Auth::id(),
                    'created_at' => $today,
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
    ): bool {
        // Creando el original
        $this->newQuote(
            $name,
            $date,
            $date_estimated,
            0,
            $service_type_id,
            $user_id,
            $categories,
            $ranges,
            $notes,
            $passengers,
            $people,
            $operation,
            1,
            $markup,
            0,
            '',
            null,
            null
        );

        // Creando el quote_open con el log "editing_quote"
        $this->newQuote(
            $name,
            $date,
            $date_estimated,
            0,
            $service_type_id,
            $user_id,
            $categories,
            $ranges,
            $notes,
            $passengers,
            $people,
            $operation,
            2,
            $markup,
            0,
            '',
            null,
            null
        );

        return true;
    }

    /**
     * @param $quote_id_original //id de la cotizacion draft
     * @param $quote_id //Id de la cotizacion a actualizar | draft
     * @param  bool  $save_as //Variable si biene del metodo guardar como
     *
     * @throws Exception
     * @throws Exception
     */
    private function replaceQuote($quote_id_original, $quote_id, bool $save_as = false): void
    {
        try {
            $quote_original = $this->getQuote($quote_id_original, true);
            if ($quote_original) {
                $this->saveQuoteServices($quote_id, $quote_original, $save_as);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    // No funciona bien, en un punto chanca el date-in y los repite a todos revisar
    private function updateOrderAndDateServices($services, $date_pivot_new = '', $option = null): void
    {

        DB::transaction(function () use ($services) {
            $index_service = 1;
            $date_pivot = "";
            foreach ($services as $service) {
                if ($service["type"] == 'group_header') {
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
                            $this->updateDateInExtension(
                                $service["id"],
                                Carbon::createFromFormat('d/m/Y', $date_pivot)->format('Y-m-d'),
                                $index_service
                            );
                        } else {

                            if (($service["type"] == "service" || $service["type"] == "flight")) {
                                if ($service["locked"] == false) {
                                    DB::table('quote_services')->where('id', $service["id"])->update([
                                        'order'   => $index_service,
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
                                        'order'    => $index_service,
                                        'date_in'  => $date_pivot,
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

    private function updateDateInServicesInAllCategories($quote_id): void
    {
        DB::transaction(function () use ($quote_id) {
            $date_in = DB::table('quotes')->where('id', $quote_id)->first()->date_in;
            $quote_categories = DB::table('quote_categories')->where('quote_id', $quote_id)->get();
            foreach ($quote_categories as $category) {
                $services = DB::table('quote_services')->where(
                    'quote_category_id',
                    $category->id
                )->where('parent_service_id', null)->orderBy('date_in')->get();
                if ($services->count() > 0) {
                    $service_initial = DB::table('quote_services')->where('id', $services[0]->id)->first();
                    $date_initial = $service_initial->date_in;
                    for ($i = 0; $i < count($services); $i++) {
                        if ($i == 0) {
                            if ($services[$i]->extension_id != null) {
                                $this->updateDateInExtension($services[$i]->id, $date_in, $services[$i]->order);
                            } else {
                                if ($services[$i]->type == 'service' || $services[$i]->type == 'flight') {
                                    DB::table('quote_services')->where('id', $services[$i]->id)->update([
                                        'date_in'  => $date_in,
                                        'date_out' => $date_in,
                                    ]);
                                }
                                if ($services[$i]->type == 'hotel') {
                                    DB::table('quote_services')->where('id', $services[$i]->id)->update([
                                        'date_in'  => $date_in,
                                        'date_out' => Carbon::parse($date_in)->addDays($services[$i]->nights)->format('Y-m-d'),
                                    ]);
                                }
                            }
                        } else {
                            $date_out_service = DB::table('quote_services')->where(
                                'id',
                                $services[$i]->id
                            )->first()->date_in;
                            $days_difference = Carbon::parse($date_initial)->diffInDays(Carbon::parse($date_out_service));
                            $date_in_service = Carbon::parse($date_in)->addDays($days_difference)->format('Y-m-d');
                            if ($services[$i]->extension_id != null) {
                                $this->updateDateInExtension($services[$i]->id, $date_in_service, $services[$i]->order);
                            } else {
                                if ($services[$i]->type == 'service' || $services[$i]->type == 'flight') {
                                    DB::table('quote_services')->where('id', $services[$i]->id)->update([
                                        'date_in'  => $date_in_service,
                                        'date_out' => $date_in_service,
                                    ]);
                                }
                                if ($services[$i]->type == 'hotel') {
                                    DB::table('quote_services')->where('id', $services[$i]->id)->update([
                                        'date_in'  => $date_in_service,
                                        'date_out' => Carbon::parse($date_in_service)->addDays($services[$i]->nights)->format('Y-m-d'),
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        });
    }

    private function add_years($fecha, $nAnios): string
    {
        $days = $nAnios * 365;
        $nuevafecha = strtotime('+' . $days . 'day', strtotime($fecha));

        return date('Y-m-j', $nuevafecha);
    }

    private function updateNightsAndCities($quote_id): true
    {

        DB::transaction(function () use ($quote_id) {

            $quote_categories = DB::table('quote_categories')->where('quote_id', $quote_id)->get();

            foreach ($quote_categories as $category) {
                $services = DB::table('quote_services')
                    ->where('quote_category_id', $category->id);

                $total_services = $services->count();

                if ($total_services > 0) {
                    $_services = $services->orderBy('date_in')
                        ->orderBy('order')->get();

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
                                if (!in_array($object->state_id, $states)) {
                                    $states[] = $object->state_id;
                                }
                            }
                        }
                    }

                    if (count($states) > 0) {
                        DB::table('quote_destinations')->where('quote_id', $quote_id)->delete();

                        foreach ($states as $state_id) {
                            DB::table('quote_destinations')->insert([
                                'quote_id' => $quote_id,
                                'state_id' => $state_id,
                            ]);
                        }
                    }

                    break;
                }
            }
        });

        return true;
    }

    private function updateCloneQuote($quote_id, $request): bool
    {
        $update_quote = Quote::with('categories.services')->find($quote_id);
        $update_quote->file_id = null;
        $update_quote->file_number = null;
        $update_quote->file_total_amount = null;
        $update_quote->clone_file_id = $request->clone_file_id;
        $update_quote->clone_parameters = json_encode($request->clone_parameters);
        $update_quote->clone_executed = $request->clone_executed;
        $update_quote->save();

        foreach ($update_quote->categories as $category) {
            foreach ($category->services as $service) {

                $service->is_file = 0;
                $service->file_itinerary_id = null;
                $service->file_status = 0;
                $service->file_amount_sale = 0;
                $service->file_amount_cost = 0;
                $service->save();
            }
        }

        return true;
    }

    private function updateAmountService($quote_service_id, $client_id, $quote_id)
    {
        $amount_service = 0;
        DB::transaction(function () use ($quote_service_id, $client_id, $quote_id, &$amount_service) {
            $quote_service = QuoteService::find($quote_service_id);
            if ($quote_service) {
                QuoteServiceAmount::where('quote_service_id', $quote_service->id)->forceDelete();
                if ($quote_service->type == 'service' and isset($quote_service->service_rate)) {
                    $available = ServiceInventory::where(
                        'service_rate_id',
                        $quote_service->service_rate->service_rate_id
                    )
                        ->where(
                            'date',
                            '>=',
                            Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->format('Y-m-d')
                        )
                        ->where(
                            'date',
                            '<=',
                            Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->format('Y-m-d')
                        )
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

                    if (Carbon::hasFormat($quote_service->date_in, 'd/m/Y')) {
                        $year = Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->year;
                        $markup_rate_client = MarkupService::where('client_id', $client_id)
                            ->where('service_id', $quote_service->object_id)
                            ->where('period', $year)
                            ->whereNull('deleted_at')
                            ->first();
                        if ($markup_rate_client == null) {

                            if ($quote_service->markup_regionalization > 0) {
                                $markup = $quote_service->markup_regionalization;
                            } else {
                                $markup = Quote::where('id', $quote_id)->first()->markup;
                                if (!$markup) {
                                    $markup = $this->getMarkupService($client_id, $quote_service->object_id, $quote_service->date_in_format);
                                }
                            }
                        } else {
                            $markup = $markup_rate_client->markup;
                        }
                    } else {
                        $markup = Quote::where('id', $quote_id)->first()->markup;
                        if (!$markup) {
                            $markup = $this->getMarkupService($client_id, $quote_service->object_id, $quote_service->date_in_format);
                        }
                    }

                    $pax_amount = ServiceRatePlan::with('service_rate.service')->where(
                        'service_rate_id',
                        $quote_service->service_rate->service_rate_id
                    )
                        ->where(
                            'date_from',
                            '<=',
                            Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->format('Y-m-d')
                        )
                        ->where(
                            'date_to',
                            '>=',
                            Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->format('Y-m-d')
                        )
                        ->where('pax_from', '<=', (int) $quote_service->adult + (int) $quote_service->child)
                        ->where('pax_to', '>=', (int) $quote_service->adult + (int) $quote_service->child)
                        ->first();

                    if ($pax_amount != null) {

                        if (
                            isset($pax_amount->service_rate) &&
                            isset($pax_amount->service_rate->service) &&
                            isset($pax_amount->service_rate->service->affected_markup)
                        ) {

                            $affected_markup = $pax_amount->service_rate->service->affected_markup;

                            if ($affected_markup != "1") {
                                $markup = 0;
                            }
                        } else {
                            $markup = 0;
                        }

                        //Update Quote Price Dynamic
                        $price_adult_without_markup = $pax_amount->price_adult;
                        $price_adult = $pax_amount->price_adult + ($pax_amount->price_adult * ($markup / 100));

                        if ($quote_service->type == "service") {
                            $flag_dynamic = 0;
                            $flag_exclude_client = 0;
                            $service_rates = ServiceRate::where('service_id', $quote_service->object_id)->where('status', 1)->get();
                            foreach ($service_rates as $service_rate) {
                                if ($service_rate->price_dynamic == 1) {
                                    $flag_dynamic = 1;

                                    foreach ($service_rates as $service_rates_client) {
                                        $service_rate_association = ServiceRateAssociation::where('service_rate_id', $service_rates_client->id)
                                            ->where('object_id', $client_id)
                                            ->where('entity', 'Client')
                                            ->where('except', 0)
                                            ->first();
                                        if ($service_rate_association) {
                                            $flag_exclude_client = 1;
                                        }
                                    }
                                }
                            }

                            if ($flag_dynamic == 1 && $flag_exclude_client == 0) {
                                $quote_dynamic_price = QuoteDynamicPrice::where('object_id', $quote_service->object_id)->where('quote_id', $quote_id)->where('client_id', $client_id)->first();
                                if ($quote_dynamic_price) {
                                    $price_adult_without_markup = $quote_dynamic_price->price_adl;
                                    $price_adult = $quote_dynamic_price->price_adl + ($quote_dynamic_price->price_adl * ($quote_dynamic_price->markup / 100));
                                } else {
                                    $price_adult_without_markup = 0;
                                    $price_adult = 0;
                                }
                            }
                        }

                        $price_child = $pax_amount->price_child;
                        QuoteServiceAmount::insert([
                            'quote_service_id'               => $quote_service->id,
                            'date_service'                   => $quote_service->date_in,
                            'price_per_night'                => 0,
                            'price_per_night_without_markup' => 0,
                            'price_teenagers_without_markup' => 0,
                            'price_teenagers'                => 0,
                            'price_adult_without_markup'     => $price_adult_without_markup,
                            'price_adult'                    => $price_adult,
                            'price_child_without_markup'     => $price_child,
                            'price_child'                    => $price_child + ($price_child * ($markup / 100)),
                            'created_at'                     => Carbon::now(),
                            'updated_at'                     => Carbon::now(),
                        ]);

                        $amount_service = $price_adult + $price_child + ($price_child * ($markup / 100));
                    } else {
                        QuoteServiceAmount::insert([
                            'quote_service_id'               => $quote_service->id,
                            'date_service'                   => $quote_service->date_in,
                            'price_per_night'                => 0,
                            'price_per_night_without_markup' => 0,
                            'price_teenagers_without_markup' => 0,
                            'price_teenagers'                => 0,
                            'price_adult_without_markup'     => 0,
                            'price_adult'                    => 0,
                            'price_child_without_markup'     => 0,
                            'price_child'                    => 0,
                            'created_at'                     => Carbon::now(),
                            'updated_at'                     => Carbon::now(),
                        ]);
                    }
                }
            }
        });

        return $amount_service;
    }

    private function updateAmountAllServices($quote_id, $client_id, $get_all_rates_services = false)
    {
        $response = [];
        $estimated_price = 0;
        $quote = $this->getQuote($quote_id, true, $get_all_rates_services);
        $markup_quote = $quote->markup ? $quote->markup : 0;
        // $hote_hyperguest_pull = $this->pre_search_hyperguest_pull($quote, $client_id, $markup_quote);
        if ($quote) {
            if ($quote->operation == 'passengers') {
                foreach ($quote->categories as $category) {
                    foreach ($category->services as $quote_service) {
                        if ($quote_service->type == 'service') {
                            DB::table('quote_service_amounts')->where('quote_service_id', $quote_service->id)->delete();
                            $estimated_price += $this->updateAmountService($quote_service->id, $client_id, $quote_id);
                        }
                        if ($quote_service->type == 'hotel') {
                            $quote_service_passengers_ids = QuoteServicePassenger::where(
                                'quote_service_id',
                                $quote_service->id
                            )->pluck('quote_passenger_id');

                            $quote_service_passengers_quantity = QuotePassenger::where(
                                'quote_id',
                                $quote_id
                            )->whereIn('type', ['ADL', 'CHD'])->whereIn(
                                'id',
                                $quote_service_passengers_ids
                            )->get()->count();
                            $quote_service_passengers_quantity = $quote_service_passengers_quantity == 0 ? 1 : $quote_service_passengers_quantity;

                            if (!$quote_service->hyperguest_pull) {
                                DB::table('quote_service_amounts')->where('quote_service_id', $quote_service->id)->delete();

                                $service_rate_plan_room = QuoteServiceRoom::where(
                                    'quote_service_id',
                                    $quote_service->id
                                )->first();

                                if ($service_rate_plan_room != null and $service_rate_plan_room != '' and $quote_service_passengers_quantity > 0) {
                                    $rate_plan_room = RatesPlansRooms::with('room.room_type')->where(
                                        'id',
                                        $service_rate_plan_room->rate_plan_room_id
                                    )->first();

                                    if ($quote_service->markup_regionalization > 0) {
                                        $markup = $quote_service->markup_regionalization;
                                    } else {
                                        if (!$markup_quote) {
                                            $markup = $this->getMarkupHotel($client_id, $quote_service->object_id, $quote_service->date_in_format);
                                        } else {
                                            $markup = $markup_quote;
                                        }
                                    }

                                    if ($rate_plan_room != null and $rate_plan_room != '') {
                                        $rate_plan_calendars = RatesPlansCalendarys::where(
                                            'rates_plans_room_id',
                                            $rate_plan_room->id
                                        )
                                            ->with('rate')
                                            ->where(
                                                'date',
                                                '>=',
                                                Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->format('Y-m-d')
                                            )
                                            ->where('date', '<=', Carbon::createFromFormat(
                                                'd/m/Y',
                                                $quote_service->date_out
                                            )->subDays(1)->format('Y-m-d'))
                                            ->get()->toArray();
                                        if (count($rate_plan_calendars) == $quote_service->nights and $quote_service_passengers_quantity > 0) {
                                            foreach ($rate_plan_calendars as $rate_plan_calendar) {

                                                if ($rate_plan_room->channel_id == 6) {
                                                    $rate_channel_selected = '';
                                                    //buscamos si encontramos tarifas por ocupacion
                                                    foreach ($rate_plan_calendar['rate'] as $rates) {
                                                        if ($rates['num_adult'] == $rate_plan_room->room->room_type->occupation) {
                                                            $rate_channel_selected = $rates;
                                                            break;
                                                        }
                                                    }

                                                    if (!$rate_channel_selected) {
                                                        //buscamos si encontramos tarifas por habitacion
                                                        if (count($rate_plan_calendar['rate']) > 0) {
                                                            $rate_channel_selected = $rate_plan_calendar['rate'][0];
                                                            // Fix: Only overwrite if price_total is positive
                                                            if ($rate_channel_selected['price_total'] > 0) {
                                                                $rate_channel_selected['price_adult'] = $rate_channel_selected['price_total'];
                                                            }
                                                        }
                                                    }

                                                    // Obtenemos la fecha de inicio y fin de la tarifa para niños en el canal de Hyperguest
                                                    $date_in = Carbon::createFromFormat('d/m/Y', $quote_service->date_in)->format('Y-m-d');
                                                    $date_out = Carbon::createFromFormat('d/m/Y', $quote_service->date_out)->format('Y-m-d');

                                                    // Obtenemos el precio por dia
                                                    $rate_plan_room_date_range = RatePlanRoomDateRange::where(function ($query) use ($date_in, $date_out) {
                                                        $query->where('date_from', '<=', $date_out)->where('date_to', '>=', $date_in);
                                                    })->where('rate_plan_room_id', $rate_plan_room->id)->first();
                                                    // Log::info('rate_plan_room_date_range: '. json_encode($rate_plan_room_date_range));

                                                    if ($rate_channel_selected) {
                                                        $price_per_night_without_markup = $rate_channel_selected['price_adult'];
                                                        $price_per_night_with_markup = ($price_per_night_without_markup * ($markup / 100)) + $price_per_night_without_markup;
                                                        $price_child_without_markup = $rate_plan_room_date_range->price_child ?? $rate_plan_room->channel_child_price; // EL precio del niño sin marca
                                                        $price_teenagers_without_markup = $rate_plan_room_date_range->price_infant ?? $rate_plan_room->channel_infant_price; // El precio del infante sin marca
                                                    } else {
                                                        $price_per_night_without_markup = 0;
                                                        $price_per_night_with_markup = 0;
                                                        $price_child_without_markup = $rate_plan_room_date_range->price_child ?? 0; // $rate_plan_room->channel_child_price;
                                                        $price_teenagers_without_markup = $rate_plan_room_date_range->price_infant ?? 0; // $rate_plan_room->channel_infant_price;
                                                    }
                                                } else {
                                                    $price_per_night_without_markup = ($rate_plan_calendar['rate'][0]['price_adult'] + $rate_plan_calendar['rate'][0]['price_extra']);
                                                    $price_per_night_with_markup = ($price_per_night_without_markup * ($markup / 100)) + $price_per_night_without_markup;
                                                    $price_child_without_markup = $rate_plan_calendar['rate'][0]['price_child'];
                                                    $price_teenagers_without_markup = $rate_plan_calendar['rate'][0]['price_infant'];
                                                }

                                                if ($quote_service->type == "hotel") {
                                                    $flag_dynamic = 0;
                                                    $flag_exclude_client = 0;
                                                    $service_rates = RatesPlans::where('hotel_id', $quote_service->object_id)->where('status', 1)->get();
                                                    foreach ($service_rates as $service_rate) {
                                                        if ($service_rate->price_dynamic == 1) {
                                                            $flag_dynamic = 1;

                                                            foreach ($service_rates as $service_rates_client) {
                                                                $service_rate_association = RatePlanAssociation::where('rate_plan_id', $service_rates_client->id)
                                                                    ->where('object_id', $client_id)
                                                                    ->where('entity', 'Client')
                                                                    ->where('except', 0)
                                                                    ->first();
                                                                if ($service_rate_association) {
                                                                    $flag_exclude_client = 1;
                                                                }
                                                            }
                                                        }
                                                    }

                                                    if ($flag_dynamic == 1 && $flag_exclude_client == 0) {
                                                        $quote_dynamic_price = QuoteDynamicPrice::where('object_id', $quote_service->object_id)
                                                            ->where('quote_service_id', $quote_service->id)->where('quote_id', $quote_id)->where('client_id', $client_id)->first();

                                                        if ($quote_dynamic_price) {
                                                            $price_per_night_without_markup = intval($quote_dynamic_price->price_adl * $quote_service_passengers_quantity);
                                                            $price_per_night_with_markup = ($price_per_night_without_markup * ($quote_dynamic_price->markup / 100)) + $price_per_night_without_markup;
                                                        } else {
                                                            $price_per_night_without_markup = 0;
                                                            $price_per_night_with_markup = 0;
                                                        }
                                                    }
                                                }

                                                QuoteServiceAmount::insert([
                                                    'quote_service_id'               => $quote_service->id,
                                                    'date_service'                   => Carbon::parse($rate_plan_calendar['date'])->format('d/m/Y'),
                                                    'price_per_night_without_markup' => $price_per_night_without_markup,
                                                    'price_per_night'                => $price_per_night_with_markup,
                                                    'price_adult_without_markup'     => $price_per_night_without_markup / $quote_service_passengers_quantity,
                                                    'price_adult'                    => $price_per_night_with_markup / $quote_service_passengers_quantity,
                                                    'price_child_without_markup'     => $price_child_without_markup,
                                                    'price_child'                    => $price_child_without_markup + ($price_child_without_markup * ($markup / 100)),
                                                    'price_teenagers_without_markup' => $price_teenagers_without_markup,
                                                    'price_teenagers'                => $price_teenagers_without_markup + ($price_teenagers_without_markup * ($markup / 100)),
                                                    'created_at'                     => Carbon::now(),
                                                    'updated_at'                     => Carbon::now(),
                                                ]);

                                                $estimated_price += $price_per_night_with_markup / $quote_service_passengers_quantity;
                                            }
                                        }
                                    }
                                }
                            } else {
                                // Hypergues PULL -- Se hace en otro proceso aparte
                            }
                        }
                    }
                }
            }

            if ($quote->operation == 'ranges') {
                $ranges = $quote->ranges;
                foreach ($quote->categories as $category) {
                    DB::table('quote_dynamic_sale_rates')->where('quote_category_id', $category->id)->delete();

                    $hotels_groups = [];
                    foreach ($category->services as $service) {

                        if ($service->type == 'hotel') {
                            $ocuppations = $service->single + $service->double + $service->triple;
                            if ($ocuppations == 0) {
                                continue;
                            }
                            $hotel_amounts = $this->getAmountServiceHotel($service, $client_id, $quote_id, false);

                            $hotels_groups[$service->quote_category_id . '_' . $service->type . '_' . $service->object_id . '_' . $service->date_in]['quote_service_id'] = $service->id;
                            $hotels_groups[$service->quote_category_id . '_' . $service->type . '_' . $service->object_id . '_' . $service->date_in]['nights'] = $service->nights;
                            $hotels_groups[$service->quote_category_id . '_' . $service->type . '_' . $service->object_id . '_' . $service->date_in]['date_in'] = $service->date_in;

                            $room_key = $service->single == '1' ? 'single' : ($service->double == '1' ? 'double' : 'triple');
                            $hotels_groups[$service->quote_category_id . '_' . $service->type . '_' . $service->object_id . '_' . $service->date_in]['room_types_grouped'][$room_key] = $hotel_amounts['room_type'];

                            $hotels_groups[$service->quote_category_id . '_' . $service->type . '_' . $service->object_id . '_' . $service->date_in]['rate_meals'][$hotel_amounts['rate_meal']] = $hotel_amounts['rate_meal'];
                            if ($service->single == '1') {
                                $hotels_groups[$service->quote_category_id . '_' . $service->type . '_' . $service->object_id . '_' . $service->date_in]['single'] = $hotel_amounts['simple'];
                            } elseif ($service->double == '1') {
                                $hotels_groups[$service->quote_category_id . '_' . $service->type . '_' . $service->object_id . '_' . $service->date_in]['double'] = $hotel_amounts['double'];
                            } elseif ($service->triple == '1') {
                                $hotels_groups[$service->quote_category_id . '_' . $service->type . '_' . $service->object_id . '_' . $service->date_in]['triple'] = $hotel_amounts['triple'];
                            }
                        }

                        foreach ($ranges as $range) {
                            if ($service->type == 'hotel') {
                            }
                            if ($service->type == 'service') {

                                $service_amount = $this->getAmountServiceService(
                                    $service->id,
                                    $client_id,
                                    $range->from,
                                    $range->to,
                                    $quote_id,
                                    false
                                );

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
                                    'date_service'      => convertDate($service->date_in, '/', '-', 1),
                                    'quote_service_id'  => $service->id,
                                    'pax_from'          => $range->from,
                                    'pax_to'            => $range->to,
                                    'simple'            => $simple,
                                    'double'            => $double,
                                    'triple'            => $triple,
                                    'created_at'        => Carbon::now(),
                                    'quote_category_id' => $category->id,
                                ]);

                                if ($service->single > 0) {
                                    $estimated_price += $simple;
                                }
                                if ($service->double > 0) {
                                    $estimated_price += $double;
                                }
                                if ($service->triple > 0) {
                                    $estimated_price += $triple;
                                }
                            }
                        }
                    }

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
                                    'date_service'      => $date_service,
                                    'quote_service_id'  => $hotel['quote_service_id'],
                                    'pax_from'          => $range->from,
                                    'pax_to'            => $range->to,
                                    'simple'            => $simple,
                                    'double'            => $double,
                                    'triple'            => $triple,
                                    'created_at'        => Carbon::now(),
                                    'quote_category_id' => $category->id,
                                    'room_types'        => $hotel['room_types_grouped']['double'] ?? $hotel['room_types_grouped']['single'] ?? $hotel['room_types_grouped']['triple'] ?? '',
                                    'rate_meals'        => implode(',', $hotel['rate_meals']),
                                ]);

                                if ($service->single > 0) {
                                    $estimated_price += $simple;
                                }
                                if ($service->double > 0) {
                                    $estimated_price += $double;
                                }
                                if ($service->triple > 0) {
                                    $estimated_price += $triple;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $estimated_price;
    }

    public function getMarkupHotel($client_id, $hotel_id, $date_in, $float = true)
    {
        // Handle d/m/Y format (e.g., 25/12/2025)
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date_in)) {
            $from = Carbon::createFromFormat('d/m/Y', $date_in);
        } else {
            $from = Carbon::parse($date_in);
        }
        $period = $from->year;
        $hotel = Hotel::find($hotel_id);
        $country_id = $hotel->country_id;

        $client_markup = $this->getMarkupRegional($client_id, $country_id, $period);

        return $client_markup ? $this->formatMarkup($client_markup->hotel, $float) : NULL;
    }

    public function getMarkupService($client_id, $service_id, $date_in, $float = true)
    {
        // Handle d/m/Y format (e.g., 25/12/2025)
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date_in)) {
            $from = Carbon::createFromFormat('d/m/Y', $date_in);
        } else {
            $from = Carbon::parse($date_in);
        }
        $period = $from->year;
        $service = Service::with('serviceOrigin')->find($service_id);

        $country_id = NULL;
        if (isset($service->serviceOrigin) && count($service->serviceOrigin) > 0) {
            $country_id = $service->serviceOrigin[0]->country_id;
        }

        if ($country_id === NULL) {
            throw new Exception("El servicio {$service->aurora_code} no tiene asociado un pais");
        }

        $client_markup = $this->getMarkupRegional($client_id, $country_id, $period);

        if (!$client_markup) {
            throw new Exception("El servicio {$service->aurora_code} no tiene asociado un markup regional de cliente para el año {$period}");
        }

        return $this->formatMarkup($client_markup->service, $float);
    }

    function formatMarkup($markup, $float = false)
    {
        // Quitar espacios y convertir a número
        $number = floatval(trim($markup));

        // Devolver como entero
        return $float ? $number : intval($number);
    }

    public function getMarkupRegional($client_id, $country_id, $period)
    {
        $client_markup = Markup::whereHas('businessRegion.countries', function ($query) use ($country_id) {
            $query->where('countries.id', $country_id);
        })->where(['client_id' => $client_id, 'period' => $period])->first();

        return $client_markup;
    }

    public function getMarkupDefaul($client_id)
    {
        $client_markup = Markup::where('client_id', $client_id)
            ->where('period', date('Y'))
            ->orderBy('business_region_id')
            ->first();

        $markup = 0;
        if ($client_markup) {
            $markup = $client_markup->hotel > 0 ? $client_markup->hotel : $client_markup->service;
        }


        return $markup;
    }




    public function getAmountServiceHotel($service, $client_id, $quote_id, $apply_round = true): array
    {

        //Todo Obtengo el hotel de la cotizacion
        // $service = DB::table('quote_services')->where('id', $service_id)->first();
        $markup = Quote::where('id', $quote_id)->first()->markup;
        $prices = [
            'simple'    => 0,
            'double'    => 0,
            'triple'    => 0,
            'room_type' => '',
            'rate_meal' => '',
        ];

        if ($service) {

            if (!$service->hyperguest_pull) {

                //Todo Obtengo las tarifas de la habitacion en la cotizacion
                $service_rate_plan_rooms_ids = DB::table('quote_service_rooms')->where(
                    'quote_service_id',
                    $service->id
                )->pluck('rate_plan_room_id');

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
                        ->where('date', '<=', Carbon::parse($service->date_out_format)->subDays()->format('Y-m-d'))
                        ->get();

                    foreach ($inventories as $inventory) {
                        $real_inventory = $inventory->inventory_num - $inventory->total_booking;

                        if ($inventory->locked == 1 || $real_inventory <= 0) {
                            $on_request = 1;
                            DB::table('quote_services')->where('id', $service->id)->update([
                                'on_request' => 1,
                            ]);
                        }
                    }
                    if ($inventories->count() < $service->nights) {
                        $on_request = 1;
                        DB::table('quote_services')->where('id', $service->id)->update([
                            'on_request' => $on_request,
                        ]);
                    }
                    if ($on_request == 0) {
                        DB::table('quote_services')->where('id', $service->id)->update([
                            'on_request' => $on_request,
                        ]);
                    }

                    if (!$markup) {
                        $rate_plan_rooms[$index]->markup = $this->getMarkupHotel($client_id, $service->object_id, $service->date_in_format);
                    } else {
                        $rate_plan_rooms[$index]->markup = $markup;
                    }
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
                            ->where('date', '>=', Carbon::createFromFormat('d/m/Y', $service->date_in)->format('Y-m-d'))
                            ->where('date', '<=', Carbon::parse($service->date_out_format)->subDays()->format('Y-m-d'))
                            ->get();

                        foreach ($rate_plan_room_calendars as $calendar) {

                            if ($rate_plan_room->channel_id == '6') {
                                $rates = DB::table('rates')->whereNull('deleted_at')->where(
                                    'rates_plans_calendarys_id',
                                    $calendar->id
                                )->get();
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

                                if (isset($rate_channel_selected->price_adult)) {
                                    $simple += $rate_channel_selected->price_adult;
                                }
                                // solo nos  importa la primera noche
                            } else {
                                $rate = DB::table('rates')->whereNull('deleted_at')->where(
                                    'rates_plans_calendarys_id',
                                    $calendar->id
                                )->first();
                                $simple += $rate->price_adult;
                                // solo nos  importa la primera noche

                            }
                            break;
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
                            ->where('date', '>=', Carbon::createFromFormat('d/m/Y', $service->date_in)->format('Y-m-d'))
                            ->where('date', '<=', Carbon::parse($service->date_out_format)->subDays()->format('Y-m-d'))
                            ->get();

                        foreach ($rate_plan_room_calendars as $calendar) {
                            if ($rate_plan_room->channel_id == '6') {

                                $rates = DB::table('rates')->whereNull('deleted_at')->where(
                                    'rates_plans_calendarys_id',
                                    $calendar->id
                                )->get();
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

                                if (isset($rate_channel_selected->price_adult)) {
                                    $double += $rate_channel_selected->price_adult;
                                }
                                // solo nos  importa la primera noche

                            } else {
                                $rate = DB::table('rates')
                                    ->whereNull('deleted_at')
                                    ->where('rates_plans_calendarys_id', $calendar->id)->first();
                                $double += $rate->price_adult;
                                // solo nos  importa la primera noche
                            }
                            break;
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
                            ->where('date', '>=', Carbon::createFromFormat('d/m/Y', $service->date_in)->format('Y-m-d'))
                            ->where('date', '<=', Carbon::parse($service->date_out_format)->subDays()->format('Y-m-d'))
                            ->get();

                        foreach ($rate_plan_room_calendars as $calendar) {
                            if ($rate_plan_room->channel_id == '6') {

                                $rates = DB::table('rates')->whereNull('deleted_at')->where(
                                    'rates_plans_calendarys_id',
                                    $calendar->id
                                )->get();
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

                                if (isset($rate_channel_selected->price_adult)) {
                                    $triple += $rate_channel_selected->price_adult;
                                }
                                // solo nos  importa la primera noche

                            } else {
                                $rate = DB::table('rates')
                                    ->whereNull('deleted_at')
                                    ->where('rates_plans_calendarys_id', $calendar->id)->first();

                                $triple += $rate->price_adult + $rate->price_extra;
                                // solo nos  importa la primera noche
                            }
                            break;
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
                                            },
                                        ]);
                                    },
                                ]);
                            },
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
                                    },
                                ]);
                            },
                        ]);
                    },
                    'translations' => function ($query) {
                        $query->select(['object_id', 'value']);
                        $query->where('type', 'room');
                        $query->where('slug', 'room_description');
                        $query->where('language_id', 2);
                    },
                ])->find($service_rate_plan_rooms_ids)->first();
            } else {

                $simple = 0;
                $double = 0;
                $triple = 0;

                $rate_plan_room = QuoteServiceRoomHyperguest::with('room.room_type')->with('rate_plan.meal')->where(
                    'quote_service_id',
                    $service->id
                )->first();

                $hotelHyperguest = $this->search_hyperguest_pull($service->toArray(), $client_id, $markup);  // los precios de hyperguest ya traen importes con markups y el precio base

                foreach ($hotelHyperguest->rooms as $room) {
                    $occupation = $room->occupation;
                    if ($room->id == $rate_plan_room->room_id) {
                        foreach ($room->rates as $rate) {
                            if ($rate->ratePlanId == $rate_plan_room->rate_plan_id) {
                                $rateSelected = $rate->rate[0];
                                $amount_days = $rateSelected->amount_days;

                                $price = $amount_days[0]->total_amount; // ya viene con el markup ya sea por cliente o por el personalizado que viene de la cotizacion

                                if ($occupation == 1) {
                                    $simple = $simple + $price;
                                }

                                if ($occupation == 2) {
                                    $double = $double + $price;
                                }

                                if ($occupation == 3) {
                                    $triple = $triple + $price;
                                }

                                $service->on_request = 0;
                                $service->save();
                            }
                        }
                    }
                }
            }

            $room_type = $rate_plan_room->room?->room_type?->type_room?->translations[0]?->value ?? '';
            $rate_meal = $rate_plan_room->rate_plan?->meal?->translations[0]?->value ?? '';

            $prices = [
                'simple'    => $apply_round ? roundLito($simple) : $simple,
                'double'    => $apply_round ? roundLito($double) : $double,
                'triple'    => $apply_round ? roundLito($triple) : $triple,
                'room_type' => $room_type,
                'rate_meal' => $rate_meal
            ];
        }

        return $prices;
    }

    public function getAmountServiceService($service_id, $client_id, $from, $to, $quote_id, $apply_round = true)
    {
        $service = DB::table('quote_services')->where('id', $service_id)->first();

        $service_rate_id = DB::table('quote_service_rates')->where(
            'quote_service_id',
            $service_id
        )->first()->service_rate_id;

        $markup = DB::table('quotes')->where('id', $quote_id)->first()->markup;

        if (!$markup) {
            $markup = $this->getMarkupService($client_id, $service->object_id, Carbon::parse($service->date_in)->format('d/m/Y'));
        }

        $pax_amounts = ServiceRatePlan::with('service_rate.service')
            ->where('service_rate_id', $service_rate_id)
            ->where('date_from', '<=', $service->date_in)
            ->where('date_to', '>=', $service->date_in)
            ->where('pax_from', '>=', (int)$from)
            ->where('pax_to', '<=', (int)$to)
            ->get();

        $total_amount = 0;

        foreach ($pax_amounts as $pax_amount) {

            $affected_markup = $pax_amount->service_rate->service->affected_markup;
            if ($affected_markup != "1") {
                $markup = 0;
            }

            $total_amount += ($pax_amount->price_adult) + ($pax_amount->price_adult * ($markup / 100));
        }

        if ($pax_amounts->count() > 0) {

            $total_amount = $total_amount / $pax_amounts->count();
        } else {
            $pax_amounts = ServiceRatePlan::with('service_rate.service')
                ->where('service_rate_id', $service_rate_id)
                ->where('date_from', '<=', $service->date_in)
                ->where('date_to', '>=', $service->date_in)
                ->get();

            if ($pax_amounts->count() > 0) {
                foreach ($pax_amounts as $index_pax_amount => $pax_amount) {
                    if ($pax_amount->pax_from <= $from && $pax_amount->pax_to >= $to) {

                        $affected_markup = $pax_amount->service_rate->service->affected_markup;
                        if ($affected_markup != "1") {
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
    ): int {
        // Obtener el markup de la cotización desde la categoría
        $quote_markup = DB::table('quote_categories')
            ->join('quotes', 'quote_categories.quote_id', '=', 'quotes.id')
            ->where('quote_categories.id', $category_id)
            ->value('quotes.markup') ?? 0;

        $service_id = DB::table('quote_services')->insertGetId([
            'quote_category_id' => $category_id,
            'type'              => $service->type,
            'object_id'         => $service->object_id,
            'date_in'           => $date_in_service,
            'date_out'          => $date_in_service,
            'adult'             => $adults,
            'child'             => $child,
            'infant'            => 0,
            'single'            => 0,
            'double'            => 0,
            'double_child'      => 0,
            'triple'            => 0,
            'triple_child'      => 0,
            'triple_active'     => 0,
            'locked'            => 0,
            'new_extension_id'  => $extension_id,
            'order'             => 0,
            'nights'            => 0,
            'markup_regionalization' => $quote_markup
        ]);
        $service_rate_id = DB::table('package_service_rates')
            ->where('package_service_id', $service->id)
            ->whereNull('deleted_at')
            ->first()->service_rate_id;

        DB::table('quote_service_rates')->insert([
            'service_rate_id'  => $service_rate_id,
            'quote_service_id' => $service_id,
        ]);

        return $service_id;
    }

    /**
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
    ): int {
        // Obtener el markup de la cotización desde la categoría
        $quote_markup = DB::table('quote_categories')
            ->join('quotes', 'quote_categories.quote_id', '=', 'quotes.id')
            ->where('quote_categories.id', $category_id)
            ->value('quotes.markup') ?? 0;

        $service_id = DB::table('quote_services')->insertGetId([
            'quote_category_id' => $category_id,
            'type'              => $service->type,
            'object_id'         => $service->object_id,
            'date_in'           => $date_in,
            'date_out'          => $date_out,
            'nights'            => Carbon::parse($date_in)->diffInDays(Carbon::parse($date_out)),
            'single'            => 0,
            'double'            => 1,
            'triple'            => 0,
            'adult'             => $adults,
            'child'             => $child,
            'infant'            => 0,
            'double_child'      => 0,
            'triple_child'      => 0,
            'triple_active'     => 0,
            'locked'            => 0,
            'new_extension_id'  => $extension_id,
            'order'             => 0,
            'markup_regionalization' => $quote_markup
        ]);
        $rates = DB::table('package_service_rooms')
            ->whereNull('deleted_at')
            ->where('package_service_id', $service->id)
            ->get();

        foreach ($rates as $rate) {
            DB::table('quote_service_rooms')->insert([
                'rate_plan_room_id' => $rate->rate_plan_room_id,
                'quote_service_id'  => $service_id,
            ]);
        }

        return $service_id;
    }

    public function updateDateInExtension($service_id, $date_in, $order): void
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
                    'order'    => $order,
                    'date_in'  => Carbon::parse($date_in)->addDays($days_difference_service_extension)->format('Y-m-d'),
                    'date_out' => Carbon::parse($date_in)->addDays($days_difference_service_extension)->format('Y-m-d'),
                ]);
            }
            if ($service->type == 'hotel') {
                DB::table('quote_services')->where('id', $service->id)->update([
                    'order'    => $order,
                    'date_in'  => Carbon::parse($date_in)->addDays($days_difference_service_extension)->format('Y-m-d'),
                    'date_out' => Carbon::parse(Carbon::parse($date_in)->addDays($days_difference_service_extension)->format('Y-m-d'))->addDays($service->nights)->format('Y-m-d'),
                ]);
            }
        }
        if ($service->type == 'service' or $service->type == 'flight') {
            DB::table('quote_services')->where('id', $service->id)->update([
                'order'    => $order,
                'date_in'  => $date_in,
                'date_out' => $date_in,
            ]);
        }
        if ($service->type == 'hotel') {
            DB::table('quote_services')->where('id', $service->id)->update([
                'order'    => $order,
                'date_in'  => $date_in,
                'date_out' => Carbon::parse($date_in)->addDays($service->nights)->format('Y-m-d'),
            ]);
        }
    }

    public function exportTableAmounts($quote_id, $category_id, $get_all_rates_services = false, $language_id = 1)
    {
        $quote = Quote::where('id', $quote_id)->first();
        $categories = [
            'passengers' => [],
        ];
        if ($quote->operation == 'passengers') {

            $data = [
                'quote_name'          => '',
                'client_code'         => '',
                'client_name'         => '',
                'lang'                => '',
                'categories'          => [],
                'categories_optional' => [],
            ];

            $multiplePassengers = false;
            $occupation_name = '';
            $category = QuoteCategory::where('id', $category_id)->where('quote_id', $quote_id)
                ->with([
                    'type_class' => function ($query) use ($language_id) {
                        $query->select('id');
                        $query->with([
                            'translations' => function ($query) use ($language_id) {
                                $query->select(['object_id', 'value']);
                                $query->where('type', 'typeclass');
                                $query->where('language_id', $language_id);
                            },
                        ]);
                    },
                ])->first();
            $data['passengers'] = QuotePassenger::where('quote_id', $quote_id)->get()->toArray();
            $data['passengers_optional'] = QuotePassenger::where('quote_id', $quote_id)->get()->toArray();

            $data['categories'][] = [
                'category' => $category['type_class']['translations'][0]['value'],
                'services' => [],
            ];
            $data['categories_optional'][] = [
                'category'          => $category['type_class']['translations'][0]['value'],
                'services_optional' => [],
            ];
            $quote_services = QuoteService::where('quote_category_id', $category['id'])->where('optional', 0);
            if (! $get_all_rates_services) {
                $quote_services = $quote_services->where('locked', 0);
            }
            $quote_services = $quote_services->with('service')
                ->with('hotel.channel')
                ->orderBy('date_in')
                ->get();

            $quote_services_optional = QuoteService::where('quote_category_id', $category['id'])->where('optional', 1);
            if (! $get_all_rates_services) {
                $quote_services_optional = $quote_services_optional->where('locked', 0);
            }
            $quote_services_optional = $quote_services_optional->with('service')
                ->with('hotel.channel')
                ->orderBy('date_in')
                ->get();
            $quote_people_initial = QuotePeople::where('quote_id', $quote_id)->first();
            $quote_ages_child = QuoteAgeChild::where('quote_id', $quote_id)->get()->toArray();

            $data['all_passengers'] = $data['passengers'];
            $data['all_passengers_optional'] = $data['passengers_optional'];
            $data['quote_ages_child'] = $quote_ages_child;

            foreach ($data['passengers'] as $index_passenger => $passenger) {
                $data['passengers'][$index_passenger]['total'] = 0;
                $data['passengers'][$index_passenger]['total_adult'] = 0;
                $data['passengers'][$index_passenger]['total_child'] = 0;
            }
            foreach ($data['passengers_optional'] as $index_passenger => $passenger) {
                $data['passengers_optional'][$index_passenger]['total'] = 0;
                $data['passengers_optional'][$index_passenger]['total_adult'] = 0;
                $data['passengers_optional'][$index_passenger]['total_child'] = 0;
            }
            foreach ($quote_services as $quote_service) {
                $quote_people = QuotePeople::where('quote_id', $quote_id)->first();
                if ($quote_service['type'] == 'service') {
                    $service = QuoteServiceAmount::where(
                        'quote_service_id',
                        $quote_service['id']
                    )->first();
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
                    foreach ($data['passengers'] as $index_passenger => $passenger) {
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
                    foreach ($data['passengers'] as $index_passenger => $passenger) {

                        if ($quote_people_initial['adults'] == $quote_service['adult'] && $quote_people_initial['child'] == $quote_service['child']) {

                            $passengers[] = roundLito(number_format(
                                ((float) ($service->price_adult)),
                                2,
                                '.',
                                ''
                            ));

                            if ($quote_people_initial['adults'] > 0) {
                                $passengers_adults[] = roundLito(number_format(
                                    ((float) ($service->price_adult)),
                                    2,
                                    '.',
                                    ''
                                ));
                            }

                            if ($quote_people_initial['child'] > 0) {
                                if ($all_childs > 0) {
                                    $quote_people['child'] = $quote_people_initial['child'] - $all_childs;
                                }

                                if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                    $passengers_childs[] = roundLito(number_format(
                                        (0.0),
                                        2,
                                        '.',
                                        ''
                                    ));
                                } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child) {
                                    $passengers_childs[] = roundLito(number_format(
                                        ((float) ($service->price_child)),
                                        2,
                                        '.',
                                        ''
                                    ));
                                } else {
                                    $passengers_childs[] = roundLito(number_format(
                                        ((float) ($service->price_adult)),
                                        2,
                                        '.',
                                        ''
                                    ));
                                }

                                if ($index_passenger >= $quote_people_initial['adults']) {
                                    $childs++;
                                }
                            }
                        } else {
                            $service_amount = $service->price_adult;

                            $multiplePassengers = true;
                            $quote_service_passenger = QuoteServicePassenger::where(
                                'quote_service_id',
                                $quote_service['id']
                            )->where('quote_passenger_id', $passenger['id'])->get()->count();

                            if ($quote_service_passenger > 0) {
                                $passengers[] = roundLito(number_format(
                                    ((float) ($service_amount / $quote_service_passenger)),
                                    2,
                                    '.',
                                    ''
                                ));
                            } else {
                                $passengers[] = 0;
                            }
                        }
                    }

                    $data['categories'][count($data['categories']) - 1]['services'][] = [
                        'date_service'      => $quote_service['date_in'],
                        'service_code'      => $quote_service['service']['aurora_code'],
                        'service_name'      => $quote_service['service']['name'],
                        'passengers'        => $passengers,
                        'passengers_adults' => $passengers_adults,
                        'passengers_childs' => $passengers_childs,
                    ];
                }
                if ($quote_service['type'] == 'hotel') {
                    $service_amount = QuoteServiceAmount::where('quote_service_id', $quote_service['id'])->first();
                    if ($quote_service['single'] > 0 && ($quote_service['double'] > 0 || $quote_service['triple'] > 0)) {
                        $multiplePassengers = true;
                    }
                    if ($quote_service['double'] > 0 && ($quote_service['single'] > 0 || $quote_service['triple'] > 0)) {
                        $multiplePassengers = true;
                    }
                    if ($quote_service['triple'] > 0 && ($quote_service['double'] > 0 || $quote_service['single'] > 0)) {
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
                    foreach ($data['passengers'] as $index_passenger => $passenger) {
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
                    $quantity_people = $quote_service['adult'] + $quote_service['child'];
                    if ($quote_service['single'] > 0) {
                        $amount_for_room_single = roundLito(number_format(
                            ((float) ($service_amount['price_per_night'])),
                            2,
                            '.',
                            ''
                        ));
                        $amount_adult_for_room_single = roundLito(number_format(
                            ((float) ($service_amount['price_adult'])),
                            2,
                            '.',
                            ''
                        ));
                        $amount_child_for_room_single = roundLito(number_format(
                            ((float) ($service_amount['price_child'])),
                            2,
                            '.',
                            ''
                        ));
                        $quantity_rooms_single = $quote_service['single'];

                        $childs = 0;
                        foreach ($data['passengers'] as $index_passenger => $passenger) {
                            $passengers[] = roundLito(number_format(
                                ((float) ($amount_for_room_single)),
                                2,
                                '.',
                                ''
                            ));

                            if ($quote_people_initial['adults'] > 0) {
                                $passengers_adults[] = roundLito(number_format(
                                    ((float) ($amount_adult_for_room_single)),
                                    2,
                                    '.',
                                    ''
                                ));
                            }

                            if ($quote_people_initial['child'] > 0) {
                                if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                    $passengers_childs[] = roundLito(number_format(
                                        (0.0),
                                        2,
                                        '.',
                                        ''
                                    ));
                                } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child) {
                                    $passengers_childs[] = roundLito(number_format(
                                        ((float) ($amount_child_for_room_single)),
                                        2,
                                        '.',
                                        ''
                                    ));
                                } else {
                                    $passengers_childs[] = roundLito(number_format(
                                        ((float) ($amount_adult_for_room_single)),
                                        2,
                                        '.',
                                        ''
                                    ));
                                }

                                if ($index_passenger >= $quote_people_initial['adults']) {
                                    $childs++;
                                }
                            }

                            $quantity_people -= 1;
                            $quantity_rooms_single -= 1;
                            $occupation_name = ' - SGL';
                            if (! str_contains($data['passengers'][$index_passenger]['last_name'], 'SGL')) {
                                $data['passengers'][$index_passenger]['last_name'] = $data['passengers'][$index_passenger]['last_name'] . ' - SGL';
                            }
                            if ($quantity_people == 0 || $quantity_rooms_single == 0) {
                                $pivot_index_passenger = $index_passenger;
                                break;
                            }
                        }
                    }

                    if ($quote_service['double'] > 0) {
                        $amount_for_room_double_for_person = roundLito(number_format(
                            ((float) (($service_amount['price_per_night']))),
                            2,
                            '.',
                            ''
                        ));

                        $amount_adult_for_room_double_for_person = roundLito(number_format(
                            ((float) (($service_amount['price_adult']))),
                            2,
                            '.',
                            ''
                        ));
                        $amount_child_for_room_double_for_person = roundLito(number_format(
                            ((float) (($service_amount['price_child']))),
                            2,
                            '.',
                            ''
                        ));
                        $quantity_rooms_double = $quote_service['double'];
                        $quantity_persons = 2 * $quantity_rooms_double;
                        $childs = 0;
                        foreach ($data['passengers'] as $index_passenger2 => $passenger) {
                            if ($index_passenger2 > $pivot_index_passenger || is_null($pivot_index_passenger)) {
                                $passengers[] = roundLito(number_format(
                                    ((float) ($amount_for_room_double_for_person)),
                                    2,
                                    '.',
                                    ''
                                ));

                                if ($quote_people_initial['adults'] > 0) {
                                    $passengers_adults[] = roundLito(number_format(
                                        ((float) ($amount_adult_for_room_double_for_person)),
                                        2,
                                        '.',
                                        ''
                                    ));
                                }

                                if ($quote_people_initial['child'] > 0) {
                                    if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                        $passengers_childs[] = roundLito(number_format(
                                            (0.0),
                                            2,
                                            '.',
                                            ''
                                        ));
                                    } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child) {
                                        $passengers_childs[] = roundLito(number_format(
                                            ((float) ($amount_child_for_room_double_for_person)),
                                            2,
                                            '.',
                                            ''
                                        ));
                                    } else {
                                        $passengers_childs[] = roundLito(number_format(
                                            ((float) ($amount_adult_for_room_double_for_person)),
                                            2,
                                            '.',
                                            ''
                                        ));
                                    }

                                    if ($index_passenger2 >= $quote_people_initial['adults']) {
                                        $childs++;
                                    }
                                }

                                $quantity_people -= 1;
                                $quantity_persons -= 1;
                                $occupation_name = ' - DBL';
                                if (! str_contains($data['passengers'][$index_passenger2]['last_name'], 'DBL')) {
                                    $data['passengers'][$index_passenger2]['last_name'] = $data['passengers'][$index_passenger2]['last_name'] . ' - DBL';
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

                    if ($quote_service['triple'] > 0) {
                        $amount_for_room_triple_for_person = roundLito(number_format(
                            ((float) (($service_amount['price_per_night']))),
                            2,
                            '.',
                            ''
                        ));
                        $amount_adult_for_room_triple_for_person = roundLito(number_format(
                            ((float) (($service_amount['price_adult']))),
                            2,
                            '.',
                            ''
                        ));
                        $amount_child_for_room_triple_for_person = roundLito(number_format(
                            ((float) (($service_amount['price_child']))),
                            2,
                            '.',
                            ''
                        ));
                        $quantity_rooms_triple = $quote_service['triple'];
                        $quantity_persons = 3 * $quantity_rooms_triple;
                        // $quantity_persons = 0;
                        $childs = 0;
                        foreach ($data['passengers'] as $index_passenger3 => $passenger) {

                            if ($index_passenger3 > $pivot_index_passenger || is_null($pivot_index_passenger)) {
                                $passengers[] = roundLito(number_format(
                                    ((float) ($amount_for_room_triple_for_person)),
                                    2,
                                    '.',
                                    ''
                                ));

                                if ($quote_people_initial['adults'] > 0) {
                                    $passengers_adults[] = roundLito(number_format(
                                        ((float) ($amount_adult_for_room_triple_for_person)),
                                        2,
                                        '.',
                                        ''
                                    ));
                                }

                                if ($quote_people_initial['child'] > 0) {
                                    if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                        $passengers_childs[] = roundLito(number_format(
                                            (0.0),
                                            2,
                                            '.',
                                            ''
                                        ));
                                    } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child) {
                                        $passengers_childs[] = roundLito(number_format(
                                            ((float) ($amount_child_for_room_triple_for_person)),
                                            2,
                                            '.',
                                            ''
                                        ));
                                    } else {
                                        $passengers_childs[] = roundLito(number_format(
                                            ((float) ($amount_adult_for_room_triple_for_person)),
                                            2,
                                            '.',
                                            ''
                                        ));
                                    }

                                    if ($index_passenger3 >= $quote_people_initial['adults']) {
                                        $childs++;
                                    }
                                }

                                $quantity_people -= 1;
                                $quantity_persons -= 1;
                                $occupation_name = ' - TPL';
                                if (! str_contains($data['passengers'][$index_passenger3]['last_name'], 'TPL')) {
                                    $data['passengers'][$index_passenger3]['last_name'] = $data['passengers'][$index_passenger3]['last_name'] . ' - TPL';
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

                    for ($i = 0; $i < $quote_service['nights']; $i++) {
                        $data['categories'][count($data['categories']) - 1]['services'][] = [
                            'date_service' => Carbon::parse(Carbon::createFromFormat(
                                'd/m/Y',
                                $quote_service['date_in']
                            )->format('Y-m-d'))->addDays($i)->format('d/m/Y'),
                            'service_code'      => $quote_service['hotel']['channel'][0]['code'],
                            'service_name'      => $quote_service['hotel']['name'],
                            'passengers'        => $passengers,
                            'passengers_adults' => $passengers_adults,
                            'passengers_childs' => $passengers_childs,
                        ];
                    }
                }

                $data['quote_people'][] = $quote_people;
                $data['quote_service'][] = $quote_service;
                $data['all_childs'][] = @$all_childs;
            }

            foreach ($quote_services_optional as $quote_service) {
                $quote_people = QuotePeople::where('quote_id', $quote_id)->first();
                if ($quote_service['type'] == 'service') {
                    $service = QuoteServiceAmount::where(
                        'quote_service_id',
                        $quote_service['id']
                    )->first();
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
                    foreach ($data['passengers_optional'] as $index_passenger => $passenger) {
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
                    $childs = 0;
                    foreach ($data['passengers_optional'] as $index_passenger => $passenger) {

                        if ($quote_people_initial['adults'] == $quote_service['adult'] && $quote_people_initial['child'] == $quote_service['child']) {

                            $passengers_optional[] = roundLito(number_format(
                                ((float) ($service->price_adult)),
                                2,
                                '.',
                                ''
                            ));

                            if ($quote_people['adults'] > 0) {
                                $passengers_optional_adults[] = roundLito(number_format(
                                    ((float) ($service->price_adult)),
                                    2,
                                    '.',
                                    ''
                                ));
                            }

                            if ($quote_people_initial['child'] > 0) {
                                if ($all_childs > 0) {
                                    $quote_people['child'] = $quote_people_initial['child'] - $all_childs;
                                }

                                if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                    $passengers_optional_childs[] = roundLito(number_format(
                                        (0.0),
                                        2,
                                        '.',
                                        ''
                                    ));
                                } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child) {
                                    $passengers_optional_childs[] = roundLito(number_format(
                                        ((float) ($service->price_child)),
                                        2,
                                        '.',
                                        ''
                                    ));
                                } else {
                                    $passengers_optional_childs[] = roundLito(number_format(
                                        ((float) ($service->price_adult)),
                                        2,
                                        '.',
                                        ''
                                    ));
                                }

                                if ($index_passenger >= $quote_people_initial['adults']) {
                                    $childs++;
                                }
                            }
                        } else {
                            $service_amount = $service->price_adult;

                            $multiplePassengers = true;
                            $quote_service_passenger = QuoteServicePassenger::where(
                                'quote_service_id',
                                $quote_service['id']
                            )->where('quote_passenger_id', $passenger['id'])->get()->count();

                            if ($quote_service_passenger > 0) {
                                $passengers_optional[] = roundLito(number_format(
                                    ((float) ($service_amount / $quote_service_passenger)),
                                    2,
                                    '.',
                                    ''
                                ));
                            } else {
                                $passengers_optional[] = 0;
                            }
                        }
                    }

                    $data['categories_optional'][count($data['categories_optional']) - 1]['services_optional'][] = [
                        'date_service'               => $quote_service['date_in'],
                        'service_code'               => $quote_service['service']['aurora_code'],
                        'service_name'               => $quote_service['service']['name'],
                        'passengers_optional'        => $passengers_optional,
                        'passengers_optional_adults' => $passengers_optional_adults,
                        'passengers_optional_childs' => $passengers_optional_childs,
                    ];
                }
                if ($quote_service['type'] == 'hotel') {
                    $service_amount = QuoteServiceAmount::where('quote_service_id', $quote_service['id'])->first();

                    if ($quote_service['single'] > 0 && ($quote_service['double'] > 0 || $quote_service['triple'] > 0)) {
                        $multiplePassengers = true;
                    }
                    if ($quote_service['double'] > 0 && ($quote_service['single'] > 0 || $quote_service['triple'] > 0)) {
                        $multiplePassengers = true;
                    }
                    if ($quote_service['triple'] > 0 && ($quote_service['double'] > 0 || $quote_service['single'] > 0)) {
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
                    foreach ($data['passengers_optional'] as $index_passenger => $passenger) {
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
                    $quantity_people = $quote_service['adult'] + $quote_service['child'];
                    if ($quote_service['single'] > 0) {
                        $amount_for_room_single = roundLito(number_format(
                            ((float) ($service_amount['price_per_night'])),
                            2,
                            '.',
                            ''
                        ));
                        $amount_adult_for_room_single = roundLito(number_format(
                            ((float) ($service_amount['price_adult'])),
                            2,
                            '.',
                            ''
                        ));
                        $amount_child_for_room_single = roundLito(number_format(
                            ((float) ($service_amount['price_child'])),
                            2,
                            '.',
                            ''
                        ));
                        $quantity_rooms_single = $quote_service['single'];
                        $childs = 0;
                        foreach ($data['passengers_optional'] as $index_passenger => $passenger) {
                            $passengers_optional[] = roundLito(number_format(
                                ((float) ($amount_for_room_single)),
                                2,
                                '.',
                                ''
                            ));

                            if ($quote_people_initial['adults'] > 0) {
                                $passengers_optional_adults[] = roundLito(number_format(
                                    ((float) ($amount_adult_for_room_single)),
                                    2,
                                    '.',
                                    ''
                                ));
                            }

                            if ($quote_people_initial['child'] > 0) {
                                if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                    $passengers_optional_childs[] = roundLito(number_format(
                                        (0.0),
                                        2,
                                        '.',
                                        ''
                                    ));
                                } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child) {
                                    $passengers_optional_childs[] = roundLito(number_format(
                                        ((float) ($amount_child_for_room_single)),
                                        2,
                                        '.',
                                        ''
                                    ));
                                } else {
                                    $passengers_optional_childs[] = roundLito(number_format(
                                        ((float) ($amount_adult_for_room_single)),
                                        2,
                                        '.',
                                        ''
                                    ));
                                }

                                if ($index_passenger >= $quote_people_initial['adults']) {
                                    $childs++;
                                }
                            }

                            $quantity_people -= 1;
                            $quantity_rooms_single -= 1;
                            $occupation_name = ' - SGL';
                            if (! str_contains($data['passengers_optional'][$index_passenger]['last_name'], 'SGL')) {
                                $data['passengers_optional'][$index_passenger]['last_name'] = $data['passengers_optional'][$index_passenger]['last_name'] . ' - SGL';
                            }
                            if ($quantity_people == 0 || $quantity_rooms_single == 0) {
                                $pivot_index_passenger = $index_passenger;
                                break;
                            }
                        }
                    }

                    if ($quote_service['double'] > 0) {
                        $amount_for_room_double_for_person = roundLito(number_format(
                            ((float) (($service_amount['price_per_night']))),
                            2,
                            '.',
                            ''
                        ));
                        $amount_adult_for_room_double_for_person = roundLito(number_format(
                            ((float) (($service_amount['price_adult']))),
                            2,
                            '.',
                            ''
                        ));
                        $amount_child_for_room_double_for_person = roundLito(number_format(
                            ((float) (($service_amount['price_child']))),
                            2,
                            '.',
                            ''
                        ));
                        $quantity_rooms_double = $quote_service['double'];
                        $quantity_persons = 2 * $quantity_rooms_double;
                        $childs = 0;
                        foreach ($data['passengers_optional'] as $index_passenger2 => $passenger) {
                            if ($index_passenger2 > $pivot_index_passenger || is_null($pivot_index_passenger)) {
                                $passengers_optional[] = roundLito(number_format(
                                    ((float) ($amount_for_room_double_for_person)),
                                    2,
                                    '.',
                                    ''
                                ));

                                if ($quote_people_initial['adults'] > 0) {
                                    $passengers_optional_adults[] = roundLito(number_format(
                                        ((float) ($amount_adult_for_room_double_for_person)),
                                        2,
                                        '.',
                                        ''
                                    ));
                                }

                                if ($quote_people_initial['child'] > 0) {
                                    if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                        $passengers_optional_childs[] = roundLito(number_format(
                                            (0.0),
                                            2,
                                            '.',
                                            ''
                                        ));
                                    } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child) {
                                        $passengers_optional_childs[] = roundLito(number_format(
                                            ((float) ($amount_child_for_room_double_for_person)),
                                            2,
                                            '.',
                                            ''
                                        ));
                                    } else {
                                        $passengers_optional_childs[] = roundLito(number_format(
                                            ((float) ($amount_for_room_double_for_person)),
                                            2,
                                            '.',
                                            ''
                                        ));
                                    }

                                    if ($index_passenger2 >= $quote_people_initial['adults']) {
                                        $childs++;
                                    }
                                }

                                $quantity_people -= 1;
                                $quantity_persons -= 1;
                                $occupation_name = ' - DBL';
                                if (! str_contains($data['passengers_optional'][$index_passenger2]['last_name'], 'DBL')) {
                                    $data['passengers_optional'][$index_passenger2]['last_name'] = $data['passengers_optional'][$index_passenger2]['last_name'] . ' - DBL';
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

                    if ($quote_service['triple'] > 0) {
                        $amount_for_room_triple_for_person = roundLito(number_format(
                            ((float) (($service_amount['price_per_night']))),
                            2,
                            '.',
                            ''
                        ));
                        $amount_adult_for_room_triple_for_person = roundLito(number_format(
                            ((float) (($service_amount['price_adult']))),
                            2,
                            '.',
                            ''
                        ));
                        $amount_child_for_room_triple_for_person = roundLito(number_format(
                            ((float) (($service_amount['price_child']))),
                            2,
                            '.',
                            ''
                        ));
                        $quantity_rooms_triple = $quote_service['triple'];
                        $quantity_persons = 3 * $quantity_rooms_triple;
                        $childs = 0;
                        foreach ($data['passengers_optional'] as $index_passenger3 => $passenger) {

                            if ($index_passenger3 > $pivot_index_passenger || is_null($pivot_index_passenger)) {
                                $passengers_optional[] = roundLito(number_format(
                                    ((float) ($amount_for_room_triple_for_person)),
                                    2,
                                    '.',
                                    ''
                                ));

                                if ($quote_people_initial['adults'] > 0) {
                                    $passengers_optional_adults[] = roundLito(number_format(
                                        ((float) ($amount_adult_for_room_triple_for_person)),
                                        2,
                                        '.',
                                        ''
                                    ));
                                }

                                if ($quote_people_initial['child'] > 0) {
                                    if ($quote_ages_child[$childs]['age'] <= $min_age_child) {
                                        $passengers_optional_childs[] = roundLito(number_format(
                                            (0.0),
                                            2,
                                            '.',
                                            ''
                                        ));
                                    } elseif ($quote_ages_child[$childs]['age'] <= $max_age_child) {
                                        $passengers_optional_childs[] = roundLito(number_format(
                                            ((float) ($amount_child_for_room_triple_for_person)),
                                            2,
                                            '.',
                                            ''
                                        ));
                                    } else {
                                        $passengers_optional_childs[] = roundLito(number_format(
                                            ((float) ($amount_adult_for_room_triple_for_person)),
                                            2,
                                            '.',
                                            ''
                                        ));
                                    }

                                    if ($index_passenger3 >= $quote_people_initial['adults']) {
                                        $childs++;
                                    }
                                }

                                $quantity_people -= 1;
                                $quantity_persons -= 1;
                                $occupation_name = ' - TPL';
                                if (! str_contains($data['passengers_optional'][$index_passenger3]['last_name'], 'TPL')) {
                                    $data['passengers_optional'][$index_passenger3]['last_name'] = $data['passengers_optional'][$index_passenger3]['last_name'] . ' - TPL';
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
                    for ($i = 0; $i < $quote_service['nights']; $i++) {
                        $data['categories_optional'][count($data['categories_optional']) - 1]['services_optional'][] = [
                            'date_service' => Carbon::parse(Carbon::createFromFormat(
                                'd/m/Y',
                                $quote_service['date_in']
                            )->format('Y-m-d'))->addDays($i)->format('d/m/Y'),
                            'service_code'               => $quote_service['hotel']['channel'][0]['code'],
                            'service_name'               => $quote_service['hotel']['name'],
                            'passengers_optional'        => $passengers_optional,
                            'passengers_optional_adults' => $passengers_optional_adults,
                            'passengers_optional_childs' => $passengers_optional_childs,
                        ];
                    }
                }

                $data['optional_quote_people'][] = $quote_people;
                $data['optional_quote_service'][] = $quote_service;
                $data['optional_all_childs'][] = @$all_childs;
            }

            if (! $multiplePassengers) {
                $total = $data['passengers'][0]['total'];
                $total_adult = $data['passengers'][0]['total_adult'];
                $total_child = $data['passengers'][0]['total_child'];
                $data['passengers'] = [
                    [
                        'first_name'  => 'PAX' . $occupation_name,
                        'last_name'   => '',
                        'total'       => roundLito((float) $total),
                        'total_adult' => roundLito((float) $total_adult),
                        'total_child' => roundLito((float) $total_child),
                    ],
                ];

                foreach ($data['categories'][0]['services'] as $index_service => $service) {
                    array_splice($data['categories'][0]['services'][$index_service]['passengers'], 1);
                }

                $total_optional = $data['passengers_optional'][0]['total'];
                $total_optional_adult = $data['passengers_optional'][0]['total_adult'];
                $total_optional_child = $data['passengers_optional'][0]['total_child'];
                $data['passengers_optional'] = [
                    [
                        'first_name'  => 'PAX' . $occupation_name,
                        'last_name'   => '',
                        'total'       => roundLito((float) $total_optional),
                        'total_adult' => roundLito((float) $total_optional_adult),
                        'total_child' => roundLito((float) $total_optional_child),
                    ],
                ];

                foreach ($data['categories_optional'][0]['services_optional'] as $index_service => $service) {
                    array_splice(
                        $data['categories_optional'][0]['services_optional'][$index_service]['passengers_optional'],
                        1
                    );
                }
            }

            foreach ($data['passengers'] as $index_passenger => $passenger) {
                foreach ($data['categories'][0]['services'] as $service) {
                    $service_pax_total = (isset($service['passengers'][$index_passenger])) ? $service['passengers'][$index_passenger] : 0;
                    $service_pax_adult = (isset($service['passengers_adults'][$index_passenger])) ? $service['passengers_adults'][$index_passenger] : 0;
                    $service_pax_child = (isset($service['passengers_childs'][$index_passenger])) ? $service['passengers_childs'][$index_passenger] : 0;
                    $data['passengers'][$index_passenger]['total'] .= number_format(
                        ((float) $service_pax_total),
                        2,
                        '.',
                        ''
                    );
                    $data['passengers'][$index_passenger]['total_adult'] .= number_format(
                        ((float) $service_pax_adult),
                        2,
                        '.',
                        ''
                    );
                    $data['passengers'][$index_passenger]['total_child'] .= number_format(
                        ((float) $service_pax_child),
                        2,
                        '.',
                        ''
                    );
                }
            }

            foreach ($data['passengers_optional'] as $index_passenger => $passenger) {
                foreach ($data['categories_optional'][0]['services_optional'] as $service) {
                    $service_pax_total = (isset($service['passengers_optional'][$index_passenger])) ? $service['passengers_optional'][$index_passenger] : 0;
                    $service_pax_adult = (isset($service['passengers_optional_adults'][$index_passenger])) ? $service['passengers_optional_adults'][$index_passenger] : 0;
                    $service_pax_child = (isset($service['passengers_optional_childs'][$index_passenger])) ? $service['passengers_optional_childs'][$index_passenger] : 0;
                    $data['passengers_optional'][$index_passenger]['total'] .= number_format(
                        ((float) $service_pax_total),
                        2,
                        '.',
                        ''
                    );
                    $data['passengers_optional'][$index_passenger]['total_adult'] .= number_format(
                        ((float) $service_pax_adult),
                        2,
                        '.',
                        ''
                    );
                    $data['passengers_optional'][$index_passenger]['total_child'] .= number_format(
                        ((float) $service_pax_child),
                        2,
                        '.',
                        ''
                    );
                }
            }

            $categories = QuoteCategory::where('quote_id', $quote_id)->get()->toArray();
            foreach ($categories as $index_category => $category) {
                if ($category_id == $category['id']) {

                    $flags = [];
                    $flags['_passengers'] = $data['all_passengers'];
                    $flags['_passengers_optional'] = $data['all_passengers_optional'];
                    $flags['_quote_people_initial'] = $quote_people_initial;
                    $flags['_quote_people'][] = @$data['quote_people'];
                    $flags['_quote_service'][] = @$data['quote_service'];
                    $flags['_all_childs'][] = @$data['all_childs'];
                    $flags['_optional_quote_people'][] = @$data['optional_quote_people'];
                    $flags['_optional_quote_service'][] = @$data['optional_quote_service'];
                    $flags['_optional_all_childs'][] = @$data['optional_all_childs'];
                    $flags['_quote_ages_child'] = @$data['quote_ages_child'];
                    $flags['_children_service'] = @$data['children_service'];
                    $flags['_optional_children_service'] = @$data['optional_children_service'];
                    $flags['multiple_passengers'] = (int) $multiplePassengers;
                    $categories[$index_category]['flags'] = $flags;

                    $categories[$index_category]['passengers'] = [];
                    foreach ($data['passengers'] as $passenger) {
                        $categories[$index_category]['passengers'][] = [
                            'passenger_name' => $passenger['first_name'] . ' ' . $passenger['last_name'],
                            'amount'         => roundLito((float) $passenger['total']),
                            'amount_adult'   => roundLito((float) $passenger['total_adult']),
                            'amount_child'   => roundLito((float) $passenger['total_child']),
                        ];
                    }
                }
            }
            //Funcionalidad de opcional
            foreach ($categories as $index_category => $category) {
                if ($category_id == $category['id']) {
                    $categories[$index_category]['passengers_optional'] = [];
                    foreach ($data['passengers_optional'] as $passenger) {
                        $categories[$index_category]['passengers_optional'][] = [
                            'passenger_name' => $passenger['first_name'] . ' ' . $passenger['last_name'],
                            'amount'         => roundLito((float) $passenger['total']),
                            'amount_adult'   => roundLito((float) $passenger['total_adult']),
                            'amount_child'   => roundLito((float) $passenger['total_child']),
                        ];
                    }
                }
            }
        }

        if ($quote->operation == 'ranges') {

            $categories = QuoteCategory::where('quote_id', $quote_id)->get()->toArray();
            $ranges_quote = QuoteRange::where('quote_id', $quote_id)->get();
            foreach ($categories as $index_category => $category) {
                $categories[$index_category]['ranges'] = [];
                $categories[$index_category]['ranges_optional'] = [];
                foreach ($ranges_quote as $range_quote) {
                    $quote_service_ids = QuoteService::where('quote_category_id', $category['id'])
                        ->where('optional', 0);
                    if (! $get_all_rates_services) {
                        $quote_service_ids = $quote_service_ids->where('locked', 0);
                    }
                    $quote_service_ids = $quote_service_ids->pluck('id');

                    $amount_range = QuoteDynamicSaleRate::where(
                        'quote_category_id',
                        $category['id']
                    )->whereIn('quote_service_id', $quote_service_ids)->where(
                        'pax_from',
                        $range_quote['from']
                    )->where('pax_to', $range_quote['to'])->sum('simple');
                    $categories[$index_category]['ranges'][] = [
                        'from'   => $range_quote['from'],
                        'to'     => $range_quote['to'],
                        'amount' => $amount_range,
                    ];
                }
                foreach ($ranges_quote as $range_quote) {
                    $quote_service_ids = QuoteService::where('quote_category_id', $category['id'])->where(
                        'optional',
                        1
                    );
                    if (! $get_all_rates_services) {
                        $quote_service_ids = $quote_service_ids->where('locked', 0);
                    }
                    $quote_service_ids = $quote_service_ids->pluck('id');
                    $amount_range = QuoteDynamicSaleRate::where(
                        'quote_category_id',
                        $category['id']
                    )->whereIn('quote_service_id', $quote_service_ids)->where(
                        'pax_from',
                        $range_quote['from']
                    )->where('pax_to', $range_quote['to'])->sum('simple');

                    $categories[$index_category]['ranges_optional'][] = [
                        'from'   => $range_quote['from'],
                        'to'     => $range_quote['to'],
                        'amount' => $amount_range,
                    ];
                }
            }
        }

        return $categories;
    }

    private function replacePackageToQuote($quote_log): void
    {
        DB::transaction(function () use ($quote_log) {

            $quote_id_editing = $quote_log->quote_id;
            $package_id = $quote_log->object_id;
            $quote_for_copy = Quote::where('id', $quote_id_editing)
                ->with([
                    'categories.services.service_rate',
                    'categories.services.service_rooms',
                    'categories.services.service_rooms_hyperguest',
                    'ranges',
                    'notes',
                    'passengers',
                    'people',
                    'destinations',
                ])->first();

            $quote_for_copy_name = $quote_for_copy->name;
            $date_in = $quote_for_copy->date_in;
            $estimated_travel_date = $quote_for_copy->estimated_travel_date;
            $user_id = $quote_for_copy->user_id;
            $markup = $quote_for_copy->markup;
            $nights = $quote_for_copy->nights;
            $service_type_id = $quote_for_copy->service_type_id;

            $new_object_id = DB::table('quotes')->insertGetId([
                'name'                  => $quote_for_copy_name,
                'date_in'               => $date_in,
                'estimated_travel_date' => $estimated_travel_date,
                'nights'                => $nights,
                'service_type_id'       => $service_type_id,
                'user_id'               => $user_id,
                'markup'                => $markup,
                'status'                => 1,
                'operation'             => $quote_for_copy->operation,
                'created_at'            => Carbon::now(),
            ]);

            foreach ($quote_for_copy->categories as $c) {
                $new_category_id = DB::table('quote_categories')->insertGetId([
                    'quote_id'      => $new_object_id,
                    'type_class_id' => $c->type_class_id,
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ]);
                foreach ($c->services as $s) {
                    $new_date_in = DateTime::createFromFormat('d/m/Y', $s->date_in);
                    $new_date_out = DateTime::createFromFormat('d/m/Y', $s->date_out);

                    $new_service_id = DB::table('quote_services')->insertGetId([
                        'quote_category_id' => $new_category_id,
                        'type'              => $s->type,
                        'object_id'         => $s->object_id,
                        'order'             => $s->order,
                        'date_in'           => $new_date_in->format('Y-m-d'),
                        'date_out'          => $new_date_out->format('Y-m-d'),
                        'nights'            => $s->nights,
                        'adult'             => $s->adult,
                        'child'             => $s->child,
                        'infant'            => $s->infant,
                        'single'            => $s->single,
                        'double'            => $s->double,
                        'triple'            => $s->triple,
                        'triple_active'     => $s->triple_active,
                        'hyperguest_pull'   => $s->hyperguest_pull,
                        'optional'          => (int) $s->optional,
                        'markup_regionalization' => $markup ?? 0,
                        'created_at'        => Carbon::now(),
                        'updated_at'        => Carbon::now(),
                    ]);
                    if ($s->type == 'service' and isset($s->service_rate->id)) {
                        DB::table('quote_service_rates')->insert([
                            'quote_service_id' => $new_service_id,
                            'service_rate_id'  => $s->service_rate->service_rate_id,
                            'created_at'       => Carbon::now(),
                            'updated_at'       => Carbon::now(),
                        ]);
                    }
                    if ($s->type == 'hotel') {
                        foreach ($s->service_rooms as $r) {
                            DB::table('quote_service_rooms')->insert([
                                'quote_service_id'  => $new_service_id,
                                'rate_plan_room_id' => $r->rate_plan_room_id,
                                'created_at'        => Carbon::now(),
                                'updated_at'        => Carbon::now(),
                            ]);
                        }

                        foreach ($s->service_rooms_hyperguest as $r) {
                            DB::table('quote_service_rooms_hyperguest')->insert([
                                'quote_service_id'  => $new_service_id,
                                'room_id' => $r->room_id,
                                'rate_plan_id' => $r->rate_plan_id,
                                'created_at'        => Carbon::now(),
                                'updated_at'        => Carbon::now(),
                            ]);
                        }
                    }
                }
            }

            if ($quote_for_copy->operation == 'passengers') {
                foreach ($quote_for_copy->people as $people) {
                    DB::table('quote_people')->insert([
                        'adults'     => $people->adults,
                        'child'      => $people->child,
                        'quote_id'   => $new_object_id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
                foreach ($quote_for_copy->passengers as $passenger) {
                    DB::table('quote_passengers')->insert([
                        'first_name'      => $passenger->first_name,
                        'last_name'       => $passenger->last_name,
                        'gender'          => $passenger->gender ? $passenger->gender : null,
                        'birthday'        => $passenger->birthday ? $passenger->birthday : null,
                        'document_number' => $passenger->document_number,
                        'doctype_iso'     => $passenger->doctype_iso,
                        'country_iso'     => $passenger->country_iso,
                        'email'           => $passenger->email,
                        'phone'           => $passenger->phone,
                        'notes'           => $passenger->notes,
                        'type'            => $passenger->type,
                        'quote_id'        => $new_object_id,
                        'created_at'      => Carbon::now(),
                        'updated_at'      => Carbon::now(),
                    ]);
                }
            }

            foreach ($quote_for_copy->notes as $note) {
                DB::table('quote_notes')->insert([
                    'parent_note_id' => $note->parent_note_id,
                    'comment'        => $note->comment,
                    'status'         => $note->status,
                    'quote_id'       => $new_object_id,
                    'user_id'        => $note->user_id,
                    'created_at'     => Carbon::now(),
                    'updated_at'     => Carbon::now(),
                ]);
            }

            foreach ($quote_for_copy->destinations as $destiny) {
                DB::table('quote_destinations')->insert([
                    'quote_id'   => $new_object_id,
                    'state_id'   => $destiny->state_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            //creo el log de paquete
            DB::table('quote_logs')->insert([
                'quote_id'   => $new_object_id,
                'type'       => 'from_package',
                'object_id'  => $package_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            //creo el log nuevo de la cotizacion
            DB::table('quote_logs')->insert([
                'quote_id'   => $quote_id_editing,
                'type'       => 'editing_quote',
                'object_id'  => $new_object_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            DB::table('quote_logs')
                ->where('id', $quote_log->id)
                ->where('type', '!=', 'from_package')
                ->delete();
        });
    }

    public function getMinDateQuoteServices($quote_service_id, $quote_category_id, $date_in_compare): ?string
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

    public function updateListServicePassengersGeneral($quote_id, $quote_adult_general, $quote_child_general): void
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
                        'locked',
                    ]);
                    $query->with([
                        'passengers' => function ($query) {
                            $query->select(['id', 'quote_service_id', 'quote_passenger_id']);
                            $query->with([
                                'passenger' => function ($query) {
                                    $query->select(['id', 'type']);
                                },
                            ]);
                        },
                    ]);
                    $query->where('locked', 0);
                },
            ])
            ->get(['id', 'quote_id']);

        $hotels_groups = collect();
        foreach ($quote_categories as $category) {
            foreach ($category->services as $service) {
                if ($service->type == 'service' or $service->type == 'flight') {
                    $this->assignPassengerToService(
                        $service,
                        $quote_passengers,
                        $quote_adult_general,
                        $quote_child_general
                    );
                }

                if ($service->type == 'hotel') {
                    $total_accommodations = (int) $service->single + (int) $service->double + (int) $service->triple + (int) $service->double_child + (int) $service->triple_child;
                    if ($total_accommodations > 0) {

                        $hotels_groups->add($service);
                    }
                }
            }
        }
    }

    public function setAccommodationNotExist(): void
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
                ->orderBy('date_in')
                ->orderBy('order')
                ->get();

            //Todo Si no tiene acomodacion guardad se crea a partir de los hoteles
            foreach ($services_original as $s) {
                if ($s->type == 'hotel') {
                    $_hotels_accommodation->add([
                        'key'          => $s->object_id . '|' . $s->date_in . '|' . $s->nights,
                        'single'       => $s->single,
                        'double'       => $s->double,
                        'double_child' => $s->double_child,
                        'triple'       => $s->triple,
                        'triple_child' => $s->triple_child,
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

    /**
     * @throws Exception
     */
    public function saveQuoteServices($quote_id, $quote_original, $save_as): void
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
            if ($quote_original->operation == 'ranges') {
                DB::table('quote_ranges')->where('quote_id', $quote_id)->delete();
            }

            //Todo Elimino los pasajeros
            DB::table('quote_passengers')->where('quote_id', $quote_id)->delete();
            DB::table('quote_age_child')->where('quote_id', $quote_id)->delete();

            //Todo Traigo los logs
            $logs = $this->getLogs($quote_original->id);
            //Todo Elimino los logs
            DB::table('quote_logs')->where('quote_id', $quote_id)->delete();

            $quote = Quote::find($quote_id);
            $quote->code = $quote_original->code;
            $quote->name = $quote_original->name;
            $quote->date_in = $quote_original->date_in;
            $quote->estimated_travel_date = $quote_original->estimated_travel_date;
            $quote->markup = $quote_original->markup;
            $quote->discount = $quote_original->discount;
            $quote->discount_detail = $quote_original->discount_detail;
            $quote->order_related = $quote_original->order_related;
            $quote->order_position = $quote_original->order_position;
            $quote->cities = $quote_original->cities;
            $quote->nights = $quote_original->nights;
            $quote->operation = $quote_original->operation;
            $quote->language_id = $quote_original->language_id;
            $quote->package_id = $quote_original->package_id;
            $quote->reference_code = $quote_original->reference_code;
            $quote->file_id = $quote_original->file_id;
            $quote->file_number = $quote_original->file_number;
            $quote->file_total_amount = $quote_original->file_total_amount;

            $single_max = [];
            $double_max = [];
            $triple_max = [];

            foreach ($quote_original->categories as $cate_index => $category) {
                $QC = new QuoteCategory();
                $QC->quote_id = $quote_id;
                $QC->type_class_id = $category->type_class_id;

                foreach ($category->services as $key_service => $s) {
                    if ($s->type == 'hotel') {
                        if ($cate_index == 0) {
                            if ($s->single > 0 or $s->double > 0 or $s->triple > 0) {
                                if (! isset($single_max[$s->object_id . '-' . $s->date_in])) {
                                    $single_max[$s->object_id . '-' . $s->date_in] = 0;
                                    $double_max[$s->object_id . '-' . $s->date_in] = 0;
                                    $triple_max[$s->object_id . '-' . $s->date_in] = 0;
                                }
                                $single_max[$s->object_id . '-' . $s->date_in] = $single_max[$s->object_id . '-' . $s->date_in] + $s->single;
                                $double_max[$s->object_id . '-' . $s->date_in] = $double_max[$s->object_id . '-' . $s->date_in] + $s->double;
                                $triple_max[$s->object_id . '-' . $s->date_in] = $triple_max[$s->object_id . '-' . $s->date_in] + $s->triple;
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
                    $QCS->optional = (int) $s->optional;
                    $QCS->code_flight = $s->code_flight;
                    $QCS->origin = $s->origin;
                    $QCS->destiny = $s->destiny;
                    $QCS->extension_id = $s->extension_id;
                    $QCS->new_extension_id = $s->new_extension_id;
                    $QCS->parent_service_id = $s->parent_service_id;
                    $QCS->schedule_id = $s->schedule_id;

                    $QCS->is_file = $s->is_file;
                    $QCS->file_itinerary_id = $s->file_itinerary_id;
                    $QCS->file_status = $s->file_status;
                    $QCS->file_amount_sale = $s->file_amount_sale;
                    $QCS->file_amount_cost = $s->file_amount_cost;
                    $QCS->hyperguest_pull = $s->hyperguest_pull;
                    $QCS->markup_regionalization = $s->markup_regionalization ?? 0;

                    Log::info("markup_regionalization: " . $s->markup_regionalization ?? 'No existe el campo markup_regionalization');

                    $QCS->notes = $s->notes;
                    if ($quote_original->file_id) {
                        $QCS->locked = $s->locked;
                    } else {
                        if ($save_as and $s->locked) {
                            $QCS->locked = 0;
                        }
                    }

                    if ($s->type == 'hotel') {
                        foreach ($s->service_rooms as $service_room) {
                            $QHR = new QuoteServiceRoom();
                            $QHR->rate_plan_room_id = $service_room->rate_plan_room_id;
                            $QCS->service_rooms->add($QHR);
                        }
                        foreach ($s->service_rooms_hyperguest as $service_room) {
                            $QHR = new QuoteServiceRoomHyperguest();
                            $QHR->room_id = $service_room->room_id;
                            $QHR->rate_plan_id = $service_room->rate_plan_id;
                            $QCS->service_rooms_hyperguest->add($QHR);
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

                    foreach ($s->amount as $amount) {
                        $QCSAMOUNT = new QuoteServiceAmount();
                        $QCSAMOUNT->quote_service_id = $amount->quote_service_id;
                        $QCSAMOUNT->date_service = $amount->date_service;
                        $QCSAMOUNT->price_per_night_without_markup = $amount->price_per_night_without_markup ? $amount->price_per_night_without_markup : 0;
                        $QCSAMOUNT->price_per_night = $amount->price_per_night ? $amount->price_per_night : 0;
                        $QCSAMOUNT->price_adult_without_markup = $amount->price_adult_without_markup ? $amount->price_adult_without_markup : 0;
                        $QCSAMOUNT->price_adult = $amount->price_adult ? $amount->price_adult : 0;
                        $QCSAMOUNT->price_child_without_markup = $amount->price_child_without_markup ? $amount->price_child_without_markup : 0;
                        $QCSAMOUNT->price_child = $amount->price_child ? $amount->price_child : 0;
                        $QCSAMOUNT->price_teenagers_without_markup = $amount->price_teenagers_without_markup ? $amount->price_teenagers_without_markup : 0;
                        $QCSAMOUNT->price_teenagers = $amount->price_teenagers ? $amount->price_teenagers : 0;
                        $QCS->amount->add($QCSAMOUNT);
                    }

                    $QC->services->add($QCS);
                }
                $quote->categories->add($QC);
            }

            //Todo Agrego la acomocacion adulto + niños

            if ($quote_original->people->count() > 0) {
                $quote_people = $quote_original->people->first();
                $QP = new QuotePeople();
                $QP->adults = $quote_people->adults;
                $QP->child = $quote_people->child;
                $QP->quote_id = $quote_id;
                $quote->people->add($QP);

                //Todo Agrego la edades de los niños
                if ($quote_people->child > 0) {
                    foreach ($quote_original->age_child as $quote_age_child) {
                        $QPCH = new QuoteAgeChild();
                        $QPCH->age = $quote_age_child->age;
                        $QPCH->quote_id = $quote_id;
                        $QPCH->quote_age_child_id = $quote_age_child->id;
                        $quote->age_child->add($QPCH);
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
                    $quote->notes->add($QN);
                }
            }

            //Todo Agrego los rangos
            if ($quote_original->operation == 'ranges') {
                foreach ($quote_original->ranges as $range) {
                    $QR = new QuoteRange();
                    $QR->from = $range->from;
                    $QR->to = $range->to;
                    $QR->quote_id = $quote_id;
                    $quote->ranges->add($QR);
                }
            }

            //Todo Agrego los pasajeros
            if ($quote_original->operation == 'passengers') {
                foreach ($quote_original->passengers as $passenger) {

                    $birthday = $passenger->birthday ? $passenger->birthday : null;
                    if ($birthday == '0000-00-00') {
                        $birthday = null;
                    }

                    $QP = new QuotePassenger();
                    $QP->first_name = $passenger->first_name;
                    $QP->last_name = $passenger->last_name;
                    $QP->gender = $passenger->gender ? $passenger->gender : null;
                    $QP->birthday = $birthday;
                    $QP->document_number = $passenger->document_number;
                    $QP->doctype_iso = $passenger->doctype_iso;
                    $QP->country_iso = $passenger->country_iso;
                    $QP->type = ! empty($passenger->type) ? $passenger->type : 'ADL';
                    $QP->email = $passenger->email;
                    $QP->phone = $passenger->phone;
                    $QP->notes = $passenger->notes;
                    $QP->quote_age_child_id = $passenger->quote_age_child_id;
                    $QP->quote_passenger_id = $passenger->id;
                    $QP->quote_id = $quote_id;
                    $quote->passengers->add($QP);
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
                    $quote->logs->add($QL);
                }
            }

            //Agregamos la distributions
            if ($quote_original->operation == 'passengers') {
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
                    $quote->distributions->add($Dist);
                }
            }

            //Todo Agrega la acomodacion
            if (! empty($quote_original->accommodation)) {
                $QA = new QuoteAccommodation();
                $QA->single = (int) $quote_original->accommodation->single;
                $QA->double = (int) $quote_original->accommodation->double;
                $QA->double_child = (int) $quote_original->accommodation->double_child;
                $QA->triple = (int) $quote_original->accommodation->triple;
                $QA->triple_child = (int) $quote_original->accommodation->triple_child;
            } else {

                // hay un bag aqui, no puede haber cotizaciones que no tengan registro en quote_accommodations
                $single_max = reset($single_max);
                $double_max = reset($double_max);
                $triple_max = reset($triple_max);

                $QA = new QuoteAccommodation();
                $QA->single = (int) $single_max;
                $QA->double = (int) $double_max;
                $QA->triple = (int) $triple_max;
                $QA->double_child = 0;
                $QA->triple_child = 0;
            }
            $quote->setRelation('accommodation', $QA);

            $quote_log_editing_quote = DB::table('quote_logs')->where('quote_id', $quote_original->id)
                ->where('type', 'editing_quote')->where('object_id', $quote_id)->first();

            $quote_log_created_editing_package = false;

            $quote_log_from_package_original = DB::table('quote_logs')->where(
                'quote_id',
                $quote_original->id
            )->where('type', 'editing_package')->first();

            $quote_log_from_package = DB::table('quote_logs')->where('quote_id', $quote_id)
                ->where('type', 'editing_package')->first();

            if ($quote_log_from_package == null && $quote_log_from_package_original != null) {
                $quote_log_created_editing_package = true;

                DB::table('quote_logs')->insert([
                    'quote_id'   => $quote_id,
                    'type'       => 'editing_package',
                    'object_id'  => $quote_log_from_package['object_id'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            if (! $quote_log_created_editing_package && $quote_log_from_package_original != null) {
                DB::table('quote_logs')->where('quote_id', $quote_id)->where('type', 'editing_package')->update([
                    'object_id' => $quote_log_from_package['object_id'],
                ]);
            }

            if ($quote_log_editing_quote == null) {
                DB::table('quote_logs')->insert([
                    'quote_id'   => $quote_id,
                    'type'       => 'editing_quote',
                    'object_id'  => $quote_original->id,
                    'user_id'    => Auth::id(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            //Todo Guardamos la cotizacion
            $quote->save();
            //Todo guardamos las categorias
            $quote->categories()->saveMany($quote->categories);
            //Todo Guaramos los rangos
            if ($quote->ranges->count() > 0) {
                $quote->ranges()->saveMany($quote->ranges);
            }

            //Todo Guardamos las edades de niños
            $quote->age_child()->saveMany($quote->age_child);

            //Todo Guardamos los pasajeros
            if ($quote->passengers->count() > 0) {

                foreach ($quote->passengers as $id => $passanger) {
                    if ($passanger->type == 'CHD') {
                        foreach ($quote->age_child as $age_child) {
                            if ($passanger->quote_age_child_id == $age_child->quote_age_child_id) {
                                $quote->passengers[$id]->quote_age_child_id = $age_child->id;
                            }
                        }
                    }
                }

                $quote->passengers()->saveMany($quote->passengers);
            }

            $quote->distributions()->saveMany($quote->distributions);

            foreach ($quote->distributions as $distribution) {

                foreach ($distribution->passengers as $id => $passenger) {

                    foreach ($quote->passengers as $quote_passenger) {
                        if ($passenger->quote_passenger_id == $quote_passenger->quote_passenger_id) {
                            $distribution->passengers[$id]->quote_passenger_id = $quote_passenger->id;
                            break;
                        }
                    }
                }
                $distribution->passengers()->saveMany($distribution->passengers);
            }

            //Todo Guardamos la acomodacion
            $quote->accommodation()->save($quote->accommodation);

            //Todo Guardamos la cantidad adultos + niños
            $quote->people()->saveMany($quote->people);

            //Todo Guardamos las notas
            $quote->notes()->saveMany($quote->notes);
            //Todo Guardamos los logs de la cotizacion
            $quote->logs()->saveMany($quote->logs);
            //Todo Recorremos los servicios y sus tarifas y guardamos

            foreach ($quote->categories as $category) {
                //Todo Guardamos los servicios/hoteles de la categoria
                $category->services()->saveMany($category->services);
                foreach ($category->services as $service) {
                    //Todo Guardamos las tarifas de los servicios
                    if ($service->amount->count() > 0) {
                        $service->amount()->saveMany($service->amount);
                    }
                    if ($service->type == 'service') {
                        if (isset($service->service_rate)) {
                            $service->service_rate()->save($service->service_rate);
                        }
                    }
                    //Todo Guardamos las tarifas de las habitaciones
                    if ($service->type == 'hotel') {
                        $service->service_rooms()->saveMany($service->service_rooms);
                        $service->service_rooms_hyperguest()->saveMany($service->service_rooms_hyperguest);
                    }
                }
            }

            //Todo Si en la cotizacion hay extensiones agregamos a la cotizacion editada
            $this->updateParentIdHasExtensions($quote->categories);
            if ($quote_original->operation == 'passengers') {
                $this->createOrUpdateServicePassengers(
                    $quote_id,
                    $quote_original->id,
                    $quote->categories,
                    $quote->passengers
                );
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function getLogs($quote_id): \Illuminate\Support\Collection
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
                if (! $_logs_copy_self) {
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
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * @param $categories Collection
     */
    public function updateParentIdHasExtensions(Collection $categories): void
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
                                'parent_service_id' => $parent_extension->id,
                            ]);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param  bool  $withRelationShip | True => Trae la cotizacion con sus relaciones
     * @param  bool  $servicesLooked | True => Trae todos los servicios asi estan bloqueados
     */
    public function getQuote($quote_id, bool $withRelationShip = false, bool $servicesLooked = false): mixed
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
                                'new_extension_id',
                                'parent_service_id',
                                'optional',
                                'code_flight',
                                'origin',
                                'destiny',
                                'date_flight',
                                'notes',
                                'schedule_id',
                                'is_file',
                                'file_itinerary_id',
                                'file_status',
                                'file_amount_sale',
                                'file_amount_cost',
                                'hyperguest_pull',
                                'markup_regionalization'
                            ]);
                            if ($servicesLooked) {
                                $query->where('locked', 0);
                            }
                            $query->orderBy('date_in');
                            $query->orderBy('order');
                            $query->with([
                                'passengers' => function ($query) {
                                    $query->with('passenger');
                                    $query->select(['id', 'quote_service_id', 'quote_passenger_id']);
                                },
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
                                        'price_child',
                                    ]);
                                },
                            ]);
                        },
                    ]);
                },
            ])
                ->with([
                    'people' => function ($query) {
                        $query->select(['id', 'adults', 'child', 'quote_id']);
                    },
                ])
                ->with([
                    'age_child' => function ($query) {
                        $query->select(['id', 'age', 'quote_id']);
                    },
                ])
                ->with([
                    'notes' => function ($query) {
                        $query->select(['id', 'parent_note_id', 'comment', 'status', 'quote_id', 'user_id']);
                    },
                ])
                ->with([
                    'ranges' => function ($query) {
                        $query->select(['id', 'from', 'to', 'quote_id']);
                    },
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
                            'triple_child',
                        ]);
                    },
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
                            'quote_age_child_id',
                        ]);
                    },
                ]);
        }

        return $quote->first([
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
            'package_id',
            'reference_code',
            'file_id',
            'file_number',
            'file_total_amount'

        ]);
    }

    public function getEditQuote($quote_id)
    {
        return DB::table('quote_logs')->where('quote_id', $quote_id)
            ->where('type', 'editing_quote')->orderBy('created_at', 'desc')
            ->first();
    }

    public function createOrUpdateServicePassengers($quote_id, $quote_original, $categories, $passengers): void
    {
        $quote_passengers_draft = QuotePassenger::where('quote_id', $quote_original)->orderBy('id')->get(['id']);
        $quote_passengers_original = QuotePassenger::where(
            'quote_id',
            $quote_id
        )->orderBy('id')->get(['id'])->toArray();
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
                        $key_pax = array_search(
                            $passenger->quote_passenger_id,
                            $quote_passengers_draft->pluck('id')->toArray()
                        );
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

    public function copyServicePassengersFromQuote($quote_id, $quote_original): void
    {
        $quote_passengers_draft = QuotePassenger::where('quote_id', $quote_id)->orderBy('id')->get(['id']);
        $quote_passengers_original = QuotePassenger::where(
            'quote_id',
            $quote_original
        )->orderBy('id')->get(['id'])->toArray();

        $categories = QuoteCategory::where('quote_id', $quote_original)
            ->with([
                'services' => function ($query) {
                    $query->select(['id', 'quote_category_id']);
                    $query->with([
                        'passengers' => function ($query) {
                            $query->select(['id', 'quote_service_id', 'quote_passenger_id']);
                        },
                    ]);
                },
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
                        $key_pax = array_search(
                            $passenger->quote_passenger_id,
                            $quote_passengers_draft->pluck('id')->toArray()
                        );
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

    public function assignPassengerToService($service, $quote_passengers, $quote_adult_general, $quote_child_general): void
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

                $passengers_not_assigned = $quote_passengers_adults->whereNotIn(
                    'id',
                    $passenger_ignored->pluck('quote_passenger_id')
                );
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

                $passengers_not_assigned = $quote_passengers_children->whereNotIn(
                    'id',
                    $passenger_ignored->pluck('quote_passenger_id')
                );
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

    /**
     * Permite asignar pasajeros a los servicios
     */
    public function updateAssignPassengerService($quote_id, $quote_service_id, $adult, $child): void
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
                        },
                    ]);
                },
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
                'locked',
            ]);

        foreach ($quote_service as $service) {
            $this->assignPassengerToService($service, $quote_passengers, $adult, $child);
        }
    }

    public function getQuantityAndEnableRoomsByTypeRoom($quote_category_id, $hotel_id, $date_in, $nights, $occupation): int
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

                if (!$hotel->hyperguest_pull) {
                    $service_room = DB::table('quote_service_rooms')->select('rate_plan_room_id')->where(
                        'quote_service_id',
                        $hotel->id
                    )->first();
                    $rate_plan_room = DB::table('rates_plans_rooms')->select('room_id')->where(
                        'id',
                        $service_room->rate_plan_room_id
                    )->first();
                    $room = DB::table('rooms')->select('room_type_id')->where('id', $rate_plan_room->room_id)->first();
                    $room_type = DB::table('room_types')->select('occupation')->where('id', $room->room_type_id)->first();

                    if ($room_type->occupation == $occupation) {
                        $quantity_hotels_disabled_find->add($hotel);
                        $quantity_hotels_disabled = 1;
                    }
                } else {

                    $service_room = DB::table('quote_service_rooms_hyperguest')->select('room_id')->where(
                        'quote_service_id',
                        $hotel->id
                    )->first();
                    $room = DB::table('rooms')->select('room_type_id')->where('id', $service_room->room_id)->first();
                    $room_type = DB::table('room_types')->select('occupation')->where('id', $room->room_type_id)->first();

                    if ($room_type->occupation == $occupation) {
                        $quantity_hotels_disabled_find->add($hotel);
                        $quantity_hotels_disabled = 1;
                    }
                }
            }
        }

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
     */
    public function getQuantityOrQueryRoomsByTypeRoom(
        $quote_category_id,
        $hotel_id,
        $date_in,
        $nights,
        $occupation,
        bool $count_rows = true
    ): int|\Illuminate\Support\Collection {

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
     * funcion permite eliminar las habitaciones que sen encuentran sin utilizar
     */
    public function destroyRoomsDisabledByOccupationsAddFromHeader($quote_service, $occupation): void
    {
        $hotels_rooms_disabled = DB::table('quote_services')->where(
            'quote_category_id',
            $quote_service->quote_category_id
        )
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

            if (!$hotel->hyperguest_pull) {

                $service_room = DB::table('quote_service_rooms')->select('rate_plan_room_id')->where(
                    'quote_service_id',
                    $hotel->id
                )->first();

                $rate_plan_room = DB::table('rates_plans_rooms')->select('room_id')->where(
                    'id',
                    $service_room->rate_plan_room_id
                )->first();
            } else {

                $rate_plan_room = DB::table('quote_service_rooms_hyperguest')->select('room_id')->where(
                    'quote_service_id',
                    $hotel->id
                )->first();
            }


            $room = DB::table('rooms')->select('room_type_id')->where(
                'id',
                $rate_plan_room->room_id
            )->first();

            $room_type = DB::table('room_types')->select('occupation')->where(
                'id',
                $room->room_type_id
            )->first();

            if ($room_type->occupation == $occupation) {
                $delete = QuoteService::find($hotel->id);
                $delete->delete();
            }
        }
    }


    /**
     * Funcion permite asignar los pasajeros de un hotel en especifico
     */
    public function updateListPassengersRoomsHotel(
        $quote_service_id,
        $quote_id
    ): void {
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
                            },
                        ]);
                    },
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
                    'locked',
                ]);

            $hotels_groups = collect();
            foreach ($quote_services as $service) {
                $total_accommodations = (int) $service->single + (int) $service->double + (int) $service->triple + (int) $service->double_child + (int) $service->triple_child;
                if ($total_accommodations > 0) {
                    $hotels_groups->add($service);
                }
            }

            if ($hotels_groups->count() > 0) {
                $hotels_groups = $hotels_groups->groupBy(function ($item, $key) {
                    $locked = ($item['locked']) ? 1 : 0;
                    $date = convertDate($item['date_in'], '/', '-', 1);

                    return $date . '|' . $item['nights'] . '|' . $item['object_id'] . '|' . $locked;
                });
            }
        }
    }

    /**
     * Funcion permite agregar habitaciones segun el tablero
     */
    public function addRoomsToHotel(
        $occupation,
        $quote_service,
        $rate_plan_room_id,
        $simple,
        $double,
        $triple,
        $double_child,
        $triple_child,
        $rate_plan_id,
        $room_id
    ): void {
        //Todo Simple
        if ($occupation == 1) {
            //Todo Busco la cantidad de habitaciones en simples
            $quantity_hotels = $this->getQuantityOrQueryRoomsByTypeRoom(
                $quote_service->quote_category_id,
                $quote_service->object_id,
                $quote_service->date_in,
                $quote_service->nights,
                $occupation
            );
            if ((int) $simple > $quantity_hotels) {
                //Todo busco la cantidad de habitaciones simples deshabilitadas
                $qtyDisabledRoom = $this->getQuantityAndEnableRoomsByTypeRoom(
                    $quote_service->quote_category_id,
                    $quote_service->object_id,
                    $quote_service->date_in,
                    $quote_service->nights,
                    $occupation
                );
                $total_rooms_created = (int) $simple - ($quantity_hotels + $qtyDisabledRoom);
                if ($total_rooms_created > 0) {
                    // Obtener el markup_regionalization del servicio original o desde la categoría
                    $markup_regionalization = $quote_service->markup_regionalization ?? DB::table('quote_categories')
                        ->join('quotes', 'quote_categories.quote_id', '=', 'quotes.id')
                        ->where('quote_categories.id', $quote_service->quote_category_id)
                        ->value('quotes.markup') ?? 0;

                    for ($i = 0; $i < $total_rooms_created; $i++) {
                        $quote_service_id = DB::table('quote_services')->insertGetId([
                            'quote_category_id' => $quote_service->quote_category_id,
                            'type'              => 'hotel',
                            'object_id'         => $quote_service->object_id,
                            'order'             => $quote_service->order,
                            'date_in'           => $quote_service->date_in,
                            'date_out'          => $quote_service->date_out,
                            'nights'            => $quote_service->nights,
                            'adult'             => 1,
                            'child'             => 0,
                            'infant'            => 0,
                            'single'            => 1,
                            'double'            => 0,
                            'double_child'      => 0,
                            'triple'            => 0,
                            'triple_child'      => 0,
                            'triple_active'     => 0,
                            'locked'            => $quote_service->locked,
                            'created_at'        => $quote_service->created_at,
                            'updated_at'        => $quote_service->updated_at,
                            'on_request'        => $quote_service->on_request,
                            'optional'          => $quote_service->optional,
                            'hyperguest_pull'   => $quote_service->hyperguest_pull,
                            'markup_regionalization' => $markup_regionalization
                        ]);
                        if (!$quote_service->hyperguest_pull) {
                            DB::table('quote_service_rooms')->insert([
                                'quote_service_id'  => $quote_service_id,
                                'rate_plan_room_id' => $rate_plan_room_id,
                                'created_at'        => Carbon::now(),
                                'updated_at'        => Carbon::now(),
                            ]);
                        } else {
                            DB::table('quote_service_rooms_hyperguest')->insert([
                                'quote_service_id'  => $quote_service_id,
                                'rate_plan_id' => $rate_plan_id,
                                'room_id' => $room_id,
                                'created_at'        => Carbon::now(),
                                'updated_at'        => Carbon::now(),
                            ]);
                        }
                    }
                }
            }
            if ((int) $simple < $quantity_hotels && $simple > 0) {
                $quantity_hotels_deleted = $quantity_hotels - $simple;
                $quote_services_deleted = $this->getQuantityOrQueryRoomsByTypeRoom(
                    $quote_service->quote_category_id,
                    $quote_service->object_id,
                    $quote_service->date_in,
                    $quote_service->nights,
                    $occupation,
                    false
                );
                foreach ($quote_services_deleted as $quote_service) {
                    if ($quantity_hotels_deleted > 0) {
                        DB::table('quote_service_passengers')->where(
                            'quote_service_id',
                            $quote_service->id
                        )->delete();

                        if (!$quote_service->hyperguest_pull) {
                            DB::table('quote_service_rooms')->where(
                                'quote_service_id',
                                $quote_service->id
                            )->delete();
                        } else {
                            DB::table('quote_service_rooms_hyperguest')->where(
                                'quote_service_id',
                                $quote_service->id
                            )->delete();
                        }

                        DB::table('quote_services')->where('id', $quote_service->id)->delete();
                        $quantity_hotels_deleted--;
                    }
                }
            }

            if ((int) $simple == 0) {
                $quote_services_deleted = $this->getQuantityOrQueryRoomsByTypeRoom(
                    $quote_service->quote_category_id,
                    $quote_service->object_id,
                    $quote_service->date_in,
                    $quote_service->nights,
                    $occupation,
                    false
                );
                foreach ($quote_services_deleted as $key => $quote_service) {
                    if ($key == 0) {
                        DB::table('quote_services')->where('id', $quote_service->id)->update([
                            'single'       => 0,
                            'double'       => 0,
                            'triple'       => 0,
                            'double_child' => 0,
                            'triple_child' => 0,
                        ]);
                    } else {
                        DB::table('quote_service_passengers')->where(
                            'quote_service_id',
                            $quote_service->id
                        )->delete();

                        if (!$quote_service->hyperguest_pull) {
                            DB::table('quote_service_rooms')->where(
                                'quote_service_id',
                                $quote_service->id
                            )->delete();
                        } else {
                            DB::table('quote_service_rooms_hyperguest')->where(
                                'quote_service_id',
                                $quote_service->id
                            )->delete();
                        }

                        DB::table('quote_services')->where('id', $quote_service->id)->delete();
                    }
                }
            }
        }
        //Todo Doble
        if ($occupation == 2) {
            //Todo Busco la cantidad de habitaciones en doble
            $quantity_hotels = $this->getQuantityOrQueryRoomsByTypeRoom(
                $quote_service->quote_category_id,
                $quote_service->object_id,
                $quote_service->date_in,
                $quote_service->nights,
                $occupation
            );
            if ((int) $double > $quantity_hotels) {
                //Todo busco la cantidad de habitaciones dobles deshabilitadas
                $qtyDisabledRoom = $this->getQuantityAndEnableRoomsByTypeRoom(
                    $quote_service->quote_category_id,
                    $quote_service->object_id,
                    $quote_service->date_in,
                    $quote_service->nights,
                    $occupation
                );
                $total_rooms_created = (int) $double - ($quantity_hotels + $qtyDisabledRoom);
                if ($total_rooms_created > 0) {
                    // Obtener el markup_regionalization del servicio original o desde la categoría
                    $markup_regionalization = $quote_service->markup_regionalization ?? DB::table('quote_categories')
                        ->join('quotes', 'quote_categories.quote_id', '=', 'quotes.id')
                        ->where('quote_categories.id', $quote_service->quote_category_id)
                        ->value('quotes.markup') ?? 0;

                    for ($i = 0; $i < $total_rooms_created; $i++) {
                        $quote_service_id = DB::table('quote_services')->insertGetId([
                            'quote_category_id' => $quote_service->quote_category_id,
                            'type'              => 'hotel',
                            'object_id'         => $quote_service->object_id,
                            'order'             => $quote_service->order,
                            'date_in'           => $quote_service->date_in,
                            'date_out'          => $quote_service->date_out,
                            'nights'            => $quote_service->nights,
                            'adult'             => 2,
                            'child'             => 0,
                            'infant'            => 0,
                            'single'            => 0,
                            'double'            => 1,
                            'double_child'      => $double_child,
                            'triple'            => 0,
                            'triple_child'      => 0,
                            'triple_active'     => 0,
                            'locked'            => $quote_service->locked,
                            'created_at'        => $quote_service->created_at,
                            'updated_at'        => $quote_service->updated_at,
                            'on_request'        => $quote_service->on_request,
                            'optional'          => $quote_service->optional,
                            'hyperguest_pull'   => $quote_service->hyperguest_pull,
                            'markup_regionalization' => $markup_regionalization
                        ]);

                        if (!$quote_service->hyperguest_pull) {
                            DB::table('quote_service_rooms')->insert([
                                'quote_service_id'  => $quote_service_id,
                                'rate_plan_room_id' => $rate_plan_room_id,
                                'created_at'        => Carbon::now(),
                                'updated_at'        => Carbon::now(),
                            ]);
                        } else {
                            DB::table('quote_service_rooms_hyperguest')->insert([
                                'quote_service_id'  => $quote_service_id,
                                'rate_plan_id' => $rate_plan_id,
                                'room_id' => $room_id,
                                'created_at'        => Carbon::now(),
                                'updated_at'        => Carbon::now(),
                            ]);
                        }
                    }
                }
            }

            if ($double < $quantity_hotels && $double > 0) {
                $quantity_hotels_deleted = $quantity_hotels - $double;
                $quote_services_deleted = $this->getQuantityOrQueryRoomsByTypeRoom(
                    $quote_service->quote_category_id,
                    $quote_service->object_id,
                    $quote_service->date_in,
                    $quote_service->nights,
                    $occupation,
                    false
                );
                foreach ($quote_services_deleted as $quote_service) {
                    if ($quantity_hotels_deleted > 0) {
                        DB::table('quote_service_passengers')->where(
                            'quote_service_id',
                            $quote_service->id
                        )->delete();

                        if (!$quote_service->hyperguest_pull) {
                            DB::table('quote_service_rooms')->where(
                                'quote_service_id',
                                $quote_service->id
                            )->delete();
                        } else {
                            DB::table('quote_service_rooms_hyperguest')->where(
                                'quote_service_id',
                                $quote_service->id
                            )->delete();
                        }

                        DB::table('quote_services')->where('id', $quote_service->id)->delete();
                        $quantity_hotels_deleted--;
                    }
                }
            }

            if ($double == 0) {
                $quote_services_deleted = $this->getQuantityOrQueryRoomsByTypeRoom(
                    $quote_service->quote_category_id,
                    $quote_service->object_id,
                    $quote_service->date_in,
                    $quote_service->nights,
                    $occupation,
                    false
                );
                foreach ($quote_services_deleted as $key => $quote_service) {
                    if ($key == 0) {
                        DB::table('quote_services')->where('id', $quote_service->id)->update([
                            'single'       => 0,
                            'double'       => 0,
                            'triple'       => 0,
                            'double_child' => 0,
                            'triple_child' => 0,
                        ]);
                    } else {
                        $QSP = QuoteServicePassenger::where('quote_service_id', $quote_service->id)->get(['id']);
                        QuoteServicePassenger::destroy($QSP->toArray());

                        if (!$quote_service->hyperguest_pull) {
                            $QSR = QuoteServiceRoom::where('quote_service_id', $quote_service->id)->get(['id']);
                            QuoteServiceRoom::destroy($QSR->toArray());
                        } else {
                            $QSR = QuoteServiceRoomHyperguest::where('quote_service_id', $quote_service->id)->get(['id']);
                            QuoteServiceRoomHyperguest::destroy($QSR->toArray());
                        }


                        $QS = QuoteService::find($quote_service->id);
                        $QS->delete();
                    }
                }
            }
        }
        //Todo Triple
        if ($occupation == 3) {
            //Todo Busco la cantidad de habitaciones en triples
            $quantity_hotels = $this->getQuantityOrQueryRoomsByTypeRoom(
                $quote_service->quote_category_id,
                $quote_service->object_id,
                $quote_service->date_in,
                $quote_service->nights,
                $occupation
            );
            if ((int) $triple > $quantity_hotels) {
                //Todo busco la cantidad de habitaciones triples deshabilitadas
                $qtyDisabledRoom = $this->getQuantityAndEnableRoomsByTypeRoom(
                    $quote_service->quote_category_id,
                    $quote_service->object_id,
                    $quote_service->date_in,
                    $quote_service->nights,
                    $occupation
                );
                $total_rooms_created = (int) $triple - ($quantity_hotels + $qtyDisabledRoom);
                if ($total_rooms_created > 0) {
                    // Obtener el markup_regionalization del servicio original o desde la categoría
                    $markup_regionalization = $quote_service->markup_regionalization ?? DB::table('quote_categories')
                        ->join('quotes', 'quote_categories.quote_id', '=', 'quotes.id')
                        ->where('quote_categories.id', $quote_service->quote_category_id)
                        ->value('quotes.markup') ?? 0;

                    for ($i = 0; $i < $total_rooms_created; $i++) {
                        $quote_service_id = DB::table('quote_services')->insertGetId([
                            'quote_category_id' => $quote_service->quote_category_id,
                            'type'              => 'hotel',
                            'object_id'         => $quote_service->object_id,
                            'order'             => $quote_service->order,
                            'date_in'           => $quote_service->date_in,
                            'date_out'          => $quote_service->date_out,
                            'nights'            => $quote_service->nights,
                            'adult'             => 3, // $quote_service->adult,
                            'child'             => 0, // $quote_service->child,
                            'infant'            => $quote_service->infant,
                            'single'            => 0,
                            'double'            => 0,
                            'double_child'      => 0,
                            'triple'            => 1,
                            'triple_child'      => $triple_child,
                            'triple_active'     => $quote_service->triple_active,
                            'locked'            => $quote_service->locked,
                            'created_at'        => $quote_service->created_at,
                            'updated_at'        => $quote_service->updated_at,
                            'on_request'        => $quote_service->on_request,
                            'optional'          => $quote_service->optional,
                            'hyperguest_pull'   => $quote_service->hyperguest_pull,
                            'markup_regionalization' => $markup_regionalization
                        ]);

                        if (!$quote_service->hyperguest_pull) {
                            DB::table('quote_service_rooms')->insert([
                                'quote_service_id'  => $quote_service_id,
                                'rate_plan_room_id' => $rate_plan_room_id,
                                'created_at'        => Carbon::now(),
                                'updated_at'        => Carbon::now(),
                            ]);
                        } else {
                            DB::table('quote_service_rooms_hyperguest')->insert([
                                'quote_service_id'  => $quote_service_id,
                                'rate_plan_id' => $rate_plan_id,
                                'room_id' => $room_id,
                                'created_at'        => Carbon::now(),
                                'updated_at'        => Carbon::now(),
                            ]);
                        }
                    }
                }
            }
            if ($triple < $quantity_hotels && $triple > 0) {
                $quantity_hotels_deleted = $quantity_hotels - $triple;

                $quote_services_deleted = $this->getQuantityOrQueryRoomsByTypeRoom(
                    $quote_service->quote_category_id,
                    $quote_service->object_id,
                    $quote_service->date_in,
                    $quote_service->nights,
                    $occupation,
                    false
                );

                foreach ($quote_services_deleted as $quote_service) {
                    if ($quantity_hotels_deleted > 0) {
                        $QSP = QuoteServicePassenger::where('quote_service_id', $quote_service->id)->get(['id']);
                        QuoteServicePassenger::destroy($QSP->toArray());

                        if (!$quote_service->hyperguest_pull) {
                            $QSR = QuoteServiceRoom::where('quote_service_id', $quote_service->id)->get(['id']);
                            QuoteServiceRoom::destroy($QSR->toArray());
                        } else {
                            $QSR = QuoteServiceRoomHyperguest::where('quote_service_id', $quote_service->id)->get(['id']);
                            QuoteServiceRoomHyperguest::destroy($QSR->toArray());
                        }


                        $QS = QuoteService::find($quote_service->id);
                        $QS->delete();
                        $quantity_hotels_deleted--;
                    }
                }
            }
            if ($triple == 0) {
                $quote_services_deleted = $this->getQuantityOrQueryRoomsByTypeRoom(
                    $quote_service->quote_category_id,
                    $quote_service->object_id,
                    $quote_service->date_in,
                    $quote_service->nights,
                    $occupation,
                    false
                );

                foreach ($quote_services_deleted as $key => $quote_service) {
                    if ($key == 0) {
                        DB::table('quote_services')->where('id', $quote_service->id)->update([
                            'single'       => 0,
                            'double'       => 0,
                            'triple'       => 0,
                            'double_child' => 0,
                            'triple_child' => 0,
                        ]);
                    } else {
                        $QSP = QuoteServicePassenger::where('quote_service_id', $quote_service->id)->get(['id']);
                        QuoteServicePassenger::destroy($QSP->toArray());

                        if (!$quote_service->hyperguest_pull) {
                            $QSR = QuoteServiceRoom::where('quote_service_id', $quote_service->id)->get(['id']);
                            QuoteServiceRoom::destroy($QSR->toArray());
                        } else {
                            $QSR = QuoteServiceRoomHyperguest::where('quote_service_id', $quote_service->id)->get(['id']);
                            QuoteServiceRoomHyperguest::destroy($QSR->toArray());
                        }

                        $QS = QuoteService::find($quote_service->id);
                        $QS->delete();
                    }
                }
            }
        }
    }

    public function setAccommodationInHotels($quote_categories, $adults, $children, $quoteDistributions): void
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
                    $total_accommodations = (int) $quote_service->single + (int) $quote_service->double + (int) $quote_service->triple + (int) $quote_service->double_child + (int) $quote_service->triple_child;
                    if ($total_accommodations > 0) {
                        $hotels_collection->add($quote_service);
                    }
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
                    return str_replace(
                        '-',
                        '',
                        $service->date_in
                    ) . '_' . $service->nights . '_' . $service->object_id . '_' . $service->locked;
                });

                foreach ($hotels_collection as $hotel_group) {

                    $order = $this->updatePaxAcomodacion($hotel_group, $adults, $children, $order, $quoteDistributions);
                }
            }
        }
    }

    public function updatePaxAcomodacion($hotel_group, $adults, $children, $order, $quoteDistributions): int
    {

        $hotel_group = $hotel_group->transform(function ($item) {
            $item->check = false;

            return $item;
        });

        $hotel_group = $hotel_group->sortBy('occupation');

        foreach ($quoteDistributions as $distribution) {

            foreach ($hotel_group as $index => $hotel) {

                if ($hotel->rate_plan_room_id or $hotel->hyperguest_pull == 1) {

                    if ($distribution->single == $hotel->single and $distribution->double == $hotel->double and $distribution->triple == $hotel->triple) {

                        if ($hotel->check) {
                            continue;
                        }

                        DB::table('quote_services')->where('id', $hotel->id)->update([
                            'adult' => $distribution->adult,
                            'child' => $distribution->child,
                            'order' => $order,
                        ]);
                        $order++;
                        $hotel->check = $distribution->passengers->toArray();
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


        return $order;
    }

    public function updatePaxAcomodacionGeneral($hotel_group, $adults, $children, $order): array
    {

        $totalPaxAdultoTomado = 0;
        $totalPaxChildrenTomado = 0;
        $allAduls = false;
        $x = 1;
        foreach ($hotel_group as $px => $hotel) {
            $adult = 0;
            $child = 0;

            if (! $allAduls) {

                if ($hotel['type_room'] == 'single') {

                    if (count($hotel_group) == $x) { // es el ultimo registro
                        $adult = $adults - $totalPaxAdultoTomado;
                        $child = $children - $totalPaxChildrenTomado;
                    } else {

                        if (($totalPaxAdultoTomado + 1) <= $adults) {
                            $totalPaxAdultoTomado = $totalPaxAdultoTomado + 1;
                            $adult = 1;
                        } else {

                            $adult = $adults - $totalPaxAdultoTomado;
                            if ($children > 0) {

                                $childTomados = 1 - $adult;
                                $child = min($childTomados, $children);
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
                        } else {

                            $adult = $adults - $totalPaxAdultoTomado;
                            $child = 0;
                            if ($children > 0) {

                                $childTomados = 2 - $adult;
                                $child = min($childTomados, $children);
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
                        } else {

                            $adult = $adults - $totalPaxAdultoTomado;
                            if ($children > 0) {

                                $childTomados = 3 - $adult;
                                $child = min($childTomados, $children);
                            }
                            $totalPaxChildrenTomado = $child;
                            $allAduls = true;
                        }
                    }
                }
            } else {

                if ($hotel['type_room'] == 'single') {
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
    ): void {

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
            $total_accommodations = (int) $quote_service->single + (int) $quote_service->double + (int) $quote_service->triple + (int) $quote_service->double_child + (int) $quote_service->triple_child;
            if ($total_accommodations > 0) {
                $hotels_collection->add($quote_service);
            }
        }
        if ($hotels_collection->count() > 0) {

            $hotels_collection = $hotels_collection->groupBy(function ($service) {
                return str_replace(
                    '-',
                    '',
                    $service->date_in
                ) . '_' . $service->nights . '_' . $service->object_id . '_' . $service->locked;
            });

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
    ): void {

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
            $total_accommodations = (int) $quote_service->single + (int) $quote_service->double + (int) $quote_service->triple + (int) $quote_service->double_child + (int) $quote_service->triple_child;
            if ($total_accommodations > 0) {
                $hotels_collection->add($quote_service);
            }
        }
        if ($hotels_collection->count() > 0) {

            $hotels_collection = $hotels_collection->groupBy(function ($service) {
                return str_replace(
                    '-',
                    '',
                    $service->date_in
                ) . '_' . $service->nights . '_' . $service->object_id . '_' . $service->locked;
            });

            foreach ($hotels_collection as $hotel_group) {
                foreach ($hotel_group as $quote_services) {
                    if ($quote_services->rate_plan_room_id) // solo para tarifas de aurora o hyperguest push
                    {
                        $this->updateAmountServiceNewService($quote_services->id, $client_id);
                    }
                }
            }
        }
    }

    public function hasHotelRoom($s, $occupation): bool
    {
        $hasRoom = false;


        foreach ($s['service_rooms'] as $r) {
            $room = RatesPlansRooms::with('room.room_type')
                ->where('id', $r['rate_plan_room_id'])
                ->first();
            if ($room and $room['room']['room_type']['occupation'] == $occupation) {
                $hasRoom = true;
                break;
            }
        }

        foreach ($s['service_rooms_hyperguest'] as $r) {
            $room = Room::with('room_type')
                ->where('id', $r['room_id'])
                ->first();
            if ($room and $room['room_type']['occupation'] == $occupation) {
                $hasRoom = true;
                break;
            }
        }

        return $hasRoom;
    }

    private function calculatePriceHotelRoom($service, $passengers): array
    {
        foreach ($passengers as $index_passenger => $passenger) {
            $passengers[$index_passenger]['amount'] = 0;
        }

        foreach ($service['amount'] as $index_amount => $amount) {
            $service['amount'][$index_amount]['passengers'] = $passengers;
        }

        $ignore_rate_child = 0;

        if (!empty($service['service_rooms']) && isset($service['service_rooms'][0]['rate_plan_room']['channel_id'])) {
            $channel_id = $service['service_rooms'][0]['rate_plan_room']['channel_id'];
        } else {
            $channel_id = null;
        }

        if ($channel_id == 6) {
            $room_id = $service['service_rooms'][0]['rate_plan_room']['room']['id'];
            $ignore_rate_child = Room::where('id', $room_id)->value('ignore_rate_child');
        }

        $child = $service['child'];
        if ($service['single'] > 0) {
            $capacity = 1;
        } elseif ($service['double'] > 0) {
            $capacity = 2;
        } else {
            $capacity = 3;
        }

        $allows_child = $service['hotel']['allows_child'];
        $allows_teenagers = $service['hotel']['allows_teenagers'];

        $min_age_child = $service['hotel']['min_age_child'] ?: 0;
        $max_age_child = $service['hotel']['max_age_child'] ?: 0;
        $min_age_teenager = $service['hotel']['min_age_teenagers'] ?: 0;
        $max_age_teenager = $service['hotel']['max_age_teenagers'] ?: 0;

        $passengersDesc = collect($service['passengers'])->sortByDesc('age')->values();
        $service['passengers'] = $passengersDesc->toArray();

        $totalPax = count($service['passengers']);
        $totalAdult = min($capacity, $totalPax);
        $childrenAsNumAdults = 0;

        if ($child > 0) {
            $totalAdult = 0;

            foreach ($service['passengers'] as $index => $passenger) {
                if ($passenger['passenger']['type'] == 'CHD') {
                    $age = isset($passenger['passenger']['age_child']) ? $passenger['passenger']['age_child']['age'] : 1;

                    if ($allows_teenagers and ($age >= $min_age_teenager && $age <= $max_age_teenager)) {
                        $service['passengers'][$index]['type_pax'] = 'teenager';
                        $service['passengers'][$index]['type_pax_order'] = 3;
                        $service['passengers'][$index]['age'] = $age;
                    } elseif ($allows_child and ($age >= $min_age_child && $age <= $max_age_child)) {
                        if ($ignore_rate_child) {
                            $childrenAsNumAdults = 1;
                        } // Se agrega un slot de adulto para saltar este niño

                        $service['passengers'][$index]['type_pax'] = 'child';
                        $service['passengers'][$index]['type_pax_order'] = 2;
                        $service['passengers'][$index]['age'] = $age;
                    } else {
                        $service['passengers'][$index]['type_pax'] = 'adult';
                        $service['passengers'][$index]['type_pax_order'] = 1;
                        $service['passengers'][$index]['age'] = $age;
                        $totalAdult++;
                    }
                } else {
                    $service['passengers'][$index]['type_pax'] = 'adult';
                    $service['passengers'][$index]['type_pax_order'] = 1;
                    $service['passengers'][$index]['age'] = 28;
                    $totalAdult++;
                }
            }
        } else {
            foreach ($service['passengers'] as $index => $passenger) {
                $service['passengers'][$index]['type_pax'] = 'adult';
                $service['passengers'][$index]['type_pax_order'] = 1;
                $service['passengers'][$index]['age'] = 28;
            }
        }

        $totalAdult = $totalAdult ?: 1;
        $errorTarifa = false;

        foreach ($service['amount'] as $index => $amount) { // cantidad de dias reservados
            $service['amount'][$index]['passengers'] = collect($service['amount'][$index]['passengers'])->sortByDesc('age_child.age')->values()->toArray();
        }

        foreach ($service['amount'] as $index_amount => $amount) { // cantidad de dias reservados
            $precioAdulto = roundLito($amount['price_per_night'] / $totalAdult);
            $totalSeleccionado = 1;

            foreach ($service['passengers'] as $index => $passenger) {
                if ($passenger['type_pax'] == 'adult') {
                    $service['passengers'][$index]['price'] = $precioAdulto;
                } elseif ($passenger['type_pax'] == 'child') {
                    if ($ignore_rate_child && $childrenAsNumAdults) {
                        $service['passengers'][$index]['price'] = 0;
                        $childrenAsNumAdults = 0;
                    } else {
                        $service['passengers'][$index]['price'] = roundLito((float)$amount['price_child']);
                    }
                } elseif ($passenger['type_pax'] == 'teenager') {
                    $service['passengers'][$index]['price'] = roundLito((float)$amount['price_teenagers']);
                } else {
                    // debe de ser otro adulto y se pasa la capciadad de adultos permitos en la habitacion.
                    $service['passengers'][$index]['price'] = -1;
                    $errorTarifa = true;
                }
                $totalSeleccionado++;
            }

            foreach ($amount['passengers'] as $index_amount_passenger => $amount_passenger) {  // todos los pasajeros (5)
                foreach ($service['passengers'] as $passenger) {  // todos los pasajeros asignados al quote_service SGL, DBL, TPL  ejemplos (1 adulto) = 1 fila, (2 adultos) 2 filas, (1 adulto + 2 niños)3 filas
                    if ($amount_passenger['id'] == $passenger['passenger']['id']) {
                        $amount['passengers'][$index_amount_passenger]['amount'] = $passenger['price'];
                    }
                }
            }

            $service['amount'][$index_amount]['passengers'] = $amount['passengers'];
        }

        if ($errorTarifa) {
            $service['price'] = 'Error';
            $service['price_error'] = trans('validations.quotes.hotels.hotel_room_max_adults', ['max_adults' => $totalAdult]);
        } else {
            $priceRoom = 0;
            foreach ($service['amount'] as $amount) {
                foreach ($amount['passengers'] as $amount_passenger) {
                    $priceRoom = $priceRoom + $amount_passenger['amount'];
                }
            }
            $service['price'] = $priceRoom;
        }

        return $service;
    }

    private function calculatePriceHotelRoomHyperguestPull($service, $passengers): array
    {



        foreach ($service['passengers'] as $index => $passenger) {
            if ($passenger['passenger']['type'] == 'CHD') {
                $age = isset($passenger['passenger']['age_child']) ? $passenger['passenger']['age_child']['age'] : 1;
            }
        }

        return [];
        foreach ($passengers as $index_passenger => $passenger) {
            $passengers[$index_passenger]['amount'] = 0;
        }

        foreach ($service['amount'] as $index_amount => $amount) {
            $service['amount'][$index_amount]['passengers'] = $passengers;
        }

        $ignore_rate_child = 0;

        // if (!empty($service['service_rooms']) && isset($service['service_rooms'][0]['rate_plan_room']['channel_id'])) {
        //     $channel_id = $service['service_rooms'][0]['rate_plan_room']['channel_id'];
        // } else {
        //     $channel_id = null;
        // }

        // if ($channel_id == 6) {
        //     $room_id = $service['service_rooms'][0]['rate_plan_room']['room']['id'];
        //     $ignore_rate_child = Room::where('id', $room_id)->value('ignore_rate_child');
        // }

        $child = $service['child'];
        if ($service['single'] > 0) {
            $capacity = 1;
        } elseif ($service['double'] > 0) {
            $capacity = 2;
        } else {
            $capacity = 3;
        }

        $allows_child = $service['hotel']['allows_child'];
        $allows_teenagers = $service['hotel']['allows_teenagers'];

        $min_age_child = $service['hotel']['min_age_child'] ?: 0;
        $max_age_child = $service['hotel']['max_age_child'] ?: 0;
        $min_age_teenager = $service['hotel']['min_age_teenagers'] ?: 0;
        $max_age_teenager = $service['hotel']['max_age_teenagers'] ?: 0;

        $passengersDesc = collect($service['passengers'])->sortByDesc('age')->values();
        $service['passengers'] = $passengersDesc->toArray();

        $totalPax = count($service['passengers']);
        $totalAdult = min($capacity, $totalPax);
        $childrenAsNumAdults = 0;

        if ($child > 0) {
            $totalAdult = 0;

            foreach ($service['passengers'] as $index => $passenger) {
                if ($passenger['passenger']['type'] == 'CHD') {
                    $age = isset($passenger['passenger']['age_child']) ? $passenger['passenger']['age_child']['age'] : 1;

                    if ($allows_teenagers and ($age >= $min_age_teenager && $age <= $max_age_teenager)) {
                        $service['passengers'][$index]['type_pax'] = 'teenager';
                        $service['passengers'][$index]['type_pax_order'] = 3;
                        $service['passengers'][$index]['age'] = $age;
                    } elseif ($allows_child and ($age >= $min_age_child && $age <= $max_age_child)) {
                        if ($ignore_rate_child) {
                            $childrenAsNumAdults = 1;
                        } // Se agrega un slot de adulto para saltar este niño

                        $service['passengers'][$index]['type_pax'] = 'child';
                        $service['passengers'][$index]['type_pax_order'] = 2;
                        $service['passengers'][$index]['age'] = $age;
                    } else {
                        $service['passengers'][$index]['type_pax'] = 'adult';
                        $service['passengers'][$index]['type_pax_order'] = 1;
                        $service['passengers'][$index]['age'] = $age;
                        $totalAdult++;
                    }
                } else {
                    $service['passengers'][$index]['type_pax'] = 'adult';
                    $service['passengers'][$index]['type_pax_order'] = 1;
                    $service['passengers'][$index]['age'] = 28;
                    $totalAdult++;
                }
            }
        } else {
            foreach ($service['passengers'] as $index => $passenger) {
                $service['passengers'][$index]['type_pax'] = 'adult';
                $service['passengers'][$index]['type_pax_order'] = 1;
                $service['passengers'][$index]['age'] = 28;
            }
        }

        $totalAdult = $totalAdult ?: 1;
        $errorTarifa = false;

        foreach ($service['amount'] as $index => $amount) { // cantidad de dias reservados
            $service['amount'][$index]['passengers'] = collect($service['amount'][$index]['passengers'])->sortByDesc('age_child.age')->values()->toArray();
        }

        foreach ($service['amount'] as $index_amount => $amount) { // cantidad de dias reservados
            $precioAdulto = roundLito($amount['price_per_night'] / $totalAdult);
            $totalSeleccionado = 1;

            foreach ($service['passengers'] as $index => $passenger) {
                if ($passenger['type_pax'] == 'adult') {
                    $service['passengers'][$index]['price'] = $precioAdulto;
                } elseif ($passenger['type_pax'] == 'child') {
                    if ($ignore_rate_child && $childrenAsNumAdults) {
                        $service['passengers'][$index]['price'] = 0;
                        $childrenAsNumAdults = 0;
                    } else {
                        $service['passengers'][$index]['price'] = roundLito((float)$amount['price_child']);
                    }
                } elseif ($passenger['type_pax'] == 'teenager') {
                    $service['passengers'][$index]['price'] = roundLito((float)$amount['price_teenagers']);
                } else {
                    // debe de ser otro adulto y se pasa la capciadad de adultos permitos en la habitacion.
                    $service['passengers'][$index]['price'] = -1;
                    $errorTarifa = true;
                }
                $totalSeleccionado++;
            }

            foreach ($amount['passengers'] as $index_amount_passenger => $amount_passenger) {  // todos los pasajeros (5)
                foreach ($service['passengers'] as $passenger) {  // todos los pasajeros asignados al quote_service SGL, DBL, TPL  ejemplos (1 adulto) = 1 fila, (2 adultos) 2 filas, (1 adulto + 2 niños)3 filas
                    if ($amount_passenger['id'] == $passenger['passenger']['id']) {
                        $amount['passengers'][$index_amount_passenger]['amount'] = $passenger['price'];
                    }
                }
            }

            $service['amount'][$index_amount]['passengers'] = $amount['passengers'];
        }

        if ($errorTarifa) {
            $service['price'] = 'Error';
            $service['price_error'] = trans('validations.quotes.hotels.hotel_room_max_adults', ['max_adults' => $totalAdult]);
        } else {
            $priceRoom = 0;
            foreach ($service['amount'] as $amount) {
                foreach ($amount['passengers'] as $amount_passenger) {
                    $priceRoom = $priceRoom + $amount_passenger['amount'];
                }
            }
            $service['price'] = $priceRoom;
        }

        return $service;
    }

    private function search_hyperguest_pull($service, $client_id, $set_markup = NULL)
    {

        $params = $this->getParamsForSearch($service, $client_id, $set_markup);
        Log::info('hyperguest_request', [
            'url' => rtrim(config('services.aurora.domain'), '/') . '/services/hotels/available',
            'params' => $params
        ]);
        try {
            $response = $this->auroraService->searchHotelsHyperguest($params);
            $results = NULL;
            if (count($response) > 0 and isset($response[0]->city->hotels[0])) {
                $results = $response[0]->city->hotels[0];
            }
        } catch (\Exception $e) {
            Log::warning('hyperguest_error', [
                'message' => $e->getMessage(),
                'url' => rtrim(config('services.aurora.domain'), '/') . '/services/hotels/available',
                'params' => $params
            ]);
            $results = NULL;
        }
        return $results;
    }

    private function getParamsForSearch($service, $client_id, $set_markup = NULL)
    {
        //  dd($service);
        $hotel = Hotel::where('id', $service['object_id'])
            ->with(['country.translations' => function ($query) {
                $query->where('language_id', 1);
            }, 'state.translations' => function ($query) {
                $query->where('language_id', 1);
            }, 'city.translations' => function ($query) {
                $query->where('language_id', 1);
            }])
            ->first();

        $ages_child = [];
        $i = 1;
        foreach ($service['passengers'] as $index => $passenger) {
            if ($passenger['passenger']['type'] == 'CHD') {
                $age = isset($passenger['passenger']['age_child']) ? $passenger['passenger']['age_child']['age'] : 1;
                array_push($ages_child, [
                    'child' => $i,
                    'age' => $age
                ]);
                $i++;
            }
        }

        return [
            'client_id' => $client_id,
            'hotels_id' => [
                $hotel->id
            ],
            'hotels_search_code' => '',
            'lang' => 'en',
            'destiny' => [
                "code" => $hotel->country->iso . "," . $hotel->state->iso,
                "label" => $hotel->country->translations[0]->value . "," . $hotel->state->translations[0]->value,
            ],
            'date_from' => $service['date_in_format'],
            'date_to' => $service['date_out_format'],
            'typeclass_id' => 'all',
            'quantity_rooms' => 1,
            'quantity_persons_rooms' => [
                [
                    "room" => 1,
                    "adults" => $service['adult'],
                    "child" => $service['child'],
                    "ages_child" => $ages_child,
                ],
            ],
            'set_markup' => $set_markup, // se respeta el markup que colocaron en la cotización
            'zero_rates' => true
        ];
    }

    private function debugLog($type, $data)
    {
        DB::table('logs_debug')->insert([
            'type' => $type,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => now()
        ]);
    }

    private function pre_search_hyperguest_pull($quote, $client_id, $markup_quote)
    {
        $hotels_select = [];
        if ($quote) {
            if ($quote->operation == 'passengers') {
                foreach ($quote->categories as $category) {
                    foreach ($category->services as $quote_service) {
                        if ($quote_service->type == 'hotel') {
                            if ($quote_service->hyperguest_pull) {

                                $params = $this->getParamsForSearch($quote_service, $client_id, $markup_quote);
                                $params['quote_service_id'] = $quote_service->id;
                                $hotels_select[] = $params;
                            }
                        }
                    }
                }
            }
        }

        $url = config('services.aurora.domain') . "/services/hotels/available";
        $token = $this->auroraService->getAuthorization();

        return $this->searchHyperguestPool($hotels_select, $url, $token);
    }

    public function searchHyperguestPool($hotels_select, $url, $token)
    {
        $client = new Client([
            'timeout'         => 40,
            'connect_timeout' => 10,
        ]);

        $chunkSize   = 20;   // número de peticiones simultáneas
        $maxRetries  = 3;    // número máximo de reintentos
        $backoffMs   = 500;  // tiempo entre reintentos (ms)

        $result = [];

        foreach (array_chunk($hotels_select, $chunkSize) as $chunk) {

            $promises = [];

            foreach ($chunk as $item) {
                $id = $item['quote_service_id'];

                $requestFn = function () use ($client, $url, $item, $token) {
                    return $client->postAsync($url, [
                        'json' => $item,
                        'headers' => [
                            'Authorization' => $token,
                            'Accept'        => 'application/json'
                        ]
                    ]);
                };

                $promises[$id] = $this->retryAsync($requestFn, $maxRetries, $backoffMs);
            }

            $responses = Utils::settle($promises)->wait();

            foreach ($responses as $id => $response) {

                if ($response['state'] === 'fulfilled') {

                    $body    = (string) $response['value']['value']->getBody();
                    $status  = $response['value']['value']->getStatusCode();
                    $retries = $response['value']['retries_used'];

                    $log = [
                        'id'           => $id,
                        'status'       => 'ok',
                        'http_code'    => $status,
                        'response'     => $body,
                        'error'        => null,
                        'retries_used' => $retries,
                    ];
                } else {
                    $reasonData  = $response['reason'];
                    $retries     = $reasonData['retries_used'] ?? 0;

                    $errorMsg = '';
                    if (isset($reasonData['reason'])) {
                        $errorMsg = is_object($reasonData['reason'])
                            ? $reasonData['reason']->getMessage()
                            : json_encode($reasonData['reason']);
                    }

                    $log = [
                        'id'           => $id,
                        'status'       => 'failed',
                        'http_code'    => 0,
                        'response'     => '',
                        'error'        => $errorMsg,
                        'retries_used' => $retries,
                    ];
                }

                // Guardar cada log inmediatamente (como en tu código original)
                try {
                    // DB::table('log_tabla_quote')->insert(['log' => json_encode($log, JSON_UNESCAPED_UNICODE)]);
                } catch (\Exception $e) {
                    \Log::error("Error guardando log: " . $e->getMessage());
                }

                $result[$id] = $log;
            }
        }

        return $result;
    }

    /**
     * Función para ejecutar peticiones con reintentos y backoff.
     */
    private function retryAsync(callable $fn, $maxRetries, $backoffMs, $attempt = 0)
    {
        return $fn()->then(
            function ($value) use ($attempt) {
                return [
                    'value'        => $value,
                    'retries_used' => $attempt
                ];
            },
            function ($reason) use ($fn, $maxRetries, $backoffMs, $attempt) {

                if ($attempt >= $maxRetries) {
                    return \GuzzleHttp\Promise\Create::rejectionFor([
                        'reason'       => $reason,
                        'retries_used' => $attempt
                    ]);
                }

                if (strpos($reason->getMessage(), 'timed out') === false) {
                    return \GuzzleHttp\Promise\Create::rejectionFor([
                        'reason'       => $reason,
                        'retries_used' => $attempt
                    ]);
                }

                usleep($backoffMs * 1000);

                return $this->retryAsync($fn, $maxRetries, $backoffMs, $attempt + 1);
            }
        );
    }
}
