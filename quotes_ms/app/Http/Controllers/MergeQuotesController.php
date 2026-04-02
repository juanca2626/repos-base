<?php

namespace App\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Quote;
use App\Models\Client;
use App\Models\Package;
use App\Models\Service;
use App\Models\Language;
use App\Models\QuoteLog;
use App\Models\Inventory;
use App\Models\QuoteNote;
use App\Models\TypeClass;
use App\Enums\QuoteStatus;
use App\Models\QuoteMerge;
use App\Models\QuoteRange;
use App\Models\RatesPlans;
use App\Models\ShareQuote;
use App\Models\UserMarket;
use App\Http\Traits\Quotes;
use App\Models\HotelClient;
use App\Models\QuotePeople;
use App\Models\Reservation;
use App\Models\ServiceRate;
use App\Models\ServiceType;
use App\Models\ClientSeller;
use App\Models\Notification;
use App\Models\QuoteService;
use Illuminate\Http\Request;
use App\Models\PoliciesRates;
use App\Models\QuoteAgeChild;
use App\Models\QuoteCategory;
use App\Models\ServiceClient;
use App\Models\DateRangeHotel;
use App\Models\PackageService;
use App\Models\QuotePassenger;
use App\Models\PackagePlanRate;
use App\Models\QuoteHistoryLog;
use App\Models\RatesPlansRooms;
use App\Models\ServiceRatePlan;
use App\Mail\QuoteDiscountAlert;
use App\Models\QuoteDestination;
use App\Models\QuoteServiceRate;
use App\Models\QuoteServiceRoom;
use App\Models\ServiceInventory;
use App\Models\PhysicalIntensity;
use App\Models\QuoteDistribution;
use Illuminate\Http\JsonResponse;
use App\Http\Stella\StellaService;
use App\Models\PackageDestination;
use App\Models\PackageDynamicRate;
use App\Models\PackageServiceRate;
use App\Models\PackageServiceRoom;
use App\Models\PackageTranslation;
use App\Models\QuoteAccommodation;
use App\Models\QuoteServiceAmount;
use App\Models\ServiceDestination;
use App\Models\ServiceTranslation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\QuoteHistories;
use App\Models\RatePlanAssociation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Models\RatesPlansCalendarys;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationSharedQuote;
use App\Models\PackageRateSaleMarkup;
use App\Models\QuoteServicePassenger;
use App\Models\PackageDynamicRateCopy;
use App\Models\PackageDynamicSaleRate;
use App\Models\ServiceRateAssociation;
use App\Models\PackagePlanRateCategory;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\QuoteDistributionPassenger;
use App\Models\QuoteServiceRoomHyperguest;
use App\Http\Traits\QuoteDetailsPriceRange;
use App\Http\Traits\Package as TraitPackage;
use App\Http\Traits\QuoteDetailsPricePassengers;
use App\Http\Traits\CalculateCancellationPolicies as TraitCalculateCancellationPolicies;

class MergeQuotesController extends Controller
{
    use Quotes;
    use TraitPackage;
    use QuoteHistories;
    use TraitCalculateCancellationPolicies;
    use QuoteDetailsPricePassengers;
    use QuoteDetailsPriceRange;
    protected StellaService $stellaService;
    protected $authorization;

    public function __construct(Request $request )
    {
    }

    public function merge(Request $request)
    {
        // try
        // {

            $to_status = 1;
            $user_id = Auth::user()->id;
            $quote_merges = ($request->input('quotes')) ? $request->input('quotes') : [];
            $client_id = $this->getClientId($request);

            if (count($quote_merges) == 0) {
                throw new \Exception("There are no quotes to join");
            }

            // $quoteMergeIds = array_values(array_diff($quote_merges, [$quote_id]));
            // $all_quote_ids = array_merge([$quote_id], $quoteMergeIds);
            // dd($quoteMergeIds, $all_quote_ids);


            $quotes_to_validate = Quote::whereIn('id', $quote_merges)
                ->with(['passengers'])
                ->orderBy('date_in')
                ->get();

            $quote_id = $quotes_to_validate->first()->id;

            $quoteMergeIds = [];
            $errors = [];
            foreach ($quotes_to_validate as $quote) {

                if($quote_id != $quote->id){
                    array_push($quoteMergeIds, $quote->id);
                }

                if ($quote->passengers && $quote->passengers->count() > 0) {
                    foreach ($quote->passengers as $passenger) {
                        if (empty($passenger->first_name) || trim($passenger->first_name) === '') {
                            $errors[] = "La cotización #{$quote->id} ({$quote->name}) tiene pasajeros sin nombre (first_name). Por favor complete el nombre de todos los pasajeros antes de realizar el merge.";
                        }
                    }
                }
            }

            // dd($quote_id,$quoteMergeIds,$quote_merges);

            if (count($errors) > 0) {
                return Response::json([
                    'success' => false,
                    'errors' => $errors
                ]);
            }

            $quote_for_copy = Quote::where('id', $quote_id)
                ->with([
                    'categories',
                    'ranges',
                    'notes',
                    'passengers',
                    'people',
                    'age_child',
                    'destinations',
                    'accommodation',
                    'distributions.passengers'
                ])->first();

            // CAMBIA EL ESTADO DE LA COTIZACION
            // $quote_for_copy->status = 3;
            // $quote_for_copy->save();

            if ($quote_for_copy->operation != 'passengers') {
                throw new \Exception("You can only join quotes of the passenger type, but not ranges");
            }

            $quote_id_new = DB::transaction(function () use (
                $quote_id,
                $to_status,
                $user_id,
                $quote_for_copy,
                $quote_merges,
                $quoteMergeIds
            ) {

                // AQUI NECESITO AGREGAR Y HACER COPIAS

                QuotePassenger::where('quote_id', $quote_id)->update([
                    'quote_passenger_id' => null
                ]);

                $quote_for_copy_name = $quote_for_copy->name . " Multi Región";

                $date_in = $quote_for_copy->date_in;

                $date_estimated = $quote_for_copy->date_estimated;

                $this->newQuote(
                    $quote_for_copy_name,
                    $date_in,
                    $date_estimated,
                    $quote_for_copy->nights,
                    $quote_for_copy->service_type_id,
                    $user_id,
                    [],
                    [],
                    [],
                    [],
                    [],
                    $quote_for_copy->operation,
                    $to_status,
                    NULL, //$quote_for_copy->markup
                    $quote_for_copy->discount,
                    $quote_for_copy->discount_detail,
                    $quote_for_copy->order_related,
                    $quote_for_copy->order_position,
                    $quote_for_copy->file_id,
                    $quote_for_copy->file_number,
                    $quote_for_copy->file_total_amount,
                    0
                );
                $new_object_id = $this->getObjectId();
                $first_category_id_for_copy = null;

                foreach ($quote_for_copy->categories as $key => $c) {
                    $new_category_id = DB::table('quote_categories')->insertGetId([
                        'quote_id'      => $new_object_id,
                        'type_class_id' => $c->type_class_id,
                        'created_at'    => Carbon::now(),
                        "updated_at"    => Carbon::now()
                    ]);

                    if($key == 0) {
                        $first_category_id_for_copy = $new_category_id;
                    }

                    $c_services = QuoteService::where('quote_category_id', $c->id)
                        ->with([
                            'service_rate' => function ($query) {
                                $query->select(['id', 'quote_service_id', 'service_rate_id']);
                            }
                        ])
                        ->with([
                            'service_rooms' => function ($query) {
                                $query->select(['id', 'quote_service_id', 'rate_plan_room_id']);
                            }
                        ])
                        ->with([
                            'service_rooms_hyperguest' => function ($query) {
                                $query->select(['id', 'quote_service_id', 'room_id', 'rate_plan_id']);
                            }
                        ])
                        ->with([
                            'passengers' => function ($query) {
                                $query->select(['id', 'quote_service_id', 'quote_passenger_id']);
                            }
                        ])
                        ->orderBy('date_in', 'asc')
                        ->orderBy('order', 'asc')
                        ->get();

                    //Todo Arreglo para agregar las extensiones
                    $array_quote_services_id_extensions = [];

                    //Todo Buscamos las extensiones
                    foreach ($c_services as $s) {
                        if ($quote_for_copy->file_id) { // cuado son cotizaciones que se han generado de files a3
                            $locked = $s->locked;
                        } else {
                            $locked = 0;
                            if ($to_status === 2) {
                                $locked = $s->locked;
                            }
                        }

                        if ($s->extension_id !== null && $s->parent_service_id == null) {
                            $new_service_id = DB::table('quote_services')->insertGetId([
                                'quote_category_id' => $new_category_id,
                                'type'              => $s->type,
                                'object_id'         => $s->object_id,
                                'order'             => $s->order,
                                'date_in'           => convertDate($s->date_in, '/', '-', 1),
                                'date_out'          => convertDate($s->date_out, '/', '-', 1),
                                'hour_in'           => $s->hour_in,
                                'nights'            => $s->nights,
                                'adult'             => $s->adult,
                                'child'             => $s->child,
                                'infant'            => $s->infant,
                                'single'            => $s->single,
                                'double'            => $s->double,
                                'triple'            => $s->triple,
                                'locked'            => $locked,
                                'triple_active'     => $s->triple_active,
                                'on_request'        => $s->on_request,
                                'optional'          => (int)$s->optional,
                                'code_flight'       => @$s->code_flight,
                                'origin'            => @$s->origin,
                                'destiny'           => @$s->destiny,
                                'extension_id'      => $s->extension_id,
                                'new_extension_id'  => $s->new_extension_id,
                                'parent_service_id' => null,
                                'schedule_id'       => $s->schedule_id,

                                'is_file'           => $s->is_file,
                                'file_itinerary_id' => $s->file_itinerary_id,
                                'file_status'       => $s->file_status,
                                'file_amount_sale'  => $s->file_amount_sale,
                                'file_amount_cost'  => $s->file_amount_cost,
                                'hyperguest_pull'   => $s->hyperguest_pull,
                                'markup_regionalization' => $quote_for_copy->markup ?? 0,
                                'created_at' => Carbon::now(),
                                "updated_at" => Carbon::now()
                            ]);

                            $array_quote_services_id_extensions[] = [
                                "quote_service_id_old" => $s->id,
                                "quote_service_id_new" => $new_service_id
                            ];


                        }
                    }

                    foreach ($c_services as $s) {
                        if ($quote_for_copy->file_id) { // cuado son cotizaciones que se han generado de files a3
                            $locked = $s->locked;
                        } else {

                            $locked = 0;
                            if ($to_status === 2) {
                                $locked = $s->locked;
                            }
                        }
                        //Todo Verifico si el servicio pertenece a la extension (parent_service_id)
                        if ($s->extension_id == null && $s->parent_service_id !== null) {
                            $new_parent_service_id = null;

                            foreach ($array_quote_services_id_extensions as $quote_services_id_extension) {
                                if ($s->parent_service_id == $quote_services_id_extension["quote_service_id_old"]) {
                                    $new_parent_service_id = $quote_services_id_extension["quote_service_id_new"];
                                }
                            }
                            $new_service_id = DB::table('quote_services')->insertGetId([
                                'quote_category_id' => $new_category_id,
                                'type'              => $s->type,
                                'object_id'         => $s->object_id,
                                'order'             => $s->order,
                                'date_in'           => convertDate($s->date_in, '/', '-', 1),
                                'date_out'          => convertDate($s->date_out, '/', '-', 1),
                                'hour_in'           => $s->hour_in,
                                'nights'            => $s->nights,
                                'adult'             => $s->adult,
                                'child'             => $s->child,
                                'infant'            => $s->infant,
                                'single'            => $s->single,
                                'double'            => $s->double,
                                'triple'            => $s->triple,
                                'locked'            => $locked,
                                'triple_active'     => $s->triple_active,
                                'on_request'        => $s->on_request,
                                'optional'          => (int)$s->optional,
                                'code_flight'       => @$s->code_flight,
                                'origin'            => @$s->origin,
                                'destiny'           => @$s->destiny,
                                'extension_id'      => null,
                                'new_extension_id'  => $s->new_extension_id,
                                'parent_service_id' => $new_parent_service_id,
                                'schedule_id'       => $s->schedule_id,

                                'is_file'           => $s->is_file,
                                'file_itinerary_id' => $s->file_itinerary_id,
                                'file_status'       => $s->file_status,
                                'file_amount_sale'  => $s->file_amount_sale,
                                'file_amount_cost'  => $s->file_amount_cost,
                                'hyperguest_pull'   => $s->hyperguest_pull,
                                'markup_regionalization' => $quote_for_copy->markup ?? 0,
                                'created_at' => Carbon::now(),
                                "updated_at" => Carbon::now()
                            ]);
                        }
                        //Todo Verifico si el servicio no es una extension
                        if ($s->extension_id == null && $s->parent_service_id == null) {
                            $new_service_id = DB::table('quote_services')->insertGetId([
                                'quote_category_id' => $new_category_id,
                                'type'              => $s->type,
                                'object_id'         => $s->object_id,
                                'order'             => $s->order,
                                'date_in'           => convertDate($s->date_in, '/', '-', 1),
                                'date_out'          => convertDate($s->date_out, '/', '-', 1),
                                'hour_in'           => $s->hour_in,
                                'nights'            => $s->nights,
                                'adult'             => $s->adult,
                                'child'             => $s->child,
                                'infant'            => $s->infant,
                                'single'            => $s->single,
                                'double'            => $s->double,
                                'triple'            => $s->triple,
                                'locked'            => $locked,
                                'triple_active'     => $s->triple_active,
                                'on_request'        => $s->on_request,
                                'optional'          => (int)$s->optional,
                                'code_flight'       => @$s->code_flight,
                                'origin'            => @$s->origin,
                                'destiny'           => @$s->destiny,
                                'extension_id'      => null,
                                'new_extension_id'  => $s->new_extension_id,
                                'parent_service_id' => null,
                                'schedule_id'       => $s->schedule_id,

                                'is_file'           => $s->is_file,
                                'file_itinerary_id' => $s->file_itinerary_id,
                                'file_status'       => $s->file_status,
                                'file_amount_sale'  => $s->file_amount_sale,
                                'file_amount_cost'  => $s->file_amount_cost,
                                'hyperguest_pull'   => $s->hyperguest_pull,
                                'markup_regionalization' => $quote_for_copy->markup ?? 0,
                                'created_at' => Carbon::now(),
                                "updated_at" => Carbon::now()
                            ]);
                        }

                        if ($s->extension_id !== null && $s->parent_service_id == null) {
                            foreach ($array_quote_services_id_extensions as $quote_services_id_extension) {
                                if ($s->id == $quote_services_id_extension["quote_service_id_old"]) {
                                    $new_service_id = $quote_services_id_extension["quote_service_id_new"];
                                }
                            }
                        }

                        if ($s->type == 'service' and isset($s->service_rate->id)) {
                            DB::table('quote_service_rates')->insert([
                                'quote_service_id' => $new_service_id,
                                'service_rate_id'  => $s->service_rate->service_rate_id,
                                'created_at'       => Carbon::now(),
                                "updated_at"       => Carbon::now()
                            ]);
                        }
                        if ($s->type == 'hotel') {
                            foreach ($s->service_rooms as $r) {
                                DB::table('quote_service_rooms')->insert([
                                    'quote_service_id'  => $new_service_id,
                                    'rate_plan_room_id' => $r->rate_plan_room_id,
                                    'created_at'        => Carbon::now(),
                                    "updated_at"        => Carbon::now()
                                ]);
                            }
                            foreach ($s->service_rooms_hyperguest as $r) {
                                DB::table('quote_service_rooms_hyperguest')->insert([
                                    'quote_service_id'  => $new_service_id,
                                    'room_id'           => $r->room_id,
                                    'rate_plan_id'      => $r->rate_plan_id,
                                    'created_at'        => Carbon::now(),
                                    "updated_at"        => Carbon::now()
                                ]);
                            }
                        }

                        /*
                        * Todo Creamos la asignacion de pasajeros con el quote_passenger_id de la cotizacion copia
                        *  para luego actualizarlo en el metodo copyServicePassengersFromQuote
                        */
                        foreach ($s->passengers as $passenger) {
                            DB::table('quote_service_passengers')->insert([
                                'quote_service_id'   => $new_service_id,
                                'quote_passenger_id' => $passenger->quote_passenger_id,
                                'created_at'         => Carbon::now(),
                                "updated_at"         => Carbon::now()
                            ]);
                        }

                    }
                }

                $this->updateDateInServicesInAllCategories($new_object_id);

                $people = $quote_for_copy->people->first();

                DB::table('quote_people')->insert([
                    'adults'     => $people->adults,
                    'child'      => $people->child,
                    'quote_id'   => $new_object_id,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);

                $quote_passengers_childs_createds = [];
                if ($people->child > 0) {
                    foreach ($quote_for_copy->age_child as $quote_age_child) {

                        $quote_passenger_child_New = DB::table('quote_age_child')->insertGetId([
                            'age'      => $quote_age_child->age,
                            'quote_id' => $new_object_id,
                        ]);

                        $quote_passengers_childs_createds[$quote_age_child->id] = $quote_passenger_child_New;

                    }
                }

                $quote_passengers_createds = [];
                foreach ($quote_for_copy->passengers as $passenger) {

                    $quote_age_child_id = null;
                    if ($passenger->type == 'CHD') {
                        if (isset($quote_passengers_childs_createds[$passenger->quote_age_child_id])) {
                            $quote_age_child_id = $quote_passengers_childs_createds[$passenger->quote_age_child_id];
                        }

                    }

                    if ($passenger->birthday === '0000-00-00' || $passenger->birthday === null) {
                        $passenger_birthday = null;
                    } else {
                        $passenger_birthday = Carbon::parse($passenger->birthday)->format('Y-m-d');
                    }

                    $quote_passengers_New = DB::table('quote_passengers')->insertGetId([
                        'first_name'         => $passenger->first_name,
                        'last_name'          => $passenger->last_name,
                        'gender'             => $passenger->gender ? $passenger->gender : null,
                        'birthday'           => $passenger_birthday,
                        'document_number'    => $passenger->document_number,
                        'doctype_iso'        => $passenger->doctype_iso,
                        'country_iso'        => $passenger->country_iso,
                        'email'              => $passenger->email,
                        'phone'              => $passenger->phone,
                        'notes'              => $passenger->notes,
                        'type'               => $passenger->type,
                        'quote_id'           => $new_object_id,
                        'quote_age_child_id' => $quote_age_child_id,
                        "created_at"         => Carbon::now(),
                        "updated_at"         => Carbon::now()
                    ]);

                    $quote_passengers_createds[$passenger->id] = $quote_passengers_New;
                }


                foreach ($quote_for_copy->distributions as $distribution) {

                    $quote_distribution = DB::table('quote_distributions')->insertGetId([
                        'type_room'      => $distribution->type_room,
                        'type_room_name' => $distribution->type_room_name,
                        'occupation'     => $distribution->occupation,
                        'single'         => $distribution->single,
                        'double'         => $distribution->double,
                        'triple'         => $distribution->triple,
                        'adult'          => $distribution->adult,
                        'child'          => $distribution->child,
                        'order'          => $distribution->order,
                        'quote_id'       => $new_object_id,
                        "created_at"     => Carbon::now(),
                        "updated_at"     => Carbon::now()
                    ]);

                    foreach ($distribution->passengers as $passenger) {
                        DB::table('quote_distribution_passengers')->insertGetId([
                            'quote_distribution_id' => $quote_distribution,
                            'quote_passenger_id'    => $quote_passengers_createds[$passenger->quote_passenger_id],
                            "created_at"            => Carbon::now(),
                            "updated_at"            => Carbon::now()
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
                        "created_at"     => Carbon::now(),
                        "updated_at"     => Carbon::now()
                    ]);
                }

                foreach ($quote_for_copy->destinations as $destiny) {
                    DB::table('quote_destinations')->insert([
                        'quote_id'   => $new_object_id,
                        'state_id'   => $destiny->state_id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                }

                $_logs = DB::table('quote_logs')->where('quote_id', $quote_for_copy->id)
                    ->whereIn('type', [
                        'from_package',
                        'from_extension',
                        'quote_added',
                        'extension_added'
                    ])->get();

                foreach ($_logs as $log) {
                    DB::table('quote_logs')->insert([
                        'quote_id'   => $new_object_id,
                        'type'       => $log->type,
                        'object_id'  => $log->object_id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                }

                if ($user_id == Auth::user()->id) { // "Duplicar"
                    DB::table('quote_logs')->insert([
                        'quote_id'   => $new_object_id,
                        'type'       => 'quote_added',
                        'object_id'  => $quote_id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                    DB::table('quote_logs')->insert([
                        'quote_id'   => $new_object_id,
                        'type'       => 'from_quote',
                        'object_id'  => $quote_id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                }

                $this->copyServicePassengersFromQuote($quote_id, $new_object_id);

                if ($quote_for_copy->accommodation) {
                    $new_accommodation = new QuoteAccommodation();
                    $new_accommodation->quote_id = $new_object_id;
                    $new_accommodation->single = $quote_for_copy->accommodation->single;
                    $new_accommodation->double = $quote_for_copy->accommodation->double;
                    $new_accommodation->double_child = $quote_for_copy->accommodation->double_child;
                    $new_accommodation->triple = $quote_for_copy->accommodation->triple;
                    $new_accommodation->triple_child = $quote_for_copy->accommodation->triple_child;
                    $new_accommodation->save();
                }

                // Obtener pasajeros ya copiados de quote_for_copy para comparación (fuera del loop para mantener entre iteraciones)
                $existing_passengers = [];
                foreach ($quote_passengers_createds as $old_id => $new_id) {
                    $passenger = $quote_for_copy->passengers->firstWhere('id', $old_id);
                    if ($passenger && !empty($passenger->first_name)) {
                        $key = mb_strtolower(trim($passenger->first_name));
                        $existing_passengers[$key] = $new_id;
                    }
                }

                // Copiar estructura de todas las cotizaciones en $quoteMergeIds
                foreach ($quoteMergeIds as $quoteMergeId) {
                    $quote_to_merge = Quote::where('id', $quoteMergeId)
                        ->with([
                            'categories',
                            'passengers',
                            'people',
                            'age_child',
                            'destinations',
                            'accommodation',
                            'distributions.passengers'
                        ])->first();

                    if (!$quote_to_merge) {
                        continue;
                    }

                    // Cambiar estado de la cotización que se está mergeando
                    // $quote_to_merge->status = 3;
                    // $quote_to_merge->save();

                    $first_category_to_merge_searched = $quote_to_merge->categories->firstWhere('type_class_id', $quote_for_copy->categories->first()->type_class_id) ?? $quote_to_merge->categories->first();

                    $c_services = QuoteService::where('quote_category_id', $first_category_to_merge_searched->id)
                        ->with([
                            'service_rate' => function ($query) {
                                $query->select(['id', 'quote_service_id', 'service_rate_id']);
                            }
                        ])
                        ->with([
                            'service_rooms' => function ($query) {
                                $query->select(['id', 'quote_service_id', 'rate_plan_room_id']);
                            }
                        ])
                        ->with([
                            'service_rooms_hyperguest' => function ($query) {
                                $query->select(['id', 'quote_service_id', 'room_id', 'rate_plan_id']);
                            }
                        ])
                        ->with([
                            'passengers' => function ($query) {
                                $query->select(['id', 'quote_service_id', 'quote_passenger_id']);
                            }
                        ])
                        ->orderBy('date_in', 'asc')
                        ->orderBy('order', 'asc')
                        ->get();

                    // Arreglo para agregar las extensiones
                    $quote_services_createds_merge = [];

                    foreach ($c_services as $s) {
                        if ($quote_to_merge->file_id) {
                            $locked = $s->locked;
                        } else {
                            $locked = 0;
                            if ($to_status === 2) {
                                $locked = $s->locked;
                            }
                        }

                        $new_service_id = DB::table('quote_services')->insertGetId([
                            'quote_category_id' => $first_category_id_for_copy,
                            'type'              => $s->type,
                            'object_id'         => $s->object_id,
                            'order'             => $s->order,
                            'date_in'           => convertDate($s->date_in, '/', '-', 1),
                            'date_out'          => convertDate($s->date_out, '/', '-', 1),
                            'hour_in'           => $s->hour_in,
                            'nights'            => $s->nights,
                            'adult'             => $s->adult,
                            'child'             => $s->child,
                            'infant'            => $s->infant,
                            'single'            => $s->single,
                            'double'            => $s->double,
                            'triple'            => $s->triple,
                            'locked'            => $locked,
                            'triple_active'     => $s->triple_active,
                            'on_request'        => $s->on_request,
                            'optional'          => (int)$s->optional,
                            'code_flight'       => @$s->code_flight,
                            'origin'            => @$s->origin,
                            'destiny'           => @$s->destiny,
                            'extension_id'      => null,
                            'new_extension_id'  => $s->new_extension_id,
                            'parent_service_id' => null,
                            'schedule_id'       => $s->schedule_id,
                            'is_file'           => $s->is_file,
                            'file_itinerary_id' => $s->file_itinerary_id,
                            'file_status'       => $s->file_status,
                            'file_amount_sale'  => $s->file_amount_sale,
                            'file_amount_cost'  => $s->file_amount_cost,
                            'hyperguest_pull'   => $s->hyperguest_pull,
                            'markup_regionalization' => $quote_to_merge->markup ?? 0,
                            'created_at'        => Carbon::now(),
                            "updated_at"        => Carbon::now()
                        ]);

                        if ($s->type == 'service' and isset($s->service_rate->id)) {
                            DB::table('quote_service_rates')->insert([
                                'quote_service_id' => $new_service_id,
                                'service_rate_id'  => $s->service_rate->service_rate_id,
                                'created_at'       => Carbon::now(),
                                "updated_at"       => Carbon::now()
                            ]);
                        }

                        if ($s->type == 'hotel') {
                            foreach ($s->service_rooms as $r) {
                                DB::table('quote_service_rooms')->insert([
                                    'quote_service_id'  => $new_service_id,
                                    'rate_plan_room_id' => $r->rate_plan_room_id,
                                    'created_at'        => Carbon::now(),
                                    "updated_at"        => Carbon::now()
                                ]);
                            }
                            foreach ($s->service_rooms_hyperguest as $r) {
                                DB::table('quote_service_rooms_hyperguest')->insert([
                                    'quote_service_id'  => $new_service_id,
                                    'room_id'           => $r->room_id,
                                    'rate_plan_id'      => $r->rate_plan_id,
                                    'created_at'        => Carbon::now(),
                                    "updated_at"        => Carbon::now()
                                ]);
                            }
                        }

                        // SERVICE AMOUNTS

                        DB::table('quote_service_amounts')->where('quote_service_id', $s->id)->get()->each(function ($service_amount) use ($new_service_id) {
                            DB::table('quote_service_amounts')->insert([
                                'quote_service_id' => $new_service_id,
                                'date_service'     => $service_amount->date_service,
                                'price_per_night_without_markup' => $service_amount->price_per_night_without_markup,
                                'price_per_night' => $service_amount->price_per_night,
                                'price_adult_without_markup' => $service_amount->price_adult_without_markup,
                                'price_adult'   => $service_amount->price_adult,
                                'price_child_without_markup' => $service_amount->price_child_without_markup,
                                'price_child' => $service_amount->price_child,
                                'created_at'       => Carbon::now(),
                                "updated_at"       => Carbon::now(),
                                'price_teenagers_without_markup' => $service_amount->price_teenagers_without_markup,
                                'price_teenagers' => $service_amount->price_teenagers,
                            ]);
                        });

                        // Guardar mapeo de IDs de servicios
                        $quote_services_createds_merge[$s->id] = $new_service_id;
                    }

                    // Copiar pasajeros de quote_to_merge si no son duplicados
                    $quote_passengers_childs_createds_merge = [];
                    if ($quote_to_merge->people && $quote_to_merge->people->first() && $quote_to_merge->people->first()->child > 0) {
                        foreach ($quote_to_merge->age_child as $quote_age_child) {
                            $quote_passenger_child_New = DB::table('quote_age_child')->insertGetId([
                                'age'      => $quote_age_child->age,
                                'quote_id' => $new_object_id,
                            ]);
                            $quote_passengers_childs_createds_merge[$quote_age_child->id] = $quote_passenger_child_New;
                        }
                    }

                    $quote_passengers_createds_merge = [];
                    foreach ($quote_to_merge->passengers as $passenger) {
                        // Verificar si el pasajero ya existe comparando first_name en minúsculas
                        $key = !empty($passenger->first_name) ? mb_strtolower(trim($passenger->first_name)) : null;

                        // Si ya existe, usar el ID existente, si no, crear uno nuevo
                        if ($key && isset($existing_passengers[$key])) {
                            // Pasajero duplicado, usar el ID existente
                            $quote_passengers_createds_merge[$passenger->id] = $existing_passengers[$key];
                        } else {
                            // Pasajero nuevo, copiarlo
                            $quote_age_child_id = null;
                            if ($passenger->type == 'CHD') {
                                if (isset($quote_passengers_childs_createds_merge[$passenger->quote_age_child_id])) {
                                    $quote_age_child_id = $quote_passengers_childs_createds_merge[$passenger->quote_age_child_id];
                                }
                            }

                            if ($passenger->birthday === '0000-00-00' || $passenger->birthday === null) {
                                $passenger_birthday = null;
                            } else {
                                $passenger_birthday = Carbon::parse($passenger->birthday)->format('Y-m-d');
                            }

                            $quote_passengers_New = DB::table('quote_passengers')->insertGetId([
                                'first_name'         => $passenger->first_name,
                                'last_name'          => !empty($passenger->last_name) ? $passenger->last_name : null,
                                'gender'             => $passenger->gender ? $passenger->gender : null,
                                'birthday'           => $passenger_birthday,
                                'document_number'    => $passenger->document_number,
                                'doctype_iso'        => $passenger->doctype_iso,
                                'country_iso'        => $passenger->country_iso,
                                'email'              => $passenger->email,
                                'phone'              => $passenger->phone,
                                'notes'              => $passenger->notes,
                                'type'               => $passenger->type,
                                'quote_id'           => $new_object_id,
                                'quote_age_child_id' => $quote_age_child_id,
                                "created_at"         => Carbon::now(),
                                "updated_at"         => Carbon::now()
                            ]);

                            $quote_passengers_createds_merge[$passenger->id] = $quote_passengers_New;

                            // Agregar el pasajero recién creado a existing_passengers para evitar duplicados
                            if ($key) {
                                $existing_passengers[$key] = $quote_passengers_New;
                            }
                        }
                    }

                    // Actualizar quote_service_passengers de los servicios copiados de quote_to_merge
                    foreach ($c_services as $s) {
                        if (isset($quote_services_createds_merge[$s->id])) {
                            $new_service_id = $quote_services_createds_merge[$s->id];

                            if ($s->passengers) {
                                foreach ($s->passengers as $service_passenger) {
                                    // Usar el mapeo de IDs de pasajeros
                                    if (isset($quote_passengers_createds_merge[$service_passenger->quote_passenger_id])) {
                                        $new_passenger_id = $quote_passengers_createds_merge[$service_passenger->quote_passenger_id];

                                        // Verificar que no exista ya la relación para evitar duplicados
                                        $exists = DB::table('quote_service_passengers')
                                            ->where('quote_service_id', $new_service_id)
                                            ->where('quote_passenger_id', $new_passenger_id)
                                            ->exists();

                                        if (!$exists) {
                                            DB::table('quote_service_passengers')->insert([
                                                'quote_service_id'   => $new_service_id,
                                                'quote_passenger_id' => $new_passenger_id,
                                                'created_at'         => Carbon::now(),
                                                "updated_at"         => Carbon::now()
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Actualizar relaciones de la cotización mergeada
                    $quote_to_merge->save();
                }

                // comentado por que ya no existe el campo
                $quote_new =  Quote::where('id', $new_object_id)->first();
                $quote_new->is_multiregion = 1;
                $quote_new->save();

                // $quote_for_copy->quote_id_multi_region_to = $new_object_id;
                // $quote_for_copy->is_multiregion = true;
                // $quote_for_copy->save();

                foreach($quote_merges as $quotes)
                {
                    DB::table('quote_merge')->insert([
                        'quote_id'   => $quotes,
                        'quote_father_id'   => $quote_for_copy->id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                }

                return $new_object_id;
            });

            return Response::json(['success' => true, 'quote_new_id' => $quote_id_new]);

        // } catch (\Exception $e) {
        //     return $this->throwError($e);
        // }


    }

    public function get_quotes(Request $request)
    {

    }

    public function unmerge($quote_id)
    {
        try {
            $result = DB::transaction(function () use ($quote_id) {
                $quoteEditing = Quote::find($quote_id);

                if (!$quoteEditing) {
                    throw new \Exception("Quote not found {$quote_id}");
                }

                $original_quote_id = $quoteEditing->quote_id_multi_region_from;

                // $quoteEditing->quote_id_multi_region_to = NULL;
                // $quoteEditing->quote_id_multi_region_from = NULL;
                $quoteEditing->save();

                $quoteOriginal = Quote::find($original_quote_id);

                if (!$quoteOriginal) {
                    throw new \Exception("Quote not found {$original_quote_id}");
                }

                // ELIMINAR DE LA TABLA QUOTE_MERGE
                QuoteMerge::where('quote_father_id',  $quoteOriginal->id)->delete();

                // $merged_quote_id = $quoteOriginal->quote_id_multi_region_to;

                // if (!$merged_quote_id) {
                //     throw new \Exception("Not found merged quote {$merged_quote_id}");
                // }

                // comentado por que el campo ya no existe
                // $quotes_to_delete = Quote::where('quote_id_multi_region_to', $merged_quote_id)->get();
                // foreach ($quotes_to_delete as $quote_to_delete) {
                //     $quote_to_delete->quote_id_multi_region_to = NULL;
                //     $quote_to_delete->quote_id_multi_region_from = NULL;
                //     $quote_to_delete->save();
                // }

                $quoteOriginal->status = 1;
                $quoteOriginal->is_multiregion = false;
                $quoteOriginal->save();

                // $quoteMerged = Quote::find($merged_quote_id);

                // if (!$quoteMerged) {
                //     throw new \Exception("Quote not found {$merged_quote_id}");
                // }

                // $quoteMerged->quote_id_multi_region_from = NULL;
                // $quoteMerged->quote_id_multi_region_to = NULL;
                // $quoteMerged->deleted_at = Carbon::now();
                // $quoteMerged->save();

                // return $original_quote_id;
            });

            return Response::json(['success' => true, 'quote_father_id' => $result]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
