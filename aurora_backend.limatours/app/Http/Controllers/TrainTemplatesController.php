<?php

namespace App\Http\Controllers;

use App\Galery;
use App\MarkupTrain;
use App\ServiceCancellationPolicies;
use App\TrainCancellationPolicy;
use App\TrainChild;
use App\TrainClass;
use App\TrainClient;
use App\TrainClientRatePlan;
use App\TrainInclusion;
use App\TrainInventory;
use App\TrainMarkupRatePlan;
use App\TrainRate;
use App\TrainTrainClass;
use App\TrainRailRoute;
use App\RailRoute;
use App\Service;
use App\Train;
use App\TrainType;
use App\Translation;
use App\AmenityTrain;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use App\TrainTemplate;
use App\TrainTax;

use Illuminate\Support\Facades\DB;
use App\Http\Traits\Translations;

class TrainTemplatesController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:traintemplates.read')->only('index');
        $this->middleware('permission:traintemplates.create')->only('store');
        $this->middleware('permission:traintemplates.update')->only('update');
        $this->middleware('permission:traintemplates.delete')->only('delete');
    }

    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('queryCustom');
        $byCompany = $request->input('byCompany');

        $train_templates = TrainTemplate::with('train_rail_route.train')
            ->with('train_train_class');

        if( $byCompany != 'all' ){
            $train_templates->whereHas('train_rail_route', function ($query) use ($byCompany) {
                $query->where('train_id', $byCompany);
            });
        }

        if ($querySearch) {

            $train_templates->where(function ($query) use ($querySearch) {

                $query->where('aurora_code', 'like', '%' . $querySearch . '%');

                $query->orWhereHas('train_train_class', function ($query) use ($querySearch) {
                    $query->where('name', 'like', '%' . $querySearch . '%');
                });
                $query->orWhereHas('train_rail_route', function ($query) use ($querySearch) {
                    $query->where('name', 'like', '%' . $querySearch . '%');
                    $query->orWhereHas('train', function ($query) use ($querySearch) {
                        $query->where('name', 'like', '%' . $querySearch . '%');
                    });
                });

            });
        }

        $count = $train_templates->count();

        if ($paging === 1) {
            $train_templates = $train_templates->take($limit)->get();
        } else {
            $train_templates = $train_templates->skip($limit * ($paging - 1))
                ->take($limit)->get();
        }

        $data = [
            'data' => $train_templates,
            'count' => $count,
        ];
        return Response::json($data);
    }

    public function store(Request $request)
    {

        $train = new TrainTemplate();
        $train->status = 1;
        $train->aurora_code = $request->input('aurora_code');
        $train->equivalence_aurora = $request->input('equivalence_aurora');
        $train->train_rail_route_id = $request->input('train_rail_route_id');
        $train->train_train_class_id = $request->input('train_train_class_id');
        $train->country_id = $request->input('country_id');
        $train->state_id = $request->input('state_id');
        $train->city_id = $request->input('city_id');
        $train->district_id = $request->input('district_id');
        $train->zone_id = $request->input('zone_id');
        $train->allow_child = 0;
        $train->allow_infant = 0;
        $train->infant_min_age = 0;
        $train->infant_max_age = 0;

        if ($train->save()) {
            //DESCRIPCION Y DIRECCION
            $names = [
                1 => ['id' => '', 'description' => ''],
                2 => ['id' => '', 'description' => ''],
                3 => ['id' => '', 'description' => ''],
                4 => ['id' => '', 'description' => ''],
            ];
            $this->saveTranslation($names, 'traintemplate', $train->id);
        }

        return Response::json(['success' => true, 'object_id' => $train->id]);
    }

    public function update(Request $request, $train_template_id){

        $train = TrainTemplate::find($train_template_id);
        $train->aurora_code = $request->input('aurora_code');
        $train->train_rail_route_id = $request->input('train_rail_route_id');
        $train->train_train_class_id = $request->input('train_train_class_id');
        $train->country_id = $request->input('country_id');
        $train->state_id = $request->input('state_id');
        $train->city_id = $request->input('city_id');
        $train->district_id = $request->input('district_id');
        $train->zone_id = $request->input('zone_id');
        $train->save();

        return Response::json(['success' => true, 'object_id' => $train->id]);
    }

    public function show($train_template_id)
    {
        $trainTemplate = TrainTemplate::with([
            'train_rail_route',
            'country.taxes',
            "country" => function ($query) {
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'country');
                        $query->where('language_id', 1);
                    },
                ]);
            },
            "zone"=> function ($query) {
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'zone');
                        $query->where('language_id', 1);
                    },
                ]);
            },
            "city"=> function ($query) {
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'city');
                        $query->where('language_id', 1);
                    },
                ]);
            },
            "district"=> function ($query) {
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'district');
                        $query->where('language_id', 1);
                    },
                ]);
            },
            "state"=> function ($query) {
                $query->with([
                    'translations' => function ($query) {
                        $query->select('object_id', 'value');
                        $query->where('type', 'state');
                        $query->where('language_id', 1);
                    },
                ]);
            },
        ])->where('id', $train_template_id)
          ->first();

        foreach ($trainTemplate['country']['taxes'] as $tax) {
            $totalTaxUsed = TrainTax::where('tax_id', $tax->id)
                ->where('train_template_id', $trainTemplate->id)->get()->count();
            $tax->used = (!$totalTaxUsed) ? false : true;
        }

        return Response::json(['success' => true, 'data' => $trainTemplate]);
    }

    public function destroy($id)
    {
        $train_template = TrainTemplate::find($id);

        $train_template->delete();

        $this->deleteTranslation('traintemplate', $id);

        return Response::json(['success' => true]);
    }

    public function updateStatus($id, Request $request)
    {
        $train = TrainTemplate::find($id);
        if ($request->input("status")) {
            $train->status = 1;
        } else {
            $train->status = 0;
        }
        $train->save();
        return Response::json(['success' => true]);
    }

    public function getTranslations($train_id)
    {
        $translations = Translation::where('type', 'traintemplate')
            ->where('object_id', $train_id)
            ->with([
                'language'
            ])->get();

        $data = [
            'data' => $translations,
            'success' => true
        ];

        return Response::json($data);
    }


    public function updateTranslations($train_id, $language_id, Request $request)
    {
        $translation = Translation::where('type', 'traintemplate')
            ->where('object_id', $train_id)->where('language_id',$language_id)->first();
        $translation->value = $request->input('value');
        $translation->save();

        $response = ['success' => true, 'object_id' => $translation->id];

        return Response::json($response);
    }

    public function getAmenities($train_id, Request $request)
    {
        $lang = $request->input("lang");
        $amenities = AmenityTrain::where('train_template_id', $train_id)
            ->with([
                'amenity.translations' => function ($query) use ($lang) {
                    $query->where('type', 'amenity');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])
            ->get();

        $data = [
            'data' => $amenities,
            'success' => true
        ];

        return Response::json($data);
    }


    public function updateAmenities($train_id, Request $request)
    {

        $amenity_ids = $request->input('amenity_ids');

        AmenityTrain::where('train_template_id', $train_id)->delete();

        foreach ( $amenity_ids as $a ){
            $amenity = new AmenityTrain;
            $amenity->train_template_id = $train_id;
            $amenity->amenity_id = $a;
            $amenity->save();
        }

        $response = ['success' => true];

        return Response::json($response);
    }


    public function updateConfigurations($id, Request $request)
    {
        $train = TrainTemplate::find($id);

        $train->allow_child = $request->input("allow_child");
        $train->allow_infant = $request->input("allow_infant");
        $train->infant_min_age = $request->input("infant_min_age");
        $train->infant_max_age = $request->input("infant_max_age");

        $train->save();

        return Response::json(['success' => true]);
    }

    public function updateTax($train_id, Request $request)
    {
        $used = $request->input("used");
        $tax_id = $request->input("tax_id");

        if ($used) {
            $findTax = TrainTax::where('train_template_id', $train_id)->where('tax_id', $tax_id)->get();
            if ($findTax->count() == 0) {
                $trainTax = new TrainTax;
                $trainTax->train_template_id = $train_id;
                $trainTax->tax_id = $tax_id;
                $trainTax->save();
            }
        } else {
            $findTax = TrainTax::where('train_template_id', $train_id)->where('tax_id', $tax_id)->get()->first();
            if ($findTax) {
                $findTax->delete();
            }
        }

        return Response::json(['success' => true]);
    }

    public function importService($service_id, Request $request){

        $language_id = 1;

        $services = Service::with([
            'galleries', 'tax', 'children_ages', 'service_translations', 'service_rate.translations',
            'service_rate.service_rate_plans', 'service_rate.inventory',
            'service_rate.clients_rate_plan', 'service_rate.markup_rate_plan',
            'service_clients', 'markup_service', 'inclusions'])
        ->with([
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
        ]);

        if( $service_id == 'all' ){
            $service_name = "tren";
            $services = $services->where('name', 'like', '%' . $service_name . '%')
                ->orWhere('aurora_code', 'like', '%' . $service_name . '%')
                ->skip($request->input('skip'))->limit( $request->input('limit') );
        } else {
            $services = $services->where('id',$service_id);
        }
        $services = $services->get([
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
            'infant_min_age',
            'infant_max_age',
            'unit_id',
            'unit_duration_id',
            'service_type_id',
            'classification_id',
            'service_sub_category_id',
            'user_id',
            'date_solicitude',
            'duration',
            'status'
        ]);
//        return $services;
        $imports = [];
        foreach ($services as $service) {
            // codigo aurora ya existe en train templates
            $train_templates = TrainTemplate::where('aurora_code', $service->aurora_code)->count();
            if( $train_templates > 0 ){
                array_push( $imports, array(
                    "code" => $service->aurora_code,
                    "success" => false,
                    "message" => "codigo aurora ya existe en train templates"
                ) );
            } else {
                $train_template = new TrainTemplate();
                $train_template->aurora_code = $service->aurora_code;
                $train_template->status = 0;
                $train_template->train_rail_route_id = self::getBasicTrainRailRouteID();
                $train_template->train_train_class_id = self::getBasicTrainTrainClassID();
                $train_template->country_id = 89;
                $train_template->state_id = 1603;
                $train_template->city_id = $service->serviceOrigin[0]->city_id;
                $train_template->district_id = 697;
                $train_template->zone_id = $service->serviceOrigin[0]->zone_id;
                $train_template->allow_child = $service->allow_child;
                $train_template->allow_infant = $service->allow_child;
                $train_template->infant_min_age = $service->infant_min_age;
                $train_template->infant_max_age = $service->infant_max_age;
                $train_template->equivalence_aurora = $service->equivalence_aurora;

                if($train_template->save()){
                    $created_at = \Carbon\Carbon::now();
                    $name_1 = '';
                    $name_2 = '';
                    $name_3 = '';
                    $name_4 = '';
                    foreach ( $service->service_translations as $t ){
                        if( $t->language_id == 1 ){
                            $name_1 = $t->name;
                        }
                        if( $t->language_id == 2 ){
                            $name_2 = $t->name;
                        }
                        if( $t->language_id == 3 ){
                            $name_3 = $t->name;
                        }
                        if( $t->language_id == 4 ){
                            $name_4 = $t->name;
                        }
                    }

                    //DESCRIPCION Y DIRECCION
                    $names = [
                        1 => ['id' => '', 'description' => $name_1],
                        2 => ['id' => '', 'description' => $name_2],
                        3 => ['id' => '', 'description' => $name_3],
                        4 => ['id' => '', 'description' => $name_4],
                    ];
                    $this->saveTranslation($names, 'traintemplate', $train_template->id);

                    // Galerías / fotos
                    foreach( $service->galleries as $g ){
                        $galery = new Galery();
                        $galery->type = "train";
                        $galery->object_id = $train_template->id;
                        $galery->position = $g->position;
                        $galery->slug = "train_gallery";
                        $galery->url = $g->url;
                        $galery->state = $g->state;
                        $galery->save();
                    }
                    // Inclusions
                    foreach( $service->inclusions as $i ){
                        $trainInclusion = new TrainInclusion();
                        $trainInclusion->train_template_id = $train_template->id;
                        $trainInclusion->see_client = $i->see_client;
                        $trainInclusion->inclusion_id = $i->inclusion_id;
                        $trainInclusion->include = $i->include;
                        $trainInclusion->save();
                    }
                    // Taxes
                    foreach( $service->tax as $t ){
                        $trainTax = new TrainTax();
                        $trainTax->train_template_id = $train_template->id;
                        $trainTax->tax_id = $t->tax_id;
                        $trainTax->save();
                    }
                    // children_ages
                    foreach( $service->children_ages as $ch ){
                        $trainChild = new TrainChild();
                        $trainChild->train_template_id = $train_template->id;
                        $trainChild->min_age = $ch->min_age;
                        $trainChild->max_age = $ch->max_age;
                        $trainChild->status = $ch->status;
                        $trainChild->save();
                    }
                    // clients
                    foreach( $service->service_clients as $s_client ){
                        if ((int)$s_client->period < 2020) {
                            $years_for_add = (2020 - (int)$s_client->period);
                        } else {
                            $years_for_add = 0;
                        }
                        $_period = (int)$s_client->period + $years_for_add;

                        $trainClient = new TrainClient();
                        $trainClient->train_template_id = $train_template->id;
                        $trainClient->period = $_period;
                        $trainClient->client_id = $s_client->client_id;
                        $trainClient->save();
                    }
                    // markups
                    foreach( $service->markup_service as $s_markup ){
                        if ((int)$s_markup->period < 2020) {
                            $years_for_add = (2020 - (int)$s_markup->period);
                        } else {
                            $years_for_add = 0;
                        }
                        $_period = (int)$s_markup->period + $years_for_add;

                        $trainMarkup = new MarkupTrain();
                        $trainMarkup->train_template_id = $train_template->id;
                        $trainMarkup->period = $_period;
                        $trainMarkup->markup = $s_markup->markup;
                        $trainMarkup->client_id = $s_markup->client_id;
                        $trainClient->save();
                    }
                    // rates
                    foreach ( $service->service_rate as $sr ){
                        $train_rate = new TrainRate();
                        $train_rate->train_template_id = $train_template->id;
                        $train_rate->name = $sr->name;
                        $train_rate->status = $sr->status;
                        $train_rate->save();
                        foreach ( $sr->translations as $sr_t ){
                            DB::table('translations')->insert([
                                "type" => "trainrate",
                                "object_id" => $train_rate->id,
                                "slug" => "commercial_name",
                                "value" => $sr_t->value,
                                "language_id" => $sr_t->language_id,
                                "created_at" => $created_at,
                                "updated_at" => $created_at
                            ]);
                        }

                        $_train_type_id = TrainType::where('abbreviation',"RT")->first()->id;
                        foreach ( $sr->service_rate_plans as $sr_plans ){
                            //*1
                            $train_cancellation_policies =
                                TrainCancellationPolicy::where('code', $sr_plans->service_cancellation_policy_id);
                            if( $train_cancellation_policies->count() > 0 ){
                                $train_cancellation_policy_id = $train_cancellation_policies->first()->id;
                            } else {
                                $service_cancellation_policy =
                                    ServiceCancellationPolicies::with('parameters')
                                        ->where('id', $sr_plans->service_cancellation_policy_id )->first();

                                $train_cancellation_policy_id =
                                    DB::table('train_cancellation_policies')->insertGetId([
                                    "name" => $service_cancellation_policy->name,
                                    "min_num" => $service_cancellation_policy->min_num,
                                    "max_num" => $service_cancellation_policy->max_num,
                                    "status" => 1,
                                    "is_channel" => 0,
                                    "code" => $service_cancellation_policy->id,
                                    "created_at" => $created_at,
                                    "updated_at" => $created_at
                                ]);

                                foreach ( $service_cancellation_policy->parameters as $scp_parameter ){
                                    $_min_day = (int)$scp_parameter->min_hour;
                                    $_max_day = (int)$scp_parameter->max_hour;
                                    if( (int)$scp_parameter->unit_duration == 1 ){
                                        $_min_day = (int)($_min_day / 24);
                                        $_max_day = (int)($_max_day / 24);
                                    }
                                    DB::table('train_cancellation_parameters')->insert([
                                        "min_day" => $_min_day,
                                        "max_day" => $_max_day,
                                        "amount" => $scp_parameter->amount,
                                        "tax" => $scp_parameter->tax,
                                        "service" => $scp_parameter->service,
                                        "service_penalty_id" => $scp_parameter->service_penalty_id,
                                        "train_cancellation_id" => $train_cancellation_policy_id,
                                        "created_at" => $created_at,
                                        "updated_at" => $created_at
                                    ]);
                                }
                            }

                            $year_first_date_in = (int)substr($sr_plans->date_from, 0, 4);

                            if ($year_first_date_in < 2020) {
                                $years_for_add = (2020 - $year_first_date_in);
                            } else {
                                $years_for_add = 0;
                            }
                            $new_date_from = $this->add_years($sr_plans->date_from, $years_for_add);
                            $new_date_to = $this->add_years($sr_plans->date_to, $years_for_add);

                            DB::table('train_rate_plans')->insert([
                                "train_type_id" => $_train_type_id,
                                "train_type_id_undefined" => 1,
                                "train_rate_id" => $train_rate->id,
                                "train_cancellation_policy_id" => $train_cancellation_policy_id,
                                "date_from" => $new_date_from,
                                "date_to" => $new_date_to,
                                "monday" => $sr_plans->monday,
                                "tuesday" => $sr_plans->tuesday,
                                "wednesday" => $sr_plans->wednesday,
                                "thursday" => $sr_plans->thursday,
                                "friday" => $sr_plans->friday,
                                "saturday" => $sr_plans->saturday,
                                "sunday" => $sr_plans->sunday,
                                "pax_from" => $sr_plans->pax_from,
                                "pax_to" => $sr_plans->pax_to,
                                "price_adult" => $sr_plans->price_adult,
                                "price_child" => $sr_plans->price_child,
                                "price_infant" => $sr_plans->price_infant,
                                "price_guide" => $sr_plans->price_guide,
                                "status" => $sr_plans->status,
                                "frequency_code" => "0000",
                                "equivalence_code" => "00",
                                "created_at" => $created_at,
                                "updated_at" => $created_at
                            ]);
                        }

                        //clients_rate_plan
                        foreach ( $sr->clients_rate_plan as $clients_rp ){
                            if ((int)$clients_rp->period < 2020) {
                                $years_for_add = (2020 - (int)$clients_rp->period);
                            } else {
                                $years_for_add = 0;
                            }
                            $_period = (int)$clients_rp->period + $years_for_add;

                            $trainClientRatePlan = new TrainClientRatePlan();
                            $trainClientRatePlan->client_id = $clients_rp->client_id;
                            $trainClientRatePlan->train_rate_id = $train_rate->id;
                            $trainClientRatePlan->period = $_period;
                            $trainClientRatePlan->save();
                        }
                        //markup_rate_plan
                        foreach ( $sr->markup_rate_plan as $markup_rp ){
                            if ((int)$markup_rp->period < 2020) {
                                $years_for_add = (2020 - (int)$markup_rp->period);
                            } else {
                                $years_for_add = 0;
                            }
                            $_period = (int)$markup_rp->period + $years_for_add;

                            $trainClientRatePlan = new TrainMarkupRatePlan();
                            $trainClientRatePlan->client_id = $markup_rp->client_id;
                            $trainClientRatePlan->train_rate_id = $train_rate->id;
                            $trainClientRatePlan->markup = $markup_rp->markup;
                            $trainClientRatePlan->period = $_period;
                            $trainClientRatePlan->save();
                        }
                        //inventories
                        foreach ( $sr->inventory as $inventory ){
                            $trainInventory = new TrainInventory();
                            $trainInventory->train_rate_id = $train_rate->id;
                            $trainInventory->day = $inventory->day;
                            $trainInventory->date = $inventory->date;
                            $trainInventory->inventory_num = $inventory->inventory_num;
                            $trainInventory->total_booking = $inventory->total_booking;
                            $trainInventory->total_canceled = $inventory->total_canceled;
                            $trainInventory->locked = $inventory->locked;
                            $trainInventory->save();
                        }
                    }

                    array_push( $imports, array(
                        "code" => $service->aurora_code,
                        "success" => true,
                        "message" => "Importado correctamente"
                    ) );
                } else {
                    array_push( $imports, array(
                        "code" => $service->aurora_code,
                        "success" => false,
                        "message" => "Error al guardar"
                    ) );
                }
            }
        }

        return Response::json( $imports );

    }

    private function add_years($fecha, $nAnios)
    {
        $days = $nAnios * 365;
        $nuevafecha = strtotime('+' . $days . 'day', strtotime($fecha));
        return date('Y-m-j', $nuevafecha);
    }

    public function getBasicTrainRailRouteID(){

        $train_id = Train::where('name','BASIC');
        if( $train_id->count() == 0 ){
            $train_id = DB::table('trains')->insertGetId([
                "code" => "X",
                "name" => "BASIC"
            ]);
        } else {
            $train_id = $train_id->first()->id;
        }

        $rail_route_id = RailRoute::where('name','BASIC');
        if( $rail_route_id->count() == 0 ){
            $rail_route_id = DB::table('rail_routes')->insertGetId([
                "status" => 1,
                "name" => "BASIC"
            ]);
        } else {
            $rail_route_id = $rail_route_id->first()->id;
        }

        $train_rail_route_id = TrainRailRoute::where('train_id',$train_id)->where('rail_route_id', $rail_route_id);
        if( $train_rail_route_id->count() == 0 ){
            $train_rail_route_id = DB::table('train_rail_routes')->insertGetId([
                "train_id" => $train_id,
                "rail_route_id" => $rail_route_id,
                "name" => "BASIC",
                "code" => 0,
                "abbreviation" => "BASIC"
            ]);
        } else {
            $train_rail_route_id = $train_rail_route_id->first()->id;
        }

        return $train_rail_route_id;

    }

    public function getBasicTrainTrainClassID(){

        $train_id = Train::where('name','BASIC');
        if( $train_id->count() == 0 ){
            $train_id = DB::table('trains')->insertGetId([
                "code" => "X",
                "name" => "BASIC"
            ]);
        } else {
            $train_id = $train_id->first()->id;
        }

        $train_class_id = TrainClass::where('name','BASIC');
        if( $train_class_id->count() == 0 ){
            $train_class_id = DB::table('train_classes')->insertGetId([
                "status" => 1,
                "name" => "BASIC"
            ]);
        } else {
            $train_class_id = $train_class_id->first()->id;
        }

        $train_train_class_id = TrainTrainClass::where('train_id',$train_id)->where('train_class_id', $train_class_id);
        if( $train_train_class_id->count() == 0 ){
            $train_train_class_id = DB::table('train_train_classes')->insertGetId([
                "train_id" => $train_id,
                "train_class_id" => $train_class_id,
                "name" => "BASIC",
                "code" => 0
            ]);
        } else {
            $train_train_class_id = $train_train_class_id->first()->id;
        }

        return $train_train_class_id;

    }

}
