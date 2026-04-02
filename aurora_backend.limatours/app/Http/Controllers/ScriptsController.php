<?php

namespace App\Http\Controllers;

use App\ClientRatePlan;
use App\Hotel;
use App\HotelClient;
use App\BagRate;
use App\GenerateRatesInCalendar;
use App\InventoryBag;
use App\Inventory;
use App\Mail\ResetPassword;
use App\Package;
use App\PolicyCancellationParameter;
use App\RatesHistory;
use App\Quote;
use App\QuoteLog;
use App\RatesPlans;
use App\RatesPlansRooms;
use App\Service;
use App\ServiceInventory;
use App\Translation;
use Carbon\Carbon;
use App\Http\Traits\Aurora1 as Aurora1;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Traits\GenerateRatesCalendar;
use App\Jobs\RateCalendaries;

class ScriptsController extends Controller
{
    use Aurora1;
    use GenerateRatesCalendar;

    public function updateCotizaciones()
    {
        DB::transaction(function () {

            $packages_services = DB::table('package_services')
                ->where('type', 'service')->whereNull('deleted_at')->get();

            foreach ($packages_services as $package_service) {
                $rates = DB::table('service_rates')->where('service_id', $package_service->object_id)->get();

                foreach ($rates as $rate) {
                    DB::table('package_service_rates')->insert([
                        "package_service_id" => $package_service->id,
                        "service_rate_id" => $rate->id
                    ]);
                    break;
                }
            }
        });
        return "cotizaciones actualizadas";
    }

    public function updateStatusPackages()
    {
        DB::transaction(function () {
            $packages = DB::table('packages')->get();

            foreach ($packages as $package) {
                $translations = DB::table('package_translations')->where('package_id', $package->id)->get();
                foreach ($translations as $translation) {
                    if (trim($translation->name) == "" || $translation->name == null) {
                        DB::table('packages')->where('id', $package->id)->update([
                            "status" => 0
                        ]);
                    }
                }
            }
        });
        return "paquetes actualizados";
    }

    public function updateTranslationsFrontend(Request $request)
    {
        $module_id = $request->get('module_id');

        DB::transaction(function () use ($module_id) {
            $translations = DB::table('translation_frontends')->where('module_id', $module_id)->where('language_id',
                2)->get();

            foreach ($translations as $translation) {
                DB::table('translation_frontends')->insert([
                    'slug' => $translation->slug,
                    'value' => $translation->value,
                    'module_id' => $module_id,
                    'language_id' => 3,
                    'created_at' => Carbon::now()
                ]);
                DB::table('translation_frontends')->insert([
                    'slug' => $translation->slug,
                    'value' => $translation->value,
                    'module_id' => $module_id,
                    'language_id' => 4,
                    'created_at' => Carbon::now()
                ]);
            }
        });

        return response()->json("actualizaciones creadas");
    }

    public function updatePermissionsPackage()
    {

        DB::transaction(function () {

            $packages = DB::table('packages')->whereIn('code', [
                'PAQ006',
                'PAQ011',
                'PAQ375',
                'PAQ007',
                'PAQ005',
                'PAQ027',
                'PAQ028',
                'PAQ277',
                'PAQ595',
                'PAQ376',
                'PAQ374',
                'PAQ004',
                'PAQ012',
                'PAQ013',
                'PAQ015',
                'PAQ016',
                'PAQ276',
                'PAQ710',
                'PAQ003',
                'PAQ275',
                'PAQ019',
                'PAQ379',
                'PAQ009',
                'PAQ010',
                'PAQ531',
                'PAQ539',
                'PAQ532',
                'PAQ714',
                'PAQ561',
                'PAQ533',
                'PAQ377',
                'PAQ378',
                'PAQ605',
                'PAQ033',
                'PAQ534',
                'PAQ036',
                'EX0003',
                'EX0067',
                'EX0005',
                'EX0007',
                'EX0006',
                'EX0009',
                'EX0008',
                'EX0002',
                'EX0001',
                'EX0079',
                'EX0712',
                'EX0716',
                'EX0713'
            ])->get();

            $users_id = [2661, 2511, 3186];
            foreach ($packages as $package) {
                foreach ($users_id as $user_id) {
                    DB::table('package_permissions')->insert([
                        "package_id" => $package->id,
                        "user_id" => $user_id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                }
            }
        });

        return "permisos de paquetes actualizados";
    }

    public function validateSuperPositionDuplicateRates(Request $request)
    {
        $exist_year_to = false;
        $rate_plans = $request->post('rate_plans');
        $year = $request->post('year_from');
        $year_to = $request->post('year_to');

        foreach ($rate_plans as $rate_plan) {
            $date_range_hotels = DB::table('date_range_hotels')->where('rate_plan_id', $rate_plan["id"])
                ->where('date_from', 'LIKE', "%" . $year_to . "%")
                ->orderBy('created_at', 'desc')->get();

            if ($date_range_hotels->count() > 0) {
                $exist_year_to = true; // Ya hay importes cargados
            }
        }
        if ($exist_year_to) {
            return response()->json(["message" => "Ya hay importes cargados al año al que desean clonar"]);
        } else {
            return \response()->json(false);
        }
    }

    public function duplicateRates(Request $request)
    {
        $rate_plans = $request->post('rate_plans');
        $year = $request->post('year_from');
        $year_to = $request->post('year_to');

        // validamos que no aya procesos ejecutandose y que en el rango de tarifas no existan duplicidad antes de migrarlo.
        $rates_plans_ids = array_column($rate_plans, 'id');
        $rates_plans_data = RatesPlans::whereIn('id', $rates_plans_ids)->get();
        foreach ($rates_plans_data as $rate_plan) {

            $generated = GenerateRatesInCalendar::where('hotel_id',$rate_plan->hotel_id )->where('rates_plans_id',$rate_plan->id)->where('status','1')->get();

            if(count($generated)>0){
                return response()->json("Hay un proceso ejecutándose, no puede ejecutar esta acción", 422);
            }

            $rangos  = $this->generateRates($rate_plan->hotel_id,$rate_plan->id,null,$year);
            if(count($rangos['date_range_hotel_duplicate'])>0){
                return response()->json("Hay un proceso ejecutándose, no puede ejecutar esta acción", 422);
            }

        }

        // return response()->json(auth()->user()->id, 422);


        DB::beginTransaction();

        try {

            foreach ($rate_plans as $rate_plan) {
                $date_range_hotels = DB::table('date_range_hotels')->where('rate_plan_id',
                    $rate_plan["id"])->where('date_from', 'LIKE', "%" . $year . "%")->orderBy('group',
                    'asc')->get();

                //obtener el maximo valor de grupo de rangos de fechas de tarifa
                $max_value_group = 0;
                $max_value_group = DB::table('date_range_hotels')->where('rate_plan_id', $rate_plan["id"])->max('group') + 1;

                //Generar nueva data con los rangos de fecha solamente del ano a duplicar
                $recent_group = $date_range_hotels[0]->group;


                foreach ($date_range_hotels as $date_range_hotel) {
                    if ($date_range_hotel->group != $recent_group) {
                        $recent_group = $date_range_hotel->group;
                        $max_value_group++;
                    }

                    DB::table('date_range_hotels')->insert([
                        'date_from' => str_replace($year, $year_to, $date_range_hotel->date_from),
                        'date_to' => str_replace($year, $year_to, $date_range_hotel->date_to),
                        'price_adult' => $date_range_hotel->price_adult,
                        'price_child' => $date_range_hotel->price_child,
                        'price_infant' => $date_range_hotel->price_infant,
                        'price_extra' => $date_range_hotel->price_extra,
                        'discount_for_national' => $date_range_hotel->discount_for_national,
                        'rate_plan_id' => $date_range_hotel->rate_plan_id,
                        'hotel_id' => $date_range_hotel->hotel_id,
                        'room_id' => $date_range_hotel->room_id,
                        'rate_plan_room_id' => $date_range_hotel->rate_plan_room_id,
                        'meal_id' => $date_range_hotel->meal_id,
                        'policy_id' => $date_range_hotel->policy_id,
                        'old_id_date_range' => null,
                        'group' => $max_value_group,
                        'updated' => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);

                    if ($rate_plan["duplicate_inventory"]) {

                        $rate_plan_room = DB::table('rates_plans_rooms')->where('id', $date_range_hotel->rate_plan_room_id)->where('channel_id', 1)->first();
                        //borrar inventario year_to si existe
                        if ($rate_plan_room->bag == 0) {

                            DB::table('inventories')->where('rate_plan_rooms_id', $date_range_hotel->rate_plan_room_id)
                                ->where('date', '>=', $year_to . '-01-01')
                                ->where('date', '<=', $year_to . '-12-31')
                                ->delete();
                        }
                        if ($rate_plan_room->bag == 1) {
                            $bag_rate = DB::table('bag_rates')->where('rate_plan_rooms_id',
                                $date_range_hotel->rate_plan_room_id)->first();

                            if($bag_rate){
                                DB::table('inventory_bags')->where('bag_room_id', $bag_rate->bag_room_id)
                                    ->where('date', '>=', $year_to . '-01-01')
                                    ->where('date', '<=', $year_to . '-12-31')
                                    ->delete();
                            }
                        }
                        //clonar inventario de year from a year to
                        if ($rate_plan_room->bag == 0) {
                            $inventories_year_from = DB::table('inventories')->where('rate_plan_rooms_id',
                                $date_range_hotel->rate_plan_room_id)
                                ->where('date', '>=', $year . '-01-01')
                                ->where('date', '<=', $year . '-12-31')
                                ->get();

                            foreach ($inventories_year_from as $inventory) {
                                DB::table('inventories')->insert([
                                    'day' => $inventory->day,
                                    'date' => str_replace($year, $year_to, $inventory->date),
                                    'inventory_num' => $inventory->inventory_num,
                                    'total_booking' => $inventory->total_booking,
                                    'total_canceled' => $inventory->total_canceled,
                                    'locked' => $inventory->locked,
                                    'rate_plan_rooms_id' => $date_range_hotel->rate_plan_room_id,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()
                                ]);
                            }
                        }
                        if ($rate_plan_room->bag == 1) {
                            $bag_rate = DB::table('bag_rates')->where('rate_plan_rooms_id',
                                $date_range_hotel->rate_plan_room_id)->first();

                            if($bag_rate){
                                $inventories_year_from = DB::table('inventory_bags')->where('bag_room_id',
                                    $bag_rate->bag_room_id)
                                    ->where('date', '>=', $year . '-01-01')
                                    ->where('date', '<=', $year . '-12-31')
                                    ->get();

                                foreach ($inventories_year_from as $inventory) {
                                    DB::table('inventory_bags')->insert([
                                        'day' => $inventory->day,
                                        'date' => str_replace($year, $year_to, $inventory->date),
                                        'inventory_num' => $inventory->inventory_num,
                                        'total_booking' => $inventory->total_booking,
                                        'total_canceled' => $inventory->total_canceled,
                                        'locked' => $inventory->locked,
                                        'bag_room_id' => $bag_rate->bag_room_id,
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now()
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // Generamos los procesos de actualizacion en el calendario
            foreach ($rates_plans_data as $rate_plan) {

                $params = [
                    'hotel_id' =>  $rate_plan->hotel_id,
                    'rates_plans_id' => $rate_plan->id,
                    'room_id' => NULL,
                    'perido' => date('Y'),
                    'status_message' => '',
                    'status' =>  1,
                    'user_add' => auth()->user()->id
                ];

                $generate_rates_in_calendar = GenerateRatesInCalendar::create($params);
                RateCalendaries::dispatch($generate_rates_in_calendar->id);
            }

            DB::commit();

            return response()->json("Tarifas Clonadas");

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage(), 422);
        }




    }

    public function duplicateClientsAndHotelsLocked(Request $request)
    {
        $year_from = $request->post('year_from');
        $year_to = $request->post('year_to');
        $hotel_id = $request->post('hotel_id');
        DB::transaction(function () use ($year_from, $year_to, $hotel_id) {
            $rate_plans = RatesPlans::where('hotel_id', $hotel_id)->get();

            foreach ($rate_plans as $rate_plan) {
                //Eliminar todos client rate plans from del year_to si existe
//                ClientRatePlan::where(['rate_plan_id' => $rate_plan->id, 'period' => $year_to])->each(function ($rate) {
//                    $rate->delete();
//                });
                ClientRatePlan::where('rate_plan_id', $rate_plan->id)->where('period', $year_to)->delete();
                //listado de client_rate_plan de year from
                $client_rate_plans = ClientRatePlan::where('rate_plan_id',
                    $rate_plan->id)->where('period', $year_from)->get();
                //Insertar los client_rate_plan con year_to nuevos
                foreach ($client_rate_plans as $client_rate_plan) {
                    DB::table('client_rate_plans')->insert([
                        'period' => $year_to,
                        'client_id' => $client_rate_plan->client_id,
                        'rate_plan_id' => $client_rate_plan->rate_plan_id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }
            //Eliminar todos los hotel_clients del year_to si existe
            HotelClient::where('hotel_id', $hotel_id)->where('period', $year_to)->delete();
            //listado de hotel_clients de year_from
            $hotel_clients = HotelClient::where('hotel_id', $hotel_id)->where('period',
                $year_from)->get();
            //insertar client_hotels de year_to nuevos
            foreach ($hotel_clients as $hotel_client) {
                DB::table('hotel_clients')->insert([
                    'period' => $year_to,
                    'client_id' => $hotel_client->client_id,
                    'hotel_id' => $hotel_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

        });
        return response()->json("registros duplicados");
    }

    public function updatePoliciesCancellation()
    {
        DB::transaction(function () {

            $policies_cancellations_parameters_max_day = PolicyCancellationParameter::where('max_day', '>', 0)->get();

            foreach ($policies_cancellations_parameters_max_day as $policies_cancellations_parameter) {
                $policy = PolicyCancellationParameter::find($policies_cancellations_parameter["id"]);
                $policy->max_day = $policy->max_day - 2;
                $policy->save();
            }

            $policies_cancellations_parameters_min_day = PolicyCancellationParameter::where('min_day', '>', 0)->get();

            foreach ($policies_cancellations_parameters_min_day as $policies_cancellations_parameter) {
                $policy = PolicyCancellationParameter::find($policies_cancellations_parameter["id"]);
                $policy->min_day = $policy->min_day + 2;

                if ($policies_cancellations_parameter["max_day"] > 0) {
                    $policy->max_day = $policy->max_day + 4;
                }
                $policy->save();
            }


        });

        return \response()->json("politicas de cancelacion actualizadas");
    }

    public function updateServiceInventories(Request $request)
    {

        $confirm = $request->input('password');
        if ($confirm != '[7]') {
            return 'Por favor ingresar la contraseña correcta para este script ?password=';
        }

        $skip = $request->input('skip');
        $limit = $request->input('limit');
        if (!is_numeric($skip) || !is_numeric($limit)) {
            return 'Por favor ingresar skip y limit &skip=0&limit=300';
        }

        set_time_limit(0);

        $services = Service::whereNotIn('service_sub_category_id', [18, 22])
            ->with('service_rate')
            ->skip($skip)->take($limit)->get();

        DB::transaction(function () use ($services) {

            foreach ($services as $service) {
                foreach ($service->service_rate as $s_rate) {
                    ServiceInventory::where('service_rate_id', $s_rate->id)
                        ->where('date', '>=', '2021-01-01')
                        ->where('date', '<=', '2021-12-31')
                        ->delete();
                    // Guardar todo el 2021 cupos de 100

                    $date_from = Carbon::createFromFormat('d/m/Y', "01/01/2021")->setTimezone('America/Lima');
                    $date_to = Carbon::createFromFormat('d/m/Y', "31/12/2021")->setTimezone('America/Lima');

                    $availability = 100;
                    $difference_days = $date_from->diffInDays($date_to->addDay());
                    //                return $difference_days;
                    for ($i = 0; $i <= $difference_days; $i++) {
                        if ($i > 0) {
                            $date_from->addDay();
                        }

                        DB::table('service_inventories')->insert([
                            'day' => $date_from->day,
                            'date' => $date_from->format('Y-m-d'),
                            'inventory_num' => $availability,
                            'total_booking' => 0,
                            'total_canceled' => 0,
                            'locked' => false,
                            'service_rate_id' => $s_rate->id,
                            'created_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);

                    }
                }
            }
        });

        return \response()->json("Tarifas para el 2021 actualizadas correctamente");

    }

    public function debugQuoteLogs(Request $request)
    {

        $confirm = $request->input('password');
        if ($confirm != '[7]') {
            return 'Por favor ingresar la contraseña correcta para este script ?password=';
        }

        $skip = $request->input('skip');
        $limit = $request->input('limit');
        if (!is_numeric($skip) || !is_numeric($limit)) {
            return 'Por favor ingresar skip y limit para la tabla quotes: &skip=0&limit=300';
        }

        set_time_limit(0);

        $quotes = Quote::with(['logs' => function ($query) {
            $query->orderBy('created_at', 'DESC');
        }])
            ->skip($skip)->take($limit)
            ->get();

//        return $quotes;

        DB::transaction(function () use ($quotes) {
            $temps = [];
            foreach ($quotes as $quote) {
                foreach ($quote->logs as $log) {
                    if (isset($temps[$quote->id][$log->type][$log->object_id])) {
                        QuoteLog::where('id', $log->id)->delete();
                    } else {
                        $temps[$quote->id][$log->type][$log->object_id] = true;
                    }
                }
            }
        });

        return \response()->json("Logs de cotizaciones depuradas correctamente");
    }

    public function generateTranslationsDayUseNoShow()
    {
        DB::transaction(function () {
            $rate_plans = RatesPlans::all();

            foreach ($rate_plans as $rate_plan) {

                DB::table('translations')->insert([
                    'type' => 'rates_plan',
                    'object_id' => $rate_plan["id"],
                    'slug' => 'no_show',
                    'value' => '100% + impuestos de ley',
                    'language_id' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                DB::table('translations')->insert([
                    'type' => 'rates_plan',
                    'object_id' => $rate_plan["id"],
                    'slug' => 'no_show',
                    'value' => '100% + taxes apply',
                    'language_id' => 2,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                DB::table('translations')->insert([
                    'type' => 'rates_plan',
                    'object_id' => $rate_plan["id"],
                    'slug' => 'no_show',
                    'value' => '100% + taxes apply',
                    'language_id' => 3,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                DB::table('translations')->insert([
                    'type' => 'rates_plan',
                    'object_id' => $rate_plan["id"],
                    'slug' => 'day_use',
                    'value' => 'Por favor contacte a su especialista para mas detalles',
                    'language_id' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                DB::table('translations')->insert([
                    'type' => 'rates_plan',
                    'object_id' => $rate_plan["id"],
                    'slug' => 'day_use',
                    'value' => 'Please contact your specialist for more details.',
                    'language_id' => 2,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                DB::table('translations')->insert([
                    'type' => 'rates_plan',
                    'object_id' => $rate_plan["id"],
                    'slug' => 'day_use',
                    'value' => 'Please contact your specialist for more details.',
                    'language_id' => 3,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        });
    }

    public function addColumnGuide()
    {
        DB::transaction(function () {
            $rate_plans = DB::table('rates_plans')->get();

            foreach ($rate_plans as $rate_plan) {
                $data_history = DB::table('rates_histories')->where('rates_plan_id', $rate_plan->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                DB::table('rates_histories')->where('rates_plan_id', $rate_plan->id)->where('id', '!=', $data_history->id)->delete();

                if ($data_history->dataRooms != null || $data_history->dataRooms != '') {
                    $records = json_decode($data_history->dataRooms);

                    foreach ($records as $index => $record) {

                        $records[$index]->guide = 0;
                    }

                    DB::table('rates_histories')->where('id', $data_history->id)->update([
                        'dataRooms' => json_encode($records),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }
        });

        return response()->json("Columna de guia agregada a planes tarifarios");
    }

    public function masiStadistics(Request $request)
    {
        $from_date = $request->__get('from_date');
        $to_date = $request->__get('to_date');
        $region = $request->__get('region');
        $market = $request->__get('market');
        $client = $request->__get('client');

        $response = $this->getMasiStadistics($from_date, $to_date, $region, $market, $client);
        return response()->json($response);
    }

    public function deleteColumnGuide()
    {
        DB::transaction(function () {
            $rate_plans = DB::table('rates_plans')->get();

            foreach ($rate_plans as $rate_plan) {
                $data_history = DB::table('rates_histories')->where('rates_plan_id', $rate_plan->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                DB::table('rates_histories')->where('rates_plan_id', $rate_plan->id)->where('id', '!=', $data_history->id)->delete();

                if ($data_history->dataRooms != null || $data_history->dataRooms != '') {
                    $records = json_decode($data_history->dataRooms);

                    foreach ($records as $index => $record) {
                        unset($records[$index]->guide);

                    }

                    DB::table('rates_histories')->where('id', $data_history->id)->update([
                        'dataRooms' => json_encode($records),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }
        });
        return response()->json("Columna de guia eliminada");
    }

    public function addRoomsAdditional(Request $request)
    {
        $hotel_id = $request->post('hotel_id');
        $rate_plans_updated = $request->post('rate_plan_ids');
        $rate_plan_ids = [];
        foreach ($rate_plans_updated as $rate_plan_updated) {
            array_push($rate_plan_ids, $rate_plan_updated["id"]);
        }

        DB::transaction(function () use ($hotel_id, $rate_plan_ids) {

            $rate_plans = RatesPlans::whereIn('id', $rate_plan_ids)->where('hotel_id', $hotel_id)->where('status', 1)->get();
            $rooms = [];

            foreach ($rate_plans as $rate_plan) {

                $date_ranges = DB::table('date_range_hotels')->where('rate_plan_id', $rate_plan->id)->get();

                foreach ($date_ranges as $index => $date_range) {
                    if (!$this->searchRoomExists($rooms, $date_range->room_id) && $date_range->price_extra > 0) {
                        array_push($rooms, ["room_id" => $date_range->room_id, "room_equivalence" => "", "rate_plan_room_id" => ""]);
                    }
                }

                foreach ($rooms as $index_room => $room) {
                    if ($room["room_equivalence"] == "") {
                        $room_id = DB::table('rooms')->insertGetId([
                            "max_capacity" => 3,
                            "min_adults" => 1,
                            "max_adults" => 3,
                            "max_child" => 0,
                            "max_infants" => 0,
                            "min_inventory" => 0,
                            "state" => 1,
                            "see_in_rates" => 1,
                            "hotel_id" => $hotel_id,
                            "room_type_id" => 3,
                            "created_at" => Carbon::now(),
                            "updated_at" => Carbon::now(),
                            "estela_id" => null,
                            "inventory" => 0,
                            "bed_additional" => 1
                        ]);

                        $rooms[$index_room]["room_equivalence"] = $room_id;

                        $room_name = DB::table('translations')->select('value')->where('type', 'room')
                            ->where('language_id', 1)->where('object_id', $room["room_id"])
                            ->where('slug', 'room_name')->first()->value;

                        $room_description = DB::table('translations')->select('value')->where('type', 'room')
                            ->where('language_id', 1)->where('object_id', $room["room_id"])
                            ->where('slug', 'room_description')->first()->value;
                        //Crear Rate Plan Room
                        $rate_plan_room_id = DB::table('rates_plans_rooms')->insertGetId([
                            'rates_plans_id' => $rate_plan->id,
                            'room_id' => $room_id,
                            'status' => 1,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                            'bag' => 0,
                            'channel_id' => 1,
                        ]);
                        $rooms[$index_room]["rate_plan_room_id"] = $rate_plan_room_id;
                        //Registros de channel
                        DB::table('channel_room')->insert([
                            "code" => $room_id,
                            "state" => 1,
                            "room_id" => $room_id,
                            "channel_id" => 1
                        ]);
                        DB::table('channel_room')->insert([
                            "code" => "",
                            "state" => 0,
                            "room_id" => $room_id,
                            "channel_id" => 2
                        ]);
                        DB::table('channel_room')->insert([
                            "code" => "",
                            "state" => 0,
                            "room_id" => $room_id,
                            "channel_id" => 3
                        ]);
                        DB::table('channel_room')->insert([
                            "code" => "",
                            "state" => 0,
                            "room_id" => $room_id,
                            "channel_id" => 4
                        ]);
                        DB::table('channel_room')->insert([
                            "code" => "",
                            "state" => 0,
                            "room_id" => $room_id,
                            "channel_id" => 5
                        ]);
                        //Traducciones de nombre de habitacion
                        DB::table('translations')->insert([
                            "type" => "room",
                            "object_id" => $room_id,
                            "slug" => "room_name",
                            "value" => $room_name . " + CAMA ADICIONAL",
                            "language_id" => 1,
                            "created_at" => Carbon::now(),
                            "updated_at" => Carbon::now()
                        ]);
                        DB::table('translations')->insert([
                            "type" => "room",
                            "object_id" => $room_id,
                            "slug" => "room_name",
                            "value" => $room_name . " + BED ADDITIONAL",
                            "language_id" => 2,
                            "created_at" => Carbon::now(),
                            "updated_at" => Carbon::now()
                        ]);
                        DB::table('translations')->insert([
                            "type" => "room",
                            "object_id" => $room_id,
                            "slug" => "room_name",
                            "value" => $room_name . " + BED ADDITIONAL",
                            "language_id" => 3,
                            "created_at" => Carbon::now(),
                            "updated_at" => Carbon::now()
                        ]);
                        DB::table('translations')->insert([
                            "type" => "room",
                            "object_id" => $room_id,
                            "slug" => "room_name",
                            "value" => $room_name . " + BED ADDITIONAL",
                            "language_id" => 4,
                            "created_at" => Carbon::now(),
                            "updated_at" => Carbon::now()
                        ]);
                        //traducciones de descripcion de habitacion
                        DB::table('translations')->insert([
                            "type" => "room",
                            "object_id" => $room_id,
                            "slug" => "room_description",
                            "value" => $room_description . " + CAMA ADICIONAL",
                            "language_id" => 1,
                            "created_at" => Carbon::now(),
                            "updated_at" => Carbon::now()
                        ]);
                        DB::table('translations')->insert([
                            "type" => "room",
                            "object_id" => $room_id,
                            "slug" => "room_description",
                            "value" => $room_description . " + BED ADDITIONAL",
                            "language_id" => 2,
                            "created_at" => Carbon::now(),
                            "updated_at" => Carbon::now()
                        ]);
                        DB::table('translations')->insert([
                            "type" => "room",
                            "object_id" => $room_id,
                            "slug" => "room_description",
                            "value" => $room_description . " + BED ADDITIONAL",
                            "language_id" => 3,
                            "created_at" => Carbon::now(),
                            "updated_at" => Carbon::now()
                        ]);
                        DB::table('translations')->insert([
                            "type" => "room",
                            "object_id" => $room_id,
                            "slug" => "room_description",
                            "value" => $room_description . " + BED ADDITIONAL",
                            "language_id" => 4,
                            "created_at" => Carbon::now(),
                            "updated_at" => Carbon::now()
                        ]);
                    }
                }

                foreach ($rooms as $room) {

                    $date_ranges = DB::table('date_range_hotels')->where('rate_plan_id', $rate_plan->id)->where('room_id', $room["room_id"])->get();

                    foreach ($date_ranges as $date_range) {
                        DB::table('date_range_hotels')->insert([
                            'date_from' => $date_range->date_from,
                            'date_to' => $date_range->date_to,
                            'price_adult' => $date_range->price_adult,
                            'price_child' => $date_range->price_child,
                            'price_infant' => $date_range->price_infant,
                            'price_extra' => $date_range->price_extra,
                            'discount_for_national' => $date_range->discount_for_national,
                            'rate_plan_id' => $date_range->rate_plan_id,
                            'hotel_id' => $date_range->hotel_id,
                            'room_id' => $room["room_equivalence"],
                            'rate_plan_room_id' => $room["rate_plan_room_id"],
                            'meal_id' => $date_range->meal_id,
                            'policy_id' => $date_range->policy_id,
                            'old_id_date_range' => $date_range->old_id_date_range,
                            'group' => $date_range->group,
                            'updated' => 1,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }

                    //Actualizar precio extra a 0 del Room anterior
                    DB::table('date_range_hotels')->where('rate_plan_id', $rate_plan->id)->where('room_id', $room["room_id"])->update([
                        'price_extra' => 0,
                        'updated' => 1
                    ]);
                }
            }
        });

        return response()->json(["message" => "Habitaciones con cama adicional creadas"]);
    }

    private function searchRoomExists($rooms, $room_id)
    {
        foreach ($rooms as $room) {
            if ($room["room_id"] == $room_id) {
                return true;
            }
        }
        return false;
    }

    public function exampleRemoteServer ()
    {
//        $token = $this->createToken('anthonyfilgueira@hotmail.com');
        $time = config('auth.passwords.users.expire');
        $link = url("/#/reset-password/" . 'asdsadasdjvyfbdfads');

        $mail = Mail::to('anthonyfilgueira@hotmail.com')->send(new ResetPassword($link, $time,'en'));

        return response()->json($mail);
    }

    public function get_packages_groups_services_limit ()
    {
        $services_ids = Service::where('status', 1)
            ->where('pax_max', '<', 40)
            ->pluck('id');

        $packages = Package::whereNotIn('id',[
            459,714,67,579,399,575,461,712,726
        ])
            ->with([
                'translations' => function ($query) {
                    $query->select([
                        'package_id',
                        'name',
                        'tradename',
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
                    $query->where('language_id', 1);
                },
            ])
            ->where('status', 1)
            ->with(['plan_rates'=>function($q)use($services_ids){
                $q->where('service_type_id', 2);
                $q->where('status', 1);
                $q->with(['plan_rate_categories'=>function($q2)use($services_ids){
                    $q2->with([
                        'category' => function ($q2_) {
                            $q2_->with([
                                'translations' => function ($q2__) {
                                    $q2__->where('type', 'typeclass');
                                    $q2__->where('language_id', 1);
                                }
                            ]);

                        }
                    ]);
                    $q2->with(['services'=>function($q3)use($services_ids){
                       $q3->whereIn('object_id', $services_ids);
                       $q3->with('service');
                    }]);
                }]);
            }])
            ->get();

//        return response()->json($packages);
        return view('exports.packages_groups_services')->with(['packages'=> $packages]);
    }

    /**
     * Lista de un grupo de hoteles en particular para ver sus amenidades y categorias
     * @return View
     */
    public function get_hotels_customize_list ()
    {
        $hotels = Hotel::with([
            'translations' => function ($query) {
                $query->where('type', 'hotel');
            }
        ])->with([
            'typeclass.translations' => function ($query)  {
                $query->where('type', 'typeclass');
                $query->whereHas('language', function ($q) {
                    $q->where('iso', "es");
                });
            }
        ])->with([
            'amenity.translations' => function ($query) {
                $query->where('type', 'amenity');
                $query->whereHas('language', function ($q) {
                    $q->where('iso', "es");
                });
            }
        ])->with([
            'channel' => function ($query) {
                $query->where('channel_id', 1);
            }
        ])
            ->whereIn('id', [308,
                324,
                325,
                327,
                330,
                331,
                332,
                333,
                338,
                339,
                341,
                342,
                348,
                349,
                350,
                351,
                352,
                353,
                357,
                359,
                361,
                364,
                365,
                366,
                367,
                368,
                369,
                370,
                371,
                372,
                373,
                382,
                383,
                384])
            ->get();

//        return response()->json($hotels);
        return view('exports.hotels_customize_list')->with(['hotels'=> $hotels]);
    }

    public function get_hotel_rates_plans_rooms_with_inventories(Request $request)
    {
        $year = $request->input('year');
        if(!$year){
            $year = Carbon::now()->year + 1;
        }

        $hotel_ids = Hotel::where('status', 1)->pluck('id');

        $rates_plans_ids = RatesPlans::whereIn('hotel_id', $hotel_ids)
            ->where('status', 1)
            ->where('allotment', 1)
            ->pluck('id');

        $rates_plans_rooms = RatesPlansRooms::whereIn('rates_plans_id', $rates_plans_ids)
            ->where('status', 1)
            ->where('channel_id', 1)
            ->with(['rate_plan.hotel.channel'])
            ->with(['room.room_type'])
//            ->skip(2200)
//            ->take(200)
            ->get();
        //2138

        $rates_plans_rooms_array = [];

        foreach ( $rates_plans_rooms as $rate_plan_room ){
            if ($rate_plan_room->bag == 1) {

                $bag_rate = BagRate::select('bag_room_id')->where('rate_plan_rooms_id',
                    $rate_plan_room->id)->first();

                if ($bag_rate != null) {

                    $inventories = InventoryBag::whereYear('date', $year)
                        ->where('locked', 0)
                        ->where('inventory_num', '>=', 1)
                        ->where('bag_room_id', $bag_rate->bag_room_id)->get();

                    if ($inventories->count() > 0) {
                        array_push( $rates_plans_rooms_array, $rate_plan_room);
                    }
                }
            }
            if ($rate_plan_room->bag == 0) {
                $inventories = Inventory::whereYear('date', $year)
                    ->where('locked', 0)
                    ->where('inventory_num', '>=', 1)
                    ->where('rate_plan_rooms_id', $rate_plan_room->id)->get();

                if ($inventories->count() > 0) {
                    array_push( $rates_plans_rooms_array, $rate_plan_room);
                }
            }

        }

//        return response()->json($rates_plans_rooms_array);
        return view('exports.hotels_rates_plans_rooms_inventories')->with(['rates_plans_rooms'=> $rates_plans_rooms_array]);
    }

    public function get_email_booking_confirmation(){
        return view('emails.booking-confirmation');
    }

    public function get_email_booking_cancellation(){
        return view('emails.booking-rq-cancellation');
    }

}
