<?php

namespace App\Http\Controllers;

use App\BusinessRegion;
use App\City;
use App\Classification;
use App\Client;
use App\ClientSeller;
use App\ClientService;
use App\ClientServiceRated;
use App\Component;
use App\ComponentSubstitute;
use App\Country;
use App\CrossSelling;
use App\Currency;
use App\DeactivatableEntity;
use App\Experience;
use App\ExperienceService;
use App\Http\Traits\ClientServices;
use App\Http\Traits\Equivalence;
use App\Http\Traits\Package as TraitPackage;
use App\Http\Traits\Services;
use App\Http\Traits\Translations;
use App\Language;
use App\Mail\NotificationService;
use App\Markup;
use App\MarkupService;
use App\PackageService;
use App\PhysicalIntensity;
use App\ProgressBar;
use App\Requirement;
use App\RequirementService;
use App\Restriction;
use App\RestrictionService;
use App\Service;
use App\ServiceCategory;
use App\ServiceChild;
use App\ServiceClient;
use App\ServiceDestination;
use App\ServiceEquivalenceAssociation;
use App\ServiceOperation;
use App\ServiceSchedule;
use App\ServiceOperationActivity;
use App\ServiceOrigin;
use App\ServiceRate;
use App\ServiceRatePlan;
use App\ServiceSubCategory;
use App\ServiceSupplement;
use App\ServiceTranslation;
use App\ServiceType;
use App\State;
use App\Translation;
use App\Unit;
use App\UnitDuration;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Calculation\Database\DVar;

/**
 * Class ServiceController
 * @package App\Http\Controllers
 */
class ServiceController extends Controller
{

    use Translations, ClientServices, TraitPackage, Services, Equivalence;

    public function __construct()
    {
        $this->middleware('permission:services.read')->only('index');
        $this->middleware('permission:services.create')->only('store');
        $this->middleware('permission:services.update')->only('update');
        $this->middleware('permission:services.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $client_id = $this->getClientId($request);
        $paging = $request->input('page') ? $request->input('page') : 1;
        $origin = $request->input('origin');
        $destiny = $request->input('destiny');
        $service_type = $request->input('service_type');
        $service_category = $request->input('service_category');
        $service_name = $request->input('service_name');
        $status = $request->input('status');
        $limit = $request->input('limit');


        $lang = ($request->has('lang')) ? $request->input('lang') : 'es';
        $language = Language::where('iso', $lang)->first();
        if ($language->count() > 0 and isset($lang)) {
            $language_id = $language->id;
        } else {
            $lang = 'es';
            $language_id = 1;
        }

        $destiny_codes = explode(",", $destiny);
        $country_id = "";
        $state_id = "";
        $city_id = "";
        $zone_id = "";

        $origin_codes = explode(",", $origin);
        $origin_country_id = "";
        $origin_state_id = "";
        $origin_city_id = "";
        $origin_zone_id = "";

        for ($i = 0; $i < count($destiny_codes); $i++) {
            if ($i == 0) {
                $country_id = $destiny_codes[$i];
            }
            if ($i == 1) {
                $state_id = $destiny_codes[$i];
            }
            if ($i == 2) {
                $city_id = $destiny_codes[$i];
            }
            if ($i == 3) {
                $zone_id = $destiny_codes[$i];
            }
        }

        for ($i = 0; $i < count($origin_codes); $i++) {
            if ($i == 0) {
                $origin_country_id = $origin_codes[$i];
            }
            if ($i == 1) {
                $origin_state_id = $origin_codes[$i];
            }
            if ($i == 2) {
                $origin_city_id = $origin_codes[$i];
            }
            if ($i == 3) {
                $origin_zone_id = $origin_codes[$i];
            }
        }

        $services = Service::with([
            'serviceOrigin.state.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'state');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceOrigin.zone.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'zone');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceDestination.state.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'state');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceDestination.zone.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'zone');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceType.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'servicetype');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceSubCategory.serviceCategories.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'servicecategory');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'physical_intensity.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'physicalintensity');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'client_services' => function ($query) use ($client_id) {
                $query->select('id', 'service_id', 'client_id');
                $query->where('client_id', $client_id);
            }
        ])->with([
            'service_translations' => function ($query) use ($language_id) {
                $query->where('language_id', $language_id);
            }

        ])->with('languages_guide');


        if ($country_id != "") {
            $services->whereHas('serviceDestination', function ($query) use ($country_id) {
                $query->where('country_id', $country_id);
            });
        }

        if ($state_id != "") {
            $services->whereHas('serviceDestination', function ($query) use ($state_id) {
                $query->where('state_id', $state_id);
            });
        }

        if ($city_id != "") {
            $services->whereHas('serviceDestination', function ($query) use ($city_id) {
                $query->where('city_id', $city_id);
            });
        }

        if ($zone_id != "") {
            $services->whereHas('serviceDestination', function ($query) use ($zone_id) {
                $query->where('zone_id', $zone_id);
            });
        }

        // ----------------

        if ($origin_country_id != "") {
            $services->whereHas('serviceOrigin', function ($query) use ($origin_country_id) {
                $query->where('country_id', $origin_country_id);
            });
        }

        if ($origin_state_id != "") {
            $services->whereHas('serviceOrigin', function ($query) use ($origin_state_id) {
                $query->where('state_id', $origin_state_id);
            });
        }

        if ($origin_city_id != "") {
            $services->whereHas('serviceOrigin', function ($query) use ($origin_city_id) {
                $query->where('city_id', $origin_city_id);
            });
        }

        if ($origin_zone_id != "") {
            $services->whereHas('serviceOrigin', function ($query) use ($origin_zone_id) {
                $query->where('zone_id', $origin_zone_id);
            });
        }

//
        if ($service_type != "") {
            $services->whereHas('serviceSubCategory.serviceCategories', function ($query) use ($service_type) {
                $query->where('id', $service_type);
            });
        }

        if ($service_category != "") {
            $services->where('service_type_id', $service_category);
        }
        if ($status != "") {
            $services = $services->where('status', $status);
        }

        if ($service_name != "") {
            if (is_numeric($service_name)) {
                $services->where('id', $service_name);
            } else {
                $services->where('name', 'like', '%' . $service_name . '%')
                    ->orWhere('aurora_code', 'like', '%' . $service_name . '%');
            }

        }


        //Todo Cliente Ecommerce que crea sus propios servicios
        if (!empty($client_id)) {
            $services = $services->whereHas('client_services', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            });
        } else {
            $services = $services->whereDoesntHave('client_services');

        }

        $count = $services->count();

        if ($paging === 1) {
            $services = $services->take($limit)->orderBy('id', 'desc')->get(
                [
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
                    'allow_guide',
                    'allow_child',
                    'allow_infant',
                    'unit_id',
                    'unit_duration_id',
                    'service_type_id',
                    'classification_id',
                    'service_sub_category_id',
                    'user_id',
                    'date_solicitude',
                    'duration',
                    'status',
                    'compensation',
                    'tag_service_id',
                    'type'
                ]
            );
        } else {
            $services = $services->skip($limit * ($paging - 1))->take($limit)
                ->orderBy('id', 'desc')
                ->get(
                    [
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
                        'allow_guide',
                        'allow_child',
                        'allow_infant',
                        'unit_id',
                        'unit_duration_id',
                        'service_type_id',
                        'classification_id',
                        'service_sub_category_id',
                        'user_id',
                        'date_solicitude',
                        'duration',
                        'status',
                        'compensation',
                        'tag_service_id',
                        'type'
                    ]
                );
        }

        foreach ($services as $service) {
            $service->progress_bar_value = $service->progress_bars->sum('value');
        }

        $data = [
            'data' => $services,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $client_id = $this->getClientId($request);

            $validate = [];
            if (empty($client_id)) {
                $validate = [
                    'aurora_code' => 'unique:services,aurora_code',
                    'equivalence_aurora' => 'unique:services,equivalence_aurora'
                ];
            }

            $validator = Validator::make($request->all(), $validate);

            if ($validator->fails()) {
                return Response::json(['success' => false]);
            } else {
                DB::beginTransaction();
                if (empty($client_id)) {
                    $generate_aurora_code = strtoupper($request->input('aurora_code'));
                } else {
                    $origin = $request->input('origin');
                    $state = State::find($origin['state_id']);
                    $generate_aurora_code = createServiceAuroraCode(strtoupper($state->iso));
                }
                $physical_intensity_id = ($request->input("physical_intensity_id") && $request->input("physical_intensity_id") != null) ? $request->input("physical_intensity_id") : null;
                $service = new Service();
                $service->aurora_code = $generate_aurora_code;
                $service->name = $request->input('name');
                $service->currency_id = empty($client_id) ? $request->input('currency_id') : 2;
                $service->latitude = $request->input('latitude');
                $service->longitude = $request->input('longitude');
                $service->qty_reserve = $request->input('qty_reserve');
                $service->qty_reserve_client = $request->input('qty_reserve_client');
                $service->equivalence_aurora = empty($client_id) ? $request->input('equivalence_aurora') : 0;
                $service->affected_igv = $request->input('affected_igv');
                $service->include_accommodation = $request->input('include_accommodation');
                $service->unit_id = $request->input('unit_id');
                $service->unit_duration_id = $request->input('unitDuration_id');
                $service->affected_markup = $request->input('affected_markup');
                $service->unit_duration_reserve = empty($client_id) ? $request->input('unitDurationReserve_id') : 1;
                $service->service_type_id = $request->input('serviceType_id');
                $service->classification_id = $request->input('classification_id');
                $service->service_sub_category_id = $request->input('subCategory_id');
                $service->duration = $request->input('duration');
                $service->user_id = 1;
                $service->allow_guide = false;
                $service->allow_child = false;
                $service->allow_infant = false;
                $service->limit_confirm_hours = 0;
                $service->unit_duration_limit_confirmation = 1;
                $service->infant_min_age = 1;
                $service->infant_max_age = 1;
                $service->date_solicitude = Carbon::now();
                $service->pax_min = (int)$request->input('capacity_min');
                $service->pax_max = (int)$request->input('capacity_max');
                $service->min_age = (int)$request->input('min_ege');
                $service->require_itinerary = $request->input('req_itinerary');
                $service->require_image_itinerary = $request->input('req_image_itinerary');
                $service->status = (int)$request->input('status');
                $service->compensation = (int)$request->input('compensation');
                $service->exclusive = (int)$request->input('exclusive');
                $service->exclusive_client_id = $request->input('exclusive_client_id');
                $service->tag_service_id = $request->input('tag_service_id');
                $service->notes = $request->input('notes');
                $service->physical_intensity_id = $physical_intensity_id;
                $service->type = $request->input('type');
                $service->save();

                //Origin
                $origin = $request->input('origin');
                $serviceOrigin = new ServiceOrigin();
                $serviceOrigin->service_id = $service->id;
                $serviceOrigin->country_id = $origin['country_id'];
                $serviceOrigin->state_id = $origin['state_id'];
                if ($origin['city_id']) {
                    $serviceOrigin->city_id = $origin['city_id'];
                }
                if ($origin['zone_id']) {
                    $serviceOrigin->zone_id = $origin['zone_id'];
                }
                $serviceOrigin->save();
                //Destino
                $destiny = $request->input('destiny');
                $serviceDestiny = new ServiceDestination();
                $serviceDestiny->service_id = $service->id;
                $serviceDestiny->country_id = $destiny['country_id'];
                $serviceDestiny->state_id = $destiny['state_id'];
                if ($destiny['city_id'] !== "") {
                    $serviceDestiny->city_id = $destiny['city_id'];
                } else {
                    $serviceDestiny->city_id = null;
                }
                if ($destiny['zone_id'] !== "") {
                    $serviceDestiny->zone_id = $destiny['zone_id'];
                } else {
                    $serviceDestiny->zone_id = null;
                }
                $serviceDestiny->save();

                //Pre-requisitos
//                    $service->requirement()->attach($request->input("translRequirements"));
                $this->attachRequirements($service->id, $request->input("translRequirements"));
                //Restricciones
//                    $service->restriction()->attach($request->input("translRestrictions"));
                $this->attachRestrictions($service->id, $request->input("translRestrictions"));
                //Experiencias
//                    $service->experience()->attach($request->input("translExperiences"));
                $this->attachExperiences($service->id, $request->input("translExperiences"));

                $langs = Language::where('state', 1)->get();
                $names = $request->input('names');
                $name_commercial = $request->input('commercial');
                $description = $request->input('description');
                $description_commerce = $request->input('description_commercial');
                $itinerary = $request->input('itinerary');
                $itinerary_commercial = $request->input('itinerary_commercial');
                $summary = $request->input('summary');
                $summary_commercial = $request->input('summary_commercial');


                foreach ($langs as $lang) {
                    $service_translation = new ServiceTranslation();
                    $service_translation->language_id = $lang->id;
                    $service_translation->service_id = $service->id;
                    $service_translation->name = $names[$lang->id]['name'];
                    $service_translation->name_commercial = $name_commercial[$lang->id]['name'];
                    $service_translation->description = $description[$lang->id]['name'];
                    $service_translation->description_commercial = $description_commerce[$lang->id]['name'];
                    $service_translation->summary = $summary[$lang->id]['name'];
                    $service_translation->summary_commercial = $summary_commercial[$lang->id]['name'];
                    $service_translation->itinerary = $itinerary[$lang->id]['name'];
                    $service_translation->itinerary_commercial = $itinerary_commercial[$lang->id]['name'];
                    $service_translation->link_trip_advisor = $itinerary_commercial[$lang->id]['name'];
                    $service_translation->accommodation = $itinerary_commercial[$lang->id]['accommodation'] ?? null;
                    $service_translation->save();
                }

                // Progress detalles
                if ($service->name != "" && $service->currency_id != "" &&
                    $service->qty_reserve != "" && $service->affected_igv != "" &&
                    $service->include_accommodation != "" && $service->unit_id != "" &&
                    $service->unit_duration_id != "" && $service->service_type_id != "" &&
                    $service->classification_id != "" && $service->service_sub_category_id != "" &&
                    $service->classification_id != "") {
                    ProgressBar::firstOrCreate(
                        [
                            'slug' => 'service_progress_details',
                            'value' => 10,
                            'type' => 'service',
                            'object_id' => $service->id
                        ]
                    );
                }

                // Progress descripcion
                if ($name_commercial[1]['name'] != "" && $description[1]['name'] != "" &&
                    $itinerary[1]['name'] != "") {
                    ProgressBar::firstOrCreate(
                        [
                            'slug' => 'service_progress_descriptions',
                            'value' => 10,
                            'type' => 'service',
                            'object_id' => $service->id
                        ]
                    );
                }

                // Progress localizacion
                if ($service->latitude != "" && $service->longitude != "") {
                    ProgressBar::firstOrCreate(
                        [
                            'slug' => 'service_progress_location',
                            'value' => 5,
                            'type' => 'service',
                            'object_id' => $service->id
                        ]
                    );
                }

                // Progress experiencias
                if (count($request->input("translExperiences")) > 0) {
                    ProgressBar::firstOrCreate(
                        [
                            'slug' => 'service_progress_experiences',
                            'value' => 10,
                            'type' => 'service',
                            'object_id' => $service->id
                        ]
                    );
                }

                if ($service->exclusive) {
                    $new_deactivatable_entity = new DeactivatableEntity();
                    $new_deactivatable_entity->entity = "App\Service";
                    $new_deactivatable_entity->object_id = $service->id;
                    $new_deactivatable_entity->after_hours = 0;
                    $new_deactivatable_entity->param = "-";
                    $new_deactivatable_entity->value = "-";
                    $new_deactivatable_entity->action = "block_service";
                    $new_deactivatable_entity->save();
                }

                if (!empty($client_id)) {
                    $newClientService = new ClientService();
                    $newClientService->client_id = $client_id;
                    $newClientService->service_id = $service->id;
                    $newClientService->save();
                }

                DB::commit();
                if (empty($client_id)) {
                    $this->serviceLockClientEcommerce($service->id);
                }
                return Response::json(['success' => true, 'data' => ['id' => $service->id]]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function serviceLockClientEcommerce($service_id)
    {
        try {
            DB::transaction(function () use ($service_id) {
                $client_ecommerce = Client::where('ecommerce', 1)->get(['id']);
                foreach ($client_ecommerce as $client) {
                    $client_periods = Markup::where('period', '>=', Carbon::now()->format('Y'))->where('client_id',
                        $client->id)->get(['period']);
                    foreach ($client_periods as $period) {
                        $service_client = new ServiceClient();
                        $service_client->period = $period->period;
                        $service_client->client_id = $client->id;
                        $service_client->service_id = $service_id;
                        $service_client->save();
                    }
                }
            });
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function attachRestrictions($service_id, $restrictions)
    {
        foreach ($restrictions as $restriction) {
            $new = new RestrictionService();
            $new->service_id = $service_id;
            $new->restriction_id = $restriction;
            $new->save();
        }
    }

    public function attachExperiences($service_id, $experiences)
    {
        foreach ($experiences as $experience) {
            $new = new ExperienceService();
            $new->service_id = $service_id;
            $new->experience_id = $experience;
            $new->save();
        }
    }

    public function attachRequirements($service_id, $requirements)
    {
        foreach ($requirements as $requirement) {
            $new = new RequirementService();
            $new->service_id = $service_id;
            $new->requirement_id = $requirement;
            $new->save();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Service $service
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        $lang = $request->input("lang");
        $language = Language::where('iso', $lang)->first();

        $language_id = 1;

        if ($language == null) {
            $language_id = 1;
        } else {
            $language_id = $language->id;
        }

        $service = Service::with([
            'service_translations'
        ])->with([
            'composition.master_service.translations'
        ])->with([
            'serviceOrigin.country.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'country');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceOrigin.state.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'state');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceOrigin.city.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'city');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceOrigin.zone.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'zone');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceDestination.country.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'country');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceDestination.state.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'state');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceDestination.city.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'city');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceDestination.zone.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'zone');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceType.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'servicetype');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceSubCategory.serviceCategories.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'servicecategory');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'currency.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'currency');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceSubCategory.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'servicesubcategory');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceSubCategory.serviceCategories.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'servicecategory');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'classification.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'classification');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'units.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'unit');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'unitDurations.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'unitduration');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'requirement.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'requirement');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'restriction.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'restriction');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'experience.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'experience');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'physical_intensity.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'physicalintensity');
                $query->where('language_id', $language_id);
            }
        ])->with('languages_guide.language')->where('id', $id)->get(
            [
                'id',
                'aurora_code',
                'name',
                'currency_id',
                'latitude',
                'longitude',
                'qty_reserve',
                'qty_reserve_client',
                'equivalence_aurora',
                'affected_igv',
                'affected_markup',
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
                'date_solicitude',
                'duration',
                'pax_min',
                'pax_max',
                'min_age',
                'require_itinerary',
                'require_image_itinerary',
                'status',
                'compensation',
                'exclusive',
                'exclusive_client_id',
                'tag_service_id',
                'physical_intensity_id',
                'notes',
                'type',
                'created_at',
            ]
        );
        $service = $service->transform(function ($service) {
            $service['composition_'] = $this->search_composition($service['id'], 1);;
            return $service;
        });

        $data = [
            'data' => $service,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $client_id = $this->getClientId($request);

        DB::beginTransaction();
        $physical_intensity_id = ($request->input("physical_intensity_id") && $request->input("physical_intensity_id") != null) ? $request->input("physical_intensity_id") : null;

        $service = Service::find($id);
//        $service->aurora_code = strtoupper($request->input('aurora_code'));
        $service->name = $request->input('name');
        $service->currency_id = empty($client_id) ? $request->input('currency_id') : 2;
        $service->latitude = $request->input('latitude');
        $service->longitude = $request->input('longitude');
        $service->qty_reserve = (int)$request->input('qty_reserve');
        $service->qty_reserve_client = (int)$request->input('qty_reserve_client');
        $service->equivalence_aurora = empty($client_id) ? $request->input('equivalence_aurora') : 0;
        $service->affected_igv = (int)$request->input('affected_igv');
        $service->include_accommodation = (int)$request->input('include_accommodation');
        $service->unit_id = $request->input('unit_id');
        $service->affected_markup = (int)$request->input('affected_markup');
        $service->unit_duration_reserve = empty($client_id) ? $request->input('unitDurationReserve_id') : 1;
        $service->unit_duration_id = $request->input('unitDuration_id');
        $service->service_type_id = $request->input('serviceType_id');
        $service->classification_id = $request->input('classification_id');
        $service->service_sub_category_id = $request->input('subCategory_id');
        $service->duration = $request->input('duration');
        $service->pax_min = $request->input('capacity_min');
        $service->pax_max = $request->input('capacity_max');
        $service->min_age = $request->input('min_ege');
        $service->require_itinerary = (int)$request->input('req_itinerary');
        $service->require_image_itinerary = (int)$request->input('req_image_itinerary');
        $service->status = (int)$request->input('status');
        $service->compensation = (int)$request->input('compensation');
        $service->exclusive = (int)$request->input('exclusive');
        $service->exclusive_client_id = $request->input('exclusive_client_id');
        $service->tag_service_id = $request->input('tag_service_id');
        $service->notes = $request->input('notes');
        $service->physical_intensity_id = $physical_intensity_id;
        $service->type = $request->input('type');
        $service_dirty = [];
        if ($service->isDirty('aurora_code')) {
            $service_dirty['aurora_code'] = $service->aurora_code;
        }

        if ($service->isDirty('name')) {
            $service_dirty['name'] = $service->name;
        }

        if ($service->isDirty('currency_id')) {
            $service_dirty['currency_id'] = $service->currency_id;
        }

        if ($service->isDirty('equivalence_aurora')) {
            $service_dirty['equivalence_aurora'] = $service->equivalence_aurora;
        }

        if ($service->isDirty('affected_igv')) {
            $service_dirty['affected_igv'] = $service->affected_igv;
        }

        if ($service->isDirty('include_accommodation')) {
            $service_dirty['include_accommodation'] = $service->include_accommodation;
        }

        if ($service->isDirty('unit_id')) {
            $service_dirty['unit_id'] = $service->unit_id;
        }

        if ($service->isDirty('affected_markup')) {
            $service_dirty['affected_markup'] = $service->affected_markup;
        }

        if ($service->isDirty('qty_reserve') || $service->isDirty('unit_duration_reserve')) {
            $service_dirty['qty_reserve'] = $service->qty_reserve . ' ' . ($service->unit_duration_reserve == 1) ? 'horas' : 'días';
        }

        if ($service->isDirty('unit_duration_id')) {
            $service_dirty['unit_duration_id'] = $service->unit_duration_id;
        }

        if ($service->isDirty('service_type_id')) {
            $service_dirty['service_type_id'] = $service->service_type_id;
        }

        if ($service->isDirty('classification_id')) {
            $service_dirty['classification_id'] = $service->classification_id;
        }

        if ($service->isDirty('service_sub_category_id')) {
            $service_dirty['service_sub_category_id'] = $service->service_sub_category_id;
        }

        if ($service->isDirty('duration')) {
            $service_dirty['duration'] = $service->duration;
        }

        if ($service->isDirty('pax_min')) {
            $service_dirty['pax_min'] = $service->pax_min;
        }

        if ($service->isDirty('pax_max')) {
            $service_dirty['pax_max'] = $service->pax_max;
        }

        if ($service->isDirty('min_age')) {
            $service_dirty['min_age'] = $service->min_age;
        }

        if ($service->isDirty('require_itinerary')) {
            $service_dirty['require_itinerary'] = $service->require_itinerary;
        }

        if ($service->isDirty('require_image_itinerary')) {
            $service_dirty['require_image_itinerary'] = $service->require_image_itinerary;
        }

        if ($service->isDirty('status')) {
            $service_dirty['status'] = $service->status;
        }

        if ($service->isDirty('notes')) {
            $service_dirty['notes'] = $service->notes;
        }

        $service->save();

        //Origin
        $origin = $request->input('origin');
        $city_origin = ($origin['city_id']) ? $origin['city_id'] : null;
        $zone_origin = ($origin['zone_id']) ? $origin['zone_id'] : null;

        $origin_service = ServiceOrigin::where('service_id', $id)->first();
        $origin_service->country_id = $origin['country_id'];
        $origin_service->state_id = $origin['state_id'];
        $origin_service->city_id = $city_origin;
        $origin_service->zone_id = $zone_origin;
        $origin_service->save();

        //Destino
        $destiny = $request->input('destiny');
        $city_destiny = ($destiny['city_id']) ? $destiny['city_id'] : null;
        $zone_destiny = ($destiny['zone_id']) ? $destiny['zone_id'] : null;
        $destiny_service = ServiceDestination::where('service_id', $id)->first();
        $destiny_service->country_id = $destiny['country_id'];
        $destiny_service->state_id = $destiny['state_id'];
        $destiny_service->city_id = $city_destiny;
        $destiny_service->zone_id = $zone_destiny;
        $destiny_service->save();

        //Pre-requisitos
        $this->requirementSync($id, $request->input("translRequirements"));

        //Restricciones
        $this->restrictionSync($id, $request->input("translRestrictions"));
        //Experiencias
        $this->experienceSync($id, $request->input("translExperiences"));

        //Textos
        $langs = Language::where('state', 1)->get();
        $names = $request->input('names');
        $name_commercial = $request->input('commercial');
        $description = $request->input('description');
        $description_commercial = $request->input('description_commercial');
        $summary = $request->input('summary');
        $summary_commercial = $request->input('summary_commercial');
        $itinerary = $request->input('itinerary');
        $itinerary_commercial = $request->input('itinerary_commercial');
        $accommodation = $request->input('accommodation');

        $translation_dirty = [];
        foreach ($langs as $key => $lang) {
            if (isset($name_commercial[$lang->id]['id'])) {
                $service_translation = ServiceTranslation::find($name_commercial[$lang->id]['id']);
                $service_translation->name = $names[$lang->id]['name'];
                $service_translation->name_commercial = $name_commercial[$lang->id]['name'];
                $service_translation->description = $description[$lang->id]['name'];
                $service_translation->description_commercial = $description_commercial[$lang->id]['name'];
                $service_translation->summary = $summary[$lang->id]['name'];
                $service_translation->summary_commercial = $summary_commercial[$lang->id]['name'];
                $service_translation->itinerary = $itinerary[$lang->id]['name'];
                $service_translation->itinerary_commercial = $itinerary_commercial[$lang->id]['name'];
                $service_translation->accommodation = $accommodation[$lang->id]['accommodation'] ?? null;

                if ($service_translation->isDirty('name_commercial')) {
                    $translation_dirty[$lang->id]['name_commercial'] = $service_translation->name_commercial;
                    $translation_dirty[$lang->id]['language'] = $lang->iso;
                }

                if ($service_translation->isDirty('description')) {
                    $translation_dirty[$lang->id]['description'] = $service_translation->description;
                    $translation_dirty[$lang->id]['language'] = $lang->iso;
                }

                if ($service_translation->isDirty('summary')) {
                    $translation_dirty[$lang->id]['summary'] = $service_translation->summary;
                    $translation_dirty[$lang->id]['language'] = $lang->iso;
                }

                if ($service_translation->isDirty('summary_commercial')) {
                    $translation_dirty[$lang->id]['summary_commercial'] = $service_translation->summary_commercial;
                    $translation_dirty[$lang->id]['language'] = $lang->iso;
                }

                if ($service_translation->isDirty('itinerary')) {
                    $translation_dirty[$lang->id]['itinerary'] = $service_translation->itinerary;
                    $translation_dirty[$lang->id]['language'] = $lang->iso;
                }

                if ($service_translation->isDirty('accommodation')) {
                    $translation_dirty[$lang->id]['accommodation'] = $service_translation->accommodation;
                    $translation_dirty[$lang->id]['language'] = $lang->iso;
                }
            } else {
                $service_translation = new ServiceTranslation();
                $service_translation->language_id = $lang->id;
                $service_translation->name = $names[1]['name'];
                $service_translation->name_commercial = $name_commercial[1]['name'];
                $service_translation->description = $description[1]['name'];
                $service_translation->description_commercial = $description_commercial[1]['name'];
                $service_translation->itinerary = $itinerary[1]['name'];
                $service_translation->itinerary_commercial = $itinerary_commercial[1]['name'];
                $service_translation->summary = $summary[1]['name'];
                $service_translation->summary_commercial = $summary_commercial[1]['name'];
                $service_translation->accommodation = $accommodation[1]['accommodation'] ?? null;
                $service_translation->service_id = $service->id;
            }
            $service_translation->save();
        }

        // Progress detalles
        if ($service->name != "" && $service->currency_id != "" &&
            $service->qty_reserve != "" && $service->affected_igv != "" &&
            $service->include_accommodation != "" && $service->unit_id != "" &&
            $service->unit_duration_id != "" && $service->service_type_id != "" &&
            $service->classification_id != "" && $service->service_sub_category_id != "" &&
            $service->classification_id != "") {
            ProgressBar::updateOrCreate(
                [
                    'slug' => 'service_progress_details',
                    'value' => 10,
                    'type' => 'service',
                    'object_id' => $service->id
                ]
            );
        }

        // Progress descripcion
        if ($name_commercial[1]['name'] != "" && $description[1]['name'] != "" &&
            $itinerary[1]['name'] != "") {
            ProgressBar::updateOrCreate(
                [
                    'slug' => 'service_progress_descriptions',
                    'value' => 10,
                    'type' => 'service',
                    'object_id' => $service->id
                ]
            );
        }

        // Progress localizacion
        if ($service->latitude != "" && $service->longitude != "") {
            ProgressBar::updateOrCreate(
                [
                    'slug' => 'service_progress_location',
                    'value' => 5,
                    'type' => 'service',
                    'object_id' => $service->id
                ]
            );
        }

        // Progress experiencias
        if (count($request->input("translExperiences")) > 0) {
            ProgressBar::updateOrCreate(
                [
                    'slug' => 'service_progress_experiences',
                    'value' => 5,
                    'type' => 'service',
                    'object_id' => $service->id
                ]
            );
        }

        if (($request->has('hasNotify') and $request->input('hasNotify')) and
            ($request->has('emails') and count($request->input('emails')) > 0) and
            (count($service_dirty) > 0 || count($translation_dirty) > 0) and empty($client_id)
        ) {

            $this->buildDataNotification(
                $id,
                $service->aurora_code,
                $request->input('emails'),
                $request->input('message'),
                $service_dirty,
                $translation_dirty
            );
        }
        DB::commit();
        return Response::json(['success' => true]);
    }

    public function requirementSync($service_id, $requirements)
    {
        if (count($requirements) == 0) {
            RequirementService::where('service_id', $service_id)->each(function ($query) {
                $query->delete();
            });
        } else {
            RequirementService::where('service_id', $service_id)->whereNotIn('requirement_id',
                $requirements)->each(function ($query) {
                $query->delete();
            });
            foreach ($requirements as $requirement) {
                $query = RequirementService::where('service_id', $service_id)
                    ->where('requirement_id', $requirement)
                    ->select(['requirement_id']);
                if ($query->count() === 0) {
                    $new = new RequirementService();
                    $new->service_id = $service_id;
                    $new->requirement_id = $requirement;
                    $new->save();
                }
            }
        }

    }

    public function restrictionSync($service_id, $restrictions)
    {
        if (count($restrictions) == 0) {
            RestrictionService::where('service_id', $service_id)->each(function ($query) {
                $query->delete();
            });
        } else {
            RestrictionService::where('service_id', $service_id)->whereNotIn('restriction_id',
                $restrictions)->each(function ($query) {
                $query->delete();
            });
            foreach ($restrictions as $restriction) {
                $query = RestrictionService::where('service_id', $service_id)->where('restriction_id',
                    $restriction)->select(['restriction_id']);
                if ($query->count() === 0) {
                    $new = new RestrictionService();
                    $new->service_id = $service_id;
                    $new->restriction_id = $restriction;
                    $new->save();
                }
            }
        }

    }

    public function experienceSync($service_id, $experiences)
    {
        if (count($experiences) == 0) {
            ExperienceService::where('service_id', $service_id)->each(function ($query) {
                $query->delete();
            });
        } else {
            ExperienceService::where('service_id', $service_id)->whereNotIn('experience_id',
                $experiences)->each(function ($query) {
                $query->delete();
            });
            foreach ($experiences as $experience) {
                $query = ExperienceService::where('service_id', $service_id)
                    ->where('experience_id', $experience)
                    ->select(['experience_id']);
                if ($query->count() === 0) {
                    $new = new ExperienceService();
                    $new->service_id = $service_id;
                    $new->experience_id = $experience;
                    $new->save();
                }
            }
        }

    }

    public function buildDataNotification($service_id, $aurora_code, $emails, $message, $service, $translations)
    {
        if (count($service) > 0) {
            if (isset($service['currency_id'])) {
                $currency = Currency::find($service['currency_id']);
                $service['currency_id'] = $currency->symbol . ' ' . $currency->iso;
            }
            if (isset($service['service_sub_category_id'])) {
                $subcategory = ServiceSubCategory::
                with([
                    'translations' => function ($query) {
                        $query->select('id', 'value', 'language_id', 'object_id');
                        $query->where('type', 'servicesubcategory');
                        $query->where('language_id', 1);
                    }
                ])->with([
                    'serviceCategories.translations' => function ($query) {
                        $query->select('id', 'value', 'language_id', 'object_id');
                        $query->where('type', 'servicecategory');
                        $query->where('language_id', 1);
                    }
                ])->where('id', $service['service_sub_category_id'])->first();
                $service['service_sub_category_id'] = $subcategory->serviceCategories->translations[0]->value . ' - ' . $subcategory->translations[0]->value;
            }
            if (isset($service['unit_id'])) {
                $unit = Unit::with([
                    'translations' => function ($query) {
                        $query->select('id', 'value', 'language_id', 'object_id');
                        $query->where('type', 'unit');
                        $query->where('language_id', 1);
                    }
                ])->where('id', $service['unit_id'])->first();;
                $service['unit_id'] = $unit->translations[0]->value;
            }
            if (isset($service['unit_duration_id'])) {
                $unitDuration = UnitDuration::with([
                    'translations' => function ($query) {
                        $query->select('id', 'value', 'language_id', 'object_id');
                        $query->where('type', 'unitduration');
                        $query->where('language_id', 1);
                    }
                ])->where('id', $service['unit_duration_id'])->first();;
                $service['unit_duration_id'] = $unitDuration->translations[0]->value;
            }
            if (isset($service['service_type_id'])) {
                $serviceType = ServiceType::with([
                    'translations' => function ($query) {
                        $query->select('id', 'value', 'language_id', 'object_id');
                        $query->where('type', 'servicetype');
                        $query->where('language_id', 1);
                    }
                ])->where('id', $service['service_type_id'])->first();;
                $service['service_type_id'] = $serviceType->translations[0]->value;
            }
            if (isset($service['classification_id'])) {
                $classification = Classification::with([
                    'translations' => function ($query) {
                        $query->select('id', 'value', 'language_id', 'object_id');
                        $query->where('type', 'classification');
                        $query->where('language_id', 1);
                    }
                ])->where('id', $service['classification_id'])->first();;
                $service['classification_id'] = $classification->translations[0]->value;
            }
        }
        $data = [
            'service_id' => $service_id,
            'aurora_code' => $aurora_code,
            'service' => $service,
            'translations' => $translations,
            'message' => $message,
        ];
        Mail::to($emails)->send(new NotificationService($data));
    }

    public function updateStatus($id, Request $request)
    {
        $service = Service::find($id, ['id', 'status']);
        if ($request->input("status")) {
            $service->status = 0;
        } else {
            $service->status = 1;
        }
        $service->save();
        return Response::json(['success' => true]);
    }

    public function get_uses($id)
    {
        $packages = $this->get_service_uses($id, 'service');

        $equivalences = ServiceEquivalenceAssociation::where('service_equivalence_id', $id)
            ->whereHas('parent_service')
            ->with([
                'parent_service' => function ($query) {
                    $query->with([
                        'serviceType' => function ($query) {
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'servicetype');
                                    $query->where('language_id', 1);
                                },
                            ]);
                        }
                    ]);
                },
            ])->get();

        $multiservices = Component::where('service_id', $id)
            ->whereHas('service_component.service')
            ->with([
                'service_component.service' => function ($query) {
                    $query->with([
                        'serviceType' => function ($query) {
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'servicetype');
                                    $query->where('language_id', 1);
                                },
                            ]);
                        }
                    ]);
                }
            ])
            ->get();

        $multiservices_replaces = ComponentSubstitute::where('service_id', $id)
            ->whereHas('multiservice.service_component.service')
            ->with([
                'multiservice.service' => function ($query) {
                    $query->with([
                        'serviceType' => function ($query) {
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'servicetype');
                                    $query->where('language_id', 1);
                                },
                            ]);
                        }
                    ]);
                }
            ])
            ->with([
                'multiservice.service_component.service' => function ($query) {
                    $query->with([
                        'serviceType' => function ($query) {
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'servicetype');
                                    $query->where('language_id', 1);
                                },
                            ]);
                        }

                    ]);
                }
            ])->get();

        $supplements = ServiceSupplement::where('object_id', $id)
            ->whereHas('parent_service')
            ->with([
                'parent_service' => function ($query) {
                    $query->with([
                        'serviceType' => function ($query) {
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'servicetype');
                                    $query->where('language_id', 1);
                                },
                            ]);
                        }
                    ]);
                },
            ])->get();

        return Response::json([
            'success' => true,
            'packages' => $packages,
            'equivalences' => $equivalences,
            'multiservices' => $multiservices,
            'multiservices_replaces' => $multiservices_replaces,
            'supplements' => $supplements,
        ]);
    }

    public function report_uses($id, Request $request)
    {

        try {

            $data = $request->input('data');

            $data["user"] = Auth::user()->name . ' (' . Auth::user()->code . ')';

            $mail = mail::to("producto@limatours.com.pe");
            $mail->cc(["neg@limatours.com.pe", "kams@limatours.com.pe", "qr@limatours.com.pe"]);
            $mail->bcc(["egj@limatours.com.pe"]);

//            $mail = mail::to("egj@limatours.com.pe");
//            $mail->cc(["bismack_1@hotmail.com","erick.garcia.jara@gmail.com"]);

            $hours_ = 48;

            if (count($data['packages']) > 0) {
                $mail->send(new \App\Mail\NotificationServiceStatus('Paquetes', 'packages', $data));
            }
            if (count($data['equivalences']) > 0) {
                $mail->send(new \App\Mail\NotificationServiceStatus('Asociación de Equivalencias', 'equivalences',
                    $data)); //

                foreach ($data['equivalences'] as $equivalence) {
                    $new_deactivatable_entity = new DeactivatableEntity();
                    $new_deactivatable_entity->entity = "App\ServiceEquivalenceAssociation";
                    $new_deactivatable_entity->object_id = $equivalence['id'];
                    $new_deactivatable_entity->after_hours = $hours_;
                    $new_deactivatable_entity->action = "delete";
                    $new_deactivatable_entity->save();
                }
            }

            if (count($data['multiservices']) > 0) {
                $mail->send(new \App\Mail\NotificationServiceStatus('Multiservicios', 'multiservices', $data)); //

                foreach ($data['multiservices'] as $multiservice) {
                    $new_deactivatable_entity = new DeactivatableEntity();
                    $new_deactivatable_entity->entity = "App\Component";
                    $new_deactivatable_entity->object_id = $multiservice['id'];
                    $new_deactivatable_entity->after_hours = $hours_;
                    $new_deactivatable_entity->action = "delete";
                    $new_deactivatable_entity->save();
                }
            }

            if (count($data['multiservices_replaces']) > 0) {
                $mail->send(new \App\Mail\NotificationServiceStatus('Multiservicios - Reemplazos',
                    'multiservices_replaces', $data)); //

                foreach ($data['multiservices_replaces'] as $replace) {
                    $new_deactivatable_entity = new DeactivatableEntity();
                    $new_deactivatable_entity->entity = "App\ComponentSubstitute";
                    $new_deactivatable_entity->object_id = $replace['id'];
                    $new_deactivatable_entity->after_hours = $hours_;
                    $new_deactivatable_entity->action = "delete";
                    $new_deactivatable_entity->save();
                }
            }

            if (count($data['supplements']) > 0) {
                $mail->send(new \App\Mail\NotificationServiceStatus('Suplementos', 'supplements', $data)); //

                foreach ($data['supplements'] as $supplement) {
                    $new_deactivatable_entity = new DeactivatableEntity();
                    $new_deactivatable_entity->entity = "App\ServiceSupplement";
                    $new_deactivatable_entity->object_id = $supplement['id'];
                    $new_deactivatable_entity->after_hours = $hours_;
                    $new_deactivatable_entity->action = "delete";
                    $new_deactivatable_entity->save();
                }
            }

            $new_deactivatable_entity = new DeactivatableEntity();
            $new_deactivatable_entity->entity = "App\Service";
            $new_deactivatable_entity->object_id = $id;
            $new_deactivatable_entity->after_hours = $hours_;
            $new_deactivatable_entity->param = "status";
            $new_deactivatable_entity->value = "0";
            $new_deactivatable_entity->save();

            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }


    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $service = Service::find($id);

            $client = new \GuzzleHttp\Client(['verify' => false]);
            $response = $client->request('PUT',
                config('services.stella.domain') . 'api/v1/equivalences/status', [
                    "json" => [
                        'nroref' => (int)$service->equivalence_aurora,
                        'status' => 0
                    ]
                ]);
            $response = json_decode($response->getBody()->getContents());

            if ($response->success) {
                $service->delete();
            } else {
                return Response::json(['success' => false, 'message' => $response]);
            }

            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e]);
        }
    }

    public function getCitiesOrigin($lang)
    {

        $service = Service::with([
            'serviceOrigin.country.translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'serviceOrigin.state.translations' => function ($query) use ($lang) {
                $query->where('type', 'state');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'serviceOrigin.city.translations' => function ($query) use ($lang) {
                $query->where('type', 'city');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'serviceOrigin.zone.translations' => function ($query) use ($lang) {
                $query->where('type', 'zone');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get(['id']);

        return Response::json(['success' => true, 'data' => $service]);
    }

    public function getCitiesDestination($lang)
    {
        $destinations = ServiceDestination::select('country_id', 'state_id', 'city_id')->whereHas('service',
            function ($query) {
                $query->where('status', 1);
            })->with([
            'country' => function ($query) {
                $query->select('id');
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'country');
                        $query->where('language_id', 1);
                    },
                ]);
            },
        ])->with([
            'state' => function ($query) {
                $query->select('id');
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'state');
                        $query->where('language_id', 1);
                    },
                ]);
            },
        ])->with([
            'city' => function ($query) {
                $query->select('id');
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'city');
                        $query->where('language_id', 1);
                    },
                ]);
            },

        ])->with([
            'zone' => function ($query) {
                $query->select('id');
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'zone');
                        $query->where('language_id', 1);
                    },
                ]);
            },

        ])->distinct()->get(['id']);

        $destinationsResponse = [];
        foreach ($destinations as $destination) {
            $zone_id = '';
            $city_id = '';
            $city_name = '';
            if (!empty($destination['zone']) and $destination['zone']['id'] !== null) {
                $zone_id = ',' . $destination['zone']['id'];
            }

            if (!empty($destination['city']) and $destination['city']['id'] !== null) {
                $city_id = ',' . $destination['city']['id'];
                $city_name = ',' . $destination['city']['translations'][0]['value'];
            }

            $destinationsResponse [] = [
                'id' => $destination['country']['id'] . ',' . $destination['state']['id'] . $city_id . $zone_id,
                'description' => $destination['country']['translations'][0]['value'] . ',' . $destination['state']['translations'][0]['value'] . $city_name
            ];

        }

        return Response::json(['success' => true, 'data' => $destinationsResponse]);
    }

    public function getCitiesOriginFormat($lang)
    {
        $destinations = ServiceOrigin::select('country_id', 'state_id', 'city_id')->whereHas('service',
            function ($query) {
                $query->where('status', 1);
            })->with([
            'country' => function ($query) {
                $query->select('id');
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'country');
                        $query->where('language_id', 1);
                    },
                ]);
            },
        ])->with([
            'state' => function ($query) {
                $query->select('id');
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'state');
                        $query->where('language_id', 1);
                    },
                ]);
            },
        ])->with([
            'city' => function ($query) {
                $query->select('id');
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'city');
                        $query->where('language_id', 1);
                    },
                ]);
            },

        ])->with([
            'zone' => function ($query) {
                $query->select('id');
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'zone');
                        $query->where('language_id', 1);
                    },
                ]);
            },

        ])->distinct()->get(['id']);

//        $destinationsResponse = [];
//        foreach ($destinations as $destination) {
//            $city_id = '';
//            $city_name = '';
//            $zone_id = '';
//            $zone_name = '';
//            if (!empty($destination['city'])) {
//                $city_id = ','.$destination['city']['id'];
//                $city_name = ','.$destination['city']['translations'][0]['value'];
//            }
//            if (!empty($destination['zone'])) {
//                $zone_id = ','.$destination['zone']['id'];
//                $zone_name = ','.$destination['zone']['translations'][0]['value'];
//
//            }
//
//            $destinationsResponse [] = [
//                'id' => $destination['country']['id'].','.$destination['state']['id'].$city_id.$zone_id,
//                'description' => $destination['country']['translations'][0]['value'].$city_name.$zone_name
//            ];
//        }

        $destinationsResponse = [];
        foreach ($destinations as $destination) {
            $details = [];

            $destinationsResponse [] = [
                'id' => (isset($destination['country']['id']) ? $destination['country']['id'] : '') . ',' .
                    (isset($destination['state']['id']) ? $destination['state']['id'] : '') . ',' .
                    (isset($destination['city']['id']) ? $destination['city']['id'] : '') . ',' .
                    (isset($destination['zone']['id']) ? $destination['zone']['id'] : ''),
                'description' =>
                    (isset($destination['country']) ? $destination['country']['translations'][0]['value'] : '') . ',' .
                    (isset($destination['state']) ? $destination['state']['translations'][0]['value'] : '') . ',' .
                    (isset($destination['city']) ? $destination['city']['translations'][0]['value'] : '')
            ];
        }

        return Response::json(['success' => true, 'data' => $destinationsResponse]);
    }

    public function serviceCrossSelling(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');

        $hotel_id = $request->input('hotel_id');

        $services_database = Service::select('id', 'name');

        $services_frontend = [];

        $cross_selling_database = CrossSelling::select('service_id')->where('hotel_id', $hotel_id)->get();

        if ($cross_selling_database->count() > 0) {
            $services_database->whereNotIn('id', $cross_selling_database);
        }
        $count = $services_database->count();

        if ($querySearch) {
            $services_database->orWhere('name', 'LIKE', '%' . $querySearch . '%');
            $services_database->orWhere('aurora_code', 'LIKE', '%' . strtoupper($querySearch) . '%');
        }

        if ($paging === 1) {
            $services_database = $services_database->take($limit)->get();
        } else {
            $services_database = $services_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {

            for ($j = 0; $j < $services_database->count(); $j++) {
                $services_frontend[$j]["cross_selling_id"] = "";
                $services_frontend[$j]["service_id"] = $services_database[$j]["id"];
                $services_frontend[$j]["name"] = $services_database[$j]["name"];
                $services_frontend[$j]["hotel_id"] = $hotel_id;
                $services_frontend[$j]["selected"] = false;
            }
        }
        $data = [
            'data' => $services_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function updateConfigurations($id, Request $request)
    {
        $service = Service::find($id);
        $service->allow_guide = (int)$request->input("allow_guide");
        $service->allow_child = (int)$request->input("allow_child");
        $service->allow_infant = (int)$request->input("allow_child");
        $service->limit_confirm_hours = $request->input("limit_confirmation_hours");
        $service->unit_duration_limit_confirmation = $request->input("unit_duration_limit_confirmation");
        $service->infant_min_age = $request->input("infant_min_age");
        $service->infant_max_age = $request->input("infant_max_age");
        $service->save();

        $findChild = ServiceChild::where('service_id', $id)->first();
        if ($findChild) {
            $update = ServiceChild::find($findChild->id);
            $update->min_age = $request->input('child_min_age');
            $update->max_age = $request->input('child_max_age');
            $update->save();
        } else {
            $serviceChild = new ServiceChild();
            $serviceChild->min_age = $request->input('child_min_age');
            $serviceChild->max_age = $request->input('child_max_age');
            $serviceChild->status = 1;
            $serviceChild->service_id = $id;
            $serviceChild->save();
        }

        return Response::json(['success' => true]);
    }


    public function serviceClient(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit') ? $request->input('limit') : 10;
        $region_id = $request->input('region_id') ? $request->input('region_id') : 1;
        $querySearch = $request->input('query');
        $client_id = $request->input('client_id');
        $period = $request->input('period');

        $region = BusinessRegion::with('countries')->find($region_id);
        $countryIds = $region->countries->pluck('id');

        // return response()->json($countryIds);
        $services_database = Service::select(['id', 'name', 'aurora_code'])
                ->whereHas('serviceOrigin', function($query) use ($countryIds) {
                    $query->whereIn('country_id', $countryIds);
                })
                ->where('type', 'service')
                ->whereDoesntHave('client_services');
                // ->whereDoesntHave('client_services');

        // return response()->json($services_database);

        $services_frontend = [];


        $service_client_database = ServiceClient::select(['service_id', 'period'])
            ->where([
                'client_id' => $client_id,
                'period' => $period,
                'business_region_id' => $region_id
            ])
            ->get();

        $service_client_ids = $service_client_database->pluck('service_id');

        if ($service_client_database->count() > 0) {
            $services_database->whereNotIn('id', $service_client_ids);
        }

        $count = $services_database->count();

        if ($querySearch) {
//            $services_database->where('name', 'like', '%'.$querySearch.'%');
            $services_database->where('aurora_code', 'like', '%' . $querySearch . '%');
        }

        $services_database = $services_database->whereDoesntHave('client_services');


        if ($paging === 1) {
            $services_database = $services_database->where('type', 'service')->take($limit)->get();
        } else {
            $services_database = $services_database->where('type',
                'service')->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {
            $markupServices = MarkupService::select(['markup', 'service_id'])->where([
                'client_id' => $client_id,
                'period' => $period
            ])->get();
            for ($j = 0; $j < count($services_database); $j++) {
                $markup = "";
                foreach ($markupServices as $markupService) {
                    if ($markupService->service_id == $services_database[$j]["id"]) {
                        $markup = $markupService->markup;
                    }
                }
                $services_frontend[$j]["id"] = "";
                $services_frontend[$j]["aurora_code"] = $services_database[$j]["aurora_code"];
                $services_frontend[$j]["service_id"] = $services_database[$j]["id"];
                $services_frontend[$j]["name"] = $services_database[$j]["name"];
                $services_frontend[$j]["client_id"] = $client_id;
                $services_frontend[$j]["markup"] = ($markup == "" ? 0 : $markup);
                $services_frontend[$j]["selected"] = false;
            }
        }
        $data = [
            'data' => $services_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function serviceClientRated(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit') ? $request->input('limit') : 10;
        $querySearch = $request->input('query');
        $client_id = $request->input('client_id');
        $period = $request->input('period') ? $request->input('period') : date('Y');
        $region_id = $request->input('region_id') ? $request->input('region_id') : 1;

        $region = BusinessRegion::with('countries')->find($region_id);
        $countryIds = $region->countries->pluck('id');

        $services_database = Service::select(['id', 'name', 'aurora_code'])
                            ->whereHas('serviceOrigin', function($query) use ($countryIds) {
                                $query->whereIn('country_id', $countryIds);
                            });

        $services_frontend = [];

        if ($querySearch) {
            $services_database->where('name', 'like', '%' . $querySearch . '%');
            $services_database->orWhere('aurora_code', 'like', '%' . $querySearch . '%');
        }

        $service_client_database = ServiceClient::select(['service_id', 'period'])
            ->where([
                'client_id' => $client_id,
                'period' => $period,
                'business_region_id' => $region_id
            ]);

        $service_client_ids = $service_client_database->pluck('service_id');

        if ($service_client_database->count() > 0) {
            $services_database->whereNotIn('id', $service_client_ids);
        }

        $count = $services_database->count();
        if ($paging === 1) {
            $services_database = $services_database->take($limit)->get();
        } else {
            $services_database = $services_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {
            $ratedServices = ClientServiceRated::select(['rated', 'service_id'])->where([
                'client_id' => $client_id,
                'period' => $period
            ])->orderBy('rated', 'desc')->get();
            for ($j = 0; $j < count($services_database); $j++) {
                $rated = 0;
                foreach ($ratedServices as $ratedService) {
                    if ($ratedService->service_id == $services_database[$j]["id"]) {
                        $rated = $ratedService->rated;
                    }
                }
                $services_frontend[$j]["aurora_code"] = $services_database[$j]["aurora_code"];
                $services_frontend[$j]["service_id"] = $services_database[$j]["id"];
                $services_frontend[$j]["name"] = $services_database[$j]["name"];
                $services_frontend[$j]["client_id"] = $client_id;
                $services_frontend[$j]["rated"] = $rated;
            }
        }
        $data = [
            'data' => $services_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function selectBox(Request $request)
    {
        $querySearch = strtoupper($request->input('query'));
        $onlySupplement = $request->input('supplement') ? $request->input('supplement') : 0;
        if ($onlySupplement == 0) {
            $onlySupplement = 'service';
        } else {
            $onlySupplement = 'supplement';
        }

        $services = Service::with([
            'serviceType.translations' => function ($query) {
                $query->where('type', 'servicetype');
                $query->whereHas('language', function ($q) {
                    $q->where('id', 1);
                });
            }
        ]);

        $services->whereHas('service_rate.service_rate_plans');

        if (!empty($querySearch)) {
            $services->where(function ($query) use ($querySearch) {
                $query->orWhere('aurora_code', 'like', '%' . $querySearch . '%');
                $query->orWhere('name', 'like', '%' . $querySearch . '%');
            });
        }

        $services = $services->where('type', $onlySupplement)->take(10)->get();

        return Response::json(['success' => true, 'data' => $services]);
    }

    public function service_search_backup(Request $request)
    {

        $destiny = $request->has('destiny') ? $request->post('destiny') : [];
        $origin = $request->has('origin') ? $request->post('origin') : [];
        $date_from = $request->post('date_from');
        $zone_destination = $request->post('zone_destination');
        $service_type = $request->post('service_type');
        $service_experience = $request->post('experience_type');
        $service_sub_category = $request->post('service_sub_category');
        $service_category = $request->post('service_category');
        $service_name = $request->post('service_name');
        $allWords = $request->post('allWords');
        $paging = $request->post('page') ? $request->input('page') : 1;
        $limit = $request->post('limit');
        $lang = ($request->post('lang') == '') ? 'es' : $request->input('lang');
        // $client_id = $request->post('client_id');
        $client_id = $this->getClientId($request);
        $adults = ($request->has('adults')) ? $request->post('adults') : 2;
        $children = ($request->has('children')) ? $request->post('children') : 0;
        $priceRange = $request->get('price_range');

        $markup_cliente = 0;

        $from = Carbon::parse($date_from);
        $from = $from->format('Y-m-d');

        if ($client_id) {
            $markup_cliente = Markup::where('client_id', $client_id)
                ->where('period', Carbon::parse($from)->format('Y'));
            if ($markup_cliente->count() > 0) {
                $markup_cliente = $markup_cliente->first()->service;
            }
        }


        $language_id = Language::where('iso', $lang)->first()->id;

        if (!isset($date_from) and $date_from == "") {
            return Response::json(['success' => true, 'data' => [], 'count' => 0]);
        }
        $origin_codes = [];
        $destiny_codes = [];
//        throw new \Exception(empty($destiny));
        if (!empty($destiny)) {
            $destiny_codes = explode(",", $destiny["code"]);
        }
        $country_id = "";
        $state_id = "";
        $city_id = "";
        $zone_id = "";
        if (!empty($origin)) {
            $origin_codes = explode(",", $origin["code"]);
        }
        $origin_country_id = "";
        $origin_state_id = "";
        $origin_city_id = "";
        $origin_zone_id = "";


        for ($i = 0; $i < count($destiny_codes); $i++) {
            if ($i == 0) {
                $country_id = $destiny_codes[$i];
            }
            if ($i == 1) {
                $state_id = $destiny_codes[$i];
            }
            if ($i == 2) {
                $city_id = $destiny_codes[$i];
            }
            if ($i == 3) {
                $zone_id = $destiny_codes[$i];
            }
        }


        for ($i = 0; $i < count($origin_codes); $i++) {
            if ($i == 0) {
                $origin_country_id = $origin_codes[$i];
            }
            if ($i == 1) {
                $origin_state_id = $origin_codes[$i];
            }
            if ($i == 2) {
                $origin_city_id = $origin_codes[$i];
            }
            if ($i == 3) {
                $origin_zone_id = $origin_codes[$i];
            }
        }


        $paxTotal = $adults + $children;

        $services = Service::with([
            'serviceOrigin.state.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'state');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceOrigin.zone.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'zone');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceDestination.state.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'state');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceDestination.city.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'city');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceDestination.zone.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'zone');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceType.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'servicetype');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceSubCategory.serviceCategories.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'servicecategory');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'experience' => function ($query) use ($language_id) {
                $query->with([
                    'translations' => function ($query) use ($language_id) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'experience');
                        $query->where('language_id', $language_id);
                    }
                ]);
            }
        ])->with([
            'service_rate' => function ($query) use ($from, $paxTotal) {
                $query->with([
                    'service_rate_plans' => function ($query) use ($from, $paxTotal) {
                        $query->where('date_from', '<=', $from);
                        $query->where('date_to', '>=', $from);
                        $query->where('pax_from', '<=', $paxTotal);
                        $query->where('pax_to', '>=', $paxTotal);
                    }
                ]);
                $query->with(['markup_rate_plan']);
                $query->withCount([
                    'inventory' => function ($query) use ($from, $paxTotal) {
                        $query->where('date', '>=', $from);
                        $query->where('date', '<=', $from);
                        $query->where('locked', '=', 0);
                        $query->where('inventory_num', '>=', $paxTotal);
                    }
                ]);
            }
        ])->with([
            'service_translations' => function ($query) use ($language_id) {
                $query->where('language_id', $language_id);
            }
        ])->with([
            'markup_service' => function ($query) use ($from, $client_id) {
                $query->where('client_id', $client_id);
                $query->where('period', Carbon::parse($from)->format('Y'));
            }
        ])->with([
            'unitDurations' => function ($query) use ($language_id) {
                $query->with([
                    'translations' => function ($query) use ($language_id) {
                        $query->select('id', 'value', 'language_id', 'object_id');
                        $query->where('type', 'unitduration');
                        $query->where('language_id', $language_id);
                    }
                ]);
            }
        ])->with([
                'galleries' => function ($query) {
                    $query->select('object_id', 'slug', 'url');
                    $query->where('type', 'service');
                },
        ]);

        //Todo: busca las tarifas de acuerdo a la fecha
        $services->whereHas('service_rate.service_rate_plans', function ($query) use ($date_from) {
            $query->where('date_from', '<=', $date_from)
                ->where('date_to', '>=', $date_from)
                ->where('status', 1);
        });

        if ($country_id != "") {
            $services->whereHas('serviceDestination', function ($query) use ($country_id) {
                $query->where('country_id', $country_id);
            });
        }

        if ($state_id != "") {
            $services->whereHas('serviceDestination', function ($query) use ($state_id) {
                $query->where('state_id', $state_id);
            });
        }

        if ($city_id != "") {
            $services->whereHas('serviceDestination', function ($query) use ($city_id) {
                $query->where('city_id', $city_id);
            });
        }

        if ($zone_id != "") {
            $services->whereHas('serviceDestination', function ($query) use ($zone_id) {
                $query->where('zone_id', $zone_id);
            });
        }

        if ($zone_destination != "") {
            $services->whereHas('serviceDestination', function ($query) use ($zone_destination) {
                $query->where('zone_id', $zone_destination);
            });
        }



        if ($service_experience != "") {
            $services->whereHas('experience', function ($query) use ($service_experience) {
                $query->where('experience_id', $service_experience);
            });
        }
        // ----------------

        if ($origin_country_id != "") {
            $services->whereHas('serviceOrigin', function ($query) use ($origin_country_id) {
                $query->where('country_id', $origin_country_id);
            });
        }

        if ($origin_state_id != "") {
            $services->whereHas('serviceOrigin', function ($query) use ($origin_state_id) {
                $query->where('state_id', $origin_state_id);
            });
        }

        if ($origin_city_id != "") {
            $services->whereHas('serviceOrigin', function ($query) use ($origin_city_id) {
                $query->where('city_id', $origin_city_id);
            });
        }

        if ($origin_zone_id != "") {
            $services->whereHas('serviceOrigin', function ($query) use ($origin_zone_id) {
                $query->where('zone_id', $origin_zone_id);
            });
        }


        if (!empty($service_category) and count($service_category) > 0) {
            $category_trans = DB::table("translations")->where("type", "servicecategory")
                ->where('language_id', 1)
                ->whereIn('object_id', $service_category)
                ->get();

            foreach ($category_trans as $cat) {
                if( $cat->value == "Misceláneos" || $cat->value == "Tren" ||
                    $cat->value == "Asistencia" || $cat->value == "Adicional" ||
                    $cat->value == "Entradas"){
                    $service_category = DB::table("translations")->where("type", "servicecategory")
                        ->where('language_id', 1)
                        ->whereIn('value', ["Misceláneos", "Tren", "Asistencia", "Adicional", "Entradas", "Paquete"])
                        ->pluck('object_id')
                        ->toArray();
                } else {
                    $service_category[] = $cat->object_id;
                    $service_category = array_unique($service_category);
                }
            }

            $services->whereHas('serviceSubCategory.serviceCategories', function ($query) use ($service_category) {
                $query->whereIn('id', $service_category);
            });
        }

        if ($service_sub_category != "") {
            $services->where('service_sub_category_id', $service_sub_category);
        }


        if ($service_type != "") {
            $services->where('service_type_id', $service_type);
        }

        if ($service_name != "") {
            $filters = explode(',', $service_name);
            for ($i = 0; $i < count($filters); $i++) {
                $_filter = trim($filters[$i]);
                if ($_filter != '') {
                    if ($allWords == 1 || $i == 0) {
                        // AND
                        $services->where(function ($query) use ($_filter, $language_id) {
                            $query->where('name', 'like', '%' . $_filter . '%');
                            $query->orWhere('aurora_code', 'like', '%' . strtoupper($_filter) . '%');
                            $query->orWhereHas('service_translations', function ($query) use ($_filter, $language_id) {
                                $query->where('name_commercial', 'like', '%' . $_filter . '%');
                                $query->orWhere('description', 'like', '%' . $_filter . '%');
                                $query->where('language_id', $language_id);
                            });
                        });
                    } else {
                        // OR
                        $services->orWhere(function ($query) use ($_filter, $language_id) {
                            $query->where('name', 'like', '%' . $_filter . '%');
                            $query->orWhere('aurora_code', 'like', '%' . strtoupper($_filter) . '%');
                            $query->orWhereHas('service_translations', function ($query) use ($_filter, $language_id) {
                                $query->where('name_commercial', 'like', '%' . $_filter . '%');
                                $query->orWhere('description', 'like', '%' . $_filter . '%');
                                $query->where('language_id', $language_id);
                            });
                        });

                    }
                }
            }
        }

        $services->where('pax_min', '<=', $paxTotal)
            ->where('pax_max', '>=', $paxTotal)
            ->where('type', Service::TYPE_SERVICE)
            ->where('status', 1);

        $services = $services->whereDoesntHave('client_services');


        $count = $services->count();


        if ($paging === 1) {
            $servicesResults = $services->orderBy('aurora_code')->take($limit)->get();
        } else {
            $servicesResults = $services->orderBy('aurora_code')->skip($limit * ($paging - 1))->take($limit)->get();
        }

//        return $services[0]; die;

        if (is_object($markup_cliente)) {
            $services = [];
        } else {

            $services = [];

            foreach ($servicesResults as $k => $service) {


                // Si no tiene textos en idioma q no sea ingles, poner en ingles por defecto.
                if (($service->service_translations[0]->name === null || $service->service_translations[0]->name === '')
                    && strtoupper($lang) !== "EN") {
                    $service->service_translations[0] = ServiceTranslation::where('service_id', $service->id)
                        ->where('language_id', 2)->first();
                }

                // Todo Verifico si tiene el markup por tarifa
                if ($service->service_rate->count() > 0 and $service->service_rate[0]->markup_rate_plan->count() > 0) {
                    $markup = $service->service_rate[0]->markup_rate_plan[0]->markup;
                } elseif ($service->markup_service->count() > 0) { // Todo Verifico si tiene el markup por servicio
                    $markup = $service->markup_service[0]->markup;
                } else {
                    $markup = $markup_cliente;
                }

                if (is_object($markup)) {
                    break;
                }

                $priceOk = true;

                // if(!$service->affected_markup){
                //     $markup = 0;
                // }

                foreach ($service->service_rate as $service_rate) {
                    foreach ($service_rate->service_rate_plans as $service_rate_plan) {
                        $service_rate_plan->markup = trim($markup);
                        $service_rate_plan->price_adult_without_markup = $service_rate_plan->price_adult;
                        $markup = ($markup / 100);
                        $price_adult = ($service_rate_plan->price_adult * $markup);
                        $service_rate_plan->price_adult = $service_rate_plan->price_adult + $price_adult;
                        $service_rate_plan->price_adult = roundLito((float)$service_rate_plan->price_adult);

                        if ($priceRange) {

                            if ($service_rate_plan->price_adult < $priceRange['min'] || $service_rate_plan->price_adult > $priceRange['max']) {
                                $priceOk = false;
                            }
                        }

                    }
                }

                if($priceOk){
                   array_push($services, $service);
                }
            }
        }

        foreach($services as $key => $value)
        {
            $images = $this->searchGalleryCloudinary('service', $value['id']);
            $galleries = [];

            foreach($images as $image)
            {
                $galleries[] = [
                    'object_id' => $value['id'],
                    'slug' => 'service_gallery',
                    'url' => $image,
                ];
            }

            $services[$key]['galleries'] = $galleries;
        }

        $data = [
            'data' => $services,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }
    public function service_search(Request $request)
    {
        $destiny         = $request->input('destiny', []);
        $origin          = $request->input('origin', []);
        $date_from       = $request->post('date_from');
        $zone_destination= $request->post('zone_destination');
        $service_type    = $request->post('service_type');
        $service_experience = $request->post('experience_type');
        $service_sub_category = $request->post('service_sub_category');
        $service_category= $request->post('service_category');
        $service_name    = $request->post('service_name');
        $allWords        = $request->post('allWords');
        $paging          = $request->post('page', 1);
        $limit           = $request->post('limit');
        $lang            = $request->post('lang') ? $request->post('lang') : 'es';
        $client_id       = $this->getClientId($request);
        $adults          = $request->post('adults', 2);
        $children        = $request->post('children', 0);
        $priceRange      = $request->get('price_range');

        if (empty($date_from)) {
            return Response::json(['success' => true, 'data' => [], 'count' => 0]);
        }
        $from = Carbon::parse($date_from)->format('Y-m-d');

        // ====================================================
        // Optimización: Consulta simplificada para markup_cliente
        // ====================================================
        $markupRecord  = Markup::where('client_id', $client_id)
            ->where('period', Carbon::parse($from)->format('Y'))
            ->first();
        $markup_cliente = $markupRecord ? $markupRecord->service : 0;

        // Obtener el ID del idioma
        $language_id = Language::where('iso', $lang)->first()->id;

        // ====================================================
        // Procesar códigos para origen y destino (lógica original)
        // ====================================================
        $destiny_codes = !empty($destiny) ? explode(",", $destiny["code"]) : [];
        $origin_codes  = !empty($origin) ? explode(",", $origin["code"]) : [];

        // Variables para destino
        $country_id = $state_id = $city_id = $zone_id = "";
        if (!empty($destiny_codes)) {
            $country_id = $destiny_codes[0] ?? "";
            $state_id   = $destiny_codes[1] ?? "";
            $city_id    = $destiny_codes[2] ?? "";
            $zone_id    = $destiny_codes[3] ?? "";
        }
        // Variables para origen
        $origin_country_id = $origin_state_id = $origin_city_id = $origin_zone_id = "";
        if (!empty($origin_codes)) {
            $origin_country_id = $origin_codes[0] ?? "";
            $origin_state_id   = $origin_codes[1] ?? "";
            $origin_city_id    = $origin_codes[2] ?? "";
            $origin_zone_id    = $origin_codes[3] ?? "";
        }

        $paxTotal = $adults + $children;

        // ================================================
        // Construcción del Query y carga de relaciones
        // ================================================
        $services = Service::query();

        // Optimización 1: Eager load de traducciones en idioma actual y fallback (por ejemplo, id=2)
        $services = Service::with([
            'serviceOrigin.state.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'state');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceOrigin.zone.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'zone');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceDestination.state.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'state');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceDestination.city.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'city');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceDestination.zone.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'zone');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceType.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'servicetype');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'serviceSubCategory.serviceCategories.translations' => function ($query) use ($language_id) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'servicecategory');
                $query->where('language_id', $language_id);
            }
        ])->with([
            'experience' => function ($query) use ($language_id) {
                $query->with([
                    'translations' => function ($query) use ($language_id) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'experience');
                        $query->where('language_id', $language_id);
                    }
                ]);
            }
        ])->with([
            'service_rate' => function ($query) use ($from, $paxTotal) {
                if (Auth::user()->user_type_id == 4) {
                    $query->where('price_dynamic', 0);
                }
                $query->with([
                    'service_rate_plans' => function ($query) use ($from, $paxTotal) {
                        $query->where('date_from', '<=', $from);
                        $query->where('date_to', '>=', $from);
                        $query->where('pax_from', '<=', $paxTotal);
                        $query->where('pax_to', '>=', $paxTotal);
                    }
                ]);
                $query->with(['markup_rate_plan']);
                $query->withCount([
                    'inventory' => function ($query) use ($from, $paxTotal) {
                        $query->where('date', '>=', $from);
                        $query->where('date', '<=', $from);
                        $query->where('locked', '=', 0);
                        $query->where('inventory_num', '>=', $paxTotal);
                    }
                ]);
            }
        ])->with([
            'service_translations' => function ($query) use ($language_id) {
                $query->where('language_id', $language_id);
            }
        ])->with([
            'markup_service' => function ($query) use ($from, $client_id) {
                $query->where('client_id', $client_id);
                $query->where('period', Carbon::parse($from)->format('Y'));
            }
        ])->with([
            'unitDurations' => function ($query) use ($language_id) {
                $query->with([
                    'translations' => function ($query) use ($language_id) {
                        $query->select('id', 'value', 'language_id', 'object_id');
                        $query->where('type', 'unitduration');
                        $query->where('language_id', $language_id);
                    }
                ]);
            }
        ])->with([
            'galleries' => function ($query) {
                $query->select('object_id', 'slug', 'url');
                $query->where('type', 'service');
            },
        ]);

        // Filtro: tarifas vigentes para la fecha
        $services->whereHas('service_rate', function ($query) use ($date_from) {
            if (Auth::user()->user_type_id == 4) {
                $query->where('price_dynamic', 0);
            }
            $query->whereHas('service_rate_plans', function ($q) use ($date_from) {
                $q->where('date_from', '<=', $date_from)
                    ->where('date_to', '>=', $date_from)
                    ->where('status', 1);
            });
        });

        // =====================================================
        // Optimización: Consolidar condiciones para destino
        // =====================================================
        if ($country_id || $state_id || $city_id || $zone_id || $zone_destination) {
            $services->whereHas('serviceDestination', function ($query) use ($country_id, $state_id, $city_id, $zone_id, $zone_destination) {
                if ($country_id)      { $query->where('country_id', $country_id); }
                if ($state_id)        { $query->where('state_id', $state_id); }
                if ($city_id)         { $query->where('city_id', $city_id); }
                if ($zone_id)         { $query->where('zone_id', $zone_id); }
                if ($zone_destination){ $query->orWhere('zone_id', $zone_destination); }
            });
        }

        // Consolidar condiciones para origen
        if ($origin_country_id || $origin_state_id || $origin_city_id || $origin_zone_id) {
            $services->whereHas('serviceOrigin', function ($query) use ($origin_country_id, $origin_state_id, $origin_city_id, $origin_zone_id) {
                if ($origin_country_id) { $query->where('country_id', $origin_country_id); }
                if ($origin_state_id)   { $query->where('state_id', $origin_state_id); }
                if ($origin_city_id)    { $query->where('city_id', $origin_city_id); }
                if ($origin_zone_id)    { $query->where('zone_id', $origin_zone_id); }
            });
        }

        // Filtro por experiencia
        if ($service_experience) {
            $services->whereHas('experience', function ($query) use ($service_experience) {
                $query->where('experience_id', $service_experience);
            });
        }

        // =====================================================
        // Optimización: Cachear traducciones de categorías
        // =====================================================

        if(!is_array($service_category) && $service_category != "") {
            $service_category = [$service_category];
        }

        if (!empty($service_category) && count($service_category) > 0) {
            $cacheKey = 'category_translations_' . implode('_', $service_category);
            $category_trans = Cache::remember($cacheKey, now()->addMinutes(60), function() use ($service_category) {
                return DB::table("translations")
                    ->where("type", "servicecategory")
                    ->where('language_id', 1)
                    ->whereIn('object_id', $service_category)
                    ->get();
            });

            foreach ($category_trans as $cat) {
                if (in_array($cat->value, ["Misceláneos", "Tren", "Asistencia", "Adicional", "Entradas"])) {
                    // Si se cumple, se redefinen las categorías
                    $service_category = DB::table("translations")
                        ->where("type", "servicecategory")
                        ->where('language_id', 1)
                        ->whereIn('value', ["Misceláneos", "Tren", "Asistencia", "Adicional", "Entradas", "Paquete"])
                        ->pluck('object_id')
                        ->toArray();
                } else {
                    $service_category[] = $cat->object_id;
                    $service_category = array_unique($service_category);
                }
            }
            $services->whereHas('serviceSubCategory.serviceCategories', function ($query) use ($service_category) {
                $query->whereIn('id', $service_category);
            });
        }

        if ($service_sub_category) {
            $services->where('service_sub_category_id', $service_sub_category);
        }
        if ($service_type) {
            $services->where('service_type_id', $service_type);
        }

        // =====================================================
        // Optimización: Búsqueda de texto optimizada para service_name
        // =====================================================
        if ($service_name) {
            $filters = explode(',', $service_name);
            for ($i = 0; $i < count($filters); $i++) {
                $_filter = trim($filters[$i]);
                if ($_filter != '') {
                    if ($allWords == 1 || $i == 0) {
                        // AND
                        $services->where(function ($query) use ($_filter, $language_id) {
                            $query->where('name', 'like', '%' . $_filter . '%');
                            $query->orWhere('aurora_code', 'like', '%' . strtoupper($_filter) . '%');
                            $query->orWhereHas('service_translations', function ($query) use ($_filter, $language_id) {
                                $query->where('name_commercial', 'like', '%' . $_filter . '%');
                                $query->orWhere('description', 'like', '%' . $_filter . '%');
                                $query->where('language_id', $language_id);
                            });
                        });
                    } else {
                        // OR
                        $services->orWhere(function ($query) use ($_filter, $language_id) {
                            $query->where('name', 'like', '%' . $_filter . '%');
                            $query->orWhere('aurora_code', 'like', '%' . strtoupper($_filter) . '%');
                            $query->orWhereHas('service_translations', function ($query) use ($_filter, $language_id) {
                                $query->where('name_commercial', 'like', '%' . $_filter . '%');
                                $query->orWhere('description', 'like', '%' . $_filter . '%');
                                $query->where('language_id', $language_id);
                            });
                        });

                    }
                }
            }
        }

        // Filtros generales: cantidad de pax, tipo de servicio, estado, etc.
        $services->where('pax_min', '<=', $paxTotal)
            ->where('pax_max', '>=', $paxTotal)
            ->where('type', Service::TYPE_SERVICE)
            ->where('status', 1)
            ->whereDoesntHave('client_services');

        // =====================================================
        // Optimización: Paginación usando Eloquent paginate()
        // =====================================================
        $servicesResults = $services->orderBy('aurora_code')->paginate($limit);
        $count = $servicesResults->total();

        // ==========================================
        // Procesar resultados: Selección de traducción y markup
        // ==========================================
        $finalServices = [];
        foreach ($servicesResults as $service) {
            // Seleccionar la traducción en idioma solicitado o fallback
            $translation = $service->service_translations->firstWhere('language_id', $language_id)
                ?? $service->service_translations->firstWhere('language_id', 2);
            $service->selected_translation = $translation;

            // Obtener markup: de tarifa, servicio o el del cliente
            if ($service->service_rate->count() > 0 && $service->service_rate[0]->markup_rate_plan->count() > 0) {
                $markup = $service->service_rate[0]->markup_rate_plan[0]->markup;
            } elseif ($service->markup_service->count() > 0) {
                $markup = $service->markup_service[0]->markup;
            } else {
                $markup = $markup_cliente;
            }

            // Calcular precios con markup y validar el rango de precio
            $priceOk = true;
            foreach ($service->service_rate as $service_rate) {
                foreach ($service_rate->service_rate_plans as $service_rate_plan) {
                    $service_rate_plan->markup = trim($markup);
                    $service_rate_plan->price_adult_without_markup = $service_rate_plan->price_adult;
                    $markupValue = ($markup / 100);
                    $price_adult = $service_rate_plan->price_adult * $markupValue;
                    $service_rate_plan->price_adult = roundLito((float)($service_rate_plan->price_adult + $price_adult));
                    if ($priceRange && ($service_rate_plan->price_adult < $priceRange['min'] || $service_rate_plan->price_adult > $priceRange['max'])) {
                        $priceOk = false;
                    }
                }
            }
            if ($priceOk) {
                $finalServices[] = $service;
            }
        }

        // =====================================================
        // Cache de galerías para evitar llamadas repetidas
        // =====================================================
        foreach ($finalServices as $service) {
            $cacheKey = 'service_gallery_' . $service->code;
            $images = Cache::remember($cacheKey, now()->addMinutes(60), function() use ($service) {
                return $this->searchGalleryCloudinary('service', $service->id);
            });
            $galleries = [];
            foreach ($images as $image) {
                $galleries[] = [
                    'object_id' => $service->id,
                    'slug'      => 'service_gallery',
                    'url'       => $image,
                ];
            }
            $service->galleries = $galleries;
        }

        // =====================================================
        // Retornar la respuesta
        // =====================================================
        $data = [
            'data'    => $finalServices,
            'count'   => $count,
            'success' => true,
        ];
        return Response::json($data);
    }


    public function form_services(Request $request)
    {
        $lang = $request->input('lang');
        $languages = Language::all()->where('state', '=', 1);
        $language = Language::where('iso', '=', $lang)->first();
        $client_id = $this->getClientId($request);

        $countries = Country::whereHas('businessRegions')
            ->with([
                'translations' => function ($query) use ($language) {
                    $query->select('id', 'value', 'language_id', 'object_id');
                    $query->where('type', 'country');
                    $query->where('language_id', $language->id);
                }
            ])->get(['id']);

        // return response()->json($countries);


        $currencies = Currency::with([
            'translations' => function ($query) use ($language) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'currency');
                $query->where('language_id', $language->id);

            }
        ])->get(['id']);

        $restrictions = Restriction::with([
            'translations' => function ($query) use ($language) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'restriction');
                $query->where('language_id', $language->id);
            }
        ]);

        if (!empty($client_id)) {
            $restrictions = $restrictions->whereHas('client_restrictions', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            });
        } else {
            $restrictions = $restrictions->whereDoesntHave('client_restrictions');
        }

        $restrictions = $restrictions->get();

        $categories = ServiceCategory::with([
            'translations' => function ($query) use ($language) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'servicecategory');
                $query->where('language_id', $language->id);
            }
        ])->get(['id']);

        $service_types = ServiceType::with([
            'translations' => function ($query) use ($language) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'servicetype');
                $query->where('language_id', $language->id);
            }
        ])->get(['id', 'code']);

        $units = Unit::with([
            'translations' => function ($query) use ($language) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'unit');
                $query->where('language_id', $language->id);
            }
        ])->get(['id']);

        $unit_durations = UnitDuration::with([
            'translations' => function ($query) use ($language) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'unitduration');
                $query->where('language_id', $language->id);
            }
        ])->get(['id']);

        $requirements = Requirement::with([
            'translations' => function ($query) use ($language) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'requirement');
                $query->where('language_id', $language->id);
            }
        ])->get(['id']);

        $classifications = Classification::with([
            'translations' => function ($query) use ($language) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'classification');
                $query->where('language_id', $language->id);
            }
        ])->get(['id']);

        $experiences = Experience::with([
            'translations' => function ($query) use ($language) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'experience');
                $query->where('language_id', $language->id);
            }
        ])->get(['id']);

        $physical_intensity = PhysicalIntensity::with([
            'translations' => function ($query) use ($language) {
                $query->select('id', 'value', 'language_id', 'object_id');
                $query->where('type', 'physicalintensity');
                $query->where('language_id', $language->id);
            }
        ])->get(['id']);

        $data = array(
            "languages" => $languages,
            "countries" => $countries,
            "currencies" => $currencies,
            "restrictions" => $restrictions,
            "categories" => $categories,
            "service_types" => $service_types,
            "units" => $units,
            "unit_durations" => $unit_durations,
            "requirements" => $requirements,
            "classifications" => $classifications,
            "experiences" => $experiences,
            "physical_intensity" => $physical_intensity,
        );

        return Response::json(['success' => true, 'data' => $data]);
    }

    public function getConfiguration($id, Request $request)
    {
        $service_configuration = Service::where('id', $id)->select('name', 'aurora_code', 'duration',
            'unit_duration_id')->first();
        return Response::json(['success' => true, 'data' => $service_configuration]);
    }

    public function searchDetails($service_id, Request $request)
    {

        $client_id = $request->input('client_id');
        if (Auth::user()->user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', Auth::user()->id)->first();
            $client_id = $client_id["client_id"];
        }

        $lang = $request->input('lang');

        $user_id = Auth::user()->id;

        $totalPax = $request->input('total_pax');
        $date_out = $request->input('date_out');

        $current_date = Carbon::now('America/Lima')->format('Y-m-d H:i:s');
        $current_date = Carbon::createFromFormat('Y-m-d H:i:s', (string)$current_date, 'America/Lima');

        $date_to = Carbon::parse($date_out);
        $period = $date_to->year;
        $dayOfWeek = $date_to->englishDayOfWeek;
        $time = Carbon::now('America/Lima')->format('H:i:00');
        $date_to_time = $date_out . ' ' . $time;
        $check_in = Carbon::createFromFormat('Y-m-d H:i:s', (string)$date_to_time, 'America/Lima');

        $service_client = Service::where('id', $service_id)
            ->with([
                'serviceType' => function ($query) use ($lang) {
                    $query->with([
                        'translations' => function ($query) use ($lang) {
                            $query->where('type', 'servicetype');
                            $query->whereHas('language', function ($q) use ($lang) {
                                $q->where('iso', $lang);
                            });
                        }
                    ]);
                }
            ])
            ->with([
                'experience' => function ($query) use ($lang) {
                    $query->with([
                        'translations' => function ($query) use ($lang) {
                            $query->whereHas('language', function ($q) use ($lang) {
                                $q->where('iso', $lang);
                            });
                        }
                    ]);
                }
            ])->with([
                'restriction' => function ($query) use ($lang) {
                    $query->with([
                        'translations' => function ($query) use ($lang) {
                            $query->whereHas('language', function ($q) use ($lang) {
                                $q->where('iso', $lang);
                            });
                        }
                    ]);
                }
            ])->with([
                'inclusions' => function ($query) use ($lang) {
                    if (Auth::user()->user_type_id !== 4 or Auth::user()->user_type_id !== 3) {
                        $query->where('see_client', 1);
                    }
                    $query->orderBy('day', 'asc');
                    $query->orderBy('order', 'asc');

                    $query->with([
                        'inclusions.translations' => function ($query) use ($lang) {
                            $query->where('type', 'inclusion');
                            $query->whereHas('language', function ($q) use ($lang) {
                                $q->where('iso', $lang);
                            });
                        }
                    ]);
                }
            ])->with([
                'service_translations' => function ($query) use ($lang) {
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->with([
                'schedules' => function ($query) {
                    $query->with('servicesScheduleDetail');
                }
            ])->with([
                'service_rate' => function ($query) use ($date_to, $totalPax, $period, $lang, $client_id) {
                    $query->select('id', 'name', 'service_id', 'status');
                    $query->where('status', 1);
                    $query->whereDoesntHave('clients_rate_plan', function ($query) use ($period, $client_id) {
                        $query->where('client_id', $client_id);
                        $query->where('period', $period);
                    });
                    $query->with([
                        'service_rate_plans' => function ($query) use ($date_to, $totalPax) {
                            $query->where('date_from', '<=', $date_to)
                                ->where('date_to', '>=', $date_to);
                            $query->where('pax_from', '<=', $totalPax)
                                ->where('pax_to', '>=', $totalPax)
                                ->where('status', 1);
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
                        'inventory' => function ($query) use ($date_to, $totalPax) {
                            $query->where('date', '>=', $date_to);
                            $query->where('date', '<=', $date_to);
                            $query->where('locked', '=', 0);
                            $query->where('inventory_num', '>=', $totalPax);
                        }
                    ])->with([
                        'markup_rate_plan' => function ($query) use ($period, $client_id) {
                            $query->select('markup', 'period', 'service_rate_id');
                            $query->where('client_id', $client_id);
                            $query->where('period', $period);
                        }
                    ])->with([
                        'translations' => function ($query) use ($lang) {
                            $query->whereHas('language', function ($q) use ($lang) {
                                $q->where('iso', $lang);
                            });
                        }
                    ]);
                }
            ])->first()->toArray();
//return Response::json( $service_client ); die;
        $services = [];
        $index_service = 0;

        $services[$index_service]['id'] = $service_client['id'];
        $services[$index_service]['name'] = $service_client['name'];
        $services[$index_service]['code'] = $service_client['aurora_code'];
        $services[$index_service]['coordinates'] = [
            'latitude' => $service_client['latitude'],
            'longitude' => $service_client['longitude'],
        ];
        $services[$index_service]['reserve_from_days'] = $service_client['qty_reserve'];
        $services[$index_service]['equivalence'] = $service_client['equivalence_aurora'];
        $services[$index_service]['affected_igv'] = $service_client['affected_igv'];
        $services[$index_service]['affected_markup'] = $service_client['affected_markup'];
        $services[$index_service]['allows_guide'] = $service_client['allow_guide'];
        $services[$index_service]['allows_child'] = $service_client['allow_child'];
        $services[$index_service]['allows_infant'] = $service_client['allow_infant'];
        $services[$index_service]['confirmation_hours_limit'] = $service_client['limit_confirm_hours'];
        $services[$index_service]['include_accommodation'] = $service_client['include_accommodation'];

        //tipo de servicio
        $services[$index_service]['service_type'] = [
            'id' => $service_client['service_type']['id'] ?? null,
            'name' => (isset($service_client['service_type']['translations']) && count($service_client['service_type']['translations']) > 0) ? $service_client['service_type']['translations'][0]['value'] : '',
            'code' => $service_client['service_type']['code'] ?? '',
        ];

        $itinerary = [];
        if (!empty($service_client['service_translations'][0]['itinerary'])) {
            $itinerary = $this->getFormatItinerary($service_client['service_translations'][0]['itinerary']);
        }
        //Descripcion
        $services[$index_service]['descriptions'] = [
            'name_commercial' => (isset($service_client['service_translations']) && count($service_client['service_translations']) > 0) ? $service_client['service_translations'][0]['name_commercial'] : '',
            'description' => (isset($service_client['service_translations']) && count($service_client['service_translations']) > 0) ? $service_client['service_translations'][0]['description'] : '',
            'itinerary' => $itinerary,
            'summary' => (isset($service_client['service_translations']) && count($service_client['service_translations']) > 0) ? $service_client['service_translations'][0]['summary'] : '',
            'remarks' => $service_client['notes'] ?? '',
        ];

//            $itineraryExplode = explode($textSearch, $service_client['service_translations'][0]['itinerary']);
//            foreach ($itineraryExplode as $itinerary) {
//                if($itinerary !== ''){
//                    $services[$index_service]['descriptions']['itinerary'][] = trim(strip_tags(html_entity_decode($itinerary)));
//                }
//            }

        $services[$index_service]['experiences'] = [];
        //Experiencias
        foreach ($service_client['experience'] as $experience) {
            $services[$index_service]['experiences'][] = [
                'id' => $experience['id'],
                'name' => (isset($experience['translations']) && count($experience['translations']) > 0) ? $experience['translations'][0]['value'] : '',
                'color' => $experience['color'],
            ];
        }

        $services[$index_service]['restrictions'] = [];
        //restricciones
        foreach ($service_client['restriction'] as $restriction) {
            $services[$index_service]['restrictions'][] = [
                'id' => $restriction['id'],
                'name' => (isset($restriction['translations']) && count($restriction['translations']) > 0) ? $restriction['translations'][0]['value'] : '',
            ];
        }

        $services[$index_service]['operations']['turns'] = [];
        //Detalle dia a dia de las operaciones

        $key_ = 0;

        foreach ($service_client['schedules'] as $keyA_ => $itemA_) {

            $operabilities = ServiceOperation::where('service_id', $services[$index_service]['id'])
                ->where('service_schedule_id', $itemA_['id'])
                ->with([
                    'services_operation_activities.service_type_activities.translations' => function ($query) use (
                        $lang
                    ) {
                        $query->where('type', 'servicetypeactivity');
                        $query->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    }
                ])->get()->toArray();

            $key = 0;
            foreach ($operabilities as $keyA => $itemA) {
                $start_time = $itemA['start_time'];
                $services[$index_service]['operations']['turns'][$key_][$key] = [
                    'day' => $itemA['day'],
                    'departure_time' => $itemA['start_time'],
                    'shifts_available' => $itemA['shifts_available'],
                    'detail' => [],
                ];
                foreach ($itemA['services_operation_activities'] as $keyB => $itemB) {
                    if ($keyB > 0) {
                        $count = count($services[$index_service]['operations']['turns'][$key_][$key]['detail']);
                        $start_time = $services[$index_service]['operations']['turns'][$key_][$key]['detail'][$count - 1]['end_time'];
                    }
                    $start_end = Carbon::createFromFormat('H:i:s',
                        $start_time)->addMinutes($itemB['minutes'])->toTimeString();

                    $services[$index_service]['operations']['turns'][$key_][$key]['detail'][] = [
                        'detail' => (count($itemB['service_type_activities']['translations']) == 0) ? '' : $itemB['service_type_activities']['translations'][0]['value'],
                        'start_time' => $start_time,
                        'end_time' => $start_end,
                    ];
                }
                $key++;
            }
            $key_++;
        }

        //Dias de operacion y horarios
        if (isset($service_client['service_rate']) && count($service_client['service_rate']) > 0 && count($service_client['service_rate'][0]['service_rate_plans']) > 0) {
            $services[$index_service]['operations']['days'] = [
                'monday' => $service_client['service_rate'][0]['service_rate_plans'][0]['monday'],
                'tuesday' => $service_client['service_rate'][0]['service_rate_plans'][0]['tuesday'],
                'wednesday' => $service_client['service_rate'][0]['service_rate_plans'][0]['wednesday'],
                'thursday' => $service_client['service_rate'][0]['service_rate_plans'][0]['thursday'],
                'friday' => $service_client['service_rate'][0]['service_rate_plans'][0]['friday'],
                'saturday' => $service_client['service_rate'][0]['service_rate_plans'][0]['saturday'],
                'sunday' => $service_client['service_rate'][0]['service_rate_plans'][0]['sunday'],
            ];
        } else {
            $services[$index_service]['operations']['days'] = [
                'monday' => 0,
                'tuesday' => 0,
                'wednesday' => 0,
                'thursday' => 0,
                'friday' => 0,
                'saturday' => 0,
                'sunday' => 0,
            ];
        }

        $services[$index_service]['operations']['schedule'] = [];
        //Horario de opearcion
        $schedules = $service_client['schedules'];

        if (count($schedules) == 0) {
            $service_start_time = Carbon::createFromFormat('H:i:s', '00:00:00', 'America/Lima');
        } else {
            $time = '00:00:00';
            if (count($schedules[0]['services_schedule_detail']) > 0) {
                $time_week = $schedules[0]['services_schedule_detail'][0][strtolower($dayOfWeek)];
                $time = (is_null($time_week)) ? '00:00:00' : $time_week;
            }
            if (Carbon::parse($check_in)->format('Y-m-d') == Carbon::parse($current_date)->format('Y-m-d')) {
                $service_start_time = Carbon::createFromFormat('H:i:s', $time, 'America/Lima');
            } else {
                $check_in = Carbon::createFromFormat('Y-m-d H:i:s', $date_out . ' ' . '00:00:00',
                    'America/Lima');
                $service_start_time = Carbon::createFromFormat('Y-m-d H:i:s', $date_out . ' ' . $time,
                    'America/Lima');
            }
        }


        foreach ($schedules as $schedule) {
            $monday = (isset($schedule['services_schedule_detail'][0]['monday']) && isset($schedule['services_schedule_detail'][1]['monday'])) ? $schedule['services_schedule_detail'][0]['monday'] . ' - ' . $schedule['services_schedule_detail'][1]['monday'] : '';
            $tuesday = (isset($schedule['services_schedule_detail'][0]['tuesday']) && isset($schedule['services_schedule_detail'][1]['tuesday'])) ? $schedule['services_schedule_detail'][0]['tuesday'] . ' - ' . $schedule['services_schedule_detail'][1]['tuesday'] : '';
            $wednesday = (isset($schedule['services_schedule_detail'][0]['wednesday']) && isset($schedule['services_schedule_detail'][1]['wednesday'])) ? $schedule['services_schedule_detail'][0]['wednesday'] . ' - ' . $schedule['services_schedule_detail'][1]['wednesday'] : '';
            $thursday = (isset($schedule['services_schedule_detail'][0]['thursday']) && isset($schedule['services_schedule_detail'][1]['thursday'])) ? $schedule['services_schedule_detail'][0]['thursday'] . ' - ' . $schedule['services_schedule_detail'][1]['thursday'] : '';
            $friday = (isset($schedule['services_schedule_detail'][0]['friday']) && isset($schedule['services_schedule_detail'][1]['friday'])) ? $schedule['services_schedule_detail'][0]['friday'] . ' - ' . $schedule['services_schedule_detail'][1]['friday'] : '';
            $saturday = (isset($schedule['services_schedule_detail'][0]['saturday']) && isset($schedule['services_schedule_detail'][1]['saturday'])) ? $schedule['services_schedule_detail'][0]['saturday'] . ' - ' . $schedule['services_schedule_detail'][1]['saturday'] : '';
            $sunday = (isset($schedule['services_schedule_detail'][0]['sunday']) && isset($schedule['services_schedule_detail'][1]['sunday'])) ? $schedule['services_schedule_detail'][0]['sunday'] . ' - ' . $schedule['services_schedule_detail'][1]['sunday'] : '';
            $services[$index_service]['operations']['schedule'][] = [
                'monday' => $monday,
                'tuesday' => $tuesday,
                'wednesday' => $wednesday,
                'thursday' => $thursday,
                'friday' => $friday,
                'saturday' => $saturday,
                'sunday' => $sunday
            ];
        }


        if ($service_client['service_type']['id'] === 1) {
            $week_name = strtolower($date_to->format('l'));
            $reserve_time = (count($schedules) > 0 && count($schedules[0]['services_schedule_detail']) > 0) ? $schedules[0]['services_schedule_detail'][0][$week_name] : Carbon::now()->format('H:m');
            $services[$index_service]['reservation_time'] = $reserve_time;
        } elseif ($service_client['service_type']['id'] === 2) {
            $services[$index_service]['reservation_time'] = '';
        } else {
            $services[$index_service]['reservation_time'] = Carbon::now()->format('H:m');
        }

        // Inclusiones foreach ($services_client['data'] as $index_service => $service_client) {
        $inclusions = collect($service_client['inclusions'])->groupBy('day')->values();
        foreach ($inclusions as $index_day => $inclusion_day) {
            $_day = Carbon::parse($date_to);
            $_date = $_day->addDays($index_day);
            $_day_name = strtolower($_date->englishDayOfWeek);
            foreach ($inclusion_day as $inclusion) {
                //incluye
                if ($inclusion['include']) {
                    $inclusion_available = ($inclusion['inclusions'][$_day_name] == 0) ? false : true;
                    $services[$index_service]['inclusions'][$inclusion['day']]['include'][] = [
                        'day' => $inclusion['day'],
                        'date' => Carbon::parse($_day)->format('Y-m-d'),
                        'name' => (count($inclusion['inclusions']['translations']) > 0) ? $inclusion['inclusions']['translations'][0]['value'] : '',
                        'available_days' => [
                            'available_day' => $inclusion_available,
                            'days' => [
                                'monday' => (boolean)$inclusion['inclusions']['monday'],
                                'tuesday' => (boolean)$inclusion['inclusions']['tuesday'],
                                'wednesday' => (boolean)$inclusion['inclusions']['wednesday'],
                                'thursday' => (boolean)$inclusion['inclusions']['thursday'],
                                'friday' => (boolean)$inclusion['inclusions']['friday'],
                                'saturday' => (boolean)$inclusion['inclusions']['saturday'],
                                'sunday' => (boolean)$inclusion['inclusions']['sunday'],
                            ]
                        ],
                    ];
                }
                //no incluye
                if (!$inclusion['include']) {
                    $services[$index_service]['inclusions'][$inclusion['day']]['no_include'][] = [
                        'day' => $inclusion['day'],
                        'date' => Carbon::parse($_day)->format('Y-m-d'),
                        'name' => (count($inclusion['inclusions']['translations']) > 0) ? $inclusion['inclusions']['translations'][0]['value'] : '',
                    ];
                }
            }
        }

        if (count($service_client['inclusions']) > 0) {
            $services[$index_service]['inclusions'] = array_values($services[$index_service]['inclusions']);
        } else {
            $services[$index_service]['inclusions'] = [];
        }

        return Response::json($services[0]);

    }

    public function syncUpdateStatus($code, $equivalance, Request $request)
    {
        try {
            $service = Service::where('aurora_code', $code)->where('equivalence_aurora', $equivalance)->firstOrFail();
            if ($request->input("status")) {
                $service->status = true;
            } else {
                $service->status = false;
            }
            $service->save();
            return Response::json(['success' => true]);
        } catch (ModelNotFoundException $ex) {
            return Response::json([
                'success' => false,
                'message' => 'not found service: ' . $code . '-' . $equivalance
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json([
                'success' => false,
                'message' => $e->getMessage() . ' - ' . $e->getLine()
            ]);
        }
    }

    public function syncUpdateGeo($code, $equivalance, Request $request)
    {
        try {
            $service = Service::where('aurora_code', $code)->where('equivalence_aurora', $equivalance)->firstOrFail();
            $service->latitude = $request->input("latitude");
            $service->longitude = $request->input("longitude");
            $service->save();
            return Response::json(['success' => true]);
        } catch (ModelNotFoundException $ex) {
            return Response::json([
                'success' => false,
                'message' => 'not found service: ' . $code . '-' . $equivalance
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json([
                'success' => false,
                'message' => $e->getMessage() . ' - ' . $e->getLine()
            ]);
        }
    }

    public function syncUpdateTexts($code, $equivalance, Request $request)
    {

        try {
            DB::beginTransaction();
            $service = Service::where('aurora_code', $code)->where('equivalence_aurora', $equivalance)->firstOrFail();
            $language = Language::where('iso', $request->input("lang"))->first();
            $service_translations = ServiceTranslation::where('service_id', $service->id)->where('language_id',
                $language->id)->first();
            $service_translations->name_commercial = $request->input("name_commercial");
            $service_translations->description = $request->input("name_commercial");
            $service_translations->itinerary = $request->input("itinerary");
            $service_translations->summary = $request->input("summary");
            $service_translations->summary = $request->input("summary");
            $service_translations->save();
            DB::commit();
            return Response::json(['success' => true]);
        } catch (ModelNotFoundException $ex) {
            DB::rollback();
            return Response::json([
                'success' => false,
                'message' => 'not found service: ' . $code . '-' . $equivalance
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json([
                'success' => false,
                'message' => $e->getMessage() . ' - ' . $e->getLine()
            ]);
        }
    }

    public function syncStore(Request $request)
    {
        try {
            DB::beginTransaction();
            $service_find = Service::where('aurora_code', $request->input("aurora_code"))
                ->where('equivalence_aurora', $request->input("aurora_equivalence"))->get(['id']);
            if ($service_find->count() === 0) {

                $service = new Service();
                $service->aurora_code = $request->input("aurora_code");
                $service->equivalence_aurora = $request->input("aurora_equivalence");
                $service->name = $request->input("name_service");
                $service->currency_id = 2;
                $service->latitude = 0;
                $service->longitude = 0;
                $service->qty_reserve = 1;
                $service->affected_igv = 1;
                $service->affected_markup = 1;
                $service->affected_schedule = 1;
                $service->allow_guide = 0;
                $service->allow_child = 0;
                $service->allow_infant = 0;
                $service->limit_confirm_hours = 0;
                $service->unit_duration_limit_confirmation = 0;
                $service->infant_min_age = 1;
                $service->infant_max_age = 1;
                $service->include_accommodation = $request->input("include_accommodation");
                $service->unit_id = 1;
                $service->unit_duration_id = 1;
                $service->unit_duration_reserve = 2;
                //Busco la clasificacion
                $classification = Translation::where('type', 'classification')
                    ->where('language_id', 1)->where('slug', 'classification_name')
                    ->where('value', $request->input("classification_name"))->get();

                if ($classification->count() > 0) {
                    $service->classification_id = $classification[0]->object_id;
                }
                //Busco la sub categoria
                $sub_category = Translation::where('type', 'servicesubcategory')
                    ->where('language_id', 1)->where('value', $request->input("service_sub_category"))->get();
                if ($sub_category->count() > 0) {
                    $service->service_sub_category_id = $sub_category[0]->object_id;
                }
                $service->user_id = 1;
                $service->date_solicitude = Carbon::now();
                $service->duration = $request->input("duration");
                $service->pax_min = 1;
                $service->pax_max = 10;
                $service->min_age = 1;
                $service->require_itinerary = 1;
                $service->require_image_itinerary = 1;
                $service->status = 0;
                $service->service_type_id = $request->input("service_type_id");
                $service->save();
                $langs = Language::where('state', 1)->get();

                //--------------------------------------GUARDO EL ORIGEN Y DESTINOS
                $state_origin_id = 1610;
                $country_origin_id = 89;
                $city_origin_id = null;
                $state_destiny_id = 1610;
                $country_destiny_id = 89;
                $city_destiny_id = null;
                //Busco la ciudad de destino
                $origin = City::where('iso', $request->input('origin'))
                    ->with('state')->get();

                if ($origin->count() > 0) {
                    $city_origin_id = $origin[0]->id;
                    $state_origin_id = $origin[0]->state_id;
                    $country_origin_id = $origin[0]->state->country_id;
                } else {
                    $origin = State::where('iso', $request->input('origin'))
                        ->with('state')->get();
                    if ($origin->count() > 0) {
                        $city_origin_id = null;
                        $state_origin_id = $origin[0]->id;
                        $country_origin_id = $origin[0]->country_id;
                    }
                }


                $service_origin = new ServiceOrigin;
                $service_origin->service_id = $service->id;
                $service_origin->country_id = $country_origin_id;
                $service_origin->state_id = $state_origin_id;
                $service_origin->city_id = $city_origin_id;
                $service_origin->save();


                //Busqueda de destinos
                $destiny = City::where('iso', $request->input('destiny'))
                    ->with('state')->get();
                if ($destiny->count() > 0) {
                    $city_destiny_id = $destiny[0]->id;
                    $state_destiny_id = $destiny[0]->state_id;
                    $country_destiny_id = $destiny[0]->state->country_id;
                } else {
                    $destiny = State::where('iso', $request->input('origin'))
                        ->with('state')->get();
                    if ($destiny->count() > 0) {
                        $city_destiny_id = null;
                        $state_destiny_id = $destiny[0]->id;
                        $country_destiny_id = $destiny[0]->country_id;
                    }
                }

                $service_detiny = new ServiceDestination();
                $service_detiny->service_id = $service->id;
                $service_detiny->country_id = $country_destiny_id;
                $service_detiny->state_id = $state_destiny_id;
                $service_detiny->city_id = $city_destiny_id;
                $service_detiny->save();

                foreach ($langs as $lang) {
                    $service_translation = new ServiceTranslation();
                    $service_translation->language_id = $lang->id;
                    $service_translation->service_id = $service->id;
                    $service_translation->name_commercial = $request->input("name_service");
                    $service_translation->description = '';
                    $service_translation->summary = '';
                    $service_translation->itinerary = '';
                    $service_translation->save();
                }
            } else {
                return Response::json([
                    'success' => false,
                    'message' => 'the service exists: ' . $request->input("aurora_code") . '-' . $request->input("aurora_equivalence")
                ]);
            }

            DB::commit();
            return Response::json(['success' => true]);
        } catch (ModelNotFoundException $ex) {
            DB::rollback();
            return Response::json([
                'success' => false,
                'message' => 'not found service: ' . $request->input("aurora_code") . '-' . $request->input("aurora_equivalence")
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json([
                'success' => false,
                'message' => $e->getMessage() . ' - ' . $e->getLine()
            ]);
        }
    }

    public function syncUpdate($code, $equivalance, Request $request)
    {
        try {
            DB::beginTransaction();
            $service = Service::where('aurora_code', $code)->where('equivalence_aurora', $equivalance)->firstOrFail();
            //Busco la sub categoria
            $sub_category = Translation::where('type', 'servicesubcategory')
                ->where('language_id', 1)->where('value', $request->input("service_sub_category"))->get();
            if ($sub_category->count() > 0) {
                $service->service_sub_category_id = $sub_category[0]->object_id;
            }

            //Busco la clasificacion
            $classification = Translation::where('type', 'classification')
                ->where('language_id', 1)->where('slug', 'classification_name')
                ->where('value', $request->input("classification_name"))->get();
            if ($classification->count() > 0) {
                $service->classification_id = $classification[0]->object_id;
            }

            $state_origin_id = '';
            $country_origin_id = '';
            $city_origin_id = null;
            $state_destiny_id = '';
            $country_destiny_id = '';
            $city_destiny_id = null;
            //Busco la ciudad de destino
            $origin = City::where('iso', $request->input('origin'))
                ->with('state')->get();
            if ($origin->count() > 0) {
                $city_origin_id = $origin[0]->id;
                $state_origin_id = $origin[0]->state_id;
                $country_origin_id = $origin[0]->state->country_id;
            } else {
                $origin = State::where('iso', $request->input('origin'))
                    ->with('state')->get();
                if ($origin->count() > 0) {
                    $city_origin_id = null;
                    $state_origin_id = $origin[0]->id;
                    $country_origin_id = $origin[0]->country_id;
                }
            }

            if ($country_origin_id !== '' and $state_origin_id !== '') {
                $service_origin = ServiceOrigin::where('service_id', $service->id)->first();
                $service_origin->country_id = $country_origin_id;
                $service_origin->state_id = $state_origin_id;
                $service_origin->city_id = $city_origin_id;
                $service_origin->save();
            }

            //Busqueda de destinos
            $destiny = City::where('iso', $request->input('destiny'))
                ->with('state')->get();
            if ($destiny->count() > 0) {
                $city_destiny_id = $destiny[0]->id;
                $state_destiny_id = $destiny[0]->state_id;
                $country_destiny_id = $destiny[0]->state->country_id;
            } else {
                $destiny = State::where('iso', $request->input('origin'))
                    ->with('state')->get();
                if ($destiny->count() > 0) {
                    $city_destiny_id = null;
                    $state_destiny_id = $destiny[0]->id;
                    $country_destiny_id = $destiny[0]->country_id;
                }
            }

            if ($country_destiny_id !== '' and $state_destiny_id !== '') {
                $service_detiny = ServiceDestination::where('service_id', $service->id)->first();
                $service_detiny->country_id = $country_destiny_id;
                $service_detiny->state_id = $state_destiny_id;
                $service_detiny->city_id = $city_destiny_id;
                $service_detiny->save();
            }

            $service->service_type_id = $request->input("service_type_id");
            $service->duration = $request->input("duration");
            $service->include_accommodation = $request->input("include_accommodation");
            $service->save();

            //Busco las experiencas por nombre
            $experiences_names = ($request->input("experiences") == null) ? [] : $request->input("experiences");

            $experiences = [];
            foreach ($experiences_names as $experience) {
                $experience = Translation::where('type', 'experience')
                    ->where('language_id', 1)->where('slug', 'experience_name')
                    ->where('value', $experience)->first();
                if ($experience) {
                    array_push($experiences, $experience->object_id);
                }
            }
            //Restricciones
            $service->experience()->sync($experiences);

            DB::commit();
            return Response::json(['success' => true]);
        } catch (ModelNotFoundException $ex) {
            DB::rollback();
            return Response::json([
                'success' => false,
                'message' => 'not found service: ' . $code . '-' . $equivalance
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json([
                'success' => false,
                'message' => $e->getMessage() . ' - ' . $e->getLine()
            ]);
        }
    }

    public function syncUpdateRates(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'codigo' => 'required',
                'nroequ' => 'required',
                'rates' => 'required'
            ]);

            if ($validator->fails()) {
                return Response::json(['success' => false, 'message' => (string)$validator->getMessageBag()]);
            } else {
                $code = $request->input('codigo');
                $nroequ = $request->input('nroequ');
                $rates = $request->input('rates');
                DB::transaction(function () use ($code, $nroequ, $rates) {
                    $service = Service::where('aurora_code', $code)->where('equivalence_aurora', $nroequ)
                        ->with([
                            'service_rate' => function ($query) {
                                $query->select('id', 'name', 'service_id', 'status');
                                $query->where('status', 1);
                                $query->with([
                                    'service_rate_plans' => function ($query) {
                                        $query->where('status', 1);
                                    }
                                ]);
                            }
                        ])->firstOrFail();
                    $service_rate = $service->service_rate;

                    //Creo una nueva tarifa si no existe
                    if ($service_rate->count() === 0) {
                        $service_rate = new ServiceRate();
                        $service_rate->name = 'Tarifa';
                        $service_rate->allotment = 1;
                        $service_rate->taxes = 0;
                        $service_rate->services = 0;
                        $service_rate->advance_sales = 0;
                        $service_rate->promotions = 0;
                        $service_rate->status = 1;
                        $service_rate->service_id = $service->id;
                        $service_rate->service_type_rate_id = 1;
                        $service_rate->save();
                        $names = [
                            1 => ['id' => '', 'commercial_name' => 'Tarifa'],
                            2 => ['id' => '', 'commercial_name' => 'Tarifa'],
                            3 => ['id' => '', 'commercial_name' => 'Tarifa'],
                            4 => ['id' => '', 'commercial_name' => 'Tarifa'],
                        ];
                        $this->saveTranslation($names, 'servicerate', $service_rate->id);
                        foreach ($rates as $rate) {
                            $service_rate_plan = new ServiceRatePlan();
                            $service_rate_plan->service_rate_id = $service_rate->id;
                            $service_rate_plan->service_cancellation_policy_id = 1;
                            $service_rate_plan->user_id = 3182;
                            $service_rate_plan->date_from = Carbon::parse($rate['date_from'])->format('Y-m-d');
                            $service_rate_plan->date_to = Carbon::parse($rate['date_to'])->format('Y-m-d');
                            $service_rate_plan->monday = 1;
                            $service_rate_plan->tuesday = 1;
                            $service_rate_plan->wednesday = 1;
                            $service_rate_plan->thursday = 1;
                            $service_rate_plan->friday = 1;
                            $service_rate_plan->saturday = 1;
                            $service_rate_plan->sunday = 1;
                            $service_rate_plan->pax_from = $rate['canpax'];
                            $service_rate_plan->pax_to = $rate['canpax'];
                            $service_rate_plan->price_adult = $rate['price'];
                            $service_rate_plan->price_child = (isset($rate['price_child'])) ? $rate['price_child'] : 0;
                            $service_rate_plan->price_infant = 0;
                            $service_rate_plan->price_guide = 0;
                            $service_rate_plan->status = 1;
                            $service_rate_plan->flag_migrate = 0;
                            $service_rate_plan->save();
                        }
                    } else {
                        $firstRate = ServiceRatePlan::where('service_rate_id', $service_rate[0]->id)
                            ->whereYear('date_from', '=', Carbon::parse($rates[0]['date_from'])->format('Y'))->get();

                        if ($firstRate->count() === 0) {
                            $yearBefore = Carbon::parse($rates[0]['date_from'])->format('Y') - 1;

                            $rateOneYearBefore = ServiceRatePlan::where('service_rate_id', $service_rate[0]->id)
                                ->whereYear('date_from', '=', $yearBefore)->first();

                            if ($rateOneYearBefore) {
                                $provider_id = $rateOneYearBefore->user_id;
                                $service_cancellation_policy_id = $rateOneYearBefore->service_cancellation_policy_id;
                            } else {
                                $provider_id = 3182;
                                $service_cancellation_policy_id = 1;
                            }

                            ServiceRatePlan::where('service_rate_id', $service_rate[0]->id)
                                ->whereYear('date_from', '=',
                                    Carbon::parse($rates[0]['date_from'])->format('Y'))->delete();

                            foreach ($rates as $rate) {
                                $service_rate_plan = new ServiceRatePlan();
                                $service_rate_plan->service_rate_id = $service_rate[0]->id;
                                $service_rate_plan->service_cancellation_policy_id = $service_cancellation_policy_id;
                                $service_rate_plan->user_id = $provider_id;
                                $service_rate_plan->date_from = Carbon::parse($rate['date_from'])->format('Y-m-d');
                                $service_rate_plan->date_to = Carbon::parse($rate['date_to'])->format('Y-m-d');
                                $service_rate_plan->monday = 1;
                                $service_rate_plan->tuesday = 1;
                                $service_rate_plan->wednesday = 1;
                                $service_rate_plan->thursday = 1;
                                $service_rate_plan->friday = 1;
                                $service_rate_plan->saturday = 1;
                                $service_rate_plan->sunday = 1;
                                $service_rate_plan->pax_from = $rate['canpax'];
                                $service_rate_plan->pax_to = $rate['canpax'];
                                $service_rate_plan->price_adult = $rate['price'];
                                $service_rate_plan->price_child = (isset($rate['price_child'])) ? $rate['price_child'] : 0;
                                $service_rate_plan->price_infant = 0;
                                $service_rate_plan->price_guide = 0;
                                $service_rate_plan->status = 1;
                                $service_rate_plan->flag_migrate = 0;
                                $service_rate_plan->save();
                            }
                        } else {
                            ServiceRatePlan::where('service_rate_id', $service_rate[0]->id)
                                ->whereYear('date_from', '=',
                                    Carbon::parse($rates[0]['date_from'])->format('Y'))->delete();

                            foreach ($rates as $rate) {

                                $first_ = ServiceRatePlan::where('service_rate_id', $service_rate[0]->id)
                                    ->where('date_from', Carbon::parse($rate['date_from'])->format('Y-m-d'))
                                    ->where('date_to', Carbon::parse($rate['date_to'])->format('Y-m-d'))
                                    ->where('pax_from', $rate['canpax'])
                                    ->where('pax_to', $rate['canpax'])
                                    ->orderBy('deleted_at', 'desc')
                                    ->withTrashed()
                                    ->first();

                                if ($first_) {
                                    $service_cancellation_policy_id_ = $first_->service_cancellation_policy_id;
                                } else {
                                    $service_cancellation_policy_id_ = $firstRate[0]->service_cancellation_policy_id;
                                }

                                $service_rate_plan = new ServiceRatePlan();
                                $service_rate_plan->service_rate_id = $service_rate[0]->id;
                                $service_rate_plan->service_cancellation_policy_id = $service_cancellation_policy_id_;
                                $service_rate_plan->user_id = $firstRate[0]->user_id;
                                $service_rate_plan->date_from = Carbon::parse($rate['date_from'])->format('Y-m-d');
                                $service_rate_plan->date_to = Carbon::parse($rate['date_to'])->format('Y-m-d');
                                $service_rate_plan->monday = 1;
                                $service_rate_plan->tuesday = 1;
                                $service_rate_plan->wednesday = 1;
                                $service_rate_plan->thursday = 1;
                                $service_rate_plan->friday = 1;
                                $service_rate_plan->saturday = 1;
                                $service_rate_plan->sunday = 1;
                                $service_rate_plan->pax_from = $rate['canpax'];
                                $service_rate_plan->pax_to = $rate['canpax'];
                                $service_rate_plan->price_adult = $rate['price'];
                                $service_rate_plan->price_child = (isset($rate['price_child'])) ? $rate['price_child'] : 0;
                                $service_rate_plan->price_infant = 0;
                                $service_rate_plan->price_guide = 0;
                                $service_rate_plan->status = 1;
                                $service_rate_plan->flag_migrate = 0;
                                $service_rate_plan->save();
                            }
                        }


                    }
                });

                return Response::json(['success' => true, 'message' => '']);
            }

        } catch (ModelNotFoundException $ex) {
            DB::rollback();
            return Response::json([
                'success' => false,
                'message' => 'Not found service: ' . $code . '-' . $nroequ
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json([
                'success' => false,
                'message' => $e->getMessage() . ' - ' . $e->getLine()
            ]);
        }
    }

    public function updateRatesInPackages($service_id)
    {

        $package_services = PackageService::where('type', 'service')
            ->where('object_id', $service_id)
            ->get();

        $_verify_id_category = [];
        $_n = 0;

        foreach ($package_services as $package_service) {
            if (!isset($_verify_id_category[$package_service->package_plan_rate_category_id])) {
                $_verify_id_category[$package_service->package_plan_rate_category_id] = true;
                $_n++;
                $this->calculatePricePackage($package_service->package_plan_rate_category_id);
            }
        }

        return Response::json([
            'success' => true,
            'package_plan_rate_categories_ids' => $_verify_id_category,
            'total' => $_n
        ]);

    }

    public function ordersRate(Request $request)
    {
        $service_code = $request->get('filter_service_code');

        $filter_city_id = $request->get('filter_city_id');

        $filter_subcategory_id = $request->get('filter_subcategory_id');

        $services_id = ServiceOrigin::where('city_id', $filter_city_id)->pluck('service_id');

        $services = [];
        if (count($services_id) > 0) {
            if ($filter_subcategory_id != null) {
                $services = Service::whereIn('id', $services_id)->where('service_sub_category_id',
                    $filter_subcategory_id)->whereHas('service_rate', function ($q) {
                    $q->where('rate', 1);
                })->where('status', 1)->orderBy('rate_order')->paginate(10);
            } else {
                $services = Service::whereIn('id', $services_id)->whereHas('service_rate', function ($q) {
                    $q->where('rate', 1);
                })->where('status', 1)->orderBy('rate_order')->paginate(10);
            }

            return \response()->json($services);
        }

        return \response()->json($services);
    }

    public function updateOrderService(Request $request)
    {

        DB::transaction(function () use ($request) {

            $services_id = ServiceOrigin::where('city_id', $request->input('city_id'))->pluck('service_id');

            $order = $request->input('order');

            $rate_order_services = Service::where('rate_order', '>=',
                $request->input('order'))->where('service_sub_category_id',
                $request->input('service_sub_category_id'))->whereIn('id', $services_id)->whereHas('service_rate',
                function ($q) {
                    $q->where('rate', 1);
                })->where('status', 1)->where('id', '!=', $request->input('service_id'))->orderBy('rate_order')->get();


            foreach ($rate_order_services as $index => $rate_order_service) {
                $order++;

                $service = Service::find($rate_order_service["id"]);
                $service->rate_order = $order;
                $service->save();
            }
            $rate_order_city = Service::find($request->input('service_id'));
            $rate_order_city->rate_order = $request->input('order');
            $rate_order_city->save();
//        if ($rate_order_service_older != null) {
//            $max_order = Service::where('service_sub_category_id',
//                $request->input('service_sub_category_id'))->whereIn('id',$services_id)->max('rate_order');
//            $max_order += 1;
//
//            $update_order = Service::find($rate_order_service_older->id);
//            $update_order->rate_order = $max_order;
//            $update_order->save();
//        }
        });
        return \response()->json("orden de servicio actualizado");
    }


    public function get_equivalences($id, Request $request)
    {
        $with_components = $request->input("with_components");

        $equivalence_services = $this->search_composition($id, $with_components);

        return Response::json(["success" => true, "data" => $equivalence_services]);

    }

    public function import_more_equivalences()
    {
        set_time_limit(0);
        $equivalence_codes_ = Service::withTrashed()->pluck('equivalence_aurora')->toArray();

        $equivalence_codes = [];
        foreach ($equivalence_codes_ as $equivalence_code) {
            if ((int)($equivalence_code) != 0) {
                array_push($equivalence_codes, $equivalence_code);
            }
        }

        $equivalence_codes_string = implode(',', $equivalence_codes);

        $client = new \GuzzleHttp\Client(['verify' => false]);
        $response = $client->request('POST',
            config('services.stella.domain') . 'api/v1/equivalences/composition', [
                "json" => [
                    'string_equivalence_numbers_not_in' => $equivalence_codes_string
                ]
            ]);
        $response = json_decode($response->getBody()->getContents());

        $response = $this->insert_equivalences($response->data, 0);

        return Response::json($response);
    }

    public function create_or_update_equivalence(Request $request)
    {

        $equivalence = json_decode(json_encode($request->input('data')));
        if (!isset($equivalence->composition)) {
            $equivalence->composition = [];
        }

        $response = $this->insert_equivalences([$equivalence], 0);

        return Response::json($response);
    }

    public function cud_equivalence_services(Request $request)
    {

        $equivalence_services = json_decode(json_encode($request->input('data')));
        $response = $this->update_equivalence_services($equivalence_services->nroref,
            $equivalence_services->composition);

        return Response::json($response);
    }

//    public function test_itinerary(){
//
//        $service = Service::with('service_translations')->find(2860); // IQAP35
//
//        return $this->getFormatItinerary( $service->service_translations[0]->itinerary );
////        return $service->service_translations[0]->itinerary;
//    }


    public function getPackagesByService(Request $request)
    {
        $service_id = $request->get('service_id');
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');

        $lang = ($request->has('lang')) ? $request->input('lang') : 'es';
        $language = Language::where('iso', $lang)->first();
        if ($language->count() > 0 and isset($lang)) {
            $language_id = $language->id;
        } else {
            // $lang = 'es';
            $language_id = 1;
        }

        $response = Service::select("services.id as service_id", DB::raw("if (packages.code is null, case
    when packages.extension = 0 then concat('PAQ',packages.id)
    when packages.extension = 1 then concat('E',packages.id)
    when packages.extension = 2 then concat('P',packages.id)
    else null end ,packages.code) as code"), "package_translations.name", "packages.extension")
            ->join("package_services", function ($join) {
                $join->on("package_services.object_id", "=", "services.id")
                    ->where("package_services.type", "=", "service")
                    ->whereNull("package_services.deleted_at");
            })
            ->join("package_plan_rate_categories", function ($join) {
                $join->on("package_plan_rate_categories.id", "=", "package_services.package_plan_rate_category_id");
            })
            ->join("package_plan_rates", function ($join) {
                $join->on("package_plan_rates.id", "=", "package_plan_rate_categories.package_plan_rate_id")
                    ->where("package_plan_rates.status", "=", 1);
            })
            ->join("packages", function ($join) {
                $join->on("packages.id", "=", "package_plan_rates.package_id")
                    ->where("packages.status", "=", 1);
            })
            ->join("package_translations", function ($join) use ($language_id) {
                $join->on("package_translations.package_id", "=", "packages.id")
                    ->where("package_translations.language_id", "=", $language_id);
            })
            ->where("services.id", $service_id);

        if ($querySearch) {
            $response = $response->where('package_plan_rates.name', 'LIKE', '%' . $querySearch . '%');
        }

        $response = $response->distinct();
        $count = $response->get()->count();

        if ($paging === 1) {
            $response = $response->take($limit)->orderBy('extension', 'desc')->get();
        } else {
            $response = $response->skip($limit * ($paging - 1))->take($limit)
                ->orderBy('extension', 'desc')
                ->get();
        }

        $data = [
            'data' => $response,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function schedules_group(Request $request)
    {
        try
        {
            $codes = $request->__get('codes');

            $service_ids = Service::select(['id', 'aurora_code'])
                ->whereIn('aurora_code', $codes)
                ->get()
                ->pluck('id');

            $schedules = ServiceSchedule::with([
                'servicesScheduleDetail',
                'services' => function ($query) {
                    $query->select(['id', 'aurora_code']);
                }])
                ->whereIn('service_id', $service_ids)
                ->get()->toArray();

            $operations = ServiceOperation::with([
                'services_operation_activities',
                'services' => function ($query) {
                    $query->select('id', 'aurora_code');
                }])
                ->whereIn('service_id', $service_ids)
                ->get()->toArray();

            $services = [];

            foreach($schedules as $schedule)
            {
                if(!isset($services[$schedule['services']['aurora_code']]))
                {
                    $services[$schedule['services']['aurora_code']] = [
                        'schedule' => [],
                        'operations' => [],
                    ];
                }

                $services[$schedule['services']['aurora_code']]['schedule'] = $schedule['services_schedule_detail'][0];
            }

            foreach($operations as $operation)
            {
                if(!isset($services[$operation['services']['aurora_code']]))
                {
                    $services[$operation['services']['aurora_code']] = [
                        'schedule' => [],
                        'operations' => [],
                    ];
                }

                $services[$operation['services']['aurora_code']]['operations'][] = $operation;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'services' => $services,
                ],
            ]);
        }
        catch(\Exception $ex)
        {
            return response()->json($this->throwError($ex));
        }
    }
}
