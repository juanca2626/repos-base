<?php

namespace App\Http\Traits;

use App\BagRate;
use App\Inventory;
use App\InventoryBag;
use App\Language;
use App\Package as Packages;
use App\PackageDestination;
use App\PackageDynamicRate;
use App\PackageDynamicSaleRate;
use App\PackageInventory;
use App\PackagePlanRate;
use App\PackagePlanRateCategory;
use App\PackageFixedSaleRate;
use App\PackageRateSaleMarkup;
use App\PackageService;
use App\PackageServiceRate;
use App\PackageServiceRoom;
use App\RatesPlansCalendarys;
use App\RatesPlansRooms;
use App\ServiceRatePlan;
use App\Client;
use App\Galery;
use App\Hotel;
use App\Room;
use App\Services\UpdatePackageDestinationsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use App\Http\Resources\Package\PackageResource;
use App\PackageCancellationPolicy;
use App\PackageServiceRoomHyperguest;
use App\Service;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

trait Package
{
    public $expiration_search = 1440;

    private function updateAmountPackageService(
        $service_id,
        $quantity_adults,
        $quantity_children,
        $quantity_single,
        $quantity_double,
        $quantity_triple
    ) {
        DB::transaction(function () use (
            $service_id,
            $quantity_adults,
            $quantity_children,
            $quantity_single,
            $quantity_double,
            $quantity_triple
        ) {
            $service = DB::table('package_services')->where('id', $service_id)->whereNull('deleted_at')->first();
            DB::table('package_service_amounts')->where('package_service_id', $service_id)->delete();
            if ($service->type == 'service') {

                $service_rate_id = DB::table('package_service_rates')->where(
                    'package_service_id',
                    $service_id
                )->whereNull('deleted_at')->first()->service_rate_id;

                $pax_amounts = DB::table('service_rate_plans')
                    ->where('service_rate_id', $service_rate_id)
                    ->where('date_from', '<=', $service->date_in)
                    ->where('date_to', '>=', $service->date_in)
                    ->where('pax_from', '<=', $quantity_adults + $quantity_children)
                    ->where('pax_to', '>=', $quantity_adults + $quantity_children)
                    ->whereNull('deleted_at')
                    ->get();
                //                dd($pax_amounts);
                $price_adult = 0;
                $price_child = 0;
                foreach ($pax_amounts as $pax_amount) {
                    $price_adult += ($pax_amount->price_adult * $quantity_adults);

                    $price_child += ($pax_amount->price_child * $quantity_children);
                }
                $divisor = 1;
                if ($pax_amounts->count() > 0) {
                    $divisor = $pax_amounts->count();
                }
                $total_amount = ($price_adult + $price_child) / $divisor;

                $quantity_pax = $quantity_adults + $quantity_children;

                if ($total_amount == 0 && $quantity_pax > 2) {
                    $pax_amount_single = DB::table('service_rate_plans')
                        ->where('service_rate_id', $service_rate_id)
                        ->where('date_from', '<=', $service->date_in)
                        ->where('date_to', '>=', $service->date_in)
                        ->where('pax_from', '=', 1)
                        ->where('pax_to', '=', 1)
                        ->whereNull('deleted_at');
                    $pax_amount_single_price_adult = 0;
                    if ($pax_amount_single->count() > 0) {
                        $pax_amount_single_price_adult = $pax_amount_single->first()->price_adult * $quantity_pax;
                    }
                    $total_amount = $pax_amount_single_price_adult;
                }

                DB::table('package_service_amounts')->where('package_service_id', $service_id)->delete();

                DB::table('package_service_amounts')->insert([
                    'package_service_id' => $service_id,
                    'amount' => roundLito((float)$total_amount),
                    'created_at' => Carbon::now(),
                ]);
            }
            if ($service->type == 'hotel') {
                $service_rate_plan_rooms_ids = DB::table('package_service_rooms')->where(
                    'package_service_id',
                    $service_id
                )->whereNull('deleted_at')->pluck('rate_plan_room_id');

                $rate_plan_rooms = DB::table('rates_plans_rooms')
                    ->whereIn('id', $service_rate_plan_rooms_ids)
                    ->whereNull('deleted_at')
                    ->get();

                $simple = 0;
                $double = 0;
                $triple = 0;
                //Calculo para Simple
                foreach ($rate_plan_rooms as $rate_plan_room) {
                    $room_type_id = DB::table('rooms')
                        ->where('id', $rate_plan_room->room_id)->whereNull('deleted_at')->first()->room_type_id;

                    $occupation = DB::table('room_types')
                        ->whereNull('deleted_at')
                        ->where('id', $room_type_id)->first()->occupation;

                    if ($occupation == 1) {
                        $rate_plan_room_calendars = DB::table('rates_plans_calendarys')
                            ->where('rates_plans_room_id', $rate_plan_room->id)
                            ->where('date', '>=', $service->date_in)
                            ->where('date', '<=', $service->date_out)
                            ->whereNull('deleted_at')
                            ->get();
                        //                        if( $service->id == 1861 ){
                        //                            var_export( $rate_plan_room_calendars ); die;
                        //                        }
                        foreach ($rate_plan_room_calendars as $calendar) {
                            $rate = DB::table('rates')
                                ->whereNull('deleted_at')
                                ->where('rates_plans_calendarys_id', $calendar->id)->first();
                            $simple += $rate->price_adult;
                        }
                        $simple = roundLito((float)$simple);

                        //                        if( $service->id == 1861 ){
                        //                            var_export( $simple ); die;
                        //                        }
                    }
                }
                //Calc para Double
                foreach ($rate_plan_rooms as $rate_plan_room) {
                    $room_type_id = DB::table('rooms')
                        ->whereNull('deleted_at')
                        ->where('id', $rate_plan_room->room_id)->first()->room_type_id;

                    $occupation = DB::table('room_types')
                        ->whereNull('deleted_at')
                        ->where('id', $room_type_id)->first()->occupation;

                    if ($occupation == 2) {
                        $rate_plan_room_calendars = DB::table('rates_plans_calendarys')
                            ->where('rates_plans_room_id', $rate_plan_room->id)
                            ->where('date', '>=', $service->date_in)
                            ->where('date', '<=', Carbon::parse($service->date_out)->subDays(1)->format('Y-m-d'))
                            ->whereNull('deleted_at')
                            ->get();

                        foreach ($rate_plan_room_calendars as $calendar) {
                            $rate = DB::table('rates')
                                ->whereNull('deleted_at')
                                ->where('rates_plans_calendarys_id', $calendar->id)->first();

                            $double += $rate->price_adult;
                        }
                        $double = roundLito((float)$double);
                    }
                }

                //Calc para Triple
                $exist_triple = false;
                foreach ($rate_plan_rooms as $rate_plan_room) {
                    $room_type_id = DB::table('rooms')
                        ->whereNull('deleted_at')
                        ->where('id', $rate_plan_room->room_id)->first()->room_type_id;

                    $occupation = DB::table('room_types')
                        ->whereNull('deleted_at')
                        ->where('id', $room_type_id)->first()->occupation;

                    if ($occupation == 3) {
                        $exist_triple = true;
                        $rate_plan_room_calendars = DB::table('rates_plans_calendarys')
                            ->where('rates_plans_room_id', $rate_plan_room->id)
                            ->where('date', '>=', $service->date_in)
                            ->where('date', '<=', Carbon::parse($service->date_out)->subDays(1)->format('Y-m-d'))
                            ->whereNull('deleted_at')
                            ->get();

                        foreach ($rate_plan_room_calendars as $calendar) {
                            $rate = DB::table('rates')
                                ->whereNull('deleted_at')
                                ->where('rates_plans_calendarys_id', $calendar->id)->first();

                            $triple += $rate->price_adult + $rate->price_extra;
                        }
                        $triple = roundLito((float)$triple);
                    }
                }

                if (!$exist_triple) {
                    $triple = ($simple + $double);
                    $triple = roundLito((float)$triple);
                }

                $total_amount = ($simple * $quantity_single) + ($double * $quantity_double) + ($triple * $quantity_triple);
                $total_amount = roundLito((float)$total_amount);
                DB::table('package_service_amounts')->insert([
                    'package_service_id' => $service_id,
                    'amount' => $total_amount,
                    'single' => $simple * $quantity_single,
                    'double' => $double * $quantity_double,
                    'triple' => $triple * $quantity_triple,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        });
    }

    public function updatePassengerAmounts(
        $plan_rate_category_id,
        $quantity_adults,
        $quantity_children,
        $quantity_single,
        $quantity_double,
        $quantity_triple
    ) {
        $services = DB::table('package_services')->where(
            'package_plan_rate_category_id',
            $plan_rate_category_id
        )->whereNull('deleted_at')->orderBy('date_in')->get();

        foreach ($services as $service) {
            DB::table('package_service_amounts')->where('package_service_id', $service->id)->delete();
            $this->updateAmountPackageService(
                $service->id,
                $quantity_adults,
                $quantity_children,
                $quantity_single,
                $quantity_double,
                $quantity_triple
            );
        }
    }

    public function calculatePricePackage($category_id)
    {
        $services_dates = PackageService::where('package_plan_rate_category_id', $category_id)->where(
            'type',
            'service'
        )->orderBy('date_in', 'asc')->get()->toArray();
        // dd($services_dates); -- 4 items

        $data_services = [];
        $data_hotels = [];
        foreach ($services_dates as $service_date) {
            // dd($service_date);
            $services_rates = PackageServiceRate::where('package_service_id', $service_date["id"])->get()->toArray();
            // dd($services_rates);
            foreach ($services_rates as $service_rate) {
                $service_rate_plans = ServiceRatePlan::where('service_rate_id', $service_rate["service_rate_id"])
                    ->where('date_from', '<=', $service_date["date_in"])
                    ->where('date_to', '>=', $service_date["date_in"])
                    ->get()->toArray();
                // dd($service_rate_plans);
                $data_services[] = $this->getDynamicRateByService($service_rate_plans, $category_id);
            }
        }

        $hotels_dates = PackageService::where('package_plan_rate_category_id', $category_id)->where('type', 'hotel')
            ->orderBy('date_in', 'asc')->orderBy('order', 'asc')->get()->groupBy('date_in');

        // dd($hotels_dates);

        foreach ($hotels_dates as $date) {
            $simple_hotels = 0;
            $double_hotels = 0;
            $triple_hotels = 0;
            $package_service_id = $date[0]["id"];
            $date_in = $date[0]["date_in"];
            $date_out = Carbon::parse($date[0]["date_out"])->subDay(1)->format('Y-m-d');

            //Buscar Triple
            $exists_rate_triple = false;
            $rate_triple = 0;

            // Obtener rooms de Aurora (PackageServiceRoom)
            $rooms_aurora = PackageServiceRoom::where('package_service_id', $package_service_id)->with([
                'rate_plan_room' => function ($query) use ($date_in, $date_out) {
                    $query->with([
                        'calendarys' => function ($query) use ($date_in, $date_out) {
                            $query->where('date', '>=', $date_in);
                            $query->where('date', '<=', $date_out);
                            $query->with('rate');
                        },
                    ]);
                    $query->with('room.room_type');
                },
            ])->get();

            // Obtener rooms de Hyperguest (PackageServiceRoomHyperguest)
            $rooms_hyperguest = PackageServiceRoomHyperguest::with('room.room_type')
                ->where('package_service_id', $package_service_id)->get();

            // Buscar Simple (puede venir de Aurora o Hyperguest)
            $found_simple = false;
            foreach ($rooms_aurora as $room) {
                if ($room["rate_plan_room"]["room"]["room_type"]["occupation"] == 1) {
                    foreach ($room["rate_plan_room"]["calendarys"] as $calendar) {

                        $price = $calendar["rate"][0]["price_adult"];

                        if (!($price > 0)) {
                            $price = $calendar["rate"][0]["price_total"];
                        }

                        $simple_hotels += $price;
                    }
                    $found_simple = true;
                    break;
                }
            }
            if (!$found_simple) {
                foreach ($rooms_hyperguest as $room) {
                    if ($room["room"]["room_type"]["occupation"] == 1) {
                        $simple_hotels += $room["price_adult"];
                        break;
                    }
                }
            }

            // Buscar Doble (puede venir de Aurora o Hyperguest)
            $found_double = false;
            foreach ($rooms_aurora as $room) {
                if ($room["rate_plan_room"]["room"]["room_type"]["occupation"] == 2) {
                    foreach ($room["rate_plan_room"]["calendarys"] as $calendar) {

                        $price = $calendar["rate"][0]["price_adult"];

                        if (!($price > 0)) {
                            $price = $calendar["rate"][0]["price_total"];
                        }

                        $double_hotels += $price;
                    }
                    $found_double = true;
                    break;
                }
            }
            if (!$found_double) {
                foreach ($rooms_hyperguest as $room) {
                    if ($room["room"]["room_type"]["occupation"] == 2) {
                        $double_hotels += $room["price_adult"];
                        break;
                    }
                }
            }

            // Buscar Triple (puede venir de Aurora o Hyperguest)
            foreach ($rooms_aurora as $room) {
                if ($room["rate_plan_room"]["room"]["room_type"]["occupation"] == 3) {
                    $exists_rate_triple = true;
                    foreach ($room["rate_plan_room"]["calendarys"] as $calendar) {

                        $price = $calendar["rate"][0]["price_adult"];

                        if (!($price > 0)) {
                            $price = $calendar["rate"][0]["price_total"];
                        }

                        $price_extra = 0;
                        if ($calendar["rate"][0]["price_extra"] > 0) {
                            $price_extra = $calendar["rate"][0]["price_extra"];
                        }
                        $triple_hotels += $price + $price_extra;
                    }
                    break;
                }
            }

            if (!$exists_rate_triple) {
                foreach ($rooms_hyperguest as $room) {
                    if ($room["room"]["room_type"]["occupation"] == 3) {
                        $exists_rate_triple = true;
                        $triple_hotels += $room["price_adult"];
                        break;
                    }
                }
            }

            // Si no existe triple real, calcular como simple + doble
            if (!$exists_rate_triple) {
                // Usar los valores ya calculados de simple y doble
                $rate_triple = $simple_hotels + $double_hotels;

                $data_hotels[] = $this->getDynamicRateByHotel(
                    $simple_hotels,
                    $double_hotels,
                    $rate_triple,
                    $category_id
                );
            } else {
                $data_hotels[] = $this->getDynamicRateByHotel(
                    $simple_hotels,
                    $double_hotels,
                    $triple_hotels,
                    $category_id
                );
            }
        }

        $this->updateDynamicRatesPackage($data_services, $data_hotels);
    }

    public function updateDynamicRatesPackage($data_rate_services, $data_rate_hotels)
    {
        $rate_dynamic = [];

        foreach ($data_rate_services as $rate_service) {
            foreach ($rate_service as $key => $item) {
                if (isset($rate_dynamic[$key])) {
                    $rate_dynamic[$key] = [
                        'id' => $item['id'],
                        'simple' => $rate_dynamic[$key]['simple'] + $item['simple'],
                        'double' => $rate_dynamic[$key]['double'] + $item['double'],
                        'triple' => $rate_dynamic[$key]['triple'] + $item['triple'],
                    ];
                } else {
                    $rate_dynamic[$key] = [
                        'id' => $item['id'],
                        'simple' => $item['simple'],
                        'double' => $item['double'],
                        'triple' => $item['triple'],
                    ];
                }
            }
        }

        foreach ($data_rate_hotels as $rate_hotel) {
            foreach ($rate_hotel as $key => $item) {
                if (isset($rate_dynamic[$key])) {
                    $rate_dynamic[$key] = [
                        'id' => $item['id'],
                        'simple' => $rate_dynamic[$key]['simple'] + $item['simple'],
                        'double' => $rate_dynamic[$key]['double'] + $item['double'],
                        'triple' => $rate_dynamic[$key]['triple'] + $item['triple'],
                    ];
                } else {
                    $rate_dynamic[$key] = [
                        'id' => $item['id'],
                        'simple' => $item['simple'],
                        'double' => $item['double'],
                        'triple' => $item['triple'],
                    ];
                }
            }
        }

        foreach ($rate_dynamic as $item) {
            $packageDynamicRate = PackageDynamicRate::find($item['id']);
            if ($packageDynamicRate) {
                $packageDynamicRate->simple = $item['simple'];
                $packageDynamicRate->double = $item['double'];
                $packageDynamicRate->triple = $item['triple'];
                $packageDynamicRate->save();
            }
        }
    }

    public function getPackageChildrenByCategory($category_id)
    {
        $package_plan_rate_categories = PackagePlanRateCategory::where('id', $category_id)
            ->with([
                'plan_rate' => function ($query) {
                    $query->select(['id', 'package_id']);
                    $query->with([
                        'package' => function ($query) {
                            $query->select(['id', 'allow_child']);
                            $query->where('allow_child', 1);
                            $query->with([
                                'children' => function ($query) {
                                    $query->select([
                                        'package_id',
                                        'min_age',
                                        'max_age',
                                        'percentage',
                                        'has_bed',
                                    ]);
                                    $query->where('status', 1);
                                },
                            ]);
                        },
                    ]);
                    $query->first();
                },
            ])
            ->first(['id', 'package_plan_rate_id', 'type_class_id']);
        if ($package_plan_rate_categories) {
            return (isset($package_plan_rate_categories->plan_rate->package->children)) ? $package_plan_rate_categories->plan_rate->package->children : collect();
        } else {
            return collect();
        }
    }

    public function getPackageChildrenByPlanRate($package_plan_rate_id)
    {
        $package_plan_rate = PackagePlanRate::where('id', $package_plan_rate_id)
            ->with([
                'package' => function ($query) {
                    $query->select(['id', 'allow_child']);
                    $query->where('allow_child', 1);
                    $query->with([
                        'children' => function ($query) {
                            $query->select([
                                'package_id',
                                'min_age',
                                'max_age',
                                'percentage',
                                'has_bed',
                            ]);
                            $query->where('status', 1);
                        },
                    ]);
                },
            ])->first(['id', 'package_id']);
        if ($package_plan_rate) {
            return (isset($package_plan_rate->package->children)) ? $package_plan_rate->package->children : collect();
        } else {
            return collect();
        }
    }

    public function updateDynamicSaleRatesPackageChildren($category_id, $children_ages, $sale_id)
    {
        if ($children_ages->count() > 0) {
            //Todo obtenemos el porcentaje de niño con cama
            $percentage_with_bed = $children_ages->first(function ($value, $key) {
                return $value['has_bed'] === 1;
            });
            //Todo obtenemos el porcentaje de niño sin cama
            $percentage_without_bed = $children_ages->first(function ($value, $key) {
                return $value['has_bed'] === 0;
            });
            $rows_package_dynamic_rates = PackageDynamicSaleRate::where('package_plan_rate_category_id', $category_id)
                ->where('package_rate_sale_markup_id', $sale_id)
                ->where('pax_from', 2)
                ->where('pax_to', 2)->get([
                    'id',
                    'service_type_id',
                    'package_plan_rate_category_id',
                    'double',
                    'child_with_bed',
                    'child_without_bed',
                ]);

            foreach ($rows_package_dynamic_rates as $package_dynamic_rates) {
                $price_child_with_bed = 0;
                $price_child_without_bed = 0;
                if ($percentage_with_bed) {
                    $price_child_with_bed = ($package_dynamic_rates->double - (($package_dynamic_rates->double * $percentage_with_bed->percentage) / 100));
                }
                if ($percentage_without_bed) {
                    $price_child_without_bed = ($package_dynamic_rates->double - (($package_dynamic_rates->double * $percentage_without_bed->percentage) / 100));
                }

                $package_dynamic_rates = PackageDynamicSaleRate::find($package_dynamic_rates->id);
                $package_dynamic_rates->child_with_bed = round($price_child_with_bed, 2);
                $package_dynamic_rates->child_without_bed = round($price_child_without_bed, 2);
                $package_dynamic_rates->save();
            }
        }
    }

    public function calculatePricePackageCopy($category_id, $sale_id, $markup)
    {
        set_time_limit(0);
        $this->resetDynamicRateCopy($category_id);

        $services_dates = DB::table('package_services')->where(
            'package_plan_rate_category_id',
            $category_id
        )->where('type', 'service')
            ->whereNull('deleted_at')->orderBy('date_in', 'asc')
            ->get();

        foreach ($services_dates as $service_date) {
            $services_rates = DB::table('package_service_rates')->where(
                'package_service_id',
                $service_date->id
            )->whereNull('deleted_at')->get();

            foreach ($services_rates as $service_rate) {
                $service_rate_plans = DB::table('service_rate_plans')->where(
                    'service_rate_id',
                    $service_rate->service_rate_id
                )
                    ->where('date_from', '<=', $service_date->date_in)
                    ->where('date_to', '>=', $service_date->date_in)
                    ->whereNull('deleted_at')
                    ->get();

                $this->updateDynamicRateByServiceCopy(
                    $service_rate_plans,
                    $category_id,
                    $service_date->calculation_included,
                    $markup
                );
            }
        }
        $hotels_dates = PackageService::where('package_plan_rate_category_id', $category_id)->where('type', 'hotel')
            ->with('hotel')
            ->orderBy('date_in', 'desc')
            ->orderBy('order', 'asc')
            ->get()->groupBy('date_in');

        $hotels_with_errors = [];
        $hotels_with_errors_i = 0;

        foreach ($hotels_dates as $date) {
            $hotels_with_errors_i = count($hotels_with_errors);
            $hotels_with_errors[$hotels_with_errors_i] = array(
                "id" => $date[0]->id,
                "hotel_id" => $date[0]->hotel->id,
                "name" => $date[0]->hotel->name,
                "date_in" => $date[0]->date_in,
                "date_out" => $date[0]->date_out,
                "rooms" => [],
            );

            $simple_hotels = 0;
            $double_hotels = 0;
            $triple_hotels = 0;
            $package_service_id = $date[0]->id;
            $date_in = $date[0]->date_in;
            $date_out = Carbon::parse($date[0]->date_out)->subDay(1)->format('Y-m-d');

            // Buscar rooms de Aurora
            $rooms_aurora = PackageServiceRoom::where('package_service_id', $package_service_id)
                ->with([
                    'rate_plan_room' => function ($query) use ($date_in, $date_out) {
                        //                        $query->whereHas('rate_plan'); // *-**********
                        $query->with([
                            'calendarys' => function ($query) use ($date_in, $date_out) {
                                $query->where('date', '>=', $date_in);
                                $query->where('date', '<=', $date_out);
                                $query->whereNull('deleted_at');
                                $query->with('rate');
                            },
                        ]);
                        $query->with('room.room_type');
                    },
                ])
                ->get();

            // Buscar rooms de Hyperguest
            $rooms_hyperguest = PackageServiceRoomHyperguest::where('package_service_id', $package_service_id)
                ->with('room.room_type')
                ->get();

            // Determinar si usar solo Hyperguest o solo Aurora
            $isHyperguest = $rooms_aurora->isEmpty() && !$rooms_hyperguest->isEmpty();
            $rooms = $isHyperguest ? $rooms_hyperguest : $rooms_aurora;


            $rooms_i = 0;
            // Validar rooms de Aurora
            foreach ($rooms_aurora as $room) {
                $rooms_i = count($hotels_with_errors[$hotels_with_errors_i]['rooms']);

                $occupation = $room["rate_plan_room"]["room"]["room_type"]["occupation"];

                $hotels_with_errors[$hotels_with_errors_i]['rooms'][$rooms_i] = [
                    "id" => $room->id,
                    "occupation" => $occupation,
                    "calendarys" => [],
                ];

                foreach ($room["rate_plan_room"]["calendarys"] as $calendar) {
                    if (count($calendar["rate"]) === 0) {
                        array_push($hotels_with_errors[$hotels_with_errors_i]['rooms'][$rooms_i]["calendarys"], [
                            "id" => $calendar["id"],
                        ]);
                    }
                }

                if (
                    count($hotels_with_errors[$hotels_with_errors_i]['rooms'][$rooms_i]["calendarys"]) === 0 &&
                    count($room["rate_plan_room"]["calendarys"]) > 0
                ) {
                    unset($hotels_with_errors[$hotels_with_errors_i]['rooms'][$rooms_i]);
                }
            }

            // Hyperguest no tiene validación de calendarys, así que no agregamos errores

            if (count($hotels_with_errors[$hotels_with_errors_i]['rooms']) === 0 && (count($rooms_aurora) > 0 || count($rooms_hyperguest) > 0)) {
                unset($hotels_with_errors[$hotels_with_errors_i]);
            } else {
                break;
            }


            // Función auxiliar para calcular precios para PackageServiceRoom
            // $calculatePriceNormal = function($calendar, $calculationIncluded, $markup, $priceExtra = 0) {
            //     $basePrice = $calendar["rate"][0]["price_adult"] + $priceExtra;

            //     if ($calculationIncluded == 1) {
            //         return $basePrice;
            //     } else {
            //         return $basePrice + ($basePrice * ($markup / 100));
            //     }
            // };

            // Función auxiliar para calcular precios para PackageServiceRoomHyperguest
            $calculatePriceHyperguest = function ($room, $calculationIncluded, $markup, $nights) {
                $basePrice = $room["price_adult"] * $nights;

                if ($calculationIncluded == 1) {
                    return $basePrice;
                } else {
                    return $basePrice + ($basePrice * ($markup / 100));
                }
            };

            // Calcular número de noches
            $date_in_obj = Carbon::parse($date_in);
            $date_out_obj = Carbon::parse($date_out);
            $nights = $date_in_obj->diffInDays($date_out_obj) + 1;

            if ($isHyperguest) {
                $exists_rate_triple = false;

                // Buscar Simple: sumar precios de ambos canales
                foreach ($rooms_hyperguest as $room) {
                    $occupation = $room["room"]["room_type"]["occupation"];
                    if ($occupation == 1) {
                        $price = $calculatePriceHyperguest($room, $date[0]->calculation_included, $markup, $nights);
                        $simple_hotels += $price;
                        break;
                    }
                }
                foreach ($rooms_aurora as $room) {
                    if ($room["rate_plan_room"]["room"]["room_type"]["occupation"] == 1) {
                        foreach ($room["rate_plan_room"]["calendarys"] as $calendar) {
                            if (!($calendar["rate"][0]["price_adult"] > 0)) {
                                $calendar["rate"][0]["price_adult"] = $calendar["rate"][0]["price_total"];
                            }
                            if ($date[0]->calculation_included == 1) {
                                $simple_hotels += $calendar["rate"][0]["price_adult"];
                            } else {
                                $simple_hotels += ($calendar["rate"][0]["price_adult"] + ($calendar["rate"][0]["price_adult"] * ($markup / 100)));
                            }
                        }
                        break;
                    }
                }

                // Buscar Doble: sumar precios de ambos canales
                foreach ($rooms_hyperguest as $room) {
                    $occupation = $room["room"]["room_type"]["occupation"];
                    if ($occupation == 2) {
                        $price = $calculatePriceHyperguest($room, $date[0]->calculation_included, $markup, $nights);
                        $double_hotels += $price;
                        break;
                    }
                }
                foreach ($rooms_aurora as $room) {
                    if ($room["rate_plan_room"]["room"]["room_type"]["occupation"] == 2) {
                        foreach ($room["rate_plan_room"]["calendarys"] as $calendar) {
                            if (!($calendar["rate"][0]["price_adult"] > 0)) {
                                $calendar["rate"][0]["price_adult"] = $calendar["rate"][0]["price_total"];
                            }
                            if ($date[0]->calculation_included == 1) {
                                $double_hotels += $calendar["rate"][0]["price_adult"];
                            } else {
                                $double_hotels += ($calendar["rate"][0]["price_adult"] + ($calendar["rate"][0]["price_adult"] * ($markup / 100)));
                            }
                        }
                        break;
                    }
                }

                // Buscar Triple: sumar precios de ambos canales
                foreach ($rooms_hyperguest as $room) {
                    $occupation = $room["room"]["room_type"]["occupation"];
                    if ($occupation == 3) {
                        $exists_rate_triple = true;
                        $price = $calculatePriceHyperguest($room, $date[0]->calculation_included, $markup, $nights);
                        $triple_hotels += $price;
                        break;
                    }
                }
                foreach ($rooms_aurora as $room) {
                    if ($room["rate_plan_room"]["room"]["room_type"]["occupation"] == 3) {
                        $exists_rate_triple = true;
                        foreach ($room["rate_plan_room"]["calendarys"] as $calendar) {
                            if (!($calendar["rate"][0]["price_adult"] > 0)) {
                                $calendar["rate"][0]["price_adult"] = $calendar["rate"][0]["price_total"];
                            }
                            $price_extra = 0;
                            if ($calendar["rate"][0]["price_extra"] > 0) {
                                $price_extra = $calendar["rate"][0]["price_extra"];
                            }
                            if ($date[0]->calculation_included == 1) {
                                $triple_hotels += $calendar["rate"][0]["price_adult"] + $price_extra;
                            } else {
                                $triple_hotels += (($calendar["rate"][0]["price_adult"] + $price_extra) + (($calendar["rate"][0]["price_adult"] + $price_extra) * ($markup / 100)));
                            }
                        }
                        break;
                    }
                }

                if (!$exists_rate_triple) {
                    // Usar los valores ya calculados de simple y doble (que ya incluyen ambos canales)
                    $rate_triple = $simple_hotels + $double_hotels;

                    $this->updateDynamicRateByHotelCopy($simple_hotels, $double_hotels, $rate_triple, $category_id);
                } else {
                    $this->updateDynamicRateByHotelCopy($simple_hotels, $double_hotels, $triple_hotels, $category_id);
                }
            } else {
                //Buscar Simple: sumar precios de ambos canales
                foreach ($rooms_aurora as $room) {
                    if ($room["rate_plan_room"]["room"]["room_type"]["occupation"] == 1) {
                        foreach ($room["rate_plan_room"]["calendarys"] as $calendar) {
                            if (!($calendar["rate"][0]["price_adult"] > 0)) {
                                $calendar["rate"][0]["price_adult"] = $calendar["rate"][0]["price_total"];
                            }

                            if ($date[0]->calculation_included == 1) {
                                $simple_hotels += $calendar["rate"][0]["price_adult"];
                            } else {
                                $simple_hotels += ($calendar["rate"][0]["price_adult"] + ($calendar["rate"][0]["price_adult"] * ($markup / 100)));
                            }
                        }
                        break;
                    }
                }
                foreach ($rooms_hyperguest as $room) {
                    $occupation = $room["room"]["room_type"]["occupation"];
                    if ($occupation == 1) {
                        $price = $calculatePriceHyperguest($room, $date[0]->calculation_included, $markup, $nights);
                        $simple_hotels += $price;
                        break;
                    }
                }

                //Buscar Doble: sumar precios de ambos canales
                foreach ($rooms_aurora as $room) {
                    if ($room["rate_plan_room"]["room"]["room_type"]["occupation"] == 2) {
                        foreach ($room["rate_plan_room"]["calendarys"] as $calendar) {
                            if (!($calendar["rate"][0]["price_adult"] > 0)) {
                                $calendar["rate"][0]["price_adult"] = $calendar["rate"][0]["price_total"];
                            }

                            if ($date[0]->calculation_included == 1) {
                                $double_hotels += $calendar["rate"][0]["price_adult"];
                            } else {
                                $double_hotels += ($calendar["rate"][0]["price_adult"] + ($calendar["rate"][0]["price_adult"] * ($markup / 100)));
                            }
                        }
                        break;
                    }
                }
                foreach ($rooms_hyperguest as $room) {
                    $occupation = $room["room"]["room_type"]["occupation"];
                    if ($occupation == 2) {
                        $price = $calculatePriceHyperguest($room, $date[0]->calculation_included, $markup, $nights);
                        $double_hotels += $price;
                        break;
                    }
                }

                //Buscar Triple: sumar precios de ambos canales
                $exists_rate_triple = false;
                foreach ($rooms_aurora as $room) {
                    if ($room["rate_plan_room"]["room"]["room_type"]["occupation"] == 3) {
                        $exists_rate_triple = true;
                        foreach ($room["rate_plan_room"]["calendarys"] as $calendar) {
                            if (!($calendar["rate"][0]["price_adult"] > 0)) {
                                $calendar["rate"][0]["price_adult"] = $calendar["rate"][0]["price_total"];
                            }
                            $price_extra = 0;
                            if ($calendar["rate"][0]["price_extra"] > 0) {
                                $price_extra = $calendar["rate"][0]["price_extra"];
                            }
                            if ($date[0]->calculation_included == 1) {
                                $triple_hotels += $calendar["rate"][0]["price_adult"] + $price_extra;
                            } else {
                                $triple_hotels += (($calendar["rate"][0]["price_adult"] + $price_extra) + (($calendar["rate"][0]["price_adult"] + $price_extra) * ($markup / 100)));
                            }
                        }
                        break;
                    }
                }
                foreach ($rooms_hyperguest as $room) {
                    $occupation = $room["room"]["room_type"]["occupation"];
                    if ($occupation == 3) {
                        $exists_rate_triple = true;
                        $price = $calculatePriceHyperguest($room, $date[0]->calculation_included, $markup, $nights);
                        $triple_hotels += $price;
                        break;
                    }
                }

                if (!$exists_rate_triple) {
                    // Usar los valores ya calculados de simple y doble (que ya incluyen ambos canales)
                    $rate_triple = $simple_hotels + $double_hotels;

                    $this->updateDynamicRateByHotelCopy(
                        $simple_hotels,
                        $double_hotels,
                        $rate_triple,
                        $category_id
                    );
                } else {
                    $this->updateDynamicRateByHotelCopy(
                        $simple_hotels,
                        $double_hotels,
                        $triple_hotels,
                        $category_id
                    );
                }
            }
        }

        if (count($hotels_with_errors) > 0) {
            return ["success" => false, "errors" => $hotels_with_errors];
        }

        $packageRates = DB::table('package_dynamic_rate_copies')
            ->where('package_plan_rate_category_id', $category_id)
            ->whereNull('deleted_at')->get();
        foreach ($packageRates as $packageRate) {
            PackageDynamicSaleRate::where('package_rate_sale_markup_id', $sale_id)
                ->where('service_type_id', $packageRate->service_type_id)
                ->where('package_plan_rate_category_id', $packageRate->package_plan_rate_category_id)
                ->where('pax_from', $packageRate->pax_from)
                ->where('pax_to', $packageRate->pax_to)
                ->delete();
            $new = new PackageDynamicSaleRate();
            $new->service_type_id = $packageRate->service_type_id;
            $new->package_plan_rate_category_id = $packageRate->package_plan_rate_category_id;
            $new->pax_from = $packageRate->pax_from;
            $new->pax_to = $packageRate->pax_to;
            $new->simple = $packageRate->simple;
            $new->double = $packageRate->double;
            $new->triple = $packageRate->triple;
            $new->status = 1;
            $new->package_rate_sale_markup_id = $sale_id;
            $new->save();
        }

        DB::table('package_dynamic_rate_copies')->where('package_plan_rate_category_id', $category_id)->delete();

        return ["success" => true];
    }

    public function getDynamicRateByService($service_rate_plans, $category_id)
    {
        $package_rates_ranges = PackageDynamicRate::where('package_plan_rate_category_id', $category_id)
            ->whereNull('deleted_at')->get();

        // dd("Rangos de tarifas: ", $package_rates_ranges->toArray());

        $data_insert = [];

        foreach ($package_rates_ranges as $key => $rate_range) {
            $rate_range['simple'] = 0;
            $rate_range['double'] = 0;
            $rate_range['triple'] = 0;
            $data_insert[$rate_range['id']] = [
                "id" => $rate_range['id'],
                "simple" => 0,
                "double" => 0,
                "triple" => 0,
            ];
            if ($rate_range["pax_from"] == 1 && $rate_range["pax_to"] == 1) {
                foreach ($service_rate_plans as $rate_plan) {
                    if (($rate_plan["pax_from"] == 1 && $rate_plan["pax_to"] == 1) or ($rate_plan["pax_from"] <= 1 && $rate_plan["pax_to"] >= 1)) {
                        $rate_range["simple"] += $rate_plan["price_adult"];
                        $data_insert[$rate_range['id']] = [
                            'id' => $rate_range["id"],
                            'simple' => $rate_range["simple"],
                            'double' => 0,
                            'triple' => 0,
                        ];
                    }
                }
            }
        }

        foreach ($package_rates_ranges as $key => $rate_range) {
            $package_rates_ranges[$key]['simple'] = 0;
            $package_rates_ranges[$key]['double'] = 0;
            $package_rates_ranges[$key]['triple'] = 0;
            if ($rate_range["pax_from"] == 2 && $rate_range["pax_to"] == 2) {
                foreach ($service_rate_plans as $rate_plan) {
                    if (($rate_plan["pax_from"] == 2 && $rate_plan["pax_to"] == 2) or ($rate_plan["pax_from"] <= 2 && $rate_plan["pax_to"] >= 2)) {

                        $rate_range["double"] += $rate_plan["price_adult"];

                        $data_insert[$rate_range['id']] = [
                            'id' => $rate_range["id"],
                            'simple' => $rate_range["double"],
                            'double' => $rate_range["double"],
                            'triple' => 0,
                        ];
                    }
                }
            }
        }

        foreach ($package_rates_ranges as $key => $rate_range) {
            $package_rates_ranges[$key]['simple'] = 0;
            $package_rates_ranges[$key]['double'] = 0;
            $package_rates_ranges[$key]['triple'] = 0;
            if ($rate_range["pax_from"] == 3 && $rate_range["pax_to"] == 3) {
                foreach ($service_rate_plans as $rate_plan) {
                    if (($rate_plan["pax_from"] == 3 && $rate_plan["pax_to"] == 3) or ($rate_plan["pax_from"] <= 3 && $rate_plan["pax_to"] >= 3)) {
                        $rate_range["triple"] += $rate_plan["price_adult"];
                        $data_insert[$rate_range['id']] = [
                            'id' => $rate_range["id"],
                            'simple' => $rate_range["triple"],
                            'double' => $rate_range["triple"],
                            'triple' => $rate_range["triple"],
                        ];
                    }
                }
            }
        }

        foreach ($package_rates_ranges as $key => $rate_range) {
            $package_rates_ranges[$key]['simple'] = 0;
            $package_rates_ranges[$key]['double'] = 0;
            $package_rates_ranges[$key]['triple'] = 0;
            if ($rate_range["pax_from"] > 3) {
                $amount = 0;
                $count = 0;
                foreach ($service_rate_plans as $service_rate) {
                    if ($service_rate["pax_from"] >= $rate_range["pax_from"] && $service_rate["pax_to"] <= $rate_range["pax_to"]) {
                        $count += 1;
                        $amount += $service_rate["price_adult"];
                    }
                }
                if ($count != 0) {

                    $rate_range["simple"] += ($amount / $count);
                    $rate_range["double"] += ($amount / $count);
                    $rate_range["triple"] += ($amount / $count);

                    $data_insert[$rate_range['id']] = [
                        'id' => $rate_range["id"],
                        'simple' => $rate_range["simple"],
                        'double' => $rate_range["double"],
                        'triple' => $rate_range["triple"],
                    ];
                }
            }
        }

        return $data_insert;
    }

    public function updateDynamicRateByServiceCopy($service_rate_plans, $category_id, $calculation_included, $markup)
    {
        DB::transaction(function () use ($service_rate_plans, $category_id, $calculation_included, $markup) {

            $package_rates_ranges = DB::table('package_dynamic_rate_copies')->where(
                'package_plan_rate_category_id',
                $category_id
            )->whereNull('deleted_at')->get();
            //            $prices = [];
            foreach ($package_rates_ranges as $rate_range) {
                if ($rate_range->pax_from == 1 && $rate_range->pax_to == 1) {
                    foreach ($service_rate_plans as $rate_plan) {
                        if ($rate_plan->pax_from <= 1 && $rate_plan->pax_to >= 1) {
                            if ($calculation_included == 1) {
                                $rate_range->simple += $rate_plan->price_adult;
                            } else {
                                $rate_range->simple += ($rate_plan->price_adult + ($rate_plan->price_adult * ($markup / 100)));
                            }
                            //                            $prices[]['simple'] = $rate_range->simple;
                            DB::table('package_dynamic_rate_copies')->where('id', $rate_range->id)
                                ->update([
                                    'simple' => $rate_range->simple,
                                ]);
                        }
                    }
                }
            }

            foreach ($package_rates_ranges as $rate_range) {
                if ($rate_range->pax_from == 2 && $rate_range->pax_to == 2) {
                    foreach ($service_rate_plans as $rate_plan) {
                        if ($rate_plan->pax_from <= 2 && $rate_plan->pax_to >= 2) {
                            if ($calculation_included == 1) {
                                $rate_range->double += $rate_plan->price_adult;
                            } else {
                                $rate_range->double += ($rate_plan->price_adult + ($rate_plan->price_adult * ($markup / 100)));
                            }
                            //                            $prices[]['double'] = $rate_range->double;
                            DB::table('package_dynamic_rate_copies')->where('id', $rate_range->id)
                                ->update([
                                    'simple' => $rate_range->double,
                                    'double' => $rate_range->double,
                                ]);
                        }
                    }
                }
            }
            foreach ($package_rates_ranges as $rate_range) {
                if ($rate_range->pax_from == 3 && $rate_range->pax_to == 3) {
                    foreach ($service_rate_plans as $rate_plan) {
                        if ($rate_plan->pax_from <= 3 && $rate_plan->pax_to >= 3) {
                            if ($calculation_included == 1) {
                                $rate_range->triple += $rate_plan->price_adult;
                            } else {
                                $rate_range->triple += ($rate_plan->price_adult + ($rate_plan->price_adult * ($markup / 100)));
                            }
                            //                            $prices[]['triple'] = $rate_range->triple;
                            DB::table('package_dynamic_rate_copies')->where('id', $rate_range->id)
                                ->update([
                                    'simple' => $rate_range->triple,
                                    'double' => $rate_range->triple,
                                    'triple' => $rate_range->triple,
                                ]);
                        }
                    }
                }
            }
            foreach ($package_rates_ranges as $rate_range) {

                if ($rate_range->pax_from > 3) {
                    $amount = 0;
                    $count = 0;
                    foreach ($service_rate_plans as $service_rate) {
                        if ($service_rate->pax_from >= $rate_range->pax_from && $service_rate->pax_to <= $rate_range->pax_to) {
                            $count += 1;
                            $amount += $service_rate->price_adult;
                        }
                    }
                    if ($count != 0) {
                        if ($calculation_included == 1) {
                            $rate_range->simple += ($amount / $count);
                            $rate_range->double += ($amount / $count);
                            $rate_range->triple += ($amount / $count);
                        } else {
                            $rate_range->simple += (($amount / $count) + (($amount / $count) * ($markup / 100)));
                            $rate_range->double += (($amount / $count) + (($amount / $count) * ($markup / 100)));
                            $rate_range->triple += (($amount / $count) + (($amount / $count) * ($markup / 100)));
                        }
                        DB::table('package_dynamic_rate_copies')->where('id', $rate_range->id)
                            ->update([
                                'simple' => $rate_range->simple,
                                'double' => $rate_range->double,
                                'triple' => $rate_range->triple,
                            ]);
                    }
                }
            }

            //            return $prices;
        });
    }

    public function getDynamicRateByHotel($simple, $double, $triple, $category_id)
    {
        $package_rates_ranges = PackageDynamicRate::where('package_plan_rate_category_id', $category_id)
            ->whereNull('deleted_at')->get();
        $data_insert = [];

        foreach ($package_rates_ranges as $key => $rate_range) {
            $package_rates_ranges[$key]['simple'] = 0;
            $package_rates_ranges[$key]['double'] = 0;
            $package_rates_ranges[$key]['triple'] = 0;

            $data_insert[$rate_range['id']] = [];
            if ($rate_range["pax_from"] == 1 && $rate_range["pax_to"] == 1) {
                $rate_range["simple"] += $simple;
                $rate_range["double"] += 0;
                $rate_range["triple"] += 0;

                $data_insert[$rate_range['id']] = [
                    'id' => $rate_range["id"],
                    'simple' => $rate_range["simple"],
                    'double' => 0,
                    'triple' => 0,
                ];
            }
            if ($rate_range["pax_from"] == 2 && $rate_range["pax_to"] == 2) {
                $rate_range["simple"] += $simple;
                $rate_range["double"] += ($double / 2);
                $rate_range["triple"] += 0;

                $data_insert[$rate_range['id']] = [
                    'id' => $rate_range["id"],
                    'simple' => $rate_range["simple"],
                    'double' => $rate_range["double"],
                    'triple' => 0,
                ];
            }
            if (($rate_range["pax_from"] == 3 && $rate_range["pax_to"] == 3) || $rate_range["pax_from"] > 3) {
                if ($triple > 0) {
                    $rate_range["simple"] += $simple;
                    $rate_range["double"] += ($double / 2);
                    $rate_range["triple"] += ($triple / 3);
                } else {
                    $rate_range["simple"] += $simple;
                    $rate_range["double"] += ($double / 2);
                    $rate_range["triple"] += (($simple + $double) / 3);
                }

                $data_insert[$rate_range['id']] = [
                    'id' => $rate_range["id"],
                    'simple' => $rate_range["simple"],
                    'double' => $rate_range["double"],
                    'triple' => $rate_range["triple"],
                ];
            }
        }

        return $data_insert;
    }

    public function updateDynamicRateByHotelCopy($simple, $double, $triple, $category_id)
    {
        DB::transaction(function () use ($simple, $double, $triple, $category_id) {

            $package_rates_ranges = DB::table('package_dynamic_rate_copies')->where(
                'package_plan_rate_category_id',
                $category_id
            )->whereNull('deleted_at')->get();
            //            throw new \Exception(json_encode($package_rates_ranges));
            foreach ($package_rates_ranges as $rate_range) {
                if ($rate_range->pax_from == 1 && $rate_range->pax_to == 1) {
                    $rate_range->simple += $simple;
                    $rate_range->double += 0;
                    $rate_range->triple += 0;
                    DB::table('package_dynamic_rate_copies')->where('id', $rate_range->id)
                        ->update([
                            'simple' => $rate_range->simple,
                            'double' => 0,
                            'triple' => 0,
                        ]);
                }
                if ($rate_range->pax_from == 2 && $rate_range->pax_to == 2) {
                    $rate_range->simple += $simple;
                    $rate_range->double += ($double / 2);
                    $rate_range->triple += 0;
                    DB::table('package_dynamic_rate_copies')->where('id', $rate_range->id)
                        ->update([
                            'simple' => $rate_range->simple,
                            'double' => $rate_range->double,
                            'triple' => 0,
                        ]);
                }
                if (($rate_range->pax_from == 3 && $rate_range->pax_to == 3) || $rate_range->pax_from > 3) {
                    if ($triple > 0) {
                        $rate_range->simple += $simple;
                        $rate_range->double += ($double / 2);
                        $rate_range->triple += ($triple / 3);
                    } else {
                        $rate_range->simple += $simple;
                        $rate_range->double += ($double / 2);
                        $rate_range->triple += (($simple + $double) / 3);
                    }
                    DB::table('package_dynamic_rate_copies')->where('id', $rate_range->id)
                        ->update([
                            'simple' => $rate_range->simple,
                            'double' => $rate_range->double,
                            'triple' => $rate_range->triple,
                        ]);
                }
            }
        });
    }

    public function resetDynamicRate($category_id)
    {
        PackageDynamicRate::where('package_plan_rate_category_id', $category_id)
            ->update([
                'simple' => 0,
                'double' => 0,
                'triple' => 0,
            ]);
    }

    public function resetDynamicRateCopy($category_id)
    {
        DB::transaction(function () use ($category_id) {
            $package_dynamic_rates = DB::table('package_dynamic_rates')
                ->where('package_plan_rate_category_id', $category_id)
                ->whereNull('deleted_at')
                ->get();

            foreach ($package_dynamic_rates as $package_dynamic_rate) {
                DB::table('package_dynamic_rate_copies')->insert([
                    'service_type_id' => $package_dynamic_rate->service_type_id,
                    'package_plan_rate_category_id' => $package_dynamic_rate->package_plan_rate_category_id,
                    'pax_from' => $package_dynamic_rate->pax_from,
                    'pax_to' => $package_dynamic_rate->pax_to,
                    'simple' => $package_dynamic_rate->simple,
                    'double' => $package_dynamic_rate->double,
                    'triple' => $package_dynamic_rate->triple,
                    'created_at' => date("Y-m-d H:i:s"),
                ]);
            }
            DB::table('package_dynamic_rate_copies')->where('package_plan_rate_category_id', $category_id)
                ->update([
                    'simple' => 0,
                    'double' => 0,
                    'triple' => 0,
                ]);
        });
    }

    public function getDestinationPackage($package_id)
    {
        (new UpdatePackageDestinationsService)->execute($package_id);

        return true;
    }

    public function arr_unique($arr)
    {
        $single = [];
        $count = array_count_values($arr);
        foreach ($arr as $key => $value) {
            if ($count[$value] == 1) {
                $single[$value] = $value;
            }
        }
        return $single;
    }

    public function resetDestinationPackage($package_id)
    {
        PackageDestination::where('package_id', $package_id)->delete();
    }

    public function getServicesByPackage($package_plan_rate_category_id, $lang_iso)
    {
        $lang_iso = strtolower($lang_iso);
        $language = Language::where('iso', $lang_iso)->first();
        $languageId = 1;
        if ($language) {
            $languageId = $language->id;
        }
        $services = PackageService::select(
            'id',
            'object_id',
            'code_flight',
            'date_in',
            'date_out',
            'type',
            'order',
            'adult',
            'child',
            'infant',
            'single',
            'double',
            'triple',
            'origin',
            'destiny'
        )
            ->with([
                'service' => function ($query) use ($languageId) {
                    $query->select(
                        'id',
                        'aurora_code',
                        'equivalence_aurora',
                        'service_sub_category_id',
                        'unit_duration_id',
                        'unit_duration_reserve',
                        'qty_reserve_client',
                        'qty_reserve'
                    );
                    $query->with([
                        'serviceDestination.state.translations' => function ($query) use ($languageId) {
                            $query->where('type', 'state');
                            $query->where('language_id', $languageId);
                        },
                    ]);
                    $query->with([
                        'service_translations' => function ($query) use ($languageId) {
                            $query->select(['id', 'language_id', 'name', 'name_commercial', 'description', 'description_commercial', 'itinerary', 'summary', 'summary_commercial', 'link_trip_advisor', 'accommodation', 'service_id']);
                            $query->where('language_id', $languageId);
                        },
                    ]);
                    $query->with([
                        'service_translations_gtm' => function ($query) use ($languageId) {
                            $query->select('id', 'language_id', 'name', 'service_id');
                            $query->where('language_id', 2);
                        },
                    ]);
                    $query->with([
                        'serviceSubCategory' => function ($query) use ($languageId) {
                            $query->select(['id', 'service_category_id']);
                            $query->with([
                                'serviceCategories' => function ($query) use ($languageId) {
                                    $query->select(['id']);
                                    $query->with([
                                        'translations' => function ($query) use ($languageId) {
                                            $query->select('object_id', 'value');
                                            $query->where('type', 'servicecategory');
                                            $query->where('language_id', $languageId);
                                        },
                                    ]);
                                },
                            ]);
                            $query->with([
                                'translations' => function ($query) use ($languageId) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'servicesubcategory');
                                    $query->where('language_id', $languageId);
                                },
                            ]);
                        },
                    ]);
                },
            ])->with([
                'hotel' => function ($query) use ($languageId) {
                    $query->select('id', 'name', 'stars', 'country_id', 'state_id', 'city_id');
                    $query->with(['channel' => function ($query) {
                        $query->where('channel_id', 1);
                    }]);
                    $query->with([
                        'country.translations' => function ($query) use ($languageId) {
                            $query->where('type', 'country');
                            $query->where('language_id', $languageId);
                        },
                    ]);
                    $query->with([
                        'state.translations' => function ($query) use ($languageId) {
                            $query->where('type', 'state');
                            $query->where('language_id', $languageId);
                        },
                    ]);
                    $query->with([
                        'city.translations' => function ($query) use ($languageId) {
                            $query->where('type', 'city');
                            $query->where('language_id', $languageId);
                        },
                    ]);
                },
            ])->with(['service_rooms'])
            ->with(['service_rates'])
            ->where('package_plan_rate_category_id', $package_plan_rate_category_id)
            ->orderBy('date_in', 'asc')->orderBy('order', 'asc')->get()->toArray();

        return $services;
    }

    /**
     * @param $services
     * @param $date 2020-10-15
     * @param $update
     * @return array
     *
     */
    public function updateDateInServices($services, $date, $update)
    {
        $date_pivot_services = null;
        foreach ($services as $key => $service) {
            if ($key == 0) {
                if ($service['type'] === 'hotel') {
                    $date_pivot_services = $service["date_in"];
                    $date_service_in = Carbon::parse($service['date_in']);
                    $date_service_out = Carbon::parse($service['date_out']);
                    $nights = $date_service_in->diffInDays($date_service_out);
                    $date_out = Carbon::parse($date)->addDays($nights)->format('Y-m-d');
                    $services[$key]['date_in'] = $date;
                    $services[$key]['date_out'] = $date_out;
                    if ($update) {
                        $update_service = PackageService::find($services[$key]['id']);
                        $update_service->date_in = $date;
                        $update_service->date_out = $date_out;
                        $update_service->save();
                    }
                }
                if ($service['type'] === 'service' or $service['type'] === 'flight') {
                    $date_pivot_services = $service["date_in"];
                    $services[$key]['date_in'] = $date;
                    $services[$key]['date_out'] = $date;
                    if ($update) {
                        $update_service = PackageService::find($services[$key]['id']);
                        $update_service->date_in = $date;
                        $update_service->date_out = $date;
                        $update_service->save();
                    }
                }
            } else {
                $date_service_in = Carbon::parse($service['date_in']);
                $date_service_out = Carbon::parse($service['date_out']);
                $old_difference_days_service = Carbon::parse($date_pivot_services)->diffInDays($date_service_in);

                if ($service['type'] === 'hotel') {
                    $nights = $date_service_in->diffInDays($date_service_out);
                    $new_date_in_ = Carbon::parse($date)->addDays($old_difference_days_service)->format('Y-m-d');
                    $new_date_out_ = Carbon::parse($new_date_in_)->addDays($nights)->format('Y-m-d'); //--
                    $services[$key]['date_in'] = $new_date_in_;
                    $services[$key]['date_out'] = $new_date_out_;
                    if ($update) {
                        $update_service = PackageService::find($services[$key]['id']);
                        $update_service->date_in = $new_date_in_;
                        $update_service->date_out = $new_date_out_;
                        $update_service->save();
                    }
                }

                if ($service['type'] === 'service' or $service['type'] === 'flight') {
                    $new_date_in_ = Carbon::parse($date)->addDays($old_difference_days_service)->format('Y-m-d');
                    $services[$key]['date_in'] = $new_date_in_;
                    $services[$key]['date_out'] = $new_date_in_;
                    if ($update) {
                        $update_service = PackageService::find($services[$key]['id']);
                        $update_service->date_in = $new_date_in_;
                        $update_service->date_out = $new_date_in_;
                        $update_service->save();
                    }
                }
            }
        }
        return ["services" => $services, "date_new" => $services[count($services) - 1]["date_in"]];
    }

    public function getHotelsWithStatus(
        $services,
        $date,
        $room_quantity_sgl = 0,
        $room_quantity_dbl = 0,
        $room_quantity_tpl = 0
    ) {
        // Log::info($services);
        foreach ($services as $key => $service) {
            // Log::info(json_encode($service));
            if ($service['type'] === 'hotel') {
                $services[$key]['hotel']['on_request'] = 0;
                $services[$key]['hotel']['code'] = $services[$key]['object_id'];
                $date_service_in = Carbon::parse($service['date_in']);
                $date_service_out = Carbon::parse($service['date_out'])->subDay()->format('Y-m-d');
                $nights = Carbon::parse($service['date_in'])->diffInDays(Carbon::parse($service['date_out']));
                $rooms_status = collect();
                $count = 0;
                if (isset($service['service_rooms']) and count($service['service_rooms']) > 0) {
                    foreach ($service['service_rooms'] as $index => $rate_plan_room) {
                        $_rooms = $this->checkStatusHotel(
                            $rate_plan_room['rate_plan_room_id'],
                            $date_service_in,
                            $date_service_out,
                            $nights,
                            $room_quantity_sgl,
                            $room_quantity_dbl,
                            $room_quantity_tpl
                        );
                        if ($_rooms) {
                            $rooms_status->add($_rooms);
                        }
                    }

                    $on_request_hotel_rooms = collect();

                    if ($rooms_status->count() > 0) {
                        if ($room_quantity_sgl > 0) {
                            $sim = $rooms_status->filter(function ($room) {
                                return $room["occupation"] == 1 && $room["on_request"] == 1;
                            });
                            if ($sim->count() > 0) {
                                $on_request_hotel_rooms->add($sim);
                            }
                        }
                        if ($room_quantity_dbl > 0) {
                            $dbl = $rooms_status->filter(function ($room) {
                                return $room["occupation"] == 2 && $room["on_request"] == 1;
                            });
                            if ($dbl->count() > 0) {
                                $on_request_hotel_rooms->add($dbl);
                            }
                        }

                        if ($room_quantity_tpl > 0) {
                            $tpl = $rooms_status->filter(function ($room) {
                                return $room["occupation"] == 3 && $room["on_request"] == 1;
                            });
                            if ($tpl->count() > 0) {
                                $on_request_hotel_rooms->add($tpl);
                            }
                        }
                    }

                    // Log::info(['$on_request_hotel_rooms', $on_request_hotel_rooms]);
                    // if ($key == 6) {

                    if ($rooms_status->isEmpty()) {
                        $count = 1;
                    } else {
                        if ($on_request_hotel_rooms->count() > 0) {
                            $count = 1;
                        } else {
                            $count = 0;
                        }
                    }
                    // }

                    $services[$key]['hotel']['on_request'] = $count;
                }
            }
        }
        return $services;
    }

    public function checkStatusHotel(
        $rate_plan_room_id,
        $date_in,
        $date_out,
        $quantity_nights,
        $room_quantity_sgl,
        $room_quantity_dbl,
        $room_quantity_tpl
    ) {
        $on_request_room = 1; //RQ
        $quantity_rooms = 0;
        $rooms = [];
        $rate_plan_room = RatesPlansRooms::where('id', $rate_plan_room_id)->where('status', 1)->with('room.room_type')->first();

        if ($rate_plan_room != null) {
            if ($rate_plan_room->room->room_type->occupation == 1) {
                $quantity_rooms = $room_quantity_sgl;
            }
            if ($rate_plan_room->room->room_type->occupation == 2) {
                $quantity_rooms = $room_quantity_dbl;
            }
            if ($rate_plan_room->room->room_type->occupation == 3) {
                $quantity_rooms = $room_quantity_tpl;
            }

            if ($quantity_rooms > 0) {

                if ($rate_plan_room->bag == 1) {
                    $bag_rate = BagRate::where('rate_plan_rooms_id', $rate_plan_room->id)->first();
                    if ($bag_rate != null) {
                        $inventories = InventoryBag::where('date', '>=', $date_in)
                            ->where('date', '<=', $date_out)
                            ->where('locked', 0)
                            ->where('inventory_num', '>=', $quantity_rooms)
                            ->where('bag_room_id', $bag_rate->bag_room_id)->get();
                        if ($inventories->count() == $quantity_nights) {
                            $on_request_room = 0;
                        }
                    }
                }

                if ($rate_plan_room->bag == 0) {
                    $inventories = Inventory::where('date', '>=', $date_in)
                        ->where('date', '<=', $date_out)
                        ->where('locked', 0)
                        ->where('inventory_num', '>=', $quantity_rooms)
                        ->where('rate_plan_rooms_id', $rate_plan_room->id)->get();

                    if ($inventories->count() == $quantity_nights) {
                        $on_request_room = 0;
                    }
                }

                $rooms = [
                    "room_id" => $rate_plan_room->room_id,
                    "rate_plan_id" => $rate_plan_room->rates_plans_id,
                    "rate_plan_room_id" => $rate_plan_room->id,
                    "occupation" => $rate_plan_room->room->room_type->occupation,
                    "quantity_rooms" => $quantity_rooms,
                    "on_request" => $on_request_room,
                    "rate_plan_room" => $rate_plan_room,
                ];
            }
        }
        return $rooms;
    }

    public function getItineraryByService($services)
    {
        $services_by_day = [];
        $itinerary = [];
        foreach ($services as $service) {
            $services_date = $service['date_in'];
            $services_type = $service['type'];
            $services_descrip = '';
            $hotel_code = '';
            $hotel_name = '';
            $hotel_starts = '';
            $hotel_id = '';
            $on_request = '';
            $date_in = $service['date_in'];
            $date_out = '';
            $hotel_country = '';
            $hotel_state = '';
            $hotel_city = '';
            $package_service_id = '';
            $services_destiny = '';
            if ($service['type'] === 'service') {
                $services_descrip = $service['service']['service_translations'][0]['description'];
                $services_destiny = $service['service']['service_destination'][0]['state']['translations'][0]['value'];
            } elseif ($service['type'] === 'hotel') {
                $hotel_code = $service['hotel']['code'];
                $hotel_name = $service['hotel']['name'];
                $hotel_starts = $service['hotel']['stars'];
                $services_destiny = $service['hotel']['state']['translations'][0]['value'];
                $hotel_country = [
                    'id' => $service['hotel']['country_id'],
                    'iso' => $service['hotel']['country']['iso'],
                    'name' => $service['hotel']['country']['translations'][0]['value'],
                ];
                $hotel_state = [
                    'id' => $service['hotel']['state_id'],
                    'iso' => $service['hotel']['state']['iso'],
                    'name' => $service['hotel']['state']['translations'][0]['value'],
                ];
                $hotel_city = [
                    'id' => $service['hotel']['city_id'],
                    'iso' => $service['hotel']['city']['iso'],
                    'name' => $service['hotel']['city']['translations'][0]['value'],
                ];
                $on_request = $service['hotel']['on_request'];
                $date_in = $service['date_in'];
                $date_out = $service['date_out'];
                $package_service_id = $service['id'];
                $nights = (Carbon::parse($date_in)->diffInDays($date_out) == 0) ? 1 : Carbon::parse($date_in)->diffInDays($date_out);
                if ($nights > 1) {
                    for ($i = 1; $i < $nights; $i++) {
                        $date_new = Carbon::parse($date_in)->addDays($i)->format('Y-m-d');
                        $services_by_day[$date_new][] = [
                            'type' => $services_type,
                            'hotel_id' => $hotel_id,
                            'hotel_code' => $hotel_code,
                            'description' => $services_descrip,
                            'destinations' => $services_destiny,
                            'hotel' => $hotel_name,
                            'hotel_stars' => $hotel_starts,
                            'on_request' => $on_request,
                            'date_in' => $date_in,
                            'date_out' => $date_out,
                            'package_service_id' => $package_service_id,
                            'hotel_country' => $hotel_country,
                            'hotel_state' => $hotel_state,
                            'hotel_city' => $hotel_city,
                        ];
                    }
                }
            }

            $services_by_day[$services_date][] = [
                'type' => $services_type,
                'hotel_id' => $hotel_id,
                'hotel_code' => $hotel_code,
                'description' => $services_descrip,
                'destinations' => $services_destiny,
                'hotel' => $hotel_name,
                'hotel_stars' => $hotel_starts,
                'on_request' => $on_request,
                'date_in' => $services_date,
                'date_out' => $date_out,
                'package_service_id' => $package_service_id,
                'hotel_country' => $hotel_country,
                'hotel_state' => $hotel_state,
                'hotel_city' => $hotel_city,
            ];
        }

        $services_by_day = array_values($services_by_day);

        foreach ($services_by_day as $service_day) {
            $city = [];
            $city_name = '';
            $hotel = [];
            $services_description = [];
            foreach ($service_day as $day) {
                $city[] = $day['destinations'];
                $city_name = implode(' - ', array_unique($city));
                if ($day['type'] === 'hotel') {
                    $hotel[] = [
                        'id' => $day['hotel_id'],
                        'code' => $day['hotel_code'],
                        'name' => $day['hotel'],
                        'original' => $day['hotel'],
                        'replace' => '',
                        'stars' => $day['hotel_stars'],
                        'on_request' => 1,
                        'date_in' => $day['date_in'],
                        'date_out' => $day['date_out'],
                        'country' => $day['hotel_country'],
                        'state' => $day['hotel_state'],
                        'city' => $day['hotel_city'],
                        'package_service_id' => $day['package_service_id'],
                    ];
                } else {
                    $services_description[] = trim($day['description']);
                }
            }
            $date_ = (count($service_day) > 0) ? $service_day[0]['date_in'] : null;
            $itinerary[] = [
                'destinations' => $city_name,
                'date_in' => $date_,
                'description' => $services_description,
                'hotel' => $hotel,
            ];
        }
        return $itinerary;
    }

    public function getMinPriceDynamicSaleRates($saleRates, $plan_rate_category_id, $service_type_id, $field, $rangePax)
    {
        $minPrice = null;
        $plant_rates_dynamic = $saleRates->filter(function ($value) use (
            $plan_rate_category_id,
            $service_type_id
        ) {
            return $value->package_plan_rate_category_id == $plan_rate_category_id and $value->service_type_id == $service_type_id;
        });

        $minPrice = $plant_rates_dynamic->where('pax_from', '<=', $rangePax)->where(
            'pax_to',
            '>=',
            $rangePax
        )->where($field, '>', 0)->min($field);

        return $minPrice;
    }

    public function getPricePerAccommodation(
        $saleRates,
        $plan_rate_categories,
        $service_type_id,
        $room_quantity_sgl,
        $room_quantity_dbl,
        $room_quantity_tpl,
        $enable_fixed_prices,
        $markup
    ) {
        $room_prices = collect();
        //Todo Si tiene precios fijos y esta habilitado la opcion enable_fixed_prices
        if ($enable_fixed_prices) {
            foreach ($saleRates as $category) {
                if ($category['sale_rates_fixed'] != null) {
                    if (count($category['sale_rates_fixed']) == 0) {
                        continue;
                    }

                    $sale_rates_fixed = $category['sale_rates_fixed'][0];

                    $room_sgl = 0;
                    $room_dbl = 0;
                    $room_tpl = 0;
                    if ($room_quantity_sgl > 0) {
                        $room_sgl = $sale_rates_fixed->simple + (($sale_rates_fixed->simple * $markup) / 100);
                        $room_sgl = (float)roundLito($room_sgl);
                    }

                    if ($room_quantity_dbl > 0) {
                        $room_dbl = $sale_rates_fixed->double + (($sale_rates_fixed->double * $markup) / 100);
                        $room_dbl = (float)roundLito($room_dbl);
                    }

                    if ($room_quantity_tpl > 0) {
                        $room_tpl = $sale_rates_fixed->triple + (($sale_rates_fixed->triple * $markup) / 100);
                        $room_tpl = (float)roundLito($room_tpl);
                    }

                    $room_prices->add([
                        "plan_rate_category_id" => $sale_rates_fixed['package_plan_rate_category_id'],
                        "category_id" => $category['type_class_id'],
                        "room_sgl" => $room_sgl,
                        "room_dbl" => $room_dbl,
                        "room_tpl" => $room_tpl,
                        "room_total" => ($room_sgl + $room_dbl + $room_tpl),
                    ]);
                }
            }
        } else {
            foreach ($plan_rate_categories as $category) {
                $plant_rates_dynamic = $saleRates->filter(function ($value) use (
                    $category,
                    $service_type_id
                ) {
                    return $value->package_plan_rate_category_id == $category['id'] and $value->service_type_id == $service_type_id;
                })->first();
                if ($plant_rates_dynamic) {
                    $room_sgl = 0;
                    $room_dbl = 0;
                    $room_tpl = 0;

                    if ($room_quantity_sgl > 0) {
                        $room_sgl = (float)roundLito($plant_rates_dynamic->simple);
                    }

                    if ($room_quantity_dbl > 0) {
                        $room_dbl = (float)roundLito($plant_rates_dynamic->double);
                    }

                    if ($room_quantity_tpl > 0) {
                        $room_tpl = (float)roundLito($plant_rates_dynamic->triple);
                    }
                    $room_prices->add([
                        "plan_rate_category_id" => $plant_rates_dynamic['package_plan_rate_category_id'],
                        "category_id" => $plant_rates_dynamic['plan_rate_category']['type_class_id'],
                        "room_sgl" => $room_sgl,
                        "room_dbl" => $room_dbl,
                        "room_tpl" => $room_tpl,
                        "room_total" => ($room_sgl + $room_dbl + $room_tpl),
                    ]);
                }
            }
        }
        //Todo Obtenemos el precio mas bajo entre todas las categorias
        if ($room_prices->count() > 0) {
            $room_prices = $room_prices->where('room_total', $room_prices->min('room_total'))->first();
        } else {
            $room_prices = [
                "plan_rate_category_id" => 0,
                "category_id" => 0,
                "room_sgl" => 0,
                "room_dbl" => 0,
                "room_tpl" => 0,
                "room_total" => 0,
            ];
        }

        return $room_prices;
    }

    public function getTotalPackage(
        $saleRates,
        $room_quantity_sgl,
        $room_quantity_dbl,
        $room_quantity_tpl,
        $adult,
        $room_quantity_child_dbl,
        $room_quantity_child_tpl,
        $child_with_bed = 0
    ) {
        $total_sgl = 0;
        $total_dbl = 0;
        $total_tpl = 0;
        if ($saleRates) {
            if ($room_quantity_sgl > 0) {
                $total_sgl = $saleRates['room_sgl'] * $room_quantity_sgl;
            }
            if ($room_quantity_dbl > 0 and $child_with_bed === 0) {
                $total_dbl = $saleRates['room_dbl'] * (2 * $room_quantity_dbl);
            } elseif ($room_quantity_dbl > 0 and $child_with_bed > 0) {
                $total_dbl = $saleRates['room_dbl'] * (($room_quantity_dbl * 2) - $room_quantity_child_dbl);
            }

            if ($room_quantity_tpl > 0 and $child_with_bed === 0) {
                $total_tpl = $saleRates['room_tpl'] * (3 * $room_quantity_tpl);
            } elseif ($room_quantity_tpl > 0 and $child_with_bed > 0) {
                $total_tpl = $saleRates['room_tpl'] * (($room_quantity_tpl * 3) - $room_quantity_child_tpl);
            }
        }

        return $total_sgl + $total_dbl + $total_tpl;
    }

    private function getInformationPackages($services, $lang)
    {
        $language_id = Language::where('iso', $lang)->where('state', 1)->first();
        if ($language_id) {
            $services = (new \App\Package)->getInformationPackages($services, $language_id->id);
        } else {
            throw new \Exception('Language not found');
        }
        return $services;
    }

    /**
     * @param $year
     * @param $package_rate_sale_markup_id
     * @param $package_rate_sale_markup_markup
     * @param $package_plan_rate_category_id
     * @param $service_type_id
     * @return array|\Illuminate\Support\Collection
     */
    public function get_dynamic_sale_rates_by_year(
        $plan_rate_id,
        $year,
        $package_rate_sale_markup_id,
        $package_rate_sale_markup_index,
        $package_rate_sale_markup_markup,
        $package_plan_rate_category_id,
        $service_type_id
    ) {

        // Verificar si los servicios de esta categoría son del año ingresado, sino un proceso en trait
        $first_package_service = PackageService::where('package_plan_rate_category_id', $package_plan_rate_category_id)
            ->orderBy('date_in', 'asc')->orderBy('order', 'asc')->first();
        $rates = [];
        if (!(session()->has('rate_errors'))) {
            session()->put('rate_errors', []);
        }

        $first_date = "";

        if ($first_package_service) {
            $first_date = $first_package_service->date_in;

            // Las tarifas dinámicas, si alteran deberían mostrarse estas
            $rates = PackageDynamicSaleRate::where('package_rate_sale_markup_id', $package_rate_sale_markup_id)
                ->where('package_plan_rate_category_id', $package_plan_rate_category_id)
                ->where('service_type_id', $service_type_id)
                ->get([
                    'id',
                    'service_type_id',
                    'package_plan_rate_category_id',
                    'pax_from',
                    'pax_to',
                    'simple',
                    'double',
                    'triple',
                    'child_with_bed',
                    'child_without_bed',
                    'status',
                    'package_rate_sale_markup_id',
                ]);

            if (count($rates) === 0) {
                $old_difference_years_service = $year - Carbon::parse($first_date)->year;
                $new_date_in = Carbon::parse($first_date)->addYears($old_difference_years_service)->format('Y-m-d');

                $services_ = PackageService::where('package_plan_rate_category_id', $package_plan_rate_category_id)
                    ->orderBy('date_in', 'asc')
                    ->orderBy('order', 'asc')->get([
                        'id',
                        'type',
                        'package_plan_rate_category_id',
                        'object_id',
                        'order',
                        'calculation_included',
                        'date_in',
                        'date_out',
                        'adult',
                        'child',
                        'infant',
                        'single',
                        'double',
                        'triple',
                        're_entry',
                        'code_flight',
                        'origin',
                        'destiny',
                    ]);
                //Todo Un método para reordenar segun nueva fecha de inicio, indicar si actualiza o no en un boolean
                $services_new_dates = $this->updateDateInServices($services_, $new_date_in, false);

                //Todo Seguido de un metodo para list_services_with_unique_hotel_by_date
                $services_ = $this->list_services_with_unique_hotel_by_date($services_new_dates['services']);

                $this->resetDynamicRateCopy($package_plan_rate_category_id); // * COPY

                foreach ($services_ as $service_date) {
                    if ($service_date->type == 'service') {
                        $services_rates = DB::table('package_service_rates')->where(
                            'package_service_id',
                            $service_date->id
                        )->whereNull('deleted_at')->get();

                        foreach ($services_rates as $service_rate) {
                            $service_rate_plans = DB::table('service_rate_plans')->where(
                                'service_rate_id',
                                $service_rate->service_rate_id
                            )
                                ->where('date_from', '<=', $service_date->date_in)
                                ->where('date_to', '>=', $service_date->date_in)
                                ->whereNull('deleted_at')
                                ->get();
                            if (count($service_rate_plans) === 0) {
                                $this->put_session_rate_errors(
                                    $package_rate_sale_markup_index,
                                    $plan_rate_id,
                                    $package_plan_rate_category_id,
                                    $service_date,
                                    "Tarifas no encontradas plan tarifario (" . $service_rate->service_rate_id . ")"
                                );
                            } else {
                                $this->updateDynamicRateByServiceCopy(
                                    $service_rate_plans,
                                    $package_plan_rate_category_id,
                                    $service_date->calculation_included,
                                    $package_rate_sale_markup_markup
                                );
                            }
                        }
                    }
                }

                foreach ($services_ as $date) {
                    if ($date->type == 'hotel') {
                        $simple_hotels = 0;
                        $double_hotels = 0;
                        $triple_hotels = 0;
                        $package_service_id = $date->id;
                        $date_in = $date->date_in;
                        $date_out = Carbon::parse($date->date_out)->subDay(1)->format('Y-m-d');

                        $package_service_rooms = DB::table('package_service_rooms')
                            ->whereNull('package_service_rooms.deleted_at')
                            ->where('package_service_id', $package_service_id)
                            ->join(
                                'rates_plans_rooms',
                                'package_service_rooms.rate_plan_room_id',
                                '=',
                                'rates_plans_rooms.id'
                            )
                            ->join('rooms', 'rates_plans_rooms.room_id', '=', 'rooms.id')
                            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
                            ->get();
                        //Buscar Simple
                        foreach ($package_service_rooms as $package_service_room) {

                            if ($package_service_room->occupation == 1) {
                                $calendars = RatesPlansCalendarys::where(
                                    'rates_plans_room_id',
                                    $package_service_room->rate_plan_room_id
                                )
                                    ->where('date', '<=', $date_out)
                                    ->where('date', '>=', $date_in)
                                    ->get();
                                //                                return print_r( json_encode( $calendars ) ); die;
                                foreach ($calendars as $calendar) {
                                    $rate = DB::table('rates')->whereNull('deleted_at')
                                        ->where('rates_plans_calendarys_id', $calendar->id)->first();
                                    if ($rate) {
                                        if ($date->calculation_included == 1) {
                                            $simple_hotels += $rate->price_adult;
                                        } else {
                                            $simple_hotels += ($rate->price_adult + ($rate->price_adult * ($package_rate_sale_markup_markup / 100)));
                                        }
                                    } else { //-------ERRORES DEBUG -------------------------------------------------------
                                        $this->put_session_rate_errors(
                                            $package_rate_sale_markup_index,
                                            $plan_rate_id,
                                            $package_plan_rate_category_id,
                                            $date,
                                            "Tarifas no asociada para el calendario(" . $calendar->id . "), en el tipo de hab. 1"
                                        );
                                        break;
                                    }
                                }

                                //-------ERRORES DEBUG -------------------------------------------------------
                                if (count($calendars) === 0) {
                                    $this->put_session_rate_errors(
                                        $package_rate_sale_markup_index,
                                        $plan_rate_id,
                                        $package_plan_rate_category_id,
                                        $date,
                                        "Tarifas no encontradas para el tipo de hab. 1"
                                    );
                                }
                                //-------ERRORES DEBUG --------------------------------------------------------
                                break;
                            }
                        }

                        //Buscar Doble
                        foreach ($package_service_rooms as $package_service_room) {

                            if ($package_service_room->occupation == 2) {
                                $calendars = DB::table('rates_plans_calendarys')
                                    ->where('rates_plans_room_id', $package_service_room->rate_plan_room_id)
                                    ->whereNull('deleted_at')
                                    ->where('date', '>=', $date_in)
                                    ->where('date', '<=', $date_out)
                                    ->get();

                                foreach ($calendars as $calendar) {
                                    $rate = DB::table('rates')->whereNull('deleted_at')->where(
                                        'rates_plans_calendarys_id',
                                        $calendar->id
                                    )->first();
                                    if ($rate) {
                                        if ($date->calculation_included == 1) {
                                            $double_hotels += $rate->price_adult;
                                        } else {
                                            $double_hotels += ($rate->price_adult + ($rate->price_adult * ($package_rate_sale_markup_markup / 100)));
                                        }
                                    } else { //-------ERRORES DEBUG -------------------------------------------------------
                                        $this->put_session_rate_errors(
                                            $package_rate_sale_markup_index,
                                            $plan_rate_id,
                                            $package_plan_rate_category_id,
                                            $date,
                                            "Tarifas no asociada para el calendario(" . $calendar->id . "), en el tipo de hab. 2"
                                        );
                                        break;
                                    }
                                }
                                //-------ERRORES DEBUG -------------------------------------------------------
                                if (count($calendars) === 0) {
                                    $this->put_session_rate_errors(
                                        $package_rate_sale_markup_index,
                                        $plan_rate_id,
                                        $package_plan_rate_category_id,
                                        $date,
                                        "Tarifas no encontradas para el tipo de hab. 2"
                                    );
                                }
                                //-------ERRORES DEBUG --------------------------------------------------------
                                break;
                            }
                        }
                        //Buscar Triple
                        foreach ($package_service_rooms as $package_service_room) {

                            if ($package_service_room->occupation == 3) {
                                $calendars = DB::table('rates_plans_calendarys')
                                    ->where('rates_plans_room_id', $package_service_room->rate_plan_room_id)
                                    ->whereNull('deleted_at')
                                    ->where('date', '>=', $date_in)
                                    ->where('date', '<=', $date_out)
                                    ->get();

                                foreach ($calendars as $calendar) {
                                    $rate = DB::table('rates')->whereNull('deleted_at')
                                        ->where('rates_plans_calendarys_id', $calendar->id)->first();
                                    if ($rate) {
                                        if ($date->calculation_included == 1) {
                                            $triple_hotels += $rate->price_adult + $rate->price_extra;
                                        } else {
                                            $price_ = $rate->price_adult + $rate->price_extra;
                                            $triple_hotels += ($price_ + ($price_ * ($package_rate_sale_markup_markup / 100)));
                                        }
                                    } else { //-------ERRORES DEBUG -------------------------------------------------------
                                        $this->put_session_rate_errors(
                                            $package_rate_sale_markup_index,
                                            $plan_rate_id,
                                            $package_plan_rate_category_id,
                                            $date,
                                            "Tarifas no asociada para el calendario(" . $calendar->id . "), en el tipo de hab. 3"
                                        );
                                        break;
                                    }
                                }

                                //-------ERRORES DEBUG -------------------------------------------------------
                                if (count($calendars) === 0) {
                                    $this->put_session_rate_errors(
                                        $package_rate_sale_markup_index,
                                        $plan_rate_id,
                                        $package_plan_rate_category_id,
                                        $date,
                                        "Tarifas no encontradas para el tipo de hab. 3"
                                    );
                                }
                                //-------ERRORES DEBUG --------------------------------------------------------
                                break;
                            }
                        }
                        $this->updateDynamicRateByHotelCopy(
                            $simple_hotels,
                            $double_hotels,
                            $triple_hotels,
                            $package_plan_rate_category_id
                        );
                    }
                }

                $rates = DB::table('package_dynamic_rate_copies')->whereNull('deleted_at')
                    ->where('package_plan_rate_category_id', $package_plan_rate_category_id)->get();

                DB::table('package_dynamic_rate_copies')
                    ->where('package_plan_rate_category_id', $package_plan_rate_category_id)->delete();
            } else {
                //Todo validamos servicios y hoteles que tengan tarifas
                $old_difference_years_service = $year - Carbon::parse($first_date)->year;
                $new_date_in = Carbon::parse($first_date)->addYears($old_difference_years_service)->format('Y-m-d');

                $services_ = PackageService::where('package_plan_rate_category_id', $package_plan_rate_category_id)
                    ->orderBy('date_in', 'asc')
                    ->orderBy('order', 'asc')->get([
                        'id',
                        'type',
                        'package_plan_rate_category_id',
                        'object_id',
                        'order',
                        'calculation_included',
                        'date_in',
                        'date_out',
                        'adult',
                        'child',
                        'infant',
                        'single',
                        'double',
                        'triple',
                        're_entry',
                        'code_flight',
                        'origin',
                        'destiny',
                    ]);
                //Todo Un método para reordenar segun nueva fecha de inicio, indicar si actualiza o no en un boolean
                $services_new_dates = $this->updateDateInServices($services_, $new_date_in, false);

                //Todo Seguido de un metodo para list_services_with_unique_hotel_by_date
                $services_ = $this->list_services_with_unique_hotel_by_date($services_new_dates['services']);

                foreach ($services_ as $service_date) {
                    //Todo validamos los servicios
                    if ($service_date->type == 'service') {
                        $services_rates = DB::table('package_service_rates')->where(
                            'package_service_id',
                            $service_date->id
                        )->whereNull('deleted_at')->get([
                            'id',
                            'package_service_id',
                            'service_rate_id',
                        ]);

                        foreach ($services_rates as $service_rate) {
                            $service_rate_plans = DB::table('service_rate_plans')->where(
                                'service_rate_id',
                                $service_rate->service_rate_id
                            )
                                ->where('date_from', '<=', $service_date->date_in)
                                ->where('date_to', '>=', $service_date->date_in)
                                ->whereNull('deleted_at')
                                ->get(['id', 'service_cancellation_policy_id', 'service_rate_id']);
                            if (count($service_rate_plans) === 0) {
                                $this->put_session_rate_errors(
                                    $package_rate_sale_markup_index,
                                    $plan_rate_id,
                                    $package_plan_rate_category_id,
                                    $service_date,
                                    "Tarifas no encontradas plan tarifario (" . $service_rate->service_rate_id . ")"
                                );
                            }
                        }
                    }
                    //Todo validamos los hoteles
                    foreach ($services_ as $date) {
                        if ($date->type == 'hotel') {
                            $package_service_id = $date->id;
                            $date_in = $date->date_in;
                            $date_out = Carbon::parse($date->date_out)->subDay(1)->format('Y-m-d');

                            $package_service_rooms = DB::table('package_service_rooms')
                                ->whereNull('package_service_rooms.deleted_at')
                                ->where('package_service_id', $package_service_id)
                                ->join(
                                    'rates_plans_rooms',
                                    'package_service_rooms.rate_plan_room_id',
                                    '=',
                                    'rates_plans_rooms.id'
                                )
                                ->join('rooms', 'rates_plans_rooms.room_id', '=', 'rooms.id')
                                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
                                ->get([
                                    'package_service_rooms.id',
                                    'package_service_rooms.rate_plan_room_id',
                                    'package_service_rooms.package_service_id',
                                    'rates_plans_rooms.rates_plans_id',
                                    'room_types.occupation',
                                ]);
                            //Todo Buscar Simple
                            foreach ($package_service_rooms as $package_service_room) {
                                if ($package_service_room->occupation == 1) {
                                    $calendars = RatesPlansCalendarys::where(
                                        'rates_plans_room_id',
                                        $package_service_room->rate_plan_room_id
                                    )
                                        ->where('date', '<=', $date_out)
                                        ->where('date', '>=', $date_in)
                                        ->get(['id']);
                                    foreach ($calendars as $calendar) {
                                        $rate = DB::table('rates')->whereNull('deleted_at')
                                            ->where('rates_plans_calendarys_id', $calendar->id)->first(['id']);
                                        if (!$rate) {
                                            $this->put_session_rate_errors(
                                                $package_rate_sale_markup_index,
                                                $plan_rate_id,
                                                $package_plan_rate_category_id,
                                                $date,
                                                "Tarifas no asociada para el calendario(" . $calendar->id . "), en el tipo de hab. 1"
                                            );
                                            break;
                                        }
                                    }

                                    if (count($calendars) === 0) {
                                        $this->put_session_rate_errors(
                                            $package_rate_sale_markup_index,
                                            $plan_rate_id,
                                            $package_plan_rate_category_id,
                                            $date,
                                            "Tarifas no encontradas para el tipo de hab. 1"
                                        );
                                    }
                                    break;
                                }
                            }

                            //Todo Buscar Doble
                            foreach ($package_service_rooms as $package_service_room) {

                                if ($package_service_room->occupation == 2) {
                                    $calendars = DB::table('rates_plans_calendarys')
                                        ->where('rates_plans_room_id', $package_service_room->rate_plan_room_id)
                                        ->whereNull('deleted_at')
                                        ->where('date', '>=', $date_in)
                                        ->where('date', '<=', $date_out)
                                        ->get(['id']);

                                    foreach ($calendars as $calendar) {
                                        $rate = DB::table('rates')->whereNull('deleted_at')
                                            ->where('rates_plans_calendarys_id', $calendar->id)->first(['id']);
                                        if (!$rate) {
                                            $this->put_session_rate_errors(
                                                $package_rate_sale_markup_index,
                                                $plan_rate_id,
                                                $package_plan_rate_category_id,
                                                $date,
                                                "Tarifas no asociada para el calendario(" . $calendar->id . "), en el tipo de hab. 2"
                                            );
                                            break;
                                        }
                                    }
                                    if (count($calendars) === 0) {
                                        $this->put_session_rate_errors(
                                            $package_rate_sale_markup_index,
                                            $plan_rate_id,
                                            $package_plan_rate_category_id,
                                            $date,
                                            "Tarifas no encontradas para el tipo de hab. 2"
                                        );
                                    }
                                    break;
                                }
                            }
                            //Todo Buscar Triple
                            foreach ($package_service_rooms as $package_service_room) {
                                if ($package_service_room->occupation == 3) {
                                    $calendars = DB::table('rates_plans_calendarys')
                                        ->where('rates_plans_room_id', $package_service_room->rate_plan_room_id)
                                        ->whereNull('deleted_at')
                                        ->where('date', '>=', $date_in)
                                        ->where('date', '<=', $date_out)
                                        ->get(['id']);

                                    foreach ($calendars as $calendar) {
                                        $rate = DB::table('rates')->whereNull('deleted_at')
                                            ->where('rates_plans_calendarys_id', $calendar->id)->first(['id']);
                                        if (!$rate) {
                                            $this->put_session_rate_errors(
                                                $package_rate_sale_markup_index,
                                                $plan_rate_id,
                                                $package_plan_rate_category_id,
                                                $date,
                                                "Tarifas no asociada para el calendario(" . $calendar->id . "), en el tipo de hab. 3"
                                            );
                                            break;
                                        }
                                    }

                                    if (count($calendars) === 0) {
                                        $this->put_session_rate_errors(
                                            $package_rate_sale_markup_index,
                                            $plan_rate_id,
                                            $package_plan_rate_category_id,
                                            $date,
                                            "Tarifas no encontradas para el tipo de hab. 3"
                                        );
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $rates;
    }

    /**
     * @param $package_rate_sale_markup_index
     * @param $plan_rate_id
     * @param $package_plan_rate_category_id
     * @param $package_service
     * @param $message
     */
    public function put_session_rate_errors(
        $package_rate_sale_markup_index,
        $plan_rate_id,
        $package_plan_rate_category_id,
        $package_service,
        $message
    ) {

        if ($package_rate_sale_markup_index > 0) {
            return;
        }

        if (!(session()->has('rate_errors.' . $plan_rate_id))) {
            session()->put('rate_errors.' . $plan_rate_id, []);
        }
        $category_id_found = "";
        foreach (session()->get('rate_errors.' . $plan_rate_id) as $kc => $category_error) {
            if ($category_error['id'] === $package_plan_rate_category_id) {
                $category_id_found = $kc;
                break;
            }
        }

        if ($package_service->type === 'hotel') {
            $service_ = PackageService::where('id', $package_service->id)
                ->with(['hotel.channel'])->first();
            $code = $service_->hotel->channel[0]->code;
            $name = $service_->hotel->name;
        } else { // service
            $service_ = PackageService::where('id', $package_service->id)
                ->with(['service'])->first();
            $code = $service_->service->aurora_code;
            $name = $service_->service->name;
        }

        if ($category_id_found !== "") {
            session()->push(
                "rate_errors.$plan_rate_id.$category_id_found" . '.services',
                [
                    "id" => $package_service->id,
                    "type" => $package_service->type,
                    "code" => $code,
                    "name" => $name,
                    "date_in" => $package_service->date_in,
                    "date_out" => $package_service->date_out,
                    "error" => $message,
                ]
            );
        } else {
            $category_ = PackagePlanRateCategory::where('id', $package_plan_rate_category_id)
                ->with([
                    'type_class.translations' => function ($query1) {
                        $query1->where('language_id', 1);
                    },
                ])->first();
            session()->push('rate_errors.' . $plan_rate_id, [
                "id" => $package_plan_rate_category_id,
                "name" => $category_->type_class->translations[0]->value,
                "services" => [
                    [
                        "id" => $package_service->id,
                        "type" => $package_service->type,
                        "code" => $code,
                        "name" => $name,
                        "date_in" => $package_service->date_in,
                        "date_out" => $package_service->date_out,
                        "error" => $message,
                    ],
                ],
            ]);
        }
    }

    /** Depura los hoteles alternativos, solo devuelve uno por fecha de inicio del hotel en toda la lista
     * @param $services
     * @return array
     */
    private function list_services_with_unique_hotel_by_date($services)
    {

        $list_unique_hotels_array = [];
        $list_unique_hotels = [];

        foreach ($services as $service) {
            if ($service['type'] === 'hotel') {
                if (!(isset($list_unique_hotels_array[$service['date_in']]))) {
                    array_push($list_unique_hotels, $service);
                    $list_unique_hotels_array[$service['date_in']] = true;
                }
            } else {
                array_push($list_unique_hotels, $service);
            }
        }

        return $list_unique_hotels;
    }

    private function regularize_sale_ranges_in_category($package_plan_rate_category_id)
    {
        try {
            $package_dynamic_rates_pax_froms = PackageDynamicRate::where(
                'package_plan_rate_category_id',
                $package_plan_rate_category_id
            )
                ->pluck('pax_from');

            PackageDynamicSaleRate::where('package_plan_rate_category_id', $package_plan_rate_category_id)
                ->whereNotIn('pax_from', $package_dynamic_rates_pax_froms)->delete();

            return ["success" => true, "message" => "Regularizado correctamente"];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e];
        }
    }

    private function get_service_uses($service_id, $type)
    {
        $package_services_ids = PackageService::where('object_id', $service_id)
            ->where('type', $type)
            ->pluck('id');

        $packages = Packages::whereHas(
            'plan_rates.plan_rate_categories.services',
            function ($query) use ($package_services_ids) {
                $query->whereIn('id', $package_services_ids);
            }
        )
            ->with([
                'plan_rates' => function ($query) use ($package_services_ids) {
                    $query->with(['service_type']);
                    $query->with([
                        'plan_rate_categories' => function ($query) use ($package_services_ids) {
                            $query->withCount([
                                'services' => function ($query2) use ($package_services_ids) {
                                    $query2->whereIn("id", $package_services_ids);
                                },
                            ]);
                            $query->with([
                                'category' => function ($query) {
                                    $query->with([
                                        'translations' => function ($q) {
                                            $q->where('type', 'typeclass');
                                            $q->where('language_id', 1);
                                        },
                                    ]);
                                },
                            ]);
                        },
                    ]);
                },
            ])
            ->with([
                'translations' => function ($query) {
                    $query->where('language_id', 1);
                },
            ])
            ->where('status', 1)
            ->get();

        return $packages;
    }

    private function get_room_uses($room_id)
    {

        $rate_plan_room_ids = DB::table('rates_plans_rooms')->where('room_id', $room_id)->pluck('id');

        $package_service_rooms_package_service_ids = DB::table('package_service_rooms')
            ->whereIn('rate_plan_room_id', $rate_plan_room_ids)
            ->pluck('package_service_id');

        $package_service_rooms_package_service_hyperguest_ids = DB::table('package_service_rooms_hyperguest')
            ->where('room_id', $room_id)
            ->pluck('package_service_id');

        $package_service_rooms_all_ids = $package_service_rooms_package_service_ids->merge(
            $package_service_rooms_package_service_hyperguest_ids
        )->unique();

        $packages = Packages::select('id', 'code', 'extension')
            ->whereHas(
                'plan_rates.plan_rate_categories.services',
                function ($query) use ($package_service_rooms_all_ids) {
                    $query->whereIn('id', $package_service_rooms_all_ids);
                }
            )
            ->with([
                'plan_rates' => function ($query) use ($package_service_rooms_all_ids) {
                    $query->with(['service_type']);
                    $query->with([
                        'plan_rate_categories' => function ($query) use ($package_service_rooms_all_ids) {
                            $query->with([
                                'services' => function ($query2) use ($package_service_rooms_all_ids) {
                                    $query2->select(
                                        'id',
                                        'type',
                                        'package_plan_rate_category_id',
                                        'object_id',
                                        'date_in',
                                        'date_out',
                                        'order'
                                    );
                                    $query2->where("type", "hotel");
                                    $query2->whereIn("id", $package_service_rooms_all_ids);
                                },
                            ]);
                            $query->with([
                                'category' => function ($query) {
                                    $query->with([
                                        'translations' => function ($q) {
                                            $q->where('type', 'typeclass');
                                            $q->where('language_id', 1);
                                        },
                                    ]);
                                },
                            ]);
                        },
                    ]);
                },
            ])
            ->with([
                'translations' => function ($query) {
                    $query->where('language_id', 1);
                },
            ])
            ->where('status', 1)
            ->get();

        return $packages;
    }

    private function get_rate_plan_uses($rate_plan_id)
    {

        $rate_plan_room_ids = DB::table('rates_plans_rooms')->where('rates_plans_id', $rate_plan_id)->pluck('id');

        $package_service_rooms_package_service_ids = DB::table('package_service_rooms')
            ->whereIn('rate_plan_room_id', $rate_plan_room_ids)
            ->pluck('package_service_id');

        $package_service_rooms_package_service_hyperguest_ids = DB::table('package_service_rooms_hyperguest')
            ->whereIn('rate_plan_id', [$rate_plan_id])
            ->pluck('package_service_id');

        $package_service_rooms_package_service_ids = $package_service_rooms_package_service_ids->merge(
            $package_service_rooms_package_service_hyperguest_ids
        )->unique();

        $packages = Packages::select('id', 'code', 'extension')
            ->whereHas(
                'plan_rates.plan_rate_categories.services',
                function ($query) use ($package_service_rooms_package_service_ids) {
                    $query->whereIn('id', $package_service_rooms_package_service_ids);
                }
            )
            ->with([
                'plan_rates' => function ($query) use ($package_service_rooms_package_service_ids) {
                    $query->with(['service_type']);
                    $query->with([
                        'plan_rate_categories' => function ($query) use ($package_service_rooms_package_service_ids) {
                            $query->with([
                                'services' => function ($query2) use ($package_service_rooms_package_service_ids) {
                                    $query2->select(
                                        'id',
                                        'type',
                                        'package_plan_rate_category_id',
                                        'object_id',
                                        'date_in',
                                        'date_out',
                                        'order'
                                    );
                                    $query2->where("type", "hotel");
                                    $query2->whereIn("id", $package_service_rooms_package_service_ids);
                                },
                            ]);
                            $query->with([
                                'category' => function ($query) {
                                    $query->with([
                                        'translations' => function ($q) {
                                            $q->where('type', 'typeclass');
                                            $q->where('language_id', 1);
                                        },
                                    ]);
                                },
                            ]);
                        },
                    ]);
                },
            ])
            ->with([
                'translations' => function ($query) {
                    $query->where('language_id', 1);
                },
            ])
            ->where('status', 1)
            ->get();

        return $packages;
    }

    public function transformDataPackage(
        $packages,
        $date_from,
        $adult,
        $child_with_bed,
        $child_without_bed,
        $type_class,
        $language = 'es',
        $cancellation_policy = null,
        $type_service = 1,
        $token_search = '',
        $room_quantity_sgl = 0,
        $room_quantity_dbl = 0,
        $room_quantity_tpl = 0,
        $room_quantity_child_dbl = 0,
        $room_quantity_child_tpl = 0,
        $filter = null,
        $gtm = false
    ) {
        // 1) Filtro por nombre / descripción (igual que antes, pero movido a helper)
        if (!empty($filter)) {
            $packages = $this->filterPackagesByText($packages, $filter);
        }

        $count_packages = $packages->count();
        if ($count_packages === 0) {
            return $packages;
        }

        // Evitar query por paquete: resolvemos el idioma una sola vez
        $languageModel = Language::where('iso', $language)->first();
        $language_id = $languageModel ? $languageModel->id : null;

        $yearFromDate = Carbon::parse($date_from)->format('Y');

        // 2) Transformar cada paquete
        $packages = $packages->transform(function ($package) use (
            $date_from,
            $adult,
            $child_with_bed,
            $child_without_bed,
            $type_class,
            $language_id,
            $cancellation_policy,
            $type_service,
            $token_search,
            $count_packages,
            $room_quantity_sgl,
            $room_quantity_dbl,
            $room_quantity_tpl,
            $room_quantity_child_dbl,
            $room_quantity_child_tpl,
            $gtm,
            $yearFromDate,
            $language
        ) {
            // Itinerario
            $itinerary = $this->buildItineraryFromPackage($package);

            // Inclusiones (incluye / no incluye)
            $inclusions = $this->buildInclusionsFromPackage($package);

            // Configuración de niños
            [$allows_child, $children_ages, $age_adult_from] = $this->buildChildrenConfigFromPackage($package);

            // Servicios incluidos, hoteles e info GTM (solo si hay 1 paquete)
            $included_services = $this->buildDefaultIncludedServices();
            $itinerary_hotels = collect();
            $flights = collect();
            $services_update_dates = ['services' => []];
            $servicesForGtm = [];

            if ($count_packages === 1) {
                [
                    $included_services,
                    $services_update_dates,
                    $itinerary_hotels,
                    $flights,
                    $servicesForGtm
                ] = $this->buildServicesInfoForSinglePackage(
                    $package,
                    $language,
                    $date_from,
                    $room_quantity_sgl,
                    $room_quantity_dbl,
                    $room_quantity_tpl,
                    $adult,
                    $child_with_bed,
                    $child_without_bed,
                    $itinerary,
                    $gtm
                );
            }

            // Tipos de servicio (plan_rates_service_type) por año
            [$services_types, $available] = $this->getServiceTypesForYear(
                $package,
                $date_from,
                $yearFromDate
            );

            // Armar estructura base del paquete (todas las keys originales)
            $packagesArray = $this->buildBasePackageArray(
                $package,
                $language_id,
                $date_from,
                $allows_child,
                $age_adult_from,
                $children_ages,
                $inclusions,
                $included_services,
                $itinerary,
                $itinerary_hotels,
                $services_update_dates['services'],
                $flights,
                $token_search,
                $servicesForGtm,
                $services_types,
                $adult,
                $child_with_bed,
                $child_without_bed,
                $available
            );

            // Políticas de cancelación
            $packagesArray = $this->applyCancellationPolicyToPackage(
                $packagesArray,
                $cancellation_policy,
                $date_from
            );

            // Configuración de "available_from" por cliente
            $packagesArray = $this->applyClientPackageSettingsToPackage(
                $packagesArray,
                $package
            );

            // Precios, disponibilidad por inventario, ofertas, etc.
            $packagesArray = $this->applyPricingAndAvailabilityToPackage(
                $packagesArray,
                $package,
                $date_from,
                $adult,
                $child_with_bed,
                $child_without_bed,
                $type_class,
                $type_service,
                $room_quantity_sgl,
                $room_quantity_dbl,
                $room_quantity_tpl,
                $room_quantity_child_dbl,
                $room_quantity_child_tpl
            );

            return $packagesArray;
        });

        // 3) Agregar descripción de días de schedule
        $packages = $packages->transform(function ($package) {
            return $this->addScheduleDescriptionToPackage($package);
        });

        return $packages;
    }

    function filterPackagesByText($packages, $filter)
    {
        // Normalizar el filtro a un array de términos
        if (is_array($filter)) {
            // Limpiamos espacios y eliminamos vacíos
            $terms = array_values(array_filter(
                array_map('trim', $filter),
                function ($v) {
                    return $v !== '';
                }
            ));
        } else {
            // Filtro enviado como texto plano
            $filter = trim((string) $filter);
            $terms = $filter !== '' ? [$filter] : [];
        }

        // Si no hay términos válidos, no filtramos nada
        if (empty($terms)) {
            return $packages->values();
        }

        $packages = $packages->filter(function ($package) use ($terms) {
            // Defensivo: asegurar que existan las traducciones
            $translation = $package->translations[0] ?? null;
            $name        = $translation->tradename ?? '';
            $description = $translation->description_commercial ?? '';

            $bestOrder = null;

            foreach ($terms as $term) {
                // Evitar procesar términos vacíos por seguridad
                if ($term === '') {
                    continue;
                }

                // Buscar en el nombre
                if (stripos($name, $term) !== false) {
                    // order_name = 0 si matchea por nombre
                    $candidateOrder = 0;
                    if ($bestOrder === null || $candidateOrder < $bestOrder) {
                        $bestOrder = $candidateOrder;
                    }
                }

                // Buscar en la descripción
                if (stripos($description, $term) !== false) {
                    // order_name = 1 si matchea por descripción
                    $candidateOrder = 1;
                    if ($bestOrder === null || $candidateOrder < $bestOrder) {
                        $bestOrder = $candidateOrder;
                    }
                }
            }

            if ($bestOrder !== null) {
                // Asignamos la prioridad de orden encontrada
                $package->order_name = $bestOrder;
                return true; // mantener el paquete
            }

            // No encontró ninguna coincidencia con ningún término
            return false;
        });

        if ($packages->count() > 0) {
            $packages = $packages->sortBy('order_name')->values();
        }

        return $packages;
    }


    protected function buildItineraryFromPackage($package)
    {
        $itinerary = [];

        $itinerary_text = strip_tags(
            htmlDecode(
                htmlspecialchars_decode($package->translations[0]->itinerary_commercial, ENT_QUOTES)
            )
        );

        $count = substr_count($itinerary_text, "#");

        if ($count > 0) {
            $textExplode = explode("#", $itinerary_text);
            for ($j = 0; $j < $count; $j++) {
                $texto = substr($textExplode[$j + 1], 1);
                $itinerary[] = [
                    'day' => $j + 1,
                    'description' => ltrim($texto),
                ];
            }
        } else {
            if (!empty($itinerary_text)) {
                $itinerary[] = [
                    'day' => 1,
                    'description' => ltrim($itinerary_text),
                ];
            }
        }

        return $itinerary;
    }

    protected function buildInclusionsFromPackage($package)
    {
        $inclusions = [
            "include" => [],
            "no_include" => [],
        ];

        $inclusion_data = $package->inclusions;

        foreach ($inclusion_data as $inclusion) {
            $translationValue = (count($inclusion['inclusions']['translations']) > 0)
                ? $inclusion['inclusions']['translations'][0]['value']
                : '';

            if ($inclusion['include']) {
                $inclusions['include'][] = ['name' => $translationValue];
            } else {
                $inclusions['no_include'][] = ['name' => $translationValue];
            }
        }

        return $inclusions;
    }

    protected function buildChildrenConfigFromPackage($package)
    {
        $allows_child = 0;
        $children_ages = [
            'with_bed' => [
                'min_age' => 0,
                'max_age' => 0,
            ],
            'without_bed' => [
                'min_age' => 0,
                'max_age' => 0,
            ],
        ];

        $age_adult_from = 0;

        if ($package['allow_child'] && count($package['children']) > 0) {
            $allows_child = 1;

            foreach ($package['children'] as $item) {
                if ((bool)$item->has_bed) {
                    $children_ages['with_bed']['min_age'] = $item->min_age;
                    $children_ages['with_bed']['max_age'] = $item->max_age;
                } else {
                    $children_ages['without_bed']['min_age'] = $item->min_age;
                    $children_ages['without_bed']['max_age'] = $item->max_age;
                }
            }

            $age_max = $package['children']->max('max_age');
            $age_adult_from = (!empty($age_max) && $age_max > 0) ? ($age_max + 1) : 0;
        }

        return [$allows_child, $children_ages, $age_adult_from];
    }

    protected function buildDefaultIncludedServices()
    {
        return [
            'breakfast_days' => true,
            'accommodation' => [],
            'lunch_days' => [],
            'dinner_days' => [],
            'transport' => false,
            'includes_flights' => false,
            'include_guides_tickets' => [
                'guides' => true,
                'tickets' => false,
            ],
        ];
    }

    protected function buildServicesInfoForSinglePackage(
        $package,
        $language,
        $date_from,
        $room_quantity_sgl,
        $room_quantity_dbl,
        $room_quantity_tpl,
        $adult,
        $child_with_bed,
        $child_without_bed,
        $itinerary,
        $gtm
    ) {
        $included_services = $this->buildDefaultIncludedServices();
        $services_update_dates = ['services' => []];
        $itinerary_hotels = collect();
        $flights = collect();
        $servicesForGtm = [];

        $package_plan_rate_category_id = $package['plan_rates'][0]['plan_rate_categories'][0]['id'];

        $services = $this->getServicesByPackage($package_plan_rate_category_id, $language);

        if ($gtm && Auth::user() && Auth::user()->user_type_id == 4) {
            $servicesForGtm = $this->getServicesForGtm($package, $services);
        }

        $services_update_dates = $this->updateDateInServices($services, $date_from, false);

        $services = $this->getHotelsWithStatus(
            $services_update_dates['services'],
            $date_from,
            $room_quantity_sgl,
            $room_quantity_dbl,
            $room_quantity_tpl
        );

        $flights = $this->getFlights(
            $services,
            $adult,
            ($child_with_bed + $child_without_bed)
        );

        $itinerary_hotels = $this->addHotelInItineraryDescription(
            $services,
            $itinerary
        );

        $transformToDays = $this->transformServicesGroupByDay($services);

        $included_services['breakfast_days'] = true;
        $included_services['accommodation'] = $this->getAccommodationByCity($transformToDays);
        $included_services['lunch_days'] = $this->getFoodDays($transformToDays, 'ALMUERZO');
        $included_services['dinner_days'] = $this->getFoodDays($transformToDays, 'CENA');
        $included_services['includes_flights'] = $this->verifyHasFlights($services);
        $included_services['include_guides_tickets']['tickets'] = $this->verifyHasTickets($transformToDays);
        $included_services['transport'] = $this->verifyHasTransport($transformToDays);

        return [
            $included_services,
            $services_update_dates,
            $itinerary_hotels,
            $flights,
            $servicesForGtm,
        ];
    }

    protected function getServiceTypesForYear($package, $date_from, $yearFromDate)
    {
        $services_types_query = $package->plan_rates_service_type->filter(function ($query) use ($yearFromDate) {
            $year_quote = Carbon::parse($query->date_from)->format('Y');
            return $year_quote === $yearFromDate;
        });

        if ($services_types_query->count() === 0) {
            $services_types_query = $package->plan_rates_service_type->filter(function ($query) use ($yearFromDate) {
                $year_quote = Carbon::parse($query->date_to)->format('Y');
                return $year_quote === $yearFromDate;
            });
        }

        $services_types = $services_types_query->where('status', 1)->values();

        $available = $services_types->count() > 0;

        return [$services_types, $available];
    }

    protected function buildBasePackageArray(
        $package,
        $language_id,
        $date_from,
        $allows_child,
        $age_adult_from,
        array $children_ages,
        array $inclusions,
        array $included_services,
        array $itinerary,
        $itinerary_hotels,
        $services,
        $flights,
        $token_search,
        $servicesForGtm,
        $services_types,
        $adult,
        $child_with_bed,
        $child_without_bed,
        $available
    ) {
        $paxTotal = ($adult + $child_with_bed + $child_without_bed);

        $packages = [
            'id' => $package['id'],
            'language_id' => $language_id,
            'date_reserve' => $date_from,
            'country_id' => $package['country_id'],
            'code' => $package['code'],
            'extension' => $package['extension'],
            'nights' => $package['nights'],
            'portada_link' => $package['portada_link'],
            'map_link' => $package['map_link'],
            'map_itinerary_link' => $package['map_itinerary_link'],
            'image_link' => $package['image_link'],
            'status' => $package['status'],
            'reference' => $package['reference'],
            'rate_type' => $package['rate_type'],
            'rate_dynamic' => $package['rate_dynamic'],
            'allow_guide' => $package['allow_guide'],
            'allow_child' => $allows_child,
            'allow_infant' => $package['allow_infant'],
            'limit_confirmation_hours' => $package['limit_confirmation_hours'],
            'infant_min_age' => $package['infant_min_age'],
            'infant_max_age' => $package['infant_max_age'],
            'adult_age_from' => $age_adult_from,
            'physical_intensity' => (empty($package['physical_intensity'])) ? null : $package['physical_intensity'],
            'tag_id' => $package['tag_id'],
            'allow_modify' => $package['allow_modify'],
            'free_sale' => $package['free_sale'],
            'recommended' => $package['recommended'],
            'destinations' => $package['destinations'],
            'services' => $services,
            'inclusions' => $inclusions,
            'included_services' => $included_services,
            'itinerary' => $itinerary,
            'itinerary_hotels' => $itinerary_hotels,
            'child_age_allowed' => $children_ages,
            'total_amount' => 0,
            'total_adults' => 0,
            'price_per_adult' => [
                'room_sgl' => 0,
                'room_dbl' => 0,
                'room_tpl' => 0,
            ],
            'price_per_person' => 0,
            'without_discount' => 0,
            'offer' => false,
            'favorite' => false,
            'available' => $available,
            'offer_value' => 0,
            'rated' => ($package->rated->count() > 0) ? $package->rated[0]->rated : 0,
            'package_destinations' => $package->package_destinations,
            'tag' => $package->tag,
            'translations' => $package->translations,
            'translations_gtm' => $package->translations_gtm,
            'itineraries' => $package->itineraries,
            'itineraries_all' => $package->itineraries_all,
            'services_gtm' => $servicesForGtm,
            'fixed_outputs' => $package->fixed_outputs,
            'galleries' => $package->galleries,
            'highlights' => $package->highlights,
            'service_types' => $services_types,
            'plan_rates' => $package->plan_rates,
            'quantity_adult' => $adult,
            'quantity_child' => [
                'quantity_children' => $child_with_bed + $child_without_bed,
                'with_bed' => $child_with_bed,
                'without_bed' => $child_without_bed,
            ],
            'extension_recommended' => $package->extension_recommended,
            'available_from' => [],
            'prices_children' => [
                'with_bed' => [
                    'price' => 0,
                    'min_age' => 0,
                    'max_age' => 0,
                ],
                'without_bed' => [
                    'price' => 0,
                    'min_age' => 0,
                    'max_age' => 0,
                ],
            ],
            'total_children' => [
                'with_bed' => 0,
                'without_bed' => 0,
            ],
            'price_per_child' => [
                'with_bed' => 0,
                'without_bed' => 0,
            ],
            'cancellation_policy' => [],
            'flights' => $flights,
            'token_search' => $token_search,
            'schedules' => $package->schedules,
            'schedule_days' => '',
        ];

        $packages['available_from'] = [
            'from' => 0,
            'unit_duration' => '',
        ];

        return $packages;
    }

    protected function applyCancellationPolicyToPackage(array $packages, $cancellation_policy, $date_from)
    {
        if (empty($cancellation_policy) || $cancellation_policy->count() === 0) {
            return $packages;
        }

        $applies_cancellation_fees = false;
        $cancellation_fees = 0;
        $last_day_cancel = null;

        $reservation_days = difDateDays(Carbon::now(), Carbon::parse($date_from));

        if ((int)$reservation_days <= $cancellation_policy[0]->day_to) {
            $applies_cancellation_fees = true;
            $cancellation_fees = $cancellation_policy[0]->cancellation_fees;
        } else {
            $last_day = subDateDays(
                Carbon::parse($date_from),
                $cancellation_policy[0]->day_to,
                'Y-m-d'
            );
            $day = Carbon::parse($last_day)->dayOfWeek;
            $last_day_cancel = $last_day;

            // sábado
            if ($day === 6) {
                $last_day_cancel = subDateDays(Carbon::parse($last_day), 1)->format('Y-m-d');
            }
            // domingo
            if ($day === 0) {
                $last_day_cancel = subDateDays(Carbon::parse($last_day), 2)->format('Y-m-d');
            }
        }

        $packages['cancellation_policy'] = [
            'applies_cancellation_fees' => $applies_cancellation_fees,
            'cancellation_fees' => $cancellation_fees,
            'last_day_cancel' => $last_day_cancel,
        ];

        return $packages;
    }

    protected function applyClientPackageSettingsToPackage(array $packages, $package)
    {
        if ($package->client_package_setting->count() > 0) {
            $packages['available_from'] = [
                'from' => $package->client_package_setting[0]->reservation_from,
                'unit_duration' => ($package->client_package_setting[0]->unit_duration_reserve == 2)
                    ? 'days'
                    : 'hours',
            ];
        }

        return $packages;
    }

    protected function applyPricingAndAvailabilityToPackage(
        array $packages,
        $package,
        $date_from,
        $adult,
        $child_with_bed,
        $child_without_bed,
        $type_class,
        $type_service,
        $room_quantity_sgl,
        $room_quantity_dbl,
        $room_quantity_tpl,
        $room_quantity_child_dbl,
        $room_quantity_child_tpl
    ) {
        $paxTotal = ($adult + $child_with_bed + $child_without_bed);
        $package_rate_sale_markup_id = '';

        // Tipo de servicio: all => filtro por rango de fechas
        if ($type_service === 'all') {
            $plan_rates = $package["plan_rates"]->filter(function ($query) use ($date_from) {
                return $query['date_from'] <= $date_from && $query['date_to'] >= $date_from;
            })->values();

            $firstPlanRate = $plan_rates->first();
            if ($firstPlanRate) {
                $plan_rates = collect([$firstPlanRate]);
            } else {
                $packages['available'] = false;
                return $packages;
            }
        } else {
            $plan_rates = $package["plan_rates"];
        }

        foreach ($plan_rates as $key => $package_plan_rate) {
            // Validación de inventario
            if ($package['free_sale'] === 0) {
                $inventory_validate = PackageInventory::where('package_plan_rate_id', $package_plan_rate['id'])
                    ->where('date', '>=', $date_from)->where('date', '<=', $date_from)
                    ->where('locked', '=', 0)
                    ->where('inventory_num', '>=', $paxTotal)
                    ->count();

                if ($inventory_validate === 0) {
                    $packages["available"] = false;
                }
            }

            $offer_query = $package_plan_rate->offers;
            $dynamicSaleRates = collect();
            $paxTotalForDynamic = ($adult + $child_with_bed);

            // Markup por mercado
            if ($package_plan_rate->package_rate_sale_markup_market->count() > 0) {
                $package_rate_sale_markup_id = $package_plan_rate->package_rate_sale_markup_market[0]["id"];
                $dynamicSaleRates = $this->getDynamicRateSales(
                    $package_rate_sale_markup_id,
                    $package_plan_rate->id,
                    $type_class,
                    $paxTotalForDynamic,
                    $package_plan_rate['enable_fixed_prices']
                );
                $markup = $package_plan_rate->package_rate_sale_markup_market[0]->markup;
            }

            // Markup por cliente
            if ($package_plan_rate->package_rate_sale_markup->count() > 0) {
                $package_rate_sale_markup_id = $package_plan_rate->package_rate_sale_markup[0]["id"];
                $dynamicSaleRates = $this->getDynamicRateSales(
                    $package_rate_sale_markup_id,
                    $package_plan_rate->id,
                    $type_class,
                    $paxTotalForDynamic,
                    $package_plan_rate['enable_fixed_prices']
                );
                $markup = $package_plan_rate->package_rate_sale_markup[0]->markup;
            }

            $prices_per_adult_room = collect();

            if ($package_rate_sale_markup_id != '') {
                if ($dynamicSaleRates->count() === 0) {
                    $min_price = 0;
                } else {
                    // Categorías por tipo de clase
                    $plan_rate_categories = ($type_class !== 'all')
                        ? $package_plan_rate['plan_rate_categories']
                        : $package_plan_rate['plan_rate_categories_all'];

                    $prices_per_adult_room = $this->getPricePerAccommodation(
                        $dynamicSaleRates,
                        $plan_rate_categories,
                        $package_plan_rate->service_type_id,
                        $room_quantity_sgl,
                        $room_quantity_dbl,
                        $room_quantity_tpl,
                        $package_plan_rate['enable_fixed_prices'],
                        $markup
                    );

                    // Ordenar categorías para que salga primero la de menor precio
                    $package["plan_rates"][$key]['plan_rate_categories_all'] = $this->rearrangeCategories(
                        $package_plan_rate['plan_rate_categories_all'],
                        $prices_per_adult_room['plan_rate_category_id']
                    );

                    $package["plan_rates"][$key]['plan_rate_categories'] = $this->rearrangeCategories(
                        $package_plan_rate['plan_rate_categories'],
                        $prices_per_adult_room['plan_rate_category_id']
                    );

                    $min_price = $this->getTotalPackage(
                        $prices_per_adult_room,
                        $room_quantity_sgl,
                        $room_quantity_dbl,
                        $room_quantity_tpl,
                        $adult,
                        $room_quantity_child_dbl,
                        $room_quantity_child_tpl,
                        $child_with_bed
                    );
                }

                // Precios de niños
                $price_per_child_with_bed = 0;
                $price_per_child_without_bed = 0;

                if ((bool)$package['allow_child'] && isset($prices_per_adult_room['plan_rate_category_id'])) {
                    $prices_child = $this->getPricesChild(
                        $package->children,
                        $package_rate_sale_markup_id,
                        $package_plan_rate->service_type_id,
                        $type_class,
                        $prices_per_adult_room['plan_rate_category_id'],
                        $plan_rate_categories,
                        $package_plan_rate['enable_fixed_prices'],
                        $markup
                    );

                    $packages['prices_children'] = $prices_child;

                    if ($child_with_bed > 0) {
                        $price_per_child_with_bed = $prices_child['with_bed']['price'];
                    }

                    if ($child_without_bed > 0) {
                        $price_per_child_without_bed = $prices_child['without_bed']['price'];
                    }
                }

                // Ofertas
                if ($offer_query->count() > 0) {
                    $is_offer = (bool)$offer_query[0]->is_offer;
                    $offer_value = $offer_query[0]->value;

                    if ($is_offer) {
                        $packages["offer"] = true;
                        $without_discount = $min_price * $paxTotalForDynamic;
                        $packages["without_discount"] = (float) roundLito($without_discount);
                        $min_price = $min_price - ($min_price * ($offer_value / 100));
                        $packages["offer_value"] = $offer_value;
                    } else {
                        $min_price = $min_price + ($min_price * ($offer_value / 100));
                    }
                } else {
                    $packages["offer"] = false;
                }

                // Cálculos finales de precios
                $price = 0;
                if ($min_price > 0) {
                    $price = $min_price / $adult;
                }

                $price_per_adult = $price;

                $price_per_child_with_bed = roundLito($price_per_child_with_bed);
                $price_per_child_without_bed = roundLito($price_per_child_without_bed);

                $total_adults = $price_per_adult * $adult;
                $total_child_with_bed = $price_per_child_with_bed * $child_with_bed;
                $total_child_without_bed = $price_per_child_without_bed * $child_without_bed;

                $total_amount_calculated = $total_adults + $total_child_with_bed + $total_child_without_bed;

                $paxTotalAll = ($adult + $child_with_bed + $child_without_bed);
                $price_per_person = ($paxTotalAll > 0)
                    ? (float) $total_amount_calculated / $paxTotalAll
                    : 0;

                $packages['total_adults'] = $total_adults;
                $packages["price_per_person"] = (float) $price_per_person;
                $packages["price_per_adult"] = $prices_per_adult_room;
                $packages["total_children"]['with_bed'] = (float) $total_child_with_bed;
                $packages["total_children"]['without_bed'] = (float) $total_child_without_bed;
                $packages["price_per_child"]['with_bed'] = (float) $price_per_child_with_bed;
                $packages["price_per_child"]['without_bed'] = (float) $price_per_child_without_bed;
                $packages["total_amount"] = (float) $total_amount_calculated;
            }
        }

        return $packages;
    }

    protected function addScheduleDescriptionToPackage($package)
    {
        $schedule_description = "";

        $package['schedules']->each(function ($schedule) use (&$schedule_description) {
            $schedules = [];
            $days = [
                'sunday',
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
            ];

            foreach ($days as $key => $day) {
                if (!empty($day) && $schedule[$day] == 1) {
                    $schedules[] = $key;
                }
            }

            $schedule_description = implode(", ", $schedules);
        });

        $package['schedule_days'] = $schedule_description;

        return $package;
    }

    public function transformPackageAvailable($packages)
    {
        return $packages->transform(function ($package) {
            $services = $package['services'] ?? [];

            $package['min_date_reserve'] = $this->calculateMinReserveDate($services);

            return $package;
        });
    }

    /**
     * Calcula la fecha mínima en la que se puede reservar el paquete
     * en base a los servicios y su qty_reserve_client.
     *
     * Regla actual:
     * - Se parte de la fecha de hoy.
     * - Por cada servicio de tipo 'service' con qty_reserve_client > 0
     *   se calcula hoy + days.
     * - Se toma la fecha MÁS LEJANA (max).
     *
     * Si no hay servicios válidos, retorna la fecha de hoy.
     *
     * @param array $services
     * @return string Y-m-d
     */
    protected function calculateMinReserveDate(array $services)
    {
        $today = date('Y-m-d');
        $maxDate = $today;

        foreach ($services as $service) {
            // Validamos estructura y tipo
            if (
                isset($service['type']) &&
                $service['type'] === 'service' &&
                isset($service['service']['qty_reserve_client'])
            ) {
                $days = (int) $service['service']['qty_reserve_client'];

                if ($days > 0) {
                    $date = date('Y-m-d', strtotime('+' . $days . ' days'));

                    // Nos quedamos con la fecha más alta
                    if ($date > $maxDate) {
                        $maxDate = $date;
                    }
                }
            }
        }

        return $maxDate;
    }



    public function transformServicesGroupByDay(array $services)
    {
        $services_group = collect();
        foreach ($services as $service) {
            $services_group->add($service);
        }
        return $services_group->groupBy('date_in');
    }

    public function verifyHasFlights($services)
    {
        $hasFlight = false;
        foreach ($services as $key => $service) {
            if ($service['type'] === 'flight') {
                $hasFlight = true;
                break;
            }
        }
        return $hasFlight;
    }

    public function getFoodDays($servicesGroupByDay, $type_food)
    {
        $days = [];
        $day_num = 0;
        foreach ($servicesGroupByDay as $key_day => $services) {
            $day_num++;
            foreach ($services as $service) {
                if ($service['type'] === 'service') {
                    if (($service['service']['unit_duration_id'] === 2 or $service['service']['unit_duration_id'] === 3 or
                        $service['service']['unit_duration_id'] === 4) and $service['service']['unit_duration_id'] >= 1) {
                        $days = [];
                        break;
                    } elseif (strtoupper($type_food) == strtoupper($service['service']['service_sub_category']['translations'][0]['value'])) {
                        $days[] = $day_num;
                    }
                }
            }
        }
        return $days;
    }

    public function verifyHasTransport($servicesGroupByDay)
    {
        $hasTransport = false;
        foreach ($servicesGroupByDay as $key_day => $services) {
            foreach ($services as $service) {
                if ($service['type'] === 'service') {
                    if (isset($service['service']['service_sub_category']['service_category_id']) && $service['service']['service_sub_category']['service_category_id'] == 1) {
                        $hasTransport = true;
                        break;
                    }
                }
            }
        }

        return $hasTransport;
    }

    public function verifyHasTickets($servicesGroupByDay)
    {
        $hasTickets = false;
        foreach ($servicesGroupByDay as $key_day => $services) {
            foreach ($services as $service) {
                if ($service['type'] === 'service') {
                    if ('ENTRADAS' == strtoupper($service['service']['service_sub_category']['translations'][0]['value'])) {
                        $hasTickets = true;
                        break;
                    }
                }
            }
        }

        return $hasTickets;
    }

    public function getAccommodationByCity($servicesGroupByDay)
    {
        $groupByCity = collect();
        $groupByCitySum = collect();
        foreach ($servicesGroupByDay as $key_day => $services) {
            foreach ($services as $service) {
                if ($service['type'] === 'hotel') {
                    $date_service_in = Carbon::parse($service['date_in']);
                    $date_service_out = Carbon::parse($service['date_out']);
                    $nights = $date_service_in->diffInDays($date_service_out);
                    $groupByCity->add([
                        'city' => $service['hotel']['city']['translations'][0]['value'],
                        'nights' => $nights,
                    ]);
                }
            }
        }

        if ($groupByCity->count() > 0) {
            $groupByCity = $groupByCity->groupBy('city');
            foreach ($groupByCity as $key_city => $city) {
                $groupByCitySum->add([
                    'city' => $key_city,
                    'nights' => $city->sum('nights'),
                ]);
            }
        }

        return $groupByCitySum;
    }

    public function getPricesChild(
        $children_ages,
        $package_rate_sale_markup_id,
        $service_type_id,
        $type_class,
        $package_plan_rate_category_id,
        $plan_rate_categories,
        $enable_fixed_prices,
        $markup
    ) {
        try {
            $packages = [
                'with_bed' => [
                    'price' => 0,
                    'min_age' => 0,
                    'max_age' => 0,
                ],
                'without_bed' => [
                    'price' => 0,
                    'min_age' => 0,
                    'max_age' => 0,
                ],
            ];
            if ($enable_fixed_prices) {
                $plan_rate_category = $plan_rate_categories->filter(function ($value) use (
                    $package_plan_rate_category_id
                ) {
                    return $value->id == $package_plan_rate_category_id;
                })->first();
                if ($plan_rate_category) {
                    if ($plan_rate_category->sale_rates_fixed->count() > 0) {
                        $age_with_bed = $children_ages->first(function ($age) {
                            return $age->has_bed === 1;
                        });

                        $age_without_bed = $children_ages->first(function ($age) {
                            return $age->has_bed === 0;
                        });

                        if ($age_with_bed) {
                            $packages['with_bed']['min_age'] = $age_with_bed->min_age;
                            $packages['with_bed']['max_age'] = $age_with_bed->max_age;
                        }

                        if ($age_without_bed) {
                            $packages['without_bed']['min_age'] = $age_without_bed->min_age;
                            $packages['without_bed']['max_age'] = $age_without_bed->max_age;
                        }

                        $price_with_bed = $plan_rate_category->sale_rates_fixed[0]->child_with_bed;
                        $price_with_bed = $price_with_bed + (($price_with_bed * $markup) / 100);
                        $price_without_bed = $plan_rate_category->sale_rates_fixed[0]->child_without_bed;
                        $price_without_bed = $price_without_bed + (($price_without_bed * $markup) / 100);
                        $packages['with_bed']['price'] = (float)$price_with_bed;
                        $packages['without_bed']['price'] = (float)$price_without_bed;
                    }
                }
            } else {
                $price_child = PackageDynamicSaleRate::where(
                    'package_rate_sale_markup_id',
                    $package_rate_sale_markup_id
                )->where('service_type_id', $service_type_id);
                if ($type_class !== 'all') {
                    $price_child->where('package_plan_rate_category_id', $package_plan_rate_category_id);
                }
                $price_child = $price_child->where('pax_from', '<=', 2)
                    ->where('pax_to', '>=', 2)
                    ->where('double', '>', 0)->first(['child_with_bed', 'child_without_bed']);
                if ($price_child) {
                    $age_with_bed = $children_ages->first(function ($age) {
                        return $age->has_bed === 1;
                    });

                    $age_without_bed = $children_ages->first(function ($age) {
                        return $age->has_bed === 0;
                    });

                    if ($age_with_bed) {
                        $packages['with_bed']['min_age'] = $age_with_bed->min_age;
                        $packages['with_bed']['max_age'] = $age_with_bed->max_age;
                    }

                    if ($age_without_bed) {
                        $packages['without_bed']['min_age'] = $age_without_bed->min_age;
                        $packages['without_bed']['max_age'] = $age_without_bed->max_age;
                    }

                    $packages['with_bed']['price'] = (float)$price_child->child_with_bed;
                    $packages['without_bed']['price'] = (float)$price_child->child_without_bed;
                }
            }

            return $packages;
        } catch (\Exception $exception) {
            throw new \Exception($package_rate_sale_markup_id . ' - ' . $exception->getMessage());
        }
    }

    public function addHotelInItineraryDescription($services, $itinerary)
    {
        $itinerary_group_by_hotel = collect();
        $servicesByDays = $this->getItineraryByService($services);

        foreach ($servicesByDays as $index => $service) {
            $description = (count($itinerary) > 0 && isset($itinerary[$index]))
                ? $itinerary[$index]['description']
                : '';
            $hasHotel = count($service['hotel']) > 0;
            if (empty($description)) {
                continue; // Salta al siguiente ciclo del foreach
            }

            $date = Carbon::parse($servicesByDays[0]['date_in'])->addDays($index)->format('Y-m-d');
            $itinerary_group_by_hotel->add([
                'day' => $date,
                'description_short' => $description,
                'group_key_hotel' => ($hasHotel) ? $service['hotel'][0]['id'] . $service['hotel'][0]['date_in'] : $date,
                'hotel' => $service['hotel'],
            ]);
        }
        $itinerary_group_by_hotel = $itinerary_group_by_hotel->groupBy('group_key_hotel')->values();

        $itinerary_group_by_hotel = $itinerary_group_by_hotel->transform(function ($item_group) {
            return $item_group->transform(function ($item) {
                return [
                    'day' => $item['day'],
                    'description_short' => $item['description_short'],
                    'hotel' => $item['hotel'],
                ];
            });
        });
        return $itinerary_group_by_hotel;
    }

    public function getFlights($services, $adults, $child)
    {
        $flights = collect();

        foreach ($services as $service) {
            if ($service['type'] === 'flight') {
                $flights->add([
                    'code_flight' => $service['code_flight'],
                    'date_in' => $service['date_in'],
                    'date_out' => $service['date_out'],
                    'adult' => $adults,
                    'child' => $child,
                    'infant' => $service['infant'],
                    'origin' => !empty($service['origin']) ? $service['origin'] : '',
                    'destiny' => !empty($service['destiny']) ? $service['destiny'] : '',
                    'nro_flight' => '',
                    'start_time' => '',
                    'end_time' => '',
                ]);
            }
        }

        return $flights;
    }

    public function rearrangeCategories($plan_rate_categories, $plan_rate_category_id)
    {
        $plan_rate_categories_order = collect();
        foreach ($plan_rate_categories as $category) {
            if ($category['id'] === $plan_rate_category_id) {
                $plan_rate_categories_order->add($category);
            }
        }

        foreach ($plan_rate_categories as $category) {
            if ($category['id'] !== $plan_rate_category_id) {
                $plan_rate_categories_order->add($category);
            }
        }

        return $plan_rate_categories_order;
    }

    public function getDynamicRateSales(
        $package_rate_sale_markup_id,
        $package_plan_rate_id,
        $type_class,
        $paxTotal,
        $enable_fixed_prices
    ) {
        if ($enable_fixed_prices) {
            $packagePlanRateCategory = PackagePlanRateCategory::where('package_plan_rate_id', $package_plan_rate_id)
                ->with([
                    'sale_rates_fixed' => function ($query) {
                        $query->select([
                            'id',
                            'package_plan_rate_category_id',
                            'simple',
                            'double',
                            'triple',
                            'child_with_bed',
                            'child_without_bed',
                        ]);
                    },
                ]);
            if ($type_class !== 'all') {
                $packagePlanRateCategory = $packagePlanRateCategory->where('type_class_id', $type_class);
            }
            return $packagePlanRateCategory->get();
        } else {
            return PackageDynamicSaleRate::where('package_rate_sale_markup_id', $package_rate_sale_markup_id)
                ->where('pax_from', '>=', $paxTotal)
                ->where('pax_to', '<=', $paxTotal)
                ->with([
                    'plan_rate_category' => function ($query) use ($type_class) {
                        if ($type_class !== 'all') {
                            $query->where('type_class_id', $type_class);
                        }
                    },
                ])
                ->get();
        }
    }

    public function getSearchPackagesClient(
        $client,
        $destiny,
        $type_package,
        $language,
        $type_service,
        $date_from,
        $type_class,
        $adult,
        $child,
        $child_with_bed,
        $tags,
        $filter = '',
        $days = 0,
        $recommendations = false,
        $only_recommended = false,
        $package_id = null,
        $limit = 0,
        $groups = []
    ) {
        $paxTotal = ($adult + $child_with_bed);
        $packages = Packages::with([
            'package_destinations' => function ($query) use ($language) {
                $query->select(['package_id', 'state_id']);
                $query->with([
                    'state' => function ($query) use ($language) {
                        $query->select(['id', 'iso', 'country_id']);
                        $query->with([
                            'translations' => function ($query) use ($language) {
                                $query->select(['object_id', 'value']);
                                $query->where('type', 'state');
                                $query->where('language_id', $language->id);
                            }
                        ]);
                    }
                ]);
            },
        ])->with(['schedules' => function ($query) use ($date_from) {
            $query->where('date_from', '<=', Carbon::parse($date_from)->format("Y-m-d"));
            $query->where('date_to', '>=', Carbon::parse($date_from)->format("Y-m-d"));
        }])->with([
            'tag' => function ($query) use ($language) {
                $query->select(['id', 'color']);
                $query->with([
                    'translations' => function ($query) use ($language) {
                        $query->select(['object_id', 'value']);
                        $query->where('type', 'tag');
                        $query->where('language_id', $language->id);
                    }
                ]);
            },
        ])->with([
            'translations' => function ($query) use ($language) {
                $query->select([
                    'package_id',
                    'name',
                    'tradename',
                    'label',
                    'description',
                    'description_commercial',
                    'itinerary_link',
                    'itinerary_link_commercial',
                    'itinerary_description',
                    'itinerary_commercial',
                    'inclusion',
                    'restriction',
                    'restriction_commercial',
                    'policies',
                    'policies_commercial',
                ]);
                $query->where('language_id', $language->id);
            },
        ])
            ->with([
                'itineraries' => function ($query) use ($language, $date_from) {
                    $query->where('year', Carbon::parse($date_from)->year);
                    $query->where('language_id', $language->id);
                }
            ])
            ->with([
                'itineraries_all' => function ($query) use ($language, $date_from) {
                    $query->where('year', '>=', date('Y'));
                    $query->where('language_id', $language->id);
                }
            ])
            ->with([
                'extension_recommended' => function ($query) {
                    $query->select('id', 'package_id', 'extension_id');
                }
            ])->with([
                'fixed_outputs' => function ($query) {
                    $query->select('id', 'package_id', 'date', 'room');
                    $query->where('date', '>=', Carbon::now()->format('Y-m-d'));
                    $query->where('state', 1);
                    $query->orderBy('date');
                }
            ])->with([
                'galleries' => function ($query) {
                    $query->select('object_id', 'slug', 'url');
                    $query->where('type', 'package');
                },
            ])->with([
                'rated' => function ($query) use ($client) {
                    $query->select('id', 'rated', 'package_id');
                    $query->where('client_id', $client->id);
                }
            ])->with([
                'client_package_setting' => function ($query) use ($client) {
                    $query->select('id', 'client_id', 'package_id', 'reservation_from', 'unit_duration_reserve');
                    $query->where('client_id', $client->id);
                }
            ])->with([
                'plan_rates' => function ($query) use (
                    $date_from,
                    $type_class,
                    $type_service,
                    $language,
                    $type_package,
                    $client,
                    $paxTotal
                ) {
                    $query->where('status', 1);
                    $query->where('date_from', '<=', $date_from);
                    $query->where('date_to', '>=', $date_from);
                    if ($type_service !== 'all') {
                        $query->where('service_type_id', $type_service);
                    } else {
                        $query->where('service_type_id', 1)->orWhere('service_type_id', 2);
                    }
                    $query->select(['id', 'package_id', 'name', 'date_from', 'date_to', 'service_type_id', 'status', 'enable_fixed_prices']);
                    $query->where('status', 1);
                    $query->with([
                        'package_rate_sale_markup' => function ($q) use ($type_class, $client, $paxTotal) {
                            $q->select(['id', 'seller_type', 'markup', 'package_plan_rate_id']);
                            $q->where('seller_type', 'App\Client')->where('status', 1)->where('seller_id', $client->id);
                        }
                    ]);
                    $query->with([
                        'service_type' => function ($query) use ($language) {
                            $query->select(['id', 'code']);
                            $query->with([
                                'translations' => function ($query) use ($language) {
                                    $query->select(['object_id', 'value']);
                                    $query->where('type', 'servicetype');
                                    $query->where('slug', 'servicetype_name');
                                    $query->where('language_id', $language->id);
                                }
                            ]);
                        }
                    ]);
                    $query->with([
                        'package_rate_sale_markup_market' => function ($q) use ($type_class, $client, $paxTotal) {
                            $q->select(['id', 'seller_type', 'markup', 'package_plan_rate_id']);
                            $q->where('seller_type', 'App\Market')->where('status', 1)->where(
                                'seller_id',
                                $client->market_id
                            );
                        }
                    ]);
                    $query->with([
                        'plan_rate_categories' => function ($q) use ($type_class, $language) {
                            if ($type_class !== 'all') {
                                $q->where('type_class_id', $type_class);
                            }
                            $q->with([
                                'category' => function ($query) use ($language) {
                                    $query->with([
                                        'translations' => function ($q) use ($language) {
                                            $q->where('type', 'typeclass');
                                            $q->where('language_id', $language->id);
                                        }
                                    ]);
                                }
                            ]);
                            $q->with([
                                'sale_rates_fixed' => function ($query) {
                                    $query->select([
                                        'id',
                                        'package_plan_rate_category_id',
                                        'simple',
                                        'double',
                                        'triple',
                                        'child_with_bed',
                                        'child_without_bed'
                                    ]);
                                }
                            ]);
                        }
                    ]);
                    $query->with([
                        'plan_rate_categories_all' => function ($q) use ($type_class, $language) {
                            //                        $q->whereHas('sale_rates');
                            $q->with([
                                'category' => function ($query) use ($language) {
                                    $query->with([
                                        'translations' => function ($q) use ($language) {
                                            $q->where('type', 'typeclass');
                                            $q->where('language_id', $language->id);
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ]);
                    $query->with([
                        'offers' => function ($query) use ($date_from, $client) {
                            $query->select(
                                'id',
                                'client_id',
                                'package_plan_rate_id',
                                'date_from',
                                'date_to',
                                'value',
                                'is_offer'
                            );
                            $query->where('date_from', '<=', $date_from);
                            $query->where('date_to', '>=', $date_from);
                            $query->where('client_id', $client->id);
                            $query->where('status', 1);
                        }
                    ]);
                    if ($type_class !== 'all') {
                        $query->whereHas('plan_rate_categories', function ($q) use ($type_class) {
                            $q->where('type_class_id', $type_class);
                            //                        $q->whereHas('sale_rates');
                        });
                    } else {
                        //                    $query->whereHas('plan_rate_categories.sale_rates');
                    }
                }
            ])->with([
                'children' => function ($query) {
                    $query->select(['id', 'package_id', 'min_age', 'max_age', 'has_bed']);
                    $query->where('status', 1);
                }
            ])->with([
                'highlights' => function ($query) use ($language) {
                    $query->select(['id', 'package_id', 'image_highlight_id']);
                    $query->with([
                        'highlights' => function ($query) use ($language) {
                            $query->select(['id', 'url']);
                            $query->where('status', 1);
                            $query->with([
                                'translations' => function ($query) use ($language) {
                                    $query->select(['object_id', 'value']);
                                    $query->where('type', 'image_highlights');
                                    $query->where('language_id', $language->id);
                                },
                                'translations_content' => function ($query) use ($language) {
                                    $query->select(['object_id', 'value']);
                                    $query->where('type', 'image_highlights');
                                    $query->where('language_id', $language->id);
                                }
                            ]);
                        }
                    ]);
                    $query->orderBy('order');
                }
            ]);

        $packages->whereHas('plan_rates', function ($query) use ($type_service, $type_class, $type_package, $date_from) {
            $query->where('status', 1);
            $query->where('date_from', '<=', $date_from);
            $query->where('date_to', '>=', $date_from);
            if ($type_service !== 'all') {
                $query->where('service_type_id', $type_service);
            } else {
                $query->where('service_type_id', 1)->orWhere('service_type_id', 2);
            }
            if ($type_class !== 'all') {
                $query->whereHas('plan_rate_categories', function ($q) use ($type_class) {
                    $q->where('type_class_id', $type_class);
                    //                    $q->whereHas('sale_rates');
                });
            } else {
                //                $query->whereHas('plan_rate_categories.sale_rates');
            }
        });

        /*
        $packages->whereHas('plan_rates', function ($query) use ($date_from, $type_package) {
            $query->where('status', 1);
            $query->where('date_from', '<=', $date_from);
            $query->where('date_to', '>=', $date_from);
//            $query->whereHas('plan_rate_categories.sale_rates');
        });

        $packages->whereHas('plan_rates', function ($query) use ($date_from) {
            $query->where('status', 1);
            $query->where('date_from', '<=', $date_from);
            $query->where('date_to', '>=', $date_from);
//            $query->whereHas('plan_rate_categories.sale_rates');
        });
        */

        $packages->whereHas('plan_rates', function ($query) use ($client, $date_from) {
            $query->where('status', 1);
            $query->where('date_from', '<=', $date_from);
            $query->where('date_to', '>=', $date_from);

            $query->where(function ($query) use ($client) {
                $query->orWhereHas('package_rate_sale_markup', function ($q) use ($client) {
                    $q->where('seller_type', 'like', '%\Client');
                    $q->where('seller_id', $client->id);
                    $q->where('status', 1);
                });

                $query->orWhereHas('package_rate_sale_markup', function ($q) use ($client) {
                    $q->where('seller_type', 'like', '%\Market');
                    $q->where('seller_id', $client->market_id);
                    $q->where('status', 1);
                });
            });
        });

        //        $packages->whereHas('plan_rates.inventory', function ($query) use ($date_from, $paxTotal) {
        //            $query->where('date', '>=', $date_from);
        //            $query->where('date', '<=', $date_from);
        //            $query->where('locked', '=', 0);
        //            $query->where('inventory_num', '>=', $paxTotal);
        //        });

        $packages->when((bool)$recommendations == true, function ($query) use ($recommendations) {
            return $query->whereHas('rated', function ($query) {
                $query->where('rated', '>', 0);
                $query->where('client_id', $this->client_id());
                $query->orderBy('rated', 'desc');
            });
        });

        $packages->when(count($destiny) > 0, function ($query) use ($destiny) {
            return $query->whereHas('package_destinations', function ($query) use ($destiny) {
                $query->whereIn('state_id', $destiny);
            });
        });

        $packages->when(!empty($tags) and count($tags) > 0, function ($query) use ($tags) {
            return $query->whereIn('tag_id', $tags);
        });

        $packages->when(!empty($groups) and count($groups) > 0, function ($query) use ($groups) {
            return $query->whereHas('tag', function ($query) use ($groups) {
                return $query->whereIn('tag_group_id', $groups);
            });
        });

        $packages->when(!empty($days) and $days > 0, function ($query) use ($days) {
            $nights = ($days === 1) ? 1 : ($days - 1);
            return $query->where('nights', $nights);
        });


        if ($child > 0) {
            // Falta filtro de edades de niños
            $packages->where('allow_child', 1);
            //            foreach ($age_child as $age) {
            //                $packages->whereHas('children', function ($query) use ($age) {
            //                    $query->where('min_age', '<=', $age["age"]);
            //                    $query->where('max_age', '>=', $age["age"]);
            //                });
            //            }
        }

        if (count($package_id) > 0) {

            $ids_ordered = implode(',', $package_id);

            $packages->whereIn(
                'id',
                $package_id
            )->orderByRaw("FIELD(id, $ids_ordered)"); // Importante, se usa en best_sellers
        }

        // dd($packages->get());

        if ($only_recommended) {
            $packages = $packages->where('recommended', 1)->inRandomOrder();
        }

        if ($limit > 0) {
            $packages = $packages->limit($limit);
        }
        //        throw new \Exception(json_encode($type_package));

        $packages = $packages->where('status', 1)->whereIn('extension', $type_package)
            ->get([
                'id',
                'country_id',
                'code',
                'extension',
                'nights',
                'portada_link',
                'map_link',
                'map_itinerary_link',
                'image_link',
                'status',
                'reference',
                'rate_type',
                'rate_dynamic',
                'allow_guide',
                'allow_child',
                'allow_infant',
                'limit_confirmation_hours',
                'infant_min_age',
                'infant_max_age',
                'infant_discount_rate',
                'physical_intensity_id',
                'tag_id',
                'allow_modify',
                'free_sale',
                // 'enable_fixed_prices',
                'recommended',
                'destinations'
            ]);
        return $packages;
    }

    public function storeTokenSearchPackages($token_search, $packages, $minutes)
    {
        Cache::put($token_search, $packages, now()->addMinutes($minutes));
    }

    public function verifyCloudinaryImgPackage($var, $w, $h, $request)
    {
        //https://res-5.cloudinary.com/litomarketing/image/upload/c_thumb,h_80,w_70/v1432940982/peru/amazonas/Tacacho_con_Cecina_024325_300.jpg
        //Default: Parapente_bthb8r
        if ($var == '') {
            if ($request == 'nom') {
                $var = 'Parapente_bthb8r';
            } else { // link
                $var =
                    'https://res-5.cloudinary.com/litomarketing/image/upload/c_thumb,h_' .
                    $h . ',w_' . $w . '/Parapente_bthb8r';
            }
        } else {
            $explode = explode("cloudinary.com", $var);
            if (count($explode) > 1) {
                $img = explode("upload/", $var);
                $img = $img[1];

                $verifyThumb = explode("c_thumb", $img);
                if (count($verifyThumb) > 1) {
                    $img = explode("/", $img);
                    $img = $img[count($img) - 1];
                }

                if ($request == 'nom') {
                    $var = $img;
                } else { // link
                    $var =
                        'https://res-5.cloudinary.com/litomarketing/image/upload/c_thumb,h_' .
                        $h . ',w_' . $w . '/' . trim($img);
                }
            } else {

                //                $var = request()->getSchemeAndHttpHost().'/images/'.$var;
                $var = "https://backend.limatours.com.pe" . '/images/' . $var;
            }
        }

        return trim(preg_replace('/\s\s+/', ' ', $var));
    }

    protected function searchGetPackageClient($params = [])
    {
        $client_id = $params['client_id'];
        // Todo Codigo del destino
        $destination_code = (isset($params['destinations'])) ? $params['destinations'] : [];
        //Todo Variable Idioma
        $lang = $params['lang'];
        $language = Language::where('iso', $lang)->first();

        //Todo Variable para traer solo los recomendados
        $only_recommended = (isset($params['only_recommended'])) ? $params['only_recommended'] : false;

        //Todo Variable tipo de servicio [SIM,PC]
        $type_service = $params['type_service'];

        //Todo Variable tipo de servicio [SIM,PC]
        $groups = (isset($params['groups'])) ? $params['groups'] : [];

        //Todo Variable tipo de servicio [SIM,PC]
        $tags = (isset($params['tags'])) ? $params['tags'] : [];

        //Todo Fecha de busqueda
        $date_from = $params['date']; //date('Y-m-d');
        $from = Carbon::parse($date_from);
        $from = $from->format('Y-m-d');

        // dd($from);
        //Todo Cantidad de pasajeros
        $quantity_persons = $params['quantity_persons'];
        $adult = (int)$quantity_persons['adults'];
        $child_with_bed = (int)$quantity_persons['child_with_bed'];
        $child_without_bed = (int)$quantity_persons['child_without_bed'];
        $childs = $child_with_bed + $child_without_bed;
        //            $age_child = $quantity_persons['age_child'];

        //Todo Acomodacion Adultos y niños con cama
        $rooms = $params['rooms'];
        $room_quantity_sgl = $rooms['quantity_sgl'];
        $room_quantity_dbl = $rooms['quantity_dbl'];
        $room_quantity_child_dbl = isset($rooms['quantity_child_dbl']) ? $rooms['quantity_child_dbl'] : 0;
        $room_quantity_tpl = $rooms['quantity_tpl'];
        $room_quantity_child_tpl = isset($rooms['quantity_child_tpl']) ? $rooms['quantity_child_tpl'] : 0;

        //Todo Categoria [Turista,lujo...]
        $type_class = (isset($params['category'])) ? $params['category'] : 'all';

        $filter = (isset($params['filter'])) ? $params['filter'] : '';

        $days = (isset($params['days'])) ? $params['days'] : 0;

        $limit = (isset($params['limit'])) ? $params['limit'] : 0;

        $package_ids = (isset($params['package_ids'])) ? $params['package_ids'] : [];

        //Todo Tipo de paquete [0 => paquete, 1 => extension, 2 => paquete exclusivo]
        $type_package = (isset($params['type_package'])) ? $params['type_package'] : [0, 1, 2];

        $client = Client::find($client_id, ['id', 'code', 'market_id', 'country_id', 'language_id']);

        //Todo Busqueda de politicas de cancelacion por la cantidad de adultos
        $policy = PackageCancellationPolicy::where('pax_from', '<=', $adult)
            ->where('pax_to', '>=', $adult)
            ->get([
                'day_from',
                'day_to',
                'cancellation_fees'
            ]);
        $faker = Faker::create();
        $token_search = $faker->unique()->uuid;
        $packages = $this->getSearchPackagesClient(
            $client,
            $destination_code,
            $type_package,
            $language,
            $type_service,
            $from,
            $type_class,
            $adult,
            $childs,
            $child_with_bed,
            $tags,
            $filter,
            $days,
            false,
            $only_recommended,
            $package_ids,
            $limit,
            $groups
        );

        $packages = $this->transformDataPackage(
            $packages,
            $date_from,
            $adult,
            $child_with_bed,
            $child_without_bed,
            $type_class,
            $lang,
            $policy,
            $type_service,
            $token_search,
            $room_quantity_sgl,
            $room_quantity_dbl,
            $room_quantity_tpl,
            $room_quantity_child_dbl,
            $room_quantity_child_tpl,
            $filter
        );
        $packages = $packages->where('total_amount', '>', 0)->where('available', true);

        $packages = $this->transformPackageAvailable($packages);

        //Todo Guardo el token en cache
        $this->storeTokenSearchPackages($token_search, $packages, $this->expiration_search);


        $packages = PackageResource::collection($packages);

        foreach ($packages as $key => $package) {
            $url = config('services.cloudinary.domain') . '/packages/' . $package['id'] . '/frontpage.jpg';
            $headers = @get_headers($url);

            if ($headers && strpos($headers[0], '200')) {
                $packages[$key]['portada_link'] = $url;
            }

            $url = config('services.cloudinary.domain') . '/packages/' . $package['id'] . '/map.jpg';
            $headers = @get_headers($url);

            if ($headers && strpos($headers[0], '200')) {
                $packages[$key]['map_itinerary_link'] = $url;
            }
        }

        return $packages;
    }

    public function super_unique(
        $array,
        $key
    ) {

        $temp_array = array();

        foreach ($array as &$v) {

            if (!isset($temp_array[$v[$key]])) {
                $temp_array[$v[$key]] = &$v;
            }
        }

        $array = array_values($temp_array);

        return $array;
    }

    protected function createPortada($params)
    {
        $params_portada = [
            "clienteId" => 15766,
            "portada" => $params['package']['portada_link'],
            "title" => $params['package']['descriptions']['name'],
            "destinies" => $params['package']['destinations']['destinations_display'],
            "type_package" => $params['package']['descriptions']['label'],
            "date_operations" => $params['package']['schedule_days'],
            "estado" => 1,
            "lang" => $params['lang'],
            "days" => $params['days'],
            "date_from" => $params['package']['rate']['date_from'],
            "date_to" => $params['package']['rate']['date_to'],
            "code" => "lsv",
            "status" => $params['with_client_logo'] ?? 1,
        ];

        $client = new \GuzzleHttp\Client();
        $link = sprintf('%sapi/public/quote/imageCreatePackage', config('services.quotes.domain'));

        try {
            $response = $client->post($link, [
                'form_params' => $params_portada,
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['image'] ?? '';
        } catch (\Exception $ex) {
            $this->throwError($ex);
            return '';
        }
    }

    protected function generateWordItineraryPublic($request)
    {
        $lang = strtolower($request->input('lang'));

        try {
            $year = (!empty($request->input('year'))) ? $request->input('year') : date("Y");
            $user_id = ($request->has('user_id')) ? $request->input('user_id') : 0;
            $use_prices = ($request->has('use_prices')) ? $request->input('use_prices') : 0;
            $user_type_id = ($request->has('user_type_id')) ? $request->input('user_type_id') : null;
            $with_client_logo = $request->input('with_client_logo') ?? 1;

            $params = [
                'client_id' => $request->input('client_id'),
                'lang' => $lang,
                'package_ids' => [$request->input('package_id')],
                'year' => $year,
                'days' => $request->input('days'),
                'category' => $request->input('category'),
                'quantity_persons' => [
                    'adults' => 2,
                    'age_child' => [
                        'age_child' => 1,
                    ],
                    'child_with_bed' => 0,
                    'child_without_bed' => 0,
                ],
                'rooms' => [
                    'quantity_child_dbl' => 0,
                    'quantity_child_tpl' => 0,
                    'quantity_dbl' => 1,
                    'quantity_sgl' => 0,
                    'quantity_tpl' => 0
                ],
                'type_service' => $request->input('type_service'),
                'date' => $year . '-02-01',
                'use_header' => 1,
                'use_prices' => ((int)$use_prices === 1 || $use_prices === 'true') ? 1 : 0,
                'user_id' => $user_id,
                'portada' => $request->input('portada') ?? '', //es una petición que nos traera la URL del itinerario seleccionado
                'ignore_contact' => true,
                'user_type_id' => $user_type_id,
                'with_client_logo' => $with_client_logo,
            ];

            $packages = $this->searchGetPackageClient($params);
            $packages = $packages->toArray(request());
            $package = $packages[0];
            $package['highlights'] = (is_array($package['highlights'])) ? $package['highlights'] : $package['highlights']->toArray(request());
            $package['categories'] = $package['categories']->toArray(request());
            $package['physical_intensity'] = $package['physical_intensity']->toArray(request());
            $package['destinations']['destinations'] = $package['destinations']['destinations']->toArray(request());
            $package['rate'] = $package['rate']->toArray(request());
            $package['galleries'] = (is_array($package['galleries'])) ? $package['galleries'] : $package['galleries']->toArray(request());
            $params['package'] = $package;

            $params['portada'] = $this->createPortada($params);

            $userAgent = $request->header('User-Agent');
            $flag_movil = $this->esDispositivoMovil($userAgent);

            $url = $this->generateWordItinerary($params, $flag_movil);

            if ($flag_movil) {
                $url = str_replace("http:", "https:", $url);
                $url = urlencode($url);
                return redirect()->to(sprintf('https://view.officeapps.live.com/op/embed.aspx?src=%s', $url));
            } else {
                return response()->download($url)->deleteFileAfterSend(true);
            }
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            return redirect()->to('https://a3.limatours.com.pe/maintenance?lang=' . $lang);
        }
    }

    private function esDispositivoMovil($userAgent)
    {
        return preg_match('/(android|iphone|ipad|mobile|silk|blackberry|opera mini|iemobile|wpdesktop|nokia|palm)/i', $userAgent);
    }

    public static function htmlDecode(
        $var
    ) {
        $text = html_entity_decode(trim($var), ENT_QUOTES, "UTF-8");
        $text = str_replace("\\", '', $text);
        return $text;
    }

    protected function getServiceFoodText($inclusions, $translations, &$flag_breakfast)
    {
        $foods = [];
        $inclusions = Collect($inclusions);

        $hasBreakfast = $inclusions->filter(function ($inclusion) use ($translations) {
            return Str::contains(
                strtolower($inclusion['inclusions']['translations'][0]['value'] ?? ''),
                strtolower($translations->breakfast)
            );
        })->isNotEmpty();

        if ($hasBreakfast) {
            $foods[] = [
                'order' => 1,
                'value' => $translations->breakfast
            ];

            $flag_breakfast = true;
        }

        $hasLunch = $inclusions->filter(function ($inclusion) use ($translations) {
            return Str::contains(
                strtolower($inclusion['inclusions']['translations'][0]['value'] ?? ''),
                strtolower($translations->lunch)
            );
        })->isNotEmpty();

        if ($hasLunch) {
            $foods[] = [
                'order' => 2,
                'value' => $translations->lunch
            ];
        }

        $hasDinner = $inclusions->filter(function ($inclusion) use ($translations) {
            return Str::contains(
                strtolower($inclusion['inclusions']['translations'][0]['value'] ?? ''),
                strtolower($translations->dinner)
            );
        })->isNotEmpty();

        if ($hasDinner) {
            $foods[] = [
                'order' => 3,
                'value' => $translations->dinner
            ];
        }

        return $foods;
    }

    public function generateWordItinerary($params, $movil = false)
    {
        $lang = $params['lang'];
        $package_ids = $params['package_ids'];
        $year = $params['year'];
        $category = $params['category'];
        $package = $params['package'];
        $use_header = $params['use_header'];
        $use_prices = $params['use_prices'];
        $urlPortada = $params['portada']; //es una petición que nos traera la URL del itinerario seleccionado
        $ignore_contact = $params['ignore_contact'] ?? false;

        $ignore = [17441, 15109, 16861, 15605, 17441]; // Cliente Producto
        $linksNewHotelsAndToursOnlyPackages = [
            224,
            41,
            743,
            717,
            1319,
            451,
            1320,
            400,
            727,
            1306,
            726,
            579,
            460,
            725,
            1336,
            1335,
            1307,
            1309,
            1308,
            1305,
            1304
        ]; // Paquetes que solo tienen enlaces del nuevo listado de hoteles y tours

        $disclaimer_codes = [
            'mapi' => [
                'CUZPQ2',
                'CUZPQ4',
                'MPIMMP',
                'MPI515',
                'MPIPI5',
                'MPIPI0',
                'MPIP1O',
                'MPI500',
                'MPI5OO',
                'MPI517',
                'MPI518',
                'MPIPI7',
                'MPIP23',
                'MPIPI9',
                'MPIP22',
                'MPIPI8',
                'MPI504',
                'MPI506',
                'MPI505',
                'MPI519',
                'MPIMIP',
                'MP1MIP',
                'MP2MIP',
                'MPIMMC',
                'MPIMPH',
                'MP1MPH',
                'MP2MPH',
            ],
            'galapagos' => [
                'SCY401',
                'SCYX01',
                'SCY102',
                'SCY101',
                'SCY501',
                'SCY201',
                'SCY502',
                'SCY202',
                'SCX501',
                'SCXX01',
                'SCX503',
                'SCX504',
                'SCX102',
                'SCX101',
                'SCX505',
                'SCX506',
                'SCX507',
                'SCX508',
                'SCX509',
                'SCX510',
                'SCX511',
                'SCX512',
                'SCX201',
                'SCX202',
                'SCX103',
                'SCX205',
                'SCX502',
                'SCX401',
                'SCX402',
                'SCX203',
                'SCX513',
                'SCX204',
                'ISA501',
                'ISA401',
                'ISA503',
                'ISA504',
                'ISA502',
                'ISA201',
                'ISA506',
                'ISA505',
                'ISA202',
            ]
        ];

        $client_id = (!in_array($params['client_id'], $ignore)) ? $params['client_id'] : 16861;

        $client = Client::select(['id', 'country_id', 'market_id', 'status', 'commission', 'commission_status'])
            ->where('id', '=', $client_id)
            ->first();
        $market_id = $client ? (int)$client->market_id : 0;

        /**
         * --- VARIABLES DE COMISIÓN ---
         */
        $user_type_id = null;
        $client_commission_status = 0;
        $client_commission = 0;
        $has_commission_label = false;

        // Primero intentar obtener user_type_id desde Auth (ruta POST autenticada)
        if (Auth::check()) {
            $user_type_id = Auth::user()->user_type_id;
        }
        // Si no está autenticado, usar el parámetro user_type_id (link público)
        elseif (isset($params['user_type_id'])) {
            $user_type_id = (int) $params['user_type_id'];
        }

        // Calcular comisión si el usuario es tipo 4 (vendedor) y existe el cliente
        if ($user_type_id == 4 && $client) {
            $client_commission_status = (int) $client->commission_status;
            $client_commission = (float) $client->commission;

            // Flag de comisión activa
            $has_commission_label = (
                $client_commission_status === 1 &&
                $client_commission > 0 &&
                $user_type_id === 4
            );
        }

        $language_id = Language::select('id')->where('iso', $lang)->first()->id;

        $dataLang = File::get(sprintf('%s/lang/%s/itinerary.json', resource_path(), $lang));
        $trad = json_decode($dataLang);

        $packagePlanRates = PackagePlanRate::whereIn('package_id', $package_ids)
            // ->where('service_type_id', '=', $service_type_id)
            ->whereYear('date_from', $year)
            ->whereYear('date_to', $year)
            ->where('status', '=', 1)
            ->orderBy('service_type_id', 'ASC')
            ->get();

        $packagePlanRate = [];
        $flag_shared = false;
        $flag_private = false;

        $package_mirror = Packages::with(['tag'])->find($package['id']);

        foreach ($packagePlanRates as $planRate) {
            if ($planRate->service_type_id == 1) {
                $flag_shared = true;
            }

            if (!$use_prices) {
                if (empty($packagePlanRate)) {
                    $packagePlanRate = $planRate;
                }
            }
        }

        $rates = []; // TARIFAS..

        if ($use_prices) {
            $paxs = ($flag_shared) ? 3 : 6;

            $show = [];

            for ($i = 0; $i < $paxs; $i++) {
                $show[$i] = false;
            }

            $show['simple'] = false;
            $show['double'] = false;
            $show['triple'] = false;
            $show['diff_double'] = false;
            $show['diff_triple'] = false;
            $show['with_bed'] = false;
            $show['without_bed'] = false;

            foreach ($packagePlanRates as $planRate) {
                $categories = PackagePlanRateCategory::where('package_plan_rate_id', $planRate->id)
                    ->with([
                        'category.translations' => function ($query) use ($lang) {
                            $query->where('type', 'typeclass');
                            $query->whereHas('language', function ($q) use ($lang) {
                                $q->where('iso', $lang);
                            });
                        }
                    ])
                    ->where(function ($query) {
                        $query->orWhereHas('category', function ($q) {
                            $q->where('code', '!=', 'X');
                            $q->where('code', '!=', 'x');
                        });
                    })
                    // ->orderBy('category.order', 'ASC')
                    ->get();

                $rate_sale_markup = PackageRateSaleMarkup::where('seller_id', '=', $client->id)
                    ->where('seller_type', 'like', '%Client')
                    ->where('package_plan_rate_id', $planRate->id)->first();

                if (empty($rate_sale_markup)) {
                    $rate_sale_markup = PackageRateSaleMarkup::where('seller_id', '=', $client->country_id)
                        ->where('seller_type', 'like', '%Country')
                        ->where('package_plan_rate_id', $planRate->id)->first();
                }

                if (empty($rate_sale_markup)) {
                    $rate_sale_markup = PackageRateSaleMarkup::where('seller_id', '=', $client->market_id)
                        ->where('seller_type', 'like', '%Market')
                        ->where('package_plan_rate_id', $planRate->id)->first();
                }

                if ($planRate->enable_fixed_prices === 1) {
                    if (empty($packagePlanRate)) {
                        $packagePlanRate = $planRate;
                    }

                    // --
                    $package_fixed_sale_rates = PackageFixedSaleRate::whereIn(
                        'package_plan_rate_category_id',
                        $categories->pluck('id')
                    )->get();

                    if ($package_fixed_sale_rates->count() === 0) {

                        $sorted = $categories->sortBy(function ($item) {
                            return $item['category']['order'];
                        });

                        $categories = $sorted->values()->all();

                        foreach ($categories as $category) {
                            $rates[$category->category->id] = [
                                'category' => $category->category->translations[0]->value,
                                'simple' => 0,
                                'double' => 0,
                                'triple' => 0,
                                'child_with_bed' => 0,
                                'child_without_bed' => 0,
                            ];
                        }
                    } else {
                        $sorted = $categories->sortBy(function ($item) {
                            return $item['category']['order'];
                        });

                        $categories = $sorted->values()->all();

                        foreach ($categories as $key => $search) {
                            if ($planRate->service_type_id == 1) {
                                foreach ($package_fixed_sale_rates as $fixed_sale_rate) {
                                    if ($search->id === $fixed_sale_rate->package_plan_rate_category_id) {
                                        $rates[$search->category->id] = [
                                            'category' => $search->category->translations[0]->value,
                                            'simple' => $fixed_sale_rate->simple * (1 + ($rate_sale_markup->markup / 100)),
                                            'double' => $fixed_sale_rate->double * (1 + ($rate_sale_markup->markup / 100)),
                                            'triple' => $fixed_sale_rate->triple * (1 + ($rate_sale_markup->markup / 100)),
                                            'child_with_bed' => $fixed_sale_rate->child_with_bed * (1 + ($rate_sale_markup->markup / 100)),
                                            'child_without_bed' => $fixed_sale_rate->child_without_bed * (1 + ($rate_sale_markup->markup / 100))
                                        ];

                                        $show['simple'] = $rates[$search->category->id]['simple'] > 0;
                                        $show['double'] = $rates[$search->category->id]['double'] > 0;
                                        $show['triple'] = $rates[$search->category->id]['triple'] > 0;
                                        $show['with_bed'] = $rates[$search->category->id]['child_with_bed'] > 0;
                                        $show['without_bed'] = $rates[$search->category->id]['child_without_bed'] > 0;
                                    }
                                }
                            } else {
                                $simple = 0;
                                $double = 0;
                                $triple = 0;

                                foreach ($package_fixed_sale_rates as $fixed_sale_rate) {
                                    if ($search->id === $fixed_sale_rate->package_plan_rate_category_id) {

                                        if (!empty($rates[$search->category->id])) {
                                            $rates[$search->category->id]['simple_priv'] = (($fixed_sale_rate->simple * (1 + ($rate_sale_markup->markup / 100))) - $rates[$search->category->id]['simple']);
                                            $rates[$search->category->id]['double_priv'] = (($fixed_sale_rate->double * (1 + ($rate_sale_markup->markup / 100))) - $rates[$search->category->id]['double']);
                                            $rates[$search->category->id]['triple_priv'] = (($fixed_sale_rate->triple * (1 + ($rate_sale_markup->markup / 100))) - $rates[$search->category->id]['triple']);
                                            $rates[$search->category->id]['child_with_bed_priv'] = (($fixed_sale_rate->child_with_bed * (1 + ($rate_sale_markup->markup / 100))) - $rates[$search->category->id]['child_with_bed']);
                                            $rates[$search->category->id]['child_without_bed_priv'] = (($fixed_sale_rate->child_without_bed * (1 + ($rate_sale_markup->markup / 100))) - $rates[$search->category->id]['child_without_bed']);

                                            $show['simple'] = $rates[$search->category->id]['simple_priv'] > 0;
                                            $show['double'] = $rates[$search->category->id]['double_priv'] > 0;
                                            $show['triple'] = $rates[$search->category->id]['triple_priv'] > 0;
                                            $show['with_bed'] = $rates[$search->category->id]['child_with_bed_priv'] > 0;
                                            $show['without_bed'] = ($rates[$search->category->id]['child_without_bed_priv'] ?? 0) > 0;
                                        } else {
                                            if (!$flag_shared) {
                                                $flag_private = true;

                                                $rates[$search->category->id] = [
                                                    'category' => $search->category->translations[0]->value,
                                                    'simple' => $fixed_sale_rate->simple * (1 + ($rate_sale_markup->markup / 100)),
                                                    'double' => $fixed_sale_rate->double * (1 + ($rate_sale_markup->markup / 100)),
                                                    'triple' => $fixed_sale_rate->triple * (1 + ($rate_sale_markup->markup / 100)),
                                                    'child_with_bed' => $fixed_sale_rate->child_with_bed * (1 + ($rate_sale_markup->markup / 100)),
                                                    'child_without_bed' => $fixed_sale_rate->child_without_bed * (1 + ($rate_sale_markup->markup / 100))
                                                ];

                                                $show['simple'] = $rates[$search->category->id]['simple'] > 0;
                                                $show['double'] = $rates[$search->category->id]['double'] > 0;
                                                $show['triple'] = $rates[$search->category->id]['triple'] > 0;
                                                $show['with_bed'] = $rates[$search->category->id]['child_with_bed'] > 0;
                                                $show['without_bed'] = $rates[$search->category->id]['child_without_bed'] > 0;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $sorted = $categories->sortBy(function ($item) {
                        return $item['category']['order'];
                    });

                    $categories = $sorted->values()->all();

                    foreach ($categories as $key => $_category) {
                        if (empty($packagePlanRate)) {
                            $packagePlanRate = $planRate;
                        }

                        $ratesPrivate = [];
                        $ratesShared = [];
                        $sale_id = @$rate_sale_markup->id;

                        $rates_dynamics = PackageDynamicSaleRate::where('package_plan_rate_category_id', $_category->id)
                            ->where('package_rate_sale_markup_id', $sale_id)
                            ->where('pax_from', '<=', $paxs)
                            ->where('service_type_id', '=', $planRate->service_type_id)
                            ->orderBy('pax_from', 'ASC')
                            ->get();

                        if ($rates_dynamics->count() === 0) {
                            continue;
                        }

                        $items = [];

                        if ($flag_shared) {
                            $simple = 0;
                            $double = 0;
                            $triple = 0;
                            $child_with_bed = 0;
                            $child_without_bed = 0;

                            foreach ($rates_dynamics as $item) {
                                if ($item->service_type_id == 1) { //compartido
                                    array_push($ratesShared, $item);
                                }
                                if ($item->service_type_id == 2) {
                                    array_push($ratesPrivate, $item);
                                }

                                $simple = ($item->pax_from == 1) ? $item->simple : $simple;
                                $double = ($item->pax_from == 2) ? $item->double : $double;
                                $triple = ($item->pax_from == 3) ? $item->triple : $triple;

                                if ($item->pax_from == 2) {
                                    $child_with_bed = $item->child_with_bed;
                                    $child_without_bed = $item->child_without_bed;
                                }
                            }

                            if (!empty($ratesShared)) {
                                $rates[$_category->category->id] = [
                                    'category' => $_category->category->translations[0]->value,
                                    'simple' => $simple,
                                    'double' => $double,
                                    'triple' => $triple,
                                    'child_with_bed' => $child_with_bed,
                                    'child_without_bed' => $child_without_bed,
                                ];
                            }

                            $simple_priv = 0;
                            $double_priv = 0;
                            $triple_priv = 0;
                            $count = count($ratesPrivate);

                            if ($count >= 1 && !empty($rates[$_category->category->id])) {
                                $simple_priv = $simple - $rates[$_category->category->id]['simple'];
                                $double_priv = $double - $rates[$_category->category->id]['double'];
                                $triple_priv = $triple - $rates[$_category->category->id]['triple'];
                                $child_with_bed_priv = $child_with_bed - $rates[$_category->category->id]['child_with_bed'];
                                $child_without_bed_priv = $child_without_bed - $rates[$_category->category->id]['child_without_bed'];

                                $private = [
                                    'simple_priv' => $simple_priv,
                                    'double_priv' => $double_priv,
                                    'triple_priv' => $triple_priv,
                                    'child_with_bed_priv' => $child_with_bed_priv,
                                    'child_without_bed_priv' => $child_without_bed_priv,
                                ];

                                foreach ($private as $key => $value) {
                                    $rates[$_category->category->id][$key] = $value;
                                }
                            }

                            $show['simple'] = @$simple > 0 || @$simple_priv > 0;
                            $show['double'] = @$double > 0 || @$double_priv > 0;
                            $show['triple'] = @$triple > 0 || @$triple_priv > 0;
                            $show['with_bed'] = @$child_with_bed > 0 || @$child_with_bed_priv > 0;
                            $show['without_bed'] = @$child_without_bed > 0 || @$child_without_bed_priv > 0;
                        } else {
                            $rates[$_category->category->id] = [
                                'category' => $_category->category->translations[0]->value
                            ];

                            $simple_amount = 0;
                            $double_amount = 0;
                            $triple_amount = 0;

                            foreach ($rates_dynamics as $item) {
                                $keys = ['', 'simple', 'double', 'triple'];
                                $key = ($item->pax_from > 3) ? 'double' : $keys[$item->pax_from];
                                $items[] = (float)roundLito($item->$key, 'hotel');

                                if ($item->pax_from == 3) {
                                    $simple_amount = $item->simple;
                                    $double_amount = $item->double;
                                    $triple_amount = $item->triple;
                                }
                            }

                            $diff_double = abs($simple_amount - $double_amount);
                            $diff_triple = abs($double_amount - $triple_amount);

                            for ($i = 0; $i < $paxs; $i++) {
                                if (@$items[$i] > 0) {
                                    $show[$i] = true;
                                }

                                $rates[$_category->category->id][$i] = @$items[$i];
                                $rates[$_category->category->id]['diff_double'] = $diff_double;

                                if ($diff_double > 0) {
                                    $show['diff_double'] = true;
                                }

                                $rates[$_category->category->id]['diff_triple'] = $diff_triple;

                                if ($diff_triple > 0) {
                                    $show['diff_triple'] = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        $package_plan_rate_id = isset($package['rate']['id']) ? $package['rate']['id'] : $packagePlanRate->id;

        $planRateCategory = PackagePlanRateCategory::where('package_plan_rate_id', $package_plan_rate_id)
            ->where('type_class_id', $category)->first();

        $with_trashed = false;
        $plan_rate_category_id = $planRateCategory->id;

        $package_services = PackageService::where('package_plan_rate_category_id', $plan_rate_category_id)
            ->with(['origin.translations' => function ($query) use ($language_id) {
                $query->where('language_id', $language_id);
            }, 'destiny.translations' => function ($query) use ($language_id) {
                $query->where('language_id', $language_id);
            }])
            ->with(['hotel' => function ($query) use ($with_trashed) {
                if ($with_trashed) {
                    $query->withTrashed();
                }
                $query->with('channel');
            }])
            ->with(['service_rooms.rate_plan_room.rate_plan' => function ($query) use ($with_trashed) {
                if ($with_trashed) {
                    $query->withTrashed();
                }
            }])
            ->with(['service_rooms.rate_plan_room.room' => function ($query) use ($with_trashed, $language_id) {
                if ($with_trashed) {
                    $query->withTrashed();
                }
                $query->with(['room_type.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                }]);
            }])
            ->with(['service_rates'])
            ->with([
                'service.serviceDestination.state.translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                }
            ])
            ->with([
                'service' => function ($query) use ($with_trashed, $language_id) {
                    $query->with([
                        'service_rate.service_rate_plans',
                        'serviceSubCategory.serviceCategories'
                    ]);
                    $query->with([
                        'serviceSubCategory.translations' => function ($query) use ($language_id) {
                            $query->where('type', 'servicesubcategory');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with([
                        'serviceType' => function ($query) use ($language_id) {
                            $query->with([
                                'translations' => function ($query) use ($language_id) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'servicetype');
                                    $query->where('language_id', $language_id);
                                },
                            ]);
                        }
                    ]);
                    $query->withCount(['serviceEquivAssociation']);
                    $query->with([
                        'galleries' => function ($query) {
                            $query->select(['object_id', 'url', 'position']);
                            $query->orderBy('position', 'ASC');
                        },
                    ]);
                    $query->with([
                        'service_translations' => function ($query) use ($language_id) {
                            $query->select([
                                'service_id',
                                'name',
                                'description',
                                'itinerary',
                                'summary',
                                'accommodation',
                            ]);
                            $query->where('language_id', $language_id);
                        },
                    ]);
                    $query->with([
                        'inclusions' => function ($query) use ($language_id) {
                            $query->select(['service_id', 'day', 'inclusion_id', 'include', 'see_client', 'order']);
                            $query->where('include', 1);
                            $query->where('see_client', 1);
                            $query->with([
                                'inclusions' => function ($query) use ($language_id) {
                                    $query->select([
                                        'id',
                                        'monday',
                                        'tuesday',
                                        'wednesday',
                                        'thursday',
                                        'friday',
                                        'saturday',
                                        'sunday'
                                    ]);
                                    $query->with([
                                        'translations' => function ($query) use ($language_id) {
                                            $query->select([
                                                'object_id',
                                                'value'
                                            ]);
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                }
                            ]);
                        },
                    ]);
                    $query->with([
                        'serviceDestination' => function ($query) use ($language_id) {
                            $query->select(['id', 'service_id', 'city_id', 'state_id']);
                            $query->with([
                                'state' => function ($query) use ($language_id) {
                                    $query->select(['id', 'iso']);
                                    $query->with([
                                        'translations' => function ($query) use ($language_id) {
                                            $query->select([
                                                'object_id',
                                                'value'
                                            ]);
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                },
                            ]);
                            $query->with([
                                'city' => function ($query) use ($language_id) {
                                    $query->select(['id', 'iso']);
                                    $query->with([
                                        'translations' => function ($query) use ($language_id) {
                                            $query->select([
                                                'object_id',
                                                'value'
                                            ]);
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                },
                            ]);
                        },
                    ]);
                    $query->with([
                        'serviceSubCategory' => function ($query) {
                            $query->select(['id', 'service_category_id', 'order']);
                        },
                    ]);
                    if ($with_trashed) {
                        $query->withTrashed();
                    }
                }
            ])
            ->with([
                'hotel' => function ($query) use ($language_id) {
                    $query->with([
                        "translations" => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with([
                        "state.translations" => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with([
                        "city.translations" => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with('galeries');
                }
            ])
            //            ->where('id', 88121)
            ->orderBy('date_in')
            ->orderBy('order');


        $package_services = $package_services->get();


        // return $package_services;

        // price_from  |  price_from_pax
        foreach ($package_services as $k => $p_s) {

            if ($p_s->type == "service") {
                foreach ($p_s->service->service_rate as $s_rate) {
                    $s_rate->price_from = '';
                    $s_rate->price_from_pax = '';
                    foreach ($s_rate->service_rate_plans as $s_plan) {
                        if (
                            strtotime($s_plan->date_from) <= strtotime($package_services[$k]->date_in) &&
                            strtotime($s_plan->date_to) >= strtotime($package_services[$k]->date_in)
                        ) {
                            $s_rate->price_from = $s_plan->price_adult;
                            $s_rate->price_from_pax = $s_plan->pax_from;
                            break;
                        }
                    }
                }
            }
        }

        $package_services = $package_services->toArray();

        // return $package_services;

        for ($i = 0; $i < count($package_services); $i++) {
            if ($package_services[$i]['type'] === "hotel") {
                for ($r = 0; $r < count($package_services[$i]['service_rooms']); $r++) {
                    $package_services[$i]['service_rooms'][$r]['rate_plan_room']['first_rate'] = [];

                    $package_services[$i]['service_rooms'][$r]['rate_plan_room']['calendarys_in_dates'] =
                        RatesPlansCalendarys::where('rates_plans_room_id', $package_services[$i]['service_rooms'][$r]['rate_plan_room']['id'])
                        ->where('date', '<', $package_services[$i]['date_out'])
                        ->where('date', '>=', $package_services[$i]['date_in'])
                        ->with('rate')
                        ->get();

                    $package_services[$i]['price_total_adult'] = 0;
                    $package_services[$i]['price_total_child'] = 0;

                    foreach ($package_services[$i]['service_rooms'][$r]['rate_plan_room']['calendarys_in_dates'] as $calendary) {
                        if (
                            count($package_services[$i]['service_rooms'][$r]['rate_plan_room']['first_rate']) == 0
                            && count($calendary->rate) > 0
                        ) {
                            $package_services[$i]['service_rooms'][$r]['rate_plan_room']['first_rate'] = $calendary->rate;
                        }

                        $package_services[$i]['price_total_adult'] += $calendary['rate'][0]['price_adult'];
                        $package_services[$i]['price_total_child'] += $calendary['rate'][0]['price_child'];
                    }
                }

                $date_from = Carbon::parse($package_services[$i]['date_in']);
                $date_to = Carbon::parse($package_services[$i]['date_out']);
                $package_services[$i]['nights'] = $date_from->diffInDays($date_to);
            }

            $package_services[$i]['optional'] = false;
        }

        $quote_services = $package_services;

        // Hasta aquí está depurado..
        //Generar Arreglo de dias
        $itineraries = [];

        $textSearch = "###";
        for ($i = 0; $i < count($quote_services); $i++) {
            $date_service = Carbon::createFromFormat('Y-m-d', $quote_services[$i]["date_in"])->format('Y-m-d');
            $name = '';
            if (!empty($quote_services[$i]["service"]["service_translations"]) && count($quote_services[$i]["service"]["service_translations"]) > 0) {
                $name = $quote_services[$i]["service"]["service_translations"][0]["itinerary"];
            }
            $parrafo = $name; // strip_tags($this->htmlDecode(htmlspecialchars_decode($name)));
            $count = substr_count($parrafo, $textSearch);

            $pattern = '/^<\/p><p(?:\s[^>]*)?>/';
            $parrafo = preg_replace($pattern, '<p>', $parrafo);

            $pattern = '/<\/p><p(?:\s[^>]*)?>$/';
            $parrafo = preg_replace($pattern, '</p>', $parrafo);

            $textExplode = explode($textSearch, $parrafo);
            $_date_in = Carbon::createFromFormat('Y-m-d', $quote_services[$i]["date_in"])->format('Y-m-d');
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $service["name"] = substr($textExplode[$j], strlen($textSearch));
                    if ($j > 0) {
                        // AUMENTAR UN DIA AL DATE
                        $fecha_add = Carbon::parse($_date_in)->addDays($j - 1)->format('Y-m-d');
                        array_push($itineraries, $fecha_add);
                    }
                }
            } else {
                array_push($itineraries, $date_service);
            }
        }


        $itinerary = array_flip($itineraries);


        foreach ($itinerary as $key => $date) {
            $itinerary[$key] = [];
        }


        $all_hotels = [];
        $all_flights = [];

        $flag_disclaimer_latam = $package_mirror->tag->tag_group_id === 8; // Project LATAM;
        $flag_disclaimer_mapi = false;
        $flag_disclaimer_galapagos = false;

        for ($i = 0; $i < count($quote_services); $i++) {
            $date_service = Carbon::createFromFormat('Y-m-d', $quote_services[$i]["date_in"])->format('Y-m-d');
            if ($quote_services[$i]["type"] == "hotel") {
                for ($j = 0; $j <= ((int)($quote_services[$i]["nights"])); $j++) {
                    $fecha_add = Carbon::parse($date_service)->addDays($j)->format('Y-m-d');
                    $date_of_week = $trad->semana[Carbon::parse(Carbon::createFromFormat(
                        'Y-m-d',
                        $fecha_add
                    )->format('Y-m-d'))->dayOfWeek]->name;
                    $day = Carbon::parse(Carbon::createFromFormat(
                        'Y-m-d',
                        $fecha_add
                    )->format('Y-m-d'))->day;
                    $month = $trad->mes[Carbon::parse(Carbon::createFromFormat(
                        'Y-m-d',
                        $fecha_add
                    )->format('Y-m-d'))->month - 1]->name;
                    $year = Carbon::parse(Carbon::createFromFormat(
                        'Y-m-d',
                        $fecha_add
                    )->format('Y-m-d'))->year;
                    if (!isset($itinerary[$fecha_add])) {
                        $itinerary[$fecha_add] = [];
                    }
                    $itinerary[$fecha_add][count($itinerary[$fecha_add])] = [
                        'key' => 'hotel_' . $quote_services[$i]["hotel"]["id"] . '_' . $quote_services[$i]["nights"] . '_' . $fecha_add,
                        'date' => $fecha_add,
                        "day_of_week" => $date_of_week,
                        "day" => $day,
                        "month" => $month,
                        "year" => $year,
                        "date_title" => $date_of_week . ' ' . $day . ' ' . $trad->de . ' ' . $month . ', ' . $year,
                        "type" => "hotel",
                        "optional" => $quote_services[$i]["optional"],
                        "meal_id" => $quote_services[$i]["service_rooms"][0]["rate_plan_room"]["rate_plan"]["meal_id"],
                        "accommodation" => $quote_services[$i]["nights"] - $j,
                        "services" => [
                            "id" => $quote_services[$i]["hotel"]["id"],
                            "name" => $quote_services[$i]["hotel"]["name"],
                            "web" => ($quote_services[$i]["hotel"]["web_site"]) ? htmlspecialchars(trim($quote_services[$i]["hotel"]["web_site"])) : "-",
                            "check_in" => $quote_services[$i]["hotel"]["check_in_time"],
                            "check_out" => $quote_services[$i]["hotel"]["check_out_time"],
                            "destiny" => $quote_services[$i]["hotel"]["city"]["translations"][0]["value"],
                            "image_" => (count($quote_services[$i]["hotel"]["galeries"]) > 0)
                                ? $quote_services[$i]["hotel"]["galeries"][0]["url"]
                                : null,
                            "image" => (count($quote_services[$i]["hotel"]["galeries"]) > 0)
                                ? $this->verifyCloudinaryImgPackage(
                                    $quote_services[$i]["hotel"]["galeries"][0]["url"],
                                    400,
                                    200,
                                    ''
                                )
                                : null,
                            "nights" => $quote_services[$i]["nights"],
                            "date_in" => $fecha_add
                        ]
                    ];

                    $all_hotels[] = 'hotel_' . $quote_services[$i]["hotel"]["id"] . '_' . $quote_services[$i]["nights"] . '_' . $fecha_add;
                }
            }

            if ($quote_services[$i]["type"] == "service") {
                if (!$flag_disclaimer_mapi && in_array($quote_services[$i]["service"]["aurora_code"], $disclaimer_codes["mapi"])) {
                    $flag_disclaimer_mapi = true;
                }

                if (!$flag_disclaimer_galapagos && in_array($quote_services[$i]["service"]["aurora_code"], $disclaimer_codes["galapagos"])) {
                    $flag_disclaimer_galapagos = true;
                }

                $parrafo = $this->htmlDecode(htmlspecialchars_decode($quote_services[$i]["service"]["service_translations"][0]["itinerary"]));
                $nameCommercial = strip_tags($this->htmlDecode(htmlspecialchars_decode($quote_services[$i]["service"]["service_translations"][0]["name"])));
                $accommodation = $quote_services[$i]["service"]["service_translations"][0]["accommodation"];
                $count = substr_count($parrafo, $textSearch);
                $textExplode = explode($textSearch, $parrafo);
                $food = ($quote_services[$i]["service"]["service_sub_category"]["service_category_id"] == 10) ? $quote_services[$i]["service"]["service_sub_category"]["translations"][0]["value"] : '';
                $inclusions = collect($quote_services[$i]['service']['inclusions'])->groupBy('day');

                if ($count > 0) {
                    for ($j = 0; $j <= $count; $j++) {
                        $service_itinerary = $textExplode[$j];

                        $fecha_add = Carbon::parse($date_service)->addDays($j)->format('Y-m-d');
                        $date_of_week = $trad->semana[Carbon::parse(Carbon::createFromFormat(
                            'Y-m-d',
                            $fecha_add
                        )->format('Y-m-d'))->dayOfWeek]->name;

                        $day = Carbon::parse(Carbon::createFromFormat(
                            'Y-m-d',
                            $fecha_add
                        )->format('Y-m-d'))->day;
                        $month = $trad->mes[Carbon::parse(Carbon::createFromFormat(
                            'Y-m-d',
                            $fecha_add
                        )->format('Y-m-d'))->month - 1]->name;
                        $year = Carbon::parse(Carbon::createFromFormat(
                            'Y-m-d',
                            $fecha_add
                        )->format('Y-m-d'))->year;
                        if (!isset($itinerary[$fecha_add])) {
                            $itinerary[$fecha_add] = [];
                        }

                        $itinerary[$fecha_add][count($itinerary[$fecha_add])] = [
                            'key' => 'service_' . $quote_services[$i]["service"]["id"] . '_' . $fecha_add,
                            'date' => $fecha_add,
                            "day_of_week" => $date_of_week,
                            "day" => $day,
                            "month" => $month,
                            "year" => $year,
                            "date_title" => $date_of_week . ' ' . $day . ' ' . $trad->de . ' ' . $month . ', ' . $year,
                            "type" => "service",
                            "optional" => $quote_services[$i]["optional"],
                            "include_accommodation" => (bool)$quote_services[$i]["service"]["include_accommodation"],
                            "services" => [
                                "id" => $quote_services[$i]["service"]["id"],
                                "name" => str_replace(" & ", " ", $nameCommercial),
                                "name_commercial" => str_replace(" & ", " ", $nameCommercial),
                                "itinerary" => str_replace(" & ", " ", $service_itinerary),
                                "description" => $quote_services[$i]["service"]["service_translations"][0]["description"],
                                "accommodation" => $accommodation,
                                "galleries" => $quote_services[$i]["service"]["galleries"],
                                "image_" => (count($quote_services[$i]["service"]["galleries"]) > 0)
                                    ? $quote_services[$i]["service"]["galleries"][0]["url"]
                                    : null,
                                "image" => (count($quote_services[$i]["service"]["galleries"]) > 0)
                                    ? $this->verifyCloudinaryImgPackage(
                                        $quote_services[$i]["service"]["galleries"][0]["url"],
                                        400,
                                        200,
                                        ''
                                    )
                                    : null,
                                "destiny" => $quote_services[$i]["service"]["service_destination"][0]["state"]["translations"][0]["value"],
                                "date_in" => convertDate($quote_services[$i]["date_in"], "/", "-", 1),
                                "food" => $food,
                                "inclusions" => $inclusions,
                                "service_type" => $quote_services[$i]["service"]['service_type']["translations"][0]["value"],
                                "code" => $quote_services[$i]["service"]["aurora_code"],
                                "isTour" => $quote_services[$i]["service"]["service_sub_category"]['service_category_id'] == 9 || $quote_services[$i]["service"]["service_sub_category"]['service_category_id'] == 2
                            ]
                        ];
                    }
                } else {
                    $date_of_week = $trad->semana[Carbon::parse(Carbon::createFromFormat(
                        'Y-m-d',
                        $quote_services[$i]["date_in"]
                    )->format('Y-m-d'))->dayOfWeek]->name;
                    $day = Carbon::parse(Carbon::createFromFormat(
                        'Y-m-d',
                        $quote_services[$i]["date_in"]
                    )->format('Y-m-d'))->day;
                    $month = $trad->mes[Carbon::parse(Carbon::createFromFormat(
                        'Y-m-d',
                        $quote_services[$i]["date_in"]
                    )->format('Y-m-d'))->month - 1]->name;
                    $year = Carbon::parse(Carbon::createFromFormat(
                        'Y-m-d',
                        $quote_services[$i]["date_in"]
                    )->format('Y-m-d'))->year;
                    $itinerary[$date_service][count($itinerary[$date_service])] = [
                        'key' => 'service_' . $quote_services[$i]["service"]["id"] . '_' . $date_service,
                        'date' => $date_service,
                        "day_of_week" => $date_of_week,
                        "day" => $day,
                        "month" => $month,
                        "year" => $year,
                        "date_title" => $date_of_week . ' ' . $day . ' ' . $trad->de . ' ' . $month . ', ' . $year,
                        "type" => "service",
                        "optional" => $quote_services[$i]["optional"],
                        "include_accommodation" => (bool)$quote_services[$i]["service"]["include_accommodation"],
                        "services" => [
                            "id" => $quote_services[$i]["service"]["id"],
                            "name" => str_replace(" & ", " ", $nameCommercial),
                            "name_commercial" => str_replace(" & ", " ", $nameCommercial),
                            "itinerary" => str_replace(" & ", " ", $parrafo),
                            "description" => $quote_services[$i]["service"]["service_translations"][0]["itinerary"],
                            "galleries" => $quote_services[$i]["service"]["galleries"],
                            "accommodation" => $accommodation,
                            "image_" => (count($quote_services[$i]["service"]["galleries"]) > 0)
                                ? $quote_services[$i]["service"]["galleries"][0]["url"]
                                : null,
                            "image" => (count($quote_services[$i]["service"]["galleries"]) > 0)
                                ? $this->verifyCloudinaryImgPackage(
                                    $quote_services[$i]["service"]["galleries"][0]["url"],
                                    400,
                                    200,
                                    ''
                                )
                                : null,
                            "destiny" => $quote_services[$i]["service"]["service_destination"][0]["state"]["translations"][0]["value"],
                            "date_in" => convertDate($quote_services[$i]["date_in"], "/", "-", 1),
                            "food" => $food,
                            "inclusions" => $inclusions,
                            "service_type" => $quote_services[$i]["service"]['service_type']["translations"][0]["value"],
                            "code" => $quote_services[$i]["service"]["aurora_code"],
                            "isTour" => $quote_services[$i]["service"]["service_sub_category"]['service_category_id'] == 9 || $quote_services[$i]["service"]["service_sub_category"]['service_category_id'] == 2
                        ]
                    ];
                }
            }

            if ($quote_services[$i]["type"] == "flight") {
                $origin = strip_tags($this->htmlDecode(htmlspecialchars_decode(@$quote_services[$i]["origin"]["translations"][0]["value"])));
                $destiny = strip_tags($this->htmlDecode(htmlspecialchars_decode(@$quote_services[$i]["destiny"]["translations"][0]["value"])));
                $date_of_week = $trad->semana[Carbon::parse(Carbon::createFromFormat(
                    'Y-m-d',
                    $quote_services[$i]["date_in"]
                )->format('Y-m-d'))->dayOfWeek]->name;
                $day = Carbon::parse(Carbon::createFromFormat(
                    'Y-m-d',
                    $quote_services[$i]["date_in"]
                )->format('Y-m-d'))->day;
                $month = $trad->mes[Carbon::parse(Carbon::createFromFormat(
                    'Y-m-d',
                    $quote_services[$i]["date_in"]
                )->format('Y-m-d'))->month - 1]->name;
                $year = Carbon::parse(Carbon::createFromFormat(
                    'Y-m-d',
                    $quote_services[$i]["date_in"]
                )->format('Y-m-d'))->year;
                $itinerary[$date_service][count($itinerary[$date_service])] = [
                    'key' => $quote_services[$i]["code_flight"] . '_' . $date_service,
                    'date' => $date_service,
                    "day_of_week" => $date_of_week,
                    "day" => $day,
                    "month" => $month,
                    "year" => $year,
                    "date_title" => $date_of_week . ' ' . $day . ' ' . $trad->de . ' ' . $month . ', ' . $year,
                    "type" => "flight",
                    "optional" => $quote_services[$i]["optional"],
                    "code_flight" => $quote_services[$i]["code_flight"],
                    "origin" => $origin,
                    "destiny" => $destiny
                ];

                if ($i == 0 || strpos($quote_services[$i]["code_flight"], "AEI") === false) {
                    $all_flights[] = ['origin' => $origin, 'destiny' => $destiny];
                }
            }
        }

        ksort($itinerary);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('calibri');
        if ($lang == 'es') {
            $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::ES_ES));
        } elseif ($lang == 'en') {
            $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::EN_US));
        } elseif ($lang == 'pt') {
            $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::PT_BR));
        } elseif ($lang == 'it') {
            $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::IT_IT));
        } else {
            $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::EN_US));
        }

        if (!empty($urlPortada) && $use_header) {
            if (strpos($urlPortada, 'covers/') === false) {
                $chunks = explode("/", $urlPortada);
                $url = last($chunks);
                $newUrl = config('services.cloudinary.domain') . "/covers/" . $url;

                $headers = @get_headers($newUrl);

                if ($headers && strpos($headers[0], '200')) {
                    $urlPortada = $newUrl;
                } else {
                    $newUrl = config('services.cloudinary.domain') . "/" . $url;
                    $headers = @get_headers($newUrl);

                    if ($headers && strpos($headers[0], '200')) {
                        $urlPortada = $newUrl;
                    }
                }
            }

            $section = $phpWord->addSection(array('bgColor' => '#363636'));
            $section->addTextBreak(10);

            //Creamos la caja en la cual estará alojada la portada
            $section->addImage(
                $urlPortada,
                array(
                    'widthwordSkeleton' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(16),
                    'height' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(22.5),
                    'positioning' => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE,
                    'posHorizontal' => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_RIGHT,
                    'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
                    'posVerticalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
                    'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(15.5),
                    'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(1.55),
                    'wrappingStyle' => \PhpOffice\PhpWord\Style\Image::WRAP_BEHIND,

                )
            );
        }

        // Creamos nueva pagina
        $section = $phpWord->addSection();

        //-----------------INICIO TITULO LIMATOURS
        //ESTILO DE LINEA
        $linestyle = array(
            'width' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(12),
            'height' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(0),
            'weight' => 2,
            'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(0),
            'color' => '#aea792',
        );
        $phpWord->addParagraphStyle(
            'title',
            array(
                'align' => 'left',
                'wrappingStyle' => 'infront',
                'spaceAfter' => 0,
            )
        );
        $section->addText(
            $trad->description_program,
            array('name' => 'Calibri', 'size' => 11, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);

        //-----------------INICIO CONTENIDO TEXTO
        $phpWord->addParagraphStyle(
            'paragraft',
            array(
                'align' => 'both',
                'spaceAfter' => 0,
                'wrappingStyle' => 'infront',
            )
        );
        $section->addText(
            htmlspecialchars($package['descriptions']['description']),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
            'paragraft'
        );

        $section->addTextBreak(1);
        //-----------------FIN CONTENIDO TEXTO
        //-----------------FIN TITULO LIMATOURS

        if (!empty($package['highlights'])) {
            //-----------------INICIO PUNTOS DESTACADOS
            $section->addText(
                $trad->highlights,
                array('name' => 'Calibri', 'size' => 11, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'title'
            );
            $section->addLine($linestyle);

            // Agregar una tabla
            $table = $section->addTable();

            // Agregar una fila a la tabla
            $table->addRow();

            // Agregar la primera celda con la imagen
            if (!empty($package['map_itinerary_link'])) {
                $cell = $table->addCell(4500);
                // Agregar la imagen
                $cell->addImage($package['map_itinerary_link'], ['width' => 200, 'height' => 250]);
                // Agregar texto debajo de la imagen
            }

            $htmlContent = '';
            //-----------------INICIO CONTENIDO TEXTO

            foreach ($package['highlights'] as $value) {
                $value['name'] = str_replace([' & '], [' &amp; '], $value['name']);

                $description = substr($value['description'], 3);
                $description = substr($description, 0, strlen($description) - 4);
                $description = str_replace(['<h5', '</h5>'], ['<div style="font-size:11px;"', '</div>'], $description);
                $description = str_replace(["<br>", "<br />", "</br>"], '&nbsp;', $description);
                $htmlContent .= '<p style="color: #808080;text-align:justify;"><strong>' . htmlspecialchars($value['name']) . '.</strong> ' . $description . '</p>';
            }

            Html::addHtml($table->addCell(9500), $htmlContent, false, false);
            //-----------------FIN PUNTOS DESTACADOS
        }


        $package_origin = @$all_flights[0]['destiny'];
        $package_destiny = @$all_flights[count($all_flights) - 1]['origin'];
        $package_destiny = ($package_destiny != $package_origin) ? (' - ' . $package_destiny) : '';

        $content = '<p style="color: #808080;"><span>' . $trad->destination . ': ' . $package_origin . $package_destiny . '</span></p>';
        $content .= '<p style="color: #808080;"><span>' . $trad->destinations . ': ' . str_replace(",", ', ', $package['destinations']['destinations_display']) . '</span></p>';
        Html::addHtml($section, $content, false, false);

        $section->addTextBreak(1);

        //-----------------INICIO  TITULO DIA DIA
        $phpWord->addParagraphStyle(
            'title',
            array(
                'align' => 'left',
                'wrappingStyle' => 'infront',
            )
        );
        $section->addText(
            htmlspecialchars($trad->dayToday),
            array('name' => 'Calibri', 'size' => 11, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);
        //        $section->addTextBreak(1);
        //------------------FIN  TITULO DIA DIA

        //-----------------INICIO ITINERARIO
        $phpWord->addParagraphStyle(
            'titleday',
            array(
                'align' => 'left',
                'spaceAfter' => 10,
                'wrappingStyle' => 'infront',
            )
        );
        $phpWord->addParagraphStyle(
            'fecha',
            array(
                'align' => 'left',
                'spaceAfter' => 170,
            )
        );

        $predefinedMultilevel = array('listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_SQUARE_FILLED);
        $phpWord->addFontStyle(
            'myOwnStyle',
            array('name' => 'Calibri', 'color' => '#545454', 'size' => 9, 'bold' => true)
        );
        $phpWord->addFontStyle('myLinkStyle', array(
            'name' => 'Calibri',
            'color' => '0000FF',
            'size' => 9,
            'bold' => true,
            'underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE
        ));

        $k = 1;
        $foodDays = [];
        $ignore = 0;
        $flag_tour = false;
        $all_images = [];

        // return $package['descriptions']['itinerary'];

        $htmlGlobal = '';
        $allFoods = collect([]);

        $resultAccommodations = [];

        $result = [];

        foreach ($itinerary as $date_key => $services) {
            foreach ($services as $service) {
                if (
                    isset($service['type']) && $service['type'] == 'service' &&
                    isset($service['include_accommodation']) && $service['include_accommodation'] == 1 &&
                    isset($service['services']['accommodation']) &&
                    isset($service['services']['id']) // Extraer el ID del servicio correctamente
                ) {
                    $id = $service['services']['id']; // Obtener el ID desde 'services'

                    if (!isset($resultAccommodations[$id])) {
                        $resultAccommodations[$id] = 0; // Inicializar el contador
                    }

                    // Contar las veces que include_accommodation == 1
                    $resultAccommodations[$id]++;
                }
            }
        }

        $printedAccommodations = [];
        foreach ($itinerary as $date_key => $services) {
            $destinies_array = [];
            $foods_array = [];
            $foodsCollection = collect([]);
            $flag_breakfast = (empty($all_hotels) && $k > 1) ? true : false;
            $images = [];

            if ($k == count($itinerary) && !$flag_breakfast) {
                $flag_breakfast = true;
            }

            // Obtengo los destinos
            foreach ($services as $service) {
                if (isset($service["services"]) && !empty($service["services"]["destiny"])) {
                    $destinies_array[] = $service["services"]["destiny"];
                }
                if ($service["type"] == "hotel" && $k > 1) {
                    $flag_breakfast = ($service["meal_id"] == 2) ? true : false;
                }
                if ($service["type"] == "service") {

                    if ($service["services"]["food"] !== "") {
                        $foods_array[] = strtolower($service["services"]["food"]);
                    }

                    $serviceId = $service['services']['id'];
                    if (!isset($foodDays[$serviceId])) {
                        $foodDays[$serviceId] = 1;
                    } else {
                        $foodDays[$serviceId] = $foodDays[$serviceId] + 1;
                    }

                    $dayToCheck = $foodDays[$serviceId];

                    if ($service["services"]["inclusions"]->has($dayToCheck)) {
                        $inclusionsFood = $this->getServiceFoodText(
                            $service["services"]["inclusions"][$dayToCheck],
                            $trad,
                            $flag_breakfast
                        );

                        foreach ($inclusionsFood as $food) {
                            $foodsCollection->push($food);
                            $allFoods->push([
                                'service' => $service,
                                'food' => $food['value'],
                            ]);
                        }
                    }
                }
            }

            $foods_collection = $foodsCollection->sortBy('order')->map(function ($food) {
                return $food['value'];
            })->unique()->toArray();

            $foods_array = array_merge($foods_array, $foods_collection);
            $foods_array = array_diff($foods_array, [$trad->breakfast]); // eliminamos el desayuno
            $foods = implode(', ', array_unique($foods_array));

            $section->addText(
                htmlspecialchars($trad->day . ' ' . ($k) . ((!empty($package['descriptions']['itinerary'][$k - 1])) ? (' | ' . trim($package['descriptions']['itinerary'][$k - 1]['description'])) : '')),
                array('name' => 'Calibri', 'size' => 11, 'color' => '#b3b182', 'bold' => true),
                'titleday'
            );

            $add_space = true;
            $day_free = true;
            $flag_foods = false;

            if (!empty($services)) {
                foreach ($services as $service) {
                    if (count($itinerary[$date_key]) > 0) {
                        if ($service["type"] == "service") {
                            $flag_tour = ($ignore % 2 === 1) ? true : false;
                            $htmlContent = '';

                            if ($service['services']["isTour"]) {
                                if (!$flag_tour) {
                                    $galleries = $this->searchGalleryCloudinary('service', $service['services']['id']);

                                    foreach ($galleries as $image) {
                                        if (count($images) < 3) {

                                            if (!in_array($image, $all_images)) {
                                                $images[] = $image;
                                                $all_images[] = $image;
                                            }
                                        }
                                    }
                                }

                                $ignore++;
                            }

                            if (!empty($service["services"]["itinerary"])) {
                                $content = $service["services"]["itinerary"];
                                $content = str_replace(['<h5', '</h5>'], ['<p style="font-size:11px;"', '</p>'], $content);
                                $content = str_replace(['<p>'], ['<p style="color:#808080;text-align:justify;">'], $content);
                                $content = str_replace(["<br>", "<br />", "</br>"], '&nbsp;', $content);

                                if (!empty($content)) {
                                    $content = str_replace('###', '&nbsp;', $content);

                                    $pattern = '/^<\/p><p(?:\s[^>]*)?>/';
                                    $content = preg_replace($pattern, '<p>', $content);

                                    $pattern = '/<\/p><p(?:\s[^>]*)?>$/';
                                    $content = preg_replace($pattern, '</p>', $content);
                                    // Sanitize HTML to prevent XML parsing errors
                                    $htmlContent = $this->sanitizeHtmlForPhpWord($content);
                                    Html::addHtml($section, $htmlContent, false, false);

                                    $htmlGlobal .= $htmlContent;
                                }

                                $add_space = false;
                                $day_free = false;
                            }
                        }
                        if ($service["type"] == "flight") {
                            // Datos de vuelos..
                            $content = "";

                            if ($service["code_flight"] == "AEIFLT") {
                                if ($service["destiny"] == "LIM") {
                                    $content = sprintf($trad->flight_international_to, $service["destiny"]);
                                }

                                if ($service["origin"] == "LIM") {
                                    $content = sprintf($trad->flight_international_from, $service["origin"]);
                                }
                            }

                            if ($service["code_flight"] == "AECFLT") {
                                $content = sprintf($trad->flight_national, $service["origin"], $service["destiny"]);

                                if (empty($service['origin'])) {
                                    $content = str_replace([' from ', ' de ', ' desde '], '', $content);
                                }

                                if (empty($service['destiny'])) {
                                    $content = str_replace([' to ', ' a ', ' para '], '', $content);
                                }
                            }

                            if (!empty($content)) {
                                $htmlContent = '<p style="color:#808080;text-align:justify;">' . $content . '</p>';
                                Html::addHtml($section, $htmlContent, false, false);

                                $htmlGlobal .= $htmlContent;

                                $add_space = false;
                            }
                        }
                    }
                }
            }

            $unique_hotels = collect($services)
                ->where('type', '=', 'hotel')
                ->where('accommodation', '>', 0)
                ->unique('key')
                ->values();


            if ($add_space) {
                $section->addTextBreak(1);
            }

            $destiny = '';

            if ($service["type"] == "service" || $service["type"] == "hotel") {
                $destiny = $service["services"]["destiny"];
            }

            if ($service["type"] == "flight") {
                $destiny = $service["destiny"];
            }

            if ($day_free) {
                if (!empty($destiny)) {
                    $content = '<p style="color:#808080;text-align:justify;">' . sprintf($trad->day_free, ucwords(strtolower($destiny))) . '</p>';
                    $htmlContent = $this->sanitizeHtmlForPhpWord($content);
                    Html::addHtml($section, $htmlContent, false, false);
                    $htmlGlobal .= $htmlContent;
                }
            }

            //Imprimo los hoteles despues de los textos de los servicios
            if (count($unique_hotels) > 0) {
                foreach ($unique_hotels as $service) {

                    if (@$service["type"] == "hotel" && $service["key"] == $all_hotels[(count($all_hotels) - 1)]) {
                        $flag_breakfast = true;
                    }

                    if (@$service["type"] == "hotel") {
                        $listItemRunHotel = $section->addListItemRun(0, $predefinedMultilevel, 'P-Style');

                        $content = '';

                        if ($service['accommodation'] > 0) {
                            $content = sprintf($trad->hotel_and_meal_description, ucwords(strtolower($service["services"]["destiny"])));
                        }

                        if ($flag_breakfast) {
                            if (!empty($content)) {
                                $content .= ((empty($foods)) ? (' ' . $trad->and . ' ') : ', ');
                            }

                            $content .= strtolower($trad->breakfast);

                            if ($service['accommodation'] == 0) {
                                $content .= sprintf($trad->hotel_description, ucwords(strtolower($service["services"]["destiny"])));
                            }
                        }

                        if (!empty($foods)) {
                            $lastCommaPos = strrpos($foods, ",");

                            if ($lastCommaPos !== false) {
                                $foods = substr_replace($foods, " " . $trad->and, $lastCommaPos, 1);
                            }

                            $content .= ', ' . strtolower($foods);
                        }

                        if ($flag_breakfast || !empty($foods)) {
                            $content .= ' ' . $trad->included;
                            $flag_foods = true;
                        }

                        $listItemRunHotel->addText(htmlspecialchars(ucfirst($content) . '.'), 'myOwnStyle');

                        // Validación de acomodos.. (?)
                        if ($service['accommodation'] > 0) {
                            break;
                        }
                    }
                }
            } else {
                $content = '';

                if (!$flag_foods && (!empty($foods) || $flag_breakfast)) {

                    if ($flag_breakfast) {
                        $content .= ucwords(strtolower($trad->breakfast));

                        if (!empty($foods)) {
                            $lastCommaPos = strrpos($foods, ",");

                            if ($lastCommaPos !== false) {
                                $foods = substr_replace($foods, " " . $trad->and, $lastCommaPos, 1);
                            }

                            $content .= ', ' . strtolower($foods);
                        }
                    } else {
                        if (!empty($foods)) {
                            $lastCommaPos = strrpos($foods, ",");

                            if ($lastCommaPos !== false) {
                                $foods = substr_replace($foods, " " . $trad->and, $lastCommaPos, 1);
                            }

                            $content .= strtolower($foods);
                        }
                    }

                    $content .= ' ' . $trad->included;

                    $listItemRunHotel = $section->addListItemRun(0, $predefinedMultilevel, 'P-Style');
                    $listItemRunHotel->addText(htmlspecialchars(ucfirst($content) . '.'), 'myOwnStyle');
                }
            }


            foreach ($services as $service) {
                // Verifica si el tipo es 'service' y si 'include_accommodation' es igual a 1
                if (isset($service['type']) && $service['type'] == 'service' && isset($service['include_accommodation']) && $service['include_accommodation'] == 1) {
                    // Verifica si el campo 'accommodation' existe dentro de 'services'
                    if (isset($service['services']['accommodation'])) {
                        // Obtén el id del servicio
                        $serviceId = $service['services']['id'];

                        // Verifica si el id del servicio existe en $resultAccommodations
                        if (isset($resultAccommodations[$serviceId])) {
                            // Obtén el total de alojamientos para este servicio
                            $totalAccommodations = $resultAccommodations[$serviceId];

                            // Inicializa el contador de alojamientos impresos para este servicio si no existe
                            if (!isset($printedAccommodations[$serviceId])) {
                                $printedAccommodations[$serviceId] = 0;
                            }

                            // Imprime el alojamiento solo si no es el último
                            if ($printedAccommodations[$serviceId] < $totalAccommodations - 1) {
                                $content = $service['services']['accommodation'];
                                $listItemRunHotel = $section->addListItemRun(0, $predefinedMultilevel, 'P-Style');
                                $listItemRunHotel->addText(htmlspecialchars(ucfirst($content) . '.'), 'myOwnStyle');
                            }

                            // Incrementa el contador de alojamientos impresos para este servicio
                            $printedAccommodations[$serviceId]++;
                        }
                    }
                }
            }

            $htmlContent = '<p></p>';
            Html::addHtml($section, $htmlContent, false, false);

            $htmlGlobal .= $htmlContent;

            $k++;

            if (!empty($images) and $params['user_id'] != 1) {
                $images = array_unique($images);

                // Filtrar imágenes válidas
                $validImages = [];
                foreach ($images as $image) {
                    if (!$image) {
                        continue;
                    }
                    // Obtener headers de la URL
                    $headers = @get_headers($image);
                    // Si se obtuvieron headers y contienen el código 200, agregamos la imagen al arreglo de válidas
                    if ($headers && strpos($headers[0], '200') !== false) {
                        $validImages[] = $image;
                    }
                }

                // Agregar una tabla
                if (!empty($validImages)) {
                    $table = $section->addTable();
                    // Agregar una fila a la tabla
                    $table->addRow();

                    foreach ($validImages as $image) {

                        try {
                            $table->addCell(4500)->addImage($image, ['width' => 150, 'height' => 84]);
                        } catch (\Exception $e) {
                            // Puedes registrar el error si lo consideras necesario
                            continue;
                        }
                    }

                    $section->addTextBreak(1, 'space1');

                    $images = [];
                }
            }
        }

        //INICIO TEXTO "FIN DE SERVICIO"
        $phpWord->addParagraphStyle(
            'textend',
            array(
                'align' => 'center',
            )
        );
        $table = $section->addTable();
        $table->addRow(300, array('exactHeight' => true));
        $cell2 = $table->addCell(9500, array(
            'align' => 'center',
            'borderTopSize' => 15,
            'borderBottomSize' => 15,
            'borderRightColor' => 'ffffff',
            'borderLeftColor' => 'ffffff',
            'borderTopColor' => 'b3b182',
            'borderBottomColor' => 'b3b182',
            'bgColor' => '#dad7c6'
        ));
        $cell2->addText(
            htmlspecialchars($trad->endService),
            array('name' => 'Calibri', 'size' => 9, 'color' => 'b3b182', 'bold' => true),
            'textend'
        );
        //FIN TEXTO "FIN DE SERVICIO"
        $section->addTextBreak(1, 'space1');
        ///------------ FIN RECORRIDO DE SERVICIOS

        $styleCell = array(
            'valign' => 'center',
            'color' => '#ffffff',
            'name' => 'Calibri',
            'size' => 10,
            'bold' => true
        );
        $phpWord->addParagraphStyle(
            'titleTable',
            array(
                'align' => 'center',
                'spaceAfter' => 0
            )
        );

        if (!$use_prices) {
            $section->addTextBreak(1, 'space1');
            // $section = $phpWord->addSection();
        }

        //-----------------INICIO INCLUYE
        $phpWord->addParagraphStyle(
            'title',
            array(
                'align' => 'left',
                'wrappingStyle' => 'infront',
            )
        );
        $section->addText(
            htmlspecialchars($trad->titleInclude),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);

        //-----------------INICIO TITULO ACOMODACION
        $listHotels = [];
        foreach ($quote_services as $key => $quote_service) {
            if ($quote_service["type"] == 'hotel') {
                $total_accommodation = 1;
                if ($total_accommodation > 0) {
                    $quote_services[$key]['key'] = $quote_service["hotel"]["id"] . '_' . $quote_service["nights"] . '_' . convertDate($quote_service["date_in"], '/', '-', 1);
                    array_push($listHotels, $quote_services[$key]);
                }
            }
        }

        $cantNigths = [];
        $nn = 0;
        $listHotels = $this->super_unique($listHotels, 'key');
        for ($i = 0; $i < count($listHotels); $i++) {
            if ($i == 0) {
                $cantNigths[$nn] = $listHotels[$i];
                $cantNigths[$nn]['CANT'] = ((int)$cantNigths[$nn]['nights']);
            } else {
                if (trim($listHotels[$i]['hotel']['city']['translations'][0]['value']) == trim($cantNigths[$nn]['hotel']['city']['translations'][0]['value'])) {
                    $cantNigths[$nn]['CANT'] += ((int)$listHotels[$i]['nights']);
                } else {
                    $nn++;
                    $cantNigths[$nn] = $listHotels[$i];
                    $cantNigths[$nn]['CANT'] = ((int)$cantNigths[$nn]['nights']);
                }
            }
        }


        for ($i = 0; $i < count($cantNigths); $i++) {
            $cantDay = $cantNigths[$i]['CANT'];
            if ($cantDay == 1) {
                $textday = $trad->nightStay;
            } else {
                $textday = $trad->nightStayp;
            }
            $ciudad = strtolower(trim($cantNigths[$i]['hotel']['city']['translations'][0]['value']));
            $section->addListItem(
                htmlspecialchars($cantDay . ' ' . $textday . ' ' . ucwords($ciudad)),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
        }
        //FIN LISTA DE NOCHES POR CIUDAD

        foreach ($allFoods as $service) {
            if (@$service["service"]["type"] == "hotel") {
                $content = sprintf($trad->city_and_meal_description, $service["food"], ucwords(strtolower($service["service"]["services"]["destiny"])));
                $section->addListItem(
                    htmlspecialchars(ucwords($content)),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
            }
        }


        //-----------------INICIO TITULO TRASLADOS Y TOURS
        $phpWord->addParagraphStyle('P-Styleguiado', array('spaceAfter' => 5, 'marginLeft' => 0, 'color' => 'c82f6b'));

        $packageServiceCategoryId = 2;

        foreach ($quote_services as $quote_service) {
            if ($quote_service["type"] !== "service") {
                continue;
            }

            $inclusions = collect($quote_service['service']['inclusions'])->groupBy('day');

            $section->addListItem(
                htmlspecialchars($quote_service["service"]["service_translations"][0]["name"]),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );

            if ($inclusions->isEmpty() || $quote_service['service']['service_sub_category']['service_category_id'] !== $packageServiceCategoryId) {
                continue;
            }

            $section->addTextBreak(1, 'space1');

            $inclusionsTable = $section->addTable('tarifas');
            $inclusionsTable->addRow(340, array('exactHeight' => true));

            $days = $inclusions->keys()->sort();

            $highestNumberOfInclusionsForADay = $inclusions->map(function ($inclusions) {
                return count($inclusions);
            })->max();

            $cellWidth = intval(9000 / count($days));

            foreach ($days as $day) {
                $inclusionsTable->addCell($cellWidth, $styleCell)->addText(
                    htmlspecialchars("{$trad->day} $day"),
                    ['name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true],
                    'titleTable'
                );
            }

            foreach (range(0, $highestNumberOfInclusionsForADay - 1) as $index) {
                $inclusionsTable->addRow(200, ['exactHeight' => false]);

                foreach ($days as $day) {
                    if (isset($inclusions[$day][$index])) {
                        $inclusionsTable->addCell($cellWidth)->addText(
                            htmlspecialchars($inclusions[$day][$index]['inclusions']['translations'][0]['value']),
                            ['name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'],
                            'titleTable'
                        );
                    } else {
                        $inclusionsTable->addCell($cellWidth)->addText('');
                    }
                }
            }

            $section->addTextBreak(1, 'space1');
        }
        //------------------FIN TITULO TRASLADOS Y TOURS
        //------------------FIN INCLUYE
        $predefinedMultilevel = array('listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_SQUARE_FILLED); //temporalmente para probar
        $section->addTextBreak(1, 'space1');

        //-----------------INICIO  TITULO NO INCLUYE
        $phpWord->addParagraphStyle(
            'title',
            array(
                'align' => 'left',
                'wrappingStyle' => 'infront',
            )
        );
        $section->addText(
            htmlspecialchars($trad->titleNotInclude),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);
        $phpWord->addParagraphStyle('P-Styleguiado', array('spaceAfter' => 5, 'marginLeft' => 0, 'color' => '#c82f6b'));
        $phpWord->addFontStyle(
            'StyleSquare',
            array('name' => 'Calibri', 'color' => '#5a5a58', 'size' => 10, 'bold' => false)
        );
        $section->addListItem(
            htmlspecialchars($trad->textNotInclude_line1),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textNotInclude_line2),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textNotInclude_line3),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textNotInclude_line4),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textNotInclude_line5),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textNotInclude_line6),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->textNotInclude_line7),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        //-----------------FIN  TITULO NO INCLUYE
        $section->addTextBreak(1, 'space1'); //Espacio

        // ESTILOS TABLA..
        $styleTable = array(
            'borderSize' => 6,
            'borderColor' => 'A69F88',
            'cellMargin' => 80,
            'align' => 'center',
            'marginRight' => 10
        );
        $styleFirstRow = array('borderBottomColor' => 'A69F88', 'bgColor' => 'A69F88');
        $phpWord->addParagraphStyle(
            'titleTable',
            array(
                'align' => 'center',
                'spaceAfter' => 0
            )
        );
        $phpWord->addParagraphStyle(
            'cellDefault',
            array(
                'align' => 'center',
                'spaceAfter' => 0
            )
        );
        $phpWord->addTableStyle('tarifas', $styleTable, $styleFirstRow);
        // FIN ESTILOS TABLA..

        // dd($rates);

        // TARIFAS DEL ITINERARIO..
        if (!empty($rates)) {
            // $section = $phpWord->addSection();
            $section->addTextBreak(1, 'space1'); //Espacio

            $phpWord->addParagraphStyle(
                'title',
                array(
                    'align' => 'left',
                    'wrappingStyle' => 'infront',
                )
            );
            $section->addText(
                htmlspecialchars($trad->titlePrices),
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'title'
            );
            $section->addLine($linestyle);

            $label_commission = $has_commission_label ? $trad->with_commission : $trad->descriptionPrices;

            $htmlContent = '<p>' . $label_commission . '</p>';
            Html::addHtml($section, $htmlContent, false, false);

            // Estilos para la tabla
            $tableStyle = array(
                'borderSize' => 6,
                'borderColor' => '999999',
                'cellMargin' => 80
            );
            $firstRowStyle = array('bgColor' => 'D7D2C1', 'color' => 'FFFFFF');
            $cellVCentered = array('valign' => 'center');
            $cellHCentered = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER);
            $phpWord->addTableStyle('Table', $tableStyle, $firstRowStyle);

            // Agregar una tabla a la sección
            $table = $section->addTable('Table');

            // Agregar la primera fila de encabezado
            $table->addRow();
            if ($flag_shared || $flag_private) {
                $table->addCell(2000, $cellVCentered)->addText('', null, $cellHCentered);
            }
            $table->addCell(2000, $cellVCentered)->addText($trad->category, null, $cellHCentered);
            if ($flag_shared || $flag_private) {
                if ($show['simple']) {
                    $table->addCell(2000, $cellVCentered)->addText($trad->titleRateSimple, null, $cellHCentered);
                }
                if ($show['double']) {
                    $table->addCell(2000, $cellVCentered)->addText($trad->titleRateDoble, null, $cellHCentered);
                }
                if ($show['triple']) {
                    $table->addCell(2000, $cellVCentered)->addText($trad->titleRateTriple, null, $cellHCentered);
                }
                if ($package['allow_child']) {
                    if ($show['with_bed']) {
                        $table->addCell(2000, $cellVCentered)->addText($trad->titleRateWithBed, null, $cellHCentered);
                    }
                    if ($show['without_bed']) {
                        $table->addCell(2000, $cellVCentered)->addText($trad->titleRateWithoutBed, null, $cellHCentered);
                    }
                }
            } else {
                for ($i = 0; $i < 6; $i++) {
                    if ($show[$i]) {
                        $table->addCell(2000, $cellVCentered)->addText('Min ' . ($i + 1), null, $cellHCentered);
                    }
                }
                if ($show['diff_double']) {
                    $table->addCell(2000, $cellVCentered)->addText('SGL SUPP', null, $cellHCentered);
                }
                if ($show['diff_triple']) {
                    $table->addCell(2000, $cellVCentered)->addText('TRP SUPP', null, $cellHCentered);
                }
            }

            // Agregar las filas de datos

            if ($flag_shared || $flag_private) {
                $index = 0;

                foreach ($rates as $key_rate => $rate) {
                    $table->addRow();

                    if ($index == 0 && $flag_shared) {
                        $table->addCell(2000, [
                            'vMerge' => 'restart',
                            'valign' => 'center',
                            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
                        ])->addText($trad->titleShared, null, $cellHCentered);
                    } else {
                        $table->addCell(4000, ['vMerge' => 'continue']);
                    }

                    $table->addCell(4000, $cellVCentered)->addText($rate['category'], null, $cellHCentered);
                    if ($show['simple']) {
                        $finalPrice = $this->getPriceAmount(
                            $rate['simple'],
                            $client_commission_status,
                            $user_type_id,
                            $client_commission
                        );
                        $table->addCell(2000, $cellVCentered)->addText((float)roundLito($finalPrice, 'hotel'), null, $cellHCentered);
                    }
                    if ($show['double']) {
                        $finalPrice = $this->getPriceAmount(
                            $rate['double'],
                            $client_commission_status,
                            $user_type_id,
                            $client_commission
                        );
                        $table->addCell(2000, $cellVCentered)->addText((float)roundLito($finalPrice, 'hotel'), null, $cellHCentered);
                    }
                    if ($show['triple']) {
                        $finalPrice = $this->getPriceAmount(
                            $rate['triple'],
                            $client_commission_status,
                            $user_type_id,
                            $client_commission
                        );
                        $table->addCell(2000, $cellVCentered)->addText((float)roundLito($finalPrice, 'hotel'), null, $cellHCentered);
                    }
                    if ($package['allow_child']) {
                        if ($show['with_bed']) {
                            $finalPrice = $this->getPriceAmount(
                                $rate['child_with_bed'],
                                $client_commission_status,
                                $user_type_id,
                                $client_commission
                            );
                            $table->addCell(2000, $cellVCentered)->addText((float)roundLito($finalPrice, 'hotel'), null, $cellHCentered);
                        }
                        if ($show['without_bed']) {
                            $finalPrice = $this->getPriceAmount(
                                $rate['child_without_bed'],
                                $client_commission_status,
                                $user_type_id,
                                $client_commission
                            );
                            $table->addCell(2000, $cellVCentered)->addText((float)roundLito($finalPrice, 'hotel'), null, $cellHCentered);
                        }
                    }

                    $index++;
                }

                if (isset($rates[$key_rate]['simple_priv'])) {
                    $table->addRow();
                    $table->addCell(4000, [
                        'gridSpan' => 2,
                        'valign' => 'center',
                        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
                    ])->addText($trad->titlePrivate, null, $cellHCentered);
                    if ($show['simple']) {
                        $baseAmount = max(array_column($rates, 'simple_priv'));
                        $finalPrice = $this->getPriceAmount(
                            $baseAmount,
                            $client_commission_status,
                            $user_type_id,
                            $client_commission
                        );
                        $table->addCell(2000, $cellVCentered)->addText((float)roundLito($finalPrice, 'hotel'), null, $cellHCentered);
                    }
                    if ($show['double']) {
                        $baseAmount = max(array_column($rates, 'double_priv'));
                        $finalPrice = $this->getPriceAmount(
                            $baseAmount,
                            $client_commission_status,
                            $user_type_id,
                            $client_commission
                        );
                        $table->addCell(2000, $cellVCentered)->addText((float)roundLito($finalPrice, 'hotel'), null, $cellHCentered);
                    }
                    if ($show['triple']) {
                        $baseAmount = max(array_column($rates, 'triple_priv'));
                        $finalPrice = $this->getPriceAmount(
                            $baseAmount,
                            $client_commission_status,
                            $user_type_id,
                            $client_commission
                        );
                        $table->addCell(2000, $cellVCentered)->addText((float)roundLito($finalPrice, 'hotel'), null, $cellHCentered);
                    }
                    if ($package['allow_child']) {
                        if ($show['with_bed']) {
                            $baseAmount = max(array_column($rates, 'child_with_bed_priv'));
                            $finalPrice = $this->getPriceAmount(
                                $baseAmount,
                                $client_commission_status,
                                $user_type_id,
                                $client_commission
                            );
                            $table->addCell(2000, $cellVCentered)->addText((float)roundLito($finalPrice, 'hotel'), null, $cellHCentered);
                        }
                        if ($show['without_bed']) {
                            $baseAmount = max(array_column($rates, 'child_without_bed_priv'));
                            $finalPrice = $this->getPriceAmount(
                                $baseAmount,
                                $client_commission_status,
                                $user_type_id,
                                $client_commission
                            );
                            $table->addCell(2000, $cellVCentered)->addText((float)roundLito($finalPrice, 'hotel'), null, $cellHCentered);
                        }
                    }
                }
            } else {
                foreach ($rates as $rate) {
                    $table->addRow();
                    $table->addCell(4000, $cellVCentered)->addText($rate['category'], null, $cellHCentered);

                    for ($i = 0; $i < 6; $i++) {
                        if ($show[$i]) {
                            $finalPrice = $this->getPriceAmount(
                                $rate[$i],
                                $client_commission_status,
                                $user_type_id,
                                $client_commission
                            );
                            $table->addCell(2000, $cellVCentered)->addText((float)roundLito($finalPrice, 'hotel'), null, $cellHCentered);
                        }
                    }
                    if ($show['diff_double']) {
                        $finalPrice = $this->getPriceAmount(
                            $rate['diff_double'],
                            $client_commission_status,
                            $user_type_id,
                            $client_commission
                        );
                        $table->addCell(2000, $cellVCentered)->addText(($rate['diff_double'] != 0) ? ((float)roundLito($finalPrice)) : '-', null, $cellHCentered);
                    }
                    if ($show['diff_triple']) {
                        $finalPrice = $this->getPriceAmount(
                            $rate['diff_triple'],
                            $client_commission_status,
                            $user_type_id,
                            $client_commission
                        );
                        $table->addCell(2000, $cellVCentered)->addText(($rate['diff_triple'] != 0) ? ((float)roundLito($finalPrice)) : '-', null, $cellHCentered);
                    }
                }
            }

            $section->addTextBreak(1, 'space1');

            $textRun = $section->addTextRun(array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'));
            $textRun->addText(htmlspecialchars($trad->hotelsLinkText));

            $trad_toursLinkText = $trad->toursLinkText;

            if (!empty(array_intersect($package_ids, $linksNewHotelsAndToursOnlyPackages))) {
                $linkHotels = $trad->hotelsLink_2;
                $lintTours = $trad->toursLink_2;
            } elseif (in_array($package_mirror->tag_id, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25])) {
                // 👆 Aquí agregamos la condición para tus tags_id extraídos
                $linkHotels = $trad->hotelsLink_program_general;
                $lintTours  = $trad->toursLink;
            } elseif ($package_mirror->tag_id == 45) {
                $linkHotels = $trad->hotelsLink_boutique;
                $lintTours  = $trad->toursLink;
            } elseif ($package_mirror->tag_id == 28 || $package_mirror->tag_id == 27) {
                $linkHotels = $trad->hotelsLink_regional;
                $lintTours = $trad->hotelsLink_2_regional;
                $trad_toursLinkText = $trad->toursLinkText_regional;
            } else {
                $linkHotels = $trad->hotelsLink;
                $lintTours = $trad->toursLink;
            }
            $textRun->addLink($linkHotels, htmlspecialchars($trad->general_recommendations_here), array('bold' => true, 'color' => '0000FF'));


            $textRun = $section->addTextRun(array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'));
            $textRun->addText(htmlspecialchars($trad_toursLinkText));
            $textRun->addLink($lintTours, htmlspecialchars($trad->general_recommendations_here), array('bold' => true, 'color' => '0000FF'));

            $section->addTextBreak(1, 'space1');

            // $section = $phpWord->addSection();
        }

        if ($flag_disclaimer_mapi) {
            $package_mirror = Packages::find($package['id']);
            if ($package_mirror->tag_id != 28) {
                //-----------------INICIO INFORMACIÓN IMPORTANTE SOBRE EL NUEVO REGLAMENTO DE INGRESOS A MACHU PICCHU
                //TITULO IMPORTENTE
                $section->addText(
                    htmlspecialchars($trad->titleImportant),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                    'title'
                );
                $section->addLine($linestyle);

                $section->addText(
                    htmlspecialchars($trad->textImportant),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                    'textImportant'
                );
                //ITEMS
                $section->addListItem(
                    htmlspecialchars($trad->textImportant_data1),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                //$section->addListItem(htmlspecialchars($trad->textImportant_data2), 0, 'StyleSquare', $predefinedMultilevel,'P-Styleguiado');
                $section->addListItem(
                    htmlspecialchars($trad->textImportant_data3),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addListItem(
                    htmlspecialchars($trad->textImportant_data5),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );

                $section->addTextBreak(1, 'space1');

                $section->addText(
                    htmlspecialchars($trad->textImportant_2),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                    'textImportant'
                );
                //ITEMS
                $section->addListItem(
                    htmlspecialchars($trad->textImportant_data6),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addListItem(
                    htmlspecialchars($trad->textImportant_data7),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addListItem(
                    htmlspecialchars($trad->textImportant_data8),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addListItem(
                    htmlspecialchars($trad->textImportant_data9),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                //-----------------FIN INFORMACION IMPORTANTE SOBRE EL NUEVO REGLAMENTO DE INGRESOS A MACHU PICCHU

                $section->addTextBreak(1, 'space1');

                //-----------------INICIO  RECOMENDACIONES INSTRUCCIONES PARA TRASLADO DE EQUIPAJE A BORDO DEL TREN RUTA A MACHU PICCHU
                //TITULO
                //-----------------INICIO IMAGEN MOCHILA
                $section->addImage(
                    public_path() . '/images/word/mochila_new.png',
                    array(
                        'width' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(1.39),
                        'height' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(2.08),
                        'positioning' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE,
                        'posHorizontal' => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_RIGHT,
                        'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_MARGIN,
                        'posVertical' => \PhpOffice\PhpWord\Style\Image::POSITION_VERTICAL_TOP,
                        'posVerticalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_LINE,
                    )
                );

                $table = $section->addTable('tarifas');
                $table->addRow(300, array('exactHeight' => true));
                $table->addCell(1900, $styleCell)->addText(
                    htmlspecialchars(''),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '#ffffff', 'bold' => true),
                    'titleTable'
                );
                $table->addCell(1300, $styleCell)->addText(
                    htmlspecialchars($trad->thPeso),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '#ffffff', 'bold' => true),
                    'titleTable'
                );
                $table->addCell(3500, $styleCell)->addText(
                    htmlspecialchars($trad->size),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '#ffffff', 'bold' => true),
                    'titleTable'
                );

                $table->addRow(300, array('exactHeight' => true));
                $table->addCell(1900)->addText(
                    htmlspecialchars('1 ' . $trad->tdbolso),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58'),
                    'titleTable'
                );
                $table->addCell(1300)->addText(
                    htmlspecialchars('8kg/17.6 lb'),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58'),
                    'titleTable'
                );
                $table->addCell(3500)->addText(
                    htmlspecialchars("62 inches/157cm"),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58'),
                    'titleTable'
                );

                $section->addTextBreak(1, 'space1');
                $section->addTextBreak(1, 'space1');
            }
        }

        if ($flag_disclaimer_galapagos) {
            //-----------------INICIO INFORMACIÓN IMPORTANTE SOBRE EL NUEVO REGLAMENTO DE INGRESOS A GALAPAGOS
            //TITULO IMPORTENTE
            $section->addText(
                htmlspecialchars($trad->titleImportantGalapagos),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'title'
            );
            $section->addLine($linestyle);

            $section->addText(
                htmlspecialchars($trad->importantGalapagos_text_1),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'paragraph'
            );
            $section->addText(
                htmlspecialchars($trad->importantGalapagos_text_2),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'paragraph'
            );

            $section->addTextBreak(1, 'space1');

            $section->addText(
                htmlspecialchars($trad->titleImportantGalapagos_2),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'title'
            );

            $section->addSpaceBreak(1, 'space1');

            $section->addText(
                htmlspecialchars($trad->importantGalapagos_fee_rate),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'textImportant'
            );
            $section->addText(
                htmlspecialchars($trad->importantGalapagos_fee_payment),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'paragraph'
            );

            $section->addText(
                htmlspecialchars($trad->importantGalapagos_fee_instructions_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'bold' => true, 'wrappingStyle' => 'infront'),
                'textImportant'
            );
            $section->addText(
                htmlspecialchars($trad->importantGalapagos_fee_instructions_text),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'paragraph'
            );

            $section->addListItem(
                htmlspecialchars($trad->importantGalapagos_fee_list_1),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
            $section->addListItem(
                htmlspecialchars($trad->importantGalapagos_fee_list_2),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
            $section->addTextBreak(1, 'space1');
            $section->addText(
                '*********************************************',
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'paragraph'
            );
            $section->addTextBreak(1, 'space1');

            $section->addText(
                htmlspecialchars($trad->importantGalapagos_tct_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'title'
            );

            $section->addSpaceBreak(1, 'space1');

            $section->addText(
                htmlspecialchars($trad->importantGalapagos_tct_rate),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'textImportant'
            );
            $section->addText(
                htmlspecialchars($trad->importantGalapagos_tct_payment),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'paragraph'
            );
            $section->addSpaceBreak(1, 'space1');

            $section->addText(
                htmlspecialchars($trad->importantGalapagos_tct_process_online_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'bold' => true, 'wrappingStyle' => 'infront'),
                'textImportant'
            );
            $listItemRun = $section->addListItemRun(0, 'StyleSquare', $predefinedMultilevel);
            $listItemRun->addText(
                htmlspecialchars($trad->importantGalapagos_tct_process_online_list_1 . ' '),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080')
            );
            $listItemRun->addLink(
                'https://siig-cgreg.gobiernogalapagos.gob.ec/tct/emission?lang=es',
                'https://siig-cgreg.gobiernogalapagos.gob.ec/tct/emission?lang=es',
                array('color' => '0000FF', 'underline' => 'single', 'size' => 10, 'name' => 'Calibri')
            );
            $section->addListItem(
                htmlspecialchars($trad->importantGalapagos_tct_process_online_list_2),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
            $section->addListItem(
                htmlspecialchars($trad->importantGalapagos_tct_process_online_list_3),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
            $section->addListItem(
                htmlspecialchars($trad->importantGalapagos_tct_process_online_list_4),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
            $section->addListItem(
                htmlspecialchars($trad->importantGalapagos_tct_process_online_list_5),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );

            $section->addText(
                htmlspecialchars($trad->importantGalapagos_tct_note_1),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'paragraph'
            );
            $section->addText(
                htmlspecialchars($trad->importantGalapagos_tct_note_2),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'paragraph'
            );
            $section->addSpaceBreak(1, 'space1');

            $section->addText(
                htmlspecialchars($trad->importantGalapagos_tct_process_presential_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'bold' => true, 'wrappingStyle' => 'infront'),
                'textImportant'
            );
            $section->addText(
                htmlspecialchars($trad->importantGalapagos_tct_process_presential_place),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'paragraph'
            );
            $section->addText(
                htmlspecialchars($trad->importantGalapagos_tct_process_presential_docs_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'bold' => true, 'wrappingStyle' => 'infront'),
                'paragraph'
            );
            $section->addListItem(
                htmlspecialchars($trad->importantGalapagos_tct_process_presential_list_1),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
            $section->addListItem(
                htmlspecialchars($trad->importantGalapagos_tct_process_presential_list_2),
                0,
                'StyleSquare',
                $predefinedMultilevel,
                'P-Styleguiado'
            );
            $section->addText(
                htmlspecialchars($trad->importantGalapagos_tct_process_presential_rate),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'textImportant'
            );
            $section->addTextBreak(1, 'space1');
            $section->addText(
                '*********************************************',
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'textImportant'
            );
            $section->addSpaceBreak(1, 'space1');
            $section->addText(
                htmlspecialchars($trad->importantGalapagos_abg_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'title'
            );
            $section->addTextBreak(1, 'space1');
            $section->addText(
                htmlspecialchars($trad->importantGalapagos_abg_text_1),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'textImportant'
            );
            $section->addText(
                htmlspecialchars($trad->importantGalapagos_abg_text_2),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'textImportant'
            );
            $section->addText(
                htmlspecialchars($trad->importantGalapagos_abg_prohibited_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'bold' => true, 'wrappingStyle' => 'infront'),
                'textImportant'
            );
            $section->addText(
                htmlspecialchars($trad->importantGalapagos_abg_prohibited_text),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'textImportant'
            );

            $textRun = $section->addTextRun('textImportant');
            $textRun->addText(
                htmlspecialchars($trad->importantGalapagos_abg_note . ' '),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080')
            );
            $textRun->addLink(
                htmlspecialchars($trad->importantGalapagos_abg_link),
                htmlspecialchars($trad->importantGalapagos_abg_link),
                array('color' => '0000FF', 'underline' => 'single', 'size' => 10, 'name' => 'Calibri')
            );

            $section->addTextBreak(1);
        }


        // RECOMENDACIONES CULTURA..
        $section->addText(
            htmlspecialchars($trad->culture_title),
            array(
                'name' => 'Calibri',
                'size' => 10,
                'color' => '5a5a58',
                'bold' => true,
                'wrappingStyle' => 'infront'
            ),
            'title'
        );
        $section->addLine($linestyle);
        $section->addListItem(
            htmlspecialchars($trad->culture_item_data1),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->culture_item_data2),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->culture_item_data3),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->culture_item_data4),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->culture_item_data5),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->culture_item_data6),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->culture_item_data7),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->culture_item_data8),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->culture_item_data9),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addListItem(
            htmlspecialchars($trad->culture_item_data10),
            0,
            'StyleSquare',
            $predefinedMultilevel,
            'P-Styleguiado'
        );
        $section->addTextBreak(1, 'space1'); //Espacio
        // FIN CULTURA..

        if (!$flag_disclaimer_latam) {

            if ($package_mirror->tag_id != 28) {
                // TEXTO MASI..
                //TITULO
                // $section = $phpWord->addSection();
                $section->addText(
                    htmlspecialchars($trad->masi_title),
                    array(
                        'name' => 'Calibri',
                        'size' => 10,
                        'color' => '5a5a58',
                        'bold' => true,
                        'wrappingStyle' => 'infront'
                    ),
                    'title'
                );
                $section->addLine($linestyle);
                //PARRAFO 1
                $section->addText(
                    htmlspecialchars($trad->masi_parrafo_1),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'wrappingStyle' => 'infront'),
                    'textImportant'
                );

                $section->addListItem(
                    htmlspecialchars($trad->masi_texto_1),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addListItem(
                    htmlspecialchars($trad->masi_texto_2),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addListItem(
                    htmlspecialchars($trad->masi_texto_3),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addListItem(
                    htmlspecialchars($trad->masi_texto_4),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addListItem(
                    htmlspecialchars($trad->masi_texto_5),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addListItem(
                    htmlspecialchars($trad->masi_texto_6),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addTextBreak(1, 'space1');
                //PARRAFO 2
                $section->addText(
                    htmlspecialchars("**********************************************"),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                    'textImportant'
                );
                $section->addTextBreak(1, 'space1');

                $section->addText(
                    htmlspecialchars($trad->masi_title_numbers),
                    array(
                        'name' => 'Calibri',
                        'size' => 10,
                        'color' => '5a5a58',
                        'bold' => true,
                        'wrappingStyle' => 'infront'
                    ),
                    'title'
                );

                if ($ignore_contact) {
                    $section->addListItem(
                        htmlspecialchars($trad->masi_numbers_1),
                        0,
                        'StyleSquare',
                        $predefinedMultilevel,
                        'P-Styleguiado'
                    );
                    $section->addListItem(
                        htmlspecialchars($trad->masi_numbers_2),
                        0,
                        'StyleSquare',
                        $predefinedMultilevel,
                        'P-Styleguiado'
                    );
                    $section->addListItem(
                        htmlspecialchars($trad->masi_numbers_3),
                        0,
                        'StyleSquare',
                        $predefinedMultilevel,
                        'P-Styleguiado'
                    );
                    $section->addListItem(
                        htmlspecialchars($trad->masi_numbers_4),
                        0,
                        'StyleSquare',
                        $predefinedMultilevel,
                        'P-Styleguiado'
                    );
                    $section->addListItem(
                        htmlspecialchars($trad->masi_numbers_5),
                        0,
                        'StyleSquare',
                        $predefinedMultilevel,
                        'P-Styleguiado'
                    );
                    $section->addListItem(
                        htmlspecialchars($trad->masi_numbers_6),
                        0,
                        'StyleSquare',
                        $predefinedMultilevel,
                        'P-Styleguiado'
                    );
                } else {
                    if ($market_id == 6) { // 1️⃣ Estados Unidos y Canadá
                        $section->addListItem(
                            htmlspecialchars($trad->masi_numbers_1),
                            0,
                            'StyleSquare',
                            $predefinedMultilevel,
                            'P-Styleguiado'
                        );
                        $section->addListItem(
                            htmlspecialchars($trad->masi_numbers_2),
                            0,
                            'StyleSquare',
                            $predefinedMultilevel,
                            'P-Styleguiado'
                        );
                    } elseif (in_array($market_id, [8, 7, 15])) { // 2️⃣ Europa y Asia Pacífico
                        $section->addListItem(
                            htmlspecialchars($trad->masi_numbers_3),
                            0,
                            'StyleSquare',
                            $predefinedMultilevel,
                            'P-Styleguiado'
                        );
                        $section->addListItem(
                            htmlspecialchars($trad->masi_numbers_4),
                            0,
                            'StyleSquare',
                            $predefinedMultilevel,
                            'P-Styleguiado'
                        );
                    } else { // 3️⃣ LATAM, Italia, España y Portugal (Otros mercados)
                        $section->addListItem(
                            htmlspecialchars($trad->masi_numbers_5),
                            0,
                            'StyleSquare',
                            $predefinedMultilevel,
                            'P-Styleguiado'
                        );
                        $section->addListItem(
                            htmlspecialchars($trad->masi_numbers_6),
                            0,
                            'StyleSquare',
                            $predefinedMultilevel,
                            'P-Styleguiado'
                        );
                    }
                }



                $section->addTextBreak(1, 'space1');
                //------------------FIN MASI: UNA NUEVA PLATAFORMA DE ASISTENCIA PERSONALIZADA
            }

            //TITULO TERMINOS Y CONDICIONES..
            $section->addText(
                htmlspecialchars($trad->terminos_y_condiciones_title),
                array(
                    'name' => 'Calibri',
                    'size' => 10,
                    'color' => '5a5a58',
                    'bold' => true,
                    'wrappingStyle' => 'infront'
                ),
                'title'
            );
            $section->addLine($linestyle);

            if ($package_mirror->tag_id == 33 || $package_mirror->tag_id == 32 || $package_mirror->tag_id == 31 || $package_mirror->tag_id == 30 || $package_mirror->tag_id == 29) {
                $section->addListItem(htmlspecialchars($trad->terminos_y_condiciones_punto_1_outdoors), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
            }

            if ($package_mirror->tag_id == 28) {
                // Use regional terms for points 1 to 8.
                for ($i = 1; $i <= 8; $i++) {
                    $key = "terminos_y_condiciones_punto_{$i}_regional";
                    $section->addListItem(htmlspecialchars($trad->$key), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
                }
            } elseif ($package_mirror->tag_id == 27) {
                // Use regional terms for points 1 to 6.
                for ($i = 1; $i <= 6; $i++) {
                    $key = "terminos_y_condiciones_punto_{$i}_regional";
                    $section->addListItem(htmlspecialchars($trad->$key), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
                }
                // Then use standard terms for points 1 to 8.
                for ($i = 1; $i <= 8; $i++) {
                    $key = "terminos_y_condiciones_punto_{$i}";
                    $section->addListItem(htmlspecialchars($trad->$key), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
                }
            } else {
                // Use standard terms for points 1 to 8.
                for ($i = 1; $i <= 8; $i++) {
                    $key = "terminos_y_condiciones_punto_{$i}";
                    $section->addListItem(htmlspecialchars($trad->$key), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
                }
            }


            //FIN TERMINOS Y CONDICIONES
            $section->addTextBreak(1, 'space1'); //Espacio

            if ($package_mirror->tag_id != 28 && $package_mirror->tag_id != 27) {
                if ($package['allow_child']) // NO CHILD
                {
                    //TITULO
                    $section->addText(
                        htmlspecialchars($trad->title_condiciones_tercera_personas_nino),
                        array(
                            'name' => 'Calibri',
                            'size' => 10,
                            'color' => '5a5a58',
                            'bold' => true,
                            'wrappingStyle' => 'infront'
                        ),
                        'title'
                    );
                    $section->addLine($linestyle);
                    //ITEMS
                    $section->addListItem(
                        htmlspecialchars($trad->condiciones_tercera_personas_nino_text_1),
                        0,
                        'StyleSquare',
                        $predefinedMultilevel,
                        'P-Styleguiado'
                    );
                    $section->addListItem(
                        htmlspecialchars($trad->condiciones_tercera_personas_nino_text_2),
                        0,
                        'StyleSquare',
                        $predefinedMultilevel,
                        'P-Styleguiado'
                    );
                    $section->addListItem(
                        htmlspecialchars($trad->condiciones_tercera_personas_nino_text_3),
                        0,
                        'StyleSquare',
                        $predefinedMultilevel,
                        'P-Styleguiado'
                    );
                    $section->addTextBreak(1, 'space1'); //Espacio
                }
            }

            //TITULO
            $section->addText(
                htmlspecialchars($trad->textCancelation_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'title'
            );
            $section->addLine($linestyle); // LINEA
            $phpWord->addFontStyle(
                'textBold',
                array('name' => 'Calibri', 'color' => '#5a5a58', 'size' => 10, 'bold' => true)
            );
            $phpWord->addFontStyle(
                'textNormal',
                array('name' => 'Calibri', 'color' => '5a5a58', 'size' => 10, 'bold' => false)
            );
            $phpWord->addParagraphStyle('P-Style', array('spaceAfter' => 5, 'marginLeft' => 360));
            $phpWord->addParagraphStyle('P-Styleguiado', array('spaceAfter' => 5, 'marginLeft' => 0, 'color' => 'c82f6b'));
            //TEXTO
            $section->addText(
                htmlspecialchars($trad->textCancelation_text_1),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'wrappingStyle' => 'infront'),
                'textImportant'
            );

            // INICIO DE TABLA CONDICIONES DE CANCELACION
            $table = $section->addTable('tarifas');
            //ENCABEZADO
            $table->addRow(340, array('exactHeight' => true));
            $table->addCell(4550, $styleCell)->addText(
                htmlspecialchars($trad->textCancelation_tbl_title1),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            $table->addCell(4550, $styleCell)->addText(
                htmlspecialchars($trad->textCancelation_tbl_title2),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            $table->addCell(4550, $styleCell)->addText(
                htmlspecialchars($trad->textCancelation_tbl_title3),
                array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true),
                'titleTable'
            );
            // FILAS 1
            $table->addRow(340, array('exactHeight' => true));
            $table->addCell(1300)->addText(
                htmlspecialchars($trad->textCancelation_tbl_line1_c1),
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                'titleTable'
            );

            if ($package_mirror->tag_id == 28 || $package_mirror->tag_id == 27) {
                $table->addCell(2800)->addText(
                    htmlspecialchars($trad->textCancelation_tbl_line1_c2_regional),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );
            } else {
                $table->addCell(2800)->addText(
                    htmlspecialchars($trad->textCancelation_tbl_line1_c2),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );
            }

            $table->addCell(2800)->addText(
                htmlspecialchars($trad->textCancelation_tbl_line1_c3),
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                'titleTable'
            );
            if ($package_mirror->tag_id != 28 && $package_mirror->tag_id != 27) {
                // FILAS 2
                $table->addRow(340, array('exactHeight' => true));
                $table->addCell(1300)->addText(
                    htmlspecialchars($trad->textCancelation_tbl_line2_c1),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );
                $table->addCell(2800)->addText(
                    htmlspecialchars($trad->textCancelation_tbl_line2_c2),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );
                $table->addCell(2800)->addText(
                    htmlspecialchars($trad->textCancelation_tbl_line1_c3),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'),
                    'titleTable'
                );
            }


            $section->addTextBreak(1, 'space1');

            if ($package_mirror->tag_id != 28) {

                $section->addListItem(
                    htmlspecialchars($trad->textCancelation_list_1),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addListItem(
                    htmlspecialchars($trad->textCancelation_list_2),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addListItem(
                    htmlspecialchars($trad->textCancelation_list_3),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addListItem(
                    htmlspecialchars($trad->textCancelation_list_4),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addListItem(
                    htmlspecialchars($trad->textCancelation_list_5),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addListItem(
                    htmlspecialchars($trad->textCancelation_list_6),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );

                $section->addTextBreak(1, 'space1'); //Espacio

                $section->addText(
                    htmlspecialchars($trad->titleinfoImportant),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                    'title'
                );
                $section->addLine($linestyle);
                $section->addText(
                    htmlspecialchars($trad->textinfoImportant),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                    'textImportant'
                );
                //FIN INFORMACIÓN SOBRE LA ENTRADA A PERÚ
                $section->addTextBreak(1, 'space1'); //Espacio

                $section->addText(
                    htmlspecialchars($trad->titleGeneralImportant),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                    'title'
                );
                $section->addLine($linestyle);
                // TEXTO IMPORTANTE

                $section->addText(
                    htmlspecialchars($trad->textGeneralImportant),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                    'textImportant'
                );

                $textRun = $section->addTextRun(array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'));
                $textRun->addText(htmlspecialchars($trad->textGeneralImportant_2));
                $textRun->addLink($trad->linkGeneraldomation1, htmlspecialchars($trad->textGeneraldomation1), array('bold' => true, 'color' => '0000FF'));

                $section->addTextBreak(1, 'space1');

                // RECOMENDACIONES TRASLADOS..
                // $section = $phpWord->addSection();
                $section->addText(
                    htmlspecialchars($trad->transfers_title),
                    array(
                        'name' => 'Calibri',
                        'size' => 10,
                        'color' => '5a5a58',
                        'bold' => true,
                        'wrappingStyle' => 'infront'
                    ),
                    'title'
                );
                $section->addLine($linestyle);
                //PARRAFO 1
                $section->addText(
                    htmlspecialchars($trad->transfers_subtitle),
                    array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                    'textImportant'
                );
                $section->addListItem(
                    htmlspecialchars($trad->transfers_text),
                    0,
                    'StyleSquare',
                    $predefinedMultilevel,
                    'P-Styleguiado'
                );
                $section->addTextBreak(1, 'space1'); //Espacioo
                // FIN TRASLADOS..
            }
        }

        if ($flag_disclaimer_latam) {
            // 1. MASI - VIRTUAL ASSISTANT
            $section->addText(
                htmlspecialchars($trad->masi_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'title'
            );
            $section->addLine($linestyle);

            $section->addText(
                htmlspecialchars($trad->masi_parrafo_1),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'textImportant'
            );

            $section->addListItem(htmlspecialchars($trad->latam_masi_text_1), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
            $section->addListItem(htmlspecialchars($trad->latam_masi_text_2), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
            $section->addListItem(htmlspecialchars($trad->masi_texto_3), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
            $section->addListItem(htmlspecialchars($trad->latam_masi_text_4), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
            $section->addListItem(htmlspecialchars($trad->masi_texto_5), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
            $section->addListItem(htmlspecialchars($trad->masi_texto_6), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');

            $section->addTextBreak(1, 'space1');
            $section->addText('**********************************************', array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'), 'textImportant');
            $section->addTextBreak(1, 'space1');

            $section->addText(
                htmlspecialchars($trad->latam_contact_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'textImportant'
            );

            if ($ignore_contact) {
                $section->addText(htmlspecialchars($trad->latam_contact_usa_canada), array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'wrappingStyle' => 'infront'), 'textImportant');
                $section->addListItem(htmlspecialchars($trad->latam_contact_usa_canada_email), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
                $section->addListItem(htmlspecialchars($trad->latam_contact_usa_canada_phone), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
                $section->addText(htmlspecialchars($trad->latam_contact_europe_asia), array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'wrappingStyle' => 'infront'), 'textImportant');
                $section->addListItem(htmlspecialchars($trad->latam_contact_europe_asia_email), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
                $section->addListItem(htmlspecialchars($trad->latam_contact_europe_asia_phone), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
                $section->addText(htmlspecialchars($trad->latam_contact_latam_europe), array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'wrappingStyle' => 'infront'), 'textImportant');
                $section->addListItem(htmlspecialchars($trad->latam_contact_latam_europe_email), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
                $section->addListItem(htmlspecialchars($trad->latam_contact_latam_europe_phone), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
            } else {
                if ($market_id == 6) { // 1️⃣ Estados Unidos y Canadá
                    $section->addText(htmlspecialchars($trad->latam_contact_usa_canada), array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'wrappingStyle' => 'infront'), 'textImportant');
                    $section->addListItem(htmlspecialchars($trad->latam_contact_usa_canada_email), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
                    $section->addListItem(htmlspecialchars($trad->latam_contact_usa_canada_phone), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
                } elseif (in_array($market_id, [5, 7, 15])) { // 2️⃣ Europa y Asia Pacífico
                    $section->addText(htmlspecialchars($trad->latam_contact_europe_asia), array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'wrappingStyle' => 'infront'), 'textImportant');
                    $section->addListItem(htmlspecialchars($trad->latam_contact_europe_asia_email), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
                    $section->addListItem(htmlspecialchars($trad->latam_contact_europe_asia_phone), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
                } else { // 3️⃣ LATAM, Italia, España y Portugal (Otros mercados)
                    $section->addText(htmlspecialchars($trad->latam_contact_latam_europe), array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'wrappingStyle' => 'infront'), 'textImportant');
                    $section->addListItem(htmlspecialchars($trad->latam_contact_latam_europe_email), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
                    $section->addListItem(htmlspecialchars($trad->latam_contact_latam_europe_phone), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
                }
            }

            $section->addTextBreak(1, 'space1');

            // 2. TERMS AND CONDITIONS
            $section->addText(
                htmlspecialchars($trad->terminos_y_condiciones_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'title'
            );
            $section->addLine($linestyle);

            $textRun = $section->addTextRun(array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'));
            $textRun->addText(htmlspecialchars($trad->latam_terms_text_1 . $trad->latam_terms_link_text . $trad->latam_terms_text_2));
            $textRun->addLink($trad->latam_terms_url, htmlspecialchars($trad->latam_terms_link_here), array('color' => '0000FF', 'underline' => 'single', 'size' => 10, 'name' => 'Calibri'));
            $textRun->addText(htmlspecialchars($trad->latam_terms_text_3));

            $section->addTextBreak(1, 'space1');

            // 3. CONDITIONS OF CANCELLATION
            $section->addText(
                htmlspecialchars($trad->textCancelation_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'title'
            );
            $section->addLine($linestyle);

            $section->addText(
                htmlspecialchars($trad->textCancelation_text_1),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#5a5a58', 'bold' => false, 'wrappingStyle' => 'infront'),
                'textImportant'
            );

            // Table
            $table = $section->addTable('tarifas');
            $table->addRow(340, array('exactHeight' => true));
            $table->addCell(1300, $styleCell)->addText(htmlspecialchars($trad->textCancelation_tbl_title1), array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true), 'titleTable');
            $table->addCell(2800, $styleCell)->addText(htmlspecialchars($trad->textCancelation_tbl_title2), array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true), 'titleTable');
            $table->addCell(2800, $styleCell)->addText(htmlspecialchars($trad->textCancelation_tbl_title3), array('name' => 'Calibri', 'size' => 10, 'color' => 'ffffff', 'bold' => true), 'titleTable');

            // Row 1
            $table->addRow(340, array('exactHeight' => true));
            $table->addCell(1300)->addText(htmlspecialchars($trad->textCancelation_tbl_line1_c1), array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'), 'titleTable');
            $table->addCell(2800)->addText(htmlspecialchars($trad->textCancelation_tbl_line1_c2), array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'), 'titleTable');
            $table->addCell(2800)->addText(htmlspecialchars($trad->textCancelation_tbl_line1_c3), array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'), 'titleTable');

            // Row 2
            $table->addRow(340, array('exactHeight' => true));
            $table->addCell(1300)->addText(htmlspecialchars($trad->latam_cancellation_10_plus), array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'), 'titleTable');
            $table->addCell(2800)->addText(htmlspecialchars($trad->textCancelation_tbl_line2_c2), array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'), 'titleTable');
            $table->addCell(2800)->addText(htmlspecialchars($trad->textCancelation_tbl_line1_c3), array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58'), 'titleTable');

            $section->addTextBreak(1, 'space1');

            $section->addListItem(htmlspecialchars($trad->textCancelation_list_1), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
            $section->addListItem(htmlspecialchars($trad->textCancelation_list_2), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
            $section->addListItem(htmlspecialchars($trad->latam_cancellation_amazon_patagonia), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
            $section->addListItem(htmlspecialchars($trad->latam_cancellation_no_show), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
            $section->addListItem(htmlspecialchars($trad->textCancelation_list_6), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');

            $section->addTextBreak(1, 'space1');

            // 4. ENTRY SOUTH AMERICA
            $section->addText(
                htmlspecialchars($trad->latam_entry_south_america_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'title'
            );
            $section->addLine($linestyle);

            $section->addText(
                htmlspecialchars($trad->latam_entry_text),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'textImportant'
            );

            $section->addTextBreak(1, 'space1');

            // 5. GENERAL RECOMMENDATIONS
            $section->addText(
                htmlspecialchars($trad->general_recommendations_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'title'
            );
            $section->addLine($linestyle);

            $section->addText(
                htmlspecialchars($trad->latam_general_recommendations_text_1),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'textImportant'
            );
            $section->addText(
                htmlspecialchars($trad->latam_general_recommendations_text_2),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'textImportant'
            );

            $textRun = $section->addTextRun(array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'));
            $textRun->addText(htmlspecialchars($trad->latam_general_recommendations_text_3), array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'));
            $textRun->addText(htmlspecialchars($trad->latam_general_recommendations_text_4), array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront')); // Color azul, subrayado?
            $textRun->addText(htmlspecialchars($trad->latam_general_recommendations_text_5), array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'));

            $section->addTextBreak(1, 'space1');

            // 6. TRANSFERS RECOMMENDATIONS
            $section->addText(
                htmlspecialchars($trad->transfers_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
                'title'
            );
            $section->addLine($linestyle);

            $section->addText(
                htmlspecialchars($trad->latam_transfers_title),
                array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
                'textImportant'
            );
            $section->addListItem(htmlspecialchars($trad->latam_transfers_text_1), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');
            $section->addListItem(htmlspecialchars($trad->latam_transfers_text_2), 0, 'StyleSquare', $predefinedMultilevel, 'P-Styleguiado');

            $section->addTextBreak(1, 'space1');
        }

        // 7. DISCLAIMER
        $section->addText(
            htmlspecialchars($trad->disclaimer_title),
            array('name' => 'Calibri', 'size' => 10, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),
            'title'
        );
        $section->addLine($linestyle);
        $section->addText(
            htmlspecialchars($trad->disclaimer_text),
            array('name' => 'Calibri', 'size' => 10, 'color' => '#808080', 'wrappingStyle' => 'infront'),
            'textImportant'
        );

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');

        $name = preg_replace('/[^A-Za-z0-9\-\s]/', '', $package['descriptions']['name']);
        $name = str_replace("  ", "-", $name);

        $dir = public_path('generate-itinerary');

        // Crear la carpeta con permisos más amplios temporalmente
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0775, true); // 0775 en lugar de 0755
        }

        // Verificar que el directorio es escribible
        if (!is_writable($dir)) {
            chmod($dir, 0775);
        }

        if (!$movil) {
            \PhpOffice\PhpWord\Settings::setTempDir($dir);
            $fullPath = $dir . '/' . $name . '.docx';
            try {
                $objWriter->save($fullPath);
            } catch (\Exception $ex) {
                return $this->throwError($ex);
            }
            return $fullPath;
        } else {
            $name = Str::slug($name);

            $filename = $name . '-' . microtime(true) . '.docx';
            $fullPath = $dir . '/' . $filename;

            $objWriter->save($fullPath);
            return url('generate-itinerary/' . $filename);
        }
    }

    public function searchGalleryCloudinary($type, $objectId)
    {
        $allImages = Galery::where('object_id', $objectId)
            ->where('type', $type)
            ->where('slug', "{$type}_gallery")
            ->pluck('url')
            ->toArray(); // Convertir a array para consistencia

        return $allImages;
    }

    /*
     * Este metodo devuelve informacion de los servicios para el Google Tag Manager para los usuarios de tipo cliente
     * @param $package
     * @param $services
     * @return array
     */

    public function getServicesForGtm($package, $services)
    {
        $services_gtm = [];
        foreach ($services as $service) {
            if ($service['type'] == 'service') {
                $services_gtm[] = [
                    "item_id" => $service['service']["id"],
                    "item_sku" => $service['service']["aurora_code"],
                    "item_name" => strtoupper($service['service']["service_translations_gtm"][0]["name"]),
                    "price" => null,
                    "item_brand" => $service['service']['service_destination'][0]['state']['iso'],
                    "item_category" => 'service',
                    "item_category2" => 'package_product',
                    "item_list_id" => $package->code,
                    "item_list_name" => strtoupper($package->translations_gtm[0]['name']),

                ];
            }

            if ($service['type'] == 'hotel') {
                $services_gtm[] = [
                    "item_id" => $service['hotel']["id"],
                    "item_sku" => $service['hotel']['channel'][0]['code'],
                    "item_name" => strtoupper($service['hotel']["name"]),
                    "item_brand" => $service['hotel']['state']['iso'],
                    "item_category" => 'hotel',
                    "item_category2" => 'package_product',
                    "item_list_id" => $package->code,
                    "item_list_name" => strtoupper($package->translations_gtm[0]['name']),
                ];
            }
        }

        return $services_gtm;
    }

    protected function buildPackageSearchCacheKey(array $params, $clientId, $version = true, $userId = null)
    {
        $payload = [
            'version'        => $version,
            'client_id'        => $clientId,
            'user_id'          => $userId, // importante: cache por usuario
            'lang'             => $params['lang'] ?? 'en',
            'date'             => $params['date_from'] ?? null,
            'destinations'     => $params['destinations'] ?? [],
            'groups'           => $params['groups'] ?? [],
            'tags'             => $params['tags'] ?? [],
            'type_service'     => $params['type_service'] ?? 'all',
            'type_package'     => $params['type_package'] ?? [],
            'category'         => $params['type_class'] ?? 'all',
            'days'             => $params['days'] ?? 0,
            'adults'           => $params['adults'] ?? 0,
            'child'           => $params['child'] ?? 0,
            'child_with_bed'   => $params['child_with_bed'] ?? 0,
            'child_without_bed' => $params['child_without_bed'] ?? 0,
            'age_child' => $params['age_child'] ?? [],
            'rooms'            => [
                'sgl'        => $params['quantity_sgl'] ?? 0,
                'dbl'        => $params['quantity_dbl'] ?? 0,
                'tpl'        => $params['quantity_tpl'] ?? 0,
                'child_dbl'  => $params['quantity_child_dbl'] ?? 0,
                'child_tpl'  => $params['quantity_child_tpl'] ?? 0,
            ],
            'filter'           => $params['filter'] ?? '',
            'limit'           => $params['limit'] ?? 10,
            'only_recommended' => $params['only_recommended'] ?? false,
            'gtm' => $params['gtm'] ?? false,
            'package_ids' => $params['package_ids'] ?? [],
        ];

        return 'packages_search:' . md5(json_encode($payload));
    }

    private function sanitizeHtmlForPhpWord($html)
    {
        if (empty($html)) {
            return '';
        }

        // Remove null bytes and normalize newlines
        $html = str_replace(chr(0), '', $html);
        $html = str_replace(["\r\n", "\r"], "\n", $html);

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        libxml_use_internal_errors(true);

        // Handle UTF-8 correctly by converting to HTML entities
        // This is more robust than the XML declaration hack for HTML fragments
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        // Wrap content
        $wrappedHtml = '<!DOCTYPE html><html><body>' . $html . '</body></html>';

        $dom->loadHTML($wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);

        if (!$body) {
            $plainText = strip_tags($html);
            return '<p style="color:#808080;text-align:justify;">' . htmlspecialchars($plainText) . '</p>';
        }

        $innerHTML = '';
        foreach ($body->childNodes as $child) {
            // saveXML produces XHTML (self-closing tags), which PHPWord prefers
            $innerHTML .= $dom->saveXML($child);
        }

        return trim($innerHTML);
    }

    public function getPriceAmount($price, $client_commission_status, $user_type_id, $client_commission)
    {
        // Convertir el precio a float en caso de venir como string con coma o separadores
        $price = floatval(str_replace(',', '', $price));
        if (
            intval($client_commission_status) === 1 &&
            intval($user_type_id) === 4 &&
            floatval($client_commission) > 0
        ) {
            $commissionRate = floatval($client_commission) / 100;
            $priceWithCommission = $price * (1 + $commissionRate);
            return roundLito($priceWithCommission);
        }
        return $price;
    }
}
