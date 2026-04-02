<?php

namespace App\Http\Controllers;

use App\DateRangeHotel;
use App\Hotel;
use App\Language;
use App\PoliciesRates;
use App\RoleAdmin;
use App\Service;
use App\ServiceRatePlan;
use App\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use jeremykenedy\LaravelRoles\Models\Role;
use Maatwebsite\Excel\Facades\Excel;

class RequestReportAuroraController extends Controller
{
    public function listHotelRooms(Request $request)
    {
        $hotels = Hotel::with([
            'rooms' => function ($query) {
                $query->select(['id', 'hotel_id', 'state']);
                $query->with([
                    'roomNameTranslations' => function ($query) {
                        $query->select(['value', 'object_id', 'language_id'])
                            ->whereIn('language_id', [1, 2, 3]);
                    },
                    'roomDescriptionTranslations' => function ($query) {
                        $query->select(['value', 'object_id', 'language_id'])
                            ->whereIn('language_id', [1, 2, 3]);
                    }
                ]);
                $query->orderBy('order');
            }
        ])
            ->where('status', 1)
            ->get(['id', 'name'])->toArray();

        return Excel::download(
            new  \App\Exports\RequestAurora\RequestRoomsByHotelList($hotels),
            'hotels_rooms.xlsx'
        );
    }

    public function listHotelRatePlans(Request $request)
    {
        $hotels = Hotel::with([
            'rates_plans' => function ($query) {
                $query->select(['id', 'name', 'hotel_id', 'status']);
                $query->with([
                    'translations' => function ($query) {
                        $query->select(['value', 'object_id', 'language_id']);
                        $query->where('type', 'rates_plan');
                        $query->where('slug', 'commercial_name');
                        $query->whereIn('language_id', [1, 2, 3]);
                    }
                ]);
            }
        ])
            ->where('status', 1)
            ->get(['id', 'name'])->toArray();
        return Excel::download(
            new  \App\Exports\RequestAurora\RequestRatePlansByHotelList($hotels),
            'hotels_rate_plans.xlsx'
        );
    }

    public function listHotelRatePlanNotes(Request $request)
    {
        $hotels = Hotel::with([
            'rates_plans' => function ($query) {
                $query->select(['id', 'name', 'hotel_id', 'status']);
                $query->with([
                    'translations_notes' => function ($query) {
                        $query->select(['value', 'object_id', 'language_id']);
                        $query->where('type', 'rates_plan');
                        $query->where('slug', 'notes');
                        $query->whereIn('language_id', [1, 2, 3]);
                    }
                ]);
            }
        ])
            ->where('status', 1)
//            ->limit(265)
//            ->where('id',376)
            ->get(['id', 'name'])->toArray();
//        dd($hotels);
        return Excel::download(
            new  \App\Exports\RequestAurora\RequestRatePlanNotesByHotelList($hotels),
            'hotels_rate_plan_notes_' . date('d-m-Y') . '_' . time() . '.xlsx'
        );
    }

    public function servicesWithRateProtectionByYear($year)
    {
        $_services = collect();
        Service::whereHas('service_rate', function ($query) use ($year) {
            $query->where('status', 1);
            $query->whereHas('service_rate_plans', function ($query) use ($year) {
                $query->whereYear('date_from', $year);
                $query->where('status', 1);
            });
        })->with([
            'service_rate' => function ($query) use ($year) {
                $query->select('id', 'name', 'service_id', 'status');
                $query->where('status', 1);
                $query->with([
                    'service_rate_plans' => function ($query) use ($year) {
                        $query->select('id', 'service_rate_id', 'date_from', 'status', 'flag_migrate');
                        $query->whereYear('date_from', $year);
                        $query->where('status', 1);
                    }
                ]);
            }
        ])->where('status', 1)
            ->select(['id', 'name', 'aurora_code', 'equivalence_aurora'])
            ->chunk(100, function ($services) use ($_services) {
                foreach ($services as $service) {
                    $_rates = collect();
                    foreach ($service->service_rate as $service_rate) {
                        $with_protection = true;
                        if ($service_rate->service_rate_plans->count() > 0) {
                            foreach ($service_rate->service_rate_plans as $service_rate_plans) {
                                if ($service_rate_plans->flag_migrate == 1) {
                                    $with_protection = false;
                                    break;
                                }
                            }
                        }
                        if (!$with_protection) {
                            $_rates->add([
                                'id' => $service_rate->id,
                                'name' => $service_rate->name,
                                'protection' => $with_protection,
                            ]);
                        }
                    }
                    if ($_rates->count() > 0) {
                        $_services->add([
                            'service_id' => $service->id,
                            'name' => $service->name,
                            'aurora_code' => $service->aurora_code,
                            'equivalence_aurora' => $service->equivalence_aurora,
                            'protection' => $_rates->first()['protection'],
                            'rates' => $_rates,
                        ]);
                    }
                }
            });

        return Excel::download(
            new  \App\Exports\RequestAurora\RequestServicesWithRateProtection($_services->toArray()),
            'services_with_rate_protection.xlsx'
        );
    }

    public function hotelsWithRateProtectionByYear($year)
    {
        $hotels = Hotel::with([
            'rates_plans' => function ($query) {
                $query->select(['id', 'name', 'hotel_id', 'status']);
                $query->where('status', 1);
            }
        ])->where('status', 1)
            ->get(['id', 'name'])->toArray();
        $_hotels = collect();
        foreach ($hotels as $hotel) {
            $rates = collect();
            foreach ($hotel['rates_plans'] as $rates_plan) {
                $with_protection = false;
                $rate_plan_id = $rates_plan['id'];
                $date_range_ids_not_show = DateRangeHotel::select('old_id_date_range')
                    ->where('rate_plan_id', $rate_plan_id)
                    ->whereNotNull('old_id_date_range')
                    ->pluck('old_id_date_range')
                    ->toArray();
                $date_ranges = DateRangeHotel::where('rate_plan_id', $rate_plan_id)
                    ->where(function ($query) use ($year) {
                        $query->whereYear('date_from', $year);
                    });
                $date_ranges = $date_ranges->whereNotIn('date_range_hotels.id', $date_range_ids_not_show)
                    ->where('date_range_hotels.flag_migrate', 1)
                    ->orderBy('date_range_hotels.date_from')
                    ->orderBy('date_range_hotels.group')->get([
                        'date_range_hotels.id',
                        'date_range_hotels.date_from',
                        'date_range_hotels.date_to',
                        'date_range_hotels.price_adult',
                        'date_range_hotels.price_child',
                        'date_range_hotels.price_infant',
                        'date_range_hotels.price_extra',
                        'date_range_hotels.discount_for_national',
                        'date_range_hotels.rate_plan_id',
                        'date_range_hotels.hotel_id',
                        'date_range_hotels.room_id',
                        'date_range_hotels.rate_plan_room_id',
                        'date_range_hotels.meal_id',
                        'date_range_hotels.policy_id',
                        'date_range_hotels.old_id_date_range',
                        'date_range_hotels.group',
                        'date_range_hotels.updated',
                        'date_range_hotels.created_at',
                        'date_range_hotels.updated_at',
                        'date_range_hotels.flag_migrate'
                    ]);

                if ($date_ranges->count() > 0) {
                    $with_protection = true;
                }
                if ($with_protection) {
                    $rates->add([
                        'id' => $rates_plan['id'],
                        'name' => $rates_plan['name'],
                        'protection' => $with_protection
                    ]);
                }
            }


            if ($rates->count() > 0) {
                $_hotels->add([
                    'hotel_id' => $hotel['id'],
                    'name' => $hotel['name'],
                    'rates' => $rates
                ]);
            }


        }
        return Excel::download(
            new  \App\Exports\RequestAurora\RequestHotelsWithRateProtection($_hotels->toArray()),
            'hotels_with_rate_protection.xlsx'
        );
    }

    public function servicesExportWithOutImages()
    {
        $_servicesWithOutImages = collect();
        Service::with([
            'galleries' => function ($query) {
                $query->select(['object_id', 'url', 'state']);
                $query->where('type', 'service');
                $query->where('slug', 'service_gallery');
            }
        ])
            ->where('status', 1)
            ->select(['id', 'name', 'aurora_code', 'equivalence_aurora'])
            ->chunk(100, function ($services) use ($_servicesWithOutImages) {
                foreach ($services as $service) {
                    if ($service->galleries != null && $service->galleries->count() === 0) {
                        $_servicesWithOutImages->add($service);
                    }
                }
            });

        return Excel::download(
            new  \App\Exports\RequestAurora\RequestServicesWithOutImages($_servicesWithOutImages->toArray()),
            'services_with_out_images.xlsx'
        );
    }

    public function servicesExportTextByLang($lang)
    {

        $language = Language::where('iso', $lang)->first();
        $services = Service::with([
            'service_translations' => function ($query) use ($language) {
                $query->select(['service_id', 'name', 'description', 'itinerary']);
                $query->where('language_id', $language->id);
            }
        ])->where('status', 1)
            ->whereNotIn('id', [
                2158,
                2367,
                2183,
                2207,
                2187,
                2320,
                1293,
                2518,
                2298,
                1997,
                2308,
                2153,
                2569,
                2515,
                1798,
                2095,
                2329,
                2312,
                2151,
                3011,
                1492,
                2845,
                2303,
                1291,
                2319,
                3350,
                2117,
                1386,
                2254,
                1658,
                1324,
                2517,
                2205,
                2179,
                1868,
                2170,
                2182,
                1875,
                2256,
                2204,
                1729,
                3272,
                2128,
                2094,
                1299,
                2118,
                1910,
                2351,
                2354,
                1490,
                1745,
                1799,
                3295,
                1890,
                2220,
                1460,
                1459,
                2184,
                2359,
                1292,
                1443,
                2115,
                2311,
                2527,
                3217,
                1959,
                1974,
                2177,
                1793,
                2171,
                2358,
                1753,
                1866,
                2156,
                1785,
                1883,
                2271,
                1512,
                1854,
                2456,
                1728,
                2144,
                2846,
                2847,
                2185,
                2272,
                1498,
                2193,
                2366,
                2516,
                2221,
                1773,
                1810,
                1289,
                1656,
                2353,
                2309,
                1956,
                2103,
                1549,
                1772,
                1814,
                2008,
                2146,
                2172,
                2235,
                2321,
                1223,
                1342,
                1961,
                2224,
                2363,
                2260,
                2360,
                2595,
                2162,
                1491,
                2317,
                1999,
                2131,
                2180,
                2213,
                1986,
                2016,
                1663,
                1904,
                2062,
                2169,
                2395,
                1566,
                2154,
                1547,
                2178,
                2222,
                1960,
                2627,
                1876,
                1882,
                2161,
                2681,
                1359,
                2142,
                2570,
                2890,
                1812,
                2176,
                2809,
                1632,
                2194,
                2526,
                2186,
                2203,
                2362,
                2006,
                2007,
                2808,
                1984,
                2000,
                2149,
                2632,
                3109,
                1575,
                1809,
                2136,
                3221,
                1429,
                1318,
                1646,
                1874,
                2141,
                1438,
                1589,
                3301,
                1537,
                2597,
                1417,
                1590,
                1595,
                1696,
                1795,
                2327,
                3007,
                3294,
                1853,
                1932,
                1963,
                2325,
                2973,
                1207,
                1790,
                2127,
                1465,
                1523,
                1638,
                1929,
                2138,
                2148,
                1734,
                1976,
                1979,
                2001,
                2035,
                3273,

                1208,
                1238,
                1243,
                1246,
                1260,
                1264,
                1265,
                1266,
                1282,
                1297,
                1298,
                1327,
                1328,
                1339,
                1440,
                1444,
                1447,
                1458,
                1506,
                1507,
                1508,
                1509,
                1511,
                1518,
                1524,
                1535,
                1581,
                1590,
                1591,
                1592,
                1595,
                1647,
                1660,
                1676,
                1693,
                1696,
                1709,
                1753,
                1754,
                1773,
                1810,
                1866,
                1883,
                1891,
                1920,
                1929,
                1970,
                2254,
                2255,
                2256,
                2258,
                2259,
                2260,
                2283,
                2296,
                2298,
                2301,
                2317,
                2319,
                2322,
                2325,
                2326,
                2328,
                2358,
                2362,
                2363,
                2366,
                2367,
                2379,
                2383,
                2387,
                2388,
                2391,
                2392,
                2409,
                2410,
                2411,
                2545,
                2690,
                2759,
                2767,
                2768,
                3004,
                3005,
                3182,
                3281,
                3367,
                3386,
                3402,
                3403
            ])->get(['id', 'aurora_code']);

        return Excel::download(
            new  \App\Exports\RequestAurora\RequestServicesTextByLang($services->toArray(), strtoupper($lang)),
            'services_export_texts.xlsx'
        );

    }

    public function hotelsExportWithOutImages()
    {
        $hotels = Hotel::with('channels')
            ->where('status', '=', 1)
            ->whereDoesntHave('galeries', function ($query) {
                $query->where('type', 'hotel')
                    ->where('slug', 'hotel_gallery');
            })->get();

        return Excel::download(new  \App\Exports\RequestAurora\RequestHotelWithOutImages($hotels),
            'hoteles_sin_imagenes.xlsx');
    }

    public function hotelsRoomExportWithOutImages()
    {
        $roomsWithOutImages = collect();
        Hotel::with([
            'rooms' => function ($query) {
                $query->select(['id', 'hotel_id', 'state']);
                $query->with([
                    'translations' => function ($query) {
                        $query->select(['value', 'object_id']);
                        $query->where('type', 'room');
                        $query->where('slug', 'room_name');
                        $query->where('language_id', 1);
                    }
                ]);
                $query->orderBy('order');
                $query->with([
                    'galeries' => function ($query) {
                        $query->where('slug', 'room_gallery');
                        $query->where('type', 'room');
                    }
                ]);
            }
        ])
            ->where('status', 1)
            ->chunk(100, function ($hotels) use ($roomsWithOutImages) {
                foreach ($hotels as $hotel) {
                    $rooms = collect();
                    foreach ($hotel->rooms as $room) {
                        if ($room->galeries->count() == 0) {
                            $rooms->add([
                                'id' => $room['id'],
                                'galeries_count' => $room->galeries->count(),
                                'name' => $room['translations'][0]['value'],
                            ]);
                        }
                    }

                    if ($rooms->count() > 0) {
                        $roomsWithOutImages->add([
                            'id' => $hotel['id'],
                            'name' => $hotel['name'],
                            'rooms' => $rooms
                        ]);
                    }

                }
            });
        return Excel::download(new  \App\Exports\RequestAurora\RequestHotelRoomsWithOutImages($roomsWithOutImages),
            'habitaciones_sin_imagenes.xlsx');

    }

    public function permissionsAndRolesExport()
    {
        $roles = RoleAdmin::all(['id', 'slug', 'name', 'description'])->toArray();
        $rolesAndPermission = collect();
        foreach ($roles as $role) {
            $permissions = collect();
            $permissions_ids = DB::table('permission_role')
                ->where('role_id', $role['id'])
                ->pluck('permission_id');
            $permissionsNames = DB::table('permissions')
                ->whereIn('id', $permissions_ids)
                ->get()->toArray();
            foreach ($permissionsNames as $permission) {
                $permissions->add([
                    'name' => explode(':', $permission->name)[0],
                    'action' => $permission->name,
                ]);
            }

            $permissions = $permissions->groupBy('name');

            $rolesAndPermission->add([
                'role' => $role['name'],
                'permissions' => $permissions
            ]);
        }

        return Excel::download(new  \App\Exports\RequestAurora\RequestPermissionsAndRoles($rolesAndPermission),
            'roles_y_sus_permisos.xlsx');

    }

    public function updateFlags(Request $request)
    {
        $codes = $request->input('codes');
        $year  = $request->input('year');

        if (!is_array($codes) || empty($codes)) {
            return response()->json([
                'message' => '"codes" debe ser un array con valores.'
            ], 422);
        }

        if (!$year || !is_numeric($year)) {
            return response()->json([
                'message' => '"year" debe ser un número válido.'
            ], 422);
        }

        $updated = DB::table('service_rate_plans')
            ->whereIn('service_rate_id', function ($q) use ($codes) {
                $q->select('id')
                  ->from('service_rates')
                  ->whereNull('deleted_at')
                  ->whereIn('service_id', function ($q2) use ($codes) {
                      $q2->select('id')
                         ->from('services')
                         ->whereIn('aurora_code', $codes)
                         ->whereNull('deleted_at');
                  });
            })
            ->whereYear('date_from', $year)
            ->whereYear('date_to', $year)
            ->whereNull('deleted_at')
            ->update(['flag_migrate' => 1]);

        return response()->json([
            'message' => 'Actualización completada',
            'updated_rows' => $updated,
            'year' => $year,
            'codes' => $codes
        ]);
    }

}
