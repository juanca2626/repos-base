<?php

namespace App\Http\Controllers;

use App\Bag;
use App\ClientRatePlan;
use App\DateRangeHotel;
use App\Inventory;
use App\InventoryBag;
use App\ProgressBar;
use App\RatesPlansRooms;
use App\Room;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Spatie\Activitylog\Models\Activity;

class InventoriesController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:inventories.read')->only('index');
        $this->middleware('permission:inventories.create')->only('store');
        $this->middleware('permission:inventories.update')->only('update');
        $this->middleware('permission:inventories.delete')->only('delete');
    }

    public function index(Request $request)
    {
        $hotel_id = $request->input('hotel_id');
        $lang = $request->input('lang');
        $allotment = $request->input('allotment');
        $room_id = $request->input('room_id');
        $rate_plan_id = $request->input('rate_plan_id');
        $client_id = $request->input('client_id');
        $month = $request->input('month');
        $year = $request->input('year');

        $invetories = Inventory::list($hotel_id, $lang, $allotment, $room_id, $rate_plan_id, $client_id, $year, $month);

        return Response::json($invetories);

    }


    public function store(Request $request)
    {
        $hotel_id = $request->input('hotel_id');
        $inventories_frontend = $request->input('inventories_selected');

        date_default_timezone_set("America/Lima");

        DB::transaction(function () use ($inventories_frontend) {

            foreach ($inventories_frontend as $inventory_frontend) {
                if ($inventory_frontend["id"] === null) {
                    if ($inventory_frontend["rate_plan_rooms_id"] != "") {
                        $id = DB::table('inventories')->insertGetId([
                            'day' => $inventory_frontend["day"],
                            'date' => $inventory_frontend["date"],
                            'inventory_num' => $inventory_frontend["inventory_num"],
                            'total_booking' => 0,
                            'total_canceled' => 0,
                            'locked' => $inventory_frontend["locked"],
                            'rate_plan_rooms_id' => $inventory_frontend["rate_plan_rooms_id"],
                            'created_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
                        activity()
                            ->performedOn(Inventory::find($id))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha Creado un Inventario de ' . $inventory_frontend["inventory_num"]);
                    }
                    if ($inventory_frontend["bag_room_id"] != "") {
                        $id = DB::table('inventory_bags')->insertGetId([
                            'day' => $inventory_frontend["day"],
                            'date' => $inventory_frontend["date"],
                            'inventory_num' => $inventory_frontend["inventory_num"],
                            'total_booking' => 0,
                            'total_canceled' => 0,
                            'locked' => $inventory_frontend["locked"],
                            'bag_room_id' => $inventory_frontend["bag_room_id"],
                            'created_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
                        activity()
                            ->performedOn(InventoryBag::find($id))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha Creado un Inventario de ' . $inventory_frontend["inventory_num"]);
                    }
                } else {
                    if ($inventory_frontend["rate_plan_rooms_id"] != "") {
                        $inventory_database = DB::table('inventories')->where('id', $inventory_frontend["id"])->first();

                        activity()
                            ->performedOn(Inventory::find($inventory_frontend["id"]))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha Actualizado un Inventario de ' . $inventory_database->inventory_num . ' a ' . $inventory_frontend["inventory_num"]);

                        DB::table('inventories')
                            ->where('id', $inventory_frontend["id"])
                            ->update(['inventory_num' => $inventory_frontend["inventory_num"]]);
                    }
                    if ($inventory_frontend["bag_room_id"] != "") {
                        $inventory_database = DB::table('inventory_bags')->where(
                            'id',
                            $inventory_frontend["id"]
                        )
                            ->first();

                        activity()
                            ->performedOn(InventoryBag::find($inventory_frontend["id"]))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha Actualizado un Inventario de ' . $inventory_database->inventory_num . ' a ' . $inventory_frontend["inventory_num"]);

                        DB::table('inventory_bags')
                            ->where('id', $inventory_frontend["id"])
                            ->update(['inventory_num' => $inventory_frontend["inventory_num"]]);
                    }
                }
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'room_progress_inventories',
                        'value' => 10,
                        'type' => 'room',
                        'object_id' => $inventory_frontend["room_id"]
                    ]
                );
            }
        });

        ProgressBar::updateOrCreate(
            [
                'slug' => 'hotel_progress_inventories',
                'value' => 10,
                'type' => 'hotel',
                'object_id' => $hotel_id
            ]
        );
        return Response::json(['success' => true]);
    }

    public function lockedDays(Request $request)
    {
        $inventories_frontend = $request->input('inventories_selected');

        date_default_timezone_set("America/Lima");

        DB::transaction(function () use ($inventories_frontend) {

            foreach ($inventories_frontend as $inventory_frontend) {
                if ($inventory_frontend["id"] === null) {
                    if ($inventory_frontend["rate_plan_rooms_id"] != "") {
                        $id = DB::table('inventories')->insertGetId([
                            'day' => $inventory_frontend["day"],
                            'date' => $inventory_frontend["date"],
                            'inventory_num' => 0,
                            'total_booking' => 0,
                            'total_canceled' => 0,
                            'locked' => true,
                            'rate_plan_rooms_id' => $inventory_frontend["rate_plan_rooms_id"],
                            'created_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
                        activity()
                            ->performedOn(Inventory::find($id))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha bloqueado un día ' . $inventory_frontend["date"]);
                    }
                    if ($inventory_frontend["bag_room_id"] != "") {
                        $id = DB::table('inventory_bags')->insertGetId([
                            'day' => $inventory_frontend["day"],
                            'date' => $inventory_frontend["date"],
                            'inventory_num' => 0,
                            'total_booking' => 0,
                            'total_canceled' => 0,
                            'locked' => true,
                            'bag_room_id' => $inventory_frontend["bag_room_id"],
                            'created_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
                        activity()
                            ->performedOn(InventoryBag::find($id))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha bloqueado un día ' . $inventory_frontend["date"]);
                    }
                } else {
                    if ($inventory_frontend["rate_plan_rooms_id"] != "") {
                        activity()
                            ->performedOn(Inventory::find($inventory_frontend["id"]))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha bloqueado un día ' . $inventory_frontend["date"]);

                        DB::table('inventories')
                            ->where('id', $inventory_frontend["id"])
                            ->update(['locked' => true]);
                    }
                    if ($inventory_frontend["bag_room_id"] != "") {
                        activity()
                            ->performedOn(InventoryBag::find($inventory_frontend["id"]))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha bloqueado un día ' . $inventory_frontend["date"]);

                        DB::table('inventory_bags')
                            ->where('id', $inventory_frontend["id"])
                            ->update(['locked' => true]);
                    }
                }
            }
        });
        return Response::json(['success' => true]);
    }

    public function enabledDays(Request $request)
    {
        $inventories_frontend = $request->input('inventories_selected');

        date_default_timezone_set("America/Lima");

        DB::transaction(function () use ($inventories_frontend) {
            foreach ($inventories_frontend as $inventory_frontend) {
                if ($inventory_frontend["id"] === null) {
                    if ($inventory_frontend["rate_plan_rooms_id"] != "") {
                        $id = DB::table('inventories')->insertGetId([
                            'day' => $inventory_frontend["day"],
                            'date' => $inventory_frontend["date"],
                            'inventory_num' => 0,
                            'total_booking' => 0,
                            'total_canceled' => 0,
                            'locked' => false,
                            'rate_plan_rooms_id' => $inventory_frontend["rate_plan_rooms_id"],
                            'created_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
                        activity()
                            ->performedOn(Inventory::find($id))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha desbloqueado un día ' . $inventory_frontend["date"]);
                    }
                    if ($inventory_frontend["bag_room_id"] != "") {
                        $id = DB::table('inventory_bags')->insertGetId([
                            'day' => $inventory_frontend["day"],
                            'date' => $inventory_frontend["date"],
                            'inventory_num' => 0,
                            'total_booking' => 0,
                            'total_canceled' => 0,
                            'locked' => false,
                            'bag_room_id' => $inventory_frontend["bag_room_id"],
                            'created_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
                        activity()
                            ->performedOn(InventoryBag::find($id))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha desbloqueado un día ' . $inventory_frontend["date"]);
                    }
                } else {
                    if ($inventory_frontend["rate_plan_rooms_id"] != "") {
                        activity()
                            ->performedOn(Inventory::find($inventory_frontend["id"]))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha desbloqueado un día ' . $inventory_frontend["date"]);

                        DB::table('inventories')
                            ->where('id', $inventory_frontend["id"])
                            ->update(['locked' => false]);
                    }
                    if ($inventory_frontend["bag_room_id"] != "") {
                        activity()
                            ->performedOn(InventoryBag::find($inventory_frontend["id"]))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha desbloqueado un día ' . $inventory_frontend["date"]);

                        DB::table('inventory_bags')
                            ->where('id', $inventory_frontend["id"])
                            ->update(['locked' => false]);
                    }
                }
            }
        });

        return Response::json(['success' => true]);
    }

    public function storeInventoryByDateRange(Request $request)
    {
        $hotel_id = $request->input('hotel_id');

        $date_from = Carbon::createFromFormat('d/m/Y', $request->input('dates_from'))->setTimezone('America/Lima');
        $date_to = Carbon::createFromFormat('d/m/Y', $request->input('dates_to'))->setTimezone('America/Lima');
        $rate_selected = $request->input('rate_selected');
        $days = $request->input('days');
        $availability = $request->input('availability');
        $rate_plan_rooms_id = $rate_selected["rate_plan_rooms_id"];
        $bag_room_id = $rate_selected["bag_room_id"];

        $difference_days = $date_from->diffInDays($date_to);
        $inventories_exists = [];
        $inventories_bags_exists = [];

        if ($rate_selected["rate_plan_rooms_id"] != "") {
            $inventories_exists = Inventory::whereBetween('date', [
                $date_from->format('Y-m-d'),
                $date_to->format('Y-m-d')
            ])
                ->where('rate_plan_rooms_id', $rate_selected["rate_plan_rooms_id"])
                ->get();
        }
        if ($rate_selected["bag_room_id"] != "") {
            $inventories_bags_exists = InventoryBag::whereBetween('date', [
                $date_from->format('Y-m-d'),
                $date_to->format('Y-m-d')
            ])
                ->where('bag_room_id', $bag_room_id)->get();
        }
        DB::transaction(function () use (
            $bag_room_id,
            $rate_plan_rooms_id,
            $inventories_exists,
            $inventories_bags_exists,
            $rate_selected,
            $difference_days,
            $days,
            $availability,
            $date_from,
            $hotel_id
        ) {
            for ($i = 0; $i <= $difference_days; $i++) {
                if ($i === 0) {
                    $valid_day = $this->checkSelectDay($days, $date_from->dayOfWeek);
                    if ($valid_day) {
                        date_default_timezone_set("America/Lima");

                        //ocurre para todas las tarifas asociadas a una bolsa
                        if ($rate_selected["bag_room_id"] != "") {
                            $inventory_bag = $this->verifyExistInventoryBagInDate(
                                $inventories_bags_exists,
                                $date_from->format('Y-m-d')
                            );
                            if (gettype($inventory_bag) != "NULL") {
                                $inventory_database = DB::table('inventory_bags')
                                    ->where('id', $inventory_bag["id"])
                                    ->first();

                                activity()
                                    ->performedOn(InventoryBag::find($inventory_bag["id"]))
                                    ->causedBy(Auth::user())
                                    ->withProperties(['date_inventory' => $inventory_bag["date"]])
                                    ->log('El usuario ' . Auth::user()->name . ' ha Actualizado un Inventario de ' . $inventory_database->inventory_num . ' a ' . $availability);

                                DB::table('inventory_bags')
                                    ->where('id', $inventory_bag["id"])
                                    ->update(['inventory_num' => $availability]);
                            } else {
                                $id = DB::table('inventory_bags')->insertGetId([
                                    'day' => $date_from->day,
                                    'date' => $date_from->format('Y-m-d'),
                                    'inventory_num' => $availability,
                                    'total_booking' => 0,
                                    'total_canceled' => 0,
                                    'locked' => false,
                                    'bag_room_id' => $rate_selected["bag_room_id"],
                                    'created_at' => date("Y-m-d H:i:s"),
                                    'updated_at' => date("Y-m-d H:i:s")
                                ]);
                                activity()
                                    ->performedOn(InventoryBag::find($id))
                                    ->causedBy(Auth::user())
                                    ->withProperties(['date_inventory' => $date_from->format('Y-m-d')])
                                    ->log('El usuario ' . Auth::user()->name . ' ha Creado un Inventario de ' . $availability);
                            }
                        }
                        //ocurre para las tarifas que no estan asociadas a una bolsa
                        if ($rate_selected["rate_plan_rooms_id"] != "") {
                            $inventory = $this->verifyExistInventoryInDate(
                                $inventories_exists,
                                $date_from->format('Y-m-d')
                            );
                            if (gettype($inventory) != "NULL") {
                                $inventory_database = DB::table('inventories')->where('id', $inventory["id"])->first();

                                activity()
                                    ->performedOn(Inventory::find($inventory["id"]))
                                    ->causedBy(Auth::user())
                                    ->withProperties(['date_inventory' => $inventory["date"]])
                                    ->log('El usuario ' . Auth::user()->name . ' ha Actualizado un Inventario de ' . $inventory_database->inventory_num . ' a ' . $availability);

                                DB::table('inventories')
                                    ->where('id', $inventory["id"])
                                    ->update(['inventory_num' => $availability]);
                            } else {
                                $id = DB::table('inventories')->insertGetId([
                                    'day' => $date_from->day,
                                    'date' => $date_from->format('Y-m-d'),
                                    'inventory_num' => $availability,
                                    'total_booking' => 0,
                                    'total_canceled' => 0,
                                    'locked' => false,
                                    'rate_plan_rooms_id' => $rate_selected["rate_plan_rooms_id"],
                                    'created_at' => date("Y-m-d H:i:s"),
                                    'updated_at' => date("Y-m-d H:i:s")
                                ]);
                                activity()
                                    ->performedOn(Inventory::find($id))
                                    ->causedBy(Auth::user())
                                    ->withProperties(['date_inventory' => $date_from->format('Y-m-d')])
                                    ->log('El usuario ' . Auth::user()->name . ' ha Creado un Inventario de ' . $availability);
                            }
                        }
                    }
                } else {
                    $date_add_day = $date_from->addDay();

                    $valid_day = $this->checkSelectDay($days, $date_add_day->dayOfWeek);
                    if ($valid_day) {
                        //ocurre cuando la tarifa esta asociada a una bolsa
                        if ($rate_selected["bag_room_id"] != "") {
                            $inventory_bag = $this->verifyExistInventoryBagInDate(
                                $inventories_bags_exists,
                                $date_add_day->format('Y-m-d')
                            );

                            if (gettype($inventory_bag) != "NULL") {
                                $inventory_database = DB::table('inventory_bags')
                                    ->where('id', $inventory_bag["id"])
                                    ->first();
                                activity()
                                    ->performedOn(InventoryBag::find($inventory_bag["id"]))
                                    ->causedBy(Auth::user())
                                    ->withProperties(['date_inventory' => $inventory_bag->date])
                                    ->log('El usuario ' . Auth::user()->name . ' ha Actualizado un Inventario de ' . $inventory_database->inventory_num . ' a ' . $availability);
                                DB::table('inventory_bags')
                                    ->where('id', $inventory_bag["id"])
                                    ->update(['inventory_num' => $availability]);
                            } else {
                                $id = DB::table('inventory_bags')->insertGetId([
                                    'day' => $date_add_day->day,
                                    'date' => $date_add_day->format('Y-m-d'),
                                    'inventory_num' => $availability,
                                    'total_booking' => 0,
                                    'total_canceled' => 0,
                                    'locked' => false,
                                    'bag_room_id' => $rate_selected["bag_room_id"],
                                    'created_at' => date("Y-m-d H:i:s"),
                                    'updated_at' => date("Y-m-d H:i:s")
                                ]);
                                activity()
                                    ->performedOn(InventoryBag::find($id))
                                    ->causedBy(Auth::user())
                                    ->withProperties(['date_inventory' => $date_from->format('Y-m-d')])
                                    ->log('El usuario ' . Auth::user()->name . ' ha Creado un Inventario de ' . $availability);
                            }
                        }
                        //ocurre para las tarifas que no estan asociadas a una bolsa
                        if ($rate_selected["rate_plan_rooms_id"] != "") {
                            $inventory = $this->verifyExistInventoryInDate(
                                $inventories_exists,
                                $date_add_day->format('Y-m-d')
                            );
                            if (gettype($inventory) != "NULL") {
                                $inventory_database = DB::table('inventories')
                                    ->where('id', $inventory["id"])
                                    ->first();

                                activity()
                                    ->performedOn(Inventory::find($inventory["id"]))
                                    ->causedBy(Auth::user())
                                    ->withProperties(['date_inventory' => $inventory["date"]])
                                    ->log('El usuario ' . Auth::user()->name . ' ha Actualizado un Inventario de ' . $inventory_database->inventory_num . ' a ' . $availability);

                                DB::table('inventories')
                                    ->where('id', $inventory["id"])
                                    ->update(['inventory_num' => $availability]);
                            } else {
                                $id = DB::table('inventories')->insertGetId([
                                    'day' => $date_add_day->day,
                                    'date' => $date_add_day->format('Y-m-d'),
                                    'inventory_num' => $availability,
                                    'total_booking' => 0,
                                    'total_canceled' => 0,
                                    'locked' => false,
                                    'rate_plan_rooms_id' => $rate_selected["rate_plan_rooms_id"],
                                    'created_at' => date("Y-m-d H:i:s"),
                                    'updated_at' => date("Y-m-d H:i:s")
                                ]);
                                activity()
                                    ->performedOn(Inventory::find($id))
                                    ->causedBy(Auth::user())
                                    ->withProperties(['date_inventory' => $date_add_day->format('Y-m-d')])
                                    ->log('El usuario ' . Auth::user()->name . ' ha Creado un Inventario de ' . $availability);
                            }
                        }
                    }
                }
            }
            ProgressBar::updateOrCreate(
                [
                    'slug' => 'hotel_progress_inventories',
                    'value' => 10,
                    'type' => 'hotel',
                    'object_id' => $hotel_id
                ]
            );
        });
        return Response::json(['success' => true]);
    }

    public function blockedInventoryByDateRange(Request $request)
    {
        $date_from = Carbon::createFromFormat('d/m/Y', $request->input('dates_from'))->setTimezone('America/Lima');
        $date_to = Carbon::createFromFormat('d/m/Y', $request->input('dates_to'))->setTimezone('America/Lima');
        $rate_selected = $request->input('rate_selected');
        $days = $request->input('days');
        $rate_plan_rooms_id = $rate_selected["rate_plan_rooms_id"];
        $bag_room_id = $rate_selected["bag_room_id"];

        if ($request->input('locked') == 1) {
            $locked = true;
            $message = "bloqueado";
        } else {
            $locked = false;
            $message = "desbloqueado";
        }
        $difference_days = $date_from->diffInDays($date_to);

        $inventories_exists = [];
        $inventories_bag_exists = [];


        if ($rate_selected["rate_plan_rooms_id"] != "") {
            $inventories_exists = Inventory::whereBetween(
                'date',
                [
                    $date_from->format('Y-m-d'),
                    $date_to->format('Y-m-d')
                ]
            )
                ->where('rate_plan_rooms_id', $rate_selected["rate_plan_rooms_id"])
                ->get();
        }
        if ($rate_selected["bag_room_id"] != "") {
            $inventories_bag_exists = InventoryBag::whereBetween(
                'date',
                [
                    $date_from->format('Y-m-d'),
                    $date_to->format('Y-m-d')
                ]
            )
                ->where('bag_room_id', $bag_room_id)
                ->get();
        }
        DB::transaction(function () use (
            $inventories_exists,
            $inventories_bag_exists,
            $rate_selected,
            $difference_days,
            $days,
            $locked,
            $date_from,
            $message,
            $rate_plan_rooms_id
        ) {
            for ($i = 0; $i <= $difference_days; $i++) {
                if ($i === 0) {
                    $valid_day = $this->checkSelectDay($days, $date_from->dayOfWeek);
                    if ($valid_day) {
                        //ocurre cuando una tarifa esta en una bolsa
                        if ($rate_selected["bag_room_id"] != "") {
                            $inventory = $this->verifyExistInventoryBagInDate(
                                $inventories_bag_exists,
                                $date_from->format('Y-m-d')
                            );
                            if (gettype($inventory) != "NULL") {
                                activity()
                                    ->performedOn(InventoryBag::find($inventory["id"]))
                                    ->causedBy(Auth::user())
                                    ->withProperties(['date_inventory' => $inventory["date"]])
                                    ->log('El usuario ' . Auth::user()->name . ' ha ' . $message . ' un día ' . $inventory["date"]);

                                DB::table('inventory_bags')
                                    ->where('id', $inventory["id"])
                                    ->update(['locked' => $locked]);
                            } else {
                                $id = DB::table('inventory_bags')->insertGetId([
                                    'day' => $date_from->day,
                                    'date' => $date_from->format('Y-m-d'),
                                    'inventory_num' => 0,
                                    'total_booking' => 0,
                                    'total_canceled' => 0,
                                    'locked' => $locked,
                                    'bag_room_id' => $rate_selected["bag_room_id"],
                                    'created_at' => date("Y-m-d H:i:s"),
                                    'updated_at' => date("Y-m-d H:i:s")
                                ]);
                                $inventory_bag = Inventory::find($id);
                                if ($inventory_bag) {
                                    activity()
                                        ->performedOn()
                                        ->causedBy(Auth::user())
                                        ->withProperties(['date_inventory' => $date_from->format('Y-m-d')])
                                        ->log('El usuario ' . Auth::user()->name . ' ha ' . $message . ' un dia ' . $date_from->format('Y-m-d'));
                                }

                            }
                        }
                        //ocurre cuando una tarifa NO esta en una bolsa
                        if ($rate_selected["rate_plan_rooms_id"] != "") {
                            $inventory = $this->verifyExistInventoryInDate(
                                $inventories_exists,
                                $date_from->format('Y-m-d')
                            );
                            if (gettype($inventory) != "NULL") {
                                activity()
                                    ->performedOn(Inventory::find($inventory["id"]))
                                    ->causedBy(Auth::user())
                                    ->withProperties(['date_inventory' => $inventory["date"]])
                                    ->log('El usuario ' . Auth::user()->name . ' ha ' . $message . ' un día ' . $inventory["date"]);

                                DB::table('inventories')
                                    ->where('id', $inventory["id"])
                                    ->update(['locked' => $locked]);
                            } else {
                                $id = DB::table('inventories')->insertGetId([
                                    'day' => $date_from->day,
                                    'date' => $date_from->format('Y-m-d'),
                                    'inventory_num' => 0,
                                    'total_booking' => 0,
                                    'total_canceled' => 0,
                                    'locked' => $locked,
                                    'rate_plan_rooms_id' => $rate_selected["rate_plan_rooms_id"],
                                    'created_at' => date("Y-m-d H:i:s"),
                                    'updated_at' => date("Y-m-d H:i:s")
                                ]);

                                activity()
                                    ->performedOn(Inventory::find($id))
                                    ->causedBy(Auth::user())
                                    ->withProperties(['date_inventory' => $date_from->format('Y-m-d')])
                                    ->log('El usuario ' . Auth::user()->name . ' ha ' . $message . ' un dia ' . $date_from->format('Y-m-d'));
                            }
                        }
                    }
                } else {
                    $date_add_day = $date_from->addDay();

                    $valid_day = $this->checkSelectDay($days, $date_add_day->dayOfWeek);
                    if ($valid_day) {
                        //ocurre cuando una tarifa esta en una bolsa
                        if ($rate_selected["bag_room_id"] != "") {
                            $inventory = $this->verifyExistInventoryBagInDate(
                                $inventories_bag_exists,
                                $date_add_day->format('Y-m-d')
                            );

                            if (gettype($inventory) != "NULL") {
                                activity()
                                    ->performedOn(InventoryBag::find($inventory["id"]))
                                    ->causedBy(Auth::user())
                                    ->withProperties(['date_inventory' => $inventory["date"]])
                                    ->log('El usuario ' . Auth::user()->name . ' ha ' . $message . ' un día ' . $inventory["date"]);

                                DB::table('inventory_bags')
                                    ->where('id', $inventory["id"])
                                    ->update(['locked' => $locked]);
                            } else {
                                $id = DB::table('inventory_bags')->insertGetId([
                                    'day' => $date_add_day->day,
                                    'date' => $date_add_day->format('Y-m-d'),
                                    'inventory_num' => 0,
                                    'total_booking' => 0,
                                    'total_canceled' => 0,
                                    'locked' => $locked,
                                    'bag_room_id' => $rate_selected["bag_room_id"],
                                    'created_at' => date("Y-m-d H:i:s"),
                                    'updated_at' => date("Y-m-d H:i:s")
                                ]);

                                activity()
                                    ->performedOn(InventoryBag::find($id))
                                    ->causedBy(Auth::user())
                                    ->withProperties(['date_inventory' => $date_add_day->format('Y-m-d')])
                                    ->log('El usuario ' . Auth::user()->name . ' ha ' . $message . ' un dia ' . $date_add_day->format('Y-m-d'));
                            }
                        }
                        //ocurre cuando una tarifa NO esta en una bolsa
                        if ($rate_selected["rate_plan_rooms_id"] != "") {
                            $inventory = $this->verifyExistInventoryInDate(
                                $inventories_exists,
                                $date_add_day->format('Y-m-d')
                            );

                            if (gettype($inventory) != "NULL") {
                                activity()
                                    ->performedOn(Inventory::find($inventory["id"]))
                                    ->causedBy(Auth::user())
                                    ->withProperties(['date_inventory' => $inventory["date"]])
                                    ->log('El usuario ' . Auth::user()->name . ' ha ' . $message . ' un día ' . $inventory["date"]);

                                DB::table('inventories')
                                    ->where('id', $inventory["id"])
                                    ->update(['locked' => $locked]);
                            } else {
                                $id = DB::table('inventories')->insertGetId([
                                    'day' => $date_add_day->day,
                                    'date' => $date_add_day->format('Y-m-d'),
                                    'inventory_num' => 0,
                                    'total_booking' => 0,
                                    'total_canceled' => 0,
                                    'locked' => $locked,
                                    'rate_plan_rooms_id' => $rate_selected["rate_plan_rooms_id"],
                                    'created_at' => date("Y-m-d H:i:s"),
                                    'updated_at' => date("Y-m-d H:i:s")
                                ]);

                                activity()
                                    ->performedOn(Inventory::find($id))
                                    ->causedBy(Auth::user())
                                    ->withProperties(['date_inventory' => $date_add_day->format('Y-m-d')])
                                    ->log('El usuario ' . Auth::user()->name . ' ha ' . $message . ' un dia ' . $date_add_day->format('Y-m-d'));
                            }
                        }
                    }
                }
            }
        });
        return Response::json(['success' => true]);
    }

    public function history(Request $request)
    {

        $inventory_id = $request->input('inventory_id');
        $bag_room_id = $request->input('bag_room_id');
        $rate_plan_rooms_id = $request->input('rate_plan_rooms_id');
        $lastActivity = null;

        if ($rate_plan_rooms_id != "") {

            $lastActivity = Activity::where('subject_id', $inventory_id)->where('subject_type',
                Inventory::class)->orderBy('created_at', 'desc')->get();
        }
        if ($bag_room_id != "") {
            $lastActivity = Activity::where('subject_id', $inventory_id)->where('subject_type',
                InventoryBag::class)->orderBy('created_at', 'desc')->get();
        }
        return Response::json(['success' => true, "data" => $lastActivity]);
    }

    public function inventoryByChannels(Request $request): JsonResponse
    {
        $hotel_id = $request->input('hotel_id');
        $lang = $request->input('lang');
        $allotment = $request->input('allotment');
        $room_id = $request->input('room_id');
        $rate_plan_id = $request->input('rate_plan_id');
        $channel_id = $request->input('channel_id');
        $year = $request->input('year');
        $month = $request->input('month');

        $dateInit = "$year-$month-01";

        $start_date = Carbon::parse($dateInit)->startOfMonth();
        $end_date = Carbon::parse($dateInit)->endOfMonth();
        $days = $end_date->day;

        $startDate = $start_date->toDateString();
        $endDate = $end_date->toDateString();

        $rate_plan_rooms = RatesPlansRooms::with([
            'room.translations' => function ($query) use ($lang) {
                $query->where('type', 'room')->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            },
            'calendarys' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            },
            'rate_plan'
        ])->whereHas('room', function ($query) use ($hotel_id, $room_id) {
            $query->where('hotel_id', $hotel_id);
            if ($room_id) {
                $query->where('room_id', $room_id);
            }
        })->whereHas('rate_plan', function ($query) use ($allotment) {
            $query->where('allotment', 1);
        })->where('bag', 0)
            ->where('channel_id', $channel_id)
            ->where('status', 1);

        if ($rate_plan_id) {
            $rate_plan_rooms->where('rates_plans_id', $rate_plan_id);
        }

        $rate_plan_rooms = $rate_plan_rooms->get();

        $inventories_exists = Inventory::whereBetween('date', [$startDate, $endDate])->get()->groupBy('rate_plan_rooms_id');

        $inventories = [];
        foreach ($rate_plan_rooms as $roomIndex => $rate_plan_room) {
            $translations = $rate_plan_room->room->translations;
            $rateName = (count($translations) > 0) ? $translations[0]->value . ' ' . $rate_plan_room->rate_plan->name : 'N/A';

            $inventories[$roomIndex] = ["rate_name" => $rateName];
            $calendarByDate = $rate_plan_room->calendarys->keyBy('date');
            $currentDate = $start_date->copy();

            for ($day = 1; $day <= $days; $day++) {
                $dateStr = $currentDate->toDateString();
                $inventory = $this->checkExists(
                    $inventories_exists,
                    $rate_plan_room->id,
                    $day
                );

                $calendar = isset($calendarByDate[$dateStr]) ? $calendarByDate[$dateStr] : null;

                $restrictionFields = [
                    'restriction_status' => $calendar ? $calendar->restriction_status : 0,
                    'restriction_arrival' => $calendar ? $calendar->restriction_arrival : 0,
                    'restriction_departure' => $calendar ? $calendar->restriction_departure : 0,
                ];

                // Validar si la fecha de restricción de salida está cerrada
                $isDepartureClosed = $restrictionFields['restriction_departure'] == 0; // 0 = cerrado, 1 = abierto

                $baseData = [
                    'day' => $day,
                    'bag_room_id' => '',
                    'room_id' => $rate_plan_room->room_id,
                    'rate_plan_rooms_id' => $rate_plan_room->id,
                    'date' => $dateStr,
                    'selected' => false,
                    'class_selected' => false,
                    'class_intermediate' => false,
                ];

                if ($inventory === null) {
                    $inventories[$roomIndex]['inventory'][$day] = array_merge($baseData, [
                        'id' => '',
                        'inventory_num' => '',
                        'locked' => false,
                        'class_locked' => false,
                        'class_departure_closed' => false,
                        'class_normal' => true,
                    ], $restrictionFields);
                } else {
                    $locked = $inventory['locked'] == 1;
                    $inventories[$roomIndex]['inventory'][$day] = array_merge($baseData, [
                        'id' => $inventory['id'],
                        'inventory_num' => $inventory['inventory_num'],
                        'locked' => $locked,
                        'class_locked' => $locked,
                        'class_departure_closed' => $isDepartureClosed,
                        'class_normal' => !$locked && !$isDepartureClosed,
                    ], $restrictionFields);
                }

                $currentDate->addDay();
            }
        }

        return Response::json([
            'success' => true,
            'inventories' => $inventories,
            'days' => $days
        ]);
    }

    private function checkExists($inventories_exists_group, $rate_plan_room_id, $day)
    {
        foreach ($inventories_exists_group as $key => $inventory_of_rate) {
            if ($key == $rate_plan_room_id) {
                foreach ($inventory_of_rate as $inventory_day) {
                    if ($inventory_day["day"] == $day) {
                        return $inventory_day;
                    }
                }
            }
        }

        return null;
    }


    private function checkSelectDay($days, $date_of_week)
    {
        foreach ($days as $day) {
            if ($day["day"] === $date_of_week && $day["selected"] === true) {
                return true;
            }
        }
    }

    private function verifyExistInventoryBagInDate($inventories_bag, $date)
    {
        foreach ($inventories_bag as $inventory) {
            if ($inventory["date"] === $date) {
                return $inventory;
            }
        }
    }

    private function verifyExistInventoryInDate($inventories, $date)
    {
        foreach ($inventories as $inventory) {
            if ($inventory["date"] === $date) {
                return $inventory;
            }
        }
    }


}
