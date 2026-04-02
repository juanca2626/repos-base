<?php

namespace App\Http\Controllers;

use Excel;
use App\Client;
use Carbon\Carbon;
use App\PackageService;
use App\PackagePlanRate;
use App\PackageDynamicRate;
use App\PackageServiceRate;
use App\PackageServiceRoom;
use Illuminate\Http\Request;
use App\PackageFixedSaleRate;
use App\PackageRateSaleMarkup;
use App\PackageDynamicSaleRate;
use App\PackageServiceOptional;
use App\PackagePlanRateCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\PackageServiceOptionalRoom;
use App\PackageServiceRoomHyperguest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Exports\PackageRatesExportMultiple;


class PackagePlanRatesController extends Controller
{
    /**
     * @param  Request  $request
     * @param $plan_rate_id
     * @return JsonResponse
     */

    /**
     * Display a listing of the resource.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index($package_id, Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');

        $packagePlanRate = PackagePlanRate::with([
            'plan_rate_categories.category.translations' => function ($query) use ($lang) {
                $query->where('type', 'typeclass');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            },
            'service_type.translations'
        ])->where('package_id', $package_id);

        $count = $packagePlanRate->count();
        if ($querySearch) {
            $packagePlanRate->where(function ($query) use ($querySearch) {
                $query->orWhere('name', 'like', '%'.$querySearch.'%');
            });
        }

        if ($paging === 1) {
            $packagePlanRate = $packagePlanRate->take($limit)->get();
        } else {
            $packagePlanRate = $packagePlanRate->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $packagePlanRate,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);


    }

    public function show(Request $request, $plan_rate_id)
    {
        $lang = $request->input("lang");

        $packagePlanRate = PackagePlanRate::with([
            'service_type',
            'plan_rate_categories.category.translations' => function ($query) use ($lang) {
                $query->where('type', 'typeclass');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('id', $plan_rate_id)->first();

        return Response::json(['success' => true, 'data' => $packagePlanRate]);
    }

    public function copy(Request $request, $package_id)
    {
        $plan_rate_id = $request->__get('plan_rate_id'); $lang = $request->__get('lang');

        $categories = PackagePlanRateCategory::with([
            'services.service', 'plan_rate', 'optionals.service_rooms',
            'services.service_rooms', 'services.service_rates',
            'rates', 'sale_rates.rate_sale_markups', 'sale_rates_fixed',
            'category' => function ($query) use ($lang) {
                $query->with([
                    'translations' => function ($q) use ($lang) {
                        $q->where('type', 'typeclass');
                        $q->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    }
                ]);

            }
        ])
        ->whereHas('services')
        ->where('package_plan_rate_id', $plan_rate_id)
        ->get()->toArray();

        // Función para duplicar..
        $markup_ignore = [];

        if(count($categories) > 0)
        {
            $_plan_rate = $categories[0]['plan_rate'];

            $plan_rate = new PackagePlanRate;
            $plan_rate->code = $_plan_rate['code'];
            $plan_rate->date_from = $_plan_rate['date_from'];
            $plan_rate->date_to = $_plan_rate['date_to'];
            $plan_rate->code = $_plan_rate['code'];
            $plan_rate->name = $_plan_rate['name'] . ' - (COPIA)';
            $plan_rate->package_id = $_plan_rate['package_id'];
            $plan_rate->service_type_id = $_plan_rate['service_type_id'];
            $plan_rate->status = 0;
            $plan_rate->save();

            foreach($categories as $key => $_category)
            {
                $category = new PackagePlanRateCategory;
                $category->package_plan_rate_id = $plan_rate->id;
                $category->type_class_id = $_category['type_class_id'];
                $category->save();

                foreach($_category['rates'] as $k => $_rate)
                {
                    $rate = new PackageDynamicRate;
                    $rate->service_type_id = $_rate['service_type_id'];
                    $rate->package_plan_rate_category_id = $category->id;
                    $rate->pax_from = $_rate['pax_from'];
                    $rate->pax_to = $_rate['pax_to'];
                    $rate->simple = $_rate['simple'];
                    $rate->double = $_rate['double'];
                    $rate->triple = $_rate['triple'];
                    $rate->save();
                }

                foreach($_category['sale_rates'] as $k => $_sale_rate)
                {
                    $_sale_markup = $_sale_rate['rate_sale_markups'];

                    if(!isset($markup_ignore[$_sale_markup['id']]))
                    {
                        $sale_markup = new PackageRateSaleMarkup;
                        $sale_markup->seller_type = $_sale_markup['seller_type'];
                        $sale_markup->seller_id = $_sale_markup['seller_id'];
                        $sale_markup->markup = $_sale_markup['markup'];
                        $sale_markup->status = $_sale_markup['status'];
                        $sale_markup->package_plan_rate_id = $plan_rate->id;
                        $sale_markup->save();

                        $markup_ignore[$_sale_markup['id']] = $sale_markup->id;
                        $sale_markup_id = $sale_markup->id;
                    }
                    else
                    {
                        $sale_markup_id = $markup_ignore[$_sale_markup['id']];
                    }

                    $sale_rate = new PackageDynamicSaleRate;
                    $sale_rate->service_type_id = $_sale_rate['service_type_id'];
                    $sale_rate->package_plan_rate_category_id = $category->id;
                    $sale_rate->pax_from = $_sale_rate['pax_from'];
                    $sale_rate->pax_to = $_sale_rate['pax_to'];
                    $sale_rate->simple = $_sale_rate['simple'];
                    $sale_rate->double = $_sale_rate['double'];
                    $sale_rate->triple = $_sale_rate['triple'];
                    $sale_rate->child_with_bed = $_sale_rate['child_with_bed'];
                    $sale_rate->child_without_bed = $_sale_rate['child_without_bed'];
                    $sale_rate->status = $_sale_rate['status'];
                    $sale_rate->package_rate_sale_markup_id = $sale_markup_id;
                    $sale_rate->save();
                }

                foreach($_category['sale_rates_fixed'] as $k => $_sale_rate_fixed)
                {
                    $sale_rate_fixed = new PackageFixedSaleRate;
                    $sale_rate_fixed->package_plan_rate_category_id = $category->id;
                    $sale_rate_fixed->simple = $_sale_rate_fixed['simple'];
                    $sale_rate_fixed->double = $_sale_rate_fixed['double'];
                    $sale_rate_fixed->triple = $_sale_rate_fixed['triple'];
                    $sale_rate_fixed->child_with_bed = $_sale_rate_fixed['child_with_bed'];
                    $sale_rate_fixed->child_without_bed = $_sale_rate_fixed['child_without_bed'];
                    $sale_rate_fixed->save();
                }

                foreach($_category['services'] as $k => $_service)
                {
                    $service = new PackageService;
                    $service->adult = $_service['adult'];
                    $service->calculation_included = $_service['calculation_included'];
                    $service->child = $_service['child'];
                    $service->code_flight = $_service['code_flight'];
                    $service->date_in = $_service['date_in'];
                    $service->date_out = $_service['date_out'];
                    $service->destiny = $_service['destiny'];
                    $service->double = $_service['double'];
                    $service->infant = $_service['infant'];
                    $service->object_id = $_service['object_id'];
                    $service->order = $_service['order'];
                    $service->origin = $_service['origin'];
                    $service->re_entry = $_service['re_entry'];
                    $service->single = $_service['single'];
                    $service->triple = $_service['triple'];
                    $service->type = $_service['type'];
                    $service->package_plan_rate_category_id = $category->id;
                    $service->save();

                    foreach($_service['service_rates'] as $sr => $_service_rate)
                    {
                        $service_rate = new PackageServiceRate;
                        $service_rate->package_service_id = $service->id;
                        $service_rate->service_rate_id = $_service_rate['service_rate_id'];
                        $service_rate->save();
                    }

                    foreach($_service['service_rooms'] as $sr => $_service_rate)
                    {
                        $service_rate = new PackageServiceRoom;
                        $service_rate->package_service_id = $service->id;
                        $service_rate->rate_plan_room_id = $_service_rate['rate_plan_room_id'];
                        $service_rate->save();
                    }
                }

                foreach($_category['optionals'] as $k => $_optional)
                {
                    $service = new PackageServiceOptional;
                    $service->adult = $_service['adult'];
                    $service->calculation_included = $_service['calculation_included'];
                    $service->child = $_service['child'];
                    $service->code_flight = $_service['code_flight'];
                    $service->date_in = $_service['date_in'];
                    $service->date_out = $_service['date_out'];
                    $service->destiny = $_service['destiny'];
                    $service->double = $_service['double'];
                    $service->infant = $_service['infant'];
                    $service->object_id = $_service['object_id'];
                    $service->order = $_service['order'];
                    $service->origin = $_service['origin'];
                    $service->re_entry = $_service['re_entry'];
                    $service->single = $_service['single'];
                    $service->triple = $_service['triple'];
                    $service->type = $_service['type'];
                    $service->package_plan_rate_category_id = $category->id;
                    $service->save();

                    foreach($_service['service_rooms'] as $sr => $_service_rate)
                    {
                        $service_rate = new PackageServiceOptionalRoom;
                        $service_rate->package_service_id = $service->id;
                        $service_rate->rate_plan_room_id = $_service_rate['rate_plan_room_id'];
                        $service_rate->save();
                    }
                }
            }
        }

        return response()->json([
            'type' => 'success',
            'categories' => $categories,
            'message' => 'Cotización duplicada correctamente',
        ]);
    }

    public function searchForExport(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('queryCustom');
        $lang = $request->input('lang');

        $packagePlanRate = PackagePlanRate::with([
            'plan_rate_categories.category.translations' => function ($query) use ($lang) {
                $query->where('type', 'typeclass');
                $query->where('language_id', 1);
            },
            'service_type.translations',
            'package.translations'
        ])->withCount('package_rate_sale_markup');

        if ($querySearch) {
            $packagePlanRate->where(function ($query) use ($querySearch) {
                $query->where('name', 'like', '%'.$querySearch.'%');
                $query->orWhereHas('package', function ($query2) use ($querySearch) {
                    $query2->where('code', 'like', '%'.$querySearch.'%');
                });
            });
        }
        $packagePlanRate = $packagePlanRate->having('package_rate_sale_markup_count', '>', 0);
        $count = $packagePlanRate->get()->count();

        if ($paging === 1) {
            $packagePlanRate = $packagePlanRate->take($limit)->get();
        } else {
            $packagePlanRate = $packagePlanRate->skip($limit * ($paging - 1))->take($limit)->get();
        }


        $data = [
            'data' => $packagePlanRate,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);

    }

    public function copyCategories(Request $request)
    {
        $plan_rate_category_id_from = $request->input("plan_rate_category_id_from");
        $plan_rate_category_id_to = $request->input("plan_rate_category_id_to");

        $services = PackageService::where('package_plan_rate_category_id', $plan_rate_category_id_from);

        if ($services->count() == 0) {
            return Response::json(['success' => false, 'type' => '0']);
        } else {

            $services = $services->get();

            $services_ids = PackageService::where('package_plan_rate_category_id',
                $plan_rate_category_id_to)->pluck('id');
            $created_at = date("Y-m-d H:i:s");


            DB::transaction(function () use ($services, $plan_rate_category_id_to, $services_ids, $created_at) {

                PackageServiceRoom::whereIn('package_service_id', $services_ids)->each(function ($room) {
                    $room->delete();
                });

                PackageServiceRoomHyperguest::whereIn('package_service_id', $services_ids)->each(function ($room) {
                    $room->delete();
                });

                PackageServiceRate::whereIn('package_service_id', $services_ids)->each(function ($rate) {
                    $rate->delete();
                });

                PackageService::where('package_plan_rate_category_id', $plan_rate_category_id_to)->each(function (
                    $service
                ) {
                    $service->delete();
                });

                foreach ($services as $service) {

                    $new_package_service = new PackageService();
                    $new_package_service->type = $service["type"];
                    $new_package_service->package_plan_rate_category_id = $plan_rate_category_id_to;
                    $new_package_service->object_id = $service["object_id"];
                    $new_package_service->order = $service["order"];
                    $new_package_service->date_in = $service["date_in"];
                    $new_package_service->date_out = $service["date_out"];
                    $new_package_service->adult = $service["adult"];
                    $new_package_service->child = $service["child"];
                    $new_package_service->infant = $service["infant"];
                    $new_package_service->single = $service["single"];
                    $new_package_service->double = $service["double"];
                    $new_package_service->triple = $service["triple"];
                    $new_package_service->re_entry = $service["re_entry"];
                    $new_package_service->save();

                    // RATES
                    $service_rates_for_copy = DB::table('package_service_rates')
                        ->where('package_service_id', $service['id'])
                        ->whereNull('deleted_at')
                        ->get();

                    foreach ($service_rates_for_copy as $s_copy) {
                        $new_package_service_rates = new PackageServiceRate();
                        $new_package_service_rates->package_service_id = $new_package_service->id;
                        $new_package_service_rates->service_rate_id = $s_copy->service_rate_id;
                        $new_package_service_rates->save();
                    }
                    // ROOMS
                    $service_rooms_for_copy = DB::table('package_service_rooms')
                        ->whereNull('deleted_at')
                        ->where('package_service_id', $service['id'])
                        ->get();

                    foreach ($service_rooms_for_copy as $r_copy) {
                        $new_package_service_rooms = new PackageServiceRoom();
                        $new_package_service_rooms->package_service_id = $new_package_service->id;
                        $new_package_service_rooms->rate_plan_room_id =  $r_copy->rate_plan_room_id;
                        $new_package_service_rooms->save();
                    }

                    // ROOMS HYPERGUEST
                    $service_rooms_for_copy_hyperguest = DB::table('package_service_rooms_hyperguest')
                        ->whereNull('deleted_at')
                        ->where('package_service_id', $service['id'])
                        ->get();

                    foreach ($service_rooms_for_copy_hyperguest as $r_copy){
                        $new_package_service_room_hyperguest = new PackageServiceRoomHyperguest();
                        $new_package_service_room_hyperguest->package_service_id = $new_package_service->id;
                        $new_package_service_room_hyperguest->rate_plan_id = $r_copy->rate_plan_id;
                        $new_package_service_room_hyperguest->room_id = $r_copy->room_id;
                        $new_package_service_room_hyperguest->num_adult = $r_copy->num_adult;
                        $new_package_service_room_hyperguest->num_infant = $r_copy->num_infant;
                        $new_package_service_room_hyperguest->price_adult = $r_copy->price_adult;
                        $new_package_service_room_hyperguest->price_child = $r_copy->price_child;
                        $new_package_service_room_hyperguest->price_infant = $r_copy->price_infant;
                        $new_package_service_room_hyperguest->price_extra = $r_copy->price_extra;
                        $new_package_service_room_hyperguest->price_amount_base = $r_copy->price_amount_base;
                        $new_package_service_room_hyperguest->price_amount_total = $r_copy->price_amount_total;
                        $new_package_service_room_hyperguest->save();
                    }

                }
            });

            return Response::json(['success' => true]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'package_id' => 'required',
                'name' => 'required',
                'date_from' => 'required',
                'date_to' => 'required'
            ]);
            if ($validator->fails()) {
                return Response::json(['success' => false]);
            } else {
                DB::beginTransaction();
                $date_from = Carbon::createFromFormat('d/m/Y', $request->input('date_from'));
                $date_to = Carbon::createFromFormat('d/m/Y', $request->input('date_to'));
                $packagePlan = new PackagePlanRate();
                $packagePlan->package_id = $request->input('package_id');
                $packagePlan->name = $request->input('name');
                $packagePlan->date_from = $date_from->format('Y-m-d');
                $packagePlan->date_to = $date_to->format('Y-m-d');
                $packagePlan->service_type_id = $request->input('service_type_id');
                $packagePlan->save();
                $categories = [];
                foreach ($request->input("categories") as $clave => $valor) {
                    $new_plan_category = new PackagePlanRateCategory();
                    $new_plan_category->package_plan_rate_id = $packagePlan->id;
                    $new_plan_category->type_class_id = (integer)$valor['type_class_id'];
                    $new_plan_category->save();
                }
//                PackagePlanRateCategory::insert($categories);
            }
            DB::commit();

            $package_plan_rate_category_first = PackagePlanRateCategory::where('package_plan_rate_id',
                $packagePlan->id)->first();

            return Response::json([
                'success' => true,
                'package_plan_rate_id' => $packagePlan->id,
                'package_plan_rate_category_id' => $package_plan_rate_category_first->id
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  PackagePlanRate  $packagePlanRate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'package_id' => 'required',
                'name' => 'required',
                'date_from' => 'required',
                'date_to' => 'required'
            ]);
            if ($validator->fails()) {
                return Response::json(['success' => false]);
            } else {
                $date_from = Carbon::createFromFormat('d/m/Y', $request->input('date_from'));
                $date_to = Carbon::createFromFormat('d/m/Y', $request->input('date_to'));
                DB::beginTransaction();
                $packagePlan = PackagePlanRate::find($id);
                $packagePlan->name = $request->input('name');
                $packagePlan->date_from = $date_from->format('Y-m-d');
                $packagePlan->date_to = $date_to->format('Y-m-d');
                $packagePlan->service_type_id = $request->input('service_type_id');
                $packagePlan->save();
                foreach ($request->input("categories") as $clave => $valor) {
                    if ($valor['id'] === null) {
                        $new_plan_category = new PackagePlanRateCategory();
                        $new_plan_category->package_plan_rate_id = $packagePlan->id;
                        $new_plan_category->type_class_id = (integer)$valor['type_class_id'];
                        $new_plan_category->save();
                    }
                }
                DB::commit();
                $package_plan_rate_category_first = PackagePlanRateCategory::where('package_plan_rate_id',
                    $packagePlan->id)->first();
                return Response::json([
                    'success' => true,
                    'package_plan_rate_id' => $packagePlan->id,
                    'package_plan_rate_category_id' => $package_plan_rate_category_first->id
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateStatus($id, Request $request)
    {
        $service = PackagePlanRate::find($id);
        if ($request->input("status")) {
            $service->status = 0;
        } else {
            $service->status = 1;
        }
        $service->save();
        return Response::json(['success' => true]);
    }

    /**
     * @param $plan_rate_category_id
     * @return JsonResponse
     */
    public function destroyCategory($plan_rate_category_id)
    {
        try {
            $service = PackagePlanRateCategory::find($plan_rate_category_id);
            $service->delete();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e]);
        }
    }

    public function searchRates(Request $request)
    {

        $package_plan_rate_id = $request->input('package_rate_plan_id');

        $category_ids = PackagePlanRateCategory::where('package_plan_rate_id', $package_plan_rate_id)->pluck('id');

        $packageRates = PackageDynamicRate::whereIn('package_plan_rate_category_id', $category_ids)->get();

        $data = [
            'data' => $packageRates,
            'success' => true
        ];

        return Response::json($data);


    }

    public function dataExcel($plan_rate_id, $service_type_id, Request $request)
    {
        $year = $request->input('year');
        $lang = $request->input('lang');
        $title = $request->input('title');
        $client_id = $request->input('client_id');

        $_categories = PackagePlanRateCategory::where('package_plan_rate_id', $plan_rate_id)
            ->with([
                'category.translations' => function ($query) use ($lang) {
                    $query->where('type', 'typeclass');
                    $query->where('language_id', 1);
                }
            ])
            ->where(function ($query) {
                $query->orWhereHas('category', function ($q) {
                    $q->where('code', '!=', 'X');
                    $q->where('code', '!=', 'x');
                });
            });
        $categories_count = $_categories->count();
        $categories = $_categories->get();

        if ($categories_count == 0) {
            $data = [
                'text' => 'Categorías no encontradas. (Básico no cuenta)',
                'error' => true
            ];

            return Response::json($data);
        }

        $rate_sale_markups = [];
        if ($client_id == null || $client_id == "")
        {
            $rate_sale_markups = PackageRateSaleMarkup::with('seller')
                ->where('package_plan_rate_id', $plan_rate_id)->get();
        }else{

            $rate_sale_markups = PackageRateSaleMarkup::with('seller')
                ->where('package_plan_rate_id', $plan_rate_id)->where('seller_id',$client_id)->get();

            if ($rate_sale_markups->count() == 0)
            {
                $client_market_id = Client::where('id',$client_id)->first()->market_id;
                $rate_sale_markups = PackageRateSaleMarkup::with('seller')
                    ->where('package_plan_rate_id', $plan_rate_id)
                    ->where('seller_id',$client_market_id)
                    ->where('seller_type','App\Market')->get();
            }
        }

        if( count($rate_sale_markups) === 0 ){

            Cache::put('rate_errors', [
                $plan_rate_id =>
                [
                    "id" => 0,
                    "type" => 'rate_sale_markups'
                ]
            ], now()->addMinutes(1));

            return Response::json(['success'=>false, 'data'=> 'rate_sale_markups' ]);
        }

        $data_rates = new PackageRatesExportMultiple($plan_rate_id, $service_type_id, $categories, $title,
                                                        $rate_sale_markups, $lang, $year);

        return Excel::download($data_rates, 'xxx.xlsx');

    }

    public function rate_errors(){
        return Response::json(['data'=> Cache::get('rate_errors') ]);
    }

    public function selectBox($package_id, Request $request)
    {
        $quotes = PackagePlanRate::with(['service_type'])
            ->where('package_id', $package_id)
            ->where('status', 1)
            ->get();
        return Response::json(['success' => true, 'data' => $quotes]);
    }

    public function getPlanRatesCategories($plan_rate_id, Request $request)
    {
        $lang = $request->input('lang');
        $categories = PackagePlanRateCategory::with([
            'category' => function ($query) use ($lang) {
                $query->with([
                    'translations' => function ($q) use ($lang) {
                        $q->where('type', 'typeclass');
                        $q->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    }
                ]);

            }
        ])->withCount([
            'sale_rates' => function ($query) use ($plan_rate_id) {
                $query->select(DB::raw('SUM(simple) as total'));
            }
        ])->whereHas('sale_rates')->whereHas('services')->where('package_plan_rate_id', $plan_rate_id)->get();
        if ($categories->count() > 0) {
            $categories->transform(function ($TypeClass) {
                return [
                    "class_id" => $TypeClass["category"]["id"],
                    "class_name" => $TypeClass["category"]["translations"][0]["value"]
                ];
            });
            return Response::json(['success' => true, 'data' => $categories]);
        } else {
            return Response::json(['success' => true, 'data' => []]);
        }

    }

    public function getPlanRatesTypeService($package_id, Request $request)
    {
        $lang = $request->input('lang');
        $categories = PackagePlanRate::with([
            'service_type' => function ($query) use ($lang) {
                $query->with([
                    'translations' => function ($q) use ($lang) {
                        $q->where('type', 'servicetype');
                        $q->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    }
                ]);

            }
        ])->where('package_id', $package_id)
            ->where('status', 1)->get();
        if ($categories->count() > 0) {
            $categories->transform(function ($TypeClass) {
                return [
                    "id" => $TypeClass["service_type"]["id"],
                    "code" => $TypeClass["service_type"]["code"],
                    "abbreviation" => $TypeClass["service_type"]["abbreviation"],
                    "translations" => $TypeClass["service_type"]["translations"]
                ];
            });
            return Response::json(['success' => true, 'data' => $categories]);
        } else {
            return Response::json(['success' => true, 'data' => []]);
        }

    }

}
