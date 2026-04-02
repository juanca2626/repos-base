<?php

namespace App\Exports;

use App\City;
use App\Client;
use App\Language;
use App\Markup;
use App\Service;
use App\ServiceCategory;
use App\ServiceClient;
use App\State;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ServiceCityExportBackup implements WithMultipleSheets
{
    use Exportable;

    protected $service_year;
    protected $lang;
    protected $client_id;

    public function __construct($service_year = null, $lang = null, $client_id = null)
    {
        $this->service_year = $service_year;

        $this->lang = $lang;

        $this->client_id = $client_id;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        set_time_limit(0);
        $service_year = $this->service_year;
        $language_id = Language::where('state', 1)->where('iso', $this->lang)->first()->id;

        $data = [
            "cities" => []
        ];
        $markup = 0;
        $markup_new = Markup::where('client_id', $this->client_id)->where('period', $service_year)->first();

        if ($markup_new == null) {
            $markup = 17;
        } else {
            $markup = (int)$markup_new->service;
        }

        $client = Client::where('id', $this->client_id)->first();
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
                $query->where('language_id', 1);
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


        $services_locked = ServiceClient::where('client_id', $client->id)->where('period',
            $service_year)->pluck('service_id');

        $services = Service::with([
            'serviceOrigin' => function ($query) {
                $query->select(['id','service_id','country_id','state_id','city_id','zone_id']);
            }
        ])->with([
                'service_rate' => function ($query) use ($service_year, $language_id) {
                    $query->select('id', 'name', 'service_id', 'status');
                    $query->where('status', 1);
                    $query->where('rate', 1);
                    $query->with([
                        'service_rate_plans' => function ($query) use ($service_year) {
                            $query->where('date_from', '>=', $service_year.'-01-01');
                            $query->where('date_to', '<=', $service_year.'-12-31');
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
                            $query->where('language_id', $language_id);
                        }
                    ]);

                }
            ])
            ->with([
                'inclusions' => function ($query) use ($language_id) {
                    $query->where('include', 1);
                    $query->where('see_client', 1);
                    $query->orderBy('day');
                    $query->orderBy('order');
//                    $query->where('language_id', $language_id);
                    $query->with([
                        'inclusions' => function ($query) use ($language_id) {
                            $query->with([
                                'translations' => function ($query) use ($language_id) {
                                    $query->where('language_id', $language_id);
                                }
                            ]);
                        }
                    ]);
                }
            ])
            ->with([
                'service_translations' => function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                }
            ])
            ->with([
                'serviceEquivAssociation' => function ($query) use ($service_year, $language_id) {
                    $query->with([
                        'service' => function ($query) use ($service_year, $language_id) {
                            $query->with([
                                'service_rate' => function ($query) use ($service_year, $language_id) {
                                    $query->select('id', 'name', 'service_id', 'status');
                                    $query->where('status', 1);
                                    $query->where('rate', 1);
                                    $query->with([
                                        'service_rate_plans' => function ($query) use ($service_year) {
                                            $query->where('date_from', '>=', $service_year.'-01-01');
                                            $query->where('date_to', '<=', $service_year.'-12-31');
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
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ]);
                }
            ])->whereHas('service_rate', function ($q) {
                $q->where('rate', 1);
            })->where('status', 1)
            ->whereNotIn('id', $services_locked)
            ->orderBy('rate_order')
            ->limit(5)
            ->get(['id', 'aurora_code', 'service_type_id', 'service_sub_category_id', 'rate_order']);


        $services_new = [];
        $code_services_with_equivalence = [];
        //Servicios con equivalencia
        foreach ($services as $service) {
            if (count($service["serviceEquivAssociation"]) > 0 && !in_array($service["aurora_code"],
                    $code_services_with_equivalence) && count($service["service_rate"]) > 0) {
                $aurora_code_sim = "";
                $aurora_code_pc = "";
                $aurora_code_na = "";
                array_push($code_services_with_equivalence, $service["aurora_code"]);
                array_push($code_services_with_equivalence,
                    $service["serviceEquivAssociation"][0]["service"]["aurora_code"]);
                if ($service["service_type_id"] == 1) {
                    $aurora_code_sim = $service["aurora_code"];
                }
                if ($service["service_type_id"] == 2) {
                    $aurora_code_pc = $service["aurora_code"];
                }
                if ($service["service_type_id"] == 3) {
                    $aurora_code_na = $service["aurora_code"];
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
                                $inclusions[$index]["inclusion_name"] .= $service_inclusion["inclusions"]["translations"][0]["value"].',';
                            }
                        }
                    }
                }

                array_push($services_new, [
                    "aurora_code" => $service["aurora_code"],
                    "aurora_code_sim" => $aurora_code_sim,
                    "aurora_code_pc" => $aurora_code_pc,
                    "aurora_code_na" => $aurora_code_na,
                    "service_city_id" => $service["serviceOrigin"][0]["city_id"],
                    "service_type_id" => $service["service_type_id"],
                    "service_sub_category_id" => $service["service_sub_category_id"],
                    "service_name" => $service["service_translations"][0]["name"],
                    "equivalence" => true,
                    "rate_order" => $service["rate_order"],
                    "rates" => [],
                    "inclusions" => $inclusions
                ]);
            }
        }



        //Servicios sin equivalencia
        foreach ($services as $service) {
            if (count($service["serviceEquivAssociation"]) == 0 && !in_array($service["aurora_code"],
                    $code_services_with_equivalence) && count($service["service_rate"]) > 0) {
                $aurora_code_sim = "";
                $aurora_code_pc = "";
                $aurora_code_na = "";
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
                                $inclusions[$index]["inclusion_name"] .= $service_inclusion["inclusions"]["translations"][0]["value"].',';
                            }
                        }
                    }
                }

                array_push($services_new, [
                    "aurora_code" => $service["aurora_code"],
                    "aurora_code_sim" => $aurora_code_sim,
                    "aurora_code_pc" => $aurora_code_pc,
                    "aurora_code_na" => $aurora_code_na,
                    "service_city_id" => $service["serviceOrigin"][0]["city_id"],
                    "service_type_id" => $service["service_type_id"],
                    "service_sub_category_id" => $service["service_sub_category_id"],
                    "service_name" => $service["service_translations"][0]["name"],
                    "equivalence" => false,
                    "rate_order" => $service["rate_order"],
                    "rates" => [],
                    "inclusions" => $inclusions
                ]);
            }
        }


        //Order Services

//        usort($services_new, 'App\Exports\ServiceCityExport::sortByRate');


        //Add Service Rates
        foreach ($services_new as $index_service_new => $service_new) {
            foreach ($services as $service) {
                if ($service_new["aurora_code"] == $service["aurora_code"]) {
                    foreach ($service["service_rate"] as $rate) {
                        array_push($services_new[$index_service_new]["rates"], [
                            "rate_id" => $rate["id"],
                            "rate_name" => $rate["name"],
                            "rate_plans" => []
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
                    foreach ($services as $service) {
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
                    foreach ($services as $service) {
                        if ($service["aurora_code"] == $service_new["aurora_code"]) {
                            foreach ($service["service_rate"] as $service_rate) {
                                if ($service_rate["id"] == $service_new_rate["rate_id"]) {
                                    foreach ($service_rate["service_rate_plans"] as $service_rate_plan) {
                                        if ($service_rate_plan["date_from"] == $service_new_rate_plan["date_from"] &&
                                            $service_rate_plan["date_to"] == $service_new_rate_plan["date_to"]) {
                                            if ($service_new["equivalence"]) {
                                                foreach ($services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"] as $index_service_new_range_equivalence => $range_equivalence) {
                                                    if (count($service["serviceEquivAssociation"][0]["service"]["service_rate"]) > 0) {
                                                        foreach ($service["serviceEquivAssociation"][0]["service"]["service_rate"][0]["service_rate_plans"] as $service_rate_plan_equivalence) {
                                                            if ($service_rate_plan_equivalence["pax_from"] >= $range_equivalence["pax_from"] && $service_rate_plan_equivalence["pax_to"] <= $range_equivalence["pax_to"]) {
//
                                                                $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_adult"] = ceil(((float)($service_rate_plan_equivalence["price_adult"] + ($service_rate_plan_equivalence["price_adult"] * ($markup / 100)))));
                                                                $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_child"] = ceil((float)($service_rate_plan_equivalence["price_child"] + ($service_rate_plan_equivalence["price_child"] * ($markup / 100))));
                                                                $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_infant"] = ceil((float)($service_rate_plan_equivalence["price_infant"] + ($service_rate_plan_equivalence["price_infant"] * ($markup / 100))));
                                                                $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_guide"] = ceil((float)($service_rate_plan_equivalence["price_guide"] + ($service_rate_plan_equivalence["price_guide"] * ($markup / 100))));
                                                            } else {
                                                                if ($service_rate_plan_equivalence["pax_from"] <= $range_equivalence["pax_from"] && $service_rate_plan_equivalence["pax_to"] >= $range_equivalence["pax_to"]) {
                                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_adult"] = ceil(((float)($service_rate_plan_equivalence["price_adult"] + ($service_rate_plan_equivalence["price_adult"] * ($markup / 100)))));
                                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_child"] = ceil((float)($service_rate_plan_equivalence["price_child"] + ($service_rate_plan_equivalence["price_child"] * ($markup / 100))));
                                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_infant"] = ceil((float)($service_rate_plan_equivalence["price_infant"] + ($service_rate_plan_equivalence["price_infant"] * ($markup / 100))));
                                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges_equivalence"][$index_service_new_range_equivalence]["price_guide"] = ceil((float)($service_rate_plan_equivalence["price_guide"] + ($service_rate_plan_equivalence["price_guide"] * ($markup / 100))));

                                                                }
                                                            }
                                                        }
                                                    }
                                                }

                                            }
                                            foreach ($services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"] as $index_service_new_range => $range) {
                                                if ($service_rate_plan["pax_from"] >= $range["pax_from"] && $service_rate_plan["pax_to"] <= $range["pax_to"]) {
//
                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_adult"] = ceil(((float)($service_rate_plan["price_adult"] + ($service_rate_plan["price_adult"] * ($markup / 100)))));
                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_child"] = ceil((float)($service_rate_plan["price_child"] + ($service_rate_plan["price_child"] * ($markup / 100))));
                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_infant"] = ceil((float)($service_rate_plan["price_infant"] + ($service_rate_plan["price_infant"] * ($markup / 100))));
                                                    $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_guide"] = ceil((float)($service_rate_plan["price_guide"] + ($service_rate_plan["price_guide"] * ($markup / 100))));
                                                } else {
                                                    if ($service_rate_plan["pax_from"] <= $range["pax_from"] && $service_rate_plan["pax_to"] >= $range["pax_to"]) {
                                                        $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_adult"] = ceil(((float)($service_rate_plan["price_adult"] + ($service_rate_plan["price_adult"] * ($markup / 100)))));
                                                        $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_child"] = ceil((float)($service_rate_plan["price_child"] + ($service_rate_plan["price_child"] * ($markup / 100))));
                                                        $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_infant"] = ceil((float)($service_rate_plan["price_infant"] + ($service_rate_plan["price_infant"] * ($markup / 100))));
                                                        $services_new[$index_service_new]["rates"][$index_service_rate]["rate_plans"][$index_service_new_rate_plan]["ranges"][$index_service_new_range]["price_guide"] = ceil((float)($service_rate_plan["price_guide"] + ($service_rate_plan["price_guide"] * ($markup / 100))));

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
        //End delete cities


        $sheets = [];

        foreach ($data["cities"] as $city) {
            $sheets[] = new ServiceYearExport($city, $this->lang, $this->service_year);
        }
        $sheets[] = new ServiceTermsAndConditions($this->lang, $this->service_year);


        return $sheets;
    }

    public static function sortByRate($a, $b)
    {
        $a = $a['rate_order'];
        $b = $b['rate_order'];

        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }

}
