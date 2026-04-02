<?php

namespace App\Http\Controllers;

use App\Room;
use App\RoomType;
use Carbon\Carbon;
use App\PackageService;
use App\PackagePlanRate;
use App\RatesPlansRooms;
use App\PackageServiceRate;
use App\PackageServiceRoom;
use App\Http\Traits\Package;
use Illuminate\Http\Request;
use App\RatesPlansCalendarys;
use App\PackageServiceOptional;
use App\PackagePlanRateCategory;
use App\Http\Stella\StellaService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\PackageServiceRoomHyperguest;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class PackageServicesController extends Controller
{
    use Package;
    protected $stellaService;

    public function __construct(StellaService $stellaService)
    {
        $this->stellaService = $stellaService;

        $this->middleware('permission:packages.read')->only('index');
        $this->middleware('permission:packages.create')->only('store');
        $this->middleware('permission:packages.update')->only('update');
        $this->middleware('permission:packages.delete')->only('delete');
    }

    public function storeHotelRoom($plan_rate_category_id, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'hotel_id' => 'required',
                'date_in' => 'required',
                'hyperguest' => 'boolean',
                'rate_plan_room_id' => 'required_unless:hyperguest,false,0,"false"'
            ]);

            $hyperguestValue = $request->get('hyperguest', false);

            if ($validator->fails()) {
                return Response::json([
                    'success' => false,
                    'error'=> $validator->errors()
                ]);
            } else {
                DB::beginTransaction();
                $hotel_id = $request->input('hotel_id');
                $date_in = $request->input('date_in');
                $package_service = PackageService::where('package_plan_rate_category_id', $plan_rate_category_id)
                    ->where('type', 'hotel')->where('object_id', $hotel_id)
                    ->where('date_in', $date_in);
                $count = $package_service->count();
                if ($count == 0) {
                    $package_service = new PackageService();
                    $package_service->type = 'hotel';
                    $package_service->object_id = $hotel_id;
                    $package_service->package_plan_rate_category_id = $plan_rate_category_id;
                    $package_service->date_in = $date_in;
                    $package_service->date_out = $request->input('date_out');
                    $package_service->re_entry = $request->input('re_entry');
                    // $package_service->hyperguest_pull = ($hyperguestValue ? 1 : 0);
                    $package_service->save();
                } else {
                    $package_service = $package_service->first();
                }

                // Obtener la ocupación del nuevo room usando el método unificado
                $occupation_for_put = $this->getOccupationForRoom(
                    $hyperguestValue,
                    $request->input('rate_plan_room_id'),
                    $request->input('room_id')
                );

                // Validar y limpiar rooms duplicados entre ambos tipos (hyperguest y no hyperguest)
                $cleaned_rooms = $this->validateAndCleanRoomsByOccupation(
                    $package_service->id,
                    $occupation_for_put,
                    $hotel_id
                );

                $rate_plan_room_ids_deletes = $cleaned_rooms['rate_plan_room_ids_deletes'];
                $delete_rooms = $cleaned_rooms['delete_rooms'];

                // Crear el nuevo registro según el tipo
                if (!$hyperguestValue) {
                    $package_service_room = new PackageServiceRoom();
                    $package_service_room->package_service_id = $package_service->id;
                    $package_service_room->rate_plan_room_id = $request->input('rate_plan_room_id');
                    $package_service_room->save();
                } else {
                    $package_service_room = new PackageServiceRoomHyperguest();
                    $package_service_room->package_service_id = $package_service->id;
                    $package_service_room->rate_plan_id = $request->input('rate_plan_id');
                    $package_service_room->room_id = $request->input('room_id');
                    $package_service_room->num_adult = $request->input('num_adult', 0);
                    $package_service_room->price_adult = $request->input('price_adult', 0);
                    $package_service_room->price_amount_base = $request->input('price_amount_base', 0);
                    $package_service_room->price_amount_total = $request->input('price_amount_total', 0);
                    $package_service_room->save();
                }
                DB::commit();

                return Response::json([
                    'success' => true,
                    'object_id' => $package_service_room->id,
                    'rate_plan_room_ids_deletes' => $rate_plan_room_ids_deletes,
                    'delete_rooms' => $delete_rooms,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e]);
        }
    }

    public function storeFlight(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'date' => 'required',
                'categories' => 'required'
            ]);

            if ($validator->fails()) {
                return Response::json(['success' => false]);
            } else {

                DB::beginTransaction(); $flight = (object) $request->__get('flight');
                $origin = (array) $flight->origin;
                $destiny = (array) $flight->destiny;
                $date_in = $flight->date;

                if($date_in != '')
                {
                    $date = explode("/", $flight->date);

                    if(count($date) > 1)
                    {
                        $date_in = $date[2] . '-' . $date[1] . '-' . $date[0];
                    }
                }

                // $date_in = $flight->date;
                $categories = $request->input('categories');

                $code_flight = ($flight->type == 1) ? 'AEIFLT' : 'AECFLT';

                foreach($categories as $key => $value)
                {
                    $plan_rate_category_id = $value['id'];

                    $package_service = PackageService::where('package_plan_rate_category_id', $plan_rate_category_id)
                        ->where('type', 'flight')
                        ->where('date_in', $date_in)
                        ->where('origin', @$origin['codciu'])
                        ->where('destiny', @$destiny['codciu']);

                    $count = $package_service->count();
                    if ($count == 0) {
                        $package_service = new PackageService();
                        $package_service->type = 'flight';
                        $package_service->object_id = 0;
                        $package_service->package_plan_rate_category_id = $plan_rate_category_id;
                        $package_service->date_in = $date_in;
                        $package_service->date_out = $date_in;
                        $package_service->code_flight =  $code_flight;
                        $package_service->origin =  @$origin['codciu'];
                        $package_service->destiny =  @$destiny['codciu'];
                        $package_service->save();
                    } else {
                        $package_service = $package_service->first();
                    }
                }

                DB::commit();

                return Response::json([
                    'success' => true,
                    'object_id' => $package_service->id,
                    'package_service' => $package_service
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e]);
        }
    }

    public function get_occupation_by_rate_plan_room_id($rate_plan_room_id)
    {

        $rate_plan_room = RatesPlansRooms::with('room.room_type')
            ->where('id', $rate_plan_room_id)->first();

        $occupation = $rate_plan_room->room->room_type->occupation;

        return $occupation;
    }

    public function getOccupationRoom(int $room_id){
        $room = Room::with('room_type')->where('id', $room_id)->first();
        return $room->room_type->occupation;
    }

    private function getOccupationForRoom($is_hyperguest, $rate_plan_room_id = null, $room_id = null)
    {
        if ($is_hyperguest) {
            return $this->getOccupationRoom($room_id);
        } else {
            return $this->get_occupation_by_rate_plan_room_id($rate_plan_room_id);
        }
    }

    private function validateAndCleanRoomsByOccupation($package_service_id, $occupation_for_put, $hotel_id)
    {
        $rate_plan_room_ids_deletes = [];
        $delete_rooms = [];

        // Obtener rooms existentes de AMBOS tipos
        $package_service_rooms = PackageServiceRoom::where('package_service_id', $package_service_id)->get();
        $package_service_rooms_hyperguest = PackageServiceRoomHyperguest::where('package_service_id', $package_service_id)->get();

        // Validar y eliminar rooms de tipo no hyperguest
        foreach ($package_service_rooms as $package_service_room) {
            $occupation = $this->get_occupation_by_rate_plan_room_id($package_service_room->rate_plan_room_id);

            if ($occupation === $occupation_for_put) {
                array_push($rate_plan_room_ids_deletes, $package_service_room->rate_plan_room_id);
                $delete_rooms[] = [
                    'hotel_id' => $hotel_id,
                    'service_room_id' => $package_service_room->id,
                    'rate_plan_room_id' => $package_service_room->rate_plan_room_id,
                    'hyperguest' => false,
                ];
                $package_service_room->delete();
            }
        }

        // Validar y eliminar rooms de tipo hyperguest
        foreach ($package_service_rooms_hyperguest as $package_service_room) {
            $occupation = $this->getOccupationRoom($package_service_room->room_id);

            if ($occupation === $occupation_for_put) {
                array_push($rate_plan_room_ids_deletes, $package_service_room->id);
                $delete_rooms[] = [
                    'hotel_id' => $hotel_id,
                    'service_room_id' => $package_service_room->id,
                    'rate_plan_id' => $package_service_room->rate_plan_id,
                    'room_id' => $package_service_room->room_id,
                    'hyperguest' => true,
                ];
                $package_service_room->delete();
            }
        }

        return [
            'rate_plan_room_ids_deletes' => $rate_plan_room_ids_deletes,
            'delete_rooms' => $delete_rooms,
        ];
    }

    public function storeServiceRate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'package_plan_rate_id' => 'required',
                'date_in' => 'required',
                'service_id' => 'required',
                'categories' => 'required',
                'service_rates_id' => 'required',
            ]);
            if ($validator->fails()) {
                return Response::json(['success' => false]);
            } else {
                DB::beginTransaction();
                $date_in = $request->input('date_in');
                $service_id = $request->input('service_id');
                $package_plan_rate_id = $request->input('package_plan_rate_id');
                $service_rates_id = $request->input('service_rates_id');
                $package_categories = $request->input('categories');

                // Eliminar servicios de las categorías..
                $codes = $request->input('remove_codes') ?? []; $order = '';

                if (!empty($codes)) {
                    $categoryIds = collect($package_categories)->pluck('id')->toArray();

                    $service = PackageService::whereIn('package_plan_rate_category_id', $categoryIds)
                        ->where('type', '=', 'service')
                        ->whereHas('service', function ($query) use ($codes) {
                            $query->whereIn('aurora_code', $codes);
                        });

                    $order = $service->order;
                    $service->delete();
                }

                foreach ($package_categories as $category) {
                    $package_service = new PackageService();
                    $package_service->type = 'service';
                    $package_service->object_id = $service_id;
                    $package_service->package_plan_rate_category_id = $category['id'];
                    $package_service->date_in = $date_in;
                    if(!empty($order))
                    {
                        $package_service->order = $order;
                    }
                    $package_service->save();
                    $package_service_rate = new PackageServiceRate();
                    $object_ids[]['id'] = $package_service->id;
                    $package_service_rate->package_service_id = $package_service->id;
                    $package_service_rate->service_rate_id = $service_rates_id;
                    $package_service_rate->save();
                }
                DB::commit();

                return Response::json(['success' => true, 'object_ids' => $object_ids]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e]);
        }

    }

    public function destroyHotelRoom($plan_rate_category_id, Request $request)
    {
        $validator = Validator::make($request->all(),[
            'hotel_id' => 'required',
            'date_in' => 'required',
            'hyperguest' => 'boolean',
            'rate_plan_room_id' => 'required_unless:hyperguest,false,0,"false"',
        ]);

        $hyperguestValue = $request->get('hyperguest', false);

        if ($validator->fails()) {
            $response = ['success' => false];
        } else {

            $hotel_id = $request->input('hotel_id');
            $date_in = $request->input('date_in');

            $package_service = PackageService::where('package_plan_rate_category_id', $plan_rate_category_id)
                ->where('type', 'hotel')->where('object_id', $hotel_id)
                ->where('date_in', $date_in);

            $data_package_service = $package_service->first();

            if ($hyperguestValue) {
                $delete_package_service_room = PackageServiceRoomHyperguest::where('package_service_id', $data_package_service->id)
                    ->where('room_id', $request->input('room_id'))->where('rate_plan_id', $request->input('rate_plan_id'))->delete();
            }else{
                $delete_package_service_room = PackageServiceRoom::where('package_service_id', $data_package_service->id)
                    ->where('rate_plan_room_id', $request->input('rate_plan_room_id'))->delete();
            }

            if ($delete_package_service_room) {
                if ($hyperguestValue){
                    $count_package_service_room = PackageServiceRoomHyperguest::where('package_service_id', $data_package_service->id)
                        ->count();

                    if ($count_package_service_room == 0) {
                        $package_service->delete();
                    }

                    $response = ['success' => true, 'object_id' => $data_package_service->id];
                }else{
                    $count_package_service_room = PackageServiceRoom::where('package_service_id', $data_package_service->id)
                        ->count();

                    if ($count_package_service_room == 0) {
                        $package_service->delete();
                    }
                    $response = ['success' => true, 'object_id' => $data_package_service->id];
                }
            } else {
                $response = ['success' => false];
            }
        }

        return Response::json($response);
    }

    public function destroy($id)
    {
        $package_service = PackageService::find($id);
        if ($package_service->type == 'hotel') {
            $room_services = PackageServiceRoom::where('package_service_id', $package_service->id)->get();
            foreach ($room_services as $room_service) {
                $room_service->delete();
            }
            $room_services_hyperguest = PackageServiceRoomHyperguest::where('package_service_id', $package_service->id)->get();
            foreach($room_services_hyperguest as $room_service_hyperguest){
                $room_service_hyperguest->delete();
            }
        } elseif ($package_service->type == 'service') {
            $service_rates = PackageServiceRate::where('package_service_id', $package_service->id)->get();
            foreach ($service_rates as $rate) {
                $rate->delete();
            }
        }

        if ($package_service->delete()) {
            $response = ['success' => true];
        } else {
            $response = ['success' => false];
        }
        return Response::json($response);
    }

    public function shareHotel(Request $request)
    {
        $category_id = $request->input('category_id');
        $package_service_hotel_id = $request->input('package_service_id');

        $package_service = PackageService::find($package_service_hotel_id);

        $find_count_same = PackageService::where('package_plan_rate_category_id', $category_id)
            ->where('date_in', $package_service->date_in)
            ->where('type', 'hotel');

        if ($find_count_same->count() > 0) {
            $find_count_same = $find_count_same->first();
            if ($find_count_same->object_id == $package_service->object_id) {
                $response = ['success' => false, 'type' => 0, 'text' => 'Ya se compartió aquí'];
            } else {
                $response = ['success' => false, 'type' => 1, 'text' => 'Ya existe otro hotel en esa fecha'];
            }
        } else {
            $clone_package_service = new PackageService();
            $clone_package_service->type = 'hotel';
            $clone_package_service->object_id = $package_service->object_id;
            $clone_package_service->package_plan_rate_category_id = $category_id;
            $clone_package_service->order = $package_service->order;
            $clone_package_service->date_in = $package_service->date_in;
            $clone_package_service->date_out = $package_service->date_out;
            $clone_package_service->adult = $package_service->adult;
            $clone_package_service->child = $package_service->child;
            $clone_package_service->infant = $package_service->infant;
            $clone_package_service->single = $package_service->single;
            $clone_package_service->double = $package_service->double;
            $clone_package_service->triple = $package_service->triple;
            $clone_package_service->re_entry = $package_service->re_entry;
            $clone_package_service->save();

            $find_rooms = PackageServiceRoom::where('package_service_id', $package_service->id)->get();

            foreach ($find_rooms as $r) {
                $clone_room = new PackageServiceRoom();
                $clone_room->package_service_id = $clone_package_service->id;
                $clone_room->rate_plan_room_id = $r->rate_plan_room_id;
                $clone_room->save();
            }

            $response = ['success' => true];

        }

        return Response::json($response);
    }

    public function shareFlight(Request $request)
    {
        $category_id = $request->input('category_id');
        $package_service_hotel_id = $request->input('package_service_id');

        $package_service = PackageService::find($package_service_hotel_id);

        $find_count_same = PackageService::where('package_plan_rate_category_id', $category_id)
            ->where('date_in', $package_service->date_in)
            ->where('type', 'flight');

        if ($find_count_same->count() > 0) {
            $find_count_same = $find_count_same->first();
            if ($find_count_same->object_id == $package_service->object_id) {
                $response = ['success' => false, 'type' => 0, 'text' => 'Ya se compartió aquí'];
            } else {
                $response = ['success' => false, 'type' => 1, 'text' => 'Ya existe otro vuelo en esa fecha'];
            }
        } else {
            $clone_package_service = new PackageService();
            $clone_package_service->type = 'flight';
            $clone_package_service->object_id = $package_service->object_id;
            $clone_package_service->package_plan_rate_category_id = $category_id;
            $clone_package_service->order = $package_service->order;
            $clone_package_service->date_in = $package_service->date_in;
            $clone_package_service->date_out = $package_service->date_out;
            $clone_package_service->adult = $package_service->adult;
            $clone_package_service->child = $package_service->child;
            $clone_package_service->infant = $package_service->infant;
            $clone_package_service->single = $package_service->single;
            $clone_package_service->double = $package_service->double;
            $clone_package_service->triple = $package_service->triple;
            $clone_package_service->re_entry = $package_service->re_entry;
            $clone_package_service->origin = $package_service->origin;
            $clone_package_service->destiny = $package_service->destiny;
            $clone_package_service->code_flight = $package_service->code_flight;
            $clone_package_service->save();

            $response = ['success' => true];
        }

        return Response::json($response);
    }

    public function newOrders(Request $request)
    {
        $newOrders = $request->input('newOrders');

        $success = 0;
        foreach ($newOrders as $newOrder) {
            $serv = PackageService::find($newOrder['id']);
            $serv->order = $newOrder['order'];
            if ($serv->save()) {
                $success++;
            }
        }

        if (count($newOrders) == $success) {
            $response = ['success' => true];
        } else {
            $response = ['success' => false];
        }
        return Response::json($response);
    }

    public function calculation_included(Request $request)
    {
        try {
            $package_service_id = $request->input('package_service_id');
            $_value = $request->input('_value');
            $store_inluded = PackageService::find($package_service_id);
            $store_inluded->calculation_included = (int)$_value;
            if ($store_inluded->save()) {
                $response = ['success' => true];
            } else {
                $response = ['success' => false];
            }
            return Response::json($response);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e->getMessage().' Line:'.$e->getLine()]);
        }
    }

    public function searchByCategory($plan_rate_category_id, Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');

        $with_trashed = $request->input('with_trashed') ? true : false;

        $package_services = PackageService::with(['hotel' => function($query) use ($with_trashed){
            if( $with_trashed ){
                $query->withTrashed();
            }
            $query->with('channel');
            $query->with('country.translations');
            $query->with('city.translations');
            $query->with('state.translations');
        }])
            ->with(['service_rooms.rate_plan_room.rate_plan'=> function($query) use ($with_trashed){
                if( $with_trashed ){
                    $query->withTrashed();
                }
            }])
            ->with(['service_rooms.rate_plan_room.room' => function($query) use ($with_trashed){
                if( $with_trashed ){
                    $query->withTrashed();
                }
                $query->with(['room_type.translations'=>function($query0){
                    $query0->where('language_id', 1);
                }]);
            }])
            ->with([
                'service_rooms_hyperguest.room.translations',
                'service_rooms_hyperguest.room.room_type',
                'service_rooms_hyperguest.rate_plan' => function($query) use ($with_trashed){
                    if( $with_trashed ){
                        $query->withTrashed();
                    }
                }
            ])
            ->with(['service_rates'])
            ->with([
                'service' => function ($query) use ($with_trashed) {
                    $query->with([
                        'service_rate.service_rate_plans'
                    ]);
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
                    $query->withCount(['serviceEquivAssociation']);
                    if( $with_trashed ){
                        $query->withTrashed();
                    }
                }
            ])
            ->where('package_plan_rate_category_id', $plan_rate_category_id)
//            ->where('id', 29934) //
            ->orderBy('date_in')
            ->orderBy('order');

        $count = $package_services->count();

        if ($paging === 1) {
            $package_services = $package_services->take($limit)->get();
        } else {
            $package_services = $package_services->skip($limit * ($paging - 1))->take($limit)->get();
        }

        // price_from  |  price_from_pax
        foreach ($package_services as $k => $p_s) {

            if ($p_s->type == "service") {
                foreach ($p_s->service->service_rate as $s_rate) {
                    $s_rate->price_from = '';
                    $s_rate->price_from_pax = '';
                    foreach ($s_rate->service_rate_plans as $s_plan) {
                        if (strtotime($s_plan->date_from) <= strtotime($package_services[$k]->date_in) &&
                            strtotime($s_plan->date_to) >= strtotime($package_services[$k]->date_in)) {
                            $s_rate->price_from = $s_plan->price_adult;
                            $s_rate->price_from_pax = $s_plan->pax_from;
                            break;
                        }
                    }
                }
            }

        }

        $package_services = $package_services->toArray();

        for( $i=0; $i<count($package_services); $i++ ){
            if ($package_services[$i]['type'] === "hotel") {
                for ( $r=0; $r< count($package_services[$i]['service_rooms']); $r++) {
                    $package_services[$i]['service_rooms'][$r]['rate_plan_room']['first_rate'] = [];

                    $package_services[$i]['service_rooms'][$r]['rate_plan_room']['calendarys_in_dates']  =
                        RatesPlansCalendarys::where('rates_plans_room_id', $package_services[$i]['service_rooms'][$r]['rate_plan_room']['id'] )
                            ->where('date', '<', $package_services[$i]['date_out'] )
                            ->where('date', '>=', $package_services[$i]['date_in'] )
                            ->with('rate')
                            ->get();

                    foreach ($package_services[$i]['service_rooms'][$r]['rate_plan_room']['calendarys_in_dates'] as $calendary) {
                        if (count($package_services[$i]['service_rooms'][$r]['rate_plan_room']['first_rate']) == 0
                            && count($calendary->rate) > 0) {
                            $package_services[$i]['service_rooms'][$r]['rate_plan_room']['first_rate'] = $calendary->rate;
                        }
                    }
                }
            }
        }

        // ORDERNAR LAS HABITACIONES DE HYPERGUEST SIMPLE, DOBLE Y TRIPLE
        foreach ($package_services as &$service) {
            if (!empty($service['service_rooms_hyperguest'])) {
                usort($service['service_rooms_hyperguest'], function ($a, $b) {
                    $occupationA = $a['room']['room_type']['occupation'] ?? 0;
                    $occupationB = $b['room']['room_type']['occupation'] ?? 0;
                    return $occupationA <=> $occupationB; // ascendente
                });
            }
        }

        return Response::json(['success' => true, 'data' => $package_services, 'count' => $count]);
    }

    /** Retornará hoteles que contengan errores que no tengan tarifas dentro de sus dias que tiene definido en su coti
     * @param $plan_rate_category_id
     * @param Request $request | limit of pages
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify_errors_rates_hotels_per_pages($plan_rate_category_id, Request $request)
    {
        $limit = $request->input('limit');

        $package_services = PackageService::with(['service_rooms.rate_plan_room.rate_plan'])
            ->where('package_plan_rate_category_id', $plan_rate_category_id)
            ->orderBy('date_in')
            ->orderBy('order')
            ->get()->toArray();


        $hotels = [];
        $count_ = 1;
        for ($i=0; $i<count($package_services);$i++) {
            $package_services[$i]['page'] = ceil( $count_ / $limit );
            if ($package_services[$i]['type'] === "hotel") {

                $date_service_in = Carbon::parse($package_services[$i]['date_in']);
                $date_service_out = Carbon::parse($package_services[$i]['date_out']);
                $nights = $date_service_in->diffInDays($date_service_out);
                $errors_ = 0;

                for ($sr=0; $sr<count( $package_services[$i]['service_rooms'] ); $sr++) {

                    $calendarys_ =
                        RatesPlansCalendarys::where('rates_plans_room_id', $package_services[$i]['service_rooms'][$sr]['rate_plan_room']['id'] )
                            ->where('date', '<', $package_services[$i]['date_out'] )
                            ->where('date', '>=', $package_services[$i]['date_in'] )
                            ->get();

                    if( count($calendarys_) < $nights ){
                        $errors_++;
                    }

                    $package_services[$i]['service_rooms'][$sr]['rate_plan_room']['calendarys'] = $calendarys_;

                }

                if( $errors_ > 0 ){
                    array_push( $hotels, $package_services[$i] );
                }

            }
            $count_++;
        }

        return Response::json(['success' => true, 'data' => $hotels]);
    }

    public function searchHotelsByCategories(Request $request)
    {
        $package_services = PackageService::with([
            'hotel.channel',
            'hotel.country.translations',
            'hotel.state.translations',
            'hotel.city.translations',
            'plan_rate_category',
            'service_rooms',
            'service_rooms_hyperguest',
        ])
            ->where('package_services.type', '=', 'hotel')
            ->whereIn('package_plan_rate_category_id', $request->input('plan_rate_categories'))
            ->get();

        $package_services_optional = PackageServiceOptional::with([
            'hotel.channel',
            'hotel.country.translations',
            'hotel.state.translations',
            'hotel.city.translations',
            'plan_rate_category',
            'service_rooms'
        ])
            ->where('package_service_optionals.type', '=', 'hotel')
            ->whereIn('package_plan_rate_category_id', $request->input('plan_rate_categories'))
            ->get();

        $data = [
            'services' => $package_services,
            'optional' => $package_services_optional,
        ];
        return Response::json(['success' => true, 'data' => $data]);
    }

    public function searchFlightsByCategories(Request $request)
    {
        $package_services = PackageService::where('package_services.type', '=', 'flight')
            ->whereIn('package_plan_rate_category_id', $request->input('plan_rate_categories'))
            ->get();
        return Response::json(['success' => true, 'data' => $package_services]);
    }

    public function updateRates(Request $request)
    {

        // BORRAR TODO LO QUE POSEE EN ESA FECHA Y DESTINO ACTUAL

        $current_country_id = $request->input('country_id');
        $current_state_id = $request->input('state_id');
        $current_date_in = $request->input('date_in');
        $current_date_out = $request->input('date_out');

        $package_services = PackageService::with(['hotel', 'service_rooms'])
            ->where('package_services.type', 'hotel')
            ->where('package_services.date_in', $current_date_in)
            ->whereIn('package_plan_rate_category_id', $request->input('plan_rate_categories'))
            ->where(function ($query) use ($current_country_id, $current_state_id) {
                $query->whereHas('hotel', function ($query) use ($current_country_id, $current_state_id) {
                    $query->where('country_id', $current_country_id);
                    $query->where('state_id', $current_state_id);
                });
            })
            ->get();

        foreach ($package_services as $pack_serv) {
            $room_services = PackageServiceRoom::where('package_service_id', $pack_serv->id)->get();
            foreach ($room_services as $room_service) {
                $room_service->delete();
            }
            $pack_serv->forceDelete();
        }

        // CREAR O ELIMINAR RATES / ACTIVAR O DESACTIVAR RE ENTRYS
        $tmpServices = [];
        $rooms = $request->input('rooms');

        if ($request->input('action')) {
            foreach ($rooms['rooms'] as $r) {
                if (!(isset($tmpServices[$r['package_service_id']]))) {

                    $firstService = PackageService::find($r['package_service_id']);
                    $firstService->re_entry = 1;
                    $firstService->save();

                    $newService = new PackageService();
                    $newService->type = 'hotel';
                    $newService->object_id = $firstService->object_id;
                    $newService->package_plan_rate_category_id = $firstService->package_plan_rate_category_id;
                    $newService->order = $firstService->order;
                    $newService->date_in = $current_date_in;
                    $newService->date_out = $current_date_out;
                    $newService->re_entry = 1;
                    $newService->save();
                    $tmpServices[$r['package_service_id']] = $newService->id;
                }

                $newServiceRoom = new PackageServiceRoom();
                $newServiceRoom->package_service_id = $tmpServices[$r['package_service_id']];
                $newServiceRoom->rate_plan_room_id = $r['rate_plan_room_id'];
                $newServiceRoom->save();
            }
        } else {
            foreach ($rooms['rooms'] as $r) {
                if (!(isset($tmpServices[$r['package_service_id']]))) {

                    $firstService = PackageService::find($r['package_service_id']);
                    $firstService->re_entry = 0;
                    $firstService->save();

                    $tmpServices[$r['package_service_id']] = $firstService->id;
                }
            }
        }

        return Response::json(['success' => true, 'data' => $package_services]);
    }

    public function destroyServiceRates(Request $request)
    {
        try {
            DB::beginTransaction();
            $object_ids = $request->get('object_ids');
            foreach ($object_ids as $id) {
                $service_rates = PackageServiceRate::where('package_service_id', $id['id'])->get();
                foreach ($service_rates as $rate) {
                    $rate->delete();
                }
                $package_service = PackageService::find($id['id']);
                $package_service->delete();
            }
            DB::commit();
            $response = ['success' => true];
            return Response::json($response);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function changeService(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'service_id' => 'required',
                'service_rate_id' => 'required'
            ]);

            if ($validator->fails()) {
                return Response::json(['success' => false]);
            } else {
                DB::beginTransaction();
                $package_service = PackageService::find($request->input('id'));
                $package_service->object_id = $request->input('service_id');

                if ($package_service->save()) {

                    $package_service_rates = PackageServiceRate::where('package_service_id',
                        $package_service->id)->get();
                    $packageServiceRateUpdate = PackageServiceRate::find($package_service_rates[0]['id']);
                    $packageServiceRateUpdate->service_rate_id = $request->input('service_rate_id');
                    $packageServiceRateUpdate->save();
                }
                DB::commit();
                return Response::json(['success' => true]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e]);
        }
    }

    public function saveServiceRateSelectedInPackage(Request $request)
    {
        $service_id = $request->post('service_id');
        $service_rate_id = $request->post('service_rate_id');
        $package_service_rate_id = $request->post('package_service_rate_id');

        if ($package_service_rate_id != "") {
            $package_service = PackageServiceRate::find($package_service_rate_id);
            $package_service->service_rate_id = $service_rate_id;
            $package_service->save();

        } else {
            $package_service = new PackageServiceRate();
            $package_service->package_service_id = $service_id;
            $package_service->service_rate_id = $service_rate_id;
            $package_service->save();
        }

        return response()->json(["message" => "tarifa de servicio seleccionado agregada"], 200);
    }

    public function passengersExport($plan_rate_category_id, Request $request)
    {
        $quantity_pax = $request->get('quantity_pax');
        $quantity_adult = $request->get('quantity_adult');
        $quantity_child = $request->get('quantity_child');
        $lang = $request->get('lang');
        return Excel::download(new  \App\Exports\PackagePassengersExport($plan_rate_category_id, $quantity_pax, $lang, $quantity_adult, $quantity_child),
            "export".'.xlsx');

    }
    public function deleteServiceRoom(Request $request)
    {
        $hyperguest = $request->input('hyperguest', false);
        if ($hyperguest) {
            $package_service_room = PackageServiceRoomHyperguest::find($request->input('service_room_id'));
            $package_service_room->delete();
        }else{
            $package_service_room = PackageServiceRoom::find($request->input('service_room_id'));
            $package_service_room->delete();
        }

        return \response()->json(["message"=>"Service Room eliminado"]);
    }

    // --
    public function search_destinations(Request $request)
    {
        $params = [];
        $params['term'] = $request->__get('term');
        $params['type'] = $request->__get('type');

        return $this->stellaService->search_destinations($params);
    }

    public function search_airlines(Request $request)
    {
        $params = [];
        $params['term'] = $request->__get('term');

        return $this->stellaService->search_airlines($params);
    }

    public function changeDateIn(Request $request)
    {
        $date = convertDate($request->input('date'), '/', '-', 1);
        $service = $request->__get('service');

        try
        {
            $response = $this->updateDateInServices([$service], $date, true);
            return Response::json(['success' => true, 'service' => $response['services'][0]]);
        }
        catch (\Exception $e)
        {
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function update_date_in($package_plan_rate_category_id, Request $request)
    {
        $date = convertDate($request->input('date_from'), '/', '-', 1);

        try
        {
            $_category = PackagePlanRateCategory::where('id', '=', $package_plan_rate_category_id)
                ->first();

            if($_category)
            {
                $categories = PackagePlanRateCategory::with(['services' => function ($query) {
                        $query->orderBy('date_in', 'asc')
                        ->orderBy('order', 'asc');
                    }])
                    ->where('package_plan_rate_id', '=', $_category->package_plan_rate_id)
                    ->get()->toArray();

                foreach($categories as $key => $category)
                {
                    $this->updateDateInServices($category['services'] ?? [], $date, true);
                }
            }

            /*
            $services = PackageService::where('package_plan_rate_category_id', $package_plan_rate_category_id)
                ->orderBy('date_in', 'asc')
                ->orderBy('order', 'asc')->get();
            */

            return Response::json(['success' => true]);
        }
        catch (\Exception $e)
        {
            return Response::json(['success' => false, 'message' => $e]);
        }
    }

}
