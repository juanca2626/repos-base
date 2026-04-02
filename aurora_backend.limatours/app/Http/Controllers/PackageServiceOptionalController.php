<?php

namespace App\Http\Controllers;

use App\PackageServiceOptional;
use App\PackageServiceOptionalRoom;
use App\RatesPlansRooms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class PackageServiceOptionalController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:packages.read')->only('index');
        $this->middleware('permission:packages.create')->only('store');
        $this->middleware('permission:packages.update')->only('update');
        $this->middleware('permission:packages.delete')->only('delete');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function searchByCategory($plan_rate_category_id)
    {
        $package_services = PackageServiceOptional::with([
            'hotel.channel',
            'service_rooms.rate_plan_room.rate_plan',
            'service_rooms.rate_plan_room.calendarys' => function ($query) {
                $query->with('rate');
            },
            'service_rooms.rate_plan_room.room.room_type.translations'
//            'service_rates'
        ])
//            ->with([
//                'service' => function ($query) {
//                    $query->with([
//                        'service_rate.service_rate_plans' => function ($query) {
//                            $query->whereYear('date_from', date('Y'));
//                        }
//                    ]);
//                    $query->with([
//                        'serviceType' => function ($query) {
//                            $query->with([
//                                'translations' => function ($query) {
//                                    $query->select('object_id', 'value');
//                                    $query->where('type', 'servicetype');
//                                    $query->where('language_id', 1);
//                                },
//                            ]);
//                        }
//                    ]);
//                    $query->withCount(['serviceEquivAssociation']);
//                }
//            ])
            ->where('package_plan_rate_category_id', $plan_rate_category_id)
            ->orderBy('date_in')->orderBy('order')->get();

        // price_from  |  price_from_pax
        foreach ($package_services as $p_s) {
            $date_in = $p_s->date_in;
            $date_out = $p_s->date_out;
            if ($p_s->type == "service") {
                foreach ($p_s->service->service_rate as $s_rate) {
                    $s_rate->price_from = '';
                    $s_rate->price_from_pax = '';
                    foreach ($s_rate->service_rate_plans as $s_plan) {
                        if (strtotime($s_plan->date_from) <= strtotime($date_in) &&
                            strtotime($s_plan->date_to) >= strtotime($date_in)) {
                            $s_rate->price_from = $s_plan->price_adult;
                            $s_rate->price_from_pax = $s_plan->pax_from;
                            break;
                        }
                    }
                }
            }
            if ($p_s->type == "hotel") {
                foreach ($p_s->service_rooms as $s_room) {
                    $s_room->rate_plan_room->first_rate = [];
                    $new_calendarys = [];
                    foreach ($s_room->rate_plan_room->calendarys as $calendary) {
                        if (strtotime($calendary->date) <= strtotime($date_out) &&
                            strtotime($calendary->date) >= strtotime($date_in)) {
                            if (count($s_room->rate_plan_room->first_rate) == 0 && count($calendary->rate) > 0) {
                                $s_room->rate_plan_room->first_rate = $calendary->rate;
                            }
                            array_push($new_calendarys, $calendary);
                        }
                    }
                    unset($s_room->rate_plan_room->calendarys);
                    $s_room->rate_plan_room->calendarys_in_dates = $new_calendarys;
                }
            }
        }

        return Response::json(['success' => true, 'data' => $package_services]);
    }

    public function storeHotelRoom($plan_rate_category_id, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'hotel_id' => 'required',
                'rate_plan_room_id' => 'required',
                'date_in' => 'required'
            ]);

            if ($validator->fails()) {
                return Response::json(['success' => false]);
            } else {
                DB::beginTransaction();
                $hotel_id = $request->input('hotel_id');
                $date_in = $request->input('date_in');
                $package_service = PackageServiceOptional::where('package_plan_rate_category_id', $plan_rate_category_id)
                    ->where('type', 'hotel')->where('object_id', $hotel_id)
                    ->where('date_in', $date_in);
                $count = $package_service->count();
                if ($count == 0) {
                    $package_service = new PackageServiceOptional();
                    $package_service->type = 'hotel';
                    $package_service->object_id = $hotel_id;
                    $package_service->package_plan_rate_category_id = $plan_rate_category_id;
                    $package_service->date_in = $date_in;
                    $package_service->date_out = $request->input('date_out');
                    $package_service->save();
                } else {
                    $package_service = $package_service->first();
                }

                $occupation_for_put =
                    $this->get_occupation_by_rate_plan_room_id($request->input('rate_plan_room_id'));

                $package_service_rooms = PackageServiceOptionalRoom::where('package_service_optional_id', $package_service->id)->get();

                $rate_plan_room_ids_deletes = [];
                foreach ($package_service_rooms as $package_service_room) {
                    $occupation =
                        $this->get_occupation_by_rate_plan_room_id($package_service_room->rate_plan_room_id);
                    if ($occupation === $occupation_for_put) {
                        array_push($rate_plan_room_ids_deletes, $package_service_room->rate_plan_room_id);
                        $package_service_room->delete();
                    }
                }

                $package_service_room = new PackageServiceOptionalRoom();
                $package_service_room->package_service_optional_id = $package_service->id;
                $package_service_room->rate_plan_room_id = $request->input('rate_plan_room_id');
                $package_service_room->save();

                DB::commit();
                return Response::json([
                    'success' => true,
                    'object_id' => $package_service_room->id,
                    'rate_plan_room_ids_deletes' => $rate_plan_room_ids_deletes
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

    public function searchHotelsByCategories(Request $request)
    {
        $package_services = PackageServiceOptional::with([
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
        return Response::json(['success' => true, 'data' => $package_services]);
    }

    public function destroy($id)
    {
        $package_service = PackageServiceOptional::find($id);
        if ($package_service->type == 'hotel') {
            $room_services = PackageServiceOptionalRoom::where('package_service_optional_id', $package_service->id)->get();
            foreach ($room_services as $room_service) {
                $room_service->delete();
            }
        }
//        elseif ($package_service->type == 'service') {
//            $service_rates = PackageServiceRate::where('package_service_optional_id', $package_service->id)->get();
//            foreach ($service_rates as $rate) {
//                $rate->delete();
//            }
//        }

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

        $package_service = PackageServiceOptional::find($package_service_hotel_id);

        $find_count_same = PackageServiceOptional::where('package_plan_rate_category_id', $category_id)
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
            $clone_package_service = new PackageServiceOptional();
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
            $clone_package_service->save();

            $find_rooms = PackageServiceOptionalRoom::where('package_service_optional_id', $package_service->id)->get();

            foreach ($find_rooms as $r) {
                $clone_room = new PackageServiceOptionalRoom();
                $clone_room->package_service_optional_id = $clone_package_service->id;
                $clone_room->rate_plan_room_id = $r->rate_plan_room_id;
                $clone_room->save();
            }

            $response = ['success' => true];

        }

        return Response::json($response);
    }

    public function deleteServiceRoom(Request $request)
    {
        $package_service_room = PackageServiceOptionalRoom::find($request->input('service_room_id'));
        $package_service_room->delete();
        return \response()->json(["message"=>"Service Room eliminado"]);
    }

    public function newOrders(Request $request)
    {
        $newOrders = $request->input('newOrders');

        $success = 0;
        foreach ($newOrders as $newOrder) {
            $serv = PackageServiceOptional::find($newOrder['id']);
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
}
