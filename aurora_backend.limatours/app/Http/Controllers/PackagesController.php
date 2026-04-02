<?php

namespace App\Http\Controllers;

use App\ChannelHotel;
use App\Client;
use App\ClientSeller;
use App\Country;
use App\Exports\PackageRatesExportMultiple;
use App\Hotel;
use App\Http\Requests\CreateRateSaleRequest;
use App\Http\Services\Traits\ClientTrait;
use App\Language;
use App\Package;
use App\PackageDynamicRateCopy;
use App\PackageDynamicSaleRate;
use App\PackageExtension;
use App\PackageHighlight;
use App\PackagePermission;
use App\PackagePlanRate;
use App\PackagePlanRateCategory;
use App\PackageRateSaleMarkup;
use App\PackageService;
use App\PackageTax;
use App\PackageTranslation;
use App\Service;
use App\RatesPlansCalendarys;
use App\Tag;
use App\Http\Traits\Images;
use App\Http\Traits\Translations;
use App\PackageDuplicationInfo;
use App\PackageFixedSaleRate;
use App\Services\DuplicatePackageService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use Illuminate\Support\Str;

class PackagesController extends Controller
{
    use Translations, \App\Http\Traits\Package, Images, ClientTrait;

    protected $type_pax = [
        1 => 'simple',
        2 => 'double',
        3 => 'triple',
    ];

    public function __construct()
    {
        $this->middleware('permission:packages.read')->only('index');
        $this->middleware('permission:packages.create')->only('store');
        $this->middleware('permission:packages.update')->only('update');
        $this->middleware('permission:packages.delete')->only('delete');
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = ($request->has('limit')) ? $request->input('limit') : 10;
        $status = ($request->has('status')) ? [(int)$request->input('status')] : [1, 0];
        $querySearch = $request->input('queryCustom');
        $lang = $request->input('lang');
        $filterBy = ($request->has('filterBy')) ? $request->input('filterBy') : 2;

        $filter_plan_rates = ($request->input('filter_plan_rates') === 'true');

        $filter_exclusive = ($request->input('filter_exclusive') === 'true');
        $filter_generals = ($request->input('filter_generals') === 'true');

        $permissions_package = PackagePermission::select(['package_id'])->where(
            'user_id',
            Auth::user()->id
        )->distinct('package_id')->get();
        $permissions_not_package = PackagePermission::select(['package_id'])->where(
            'user_id',
            '<>',
            Auth::user()->id
        )->distinct('package_id')->get();

        $packages = Package::whereIn('status', $status)
            ->with([
                'tag.translations' => function ($query) use ($lang) {
                    $query->where('type', 'tag');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                },
                'country.translations',
                'tag.tag_group.translations' => function ($query) use ($lang) {
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                },
            ])->with([
                'physical_intensity',
            ])->with([
                'translations' => function ($query) use ($lang) {
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                },
            ])
            ->with('duplicationInfo');

        if ($filter_plan_rates) {
            $date_from = Carbon::now();
            $packages->with([
                'plan_rates' => function ($query) use ($date_from, $lang) {
                    $query->where('date_from', '<=', $date_from);
                    $query->where('date_to', '>=', $date_from);
                    $query->where('status', 1);
                    $query->with([
                        'plan_rate_categories' => function ($q) use ($lang) {
                            $q->whereHas('sale_rates');
                            $q->with([
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
                            ]);
                        },
                        'service_type'
                    ]);
                    $query->whereHas('plan_rate_categories.sale_rates');
                }
            ]);
        }

        if ($querySearch) {
            $packages = $packages->where(function ($query) use ($querySearch) {

                $new_q = str_replace('P', '', str_replace('E', '', $querySearch));
                $query->orWhere('id', 'like', '%' . $new_q . '%');

                $query->orWhere('code', 'like', '%' . $querySearch . '%');

                $query->orWhereHas('translations', function ($q) use ($querySearch) {

                    $q->where('name', 'like', '%' . $querySearch . '%');
                });
            });
        }

        if ($filterBy != 2) {
            $packages->where('extension', $filterBy);
        }

        // exclusive  |  generals
        $permissions_ids = DB::table('permission_role')
            ->where('role_id', Auth::user()->roles()->first()->id)
            ->pluck('permission_id');
        $permissions = DB::table('permissions')
            ->where('name', 'like', 'Packages:%')
            ->whereIn('id', $permissions_ids)->get();
        $p_generals = false;
        $p_exclusive = false;
        foreach ($permissions as $p) {
            if ($p->slug == 'packages.exclusive') {
                $p_exclusive = true;
            }
            if ($p->slug == 'packages.generals') {
                $p_generals = true;
            }
        }

        // INFO: Se comentó porque se mejoraron los filtros, ya no es por un texto fijo sino por los ids de groups y tags
        // exclusive  |  generals
        // $exclusive_tag_group_id = DB::table('translations')
        //     ->whereNull('deleted_at')
        //     ->where('type', 'taggroup')
        //     ->where('slug', 'group_name')
        //     ->where('language_id', 1)
        //     ->where('value', "EXCLUSIVAS")
        //     ->first();

        // if ($exclusive_tag_group_id) {

        //     $exclusive_tag_group_id = $exclusive_tag_group_id->object_id;

        //     $exclusive_tags_ids = Tag::whereHas('tag_group', function ($q) use ($exclusive_tag_group_id) {
        //         $q->where('id', $exclusive_tag_group_id);
        //     })->pluck('id');

        //     if (($p_exclusive and !($p_generals)) || ($p_generals and !($p_exclusive))) {

        //         if ($p_exclusive) {
        //             $packages->whereIn('tag_id', $exclusive_tags_ids);
        //         }
        //         if ($p_generals) {
        //             $packages->whereNotIn('tag_id', $exclusive_tags_ids);
        //         }
        //     } else {

        //         if (($filter_exclusive and !($filter_generals)) || ($filter_generals and !($filter_exclusive))) {

        //             if ($filter_exclusive) {
        //                 $packages->whereIn('tag_id', $exclusive_tags_ids);
        //             }
        //             if ($filter_generals) {
        //                 $packages->whereNotIn('tag_id', $exclusive_tags_ids);
        //             }
        //         }
        //     }
        // }

        $packages->when($request->input('group_id'), function ($query, $groupId) {
            return $query->whereHas('tag', function ($query) use ($groupId) {
                return $query->where('tag_group_id', $groupId);
            });
        });

        $packages->when($request->input('tag_id'), function ($query, $tagId) {
            return $query->whereHas('tag', function ($query) use ($tagId) {
                return $query->where('id', $tagId);
            });
        });

        $packages->when($request->input('type_package'), function ($query, $typePackage) {
            return $query->where('extension', $typePackage);
        });

        $count = $packages->count();
        if ($paging === 1) {
            $packages = $packages->take($limit)->orderBy('status', 'desc')->get();
        } else {
            $packages = $packages->skip($limit * ($paging - 1))->orderBy('status', 'desc')->take($limit)->get();
        }

        $packages->each->append('is_processing_plan_rates');

        $packages = $packages->transform(function ($item) use ($permissions_not_package) {
            $item['allow_edit'] = true;
            foreach ($permissions_not_package as $permissions_not) {
                if ($permissions_not['package_id'] == $item['id']) {
                    $item['allow_edit'] = false;
                }
            }
            return $item;
        });

        $packages = $packages->transform(function ($item) use ($permissions_package) {
            foreach ($permissions_package as $permissions) {
                if ($permissions['package_id'] == $item['id']) {
                    $item['allow_edit'] = true;
                }
            }
            return $item;
        });


        $data = [
            'data' => $packages,
            'count' => $count,
            'success' => true,
            'permissions' => [
                'generals' => $p_generals,
                'exclusive' => $p_exclusive
            ]
        ];

        return Response::json($data);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'unique:packages',
            'physical_intensity_id' => 'required',
            'tag_id' => 'required',
            'duration' => 'required',
        ]);

        if ($validator->fails()) {
            $response = ['success' => false];
        } else {
            $package = new Package();
            $package->code = $request->input('code');
            $package->country_id = $request->input('country_id');
            $package->status = 1;
            $package->nights = $request->input('duration');
            $package->extension = $request->input('type_package');
            $package->rate_dynamic = $request->input('rate_dynamic');
            $package->physical_intensity_id = $request->input('physical_intensity_id');
            $package->tag_id = $request->input('tag_id');
            $package->recommended = $request->input('recommended');
            $package->allow_modify = $request->input('allow_modify');
            $package->free_sale = $request->input('free_sale');
            $package->portada_link = '//' . str_replace(['https://', 'http://', '//'], '', $request->input('portada_link'));
            $package->map_link = '//' . str_replace(['https://', 'http://', '//'], '', $request->input('map_link'));
            $package->map_itinerary_link = '//' . str_replace(['https://', 'http://', '//'], '', $request->input('map_itinerary_link'));
            $package->user_id = Auth::user()->id;
            $package->save();

            // TEXTOS POR DEFECTO
            $langs = Language::where('state', 1)->get();

            foreach ($langs as $lang) {
                if ($lang->id <= 4) {
                    $package_translation = new PackageTranslation();
                    $package_translation->language_id = $lang->id;
                    $package_translation->package_id = $package->id;
                    $package_translation->name = '';
                    $package_translation->tradename = '';
                    $package_translation->description = '';
                    $package_translation->label = '';
                    $package_translation->itinerary_link = '';
                    $package_translation->itinerary_label = '';
                    $package_translation->itinerary_description = '';
                    $package_translation->inclusion = '';
                    $package_translation->restriction = '';
                    $package_translation->policies = '';
                    $package_translation->save();
                }
            }

            $response = ['success' => true, 'object_id' => $package->id];
        }

        return Response::json($response);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        $lang = $request->input("lang");

        $package = Package::with([
            'country.translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            },
        ])->with(['country.taxes'])->with([
            'physical_intensity.translations' => function ($query) use ($lang) {
                $query->where('type', 'physicalintensity');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            },
        ])->with([
            'tag.translations' => function ($query) use ($lang) {
                $query->where('type', 'tag');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            },
        ])->with([
            'tag.tag_group.translations' => function ($query) use ($lang) {
                $query->where('type', 'taggroup');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            },
        ])
            ->with('duplicationInfo')
            ->where('id', $id)
            ->first();

        $package->append('is_processing_plan_rates');

        if ($package) {
            foreach ($package['country']['taxes'] as $tax) {
                $totalTaxUsed = PackageTax::where('tax_id', $tax->id)->where(
                    'package_id',
                    $package->id
                )->get()->count();
                $tax->used = (!$totalTaxUsed) ? false : true;
            }
        }

        return Response::json(['success' => true, 'data' => $package]);
    }

    /**
     * @param  Request  $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'physical_intensity_id' => 'required',
            'tag_id' => 'required',
            'duration' => 'required',
        ];

        $package = Package::find($id);

        if ($package->code != $request->input('code')) {
            $rules['code'] = 'unique:packages';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response = ['success' => false];
        } else {
            $package->code = $request->input('code');
            $package->country_id = $request->input('country_id');
            $package->nights = $request->input('duration');
            $package->extension = $request->input('type_package');
            $package->rate_dynamic = $request->input('rate_dynamic');
            $package->physical_intensity_id = $request->input('physical_intensity_id');
            $package->tag_id = $request->input('tag_id');
            $package->recommended = $request->input('recommended');
            $package->allow_modify = (int)$request->input('allow_modify');
            $package->free_sale = $request->input('free_sale');
            $package->portada_link = (!empty($request->input('portada_link'))) ? ('//' . str_replace(['https://', 'http://', '//'], '', $request->input('portada_link'))) : '';
            $package->map_link = (!empty($request->input('map_link'))) ? ('//' . str_replace(['https://', 'http://', '//'], '', $request->input('map_link'))) : '';
            $package->map_itinerary_link = (!empty($request->input('map_itinerary_link'))) ? ('//' . str_replace(['https://', 'http://', '//'], '', $request->input('map_itinerary_link'))) : '';

            if (!empty($package->user_id)) {
                $package->user_id = Auth::user()->id;
            }

            $package->save();

            $response = ['success' => true, 'object_id' => $package->id];
        }

        return Response::json($response);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $package = Package::find($id);
        $package->status = -1;
        $package->tag_id = null;

        return Response::json(['success' => $package->save()]);
    }

    public function updateStatus($id, Request $request)
    {
        $package = Package::find($id);
        if ($request->input("status")) {
            $package->status = 0;
        } else {
            $package->status = 1;
        }
        $package->save();
        return Response::json(['success' => true]);
    }

    public function updateRecommended($id, Request $request)
    {
        $package = Package::find($id);
        if ($request->input("recommended")) {
            $package->recommended = 0;
        } else {
            $package->recommended = 1;
        }
        $package->save();
        return Response::json(['success' => true]);
    }

    public function updateFreeSale($id, Request $request)
    {
        $package = Package::find($id);
        if ($request->input("free_sale")) {
            $package->free_sale = 0;
        } else {
            $package->free_sale = 1;
        }
        $package->save();
        return Response::json(['success' => true]);
    }

    /**
     * @param $id
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updateTax($package_id, Request $request)
    {
        $used = $request->input("used");
        $tax_id = $request->input("tax_id");

        if ($used) {
            $findTax = PackageTax::where('package_id', $package_id)->where('tax_id', $tax_id)->get();
            if ($findTax->count() == 0) {
                $packageTax = new PackageTax;
                $packageTax->package_id = $package_id;
                $packageTax->tax_id = $tax_id;
                $packageTax->save();
            }
        } else {
            $findTax = PackageTax::where('package_id', $package_id)->where('tax_id', $tax_id)->get()->first();
            if ($findTax) {
                $findTax->delete();
            }
        }

        return Response::json(['success' => true]);
    }

    public function updateConfigurations($id, Request $request)
    {
        $package = Package::find($id);

        $package->allow_guide = $request->input("allow_guide");
        $package->allow_child = $request->input("allow_child");
        $package->allow_infant = $request->input("allow_infant");
        $package->limit_confirmation_hours = $request->input("limit_confirmation_hours");
        $package->infant_min_age = $request->input("infant_min_age");
        $package->infant_max_age = $request->input("infant_max_age");
        $package->infant_discount_rate = $request->input("infant_discount_rate");
        $package->save();

        return Response::json(['success' => true]);
    }

    public function getPackageRatesCost(Request $request)
    {
        $package_id = $request->get('package_id');

        $rates_sale = PackagePlanRate::with('package.translations')->with('service_type')->where(
            'package_id',
            $package_id
        )->get();

        return response()->json($rates_sale, 200);
    }

    public function getSalesRateMarkups(Request $request)
    {
        $package_plan_rate_id = $request->get('package_plan_rate_id');

        $sales = PackageRateSaleMarkup::with('seller')->where('package_plan_rate_id', $package_plan_rate_id)->get();

        $sales = $sales->transform(function ($item) {

            if ($item->seller_type == 'App\Client') {
                $country = Country::with([
                    'translations' => function ($query) {
                        $query->where('type', 'country');
                        $query->where('language_id', 1);
                    }
                ])->where('id', $item->seller->country_id)->first();

                $item->seller->countries = $country;
            }
            return $item;
        });

        return response()->json($sales, 200);
    }

    public function addPackageRateSaleMarkup(Request $request)
    {
        $package_plan_rate_id = $request->post('package_plan_rate_id');
        $market_id = $request->post('market_id');
        $country_id = $request->post('country_id');
        $markup = ($request->has('markup')) ? $request->post('markup') : 0;

        if (Auth::user()->user_type_id == 1) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }
        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->post('client_id');
        }

        if (!empty($market_id) and empty($country_id) and count($client_id) == 0) {
            $sale = new PackageRateSaleMarkup();
            $sale->package_plan_rate_id = $package_plan_rate_id;
            $exists_markup = PackageRateSaleMarkup::where('seller_type', "App\Market")->where(
                'seller_id',
                $market_id
            )->where('package_plan_rate_id', $package_plan_rate_id)->get();
            if ($exists_markup->count() > 0) {
                return response()->json(["message" => "ya este mercado ha sido guardado"], 422);
            } else {
                $sale->seller_type = "App\Market";
                $sale->seller_id = $market_id;
                $sale->markup = $markup;
                $sale->status = 1;
                $sale->save();

                $sale_id = $sale->id;

                $category_ids = PackagePlanRateCategory::where(
                    'package_plan_rate_id',
                    $package_plan_rate_id
                )->pluck('id');
                $packageRates = PackageDynamicRateCopy::whereIn('package_plan_rate_category_id', $category_ids)->get();
                DB::transaction(function () use ($sale_id, $packageRates) {
                    foreach ($packageRates as $packageRate) {
                        DB::table('package_dynamic_sale_rates')->insert([
                            'service_type_id' => $packageRate["service_type_id"],
                            'package_plan_rate_category_id' => $packageRate["package_plan_rate_category_id"],
                            'pax_from' => $packageRate["pax_from"],
                            'pax_to' => $packageRate["pax_to"],
                            'simple' => $packageRate["simple"],
                            'double' => $packageRate["double"],
                            'triple' => $packageRate["triple"],
                            'status' => 1,
                            'updated_at' => date("Y-m-d H:i:s"),
                            'package_rate_sale_markup_id' => $sale_id
                        ]);
                    }
                });
            }
        } elseif (count($client_id) > 0) {
            DB::transaction(function () use ($client_id, $package_plan_rate_id, $markup) {
                foreach ($client_id as $key => $client) {
                    $exists_markup = PackageRateSaleMarkup::where('seller_type', "App\Client")->where(
                        'seller_id',
                        $client['id']
                    )->where('package_plan_rate_id', $package_plan_rate_id)->get();
                    if ($exists_markup->count() > 0) {
                        continue;
                    } else {
                        $sale = new PackageRateSaleMarkup();
                        $sale->package_plan_rate_id = $package_plan_rate_id;
                        $sale->seller_type = "App\Client";
                        $sale->seller_id = $client['id'];
                        $sale->markup = $markup;
                        $sale->status = 1;
                        $sale->save();
                        $sale_id = $sale->id;
                        $category_ids = PackagePlanRateCategory::where(
                            'package_plan_rate_id',
                            $package_plan_rate_id
                        )->pluck('id');
                        $packageRates = PackageDynamicRateCopy::whereIn(
                            'package_plan_rate_category_id',
                            $category_ids
                        )->get();
                        DB::transaction(function () use ($sale_id, $packageRates) {
                            foreach ($packageRates as $packageRate) {
                                DB::table('package_dynamic_sale_rates')->insert([
                                    'service_type_id' => $packageRate["service_type_id"],
                                    'package_plan_rate_category_id' => $packageRate["package_plan_rate_category_id"],
                                    'pax_from' => $packageRate["pax_from"],
                                    'pax_to' => $packageRate["pax_to"],
                                    'simple' => $packageRate["simple"],
                                    'double' => $packageRate["double"],
                                    'triple' => $packageRate["triple"],
                                    'status' => 1,
                                    'updated_at' => date("Y-m-d H:i:s"),
                                    'package_rate_sale_markup_id' => $sale_id
                                ]);
                            }
                        });
                    }
                }
            });
        }

        return response()->json(["message" => "registro agregado correctamente"]);
    }

    public function updateStatusPackageRateSaleMarkup(Request $request)
    {
        $sale_id = $request->post('sale_id');

        $sale = PackageRateSaleMarkup::find($sale_id);

        if ($sale->status == 1) {
            $sale->status = 0;
        } else {
            $sale->status = 1;
        }
        $sale->save();

        return response()->json(["message" => "Status actualizado correctamente"]);
    }

    public function updateMarkupPackageRateSale(CreateRateSaleRequest $request)
    {
        try {
            $sale_id = $request->post('sale_id');
            $markup = $request->post('markup');
            $sale = PackageRateSaleMarkup::findOrFail($sale_id);
            $sale->markup = $markup;
            $sale->save();

            $prices = collect();
            $sale_id = $sale->id;
            $category_ids = PackagePlanRateCategory::where(
                'package_plan_rate_id',
                $sale->package_plan_rate_id
            )->pluck('id');
            $ages_children = $this->getPackageChildrenByCategory($category_ids[0]);
            foreach ($category_ids as $category_id) {
                $this->calculatePricePackageCopy($category_id, $sale_id, $markup);
                if ($ages_children->count() > 0) {
                    $prices->add($this->updateDynamicSaleRatesPackageChildren($category_id, $ages_children, $sale_id));
                }
            }
            return response()->json(["message" => "Markup actualizado correctamente"]);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()]);
        }
    }

    public function updateMarkups(Request $request)
    {

        $package_plan_rates = $request->post('package_plan_rates');
        $package_plan_rates_errors = [];
        $ages_children = $this->getPackageChildrenByPlanRate($package_plan_rates[0]['package_plan_rate_id']);

        foreach ($package_plan_rates as $package_plan_rate) {

            $package_plan_rates_errors_i = count($package_plan_rates_errors);
            $package_plan_rates_errors[$package_plan_rates_errors_i] = $package_plan_rate;
            $package_plan_rates_errors[$package_plan_rates_errors_i]['categories'] = [];

            $sale = PackageRateSaleMarkup::findOrFail($package_plan_rate["id"]);

            // dd($package_plan_rate['markup']); -- 18

            $sale->markup = $package_plan_rate["markup"];
            $sale->save();

            $categories = PackagePlanRateCategory::where('package_plan_rate_id', $sale->package_plan_rate_id)
                ->with([
                    'type_class.translations' => function ($query1) {
                        $query1->where('language_id', 1);
                    }
                ])
                ->get();

            foreach ($categories as $category) {
                $this->regularize_sale_ranges_in_category($category->id);
            }

            foreach ($categories as $category) {
                $excecute_ = $this->calculatePricePackageCopy(
                    $category->id,
                    $package_plan_rate["id"],
                    $package_plan_rate["markup"]
                );
                if (!($excecute_["success"])) {
                    array_push($package_plan_rates_errors[$package_plan_rates_errors_i]['categories'], [
                        "id" => $category->id,
                        "name" => $category->type_class->translations[0]->value,
                        "hotel_errors" => $excecute_["errors"]
                    ]);
                } else {
                    if ($ages_children->count() > 0) {
                        $this->updateDynamicSaleRatesPackageChildren(
                            $category->id,
                            $ages_children,
                            $package_plan_rate["id"]
                        );
                    }
                }
            }

            if (count($package_plan_rates_errors[$package_plan_rates_errors_i]['categories']) === 0) {
                unset($package_plan_rates_errors[$package_plan_rates_errors_i]);
            }
        }

        if (count($package_plan_rates_errors) > 0) {
            return response()->json([
                "success" => false,
                "message" => "Errores en tarifas",
                "errors" => $package_plan_rates_errors
            ]);
        } else {
            return response()->json(["success" => true, "message" => "Markups Actualizados correctamente"]);
        }
    }

    public function recalculateMarkupPackageRateSale(Request $request)
    {
        $package_plan_rate_id = $request->get('package_plan_rate_id');

        DB::transaction(function () use ($package_plan_rate_id) {
            $sales = DB::table('package_rate_sale_markups')
                ->whereNull('deleted_at')
                ->where('status', 1)->where(
                    'package_plan_rate_id',
                    $package_plan_rate_id
                )->get();

            foreach ($sales as $sale) {

                $category_ids = DB::table('package_plan_rate_categories')->where(
                    'package_plan_rate_id',
                    $sale->package_plan_rate_id
                )->where('deleted_at', null)->pluck('id');

                foreach ($category_ids as $category_id) {
                    $this->resetDynamicRateCopy($category_id);

                    $services_dates = DB::table('package_services')
                        ->where('package_plan_rate_category_id', $category_id)
                        ->where('type', 'service')
                        ->where('deleted_at', null)
                        ->orderBy('date_in', 'asc')->get();

                    foreach ($services_dates as $service_date) {
                        $services_rates = DB::table('package_service_rates')->where(
                            'package_service_id',
                            $service_date->id
                        )->whereNull('deleted_at')->get();

                        foreach ($services_rates as $service_rate) {
                            $service_rate_plans = DB::table('service_rate_plans')
                                ->whereNull('deleted_at')
                                ->where('service_rate_id', $service_rate->service_rate_id)
                                ->where('date_from', '<=', $service_date->date_in)
                                ->where('date_to', '>=', $service_date->date_in)
                                ->get();

                            $this->updateDynamicRateByServiceCopy(
                                $service_rate_plans,
                                $category_id,
                                $service_date->calculation_included,
                                $sale->markup
                            );
                        }
                    }

                    $hotels_dates = DB::table('package_services')
                        ->where('package_plan_rate_category_id', $category_id)
                        ->where('type', 'hotel')
                        ->orderBy('date_in', 'desc')
                        ->where('deleted_at', null)
                        ->orderBy('order', 'asc')->get()->groupBy('date_in');

                    foreach ($hotels_dates as $date) {
                        $simple_hotels = 0;
                        $double_hotels = 0;
                        $triple_hotels = 0;
                        $package_service_id = $date[0]->id;
                        $date_in = $date[0]->date_in;
                        $date_out = Carbon::parse($date[0]->date_out)->subDay(1)->format('Y-m-d');

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
                                $calendars = DB::table('rates_plans_calendarys')
                                    ->whereNull('deleted_at')
                                    ->where('rates_plans_room_id', $package_service_room->rate_plan_room_id)
                                    ->where('date', '>=', $date_in)
                                    ->where('date', '<=', $date_out)
                                    ->get();

                                foreach ($calendars as $calendar) {
                                    $rate = DB::table('rates')->whereNull('deleted_at')
                                        ->where(
                                            'rates_plans_calendarys_id',
                                            $calendar->id
                                        )->first();
                                    if ($date[0]->calculation_included == 1) {
                                        $simple_hotels += $rate->price_adult;
                                    } else {
                                        $simple_hotels += ($rate->price_adult + ($rate->price_adult * ($sale->markup / 100)));
                                    }
                                }
                                break;
                            }
                        }
                        //Buscar Doble
                        foreach ($package_service_rooms as $package_service_room) {

                            if ($package_service_room->occupation == 2) {
                                $calendars = DB::table('rates_plans_calendarys')
                                    ->whereNull('deleted_at')
                                    ->where('rates_plans_room_id', $package_service_room->rate_plan_room_id)
                                    ->where('date', '>=', $date_in)
                                    ->where('date', '<=', $date_out)
                                    ->get();

                                foreach ($calendars as $calendar) {
                                    $rate = DB::table('rates')
                                        ->whereNull('deleted_at')
                                        ->where(
                                            'rates_plans_calendarys_id',
                                            $calendar->id
                                        )->first();
                                    if ($date[0]->calculation_included == 1) {
                                        $double_hotels += $rate->price_adult;
                                    } else {
                                        $double_hotels += ($rate->price_adult + ($rate->price_adult * ($sale->markup / 100)));
                                    }
                                }
                                break;
                            }
                        }
                        //Buscar Triple
                        foreach ($package_service_rooms as $package_service_room) {

                            if ($package_service_room->occupation == 3) {
                                $calendars = DB::table('rates_plans_calendarys')
                                    ->whereNull('deleted_at')
                                    ->where('rates_plans_room_id', $package_service_room->rate_plan_room_id)
                                    ->where('date', '>=', $date_in)
                                    ->where('date', '<=', $date_out)
                                    ->get();

                                foreach ($calendars as $calendar) {
                                    $rate = DB::table('rates')
                                        ->whereNull('deleted_at')
                                        ->where(
                                            'rates_plans_calendarys_id',
                                            $calendar->id
                                        )->first();
                                    if ($date[0]->calculation_included == 1) {
                                        $triple_hotels += $rate->price_adult + $rate->price_extra;
                                    } else {
                                        $rate_ = $rate->price_adult + $rate->price_extra;
                                        $triple_hotels += ($rate_ + ($rate_ * ($sale->markup / 100)));
                                    }
                                }
                                break;
                            }
                        }
                        $this->updateDynamicRateByHotelCopy(
                            $simple_hotels,
                            $double_hotels,
                            $triple_hotels,
                            $category_id
                        );
                    }
                    $packageRates = DB::table('package_dynamic_rate_copies')
                        ->whereNull('deleted_at')->where(
                            'package_plan_rate_category_id',
                            $category_id
                        )->get();
                    foreach ($packageRates as $packageRate) {
                        $find = PackageDynamicSaleRate::where('package_rate_sale_markup_id', $sale->id)
                            ->where('service_type_id', $packageRate->service_type_id)
                            ->where('package_plan_rate_category_id', $packageRate->package_plan_rate_category_id)
                            ->where('pax_from', $packageRate->pax_from)
                            ->where('pax_to', $packageRate->pax_to)
                            ->first();
                        if (isset($find->id)) {
                            PackageDynamicSaleRate::find($find->id)->delete();
                        }
                        $new = new PackageDynamicSaleRate();
                        $new->service_type_id = $packageRate->service_type_id;
                        $new->package_plan_rate_category_id = $packageRate->package_plan_rate_category_id;
                        $new->pax_from = $packageRate->pax_from;
                        $new->pax_to = $packageRate->pax_to;
                        $new->simple = $packageRate->simple;
                        $new->double = $packageRate->double;
                        $new->triple = $packageRate->triple;
                        $new->status = 1;
                        $new->package_rate_sale_markup_id = $sale->id;
                        $new->save();
                    }
                    DB::table('package_dynamic_rate_copies')->where(
                        'package_plan_rate_category_id',
                        $category_id
                    )->delete();
                }
            }
        });

        return response()->json(["message" => "Tablas tarifarias de venta actualizadas correctamente"]);
    }

    public function getActive(Request $request)
    {
        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }

        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->input('client_id');
        }

        $package_id = ($request->has('package_id')) ? $request->input('package_id') : [];
        //    throw new \Exception(json_encode($package_id));

        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $type_service = $request->input('service_type');
        $date_from = $request->input('date');
        $from = Carbon::parse($date_from);
        $from = $from->format('Y-m-d');
        $quantity_persons = $request->input('quantity_persons');
        $adult = $quantity_persons[0]['adults'];
        $child_with_bed = (int)$quantity_persons[0]['child_with_bed'];
        $child_without_bed = (int)$quantity_persons[0]['child_without_bed'];
        $child = $child_with_bed + $child_without_bed;
        $age_child = $quantity_persons[0]['age_childs'];
        $type_class = $request->input('type_class');
        $type_package = ($request->has('type_package')) ? $request->input('type_package') : [0];

        $packages = $this->getPackagesByType(
            $type_package,
            $language,
            $client_id,
            $type_service,
            $from,
            $type_class,
            $adult,
            $child,
            $age_child,
            [],
            0,
            $package_id
        );
        return response()->json($packages, 200);
    }


    public function getPackagesByType(
        $type_package,
        $language,
        $client_id,
        $type_service,
        $date_from,
        $type_class,
        $adult,
        $child,
        $age_childs,
        $destiny,
        $recommendations,
        $package_id = null
    ) {

        $client = Client::findOrFail($client_id);
        $paxTotal = $adult + $child;

        $packages = $this->getPackagesClient(
            $client,
            $destiny,
            $type_package,
            $language,
            $type_service,
            $date_from,
            $type_class,
            $adult,
            $child,
            0,
            [],
            '',
            0,
            $recommendations,
            false,
            $package_id
        );

        //        return $packages;

        $packages = $packages->transform(function ($package) use (
            $client,
            $paxTotal,
            $type_class,
            $type_package,
            $package_id,
            $date_from,
            $language
        ) {

            $packages = [
                'id' => $package['id'],
                'country_id' => $package['country_id'],
                'code' => $package['code'],
                'extension' => $package['extension'],
                'nights' => $package['nights'],
                'map_link' => $package['map_link'],
                'image_link' => $package['image_link'],
                'status' => $package['status'],
                'reference' => $package['reference'],
                'rate_type' => $package['rate_type'],
                'rate_dynamic' => $package['rate_dynamic'],
                'allow_guide' => $package['allow_guide'],
                'allow_child' => $package['allow_child'],
                'allow_infant' => $package['allow_infant'],
                'limit_confirmation_hours' => $package['limit_confirmation_hours'],
                'infant_min_age' => $package['infant_min_age'],
                'infant_max_age' => $package['infant_max_age'],
                'physical_intensity_id' => $package['physical_intensity_id'],
                'tag_id' => $package['tag_id'],
                'allow_modify' => $package['allow_modify'],
                'recommended' => $package['recommended'],
                'destinations' => $package['destinations'],
                'price' => 0,
                'without_discount' => 0,
                'offer' => false,
                'rated' => ($package->rated->count() > 0) ? $package->rated[0]->rated : 0,
                'package_destinations' => $package->package_destinations,
                'tag' => $package->tag,
                'translations' => $package->translations,
                'fixed_outputs' => $package->fixed_outputs,
                'galleries' => $package->galleries,
                'plan_rates' => $package->plan_rates,
                'extension_recommended' => $package->extension_recommended,
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
                ]
            ];

            $package_rate_sale_markup_id = '';

            foreach ($packages["galleries"] as $key => $gallery) {
                $packages["galleries"][$key] = $this->verifyCloudinaryImg($gallery['url'], 500, 450, '');
            }

            foreach ($package["plan_rates"] as $package_plan_rate) {
                $offer_query = $package_plan_rate->offers;
                $package_rate_sale_markup = PackageRateSaleMarkup::where(
                    'seller_type',
                    "App\Client"
                )->where('seller_id', $client->id)->where(
                    'package_plan_rate_id',
                    $package_plan_rate['id']
                )->where('status', 1)->get();
                if ($package_rate_sale_markup->count() > 0) {
                    $package_rate_sale_markup_id = $package_rate_sale_markup[0]["id"];
                } else {
                    $package_rate_sale_markup = PackageRateSaleMarkup::where(
                        'seller_type',
                        'App\Market'
                    )->where('package_plan_rate_id', $package_plan_rate['id'])->where(
                        'seller_id',
                        $client->market_id
                    )->where('status', 1)->get();

                    if ($package_rate_sale_markup->count() > 0) {
                        $package_rate_sale_markup_id = $package_rate_sale_markup[0]['id'];
                    }
                }
                if ($package_rate_sale_markup_id != '') {
                    $fields = $this->getFieldPriceByTotalPax($paxTotal, $package_plan_rate->service_type_id);
                    $field = $fields['field'];
                    $rangePax = $fields['range_pax'];
                    $min_price = PackageDynamicSaleRate::where(
                        'package_rate_sale_markup_id',
                        $package_rate_sale_markup_id
                    )->where(
                        'service_type_id',
                        $package_plan_rate->service_type_id
                    );
                    if ($type_class !== 'all') {
                        $min_price->where(
                            'package_plan_rate_category_id',
                            $package_plan_rate['plan_rate_categories'][0]['id']
                        );
                    }


                    if (count($package_id) > 0) {
                        //Obtengo el itinerario
                        $services = $this->getServicesByPackage(
                            $package_plan_rate['plan_rate_categories'][0]['id'],
                            $language->iso
                        );
                        $services = $this->getHotelsWithStatus($services, $date_from);
                        $packages["services"] = $services;
                        $packages["itinerary"] = $this->getItineraryByService($services);
                    }

                    $min_price = $min_price->where('pax_from', '<=', $rangePax)
                        ->where('pax_to', '>=', $rangePax)
                        ->where($field, '>', 0)->min($field);


                    if ((bool)$package['allow_child']) {
                        $price_child = PackageDynamicSaleRate::where(
                            'package_rate_sale_markup_id',
                            $package_rate_sale_markup_id
                        )->where(
                            'service_type_id',
                            $package_plan_rate->service_type_id
                        );
                        if ($type_class !== 'all') {
                            $price_child->where(
                                'package_plan_rate_category_id',
                                $package_plan_rate['plan_rate_categories'][0]['id']
                            );
                        }
                        $price_child = $price_child->where('pax_from', '<=', 2)
                            ->where('pax_to', '>=', 2)
                            ->where('double', '>', 0)->first(['child_with_bed', 'child_without_bed']);

                        $age_with_bed = $package->children->first(function ($age) {
                            return $age->has_bed === 1;
                        });

                        $age_without_bed = $package->children->first(function ($age) {
                            return $age->has_bed === 0;
                        });

                        if ($age_with_bed) {
                            $packages['price_child']['with_bed']['min_age'] = $age_with_bed->min_age;
                            $packages['price_child']['with_bed']['max_age'] = $age_with_bed->max_age;
                        }

                        if ($age_without_bed) {
                            $packages['price_child']['without_bed']['min_age'] = $age_without_bed->min_age;
                            $packages['price_child']['without_bed']['max_age'] = $age_without_bed->max_age;
                        }

                        $packages['price_child']['with_bed']['price'] = $price_child->child_with_bed;
                        $packages['price_child']['without_bed']['price'] = $price_child->child_without_bed;
                    }


                    if ($offer_query->count() > 0) {
                        $is_offer = $offer_query[0]->is_offer;
                        $offer_value = $offer_query[0]->value;
                        if ($is_offer) {
                            $packages["offer"] = true;
                            $packages["without_discount"] = (float)roundLito($min_price);
                            $min_price = $min_price - ($min_price * ($offer_value / 100));
                        } else {
                            $min_price = $min_price + ($min_price * ($offer_value / 100));
                        }
                    }

                    if ($packages["price"] === 0) {
                        $packages["price"] = (float)roundLito($min_price);
                    }
                }
            }
            return $packages;
        });

        return $packages;
    }

    /*
     * @params
     * $paxTotal = Total de pax
     * $type_service = tipo de servicio [sim,pc]
     * @return
     * $field = Campo que sirve para saber de donde sacar el precio [simple,doble,triple]
     */

    public function getFieldPriceByTotalPax($paxTotal, $type_service)
    {
        $field = '';
        $paxRange = $paxTotal;
        $cant = 1;
        if ($type_service == 1) { // Compartido
            if ($paxTotal >= 1 and $paxTotal <= 3) { //pax en base simple,doble,triple
                $field = $this->type_pax[$paxTotal];
            } else { // si es mayor a 3 en base doble
                $paxRange = 2;
                $field = $this->type_pax[2];
                $cant = $paxTotal;
            }
        } elseif ($type_service == 2) { //Privado
            if ($paxTotal >= 1 and $paxTotal <= 3) {  //pax en base simple,doble,triple
                $field = $this->type_pax[$paxTotal];
            } elseif ($paxTotal > 3) { // pax en base simple
                $paxRange = 2;
                $field = $this->type_pax[2];
                $cant = $paxTotal;
            }
        }

        $response = [
            'field' => $field,
            'range_pax' => $paxRange,
            'cant' => $cant
        ];

        return $response;
    }

    public function getExtensions(Request $request)
    {
        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }

        if (Auth::user()->user_type_id == 3) {
            $client_id = $request->post('client_id');
        }

        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $type_service = $request->input('service_type');
        $date_from = $request->input('date');
        $from = Carbon::parse($date_from);
        $from = $from->format('Y-m-d');
        $quantity_persons = $request->input('quantity_persons');
        $adult = $quantity_persons[0]['adults'];
        $child_with_bed = (int)$quantity_persons[0]['child_with_bed'];
        $child_without_bed = (int)$quantity_persons[0]['child_without_bed'];
        $child = $child_with_bed + $child_without_bed;
        $type_class = $request->input('type_class');
        $age_childs = $quantity_persons[0]['age_childs'];
        $type_package = ($request->has('type_package')) ? $request->input('type_package') : [0];

        $extensions = $this->getPackagesByType(
            [$type_package],
            $language,
            $client_id,
            $type_service,
            $from,
            $type_class,
            $adult,
            $child,
            $age_childs,
            [],
            0,
            []
        );

        return response()->json($extensions, 200);
    }

    public function savePackagesSelected(Request $request)
    {
        $lang = ($request->has('lang')) ? $request->input('lang') : 'es';
        $packages_selected = $request->post('packages_selected');
        $packages_params = $request->post('params');
        $date = $packages_params['date'];

        Cache::forget('packages_selected_' . Auth::user()->id);

        //Obtengo el itinerario de las extensiones y el paquete
        foreach ($packages_selected as $key => $package) {
            if ($packages_selected[$key] !== null) {
                $package_plan_rate_category_id = $packages_selected[$key]['plan_rates'][0]['plan_rate_categories'][0]['id'];
                //                throw new \Exception(json_encode());

                $services = $this->getServicesByPackage($package_plan_rate_category_id, $lang);
                if (count($services) === 0) {
                    $category = $packages_selected[$key]['plan_rates'][0]['plan_rate_categories'][0]['category']['translations'][0]['value'];
                    return response()->json([
                        'success' => false,
                        "message" => "El paquete ó extension seleccionado no cuenta con servicios en la categoria: " . $category,
                    ]);
                }
                $array_services_new = $this->updateDateInServices($services, $date, false);
                $services = $array_services_new["services"];
                $services = $this->getHotelsWithStatus($services, $date);
                $packages_selected[$key]['services'] = $services;
                $packages_selected[$key]['itinerary'] = $this->getItineraryByService($services);
                $date = $array_services_new["date_new"];
            }
        }
        //        var_export($packages_selected);
        //        die;
        $data = [
            'package' => $packages_selected,
            'params' => $packages_params,
        ];

        Cache::add('packages_selected_' . Auth::user()->id, $data, 3600);
        return response()->json([
            'success' => true,
            "message" => "paquetes seleccionados han sido guardados",
            "services" => $packages_selected
        ], 200);
    }

    public function get_services(Request $request)
    {
        $lang = ($request->has('lang')) ? $request->input('lang') : 'es';
        $date = $request->input('date');
        $package_plan_rate_category_id = $request->input('package_plan_rate_category_id');

        $services = $this->getServicesByPackage($package_plan_rate_category_id, $lang);
        $array_services_new = $this->updateDateInServices($services, $date, false);
        $services = $array_services_new["services"];
        $services = $this->getHotelsWithStatus($services, $date);
        $itinerary = $this->getItineraryByService($services);

        return response()->json([
            "services" => $services,
            "itinerary" => $itinerary,
        ], 200);
    }

    public function savePackageReserveDetails(Request $request)
    {
        $reserve_details = [
            "name_package" => $request->post('name_package'),
            "date_reserve" => $request->post('date_reserve'),
            "image_package" => $request->post('image_package'),
            "quantity_adults" => $request->post('quantity_adults'),
            "quantity_child" => $request->post('quantity_child'),
            "booking_code" => $request->post('booking_code'),
            "reference" => $request->post('reference'),
            "total" => $request->post('total'),
        ];

        Cache::add('packages_reserve_details_' . Auth::user()->id, $reserve_details, 3600);

        return response()->json(["message" => "detalles de reserva guardados", "details" => $reserve_details], 200);
    }

    public function getPackageReserveDetails()
    {

        $reserve_details = [];
        if (Cache::has('packages_reserve_details_' . Auth::user()->id)) {
            $reserve_details = Cache::get('packages_reserve_details_' . Auth::user()->id);
        }
        return response()->json($reserve_details, 200);
    }

    public function getPackagesSelected(Request $request)
    {
        $packages_selected = [];
        if (Cache::has('packages_selected_' . Auth::user()->id)) {
            $packages_selected = Cache::get('packages_selected_' . Auth::user()->id);
        }
        return response()->json($packages_selected, 200);
    }

    public function packageExtensions($package_id, Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');

        $extensions = Package::with([
            'translations' => function ($query) use ($lang) {
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            },
        ]);

        if ($querySearch) {
            $extensions->where(function ($query) use ($querySearch) {
                $query->orWhere('code', 'like', '%' . $querySearch . '%');
                $query->orWhereHas('translations', function ($q) use ($querySearch) {
                    $q->where('name', 'like', '%' . $querySearch . '%');
                });
            });
        }

        $extensions->where('extension', 1);
        $extensions_related_ids = PackageExtension::where('package_id', $package_id)->pluck('extension_id');
        $extensions = $extensions->whereNotIn('id', $extensions_related_ids);


        $count = $extensions->count();

        if ($paging === 1) {
            $extensions = $extensions->take($limit)->orderBy('status', 'desc')->get();
        } else {
            $extensions = $extensions->skip($limit * ($paging - 1))->orderBy('status', 'desc')->take($limit)->get();
        }

        for ($j = 0; $j < count($extensions); $j++) {
            $extensions[$j]["selected"] = false;
        }

        $data = [
            'data' => $extensions,
            'count' => $count,
            'success' => true,
        ];

        return Response::json($data);
    }

    public function getExtensionsQuote(Request $request)
    {

        $type_class = $request->post('type_class_id');
        $type_service = $request->post('type_service');
        $destination = $request->post('destination');
        $lang = $request->post('lang');
        $filter = $request->post('filter');
        $date_from = $request->input('date');
        $date_from = Carbon::parse($date_from);
        $date_from = $date_from->format('Y-m-d');

        $extensions = Package::with([
            'package_destinations.state.translations' => function ($query) use ($lang) {
                $query->where('type', 'state');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            },
        ])->with([
            'tag.translations' => function ($query) use ($lang) {
                $query->where('type', 'tag');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            },
        ])->with([
            'translations' => function ($query) use ($lang) {
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            },
        ])->with([
            'extension_recommended' => function ($query) use ($lang) {
                $query->select('id', 'package_id', 'extension_id');
            }
        ])->with([
            'plan_rates' => function ($query) use ($date_from, $type_service, $lang, $type_class) {
                $query->where('date_from', '<=', $date_from);
                $query->where('date_to', '>=', $date_from);
                $query->where('service_type_id', $type_service);
                $query->where('status', 1);
                $query->with([
                    'plan_rate_categories' => function ($q) use ($lang, $type_class) {
                        // if($type_class){
                        //    $q->where('type_class_id', $type_class);
                        // }

                        $q->whereHas('sale_rates');
                        $q->with([
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
                        ]);
                    }
                ]);
                $query->whereHas('plan_rate_categories', function ($q) use ($type_class) {
                    // if($type_class){
                    //     $q->where('type_class_id', $type_class);
                    //  }
                    $q->whereHas('sale_rates');
                });
            }
        ]);

        $extensions->whereHas('plan_rates', function ($query) use ($type_service, $type_class) {
            $query->where('status', 1);
            $query->where('service_type_id', $type_service);
            $query->whereHas('plan_rate_categories', function ($q) use ($type_class) {
                if ($type_class) {
                    $q->where('type_class_id', $type_class);
                }
                $q->whereHas('sale_rates');
            });
        });

        $extensions->whereHas('plan_rates', function ($query) use ($date_from) {
            $query->where('status', 1);
            $query->where('date_from', '<=', $date_from);
            $query->where('date_to', '>=', $date_from);
            $query->whereHas('plan_rate_categories.sale_rates');
        });

        if ($filter and $filter != '') {
            $extensions->where(function ($query) use ($filter) {

                $new_q = str_replace('P', '', str_replace('E', '', $filter));
                $query->orWhere('id', 'like', '%' . $new_q . '%');

                $query->orWhere('code', 'like', '%' . $filter . '%');

                $query->orWhereHas('translations', function ($q) use ($filter) {

                    $q->where('name', 'like', '%' . $filter . '%');
                });
            });
        }

        if ($destination) {
            $extensions->where('destinations', 'like', '%' . $destination . '%');
        }

        $extensions = $extensions->where('status', 1)->where('extension', 1)->get();

        return \response()->json($extensions, 200);
    }

    public function changePackagesSelected(Request $request)
    {
        $date = $request->input('date');
        $service_rooms = $request->input('service_rooms');
        $package_service_id = $request->input('package_service_id');
        $hotel_id = $request->input('hotel_id');
        $hotel = Hotel::select('id', 'name', 'stars', 'country_id', 'state_id', 'city_id')
            ->with([
                'country.translations' => function ($query) {
                    $query->where('type', 'country');
                    $query->whereHas('language', function ($q) {
                        $q->where('iso', 'es');
                    });
                }
            ])->with([
                'state.translations' => function ($query) {
                    $query->where('type', 'state');
                    $query->whereHas('language', function ($q) {
                        $q->where('iso', 'es');
                    });
                }
            ])->with([
                'city.translations' => function ($query) {
                    $query->where('type', 'city');
                    $query->whereHas('language', function ($q) {
                        $q->where('iso', 'es');
                    });
                }
            ])->where('id', $hotel_id)->first();
        $on_request = 0;
        if (Cache::has('packages_selected_' . Auth::user()->id)) {
            $packages_selected = Cache::get('packages_selected_' . Auth::user()->id);
            foreach ($packages_selected['package'] as $key => $package) {
                if ($packages_selected['package'][$key] !== null) {
                    //Recorro el itinerario para cambiar por el hotel seleccionado
                    foreach ($packages_selected['package'][$key]['itinerary'] as $keyIti => $itinerary) {
                        foreach ($packages_selected['package'][$key]['itinerary'][$keyIti]['hotel'] as $keyHtl => $hotelItinerary) {
                            $package_service_hotel_id = $packages_selected['package'][$key]['itinerary'][$keyIti]['hotel'][$keyHtl]['package_service_id'];
                            if ($package_service_hotel_id == $package_service_id) {
                                $packages_selected['package'][$key]['itinerary'][$keyIti]['hotel'][$keyHtl]['id'] = $hotel_id;
                                $packages_selected['package'][$key]['itinerary'][$keyIti]['hotel'][$keyHtl]['name'] = $hotel->name;
                                $packages_selected['package'][$key]['itinerary'][$keyIti]['hotel'][$keyHtl]['stars'] = $hotel->stars;
                                $packages_selected['package'][$key]['itinerary'][$keyIti]['hotel'][$keyHtl]['country'] = [
                                    'id' => $hotel->country->id,
                                    'name' => $hotel->country->translations[0]['value']
                                ];
                                $packages_selected['package'][$key]['itinerary'][$keyIti]['hotel'][$keyHtl]['state'] = [
                                    'id' => $hotel->state->id,
                                    'name' => $hotel->state->translations[0]['value']
                                ];
                                $packages_selected['package'][$key]['itinerary'][$keyIti]['hotel'][$keyHtl]['city'] = [
                                    'id' => $hotel->city->id,
                                    'name' => $hotel->city->translations[0]['value']
                                ];
                                //Verifico el inventario
                                $date_in = $packages_selected['package'][$key]['itinerary'][$keyIti]['hotel'][$keyHtl]['date_in'];
                                $date_out = $packages_selected['package'][$key]['itinerary'][$keyIti]['hotel'][$keyHtl]['date_out'];
                                $date_service_in = Carbon::parse($date_in);
                                $date_service_out = Carbon::parse($date_out);
                                $nigths = $date_service_in->diffInDays($date_service_out);
                                $date_out = Carbon::parse($date)->addDay($nigths)->format('Y-m-d');
                                foreach ($service_rooms as $key_room => $room_id) {
                                    $on_request = $this->checkStatusHotel($room_id, $date, $date_out, $nigths);
                                    $packages_selected['package'][$key]['itinerary'][$keyIti]['hotel'][$keyHtl]['on_request'] = $on_request;
                                }
                            }
                        }
                    }

                    //Recorro los servicios para cambiar por el hotel seleccionado
                    foreach ($packages_selected['package'][$key]['services'] as $keyIti => $service) {
                        if ($service['type'] == 'hotel' and ($package_service_id == $service['id'])) {
                            $packages_selected['package'][$key]['services'][$keyIti]['object_id'] = $hotel_id;
                            $packages_selected['package'][$key]['services'][$keyIti]['hotel']['on_request'] = $on_request;
                            $packages_selected['package'][$key]['services'][$keyIti]['hotel']['id'] = $hotel_id;
                            $packages_selected['package'][$key]['services'][$keyIti]['hotel']['name'] = $hotel->name;
                            $packages_selected['package'][$key]['services'][$keyIti]['hotel']['stars'] = $hotel->stars;
                            $packages_selected['package'][$key]['services'][$keyIti]['hotel']['country_id'] = $hotel->country->id;
                            $packages_selected['package'][$key]['services'][$keyIti]['hotel']['state_id'] = $hotel->state->id;
                            $packages_selected['package'][$key]['services'][$keyIti]['hotel']['city_id'] = $hotel->city->id;
                            $packages_selected['package'][$key]['services'][$keyIti]['hotel']['country'] = $hotel->country->toArray();
                            $packages_selected['package'][$key]['services'][$keyIti]['hotel']['state'] = $hotel->state->toArray();
                            $packages_selected['package'][$key]['services'][$keyIti]['hotel']['city'] = $hotel->city->toArray();
                            // agrego los rooms seleccionados
                            $packages_selected['package'][$key]['services'][$keyIti]['service_rooms'] = [];
                            foreach ($service_rooms as $key_room => $room_id) {
                                $packages_selected['package'][$key]['services'][$keyIti]['service_rooms'][] = [
                                    'rate_plan_room_id' => $room_id,
                                    'package_service_id' => $service['id'],
                                ];
                            }
                        }
                    }
                }
            }
            $data = [
                'package' => $packages_selected['package'],
                'params' => $packages_selected['params'],
            ];
            //                        throw new \Exception(json_encode($packages_selected['package']));
            Cache::put('packages_selected_' . Auth::user()->id, $data, 1200);
        }

        return response()->json(["success" => true], 200);
    }

    public function updateAllowModify($id, Request $request)
    {
        $package = Package::find($id);
        if ($request->input("allow_modify")) {
            $package->allow_modify = 0;
        } else {
            $package->allow_modify = 1;
        }
        $package->save();
        return Response::json(['success' => true]);
    }

    public function packagePermissionsList(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('queryCustom');
        $lang = $request->input('lang');
        $filterBy = $request->input('filterBy');

        $filter_exclusive = ($request->input('filter_exclusive') === 'true');
        $filter_generals = ($request->input('filter_generals') === 'true');

        $packages = Package::whereIn('status', [1, 0])
            ->with([
                'permissions.user',
            ])->with([
                'physical_intensity',
            ])->with([
                'translations' => function ($query) use ($lang) {
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                },
            ]);;


        if ($querySearch) {
            $packages = $packages->where(function ($query) use ($querySearch) {

                $new_q = str_replace('P', '', str_replace('E', '', $querySearch));
                $query->orWhere('id', 'like', '%' . $new_q . '%');

                $query->orWhere('code', 'like', '%' . $querySearch . '%');

                $query->orWhereHas('translations', function ($q) use ($querySearch) {

                    $q->where('name', 'like', '%' . $querySearch . '%');
                });
            });
        }


        $count = $packages->count();
        if ($paging === 1) {
            $packages = $packages->take($limit)->orderBy('status', 'desc')->get();
        } else {
            $packages = $packages->skip($limit * ($paging - 1))->orderBy('status', 'desc')->take($limit)->get();
        }


        $data = [
            'data' => $packages,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroyPermission($id)
    {
        $package = PackagePermission::find($id);
        $package->delete();
        return Response::json(['success' => $package->save()]);
    }

    public function storePermission(Request $request)
    {
        // 1. Validación simple y directa
        $validator = Validator::make($request->all(), [
            'users' => 'required|array|min:1',
            'users.*.code' => 'required|integer',
            'packages' => 'required|array|min:1',
            'packages.*.code' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error'   => $validator->errors()->all(),
            ], 422);
        }

        // 2. Tomamos solo los codes y eliminamos duplicados
        $userIds = collect($request->input('users'))
            ->pluck('code')
            ->unique()
            ->values();

        $packageIds = collect($request->input('packages'))
            ->pluck('code')
            ->unique()
            ->values();

        $now = Carbon::now();

        // 3. Transacción con menos trabajo repetido
        DB::transaction(function () use ($userIds, $packageIds, $now) {
            foreach ($userIds as $userId) {
                foreach ($packageIds as $packageId) {
                    DB::table('package_permissions')->updateOrInsert(
                        [
                            'package_id' => $packageId,
                            'user_id'    => $userId,
                        ],
                        [
                            'package_id' => $packageId,
                            'user_id'    => $userId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }
            }
        });

        return response()->json(['success' => true]);
    }

    public function search(Request $request)
    {
        $limit = ($request->has('limit')) ? $request->input('limit') : 20;
        $paging = ($request->has('page')) ? $request->input('page') : 1;

        $querySearch = $request->input('queryCustom');
        $packages = Package::with([
            'translations' => function ($query) {
                $query->select(['id', 'package_id', 'name']);
                $query->where('language_id', 1);
            },
        ]);


        if ($querySearch) {
            $packages = $packages->where(function ($query) use ($querySearch) {
                $new_q = str_replace('P', '', str_replace('E', '', $querySearch));
                $query->orWhere('id', 'like', '%' . $new_q . '%');
                $query->orWhere('code', 'like', '%' . $querySearch . '%');
                $query->orWhereHas('translations', function ($q) use ($querySearch) {
                    $q->where('name', 'like', '%' . $querySearch . '%');
                });
            });
        }

        $count = $packages->get()->count();

        if ($paging === 1) {
            $packages = $packages->take($limit)->orderBy('id', 'desc')
                ->get([
                    'id',
                    'code',
                    'destinations'
                ]);
        } else {
            $packages = $packages->skip($limit * ($paging - 1))->take($limit)->orderBy('id', 'desc')->get([
                'id',
                'code',
                'destinations'
            ]);
        }


        $data = [
            'data' => $packages,
            'count' => $count,
            'success' => true,
        ];

        return Response::json($data);
    }

    public function getServicesEquivalences($id, $package_plan_rate_category_id)
    {

        $services = PackageService::where('package_plan_rate_category_id', $package_plan_rate_category_id)
            ->orderBy('date_in')->orderBy('order')
            ->get();

        $services->transform(function ($query) {
            $query['equivalence'] = "";
            if ($query['type'] === 'service') {
                $service = Service::find($query['object_id']);
                if ($service) {
                    $query['equivalence'] = $service->equivalence_aurora;
                }
            }
            if ($query['type'] === 'hotel') {
                $channel_hotel = ChannelHotel::where('hotel_id', $query['object_id'])->first();
                if ($channel_hotel) {
                    $query['equivalence'] = $channel_hotel->code;
                }
            }

            return $query;
        });

        $data = [
            'data' => $services,
            'success' => true,
        ];

        return Response::json($data);
    }

    public function storeAllHighlights(Request $request)
    {
        $highlight_ids = $request->get('highlights');
        $package_ids = $request->get('packages');
        try {
            DB::beginTransaction();
            foreach ($highlight_ids as $highlight_id) {
                foreach ($package_ids as $package_id) {
                    $find = PackageHighlight::where('package_id', $package_id)->where(
                        'image_highlight_id',
                        $highlight_id
                    )->get();
                    if ($find->count() === 0) {
                        $order = PackageHighlight::where('package_id', $package_id)->orderBy(
                            'order',
                            'desc'
                        )->first(['order']);
                        if ($order) {
                            $order = $order->order + 1;
                        } else {
                            $order = 1;
                        }
                        $save = new PackageHighlight();
                        $save->package_id = $package_id;
                        $save->image_highlight_id = $highlight_id;
                        $save->order = $order;
                        $save->save();
                    }
                }
            }
            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function enabledFixedPrices($id, Request $request)
    {
        $package = PackagePlanRate::find($id);
        $package->enable_fixed_prices = $request->input("enable_fixed_prices");
        $package->save();
        return Response::json(['success' => true]);
    }

    public function getConfigurations($id, Request $request)
    {
        $package = Package::find($id, ['id', 'enable_fixed_prices']);
        return Response::json(['success' => true, 'data' => $package]);
    }

    public function deletePackageRateSaleMarkup($id)
    {
        try {
            $sale = PackageRateSaleMarkup::find($id);
            if ($sale) {
                if ($sale->has('dynamic_sale_rates')) {
                    $sale->dynamic_sale_rates()->delete();
                }
                $sale->delete();
                return Response::json(['success' => true]);
            } else {
                return Response::json(['success' => false]);
            }
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'error' => $exception->getMessage()]);
        }
    }

    public function duplicatePackage($id)
    {
        $duplicatePackageService = new DuplicatePackageService;

        $response = $duplicatePackageService->execute($id);

        return Response::json($response);
    }

    public function duplicatationInfo($id)
    {
        $duplicatationInfo = PackageDuplicationInfo::query()
            ->where('package_id', $id)
            ->first();

        if (!$duplicatationInfo) {
            return ['success' => false, 'error' => 'Duplication info not found'];
        }

        $duplicatationInfo->append('is_processing_plan_rates');

        return ['success' => true, 'data' => $duplicatationInfo];
    }

    public function wordItinerary(Request $request)
    {
        $year = (!empty($request->input('year'))) ? $request->input('year') : date("Y");

        $params = [
            'client_id' => $request->input('client_id'),
            'lang' => strtolower($request->input('lang')),
            'package_ids' => $request->input('package_ids'),
            'year' => $year,
            'days' => $request->input('date_to_days'),
            'category' => $request->input('category'),
            'quantity_persons' => $request->input('quantity_persons'),
            'rooms' => $request->input('rooms'),
            'type_service' => $request->input('type_service'),
            'package' => $request->input('package'),
            'use_header' => $request->input('use_header'),
            'use_prices' => $request->input('use_prices'),
            'user_id' => 0,
            'portada' => $request->input('urlPortadaLogo'), //es una petición que nos traera la URL del itinerario seleccionado
        ];

        $url = $this->generateWordItinerary($params);
        return response()->download($url)->deleteFileAfterSend(true);
    }

    public static function orderMultiDimensionalArray(
        $toOrderArray,
        $field,
        $inverse = false
    ) {
        $position = array();
        $newRow = array();
        foreach ($toOrderArray as $key => $row) {
            $position[$key] = $row[$field];
            $newRow[$key] = $row;
        }
        if ($inverse) {
            arsort($position);
        } else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }

    public static function groupedArray(
        $array,
        $value
    ) {
        $groupedArray = array();
        $paisArray = array();
        foreach ($array as $key => $valuesAry) {
            $pais = $valuesAry[$value];
            if (!in_array($pais, $paisArray)) {
                //si no existe, lo agrego
                $paisArray[] = $pais;
            }
            $paisIndex = array_search($pais, $paisArray);
            $groupedArray[$paisIndex][] = $valuesAry;
        }
        return $groupedArray;
    }

    public static function convertDate(
        $var,
        $delimiter_from,
        $delimiter_to,
        $orientation
    ) {
        $explode = explode($delimiter_from, $var);
        $response = ($orientation)
            ? $explode[2] . $delimiter_to . $explode[1] . $delimiter_to . $explode[0]
            : $explode[0] . $delimiter_to . $explode[1] . $delimiter_to . $explode[2];
        return $response;
    }
}
