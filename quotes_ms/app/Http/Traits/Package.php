<?php

namespace App\Http\Traits;

use App\Models\PackageDynamicRate;
use App\Models\PackageService;
use App\Models\PackageServiceRate;
use App\Models\PackageServiceRoom;
use App\Models\ServiceRatePlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait Package
{
    public function calculatePricePackage($category_id)
    {
        $services_dates = PackageService::where('package_plan_rate_category_id', $category_id)->where(
            'type',
            'service'
        )->orderBy('date_in', 'asc')->get();
        $data_services = [];
        $data_hotels = [];
        foreach ($services_dates as $service_date) {
            $services_rates = PackageServiceRate::where('package_service_id', $service_date["id"])->get();
            foreach ($services_rates as $service_rate) {
                $service_rate_plans = ServiceRatePlan::where('service_rate_id', $service_rate["service_rate_id"])
                    ->where('date_from', '<=', $service_date["date_in"])
                    ->where('date_to', '>=', $service_date["date_in"])
                    ->get();
                $data_services [] = $this->getDynamicRateByService($service_rate_plans, $category_id);
            }
        }

        $hotels_dates = PackageService::where('package_plan_rate_category_id', $category_id)->where('type', 'hotel')
            ->orderBy('date_in', 'asc')->orderBy('order', 'asc')->get()->groupBy('date_in');

        foreach ($hotels_dates as $date) {
            $simple_hotels = 0;
            $double_hotels = 0;
            $triple_hotels = 0;
            $package_service_id = $date[0]["id"];
            $date_in = $date[0]["date_in"];
            $date_out = Carbon::parse($date[0]["date_out"])->subDay(1)->format('Y-m-d');

            $rooms = PackageServiceRoom::where('package_service_id', $package_service_id)->with([
                'rate_plan_room' => function ($query) use ($date_in, $date_out) {
                    $query->with([
                        'calendarys' => function ($query) use ($date_in, $date_out) {
                            $query->where('date', '>=', $date_in);
                            $query->where('date', '<=', $date_out);
                            $query->with('rate');
                        }
                    ]);
                    $query->with('room.room_type');
                }
            ])->get();

            //Buscar Simple
            foreach ($rooms as $room) {
                if ($room["rate_plan_room"]["room"]["room_type"]["occupation"] == 1) {
                    foreach ($room["rate_plan_room"]["calendarys"] as $calendar) {
                        $simple_hotels += $calendar["rate"][0]["price_adult"];
                    }
                    break;
                }
            }
            //Buscar Doble
            foreach ($rooms as $room) {
                if ($room["rate_plan_room"]["room"]["room_type"]["occupation"] == 2) {
                    foreach ($room["rate_plan_room"]["calendarys"] as $calendar) {
                        $double_hotels += $calendar["rate"][0]["price_adult"];
                    }
                    break;
                }
            }
            //Buscar Triple
            $exists_rate_triple = false;
            foreach ($rooms as $room) {
                if ($room["rate_plan_room"]["room"]["room_type"]["occupation"] == 3) {
                    $exists_rate_triple = true;
                    foreach ($room["rate_plan_room"]["calendarys"] as $calendar) {
                        $price_extra = 0;
                        if ($calendar["rate"][0]["price_extra"] > 0) {
                            $price_extra = $calendar["rate"][0]["price_extra"];
                        }
                        $triple_hotels += $calendar["rate"][0]["price_adult"] + $price_extra;
                    }
                    break;
                }
            }
            if (!$exists_rate_triple) {
                $rate_triple = 0;
                foreach ($rooms as $room) {
                    if ($room["rate_plan_room"]["room"]["room_type"]["occupation"] == 1) {
                        foreach ($room["rate_plan_room"]["calendarys"] as $calendar) {
                            $rate_triple += $calendar["rate"][0]["price_adult"];
                        }
                        break;
                    }
                }
                foreach ($rooms as $room) {
                    if ($room["rate_plan_room"]["room"]["room_type"]["occupation"] == 2) {
                        foreach ($room["rate_plan_room"]["calendarys"] as $calendar) {
                            $rate_triple += $calendar["rate"][0]["price_adult"];
                        }
                        break;
                    }
                }
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

        foreach ($data_rate_services as $key_rate => $rate_service) {
            foreach ($rate_service as $key => $item) {
                if (isset($item['id'])) {
                    if ($key_rate == 0) {
                        $rate_dynamic[$key] = [
                            'id'     => $item['id'],
                            'simple' => $item['simple'],
                            'double' => $item['double'],
                            'triple' => $item['triple'],
                        ];
                    } else {
                        $rate_dynamic[$key] = [
                            'id'     => $item['id'],
                            'simple' => $rate_dynamic[$key]['simple'] + $item['simple'],
                            'double' => $rate_dynamic[$key]['double'] + $item['double'],
                            'triple' => $rate_dynamic[$key]['triple'] + $item['triple'],
                        ];
                    }
                }
            }
        }

        foreach ($data_rate_hotels as $key_rate => $rate_hotel) {
            foreach ($rate_hotel as $key => $item) {
                if (isset($rate_dynamic[$key])) {
                    $rate_dynamic[$key] = [
                        'id'     => $item['id'],
                        'simple' => $rate_dynamic[$key]['simple'] + $item['simple'],
                        'double' => $rate_dynamic[$key]['double'] + $item['double'],
                        'triple' => $rate_dynamic[$key]['triple'] + $item['triple'],
                    ];
                } else {
                    $rate_dynamic[$key] = [
                        'id'     => $item['id'],
                        'simple' => $item['simple'],
                        'double' => $item['double'],
                        'triple' => $item['triple'],
                    ];
                }

            }
        }

        foreach ($rate_dynamic as $key_rate => $item) {
            $packageDynamicRate = PackageDynamicRate::find($item['id']);
            if ($packageDynamicRate) {
                $packageDynamicRate->simple = $item['simple'];
                $packageDynamicRate->double = $item['double'];
                $packageDynamicRate->triple = $item['triple'];
                $packageDynamicRate->save();
            }
        }

    }

    public function getDynamicRateByService($service_rate_plans, $category_id)
    {
        $package_rates_ranges = PackageDynamicRate::where('package_plan_rate_category_id', $category_id)->get();
        $data_insert = [];
        foreach ($package_rates_ranges as $key => $rate_range) {
            $package_rates_ranges[$key]['simple'] = 0;
            $package_rates_ranges[$key]['double'] = 0;
            $package_rates_ranges[$key]['triple'] = 0;
            $data_insert[$rate_range['id']] = [
                "id"     => $rate_range['id'],
                "simple" => 0,
                "double" => 0,
                "triple" => 0,
            ];
            if ($rate_range["pax_from"] == 1 && $rate_range["pax_to"] == 1) {
                foreach ($service_rate_plans as $rate_plan) {
                    if (($rate_plan["pax_from"] == 1 && $rate_plan["pax_to"] == 1) or ($rate_plan["pax_from"] <= 1 && $rate_plan["pax_to"] >= 1)) {
                        $rate_range["simple"] += $rate_plan["price_adult"];
                        $data_insert[$rate_range['id']] = [
                            'id'     => $rate_range["id"],
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
                            'id'     => $rate_range["id"],
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
                            'id'     => $rate_range["id"],
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
                        'id'     => $rate_range["id"],
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
            foreach ($package_rates_ranges as $rate_range) {
                if ($rate_range->pax_from == 1 && $rate_range->pax_to == 1) {
                    foreach ($service_rate_plans as $rate_plan) {
                        if ($rate_plan->pax_from <= 1 && $rate_plan->pax_to >= 1) {
                            if ($calculation_included == 1) {
                                $rate_range->simple += $rate_plan->price_adult;
                            } else {
                                $rate_range->simple += ($rate_plan->price_adult + ($rate_plan->price_adult * ($markup / 100)));
                            }
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
                            DB::table('package_dynamic_rate_copies')->where('id', $rate_range->id)
                                ->update([
                                    'simple' => $rate_range->double,
                                    'double' => $rate_range->double
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
                            DB::table('package_dynamic_rate_copies')->where('id', $rate_range->id)
                                ->update([
                                    'simple' => $rate_range->triple,
                                    'double' => $rate_range->triple,
                                    'triple' => $rate_range->triple
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

        });
    }

    public function getDynamicRateByHotel($simple, $double, $triple, $category_id)
    {
        $package_rates_ranges = PackageDynamicRate::where(
            'package_plan_rate_category_id',
            $category_id
        )->whereNull('deleted_at')->get();
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
                    'id'     => $rate_range["id"],
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
                    'id'     => $rate_range["id"],
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
                    'id'     => $rate_range["id"],
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

    public function resetDynamicRateCopy($category_id)
    {
        DB::transaction(function () use ($category_id) {
            $package_dynamic_rates = DB::table('package_dynamic_rates')
                ->where('package_plan_rate_category_id', $category_id)
                ->whereNull('deleted_at')
                ->get();

            foreach ($package_dynamic_rates as $package_dynamic_rate) {
                DB::table('package_dynamic_rate_copies')->insert([
                    'service_type_id'               => $package_dynamic_rate->service_type_id,
                    'package_plan_rate_category_id' => $package_dynamic_rate->package_plan_rate_category_id,
                    'pax_from'                      => $package_dynamic_rate->pax_from,
                    'pax_to'                        => $package_dynamic_rate->pax_to,
                    'simple'                        => $package_dynamic_rate->simple,
                    'double'                        => $package_dynamic_rate->double,
                    'triple'                        => $package_dynamic_rate->triple,
                    'created_at'                    => date("Y-m-d H:i:s")
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
                    $new_date_out_ = Carbon::parse($new_date_in_)->addDays($nights)->format('Y-m-d');
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

}
