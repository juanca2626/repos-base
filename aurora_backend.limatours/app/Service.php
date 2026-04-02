<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Activitylog\Traits\LogsActivity;

use App\City;
use App\Client;
use App\Language;
use App\Markup;
use App\ServiceCategory;
use App\ServiceClient;
use App\State;
use Carbon\Carbon;

class Service extends Model implements Auditable
{
    use SoftDeletes, LogsActivity, \OwenIt\Auditing\Auditable;

    /*
     * column: type -> tipo de servicio puede ser un suplemento o servicio
     * un suplemento no se puede vender solo debe ir acompañado de un servicio
     */
    CONST ACTIVE = 1;
    CONST INACTIVE = 1;
    CONST TYPE_SERVICE = 'service';
    CONST TYPE_SUPPLEMENT = 'supplement';

    public function generateTags(): array
    {
        return ['service'];
    }

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected static $logName = 'service';

    protected static $ignoreChangedAttributes = [
        'id',
        'latitude',
        'longitude',
        'allow_child',
        'allow_infant',
        'allow_guide',
        'limit_confirm_hours',
        'unit_duration_limit_confirmation',
        'infant_min_age',
        'infant_max_age',
        'date_solicitude',
        'user_id',
        'created_at',
        'updated_at'
    ];

    protected static $logAttributes = [
        'aurora_code',
        'name',
        'currency_id',
        'qty_reserve',
        'equivalence_aurora',
        'affected_igv',
        'affected_markup',
        'include_accommodation',
        'unit_id',
        'unit_duration_id',
        'unit_duration_reserve',
        'service_type_id',
        'classification_id',
        'service_sub_category_id',
        'duration',
        'pax_min',
        'pax_max',
        'min_age',
        'require_itinerary',
        'require_image_itinerary',
        'status',
        'notes',
        'price_dynamic',
    ];

    public function equivalence_services()
    {
        return $this->hasMany('App\EquivalenceService');
    }

    public function serviceOrigin()
    {
        return $this->hasMany('App\ServiceOrigin');
    }

    public function serviceDestination()
    {
        return $this->hasMany('App\ServiceDestination');
    }

    public function currency()
    {
        return $this->belongsTo('App\Currency');
    }

    public function units()
    {
        return $this->belongsTo('App\Unit', 'unit_id');
    }

    public function unitDurations()
    {
        return $this->belongsTo('App\UnitDuration', 'unit_duration_id');
    }

    public function serviceType()
    {
        return $this->belongsTo('App\ServiceType');
    }

    public function classification()
    {
        return $this->belongsTo('App\Classification');
    }

    public function serviceSubCategory()
    {
        return $this->belongsTo('App\ServiceSubCategory');
    }

    public function tag_service()
    {
        return $this->belongsTo('App\TagService');
    }

    public function languages_guide()
    {
        return $this->hasMany('App\LanguageServiceGuide', 'service_id', 'id');
    }

    public function requirement()
    {
        return $this->belongsToMany('App\Requirement')
            ->whereNull('requirement_service.deleted_at')->withTimestamps();
    }

    public function service_translations()
    {
        return $this->hasMany('App\ServiceTranslation', 'service_id', 'id');
    }

    public function reservations_services()
    {
        return $this->hasMany('App\ReservationsService', 'service_id', 'id');
    }

    public function restriction()
    {
        return $this->belongsToMany('App\Restriction')
            ->whereNull('restriction_service.deleted_at')->withTimestamps();
    }

    public function experience()
    {
        return $this->belongsToMany('App\Experience')
            ->whereNull('experience_service.deleted_at')->withTimestamps();
    }

    public function inclusions()
    {
        return $this->hasMany('App\ServiceInclusion');
    }

    public function service_rate()
    {
        return $this->hasMany('App\ServiceRate');
    }

    public function progress_bars()
    {
        return $this->hasMany('App\ProgressBar', 'object_id', 'id')
            ->where('progress_bars.type', '=', 'service');
    }

    public function cancellation_policy()
    {
        return $this->hasMany('App\ServiceCancellationPolicies');
    }

    public function serviceEquivAssociation()
    {
        return $this->hasMany('App\ServiceEquivalenceAssociation');
    }

    public function service_clients()
    {
        return $this->hasMany('App\ServiceClient');
    }

    public function children_ages()
    {
        return $this->hasMany('App\ServiceChild');
    }

    public function galleries()
    {
        return $this->hasMany('App\Galery', 'object_id', 'id')
            ->where('type', '=', 'service');
    }

    public function schedules()
    {
        return $this->hasMany('App\ServiceSchedule');
    }

    public function tax()
    {
        return $this->hasMany('App\ServiceTax');
    }

    public function operability()
    {
        return $this->hasMany('App\ServiceOperation');
    }

    public function markup_service()
    {
        return $this->hasMany('App\MarkupService');
    }

    public function service_component()
    {
        return $this->hasMany('App\ServiceComponent');
    }

    public function rated()
    {
        return $this->hasMany('App\ClientServiceRated');
    }

    public function setting_client()
    {
        return $this->hasMany('App\ClientServiceSetting');
    }

    public function highlights()
    {
        return $this->hasMany('App\ServiceInformationImportant');
    }

    public function instructions()
    {
        return $this->hasMany('App\ServiceInstruction');
    }

    public function physical_intensity()
    {
        return $this->belongsTo('App\PhysicalIntensity');
    }

    public function client_services()
    {
        return $this->hasMany('App\ClientService', 'service_id');
    }

    public function getMasiAttribute($value)
    {
        if ($value == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function client_service_setting()
    {
        return $this->hasMany('App\ClientServiceSetting', 'service_id');
    }

    /**
     * @param array|null $ids
     * @param bool $first
     * @return Service[]|\Illuminate\Database\Eloquent\Builder[]|Collection|mixed
     */
    public function getInformationServices(array $ids = null, $language_id = 1, $first = false)
    {
        $services = self::select([
            'id',
            'aurora_code',
            'name',
            'currency_id',
            'latitude',
            'longitude',
            'qty_reserve',
            'equivalence_aurora',
            'affected_igv',
            'affected_markup',
            'affected_schedule',
            'allow_guide',
            'allow_child',
            'allow_infant',
            'limit_confirm_hours',
            'unit_duration_limit_confirmation',
            'infant_min_age',
            'infant_max_age',
            'include_accommodation',
            'unit_id',
            'unit_duration_id',
            'unit_duration_reserve',
            'service_type_id',
            'classification_id',
            'service_sub_category_id',
            'user_id',
            'duration',
            'pax_min',
            'pax_max',
            'min_age',
            'require_itinerary',
            'require_image_itinerary',
            'status',
//            'notes',
            'tag_service_id'
        ])
            ->where('status', 1)
            ->with([
                'tax' => function ($query) {
                    $query->select('amount', 'service_id');
                    $query->where('status', 1);
                }
            ])
            ->with([
                'languages_guide' => function ($query) {
                    $query->select('id', 'language_id', 'service_id');
                    $query->with([
                        'language' => function ($query) {
                            $query->select('id', 'name', 'iso');
                        }
                    ]);
                }
            ])
            ->with([
                'tag_service' => function ($query) use ($language_id) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'slug', 'value');
                            $query->where('type', 'tagservices');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])
            ->with([
                'serviceDestination.country' => function ($query) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'country');
                            $query->where('language_id', 1);
                        }
                    ]);
                }
            ])->with([
                'serviceDestination.state' => function ($query) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'state');
                            $query->where('language_id', 1);
                        }
                    ]);
                }
            ])->with([
                'serviceDestination.city' => function ($query) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'city');
                            $query->where('language_id', 1);
                        }
                    ]);
                }
            ])->with([
                'serviceDestination.zone' => function ($query) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'zone');
                            $query->where('language_id', 1);
                        }
                    ]);
                }
            ])->with([
                'serviceOrigin.country' => function ($query) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'country');
                            $query->where('language_id', 1);
                        }
                    ]);
                }
            ])->with([
                'serviceOrigin.state' => function ($query) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'state');
                            $query->where('language_id', 1);
                        }
                    ]);
                }
            ])->with([
                'serviceOrigin.city' => function ($query) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'city');
                            $query->where('language_id', 1);
                        }
                    ]);
                }
            ])->with([
                'serviceOrigin.zone' => function ($query) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'zone');
                            $query->where('language_id', 1);
                        }
                    ]);
                }
            ])->with([
                'currency.translations' => function ($query) use ($language_id) {
                    $query->where('type', 'currency');
                    $query->where('language_id', $language_id);
                }
            ])->with([
                'serviceSubCategory.serviceCategories' => function ($query) use ($language_id) {
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'servicecategory');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])->with([
                'serviceSubCategory.translations' => function ($query) use ($language_id) {
                    $query->where('type', 'servicesubcategory');
                    $query->where('language_id', $language_id);
                }
            ])->with([
                'classification' => function ($query) use ($language_id) {
                    $query->with([
                        'galeries' => function ($query) {
                            $query->where('type', 'classification');
                        }
                    ]);
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->where('type', 'classification');
                            $query->where('language_id', $language_id);
                        }
                    ]);

                }
            ])->with([
                'unitDurations.translations' => function ($query) use ($language_id) {
                    $query->where('type', 'unitduration');
                    $query->where('language_id', $language_id);
                }
            ])->with([
                'serviceType' => function ($query) use ($language_id) {
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->where('type', 'servicetype');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])->with([
                'experience' => function ($query) use ($language_id) {
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])->with([
                'restriction' => function ($query) use ($language_id) {
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])
            ->with([
                'service_translations' => function ($query) use ($language_id) {
                    $query->select('id', 'language_id', 'name_commercial', 'description_commercial',
                        'itinerary_commercial', 'summary_commercial',
                        'service_id');
                    $query->where('language_id', $language_id);
                }
            ])
            ->with([
                'galleries' => function ($query) {
                    $query->select('object_id', 'slug', 'url');
                    $query->where('type', 'service');
                },
            ])
            ->with([
                'inclusions' => function ($query) {
                    if (Auth::user()->user_type_id !== 4 or Auth::user()->user_type_id !== 3) {
                        $query->where('see_client', 1);
                    }
                }
            ])
            ->with([
                'inclusions.inclusions.translations' => function ($query) use ($language_id) {
                    $query->where('type', 'inclusion');
                    $query->where('language_id', $language_id);
                }
            ])
            ->with([
                'schedules' => function ($query) {
                    $query->with('servicesScheduleDetail');
                }
            ])
            ->with([
                'operability' => function ($query) use ($language_id) {
                    $query->with([
                        'services_operation_activities.service_type_activities.translations' => function ($query) use (
                            $language_id
                        ) {
                            $query->where('type', 'servicetypeactivity');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])->whereIn('id', $ids)->get();

        return $first ? $services->first() : $services;
    }


    public function supplements()
    {
        return $this->hasMany('App\ServiceSupplement', 'service_id');
    }

    public function composition()
    {
        return $this->hasMany('App\EquivalenceService', 'service_id');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', true);
    }

    public function ratesServicesByYear($service_year, $lang, $client_id)
    {

        $service_year = $service_year;
        $language_id = Language::where('state', 1)->where('iso', $lang)->first()->id;

        $data = [
            "cities" => []
        ];

        $markup_new = Markup::where('client_id', $client_id)->where('period', $service_year)->first();

        if ($markup_new == null) {
            $markup_client = 17;
        } else {
            $markup_client = (int)$markup_new->service;
        }

        $client = Client::where('id', $client_id)->first();
        //Todo Variable para guardar las categorias
        $service_categories_new = [];

        //Todo Buscamos las categorias
        $service_categories = ServiceCategory::with([
            'translations' => function ($query) use ($language_id) {
                $query->select(['object_id', 'value']);
                $query->where('type', 'servicecategory');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceSubCategory' => function ($query) use ($language_id) {
                $query->select(['id', 'service_category_id', 'order']);
                $query->with([
                    'translations' => function ($query) use ($language_id) {
                        $query->select(['object_id', 'value']);
                        $query->where('type', 'servicesubcategory');
                        $query->where('language_id', $language_id);
                    }
                ]);
                $query->orderBy('order');
            }
        ])->orderBy('order')->get(['id', 'order']);

        //Todo Recorremos las categorias y las guardamos
        foreach ($service_categories as $category) {
            $service_categories_new [] = [
                "service_category_id" => $category["id"],
                "category_name" => $category["translations"][0]["value"],
                "subcategories" => []
            ];
        }

        //Todo Recorremos las categorias y las subcategorias y lo guadarmos
        foreach ($service_categories_new as $index_service_category_new => $category_new) {
            foreach ($service_categories as $category) {
                if ($category_new["service_category_id"] == $category["id"]) {
                    foreach ($category["serviceSubCategory"] as $subcategory) {
                        array_push($service_categories_new[$index_service_category_new]["subcategories"], [
                            "subcategory_id" => $subcategory["id"],
                            "subcategory_name" => $subcategory["translations"][0]["value"],
                            "services" => []
                        ]);
                    }
                }
            }
        }

        //Todo Consulto los estados del pais Peru (89)
        $states_id = State::where('country_id', 89)->pluck('id');

        //Todo Consulto las ciudades
        $cities = City::with([
            'translations' => function ($query) use ($language_id) {
                $query->select(['object_id', 'value']);
                $query->where('type', 'city');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'order_rate' => function ($query) {
                $query->select(['city_id', 'order']);
            }
        ])->whereIn('state_id', $states_id)->get(['id', 'iso'])->sortBy('order_rate.order')->values();


        //Todo Agrego y creo un arreglo de rangos desde 1 hasta 40 pax
        $ranges = [];
        for ($i = 1; $i <= 40; $i++) {
            array_push($ranges, [
                "pax_from" => $i,
                "pax_to" => $i,
                "price_adult" => "",
                "price_child" => "",
                "price_infant" => "",
                "price_guide" => "",
                "flag_migrate" => "",
            ]);
        }

        //Llenar arreglo de ciudades

        foreach ($cities as $city) {
            array_push($data["cities"], [
                "city_id" => $city["id"],
                "city_name" => $city["translations"][0]["value"],
                "date_download" => Carbon::now()->format('d/m/Y'),
                "client_code" => $client->code,
                "client_name" => $client->name,
                "ranges" => $ranges,
                "categories" => $service_categories_new
            ]);
        }


        $services_locked = [];
        if ($client->id != 15766) { // 5NEG
            $services_locked = ServiceClient::where('client_id', $client->id)
                ->where('period', $service_year)->pluck('service_id');
        }
        $services_new = [];
        $_services = collect();
        $code_services_with_equivalence = [];

        Service::with([
            'serviceOrigin' => function ($query) {
                $query->select(['id', 'service_id', 'country_id', 'state_id', 'city_id', 'zone_id']);
            }
        ])->with([
            'service_rate' => function ($query) use ($service_year, $language_id, $client_id) {
                $query->select('id', 'name', 'service_id', 'rate', 'status');
                $query->where('status', 1);
                $query->where('rate', 1);
                $query->with([
                    'service_rate_plans' => function ($query) use ($service_year) {
                        $query->select([
                            'id',
                            'service_rate_id',
                            'service_cancellation_policy_id',
                            'user_id',
                            'date_from',
                            'date_to',
                            'pax_from',
                            'pax_to',
                            'price_adult',
                            'price_child',
                            'price_infant',
                            'price_guide',
                            'flag_migrate',
                            'status'
                        ]);
                        $query->where('date_from', '>=', $service_year . '-01-01');
                        $query->where('date_to', '<=', $service_year . '-12-31');
                        $query->with([
                            'policy' => function ($query) {
                                $query->where('status', 1);
                                $query->with([
                                    'parameters' => function ($query) {
                                        $query->with('penalty');
                                    }
                                ]);
                            }
                        ]);
                    }
                ])->with([
                    'markup_rate_plan' => function ($query) use ($service_year, $client_id) {
                        $query->select('markup', 'period', 'service_rate_id');
                        $query->where('client_id', $client_id);
                        $query->where('period', $service_year);
                    }
                ])->with([
                    'translations' => function ($query) use ($language_id) {
                        $query->select(['object_id', 'value']);
                        $query->where('language_id', $language_id);
                    }
                ]);

            }
        ])
            ->with([
                'inclusions' => function ($query) use ($language_id) {
                    $query->select(['id', 'service_id', 'day', 'inclusion_id', 'include', 'see_client', 'order']);
                    $query->where('include', 1);
                    $query->where('see_client', 1);
                    $query->orderBy('day');
                    $query->orderBy('order');
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
                                    $query->select(['object_id', 'value']);
                                    $query->where('language_id', $language_id);
                                }
                            ]);
                        }
                    ]);
                }
            ])
            ->with([
                'service_translations' => function ($query) use ($language_id) {
                    $query->select([
                        'name',
                        'name_commercial',
                        'description',
                        'description_commercial',
                        'itinerary',
                        'itinerary_commercial',
                        'service_id'
                    ]);
                    $query->where('language_id', $language_id);
                }
            ])
            ->with([
                'serviceEquivAssociation' => function ($query) use ($service_year, $language_id) {
                    $query->with([
                        'service' => function ($query) use ($service_year, $language_id) {
                            $query->select(['id', 'name', 'aurora_code', 'service_type_id', 'service_sub_category_id']);
                            $query->with([
                                'service_rate' => function ($query) use ($service_year, $language_id) {
                                    $query->select('id', 'name', 'service_id', 'status');
                                    $query->where('status', 1);
                                    $query->where('rate', 1);
                                    $query->with([
                                        'service_rate_plans' => function ($query) use ($service_year) {
                                            $query->select([
                                                'id',
                                                'service_rate_id',
                                                'service_cancellation_policy_id',
                                                'user_id',
                                                'date_from',
                                                'date_to',
                                                'pax_from',
                                                'pax_to',
                                                'price_adult',
                                                'price_child',
                                                'price_infant',
                                                'price_guide',
                                                'flag_migrate',
                                                'status'
                                            ]);
                                            $query->where('date_from', '>=', $service_year . '-01-01');
                                            $query->where('date_to', '<=', $service_year . '-12-31');
                                            $query->with([
                                                'policy' => function ($query) {
                                                    $query->where('status', 1);
                                                    $query->with([
                                                        'parameters' => function ($query) {
                                                            $query->with('penalty');
                                                        }
                                                    ]);
                                                }
                                            ]);
                                        }
                                    ])->with([
                                        'translations' => function ($query) use ($language_id) {
                                            $query->select(['object_id', 'value']);
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ]);
                }
            ])
            ->with([
                'markup_service' => function ($query) use ($service_year, $client_id) {
                    $query->select(['id', 'service_id', 'markup']);
                    $query->where('client_id', $client_id);
                    $query->where('period', $service_year);
                }
            ])
            ->whereHas('service_rate', function ($q) {
                $q->where('rate', 1);
            })
            ->whereDoesntHave('client_services')
            ->where('status', 1)
            ->whereNotIn('id', $services_locked)
            // ->whereIn('id', [2298])
            // ->whereIn('id', [2298,2320])
            ->orderBy('rate_order')
            ->chunk(100, function ($services) use ($_services) {
                foreach ($services as $service) {
                    $_services->add($service);
                }
            });


        //Todo servicios con equivalencia
        foreach ($_services as $service) {
            if (count($service["serviceEquivAssociation"]) > 0 && !in_array($service["aurora_code"],
                    $code_services_with_equivalence) && count($service["service_rate"]) > 0) {
                $aurora_code_sim = "";
                $aurora_code_pc = "";
                $aurora_code_na = "";
                $aurora_code_semi = "";
                array_push($code_services_with_equivalence, $service["aurora_code"]);
                array_push($code_services_with_equivalence,
                    $service["serviceEquivAssociation"][0]["service"]["aurora_code"]);
                //Todo Compartido
                if ($service["service_type_id"] == 1) {
                    $aurora_code_sim = $service["aurora_code"];
                }
                //Todo privado
                if ($service["service_type_id"] == 2) {
                    $aurora_code_pc = $service["aurora_code"];
                }
                //Todo Ninguno
                if ($service["service_type_id"] == 3) {
                    $aurora_code_na = $service["aurora_code"];
                }

                //Todo Semi privado
                if ($service["service_type_id"] == 4) {
                    $aurora_code_semi = $service["aurora_code"];
                }


                if ($service["serviceEquivAssociation"][0]["service"]["service_type_id"] == 1) {
                    $aurora_code_sim = $service["serviceEquivAssociation"][0]["service"]["aurora_code"];
                }

                if ($service["serviceEquivAssociation"][0]["service"]["service_type_id"] == 2) {
                    $aurora_code_pc = $service["serviceEquivAssociation"][0]["service"]["aurora_code"];
                }

                if ($service["serviceEquivAssociation"][0]["service"]["service_type_id"] == 3) {
                    $aurora_code_na = $service["serviceEquivAssociation"][0]["service"]["aurora_code"];
                }

                if ($service["serviceEquivAssociation"][0]["service"]["service_type_id"] == 4) {
                    $aurora_code_semi = $service["serviceEquivAssociation"][0]["service"]["aurora_code"];
                }

                $inclusions = [
                    [
                        "day" => 1,
                        "inclusion_name" => "",
                    ]
                ];
                $day = 1;
                foreach ($service["inclusions"] as $inclusion) {
                    if ($inclusion["day"] > $day) {
                        $day = $inclusion["day"];
                        array_push($inclusions, [
                            "day" => $inclusion["day"],
                            "inclusion_name" => "",
                        ]);
                    }
                }
                foreach ($inclusions as $index => $inclusion) {
                    foreach ($service["inclusions"] as $service_inclusion) {
                        if ($service_inclusion["day"] == $inclusion["day"]) {
                            if (count($service_inclusion["inclusions"]["translations"]) > 0) {
                                $inclusions[$index]["inclusion_name"] .= $service_inclusion["inclusions"]["translations"][0]["value"] . ',';
                            }
                        }
                    }
                }

                array_push($services_new, [
                    "aurora_code" => $service["aurora_code"],
                    "aurora_code_sim" => $aurora_code_sim,
                    "aurora_code_pc" => $aurora_code_pc,
                    "aurora_code_na" => $aurora_code_na,
                    "aurora_code_semi" => $aurora_code_semi,
                    "service_city_id" => $service["serviceOrigin"][0]["city_id"],
                    "service_type_id" => $service["service_type_id"],
                    "service_sub_category_id" => $service["service_sub_category_id"],
                    "service_name" => $service["service_translations"][0]["name"],
                    "equivalence" => true,
                    "rate_order" => $service["rate_order"],
                    "rates" => [],
                    "inclusions" => $inclusions,
                    "markup_service" => $service['markup_service'],
                ]);
            }
        }


        //Todo Servicios sin equivalencia
        foreach ($_services as $service) {
            if (count($service["serviceEquivAssociation"]) == 0 && !in_array($service["aurora_code"],
                    $code_services_with_equivalence) && count($service["service_rate"]) > 0) {
                $aurora_code_sim = "";
                $aurora_code_pc = "";
                $aurora_code_na = "";
                $aurora_code_semi = "";
                array_push($code_services_with_equivalence, $service["aurora_code"]);
                if ($service["service_type_id"] == 1) {
                    $aurora_code_sim = $service["aurora_code"];
                }
                if ($service["service_type_id"] == 2) {
                    $aurora_code_pc = $service["aurora_code"];
                }
                if ($service["service_type_id"] == 3) {
                    $aurora_code_na = $service["aurora_code"];
                }
                if ($service["service_type_id"] == 3) {
                    $aurora_code_semi = $service["aurora_code"];
                }

                $inclusions = [
                    [
                        "day" => 1,
                        "inclusion_name" => "",
                    ]
                ];
                $day = 1;
                foreach ($service["inclusions"] as $inclusion) {
                    if ($inclusion["day"] > $day) {
                        $day = $inclusion["day"];
                        array_push($inclusions, [
                            "day" => $inclusion["day"],
                            "inclusion_name" => "",
                        ]);
                    }
                }
                foreach ($inclusions as $index => $inclusion) {
                    foreach ($service["inclusions"] as $service_inclusion) {
                        if ($service_inclusion["day"] == $inclusion["day"]) {
                            if (count($service_inclusion["inclusions"]["translations"]) > 0) {
                                $inclusions[$index]["inclusion_name"] .= $service_inclusion["inclusions"]["translations"][0]["value"] . ',';
                            }
                        }
                    }
                }

                array_push($services_new, [
                    "aurora_code" => $service["aurora_code"],
                    "aurora_code_sim" => $aurora_code_sim,
                    "aurora_code_pc" => $aurora_code_pc,
                    "aurora_code_na" => $aurora_code_na,
                    "aurora_code_semi" => $aurora_code_semi,
                    "service_city_id" => $service["serviceOrigin"][0]["city_id"],
                    "service_type_id" => $service["service_type_id"],
                    "service_sub_category_id" => $service["service_sub_category_id"],
                    "service_name" => $service["service_translations"][0]["name"],
                    "equivalence" => false,
                    "rate_order" => $service["rate_order"],
                    "rates" => [],
                    "markup_service" => $service['markup_service'],
                    "inclusions" => $inclusions
                ]);
            }
        }


        //Add Service Rates
        foreach ($services_new as $index_service_new => $service_new) {
            foreach ($_services as $service) {
                if ($service_new["aurora_code"] == $service["aurora_code"]) {
                    foreach ($service["service_rate"] as $rate) {
                        array_push($services_new[$index_service_new]["rates"], [
                            "rate_id" => $rate["id"],
                            "rate_name" => $rate["name"],
                            "rate_plans" => [],
                            "markup_rate_plan" => $rate["markup_rate_plan"],
                        ]);
                    }
                }
            }
        }
        //End Service Rates


        //Add Service Rate Plans
        foreach ($services_new as $index_service_new => $service_new) {
            foreach ($service_new["rates"] as $index_service_rate => $service_new_rate) {
                if (count($service_new_rate["rate_plans"]) == 0) {
                    foreach ($_services as $service) {
                        if ($service["aurora_code"] == $service_new["aurora_code"]) {
                            foreach ($service["service_rate"] as $service_rate) {
                                if ($service_rate["id"] == $service_new_rate["rate_id"]) {
                                    if (count($service_rate["service_rate_plans"]) > 0) {
                                        if (count($services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"]) == 0) {
                                            $ranges_date = [];
                                            foreach ($service_rate["service_rate_plans"] as $service_rate_plan) {
                                                if ($service_rate_plan["pax_from"] == 1 && $service_rate_plan["pax_to"] == 1) {
                                                    array_push($ranges_date, [
                                                        "date_from" => $service_rate_plan["date_from"],
                                                        "date_to" => $service_rate_plan["date_to"],
                                                        "policy_name" => $service_rate_plan["policy"]["name"]
                                                    ]);
                                                }
                                            }
                                            if (count($ranges_date) > 1) {
                                                $range_date_max = 0;
                                                $range_selected = [];

                                                foreach ($ranges_date as $range_date) {
                                                    $date_from = Carbon::parse($range_date["date_from"]);

                                                    $range_date_difference_in_days = $date_from->diffInDays(Carbon::parse($range_date["date_to"]));

                                                    if ($range_date_difference_in_days > $range_date_max) {
                                                        $range_date_max = $range_date_difference_in_days;
                                                        $range_selected = $range_date;
                                                    }
                                                }
                                                if (!empty($range_selected)) {
                                                    array_push($services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"],
                                                        [
                                                            "date_from" => $range_selected["date_from"],
                                                            "date_to" => $range_selected["date_to"],
                                                            "policy_name" => $range_selected["policy_name"],
                                                            "ranges" => $ranges,
                                                            "ranges_equivalence" => $ranges
                                                        ]);
                                                }
                                            } else {
                                                array_push($services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"],
                                                    [
                                                        "date_from" => $service_rate["service_rate_plans"][0]["date_from"],
                                                        "date_to" => $service_rate["service_rate_plans"][0]["date_to"],
                                                        "policy_name" => $service_rate["service_rate_plans"][0]["policy"]["name"],
                                                        "ranges" => $ranges,
                                                        "ranges_equivalence" => $ranges
                                                    ]);
                                            }
                                        }
                                    }
                                }
                            }
                        }

                    }
                }
            }

        }
        //End Service Rate Plans

        //Add service Rate Plans Ranges
        foreach ($services_new as $index_service_new => $service_new) {
            foreach ($service_new["rates"] as $index_service_rate => $service_new_rate) {
                foreach ($service_new_rate["rate_plans"] as $index_service_new_rate_plan => $service_new_rate_plan) {
                    foreach ($_services as $service) {
                        if ($service["aurora_code"] == $service_new["aurora_code"]) {
                            $markup = $markup_client;
                            foreach ($service["service_rate"] as $service_rate) {
                                if ($service_rate["id"] == $service_new_rate["rate_id"]) {
                                    if (count($service_rate['markup_rate_plan']) > 0) {
                                        $markup = $service_rate['markup_rate_plan'][0]['markup'];
                                    } elseif (count($service_new['markup_service']) > 0) {
                                        $markup = $service_new['markup_service'][0]['markup'];
                                    }
                                    foreach ($service_rate["service_rate_plans"] as $service_rate_plan) {
                                        if ($service_rate_plan["date_from"] == $service_new_rate_plan["date_from"] &&
                                            $service_rate_plan["date_to"] == $service_new_rate_plan["date_to"]) {
                                            if ($service_new["equivalence"]) {
                                                foreach ($services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"] as $index_service_new_range_equivalence => $range_equivalence) {
                                                    if (count($service["serviceEquivAssociation"][0]["service"]["service_rate"]) > 0) {
                                                        foreach ($service["serviceEquivAssociation"][0]["service"]["service_rate"][0]["service_rate_plans"] as $service_rate_plan_equivalence) {
                                                            if ($service_rate_plan_equivalence["pax_from"] >= $range_equivalence["pax_from"] && $service_rate_plan_equivalence["pax_to"] <= $range_equivalence["pax_to"]) {

                                                                $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_adult"] = (float)roundLito(((float)($service_rate_plan_equivalence["price_adult"] + ($service_rate_plan_equivalence["price_adult"] * ($markup / 100)))));
                                                                $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_child"] = (float)roundLito((float)($service_rate_plan_equivalence["price_child"] + ($service_rate_plan_equivalence["price_child"] * ($markup / 100))));
                                                                $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_infant"] = (float)roundLito((float)($service_rate_plan_equivalence["price_infant"] + ($service_rate_plan_equivalence["price_infant"] * ($markup / 100))));
                                                                $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_guide"] = (float)roundLito((float)($service_rate_plan_equivalence["price_guide"] + ($service_rate_plan_equivalence["price_guide"] * ($markup / 100))));
                                                                $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["flag_migrate"] = $service_rate_plan_equivalence["flag_migrate"];
                                                            } else {
                                                                if ($service_rate_plan_equivalence["pax_from"] <= $range_equivalence["pax_from"] && $service_rate_plan_equivalence["pax_to"] >= $range_equivalence["pax_to"]) {
                                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_adult"] = (float)roundLito(((float)($service_rate_plan_equivalence["price_adult"] + ($service_rate_plan_equivalence["price_adult"] * ($markup / 100)))));
                                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_child"] = (float)roundLito((float)($service_rate_plan_equivalence["price_child"] + ($service_rate_plan_equivalence["price_child"] * ($markup / 100))));
                                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_infant"] = (float)roundLito((float)($service_rate_plan_equivalence["price_infant"] + ($service_rate_plan_equivalence["price_infant"] * ($markup / 100))));
                                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_guide"] = (float)roundLito((float)($service_rate_plan_equivalence["price_guide"] + ($service_rate_plan_equivalence["price_guide"] * ($markup / 100))));
                                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["flag_migrate"] = $service_rate_plan_equivalence["flag_migrate"];

                                                                }
                                                            }
                                                        }
                                                    }
                                                }

                                            }
                                            foreach ($services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"] as $index_service_new_range => $range) {
                                                if ($service_rate_plan["pax_from"] >= $range["pax_from"] && $service_rate_plan["pax_to"] <= $range["pax_to"]) {
                                                    //
                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_adult"] = (float)roundLito(((float)($service_rate_plan["price_adult"] + ($service_rate_plan["price_adult"] * ($markup / 100)))));
                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_child"] = (float)roundLito((float)($service_rate_plan["price_child"] + ($service_rate_plan["price_child"] * ($markup / 100))));
                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_infant"] = (float)roundLito((float)($service_rate_plan["price_infant"] + ($service_rate_plan["price_infant"] * ($markup / 100))));
                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_guide"] = (float)roundLito((float)($service_rate_plan["price_guide"] + ($service_rate_plan["price_guide"] * ($markup / 100))));
                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["flag_migrate"] = $service_rate_plan["flag_migrate"];
                                                } else {
                                                    if ($service_rate_plan["pax_from"] <= $range["pax_from"] && $service_rate_plan["pax_to"] >= $range["pax_to"]) {
                                                        $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_adult"] = (float)roundLito(((float)($service_rate_plan["price_adult"] + ($service_rate_plan["price_adult"] * ($markup / 100)))));
                                                        $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_child"] = (float)roundLito((float)($service_rate_plan["price_child"] + ($service_rate_plan["price_child"] * ($markup / 100))));
                                                        $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_infant"] = (float)roundLito((float)($service_rate_plan["price_infant"] + ($service_rate_plan["price_infant"] * ($markup / 100))));
                                                        $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_guide"] = (float)roundLito((float)($service_rate_plan["price_guide"] + ($service_rate_plan["price_guide"] * ($markup / 100))));
                                                        $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["flag_migrate"] = $service_rate_plan["flag_migrate"];

                                                    }
                                                }
                                            }
                                        }
                                    }

                                }
                            }
                        }
                    }
                }
            }
        }
        //End service Rate plans Ranges

        //llenar Arreglo de servicios
        foreach ($services_new as $service) {
            foreach ($data["cities"] as $index_data_city => $city) {
                if ($city["city_id"] == $service["service_city_id"]) {
                    foreach ($city["categories"] as $index_data_category => $category) {
                        foreach ($category["subcategories"] as $index_data_subcategory => $subcategory) {
                            if ($subcategory["subcategory_id"] == $service["service_sub_category_id"]) {
                                array_push($data["cities"][$index_data_city]["categories"][$index_data_category]["subcategories"][$index_data_subcategory]["services"],
                                    $service);
                            }
                        }
                    }
                }
            }
        }

        //        dd($data["cities"]);


        //Proceso para limpiar el array
        //delete subcategories
        foreach ($data["cities"] as $index_new_city => $city) {
            foreach ($city["categories"] as $index_new_category => $category) {
                foreach ($category["subcategories"] as $index_new_subcategory => $subcategory) {
                    if (count($subcategory["services"]) == 0) {
                        unset($data["cities"][$index_new_city]["categories"][$index_new_category]["subcategories"][$index_new_subcategory]);
                    }
                }
            }
        }
        //End delete subcategories

        //delete categories
        foreach ($data["cities"] as $index_new_city => $city) {
            foreach ($city["categories"] as $index_new_category => $category) {
                if (count($category["subcategories"]) == 0) {
                    unset($data["cities"][$index_new_city]["categories"][$index_new_category]);
                }
            }
        }
        //End delete categories
        //delete cities
        foreach ($data["cities"] as $index_new_city => $city) {
            if (count($city["categories"]) == 0) {
                unset($data["cities"][$index_new_city]);
            }
        }

        return $data;
    }

    public function ratesServicesByYearUpdate($service_year, $lang, $client_id)
    {

        $service_year = $service_year;
        $language_id = Language::where('state', 1)->where('iso', $lang)->first()->id;

        $data = [
            "cities" => []
        ];

        $markup_new = Markup::where('client_id', $client_id)->where('period', $service_year)->first();

        if ($markup_new == null) {
            $markup_client = 17;
        } else {
            $markup_client = (int)$markup_new->service;
        }

        $client = Client::where('id', $client_id)->first();
        //Todo Variable para guardar las categorias
        $service_categories_new = [];

        //Todo Buscamos las categorias
        $service_categories = ServiceCategory::with([
            'translations' => function ($query) use ($language_id) {
                $query->select(['object_id', 'value']);
                $query->where('type', 'servicecategory');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceSubCategory' => function ($query) use ($language_id) {
                $query->select(['id', 'service_category_id', 'order']);
                $query->with([
                    'translations' => function ($query) use ($language_id) {
                        $query->select(['object_id', 'value']);
                        $query->where('type', 'servicesubcategory');
                        $query->where('language_id', $language_id);
                    }
                ]);
                $query->orderBy('order');
            }
        ])->orderBy('order')->get(['id', 'order']);

        //Todo Recorremos las categorias y las guardamos
        foreach ($service_categories as $category) {
            $service_categories_new [] = [
                "service_category_id" => $category["id"],
                "category_name" => $category["translations"][0]["value"],
                "subcategories" => []
            ];
        }

        //Todo Recorremos las categorias y las subcategorias y lo guadarmos
        foreach ($service_categories_new as $index_service_category_new => $category_new) {
            foreach ($service_categories as $category) {
                if ($category_new["service_category_id"] == $category["id"]) {
                    foreach ($category["serviceSubCategory"] as $subcategory) {
                        array_push($service_categories_new[$index_service_category_new]["subcategories"], [
                            "subcategory_id" => $subcategory["id"],
                            "subcategory_name" => $subcategory["translations"][0]["value"],
                            "services" => []
                        ]);
                    }
                }
            }
        }

        //Todo Consulto los estados del pais Peru (89)
        $states_id = State::where('country_id', 89)->pluck('id');

        //Todo Consulto las ciudades
        $cities = City::with([
            'translations' => function ($query) use ($language_id) {
                $query->select(['object_id', 'value']);
                $query->where('type', 'city');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'order_rate' => function ($query) {
                $query->select(['city_id', 'order']);
            }
        ])->whereIn('state_id', $states_id)->get(['id', 'iso'])->sortBy('order_rate.order')->values();


        //Todo Agrego y creo un arreglo de rangos desde 1 hasta 40 pax
        $ranges = [];
        for ($i = 1; $i <= 40; $i++) {
            array_push($ranges, [
                "pax_from" => $i,
                "pax_to" => $i,
                "price_adult" => "",
                "price_child" => "",
                "price_infant" => "",
                "price_guide" => "",
                "flag_migrate" => "",
            ]);
        }

        //Llenar arreglo de ciudades

        foreach ($cities as $city) {
            array_push($data["cities"], [
                "city_id" => $city["id"],
                "city_name" => $city["translations"][0]["value"],
                "date_download" => Carbon::now()->format('d/m/Y'),
                "client_code" => $client->code,
                "client_name" => $client->name,
                "ranges" => $ranges,
                "categories" => $service_categories_new
            ]);
        }


        $services_locked = [];
        if ($client->id != 15766) { // 5NEG
            $services_locked = ServiceClient::where('client_id', $client->id)
                ->where('period', $service_year)->pluck('service_id');
        }
        $services_new = [];
        $_services = collect();
        $code_services_with_equivalence = [];

        Service::with([
            'serviceOrigin' => function ($query) {
                $query->select(['id', 'service_id', 'country_id', 'state_id', 'city_id', 'zone_id']);
            }
        ])->with([
            'service_rate' => function ($query) use ($service_year, $language_id, $client_id) {
                $query->select(['id', 'name', 'service_id', 'rate', 'status']);
                $query->where('status', 1);
                $query->where('rate', 1);
                $query->with([
                    'service_rate_plans' => function ($query) use ($service_year) {
                        $query->select([
                            'id',
                            'service_rate_id',
                            'service_cancellation_policy_id',
                            'user_id',
                            'date_from',
                            'date_to',
                            'pax_from',
                            'pax_to',
                            'price_adult',
                            'price_child',
                            'price_infant',
                            'price_guide',
                            'flag_migrate',
                            'status'
                        ]);
                        $query->where('date_from', '>=', $service_year . '-01-01');
                        $query->where('date_to', '<=', $service_year . '-12-31');
                        $query->with([
                            'policy' => function ($query) {
                                $query->where('status', 1);
                                $query->with([
                                    'parameters' => function ($query) {
                                        $query->with('penalty');
                                    }
                                ]);
                            }
                        ]);
                        $query->orderBy('date_from', 'asc');
                    }
                ])->with([
                    'markup_rate_plan' => function ($query) use ($service_year, $client_id) {
                        $query->select('markup', 'period', 'service_rate_id');
                        $query->where('client_id', $client_id);
                        $query->where('period', $service_year);
                    }
                ])->with([
                    'translations' => function ($query) use ($language_id) {
                        $query->select(['object_id', 'value']);
                        $query->where('language_id', $language_id);
                    }
                ]);

            }
        ])
            ->with([
                'inclusions' => function ($query) use ($language_id) {
                    $query->select(['id', 'service_id', 'day', 'inclusion_id', 'include', 'see_client', 'order']);
                    $query->where('include', 1);
                    $query->where('see_client', 1);
                    $query->orderBy('day');
                    $query->orderBy('order');
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
                                    $query->select(['object_id', 'value']);
                                    $query->where('language_id', $language_id);
                                }
                            ]);
                        }
                    ]);
                }
            ])
            ->with([
                'service_translations' => function ($query) use ($language_id) {
                    $query->select([
                        'name',
                        'name_commercial',
                        'service_id'
                    ]);
                    $query->where('language_id', $language_id);
                }
            ])
            ->with([
                'serviceEquivAssociation' => function ($query) use ($service_year, $language_id) {
                    $query->with([
                        'service' => function ($query) use ($service_year, $language_id) {
                            $query->select(['id', 'name', 'aurora_code', 'service_type_id', 'service_sub_category_id']);
                            $query->with([
                                'service_rate' => function ($query) use ($service_year, $language_id) {
                                    $query->select('id', 'name', 'service_id', 'status');
                                    $query->where('status', 1);
                                    $query->where('rate', 1);
                                    $query->with([
                                        'service_rate_plans' => function ($query) use ($service_year) {
                                            $query->select([
                                                'id',
                                                'service_rate_id',
                                                'service_cancellation_policy_id',
                                                'user_id',
                                                'date_from',
                                                'date_to',
                                                'pax_from',
                                                'pax_to',
                                                'price_adult',
                                                'price_child',
                                                'price_infant',
                                                'price_guide',
                                                'flag_migrate',
                                                'status'
                                            ]);
                                            $query->where('date_from', '>=', $service_year . '-01-01');
                                            $query->where('date_to', '<=', $service_year . '-12-31');
                                            $query->with([
                                                'policy' => function ($query) {
                                                    $query->where('status', 1);
                                                    $query->with([
                                                        'parameters' => function ($query) {
                                                            $query->with('penalty');
                                                        }
                                                    ]);
                                                }
                                            ]);
                                            $query->orderBy('date_from', 'asc');
                                        }
                                    ])->with([
                                        'translations' => function ($query) use ($language_id) {
                                            $query->select(['object_id', 'value']);
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ]);
                }
            ])
            ->with([
                'markup_service' => function ($query) use ($service_year, $client_id) {
                    $query->select(['id', 'service_id', 'markup']);
                    $query->where('client_id', $client_id);
                    $query->where('period', $service_year);
                }
            ])
            ->whereHas('service_rate', function ($q) {
                $q->where('rate', 1);
            })
            ->whereDoesntHave('client_services')
            ->where('status', 1)
            ->whereNotIn('id', $services_locked)
//            ->whereIn('id', [2991, 2845, 3386])
//            ->whereIn('id', [2517, 1299, 2158])
//            ->whereIn('id', [1299, 2517])
            ->select([
                'id',
                'aurora_code',
                'name',
                'service_type_id',
                'classification_id',
                'service_sub_category_id',
                'status'
            ])
            ->limit(10)
            ->orderBy('rate_order')
            ->chunk(100, function ($services) use ($_services) {
                foreach ($services as $service) {
                    $_services->add($service);
                }
            });

//        return $_services;

        //Todo servicios con equivalencia
        foreach ($_services as $service) {
            if (count($service["serviceEquivAssociation"]) > 0 && !in_array($service["aurora_code"],
                    $code_services_with_equivalence) && count($service["service_rate"]) > 0) {
                $aurora_code_sim = "";
                $aurora_code_pc = "";
                $aurora_code_na = "";
                $aurora_code_semi = "";
                array_push($code_services_with_equivalence, $service["aurora_code"]);
                array_push($code_services_with_equivalence,
                    $service["serviceEquivAssociation"][0]["service"]["aurora_code"]);
                //Todo Compartido
                if ($service["service_type_id"] == 1) {
                    $aurora_code_sim = $service["aurora_code"];
                }
                //Todo privado
                if ($service["service_type_id"] == 2) {
                    $aurora_code_pc = $service["aurora_code"];
                }
                //Todo Ninguno
                if ($service["service_type_id"] == 3) {
                    $aurora_code_na = $service["aurora_code"];
                }

                //Todo Semi privado
                if ($service["service_type_id"] == 4) {
                    $aurora_code_semi = $service["aurora_code"];
                }


                if ($service["serviceEquivAssociation"][0]["service"]["service_type_id"] == 1) {
                    $aurora_code_sim = $service["serviceEquivAssociation"][0]["service"]["aurora_code"];
                }

                if ($service["serviceEquivAssociation"][0]["service"]["service_type_id"] == 2) {
                    $aurora_code_pc = $service["serviceEquivAssociation"][0]["service"]["aurora_code"];
                }

                if ($service["serviceEquivAssociation"][0]["service"]["service_type_id"] == 3) {
                    $aurora_code_na = $service["serviceEquivAssociation"][0]["service"]["aurora_code"];
                }

                if ($service["serviceEquivAssociation"][0]["service"]["service_type_id"] == 4) {
                    $aurora_code_semi = $service["serviceEquivAssociation"][0]["service"]["aurora_code"];
                }

                $inclusions = [
                    [
                        "day" => 1,
                        "inclusion_name" => "",
                    ]
                ];
                $day = 1;
                foreach ($service["inclusions"] as $inclusion) {
                    if ($inclusion["day"] > $day) {
                        $day = $inclusion["day"];
                        array_push($inclusions, [
                            "day" => $inclusion["day"],
                            "inclusion_name" => "",
                        ]);
                    }
                }
                foreach ($inclusions as $index => $inclusion) {
                    foreach ($service["inclusions"] as $service_inclusion) {
                        if ($service_inclusion["day"] == $inclusion["day"]) {
                            if (count($service_inclusion["inclusions"]["translations"]) > 0) {
                                $inclusions[$index]["inclusion_name"] .= $service_inclusion["inclusions"]["translations"][0]["value"] . ',';
                            }
                        }
                    }
                }

                $services_new[] = [
                    "aurora_code" => $service["aurora_code"],
                    "aurora_code_sim" => $aurora_code_sim,
                    "aurora_code_pc" => $aurora_code_pc,
                    "aurora_code_na" => $aurora_code_na,
                    "aurora_code_semi" => $aurora_code_semi,
                    "service_city_id" => $service["serviceOrigin"][0]["city_id"],
                    "service_type_id" => $service["service_type_id"],
                    "service_sub_category_id" => $service["service_sub_category_id"],
                    "service_name" => $service["service_translations"][0]["name"],
                    "equivalence" => true,
                    "rate_order" => $service["rate_order"],
                    "rates" => [],
                    "inclusions" => $inclusions,
                    "markup_service" => $service['markup_service'],
                ];
            }
        }


        //Todo Servicios sin equivalencia
        foreach ($_services as $service) {
            if (count($service["serviceEquivAssociation"]) == 0 && !in_array($service["aurora_code"],
                    $code_services_with_equivalence) && count($service["service_rate"]) > 0) {
                $aurora_code_sim = "";
                $aurora_code_pc = "";
                $aurora_code_na = "";
                $aurora_code_semi = "";
                array_push($code_services_with_equivalence, $service["aurora_code"]);
                if ($service["service_type_id"] == 1) {
                    $aurora_code_sim = $service["aurora_code"];
                }
                if ($service["service_type_id"] == 2) {
                    $aurora_code_pc = $service["aurora_code"];
                }
                if ($service["service_type_id"] == 3) {
                    $aurora_code_na = $service["aurora_code"];
                }
                if ($service["service_type_id"] == 3) {
                    $aurora_code_semi = $service["aurora_code"];
                }

                $inclusions = [
                    [
                        "day" => 1,
                        "inclusion_name" => "",
                    ]
                ];
                $day = 1;
                foreach ($service["inclusions"] as $inclusion) {
                    if ($inclusion["day"] > $day) {
                        $day = $inclusion["day"];
                        $inclusions[] = [
                            "day" => $inclusion["day"],
                            "inclusion_name" => "",
                        ];
                    }
                }
                foreach ($inclusions as $index => $inclusion) {
                    foreach ($service["inclusions"] as $service_inclusion) {
                        if ($service_inclusion["day"] == $inclusion["day"]) {
                            if (count($service_inclusion["inclusions"]["translations"]) > 0) {
                                $inclusions[$index]["inclusion_name"] .= $service_inclusion["inclusions"]["translations"][0]["value"] . ',';
                            }
                        }
                    }
                }

                $services_new[] = [
                    "aurora_code" => $service["aurora_code"],
                    "aurora_code_sim" => $aurora_code_sim,
                    "aurora_code_pc" => $aurora_code_pc,
                    "aurora_code_na" => $aurora_code_na,
                    "aurora_code_semi" => $aurora_code_semi,
                    "service_city_id" => $service["serviceOrigin"][0]["city_id"],
                    "service_type_id" => $service["service_type_id"],
                    "service_sub_category_id" => $service["service_sub_category_id"],
                    "service_name" => $service["service_translations"][0]["name"],
                    "equivalence" => false,
                    "rate_order" => $service["rate_order"],
                    "rates" => [],
                    "markup_service" => $service['markup_service'],
                    "inclusions" => $inclusions
                ];
            }
        }


        //Add Service Rates
        foreach ($services_new as $index_service_new => $service_new) {
            foreach ($_services as $service) {
                if ($service_new["aurora_code"] == $service["aurora_code"]) {
                    foreach ($service["service_rate"] as $rate) {
                        $services_new[$index_service_new]["rates"][] = [
                            "rate_id" => $rate["id"],
                            "rate_name" => $rate["name"],
                            "rate_plans" => [],
                            "markup_rate_plan" => $rate["markup_rate_plan"],
                        ];
                    }
                }
            }
        }
        //End Service Rates

        //Add Service Rate Plans
        foreach ($services_new as $index_service_new => $service_new) {
            foreach ($service_new["rates"] as $index_service_rate => $service_new_rate) {
                if (count($service_new_rate["rate_plans"]) == 0) {
                    foreach ($_services as $service) {
                        if ($service["aurora_code"] == $service_new["aurora_code"]) {
                            foreach ($service["service_rate"] as $service_rate) {
                                if ($service_rate["id"] == $service_new_rate["rate_id"]) {
                                    if (count($service_rate["service_rate_plans"]) > 0) {
                                        if (count($services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"]) == 0) {
                                            $datesUniques = [];
                                            foreach ($service_rate["service_rate_plans"] as $service_rate_plan) {
                                                $dateFrom = Carbon::parse($service_rate_plan['date_from'])->locale($lang)->translatedFormat('d-M y');
                                                $dateTo = Carbon::parse($service_rate_plan['date_to'])->locale($lang)->translatedFormat('d-M y');
                                                $dateUnique = "$dateFrom-$dateTo";
                                                if (!in_array($dateUnique, $datesUniques)) {
                                                    // Si no existe, agrégala al arreglo de fechas únicas
                                                    $datesUniques[] = $dateUnique;
                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][] = [
                                                        "date_from_label" => ucwords($dateFrom, "-"),
                                                        "date_to_label" => ucwords($dateTo, "-") ,
                                                        "date_from" => $service_rate_plan['date_from'],
                                                        "date_to" => $service_rate_plan['date_to'],
                                                        "policy_name" => $service_rate_plan["policy"]["name"],
                                                        "ranges" => $ranges,
                                                        "ranges_equivalence" => $ranges
                                                    ];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                    }
                }
            }

        }
        //End Service Rate Plans

        //Add service Rate Plans Ranges
        foreach ($services_new as $index_service_new => $service_new) {
            foreach ($service_new["rates"] as $index_service_rate => $service_new_rate) {
                foreach ($service_new_rate["rate_plans"] as $index_service_new_rate_plan => $service_new_rate_plan) {
                    foreach ($_services as $service) {
                        if ($service["aurora_code"] == $service_new["aurora_code"]) {
                            $markup = $markup_client;
                            foreach ($service["service_rate"] as $service_rate) {
                                if ($service_rate["id"] == $service_new_rate["rate_id"]) {
                                    if (count($service_rate['markup_rate_plan']) > 0) {
                                        $markup = $service_rate['markup_rate_plan'][0]['markup'];
                                    } elseif (count($service_new['markup_service']) > 0) {
                                        $markup = $service_new['markup_service'][0]['markup'];
                                    }
                                    foreach ($service_rate["service_rate_plans"] as $service_rate_plan) {
                                        if ($service_rate_plan["date_from"] == $service_new_rate_plan["date_from"] &&
                                            $service_rate_plan["date_to"] == $service_new_rate_plan["date_to"]) {
                                            if ($service_new["equivalence"]) {
                                                foreach ($services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"] as $index_service_new_range_equivalence => $range_equivalence) {
                                                    if (count($service["serviceEquivAssociation"][0]["service"]["service_rate"]) > 0) {
                                                        foreach ($service["serviceEquivAssociation"][0]["service"]["service_rate"][0]["service_rate_plans"] as $service_rate_plan_equivalence) {
                                                            if ($service_rate_plan_equivalence["pax_from"] >= $range_equivalence["pax_from"] && $service_rate_plan_equivalence["pax_to"] <= $range_equivalence["pax_to"]) {

                                                                $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_adult"] = (float)roundLito(((float)($service_rate_plan_equivalence["price_adult"] + ($service_rate_plan_equivalence["price_adult"] * ($markup / 100)))));
                                                                $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_child"] = (float)roundLito((float)($service_rate_plan_equivalence["price_child"] + ($service_rate_plan_equivalence["price_child"] * ($markup / 100))));
                                                                $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_infant"] = (float)roundLito((float)($service_rate_plan_equivalence["price_infant"] + ($service_rate_plan_equivalence["price_infant"] * ($markup / 100))));
                                                                $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_guide"] = (float)roundLito((float)($service_rate_plan_equivalence["price_guide"] + ($service_rate_plan_equivalence["price_guide"] * ($markup / 100))));
                                                                $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["flag_migrate"] = $service_rate_plan_equivalence["flag_migrate"];
                                                            } else {
                                                                if ($service_rate_plan_equivalence["pax_from"] <= $range_equivalence["pax_from"] && $service_rate_plan_equivalence["pax_to"] >= $range_equivalence["pax_to"]) {
                                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_adult"] = (float)roundLito(((float)($service_rate_plan_equivalence["price_adult"] + ($service_rate_plan_equivalence["price_adult"] * ($markup / 100)))));
                                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_child"] = (float)roundLito((float)($service_rate_plan_equivalence["price_child"] + ($service_rate_plan_equivalence["price_child"] * ($markup / 100))));
                                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_infant"] = (float)roundLito((float)($service_rate_plan_equivalence["price_infant"] + ($service_rate_plan_equivalence["price_infant"] * ($markup / 100))));
                                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_guide"] = (float)roundLito((float)($service_rate_plan_equivalence["price_guide"] + ($service_rate_plan_equivalence["price_guide"] * ($markup / 100))));
                                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["flag_migrate"] = $service_rate_plan_equivalence["flag_migrate"];

                                                                }
                                                            }
                                                        }
                                                    }
                                                }

                                            }
                                            foreach ($services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"] as $index_service_new_range => $range) {
                                                if ($service_rate_plan["pax_from"] >= $range["pax_from"] && $service_rate_plan["pax_to"] <= $range["pax_to"]) {
                                                    //
                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_adult"] = (float)roundLito(((float)($service_rate_plan["price_adult"] + ($service_rate_plan["price_adult"] * ($markup / 100)))));
                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_child"] = (float)roundLito((float)($service_rate_plan["price_child"] + ($service_rate_plan["price_child"] * ($markup / 100))));
                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_infant"] = (float)roundLito((float)($service_rate_plan["price_infant"] + ($service_rate_plan["price_infant"] * ($markup / 100))));
                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_guide"] = (float)roundLito((float)($service_rate_plan["price_guide"] + ($service_rate_plan["price_guide"] * ($markup / 100))));
                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["flag_migrate"] = $service_rate_plan["flag_migrate"];
                                                } else {
                                                    if ($service_rate_plan["pax_from"] <= $range["pax_from"] && $service_rate_plan["pax_to"] >= $range["pax_to"]) {
                                                        $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_adult"] = (float)roundLito(((float)($service_rate_plan["price_adult"] + ($service_rate_plan["price_adult"] * ($markup / 100)))));
                                                        $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_child"] = (float)roundLito((float)($service_rate_plan["price_child"] + ($service_rate_plan["price_child"] * ($markup / 100))));
                                                        $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_infant"] = (float)roundLito((float)($service_rate_plan["price_infant"] + ($service_rate_plan["price_infant"] * ($markup / 100))));
                                                        $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_guide"] = (float)roundLito((float)($service_rate_plan["price_guide"] + ($service_rate_plan["price_guide"] * ($markup / 100))));
                                                        $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["flag_migrate"] = $service_rate_plan["flag_migrate"];

                                                    }
                                                }
                                            }
                                        }
                                    }

                                }
                            }
                        }
                    }
                }
            }
        }
        //End service Rate plans Ranges
        //llenar Arreglo de servicios
        foreach ($services_new as $service) {
            foreach ($data["cities"] as $index_data_city => $city) {
                if ($city["city_id"] == $service["service_city_id"]) {
                    foreach ($city["categories"] as $index_data_category => $category) {
                        foreach ($category["subcategories"] as $index_data_subcategory => $subcategory) {
                            if ($subcategory["subcategory_id"] == $service["service_sub_category_id"]) {
                                array_push($data["cities"][$index_data_city]["categories"][$index_data_category]["subcategories"][$index_data_subcategory]["services"],
                                    $service);
                            }
                        }
                    }
                }
            }
        }

        //        dd($data["cities"]);


        //Proceso para limpiar el array
        //delete subcategories
        foreach ($data["cities"] as $index_new_city => $city) {
            foreach ($city["categories"] as $index_new_category => $category) {
                foreach ($category["subcategories"] as $index_new_subcategory => $subcategory) {
                    if (count($subcategory["services"]) == 0) {
                        unset($data["cities"][$index_new_city]["categories"][$index_new_category]["subcategories"][$index_new_subcategory]);
                    }
                }
            }
        }
        //End delete subcategories

        //delete categories
        foreach ($data["cities"] as $index_new_city => $city) {
            foreach ($city["categories"] as $index_new_category => $category) {
                if (count($category["subcategories"]) == 0) {
                    unset($data["cities"][$index_new_city]["categories"][$index_new_category]);
                }
            }
        }
        //End delete categories
        //delete cities
        foreach ($data["cities"] as $index_new_city => $city) {
            if (count($city["categories"]) == 0) {
                unset($data["cities"][$index_new_city]);
            }
        }

        return $data;
    }

    static function getSevicesOriginDestinationInHotel()
    {

        $services = Service::join('service_translations', function($join) {
            $join->on('services.id', '=', 'service_translations.service_id');
        })
        ->where('services.status', 1)
        ->whereIn('services.service_sub_category_id', [20,1,2 ])
        ->where('service_translations.language_id', 1)
        ->where(function ($query) {
            $query->where('services.name', 'like', '%hotel%')
                ->orWhere('service_translations.itinerary', 'like', '%hotel%');
        })
        ->get([
           'services.id',
           'services.name',
           'service_translations.itinerary'
        ]);

        return $services;
    }

    static function searchOrigin($service)
    {
       if (strpos($service, 'del hotel')  !== false
            or strpos($service, 'de hotel')  !== false
            // or strpos($service, 'el hotel')  !== false
            or strpos($service, 'de su hotel')  !== false
            or  strpos($service, 'de tu hotel')  !== false
            or  strpos($service, 'desde el hotel ')  !== false


        )
        {
            return 'H';
        }

        return '';
    }

    static function searchDestiny($service)
    {
       if (strpos($service, 'al hotel')  !== false
            or strpos($service, 'a hotel')  !== false
            or strpos($service, 'a su hotel')  !== false
            or strpos($service, 'a tu hotel')  !== false
            or  strpos($service, 'hasta el hotel ')  !== false
        )
        {
            return 'H';
        }

        return '';
    }

    static function getOriginDestination($service_reservations)
    {

        $services = self::getSevicesOriginDestinationInHotel();


        foreach($service_reservations as $service_reservation){
            $service = $services->where('id', $service_reservation->service_id)->first();

            if($service){

                $origin = self::searchOrigin($service->name);
                if(!$origin){
                    $origin = self::searchOrigin($service->itinerary);
                }

                $destination = self::searchDestiny($service->name);
                if(!$destination){
                    $destination = self::searchDestiny($service->itinerary);
                }


                $service_reservation->origin = $origin;
                $service_reservation->destination = $destination;

            }else{
                $service_reservation->origin = '';
                $service_reservation->destination = '';
            }
        }

        return $service_reservations;
    }

    public function service_translations_gtm()
    {
        return $this->hasMany('App\ServiceTranslation', 'service_id', 'id');
    }
}
