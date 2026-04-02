<?php

namespace App\Http\Controllers;

use App\City;
use App\Classification;
use App\Client;
use App\ClientRatePlan;
use App\ClientSeller;
use App\ClientServiceOffer;
use App\Country;
use App\Currency;
use App\ExperienceService;
use App\FixedOutput;
use App\Galery;
use App\Hotel;
use App\HotelClient;
use App\Language;
use App\Market;
use App\Markup;
use App\MarkupHotel;
use App\MarkupRatePlan;
use App\MarkupService;
use App\Package;
use App\PackageChild;
use App\PackageDynamicRate;
use App\PackageDynamicSaleRate;
use App\PackageExtension;
use App\PackageInventory;
use App\PackagePermission;
use App\PackagePlanRate;
use App\PackagePlanRateCategory;
use App\PackageRateSaleMarkup;
use App\PackageSchedule;
use App\PackageService;
use App\PackageServiceRate;
use App\PackageServiceRoom;
use App\PackageTax;
use App\PackageTranslation;
use App\PhysicalIntensity;
use App\RatesPlans;
use App\RatesPlansRooms;
use App\RequirementService;
use App\RestrictionService;
use App\Service;
use App\ServiceCancellationPolicies;
use App\ServiceChild;
use App\ServiceClient;
use App\ServiceClientRatePlan;
use App\ServiceDestination;
use App\ServiceInclusion;
use App\ServiceInventory;
use App\ServiceMarkupRatePlan;
use App\ServiceOperation;
use App\ServiceOperationActivity;
use App\ServiceOrigin;
use App\ServiceRate;
use App\ServiceRatePlan;
use App\ServiceSchedule;
use App\ServiceScheduleDetail;
use App\ServiceSubCategory;
use App\ServiceTax;
use App\ServiceTranslation;
use App\ServiceType;
use App\State;
use App\Tag;
use App\TagService;
use App\Tax;
use App\Http\Traits\Images;
use App\TypeClass;
use App\Unit;
use App\UnitDuration;
use App\User;
use App\Zone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use OwenIt\Auditing\Facades\Auditor;
use OwenIt\Auditing\Models\Audit;

//use OwenIt\Auditing\Contracts\Auditor;
class AuditController extends Controller
{
    use Images;

    protected $fields = [
        'id',
        'user_type',
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
        'tags',
        'created_at',
        'updated_at',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function auditPackage(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $package_id = $request->input('package_id');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $event = $request->input('event');

        $audits = Audit::with([
            'user' => function ($query) {
                $query->with('roles');
            }
        ])->where('tags', 'package');
        if (!empty($date_from) && !empty($date_to)) {
            $audits = $audits->where(function ($query) use ($date_from, $date_to) {
                $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
            });
        }

        if (!empty($event)) {
            $audits = $audits->where('event', $event);
        }

        if (!empty($package_id)) {
            $package = Package::with([
                'translations' => function ($query) {
                    $query->select('id', 'package_id');
                    $query->withTrashed();
                }
            ])->with([
                'children' => function ($query) {
                    $query->select('id', 'package_id');
                    $query->withTrashed();
                }
            ])->with([
                'schedules' => function ($query) {
                    $query->select('id', 'package_id');
                    $query->withTrashed();
                }
            ])->with([
                'plan_rates' => function ($query) {
                    $query->select('id', 'package_id');
                    $query->withTrashed();
                    $query->with([
                        'plan_rate_categories' => function ($query) {
                            $query->select('id', 'package_plan_rate_id');
                            $query->withTrashed();
                            $query->with([
                                'services' => function ($query) {
                                    $query->select('id', 'package_plan_rate_category_id');
                                    $query->withTrashed();
                                    $query->with([
                                        'service_rooms' => function ($query) {
                                            $query->select('id', 'package_service_id');
                                            $query->withTrashed();
                                        }
                                    ]);
                                    $query->with([
                                        'service_rates' => function ($query) {
                                            $query->select('id', 'package_service_id');
                                            $query->withTrashed();
                                        }
                                    ]);
                                }
                            ]);
                            $query->with([
                                'rates' => function ($query) {
                                    $query->select('id', 'package_plan_rate_category_id');
                                    $query->withTrashed();
                                }
                            ]);
                            $query->with([
                                'sale_rates' => function ($query) {
                                    $query->select('id', 'package_plan_rate_category_id');
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ]);
                    $query->with([
                        'inventory' => function ($query) {
                            $query->select('id', 'package_plan_rate_id');
                            $query->withTrashed();
                        }
                    ]);
                    $query->with([
                        'package_rate_sale_markup' => function ($query) {
                            $query->select('id', 'package_plan_rate_id');
                            $query->withTrashed();
                        }
                    ]);
                }
            ])->with([
                'extension_recommended' => function ($query) {
                    $query->select('id', 'package_id', 'extension_id');
                    $query->withTrashed();
                }
            ])->with([
                'fixed_outputs' => function ($query) {
                    $query->select('id', 'package_id');
                    $query->withTrashed();
                }
            ])->with([
                'galleries' => function ($query) {
                    $query->select('id', 'object_id');
                    $query->where('slug', 'package_gallery');
                    $query->where('type', 'package');
                    $query->withTrashed();
                }
            ])->with([
                'tax' => function ($query) {
                    $query->select('id', 'package_id');
                    $query->withTrashed();
                }
            ])->with([
                'permissions' => function ($query) {
                    $query->select('id', 'package_id');
                    $query->withTrashed();
                }
            ])->where('id', $package_id)->withTrashed()->first();

            $package_ids = collect()->add($package_id);
            $translation_ids = $package->translations()->pluck('id');
            $children_ids = $package->children()->pluck('id');
            $schedules_ids = $package->schedules()->pluck('id');
            $plan_rate_plans_ids = $package->plan_rates()->pluck('id');
            $extensions_ids = $package->extension_recommended()->pluck('id');
            $fixed_outputs_ids = $package->fixed_outputs()->pluck('id');
            $galleries_ids = $package->galleries()->pluck('id');
            $tax_ids = $package->tax()->pluck('id');
            $permissions_ids = $package->permissions()->pluck('id');

            $plan_rates_categories_ids = collect();
            $package_services_ids = collect();
            $package_services_rates_ids = collect();
            $package_services_rate_rooms_ids = collect();
            $package_rates_cost_ids = collect();
            $package_rates_sales_ids = collect();
            $package_rates_inventories_ids = collect();
            $package_rates_sale_markups_ids = collect();

            foreach ($package->plan_rates as $plan_rate) {
                foreach ($plan_rate->plan_rate_categories as $plan_rate_category) {
                    $plan_rates_categories_ids->add($plan_rate_category->id);
                    foreach ($plan_rate_category->services as $service) {
                        $package_services_ids->add($service->id);
                        foreach ($service->service_rooms as $service_room) {
                            $package_services_rate_rooms_ids->add($service_room->id);
                        }
                        foreach ($service->service_rates as $service_rate) {
                            $package_services_rates_ids->add($service_rate->id);
                        }
                    }
                    foreach ($plan_rate_category->rates as $rate) {
                        $package_rates_cost_ids->add($rate->id);
                    }
                    foreach ($plan_rate_category->sale_rates as $sale_rate) {
                        $package_rates_sales_ids->add($sale_rate->id);
                    }
                }
                foreach ($plan_rate->inventory as $inventory_value) {
                    $package_rates_inventories_ids->add($inventory_value->id);
                }
                foreach ($plan_rate->package_rate_sale_markup as $markup) {
                    $package_rates_sale_markups_ids->add($markup->id);
                }
            }

            if ($package_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($package_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\Package');
                    $query->whereIn('auditable_id', $package_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($permissions_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($permissions_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\PackagePermission');
                    $query->whereIn('auditable_id', $permissions_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($translation_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($translation_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\PackageTranslation');
                    $query->whereIn('auditable_id', $translation_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($children_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($children_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\PackageChild');
                    $query->whereIn('auditable_id', $children_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($schedules_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($schedules_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\PackageSchedule');
                    $query->whereIn('auditable_id', $schedules_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($plan_rate_plans_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($plan_rate_plans_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\PackagePlanRate');
                    $query->whereIn('auditable_id', $plan_rate_plans_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($extensions_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($extensions_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\PackageExtension');
                    $query->whereIn('auditable_id', $extensions_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($fixed_outputs_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($fixed_outputs_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\FixedOutput');
                    $query->whereIn('auditable_id', $fixed_outputs_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($galleries_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($galleries_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\Galery');
                    $query->whereIn('auditable_id', $galleries_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($tax_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($tax_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\PackageTax');
                    $query->whereIn('auditable_id', $tax_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($plan_rates_categories_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use (
                    $plan_rates_categories_ids,
                    $event,
                    $date_from,
                    $date_to
                ) {
                    $query->where('auditable_type', 'App\PackagePlanRateCategory');
                    $query->whereIn('auditable_id', $plan_rates_categories_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($package_services_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($package_services_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\PackageService');
                    $query->whereIn('auditable_id', $package_services_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($package_services_rates_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use (
                    $package_services_rates_ids,
                    $event,
                    $date_from,
                    $date_to
                ) {
                    $query->where('auditable_type', 'App\PackageServiceRate');
                    $query->whereIn('auditable_id', $package_services_rates_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($package_services_rate_rooms_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use (
                    $package_services_rate_rooms_ids,
                    $event,
                    $date_from,
                    $date_to
                ) {
                    $query->where('auditable_type', 'App\PackageServiceRoom');
                    $query->whereIn('auditable_id', $package_services_rate_rooms_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($package_rates_cost_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use (
                    $package_rates_cost_ids,
                    $event,
                    $date_from,
                    $date_to
                ) {
                    $query->where('auditable_type', 'App\PackageDynamicRate');
                    $query->whereIn('auditable_id', $package_rates_cost_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($package_rates_sales_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use (
                    $package_rates_sales_ids,
                    $event,
                    $date_from,
                    $date_to
                ) {
                    $query->where('auditable_type', 'App\PackageDynamicSaleRate');
                    $query->whereIn('auditable_id', $package_rates_sales_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($package_rates_inventories_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use (
                    $package_rates_inventories_ids,
                    $event,
                    $date_from,
                    $date_to
                ) {
                    $query->where('auditable_type', 'App\PackageInventory');
                    $query->whereIn('auditable_id', $package_rates_inventories_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($package_rates_sale_markups_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use (
                    $package_rates_sale_markups_ids,
                    $event,
                    $date_from,
                    $date_to
                ) {
                    $query->where('auditable_type', 'App\PackageRateSaleMarkup');
                    $query->whereIn('auditable_id', $package_rates_sale_markups_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

            if ($permissions_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($permissions_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\PackagePermission');
                    $query->whereIn('auditable_id', $permissions_ids);
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                });
            }

        } else {
            $audits = $audits->whereIn('auditable_type',
                [
                    'App\FixedOutput',
                    'App\Galery',
                    'App\Package',
                    'App\PackageChild',
                    'App\PackageDynamicRate',
                    'App\PackageDynamicSaleRate',
                    'App\PackageExtension',
                    'App\PackageInventory',
                    'App\PackagePlanRate',
                    'App\PackagePlanRateCategory',
                    'App\PackageRateSaleMarkup',
                    'App\PackageSchedule',
                    'App\PackageService',
                    'App\PackageServiceRate',
                    'App\PackageServiceRoom',
                    'App\PackageTax',
                    'App\PackageTranslation',
                    'App\PackagePermission',
                ]);
        }


        $count = $audits->count();

        if ($paging === 1) {
            $audits = $audits
                ->take($limit)
                ->orderBy('id', 'desc')
                ->get($this->fields
                );
        } else {
            $audits = $audits
                ->skip($limit * ($paging - 1))
                ->take($limit)
                ->orderBy('id', 'desc')
                ->get($this->fields);
        }

        $audits = $audits->transform(function ($item) {
            $package = [];
            $item['user_agent_name'] = getBrowserByUserAgent($item['user_agent']);
            if ($item['auditable_type'] == 'App\Package') {
                $item['module'] = 'Datos generales';
                $package_find = Package::with([
                    'translations' => function ($query) {
                        $query->select(['id', 'package_id', 'language_id', 'name']);
                        $query->where('language_id', 1);
                        $query->withTrashed();
                    }
                ])->where('id', $item['auditable_id'])->withTrashed()->limit(1)->get();

                $package = $package_find->transform(function ($item) {
                    $pack['id'] = $item['id'];
                    $pack['name'] = $item['translations'][0]['name'];
                    return $pack;
                });

            }

            if ($item['auditable_type'] == 'App\PackageTranslation') {
                $package_find = PackageTranslation::where('id', $item['auditable_id'])
                    ->with([
                        'language' => function ($query) {
                            $query->select(['id', 'iso', 'name']);
                            $query->withTrashed();
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar Mascara > Textos > '.$package_find[0]['language']['name'];
                $package = $package_find->transform(function ($item) {
                    $pack['id'] = $item['package_id'];
                    $pack['name'] = $item['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\PackageTax') {
                $item['module'] = 'Administrar Mascara > Configuracíones > Impuestos';
                $package_find = PackageTax::where('id', $item['auditable_id'])
                    ->with([
                        'package' => function ($query) {
                            $query->select(['id', 'code']);
                            $query->withTrashed();
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['id', 'package_id', 'language_id', 'name']);
                                    $query->where('language_id', 1);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $package = $package_find->transform(function ($item) {
                    $pack['id'] = $item['package_id'];
                    $pack['name'] = $item['package']['translations'][0]['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\Galery') {
                $item['module'] = 'Administrar Mascara > Galería';
                $package_find = Galery::where('id', $item['auditable_id'])
                    ->where('slug', 'package_gallery')
                    ->where('type', 'package')
                    ->with([
                        'package' => function ($query) {
                            $query->select(['id', 'code']);
                            $query->withTrashed();
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['id', 'package_id', 'language_id', 'name']);
                                    $query->where('language_id', 1);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $package = $package_find->transform(function ($item) {
                    $pack['id'] = $item['object_id'];
                    $pack['name'] = $item['package']['translations'][0]['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\FixedOutput') {
                $item['module'] = 'Administrar Mascara > Salidas > Fijas';
                $package_find = FixedOutput::where('id', $item['auditable_id'])
                    ->with([
                        'package' => function ($query) {
                            $query->select(['id', 'code']);
                            $query->withTrashed();
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['id', 'package_id', 'language_id', 'name']);
                                    $query->where('language_id', 1);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $package = $package_find->transform(function ($item) {
                    $pack['id'] = $item['package_id'];
                    $pack['name'] = $item['package']['translations'][0]['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\PackageSchedule') {
                $item['module'] = 'Administrar Mascara > Salidas > Rangos';
                $package_find = PackageSchedule::where('id', $item['auditable_id'])
                    ->with([
                        'package' => function ($query) {
                            $query->select(['id', 'code']);
                            $query->withTrashed();
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['id', 'package_id', 'language_id', 'name']);
                                    $query->where('language_id', 1);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $package = $package_find->transform(function ($item) {
                    $pack['id'] = $item['package_id'];
                    $pack['name'] = $item['package']['translations'][0]['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\PackageExtension') {
                $item['module'] = 'Administrar Mascara > Extensiones';
                $package_find = PackageExtension::where('id', $item['auditable_id'])
                    ->with([
                        'package' => function ($query) {
                            $query->select(['id', 'code']);
                            $query->withTrashed();
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['id', 'package_id', 'language_id', 'name']);
                                    $query->where('language_id', 1);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $package = $package_find->transform(function ($item) {
                    $pack['id'] = $item['package_id'];
                    $pack['name'] = $item['package']['translations'][0]['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\PackageChild') {
                $package_find = PackageChild::where('id', $item['auditable_id'])
                    ->with([
                        'package' => function ($query) {
                            $query->select(['id', 'code']);
                            $query->withTrashed();
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['id', 'package_id', 'language_id', 'name']);
                                    $query->where('language_id', 1);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar Mascara > Configuración > Permitir Niños';
                $package = $package_find->transform(function ($item) {
                    $pack['id'] = $item['package_id'];
                    $pack['name'] = $item['package']['translations'][0]['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\PackagePlanRate') {
                $package_find = PackagePlanRate::where('id', $item['auditable_id'])
                    ->with([
                        'package' => function ($query) {
                            $query->select(['id', 'code']);
                            $query->withTrashed();
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['id', 'package_id', 'language_id', 'name']);
                                    $query->where('language_id', 1);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Cotizaciones > Costo > '.$package_find[0]['name'];
                $package = $package_find->transform(function ($item) {
                    $pack['id'] = $item['package_id'];
                    $pack['name'] = $item['package']['translations'][0]['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\PackagePlanRateCategory') {
                $package_find = PackagePlanRateCategory::where('id', $item['auditable_id'])
                    ->with([
                        'category' => function ($query) {
                            $query->withTrashed();
                            $query->with([
                                'translations' => function ($query) {
                                    $query->where('language_id', 1);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])
                    ->with([
                        'plan_rate' => function ($query) {
                            $query->select(['id', 'package_id', 'name', 'service_type_id']);
                            $query->withTrashed();
                            $query->with([
                                'package' => function ($query) {
                                    $query->select(['id', 'code']);
                                    $query->withTrashed();
                                    $query->with([
                                        'translations' => function ($query) {
                                            $query->select(['id', 'package_id', 'language_id', 'name']);
                                            $query->where('language_id', 1);
                                            $query->withTrashed();
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Cotizaciones > Costo > '.$package_find[0]['plan_rate']['name'].' Categorias >'.$package_find[0]['category']['translations'][0]['value'];
                $package = $package_find->transform(function ($item) {
                    $pack['id'] = $item['plan_rate']['package']['id'];
                    $pack['name'] = $item['plan_rate']['package']['translations'][0]['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\PackageService') {
                $package_find = PackageService::where('id', $item['auditable_id'])
                    ->with([
                        'plan_rate_category' => function ($query) {
                            $query->select(['id', 'package_plan_rate_id', 'type_class_id']);
                            $query->withTrashed();
                            $query->with([
                                'category' => function ($query) {
                                    $query->withTrashed();
                                    $query->with([
                                        'translations' => function ($query) {
                                            $query->where('language_id', 1);
                                            $query->withTrashed();
                                        }
                                    ]);
                                }
                            ]);
                            $query->with([
                                'plan_rate' => function ($query) {
                                    $query->select(['id', 'package_id', 'name', 'service_type_id']);
                                    $query->withTrashed();
                                    $query->with([
                                        'package' => function ($query) {
                                            $query->select(['id', 'code']);
                                            $query->withTrashed();
                                            $query->with([
                                                'translations' => function ($query) {
                                                    $query->select(['id', 'package_id', 'language_id', 'name']);
                                                    $query->where('language_id', 1);
                                                    $query->withTrashed();
                                                }
                                            ]);
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                if ($package_find[0]['type'] === 'service') {
                    $item['module'] = 'Cotizaciones > Costo > '.$package_find[0]['plan_rate_category']['plan_rate']['name'].' > '.$package_find[0]['plan_rate_category']['category']['translations'][0]['value'].' > Servicio';
                } else {
                    $item['module'] = 'Cotizaciones > Costo > '.$package_find[0]['plan_rate_category']['plan_rate']['name'].' > '.$package_find[0]['plan_rate_category']['category']['translations'][0]['value'].' > Hotel';
                }

                $package = $package_find->transform(function ($item_service) {
                    $pack['id'] = $item_service['plan_rate_category']['plan_rate']['package']['id'];
                    $pack['name'] = $item_service['plan_rate_category']['plan_rate']['package']['translations'][0]['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\PackageServiceRate') {
                $package_find = PackageServiceRate::where('id', $item['auditable_id'])
                    ->with([
                        'service' => function ($query) {
                            $query->select(['id', 'type', 'package_plan_rate_category_id', 'object_id']);
                            $query->withTrashed();
                            $query->with([
                                'plan_rate_category' => function ($query) {
                                    $query->select(['id', 'package_plan_rate_id', 'type_class_id']);
                                    $query->withTrashed();
                                    $query->with([
                                        'category' => function ($query) {
                                            $query->withTrashed();
                                            $query->with([
                                                'translations' => function ($query) {
                                                    $query->where('language_id', 1);
                                                    $query->withTrashed();
                                                }
                                            ]);
                                        }
                                    ]);
                                    $query->with([
                                        'plan_rate' => function ($query) {
                                            $query->select(['id', 'package_id', 'name', 'service_type_id']);
                                            $query->withTrashed();
                                            $query->with([
                                                'package' => function ($query) {
                                                    $query->select(['id', 'code']);
                                                    $query->withTrashed();
                                                    $query->with([
                                                        'translations' => function ($query) {
                                                            $query->select(['id', 'package_id', 'language_id', 'name']);
                                                            $query->where('language_id', 1);
                                                            $query->withTrashed();
                                                        }
                                                    ]);
                                                }
                                            ]);
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Cotizaciones > Costo > '.$package_find[0]['service']['plan_rate_category']['plan_rate']['name'].' > Servicio > Tarifa';
                $package = $package_find->transform(function ($item_service) {
                    $pack['id'] = $item_service['service']['plan_rate_category']['plan_rate']['package']['id'];
                    $pack['name'] = $item_service['service']['plan_rate_category']['plan_rate']['package']['translations'][0]['name'];
                    return $pack;
                });

            }

            if ($item['auditable_type'] == 'App\PackageServiceRoom') {
                $package_find = PackageServiceRoom::where('id', $item['auditable_id'])
                    ->with([
                        'service' => function ($query) {
                            $query->select(['id', 'type', 'package_plan_rate_category_id', 'object_id']);
                            $query->withTrashed();
                            $query->with([
                                'plan_rate_category' => function ($query) {
                                    $query->select(['id', 'package_plan_rate_id', 'type_class_id']);
                                    $query->withTrashed();
                                    $query->with([
                                        'category' => function ($query) {
                                            $query->withTrashed();
                                            $query->with([
                                                'translations' => function ($query) {
                                                    $query->where('language_id', 1);
                                                    $query->withTrashed();
                                                }
                                            ]);
                                        }
                                    ]);
                                    $query->with([
                                        'plan_rate' => function ($query) {
                                            $query->select(['id', 'package_id', 'name', 'service_type_id']);
                                            $query->withTrashed();
                                            $query->with([
                                                'package' => function ($query) {
                                                    $query->select(['id', 'code']);
                                                    $query->withTrashed();
                                                    $query->with([
                                                        'translations' => function ($query) {
                                                            $query->select(['id', 'package_id', 'language_id', 'name']);
                                                            $query->where('language_id', 1);
                                                            $query->withTrashed();
                                                        }
                                                    ]);
                                                }
                                            ]);
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Cotizaciones > Costo > '.$package_find[0]['service']['plan_rate_category']['plan_rate']['name'].' > Hotel > Tarifa habitación';
                $package = $package_find->transform(function ($item_service) {
                    $pack['id'] = $item_service['service']['plan_rate_category']['plan_rate']['package']['id'];
                    $pack['name'] = $item_service['service']['plan_rate_category']['plan_rate']['package']['translations'][0]['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\PackageDynamicRate') {
                $package_find = PackageDynamicRate::where('id', $item['auditable_id'])
                    ->with([
                        'plan_rate_category' => function ($query) {
                            $query->select(['id', 'package_plan_rate_id', 'type_class_id']);
                            $query->withTrashed();
                            $query->with([
                                'category' => function ($query) {
                                    $query->withTrashed();
                                    $query->with([
                                        'translations' => function ($query) {
                                            $query->where('language_id', 1);
                                            $query->withTrashed();
                                        }
                                    ]);
                                }
                            ]);
                            $query->with([
                                'plan_rate' => function ($query) {
                                    $query->select(['id', 'package_id', 'name', 'service_type_id']);
                                    $query->withTrashed();
                                    $query->with([
                                        'package' => function ($query) {
                                            $query->select(['id', 'code']);
                                            $query->withTrashed();
                                            $query->with([
                                                'translations' => function ($query) {
                                                    $query->select(['id', 'package_id', 'language_id', 'name']);
                                                    $query->where('language_id', 1);
                                                    $query->withTrashed();
                                                }
                                            ]);
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ])
                    ->withTrashed()->limit(1)->get();
                $item['module'] = 'Cotizaciones > Costo > '.$package_find[0]['plan_rate_category']['plan_rate']['name'].' > '.$package_find[0]['plan_rate_category']['category']['translations'][0]['value'].' > Tarifa';
                $package = $package_find->transform(function ($item_service) {
                    $pack['id'] = $item_service['plan_rate_category']['plan_rate']['package']['id'];
                    $pack['name'] = $item_service['plan_rate_category']['plan_rate']['package']['translations'][0]['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\PackageInventory') {
                $package_find = PackageInventory::where('id', $item['auditable_id'])
                    ->with([
                        'plan_rate' => function ($query) {
                            $query->select(['id', 'package_id', 'name', 'service_type_id']);
                            $query->withTrashed();
                            $query->with([
                                'package' => function ($query) {
                                    $query->select(['id', 'code']);
                                    $query->withTrashed();
                                    $query->with([
                                        'translations' => function ($query) {
                                            $query->select(['id', 'package_id', 'language_id', 'name']);
                                            $query->where('language_id', 1);
                                            $query->withTrashed();
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Cotizaciones > '.$package_find[0]['plan_rate']['name'].' > Inventario';
                $package = $package_find->transform(function ($item_service) {
                    $pack['id'] = $item_service['plan_rate']['package']['id'];
                    $pack['name'] = $item_service['plan_rate']['package']['translations'][0]['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\PackageRateSaleMarkup') {
                $package_find = PackageRateSaleMarkup::where('id', $item['auditable_id'])
                    ->with([
                        'plan_rate' => function ($query) {
                            $query->select(['id', 'package_id', 'name', 'service_type_id']);
                            $query->withTrashed();
                            $query->with([
                                'package' => function ($query) {
                                    $query->select(['id', 'code']);
                                    $query->withTrashed();
                                    $query->with([
                                        'translations' => function ($query) {
                                            $query->select(['id', 'package_id', 'language_id', 'name']);
                                            $query->where('language_id', 1);
                                            $query->withTrashed();
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();

                if ($package_find[0]['seller_type'] == 'App\Market') {
                    $type = 'Mercado';
                } else {
                    $type = 'Cliente';
                }
                $item['module'] = 'Cotizaciones > Venta > '.$package_find[0]['plan_rate']['name'].' > Tarifa > Markup > '.$type;
                $package = $package_find->transform(function ($item_service) {
                    $pack['id'] = $item_service['plan_rate']['package']['id'];
                    $pack['name'] = $item_service['plan_rate']['package']['translations'][0]['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\PackageDynamicSaleRate') {
                $package_find = PackageDynamicSaleRate::where('id', $item['auditable_id'])
                    ->with([
                        'plan_rate_category' => function ($query) {
                            $query->select(['id', 'package_plan_rate_id', 'type_class_id']);
                            $query->withTrashed();
                            $query->with([
                                'category' => function ($query) {
                                    $query->withTrashed();
                                    $query->with([
                                        'translations' => function ($query) {
                                            $query->where('language_id', 1);
                                            $query->withTrashed();
                                        }
                                    ]);
                                }
                            ]);
                            $query->with([
                                'plan_rate' => function ($query) {
                                    $query->select(['id', 'package_id', 'name', 'service_type_id']);
                                    $query->withTrashed();
                                    $query->with([
                                        'package' => function ($query) {
                                            $query->select(['id', 'code']);
                                            $query->withTrashed();
                                            $query->with([
                                                'translations' => function ($query) {
                                                    $query->select(['id', 'package_id', 'language_id', 'name']);
                                                    $query->where('language_id', 1);
                                                    $query->withTrashed();
                                                }
                                            ]);
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Cotizaciones > Venta > '.$package_find[0]['plan_rate_category']['plan_rate']['name'].' > '.$package_find[0]['plan_rate_category']['category']['translations'][0]['value'].' > Tarifa';
                $package = $package_find->transform(function ($item_service) {
                    $pack['id'] = $item_service['plan_rate_category']['plan_rate']['package']['id'];
                    $pack['name'] = $item_service['plan_rate_category']['plan_rate']['package']['translations'][0]['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\PackagePermission') {
                $package_find = PackagePermission::where('id', $item['auditable_id'])
                    ->with([
                        'user' => function ($query) {
                            $query->select(['id', 'code', 'name']);
                            $query->withTrashed();
                        }
                    ])->with([
                        'package' => function ($query) {
                            $query->select(['id', 'code']);
                            $query->withTrashed();
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['id', 'package_id', 'language_id', 'name']);
                                    $query->where('language_id', 1);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Paquetes > Permisos >'.$package_find[0]['user']['code'].' - '.$package_find[0]['user']['name'];
                $package = $package_find->transform(function ($item) {
                    $pack['id'] = $item['package_id'];
                    $pack['name'] = $item['package']['translations'][0]['name'];
                    return $pack;
                });
            }

            $item['old_values'] = $this->getValuesTablesForeing($item['old_values'], $item['auditable_id'],
                $item['auditable_type']);
            $item['new_values'] = $this->getValuesTablesForeing($item['new_values'], $item['auditable_id'],
                $item['auditable_type']);
            $item['package'] = $package;

            return $item;
        });

        $data = [
            'data' => $audits,
            'count' => $count,
            'success' => true
        ];


        return Response::json($data);


    }


    /**
     * auditRestore a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function auditRestore(Request $request, Auditor $auditor)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);

            if ($validator->fails()) {
                return Response::json(['success' => false, 'message' => (string)$validator->getMessageBag()]);
            } else {
                DB::beginTransaction();
                $data = Audit::find($request->input('id'));
                $model_name = $data->auditable_type;
                if ($data->event === 'updated') {
                    $model_find = $model_name::withTrashed()->find($data->auditable_id);
                    $model_find->setAuditEvent('updated');
                    $model_find->update($data->old_values);
                }

                if ($data->event === 'deleted') {
                    $model_find = $model_name::onlyTrashed()->find($data->auditable_id);
                    $model_find->setAuditEvent('restored');
                    $model_find->restore();
                }

                if ($data->event === 'created') {
                    $model_find = $model_name::withTrashed()->find($data->auditable_id);
                    $model_find->setAuditEvent('deleted');
                    $model_find->delete();
                }

                DB::commit();
                return Response::json(['success' => true, 'message' => 'Se restauró correctamente']);
            }
        } catch (AuditableTransitionException $e) {
            DB::rollback();
            return Response::json([
                'success' => false,
                'message' => 'Incompatible attributes: %s',
                implode(', ', $e->getIncompatibilities())
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    public function auditClient(Request $request)
    {
        $paging = $request->input('page') ? (int)$request->input('page') : 1;
        $limit = $request->input('limit');
        $client_id = $request->input('client_id');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $event = $request->input('event');

        $audits = Audit::with([
            'user' => function ($query) {
                $query->with('roles');
            }
        ])->where('tags', 'client');
        if (!empty($date_from) && !empty($date_to)) {
            $audits = $audits->where(function ($query) use ($date_from, $date_to) {
                $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
            });
        }

        if (!empty($event)) {
            $audits = $audits->where('event', $event);
        }

        if (!empty($client_id)) {
            $client = Client::with([
                'markups' => function ($query) {
                    $query->select('id', 'client_id');
                    $query->withTrashed();
                }
            ])->with([
                'sellers' => function ($query) {
                    $query->select('id', 'client_id', 'user_id');
                    $query->withTrashed();
                }
            ])->with([
                'galeries' => function ($query) {
                    $query->select('id', 'object_id');
                    $query->where('slug', 'client_logo');
                    $query->where('type', 'client');
                    $query->withTrashed();
                }
            ])->with([
                'markup_hotel' => function ($query) {
                    $query->select('id', 'client_id', 'hotel_id');
                    $query->withTrashed();
                }
            ])->with([
                'markup_service' => function ($query) {
                    $query->select('id', 'client_id', 'service_id');
                    $query->withTrashed();
                }
            ])->with([
                'markup_rate_hotel' => function ($query) {
                    $query->select('id', 'client_id', 'rate_plan_id');
                    $query->withTrashed();
                }
            ])->with([
                'markup_rate_service' => function ($query) {
                    $query->select('id', 'client_id', 'service_rate_id');
                    $query->withTrashed();
                }
            ])->with([
                'fromService' => function ($query) {
                    $query->select('id', 'client_id');
                    $query->withTrashed();
                }
            ])->with([
                'fromHotel' => function ($query) {
                    $query->select('id', 'client_id');
                    $query->withTrashed();
                }
            ])->with([
                'service_client_rate_plans' => function ($query) {
                    $query->select('id', 'client_id');
                    $query->withTrashed();
                }
            ])->with([
                'hotel_client_rate_plans' => function ($query) {
                    $query->select('id', 'client_id');
                    $query->withTrashed();
                }
            ])->with([
                'service_offer' => function ($query) {
                    $query->select('id', 'client_id');
                    $query->withTrashed();
                }
            ])->where('id', $client_id)->withTrashed()->first();
            $client_ids = collect()->add($client_id);
            $markups_ids = $client->markups()->pluck('id');
            $sellers_ids = $client->sellers()->pluck('id');
            $galleries_ids = $client->galeries()->pluck('id');
            $markup_hotel_ids = $client->markup_hotel()->pluck('id');
            $markup_service_ids = $client->markup_service()->pluck('id');
            $markup_rate_hotel_ids = $client->markup_rate_hotel()->pluck('id');
            $markup_rate_service_ids = $client->markup_rate_service()->pluck('id');
            $service_client_ids = $client->fromService()->pluck('id');
            $hotel_client_ids = $client->fromHotel()->pluck('id');
            $service_rate_plan_ids = $client->service_client_rate_plans()->pluck('id');
            $hotel_rate_plan_ids = $client->hotel_client_rate_plans()->pluck('id');
            $service_offer_ids = $client->service_offer()->pluck('id');

            if ($client_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($client_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\Client');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $client_ids);
                });
            }

            if ($markups_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($markups_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\Markup');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $markups_ids);
                });
            }

            if ($sellers_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($sellers_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ClientSeller');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $sellers_ids);
                });
            }

            if ($galleries_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($galleries_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\Galery');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $galleries_ids);
                });
            }

            if ($markup_hotel_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($markup_hotel_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\MarkupHotel');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $markup_hotel_ids);
                });
            }

            if ($markup_service_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($markup_service_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\MarkupService');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $markup_service_ids);
                });
            }

            if ($markup_rate_hotel_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use (
                    $markup_rate_hotel_ids,
                    $event,
                    $date_from,
                    $date_to
                ) {
                    $query->where('auditable_type', 'App\MarkupRatePlan');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $markup_rate_hotel_ids);
                });
            }

            if ($markup_rate_service_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use (
                    $markup_rate_service_ids,
                    $event,
                    $date_from,
                    $date_to
                ) {
                    $query->where('auditable_type', 'App\ServiceMarkupRatePlan');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $markup_rate_service_ids);
                });
            }

            if ($service_client_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_client_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ServiceClient');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_client_ids);
                });
            }

            if ($hotel_client_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($hotel_client_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\HotelClient');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $hotel_client_ids);
                });
            }

            if ($service_rate_plan_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use (
                    $service_rate_plan_ids,
                    $event,
                    $date_from,
                    $date_to
                ) {
                    $query->where('auditable_type', 'App\ServiceClientRatePlan');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_rate_plan_ids);
                });
            }

            if ($hotel_rate_plan_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($hotel_rate_plan_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ClientRatePlan');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $hotel_rate_plan_ids);
                });
            }

            if ($service_offer_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_offer_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ClientServiceOffer');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_offer_ids);
                });
            }

        } else {
            $audits = $audits->whereIn('auditable_type',
                [
                    'App\Client',
                    'App\Galery',
                    'App\Markup',
                    'App\ClientSeller',
                    'App\MarkupHotel',
                    'App\MarkupService',
                    'App\MarkupRatePlan',
                    'App\ServiceMarkupRatePlan',
                    'App\ServiceClient',
                    'App\HotelClient',
                    'App\ServiceClientRatePlan',
                    'App\ClientRatePlan',
                    'App\ClientServiceOffer',
                ]
            );
        }

        $count = $audits->count();

        if ($paging === 1) {
            $audits = $audits
                ->take($limit)
                ->orderBy('id', 'desc')
                ->get($this->fields);

        } else {
            $audits = $audits
                ->skip($limit * ($paging - 1))
                ->take($limit)
                ->orderBy('id', 'desc')
                ->get($this->fields);
        }


        $audits = $audits->transform(function ($item) {
            $client = [];
            $item['user_agent_name'] = getBrowserByUserAgent($item['user_agent']);
            if ($item['auditable_type'] == 'App\Client') {
                $item['module'] = 'Datos generales';
                $client_find = Service::where('id', $item['auditable_id'])->withTrashed()->limit(1)->get();
                $client = $client_find->transform(function ($item) {
                    $pack['id'] = $item['id'];
                    $pack['name'] = $item['code'].' - '.$item['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\Galery') {
                $item['module'] = 'Datos generales > Logo';
                $client_find = Galery::where('id', $item['auditable_id'])
                    ->where('slug', 'client_logo')
                    ->where('type', 'client')
                    ->with([
                        'client' => function ($query) {
                            $query->select(['id', 'code', 'name']);
                            $query->withTrashed();
                        }
                    ])->withTrashed()->limit(1)->get();
                $client = $client_find->transform(function ($item) {
                    $pack['id'] = $item['object_id'];
                    $pack['name'] = $item['client']['code'].' - '.$item['client']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\Markup') {
                $client_find = Markup::where('id', $item['auditable_id'])
                    ->with([
                        'client' => function ($query) {
                            $query->select(['id', 'code', 'name']);
                            $query->withTrashed();
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar cliente > Markup > '.$client_find[0]['period'];
                $client = $client_find->transform(function ($item) {
                    $pack['id'] = $item['client']['id'];
                    $pack['name'] = $item['client']['code'].' - '.$item['client']['name'];
                    return $pack;
                });

            }

            if ($item['auditable_type'] == 'App\ClientSeller') {
                $client_find = ClientSeller::where('id', $item['auditable_id'])
                    ->with([
                        'user' => function ($query) {
                            $query->select(['id', 'code', 'name']);
                            $query->withTrashed();
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar cliente > Seller > '.$client_find[0]['user']['name'];
                $client = $client_find->transform(function ($item) {
                    $pack['id'] = $item['client']['id'];
                    $pack['name'] = $item['client']['code'].' - '.$item['client']['name'];
                    return $pack;
                });

            }

            if ($item['auditable_type'] == 'App\MarkupService') {
                $client_find = MarkupService::where('id', $item['auditable_id'])
                    ->with([
                        'client' => function ($query) {
                            $query->select(['id', 'code', 'name']);
                            $query->withTrashed();
                        }
                    ])->with([
                        'service' => function ($query) {
                            $query->select(['id', 'aurora_code', 'name']);
                            $query->withTrashed();
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar cliente > Servicio > '.$client_find[0]['service']['aurora_code'].' - '.$client_find[0]['service']['name'].' > Markup personalizado';
                $client = $client_find->transform(function ($item) {
                    $pack['id'] = $item['client']['id'];
                    $pack['name'] = $item['client']['code'].' - '.$item['client']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\MarkupHotel') {
                $client_find = MarkupHotel::where('id', $item['auditable_id'])
                    ->with([
                        'client' => function ($query) {
                            $query->select(['id', 'code', 'name']);
                            $query->withTrashed();
                        }
                    ])->with([
                        'hotel' => function ($query) {
                            $query->select(['id', 'name']);
                            $query->withTrashed();
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar cliente > Hotel > '.$client_find[0]['hotel']['name'].' > Markup personalizado';
                $client = $client_find->transform(function ($item) {
                    $pack['id'] = $item['client']['id'];
                    $pack['name'] = $item['client']['code'].' - '.$item['client']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\MarkupRatePlan') {
                $client_find = MarkupRatePlan::where('id', $item['auditable_id'])
                    ->with([
                        'client' => function ($query) {
                            $query->select(['id', 'code', 'name']);
                            $query->withTrashed();
                        }
                    ])->with([
                        'hotel_rate_plans' => function ($query) {
                            $query->select(['id', 'name', 'hotel_id']);
                            $query->withTrashed();
                            $query->with([
                                'hotel' => function ($query) {
                                    $query->select(['id', 'name']);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar cliente > Hotel > '.$client_find[0]['hotel_rate_plans']['hotel']['name'].' > Markup personalizado > Tarifa';
                $client = $client_find->transform(function ($item) {
                    $pack['id'] = $item['client']['id'];
                    $pack['name'] = $item['client']['code'].' - '.$item['client']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceMarkupRatePlan') {
                $client_find = ServiceMarkupRatePlan::where('id', $item['auditable_id'])
                    ->with([
                        'client' => function ($query) {
                            $query->select(['id', 'code', 'name']);
                            $query->withTrashed();
                        }
                    ])->with([
                        'service_rate' => function ($query) {
                            $query->select(['id', 'service_id']);
                            $query->withTrashed();
                            $query->with([
                                'service' => function ($query) {
                                    $query->select(['id', 'aurora_code', 'name']);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar cliente > Servicio > '.$client_find[0]['service_rate']['service']['aurora_code'].' - '.$client_find[0]['service_rate']['service']['name'].' > Markup personalizado > Tarifa';
                $client = $client_find->transform(function ($item) {
                    $pack['id'] = $item['client']['id'];
                    $pack['name'] = $item['client']['code'].' - '.$item['client']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceClient') {
                $client_find = ServiceClient::where('id', $item['auditable_id'])
                    ->with([
                        'client' => function ($query) {
                            $query->select(['id', 'code', 'name']);
                            $query->withTrashed();
                        }
                    ])->with([
                        'service' => function ($query) {
                            $query->select(['id', 'name', 'aurora_code']);
                            $query->withTrashed();
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar cliente > Servicio > '.$client_find[0]['service']['aurora_code'].' - '.$client_find[0]['service']['name'].' > Bloqueo';
                $client = $client_find->transform(function ($item) {
                    $pack['id'] = $item['client']['id'];
                    $pack['name'] = $item['client']['code'].' - '.$item['client']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\HotelClient') {
                $client_find = HotelClient::where('id', $item['auditable_id'])
                    ->with([
                        'client' => function ($query) {
                            $query->select(['id', 'code', 'name']);
                            $query->withTrashed();
                        }
                    ])->with([
                        'hotel' => function ($query) {
                            $query->select(['id', 'name']);
                            $query->withTrashed();
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar cliente > Hotel > '.$client_find[0]['hotel']['name'].' > Bloqueo';
                $client = $client_find->transform(function ($item) {
                    $pack['id'] = $item['client']['id'];
                    $pack['name'] = $item['client']['code'].' - '.$item['client']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceClientRatePlan') {
                $client_find = ServiceClientRatePlan::where('id', $item['auditable_id'])
                    ->with([
                        'client' => function ($query) {
                            $query->select(['id', 'code', 'name']);
                            $query->withTrashed();
                        }
                    ])->with([
                        'service_rate' => function ($query) {
                            $query->select(['id', 'name', 'service_id']);
                            $query->withTrashed();
                            $query->with([
                                'service' => function ($query) {
                                    $query->select(['id', 'name', 'aurora_code']);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar cliente > Servicio > '.$client_find[0]['service_rate']['service']['aurora_code'].' - '.$client_find[0]['service_rate']['service']['name'].' > Tarifa > '.$client_find[0]['service_rate']['name'].' > Bloqueo';
                $client = $client_find->transform(function ($item) {
                    $pack['id'] = $item['client']['id'];
                    $pack['name'] = $item['client']['code'].' - '.$item['client']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ClientRatePlan') {
                $client_find = ClientRatePlan::where('id', $item['auditable_id'])
                    ->with([
                        'client' => function ($query) {
                            $query->select(['id', 'code', 'name']);
                            $query->withTrashed();
                        }
                    ])->with([
                        'rate_plan' => function ($query) {
                            $query->select(['id', 'name', 'hotel_id']);
                            $query->withTrashed();
                            $query->with([
                                'hotel' => function ($query) {
                                    $query->select(['id', 'name']);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar cliente > Hotel > '.$client_find[0]['rate_plan']['hotel']['name'].' > Tarifa > '.$client_find[0]['rate_plan']['name'].' > Bloqueo';
                $client = $client_find->transform(function ($item) {
                    $pack['id'] = $item['client']['id'];
                    $pack['name'] = $item['client']['code'].' - '.$item['client']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ClientServiceOffer') {
                $client_find = ClientServiceOffer::where('id', $item['auditable_id'])
                    ->with([
                        'client' => function ($query) {
                            $query->select(['id', 'code', 'name']);
                            $query->withTrashed();
                        }
                    ])->with([
                        'service_rate' => function ($query) {
                            $query->select(['id', 'name', 'service_id']);
                            $query->withTrashed();
                            $query->with([
                                'service' => function ($query) {
                                    $query->select(['id', 'name', 'aurora_code']);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar cliente > Servicio ofertas > '.$client_find[0]['service_rate']['service']['aurora_code'].' - '.$client_find[0]['service_rate']['service']['name'].' > Tarifa > '.$client_find[0]['service_rate']['name'].' > Oferta';
                $client = $client_find->transform(function ($item) {
                    $pack['id'] = $item['client']['id'];
                    $pack['name'] = $item['client']['code'].' - '.$item['client']['name'];
                    return $pack;
                });
            }


            $item['old_values'] = $this->getValuesTablesForeing($item['old_values'], $item['auditable_id'],
                $item['auditable_type']);
            $item['new_values'] = $this->getValuesTablesForeing($item['new_values'], $item['auditable_id'],
                $item['auditable_type']);
            $item['client'] = $client;

            return $item;

        });

        $data = [
            'data' => $audits,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function auditService(Request $request)
    {
        $paging = $request->input('page') ? (int)$request->input('page') : 1;
        $limit = $request->input('limit');
        $service_id = $request->input('service_id');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $event = $request->input('event');

        $audits = Audit::with([
            'user' => function ($query) {
                $query->with('roles');
            }
        ])->where('tags', 'service');
        if (!empty($date_from) && !empty($date_to)) {
            $audits = $audits->where(function ($query) use ($date_from, $date_to) {
                $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
            });
        }

        if (!empty($event)) {
            $audits = $audits->where('event', $event);
        }

        if (!empty($service_id)) {
            $service = Service::with([
                'service_translations' => function ($query) {
                    $query->select('id', 'service_id');
                    $query->withTrashed();
                }
            ])->with([
                'serviceOrigin' => function ($query) {
                    $query->select('id', 'service_id');
                    $query->withTrashed();
                }
            ])->with([
                'serviceDestination' => function ($query) {
                    $query->select('id', 'service_id');
                    $query->withTrashed();
                }
            ])->with([
                'requirement' => function ($query) {
                    $query->withTrashed();
                }
            ])->with([
                'restriction' => function ($query) {
                    $query->withTrashed();
                }
            ])->with([
                'experience' => function ($query) {
                    $query->withTrashed();
                }
            ])->with([
                'schedules' => function ($query) {
                    $query->select('id', 'service_id');
                    $query->withTrashed();
                }
            ])->with([
                'schedules.servicesScheduleDetail' => function ($query) {
                    $query->select('id', 'service_schedule_id');
                    $query->withTrashed();
                }
            ])->with([
                'operability' => function ($query) {
                    $query->select('id', 'service_id');
                    $query->withTrashed();
                }
            ])->with([
                'operability.services_operation_activities' => function ($query) {
                    $query->select('id', 'service_operation_id');
                    $query->withTrashed();
                }
            ])->with([
                'galleries' => function ($query) {
                    $query->select('id', 'object_id');
                    $query->where('slug', 'service_gallery');
                    $query->where('type', 'type');
                    $query->withTrashed();
                }
            ])->with([
                'tax' => function ($query) {
                    $query->select('id', 'service_id');
                    $query->withTrashed();
                }
            ])->with([
                'children_ages' => function ($query) {
                    $query->select('id', 'service_id');
                    $query->withTrashed();
                }
            ])->with([
                'inclusions' => function ($query) {
                    $query->select('id', 'service_id');
                    $query->withTrashed();
                }
            ])->with([
                'service_rate' => function ($query) {
                    $query->select('id', 'service_id');
                    $query->withTrashed();
                }
            ])->with([
                'service_rate.service_rate_plans' => function ($query) {
                    $query->select('id', 'service_rate_id');
                    $query->withTrashed();
                }
            ])->with([
                'service_rate.inventory' => function ($query) {
                    $query->select('id', 'service_rate_id');
                    $query->withTrashed();
                }
            ])->with([
                'service_component' => function ($query) {
                    $query->select('id', 'service_id');
                    $query->withTrashed();
                }
            ])->with([
                'service_component.components' => function ($query) {
                    $query->select('id', 'service_component_id');
                    $query->withTrashed();
                }
            ])->with([
                'serviceEquivAssociation' => function ($query) {
                    $query->select('id', 'service_id');
                    $query->withTrashed();
                }
            ])->where('id', $service_id)->withTrashed()->first();
            $service_ids = collect()->add($service_id);
            $service_translations_ids = $service->service_translations()->pluck('id');
            $service_origin_ids = $service->serviceOrigin()->pluck('id');
            $service_destination_ids = $service->serviceDestination()->pluck('id');
            $service_restriction_ids = collect();
            $service_experience_ids = collect();
            $service_requirement_ids = collect();
            $service_schedules_details_ids = collect();
            $service_operation_activities_ids = collect();
            $service_rate_plans_ids = collect();
            $service_rate_inventory_ids = collect();
            $service_components_component_ids = collect();
            foreach ($service->requirement as $requirement) {
                $service_requirement_ids->add($requirement->id);
            }
            foreach ($service->restriction as $restriction) {
                $service_restriction_ids->add($restriction->id);
            }
            foreach ($service->experience as $experience) {
                $service_experience_ids->add($experience->id);
            }
            $service_schedules_ids = $service->schedules()->pluck('id');
            foreach ($service->schedules as $schedule) {
                foreach ($schedule->servicesScheduleDetail as $schedule_detail) {
                    $service_schedules_details_ids->add($schedule_detail->id);
                }
            }
            $service_operability_ids =  $service->operability()->pluck('id');
            foreach ($service->operability as $operability) {
                foreach ($operability->services_operation_activities as $operability_detail) {
                    $service_operation_activities_ids->add($operability_detail->id);
                }
            }
            $service_galleries_ids = $service->galleries()->pluck('id');
            $service_taxes_ids = $service->tax()->pluck('id');
            $service_children_ages_ids = $service->children_ages()->pluck('id');
            $service_inclusions_ids = $service->inclusions()->pluck('id');
            $service_rates_ids = $service->service_rate()->pluck('id');
            foreach ($service->service_rate as $service_rate) {
                foreach ($service_rate->service_rate_plans as $service_rate_plans) {
                    $service_rate_plans_ids->add($service_rate_plans->id);
                }
            }

            foreach ($service->service_rate as $service_rate) {
                foreach ($service_rate->inventory as $inventory) {
                    $service_rate_inventory_ids->add($inventory->id);
                }
            }
            $service_component_ids = $service->service_component()->pluck('id');
            foreach ($service->service_component as $service_component) {
                foreach ($service_component->components as $components) {
                    $service_components_component_ids->add($components->id);
                }
            }

            $service_equiv_association_ids = $service->serviceEquivAssociation()->pluck('id');

            if ($service_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\Service');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_ids);
                });
            }

            if ($service_translations_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_translations_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ServiceTranslation');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_translations_ids);
                });
            }

            if ($service_origin_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_origin_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ServiceOrigin');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_origin_ids);
                });
            }

            if ($service_destination_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_destination_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ServiceDestination');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_destination_ids);
                });
            }

            if ($service_requirement_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_requirement_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\RequirementService');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_requirement_ids);
                });
            }

            if ($service_restriction_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_restriction_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\RestrictionService');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_restriction_ids);
                });
            }

            if ($service_experience_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_experience_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ExperienceService');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_experience_ids);
                });
            }

            if ($service_galleries_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_galleries_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\Galery');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_galleries_ids);
                });
            }

            if ($service_schedules_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_schedules_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ServiceSchedule');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_schedules_ids);
                });
            }

            if ($service_schedules_details_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_schedules_details_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ServiceScheduleDetail');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_schedules_details_ids);
                });
            }

            if ($service_operability_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use (
                    $service_operability_ids,
                    $event,
                    $date_from,
                    $date_to
                ) {
                    $query->where('auditable_type', 'App\ServiceOperation');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_operability_ids);
                });
            }

            if ($service_operation_activities_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use (
                    $service_operation_activities_ids,
                    $event,
                    $date_from,
                    $date_to
                ) {
                    $query->where('auditable_type', 'App\ServiceOperationActivity');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_operation_activities_ids);
                });
            }

            if ($service_taxes_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_taxes_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ServiceTax');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_taxes_ids);
                });
            }

            if ($service_children_ages_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_children_ages_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ServiceChild');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_children_ages_ids);
                });
            }

            if ($service_inclusions_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use (
                    $service_inclusions_ids,
                    $event,
                    $date_from,
                    $date_to
                ) {
                    $query->where('auditable_type', 'App\ServiceInclusion');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_inclusions_ids);
                });
            }

            if ($service_rates_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_rates_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ServiceRate');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_rates_ids);
                });
            }

            if ($service_rate_plans_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_rate_plans_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ServiceRatePlan');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_rate_plans_ids);
                });
            }

            if ($service_rate_inventory_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_rate_inventory_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ServiceInventory');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_rate_inventory_ids);
                });
            }

            if ($service_component_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_component_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ServiceComponent');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_component_ids);
                });
            }

            if ($service_components_component_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_components_component_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ServiceComponent');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_components_component_ids);
                });
            }

            if ($service_components_component_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_components_component_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\Component');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_components_component_ids);
                });
            }

            if ($service_equiv_association_ids->count() > 0) {
                $audits = $audits->orWhere(function ($query) use ($service_equiv_association_ids, $event, $date_from, $date_to) {
                    $query->where('auditable_type', 'App\ServiceEquivalenceAssociation');
                    if (!empty($event)) {
                        $query->where('event', $event);
                    }
                    if (!empty($date_from) && !empty($date_to)) {
                        $query->whereDate('created_at', '>=', Carbon::parse($date_from)->format('Y-m-d'));
                        $query->whereDate('created_at', '<=', Carbon::parse($date_to)->format('Y-m-d'));
                    }
                    $query->whereIn('auditable_id', $service_equiv_association_ids);
                });
            }

        } else {
            $audits = $audits->whereIn('auditable_type',
                [
                    'App\Service',
                    'App\ServiceOrigin',
                    'App\ServiceDestination',
                    'App\ServiceTranslation',
                    'App\RequirementService',
                    'App\RestrictionService',
                    'App\ExperienceService',
                    'App\ServiceSchedule',
                    'App\ServiceScheduleDetail',
                    'App\ServiceOperation',
                    'App\ServiceOperationActivity',
                    'App\Galery',
                    'App\ServiceTax',
                    'App\ServiceChild',
                    'App\ServiceInclusion',
                    'App\ServiceRate',
                    'App\ServiceRatePlan',
                    'App\ServiceInventory',
//                    'App\ServiceComponent',
//                    'App\Component',
//                    'App\ServiceEquivalenceAssociation'
                ]
            );
        }

        $count = $audits->count();

        if ($paging === 1) {
            $audits = $audits
                ->take($limit)
                ->orderBy('id', 'desc')
                ->get($this->fields);

        } else {
            $audits = $audits
                ->skip($limit * ($paging - 1))
                ->take($limit)
                ->orderBy('id', 'desc')
                ->get($this->fields);
        }


        $audits = $audits->transform(function ($item) {
            $service = [];
            $item['user_agent_name'] = getBrowserByUserAgent($item['user_agent']);
            if ($item['auditable_type'] == 'App\Service') {
                $item['module'] = 'Datos generales';
                $service_find = Service::where('id', $item['auditable_id'])->withTrashed()->limit(1)->get();
                $service = $service_find->transform(function ($item) {
                    $pack['id'] = $item['id'];
                    $pack['name'] = $item['aurora_code'].' - '.$item['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceOrigin') {
                $data = ServiceOrigin::where('id', $item['auditable_id'])
                    ->with([
                        'service' => function ($query) {
                            $query->select(['id','name','aurora_code']);
                            $query->withTrashed();
                        },
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Datos generales > Origen';
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['service']['id'];
                    $pack['name'] = $item['service']['aurora_code'].' - '.$item['service']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceTranslation') {
                $data = ServiceTranslation::where('id', $item['auditable_id'])
                    ->with([
                        'services' => function ($query) {
                            $query->select(['id','name','aurora_code']);
                            $query->withTrashed();
                        },
                    ])->with([
                        'language' => function ($query) {
                            $query->select(['id', 'iso', 'name']);
                            $query->withTrashed();
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Datos generales > Textos > '.$data[0]['language']['name'];
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['services']['id'];
                    $pack['name'] = $item['services']['aurora_code'].' - '.$item['services']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceDestination') {
                $data = ServiceDestination::where('id', $item['auditable_id'])
                    ->with([
                        'service' => function ($query) {
                            $query->select(['id','name','aurora_code']);
                            $query->withTrashed();
                        },
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Datos generales > Destino';
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['service']['id'];
                    $pack['name'] = $item['service']['aurora_code'].' - '.$item['service']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\RequirementService') {
                $data = RequirementService::where('id', $item['auditable_id'])
                    ->with([
                        'service' => function ($query) {
                            $query->select(['id','name','aurora_code']);
                            $query->withTrashed();
                        },
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Datos generales > Pre requisitos';
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['service']['id'];
                    $pack['name'] = $item['service']['aurora_code'].' - '.$item['service']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\RestrictionService') {
                $data = RestrictionService::where('id', $item['auditable_id'])
                    ->with([
                        'service' => function ($query) {
                            $query->select(['id','name','aurora_code']);
                            $query->withTrashed();
                        },
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Datos generales > Restricciones';
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['service']['id'];
                    $pack['name'] = $item['service']['aurora_code'].' - '.$item['service']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ExperienceService') {
                $data = ExperienceService::where('id', $item['auditable_id'])
                    ->with([
                        'service' => function ($query) {
                            $query->select(['id','name','aurora_code']);
                            $query->withTrashed();
                        },
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Datos generales > Experiencias';
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['service']['id'];
                    $pack['name'] = $item['service']['aurora_code'].' - '.$item['service']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceSchedule') {
                $data = ServiceSchedule::where('id', $item['auditable_id'])
                    ->with([
                        'services' => function ($query) {
                            $query->select(['id','name','aurora_code']);
                            $query->withTrashed();
                        },
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar > Horarios';
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['services']['id'];
                    $pack['name'] = $item['services']['aurora_code'].' - '.$item['services']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceSchedule') {
                $data = ServiceSchedule::where('id', $item['auditable_id'])
                    ->with([
                        'services' => function ($query) {
                            $query->select(['id','name','aurora_code']);
                            $query->withTrashed();
                        },
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar > Horarios';
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['services']['id'];
                    $pack['name'] = $item['services']['aurora_code'].' - '.$item['services']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceScheduleDetail') {
                $data = ServiceScheduleDetail::where('id', $item['auditable_id'])
                    ->with([
                        'servicesSchedule' => function ($query) {
                            $query->select(['id','service_id']);
                            $query->withTrashed();
                            $query->with([
                                'services' => function ($query) {
                                    $query->select(['id','name','aurora_code']);
                                    $query->withTrashed();
                                },
                            ]);
                        },
                    ])->withTrashed()->limit(1)->get();
                $tipo = ($data[0]['type'] == 'I') ? 'Inicio' : 'fin';
                $item['module'] = 'Administrar > Horarios > '.$tipo;
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['servicesSchedule']['services']['id'];
                    $pack['name'] = $item['servicesSchedule']['services']['aurora_code'].' - '.$item['servicesSchedule']['services']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceOperation') {
                $data = ServiceOperation::where('id', $item['auditable_id'])
                    ->with([
                        'services' => function ($query) {
                            $query->select(['id','name','aurora_code']);
                            $query->withTrashed();
                        },
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar > Operatividad > Hora de inicio';
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['services']['id'];
                    $pack['name'] = $item['services']['aurora_code'].' - '.$item['services']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceOperationActivity') {
                $data = ServiceOperationActivity::where('id', $item['auditable_id'])
                    ->with([
                        'service_operations' => function ($query) {
                            $query->select(['id','service_id']);
                            $query->withTrashed();
                            $query->with([
                                'services' => function ($query) {
                                    $query->select(['id','name','aurora_code']);
                                    $query->withTrashed();
                                },
                            ]);
                        },
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar > Operatividad > Horario > Actividades';
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['service_operations']['services']['id'];
                    $pack['name'] = $item['service_operations']['services']['aurora_code'].' - '.$item['service_operations']['services']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\Galery') {
                $item['module'] = 'Datos generales > Logo';
                $client_find = Galery::where('id', $item['auditable_id'])
                    ->where('slug', 'service_gallery')
                    ->where('type', 'service')
                    ->with([
                        'service' => function ($query) {
                            $query->select(['id', 'aurora_code', 'name']);
                            $query->withTrashed();
                        }
                    ])->withTrashed()->limit(1)->get();
                $service = $client_find->transform(function ($item) {
                    $pack['id'] = $item['object_id'];
                    $pack['name'] = $item['service']['aurora_code'].' - '.$item['service']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceTax') {
                $data = ServiceTax::where('id', $item['auditable_id'])
                    ->with([
                        'service' => function ($query) {
                            $query->select(['id','name','aurora_code']);
                            $query->withTrashed();
                        },
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar > Configuración > Impuestos';
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['service']['id'];
                    $pack['name'] = $item['service']['aurora_code'].' - '.$item['service']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceChild') {
                $data = ServiceChild::where('id', $item['auditable_id'])
                    ->with([
                        'services' => function ($query) {
                            $query->select(['id','name','aurora_code']);
                            $query->withTrashed();
                        },
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar > Configuración > Niños';
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['services']['id'];
                    $pack['name'] = $item['services']['aurora_code'].' - '.$item['services']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceInclusion') {
                $data = ServiceInclusion::where('id', $item['auditable_id'])
                    ->with([
                        'services' => function ($query) {
                            $query->select(['id','name','aurora_code']);
                            $query->withTrashed();
                        },
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar > Inclusiones';
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['services']['id'];
                    $pack['name'] = $item['services']['aurora_code'].' - '.$item['services']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceRate') {
                $data = ServiceRate::where('id', $item['auditable_id'])
                    ->with([
                        'service' => function ($query) {
                            $query->select(['id','name','aurora_code']);
                            $query->withTrashed();
                        },
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar > Tarifas';
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['service']['id'];
                    $pack['name'] = $item['service']['aurora_code'].' - '.$item['service']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceRatePlan') {
                $data = ServiceRatePlan::where('id', $item['auditable_id'])
                    ->with([
                        'service_rate' => function ($query) {
                            $query->select(['id','service_id']);
                            $query->withTrashed();
                            $query->with([
                                'service' => function ($query) {
                                    $query->select(['id','name','aurora_code']);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                $item['module'] = 'Administrar > Tarifas > Plan Tarifario';
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['service_rate']['service']['id'];
                    $pack['name'] = $item['service_rate']['service']['aurora_code'].' - '.$item['service_rate']['service']['name'];
                    return $pack;
                });
            }

            if ($item['auditable_type'] == 'App\ServiceInventory') {
                $data = ServiceInventory::where('id', $item['auditable_id'])
                    ->with([
                        'service_rate' => function ($query) {
                            $query->select(['id','name','service_id']);
                            $query->withTrashed();
                            $query->with([
                                'service' => function ($query) {
                                    $query->select(['id','name','aurora_code']);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->withTrashed()->limit(1)->get();
                if($data[0]['locked'] == 1) {
                    $type = 'Bloqueo';
                }else{
                    $type = 'Asignacion';
                }
                $item['module'] = 'Administrar > Tarifas > '. $data[0]['service_rate']['name']. ' > Disponibilidad > '.$type;
                $service = $data->transform(function ($item) {
                    $pack['id'] = $item['service_rate']['service']['id'];
                    $pack['name'] = $item['service_rate']['service']['aurora_code'].' - '.$item['service_rate']['service']['name'];
                    return $pack;
                });
            }


            $item['old_values'] = $this->getValuesTablesForeing($item['old_values'], $item['auditable_id'],
                $item['auditable_type']);
            $item['new_values'] = $this->getValuesTablesForeing($item['new_values'], $item['auditable_id'],
                $item['auditable_type']);
            $item['service'] = $service;

            return $item;

        });

        $data = [
            'data' => $audits,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function getValuesTablesForeing($values, $auditable_id, $model, $tag = '')
    {
        switch ($model) {
            case 'App\Package':
                if (isset($values['country_id'])) {
                    $country = Country::with([
                        'translations' => function ($query) {
                            $query->where('type', 'country');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['country_id'])->withTrashed()->first();
                    $values['country_id_name'] = $country['translations'][0]['value'];
                }
                if (isset($values['physical_intensity_id'])) {
                    $physical_intensity = PhysicalIntensity::with([
                        'translations' => function ($query) {
                            $query->where('type', 'physicalintensity');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['physical_intensity_id'])->withTrashed()->first();
                    $values['physical_intensity_id_name'] = $physical_intensity['translations'][0]['value'];

                }

                if (isset($values['tag_id'])) {
                    $tag = Tag::with([
                        'translations' => function ($query) {
                            $query->where('type', 'tag');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['tag_id'])->withTrashed()->first();
                    $values['tag_id_name'] = $tag['translations'][0]['value'];
                }
                break;
            case 'App\PackageTax':
                if (isset($values['tax_id'])) {
                    $tax = Tax::where('id', $values['tax_id'])->withTrashed()->first();
                    $values['tax_id_name'] = $tax['name'];
                    $values['tax_id_value'] = $tax['value'];
                }
                break;
            case 'App\PackagePermission':
                if (isset($values['package_id'])) {
                    $permission = PackagePermission::with([
                        'user' => function ($query) {
                            $query->select(['id', 'code', 'name']);
                            $query->withTrashed();
                        }
                    ])->where('id', $auditable_id)->withTrashed()->first();
                    $values['user_id_name'] = $permission['user']['code'].' - '.$permission['user']['name'];
                }
                break;
            case 'App\Galery':
                if (isset($values['url'])) {
                    $url = $this->verifyCloudinaryImg($values['url'], 120, 120, '');
                    $values['url'] = $url;
                }
                break;
            case 'App\PackagePlanRate' :
                if (isset($values['service_type_id'])) {
                    $service_type = ServiceType::with([
                        'translations' => function ($query) {
                            $query->where('type', 'servicetype');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['service_type_id'])->withTrashed()->first();
                    $values['service_type_id_name'] = $service_type['translations'][0]['value'];
                }
                break;
            case 'App\PackagePlanRateCategory':
                if (isset($values['type_class_id'])) {
                    $type_class_id = TypeClass::with([
                        'translations' => function ($query) {
                            $query->where('type', 'typeclass');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['type_class_id'])->withTrashed()->first();
                    $values['type_class_id_name'] = $type_class_id['translations'][0]['value'];
                }
                if (isset($values['package_plan_rate_id'])) {
                    $plan_rate = PackagePlanRate::where('id', $values['package_plan_rate_id'])->withTrashed()->first();
                    $values['package_plan_rate_id_name'] = $plan_rate['name'];
                }
                break;
            case 'App\PackageService':
                $service = PackageService::with([
                    'service' => function ($query) {
                        $query->select('id', 'aurora_code', 'name');
                        $query->withTrashed();
                    }
                ])->with([
                    'hotel' => function ($query) {
                        $query->select('id', 'name');
                        $query->withTrashed();
                    }
                ])->where('id', $auditable_id)
                    ->select(['id', 'type', 'object_id', 'package_plan_rate_category_id'])
                    ->withTrashed()->first();
                if ($service['type'] === 'service') {
                    $values['object_id_name'] = $service['service']['aurora_code'].' - '.$service['service']['name'];
                    $values['type'] = $service['type'];
                } else {
                    $values['object_id_name'] = $service['hotel']['name'];
                    $values['type'] = $service['type'];
                }

                if (isset($values['package_plan_rate_category_id'])) {
                    $category = PackagePlanRateCategory::
                    with([
                        'category' => function ($query) {
                            $query->withTrashed();
                            $query->with([
                                'translations' => function ($query) {
                                    $query->where('language_id', 1);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->where('id', $values['package_plan_rate_category_id'])->withTrashed()->first();
                    $values['package_plan_rate_category_id_name'] = $category['category']['translations'][0]['value'];
                }
                break;
            case 'App\PackageServiceRate':
                if (isset($values['service_rate_id'])) {
                    $service_rate = ServiceRate::where('id', $values['service_rate_id'])
                        ->select(['id', 'name'])->withTrashed()->first();
                    $values['service_rate_id_name'] = $service_rate['id'].' - '.$service_rate['name'];
                }
                if (isset($values['package_service_id'])) {
                    $package_service = PackageService::with([
                        'service' => function ($query) {
                            $query->select(['id', 'aurora_code', 'name']);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['package_service_id'])
                        ->select(['id', 'type', 'object_id'])
                        ->withTrashed()->first();
                    $values['package_service_id_name'] = $package_service['service']['aurora_code'].' - '.$package_service['service']['name'];
                }
                break;
            case 'App\PackageExtension':
                if (isset($values['extension_id'])) {
                    $extensions = PackageExtension::with([
                        'extension' => function ($query) {
                            $query->select(['id', 'code']);
                            $query->withTrashed();
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['id', 'package_id', 'language_id', 'name']);
                                    $query->where('language_id', 1);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->where('id', $auditable_id)->withTrashed()->first();
                    $values['extension_id_name'] = $extensions['extension']['id'].' - '.$extensions['extension']['translations'][0]['name'];
                }
                break;
            case 'App\PackageServiceRoom':
                if (isset($values['rate_plan_room_id'])) {
                    $rate_plan_rooms = RatesPlansRooms::with([
                        'rate_plan' => function ($query) {
                            $query->select(['id', 'code', 'name']);
                            $query->withTrashed();
                        }
                    ])->with([
                        'room' => function ($query) {
                            $query->select(['id', 'hotel_id', 'room_type_id']);
                            $query->withTrashed();
                            $query->with([
                                'translations' => function ($query) {
                                    $query->where('type', 'room');
                                    $query->where('language_id', 1);
                                    $query->withTrashed();
                                }
                            ]);
                            $query->with([
                                'hotel' => function ($query) {
                                    $query->select(['id', 'name']);
                                    $query->withTrashed();
                                }
                            ]);
                        }
                    ])->where('id', $values['rate_plan_room_id'])->select([
                        'id',
                        'rates_plans_id',
                        'room_id'
                    ])->withTrashed()->first();
                    $values['rate_plan_room_id_hotel'] = $rate_plan_rooms['room']['hotel']['name'];
                    $values['rate_plan_room_id_name'] = $rate_plan_rooms['rate_plan']['name'];
                    $values['rate_plan_room_id_room'] = $rate_plan_rooms['room']['translations'][0]['value'];
                }
                if (isset($values['package_service_id'])) {
                    $package_service = PackageService::with([
                        'hotel' => function ($query) {
                            $query->select(['id', 'name']);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['package_service_id'])
                        ->select(['id', 'type', 'object_id'])
                        ->withTrashed()->first();
                    $values['package_service_id_name'] = $package_service['hotel']['name'];
                }
                break;
            case 'App\PackageDynamicRate':
                if (isset($values['service_type_id'])) {
                    $service_type = ServiceType::with([
                        'translations' => function ($query) {
                            $query->where('type', 'servicetype');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['service_type_id'])->withTrashed()->first();
                    $values['service_type_id_name'] = $service_type['translations'][0]['value'];
                }
                $rate_dynamic = PackageDynamicRate::where('id', $auditable_id)->withTrashed()->first();
                $values['pax_to'] = $rate_dynamic['pax_to'];
                $values['pax_from'] = $rate_dynamic['pax_from'];
                break;
            case 'App\PackageRateSaleMarkup':
                $sales = PackageRateSaleMarkup::with('seller')->withTrashed()
                    ->where('id', $auditable_id)->first();
                $values['seller_type'] = $sales['seller_type'];
                if ($sales['seller_type'] == 'App\Market') {
                    $values['seller_type_name'] = $sales['seller']['name'];
                } else {
                    $values['seller_type_name'] = $sales['seller']['name'];
                }
                break;
            case 'App\PackageDynamicSaleRate':
                $sales = PackageDynamicSaleRate::with([
                    'rate_sale_markups' => function ($query) {
                        $query->withTrashed();
                        $query->with('seller');
                    }
                ])->withTrashed()
                    ->where('id', $auditable_id)->first();
                $values['pax_to'] = $sales['pax_to'];
                $values['pax_from'] = $sales['pax_from'];
                $values['seller_type'] = $sales['rate_sale_markups']['seller_type'];
                if ($sales['seller_type'] == 'App\Market') {
                    $values['package_rate_sale_markup_id_name'] = $sales['rate_sale_markups']['seller']['name'];
                } else {
                    $values['package_rate_sale_markup_id_name'] = $sales['rate_sale_markups']['seller']['name'];
                }

                if (isset($values['service_type_id'])) {
                    $service_type = ServiceType::with([
                        'translations' => function ($query) {
                            $query->where('type', 'servicetype');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['service_type_id'])->withTrashed()->first();
                    $values['service_type_id_name'] = $service_type['translations'][0]['value'];
                }

                break;
            case 'App\Client':
                if (isset($values['country_id'])) {
                    $country = Country::with([
                        'translations' => function ($query) {
                            $query->where('type', 'country');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['country_id'])->withTrashed()->first();
                    $values['country_id_name'] = $country['translations'][0]['value'];
                }
                if (isset($values['market_id'])) {
                    $market = Market::where('id', $values['market_id'])->withTrashed()->first();
                    $values['market_id_name'] = $market['name'];
                }
                if (isset($values['language_id'])) {
                    $language = Language::where('id', $values['language_id'])->withTrashed()->first();
                    $values['language_id_name'] = $language['name'];
                }
                break;
            case 'App\ClientSeller':
                if (isset($values['user_id'])) {
                    $user = User::where('id', $values['user_id'])->withTrashed()->first();
                    $values['user_id_name'] = $user['code'].' - '.$user['name'];
                }
                break;
            case 'App\MarkupService':
                if (isset($values['service_id'])) {
                    $user = Service::where('id', $values['service_id'])->select([
                        'id',
                        'aurora_code',
                        'name'
                    ])->withTrashed()->first();
                    $values['service_id_name'] = $user['aurora_code'].' - '.$user['name'];
                }
                break;
            case 'App\MarkupHotel':
                if (isset($values['hotel_id'])) {
                    $hotel = Hotel::where('id', $values['hotel_id'])->select(['id', 'name'])->withTrashed()->first();
                    $values['hotel_id_name'] = $hotel['name'];
                }
                break;
            case 'App\ServiceMarkupRatePlan':
                if (isset($values['service_rate_id'])) {
                    $service_rate = ServiceRate::where('id', $values['service_rate_id'])->select([
                        'id',
                        'name'
                    ])->withTrashed()->first();
                    $values['service_rate_id_name'] = $service_rate['name'];
                }
                break;
            case 'App\MarkupRatePlan':
                if (isset($values['rate_plan_id'])) {
                    $rate = RatesPlans::where('id', $values['rate_plan_id'])->select([
                        'id',
                        'name'
                    ])->withTrashed()->first();
                    $values['rate_plan_id_name'] = $rate['name'];
                }
                break;
            case 'App\ServiceClient':
                if (isset($values['service_id'])) {
                    $service = Service::where('id', $values['service_id'])->select([
                        'id',
                        'aurora_code',
                        'name'
                    ])->withTrashed()->first();
                    $values['service_id_name'] = $service['aurora_code'].' - '.$service['name'];
                }
                break;
            case 'App\HotelClient':
                if (isset($values['hotel_id'])) {
                    $hotel = Hotel::where('id', $values['hotel_id'])->select(['id', 'name'])->withTrashed()->first();
                    $values['hotel_id_name'] = $hotel['name'];
                }
                break;
            case 'App\ServiceClientRatePlan':
                if (isset($values['service_rate_id'])) {
                    $service_rate = ServiceRate::where('id', $values['service_rate_id'])
                        ->select(['id', 'name', 'service_id'])
                        ->withTrashed()->first();
                    $values['service_rate_id_name'] = $service_rate['name'];
                }
                break;
            case 'App\ClientRatePlan':
                if (isset($values['rate_plan_id'])) {
                    $hotel_rate = RatesPlans::where('id', $values['rate_plan_id'])
                        ->select(['id', 'name'])
                        ->withTrashed()->first();
                    $values['rate_plan_id_name'] = $hotel_rate['name'];
                }
                break;
            case 'App\ClientServiceOffer':
                if (isset($values['service_rate_id'])) {
                    $service_rate = ServiceRate::where('id', $values['service_rate_id'])
                        ->select(['id', 'name', 'service_id'])
                        ->withTrashed()->first();
                    $values['service_rate_id_name'] = $service_rate['name'];
                }
                break;
            case 'App\Service':
                if (isset($values['unit_id'])) {
                    $data = Unit::with([
                        'translations' => function ($query) {
                            $query->where('type', 'unit');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['unit_id'])->withTrashed()->first();
                    $values['unit_id_name'] = $data['translations'][0]['value'];
                }
                if (isset($values['unit_duration_id'])) {
                    $data = UnitDuration::with([
                        'translations' => function ($query) {
                            $query->where('type', 'unitduration');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['unit_duration_id'])->withTrashed()->first();
                    $values['unit_duration_id_name'] = $data['translations'][0]['value'];
                }
                if (isset($values['currency_id'])) {
                    $data = Currency::where('id', $values['currency_id'])->withTrashed()->first();
                    $values['currency_id_name'] = $data['iso'];
                }
                if (isset($values['tag_service_id'])) {
                    $data = TagService::with([
                        'translations' => function ($query) {
                            $query->where('type', 'tagservices');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['tag_service_id'])->withTrashed()->first();
                    $values['tag_service_id_name'] = $data['translations'][0]['value'];
                }
                if (isset($values['service_type_id'])) {
                    $service_type = ServiceType::with([
                            'translations' => function ($query) {
                                $query->where('type', 'servicetype');
                                $query->where('language_id', 1);
                                $query->withTrashed();
                            }
                        ])->where('id', $values['service_type_id'])->withTrashed()->first();
                        $values['service_type_id_name'] = $service_type['translations'][0]['value'];

                }
                if (isset($values['classification_id'])) {
                    $service_type = Classification::with([
                        'translations' => function ($query) {
                            $query->where('type', 'classification');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['classification_id'])->withTrashed()->first();
                    $values['classification_id_name'] = $service_type['translations'][0]['value'];

                }
                if (isset($values['service_sub_category_id'])) {
                    $service_type = ServiceSubCategory::with([
                        'translations' => function ($query) {
                            $query->where('type', 'servicesubcategory');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['service_sub_category_id'])->withTrashed()->first();
                    $values['service_sub_category_id_name'] = $service_type['translations'][0]['value'];
                }
                break;
            case 'App\ServiceOrigin':
                if (isset($values['country_id'])) {
                    $data = Country::with([
                        'translations' => function ($query) {
                            $query->where('type', 'country');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['country_id'])->withTrashed()->first();
                    $values['country_id_name'] = $data['translations'][0]['value'];
                }
                if (isset($values['state_id'])) {
                    $data = State::with([
                        'translations' => function ($query) {
                            $query->where('type', 'state');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['state_id'])->withTrashed()->first();
                    $values['state_id_name'] = $data['translations'][0]['value'];
                }
                if (isset($values['city_id'])) {
                    $data = City::with([
                        'translations' => function ($query) {
                            $query->where('type', 'city');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['city_id'])->withTrashed()->first();
                    $values['city_id_name'] = $data['translations'][0]['value'];
                }
                if (isset($values['zone_id'])) {
                    $data = Zone::with([
                        'translations' => function ($query) {
                            $query->where('type', 'zone');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['zone_id'])->withTrashed()->first();
                    $values['zone_id_name'] = $data['translations'][0]['value'];
                }
                break;
            case 'App\ServiceDestination':
                if (isset($values['country_id'])) {
                    $data = Country::with([
                        'translations' => function ($query) {
                            $query->where('type', 'country');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['country_id'])->withTrashed()->first();
                    $values['country_id_name'] = $data['translations'][0]['value'];
                }
                if (isset($values['state_id'])) {
                    $data = State::with([
                        'translations' => function ($query) {
                            $query->where('type', 'state');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['state_id'])->withTrashed()->first();
                    $values['state_id_name'] = $data['translations'][0]['value'];
                }
                if (isset($values['city_id'])) {
                    $data = City::with([
                        'translations' => function ($query) {
                            $query->where('type', 'city');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['city_id'])->withTrashed()->first();
                    $values['city_id_name'] = $data['translations'][0]['value'];
                }
                if (isset($values['zone_id'])) {
                    $data = Zone::with([
                        'translations' => function ($query) {
                            $query->where('type', 'zone');
                            $query->where('language_id', 1);
                            $query->withTrashed();
                        }
                    ])->where('id', $values['zone_id'])->withTrashed()->first();
                    $values['zone_id_name'] = $data['translations'][0]['value'];
                }
                break;
            case 'App\ServiceTranslation':
                if (isset($values['language_id'])) {
                    $language = Language::where('id', $values['language_id'])->withTrashed()->first();
                    $values['language_id_name'] = $language['name'];
                }
                break;
            case 'App\RequirementService':
                if (isset($values['requirement_id'])) {
                    $data = RequirementService::where('id', $auditable_id)
                        ->with([
                            'requirement.translations' => function ($query) {
                                $query->where('type', 'requirement');
                                $query->where('language_id', 1);
                                $query->withTrashed();
                            }
                        ])
                        ->withTrashed()->first();
                    $values['requirement_id_name'] = $data['requirement']['translations'][0]['value'];
                }
                break;
            case 'App\RestrictionService':
                if (isset($values['restriction_id'])) {
                    $data = RestrictionService::where('id', $auditable_id)
                        ->with([
                            'restriction.translations' => function ($query) {
                                $query->where('type', 'restriction');
                                $query->where('language_id', 1);
                                $query->withTrashed();
                            }
                        ])
                        ->withTrashed()->first();
                    $values['restriction_id_name'] = $data['restriction']['translations'][0]['value'];
                }
                break;
            case 'App\ExperienceService':
                if (isset($values['experience_id'])) {
                    $data = ExperienceService::where('id', $auditable_id)
                        ->with([
                            'experience.translations' => function ($query) {
                                $query->where('type', 'experience');
                                $query->where('language_id', 1);
                                $query->withTrashed();
                            }
                        ])->withTrashed()->first();
                    $values['experience_id_name'] = $data['experience']['translations'][0]['value'];
                }
                break;
            case 'App\ServiceOperationActivity':
                if (isset($values['service_type_activity_id'])) {
                    $data = ServiceOperationActivity::where('id', $auditable_id)
                        ->with([
                            'service_type_activities.translations' => function ($query) {
                                $query->where('type', 'servicetypeactivity');
                                $query->where('language_id', 1);
                                $query->withTrashed();
                            }
                        ])->withTrashed()->first();
                    $values['service_type_activity_id_name'] = $data['service_type_activities']['translations'][0]['value'];
                }
                break;
            case 'App\ServiceInclusion':
                    $data = ServiceInclusion::where('id', $auditable_id)
                        ->with([
                            'inclusions.translations' => function ($query) {
                                $query->where('type', 'inclusion');
                                $query->where('language_id', 1);
                                $query->withTrashed();
                            }
                        ])->withTrashed()->first();
                    $values['inclusion_id'] = $data['inclusions']['translations'][0]['object_id'];
                    $values['inclusion_id_name'] = $data['inclusions']['translations'][0]['value'];
                break;
            case 'App\ServiceRatePlan':
                if (isset($values['service_cancellation_policy_id'])) {
                    $data = ServiceCancellationPolicies::where('id',$values['service_cancellation_policy_id'])->withTrashed()->limit(1)->get();
                    $values['service_cancellation_policy_id_name'] = $data[0]['id'] .' - '. $data[0]['name'];
                }
                $data = ServiceRatePlan::where('id',$auditable_id)
                    ->with([
                        'service_rate' => function ($query) {
                            $query->select(['id','name','service_id']);
                            $query->withTrashed();
                        }
                    ])->withTrashed()->limit(1)->get();
                $values['service_rate_id_name'] = $data[0]['service_rate']['id'] .' - '. $data[0]['service_rate']['name'];
                break;
            case 'App\ServiceInventory':
                $data = ServiceInventory::where('id',$auditable_id)
                    ->with([
                        'service_rate' => function ($query) {
                            $query->select(['id','name','service_id']);
                            $query->withTrashed();
                        }
                    ])->withTrashed()->limit(1)->get();
                $values['service_rate_id_name'] = $data[0]['service_rate']['id'] .' - '. $data[0]['service_rate']['name'];
                break;
        }
        return $values;
    }


}
