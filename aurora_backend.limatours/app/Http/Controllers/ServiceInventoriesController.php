<?php

namespace App\Http\Controllers;

use App\ProgressBar;
use App\ServiceInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ServiceInventoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $service_rate_id = $request->input('service_rate_id');
        $month = $request->input('month');
        $year = $request->input('year');

        $inventories = [];
        $fecha_carbon = Carbon::parse($year . "-" . $month . "-01");
        $days = cal_days_in_month(CAL_GREGORIAN, $fecha_carbon->month, $fecha_carbon->year);

        $serviceInventory = ServiceInventory::whereBetween('date', [
            $year . "-" . $month . "-01",
            $year . "-" . $month . "-" . $days
        ])->where('service_rate_id', $service_rate_id)->get();

        $index = 0;
        for ($i = 1; $i <= $days; $i++) {
            $day_exists = $this->checkDayExists($serviceInventory, $i);
            $date = ($i === 1) ? $fecha_carbon->format('Y-m-d') : $fecha_carbon->addDay()->format('Y-m-d');
            if ($day_exists === null) {
                $inventories[$index]["day"] = $i;
                $inventories[$index]["id"] = '';
                $inventories[$index]["date"] = $date;
                $inventories[$index]["service_rate_id"] = $service_rate_id;
                $inventories[$index]["inventory_num"] = 0;
                $inventories[$index]["total_booking"] = 0;
                $inventories[$index]["locked"] = false;
                $inventories[$index]["class_selected"] = false;
                $inventories[$index]["class_locked"] = false;
                $inventories[$index]["selected"] = false;
                $inventories[$index]["class_intermediate"] = false;
            } else {
                $inventories[$index]["day"] = $serviceInventory[$day_exists]->day;
                $inventories[$index]["id"] = $serviceInventory[$day_exists]->id;
                $inventories[$index]["date"] = $serviceInventory[$day_exists]->date;
                $inventories[$index]["service_rate_id"] = $serviceInventory[$day_exists]->service_rate_id;
                $inventories[$index]["inventory_num"] = $serviceInventory[$day_exists]->inventory_num;
                $inventories[$index]["total_booking"] = $serviceInventory[$day_exists]->total_booking;
                $inventories[$index]["locked"] = (boolean)$serviceInventory[$day_exists]->locked;
                $inventories[$index]["class_selected"] = false;
                $inventories[$index]["class_locked"] = (boolean)$serviceInventory[$day_exists]->locked;;
                $inventories[$index]["selected"] = false;
                $inventories[$index]["class_intermediate"] = false;

            }
            $index++;
        }


        return Response::json([
            'success' => true,
            'inventories' => $inventories,
            "days" => $days
        ]);
    }

    private function checkDayExists($inventories_exists, $day)
    {
        foreach ($inventories_exists as $key => $inventory_day) {
            if ($inventory_day["day"] == $day) {
                return $key;
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $service_id = $request->input('service_id');
        $service_rate_id = $request->input('service_rate_id');
        $inventories_frontend = $request->input('inventories_selected');
        date_default_timezone_set("America/Lima");
        DB::transaction(function () use ($inventories_frontend) {

            foreach ($inventories_frontend as $inventory_frontend) {
                if ($inventory_frontend["id"] === null) {
                    $inventory = new ServiceInventory();
                    $inventory->day = $inventory_frontend["day"];
                    $inventory->date = $inventory_frontend["date"];
                    $inventory->inventory_num = $inventory_frontend["inventory_num"];
                    $inventory->total_booking = 0;
                    $inventory->total_canceled = 0;
                    $inventory->locked = (int)$inventory_frontend["locked"];
                    $inventory->service_rate_id = $inventory_frontend["service_rate_id"];
                    $inventory->save();
//                    $id = DB::table('service_inventories')->insertGetId([
//                        'day' => $inventory_frontend["day"],
//                        'date' => $inventory_frontend["date"],
//                        'inventory_num' => $inventory_frontend["inventory_num"],
//                        'total_booking' => 0,
//                        'total_canceled' => 0,
//                        'locked' => $inventory_frontend["locked"],
//                        'service_rate_id' => $inventory_frontend["service_rate_id"],
//                        'created_at' => date("Y-m-d H:i:s"),
//                        'updated_at' => date("Y-m-d H:i:s")
//                    ]);

                    activity()
                        ->performedOn(ServiceInventory::find($inventory->id))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('El usuario ' . Auth::user()->name . ' ha Creado un Inventario de '
                            . $inventory_frontend["inventory_num"]);

                } else {
                    $inventory_database = DB::table('service_inventories')->where('id',
                        $inventory_frontend["id"])->first();
                    activity()
                        ->performedOn(ServiceInventory::find($inventory_frontend["id"]))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('El usuario ' . Auth::user()->name . ' ha Actualizado un Inventario de '
                            . $inventory_database->inventory_num . ' a ' . $inventory_frontend["inventory_num"]);
                    $inventory = ServiceInventory::find($inventory_frontend["id"]);
                    $inventory->inventory_num = $inventory_frontend["inventory_num"];
                    $inventory->save();
//                    DB::table('service_inventories')
//                        ->where('id', $inventory_frontend["id"])
//                        ->update(['inventory_num' => $inventory_frontend["inventory_num"]]);
                }

            }
        });

        ProgressBar::updateOrCreate(
            [
                'slug' => 'service_progress_availability',
                'value' => 10,
                'type' => 'service',
                'object_id' => $service_id
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
                    $inventory = new ServiceInventory();
                    $inventory->day = $inventory_frontend["day"];
                    $inventory->date = $inventory_frontend["date"];
                    $inventory->inventory_num = 0;
                    $inventory->total_booking = 0;
                    $inventory->total_canceled = 0;
                    $inventory->locked = 1;
                    $inventory->service_rate_id = $inventory_frontend["service_rate_id"];
                    $inventory->save();

//                    $id = DB::table('service_inventories')->insertGetId([
//                        'day' => $inventory_frontend["day"],
//                        'date' => $inventory_frontend["date"],
//                        'inventory_num' => 0,
//                        'total_booking' => 0,
//                        'total_canceled' => 0,
//                        'locked' => true,
//                        'service_rate_id' => $inventory_frontend["service_rate_id"],
//                        'created_at' => date("Y-m-d H:i:s"),
//                        'updated_at' => date("Y-m-d H:i:s")
//                    ]);
                    activity()
                        ->performedOn(ServiceInventory::find($inventory->id))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('El usuario ' . Auth::user()->name . ' ha bloqueado un día ' . $inventory_frontend["date"]);
                } else {
                    activity()
                        ->performedOn(ServiceInventory::find($inventory_frontend["id"]))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('El usuario ' . Auth::user()->name . ' ha bloqueado un día ' . $inventory_frontend["date"]);
                    $inventory = ServiceInventory::find($inventory_frontend["id"]);
                    $inventory->locked = 1;
                    $inventory->save();
//                    DB::table('service_inventories')
//                        ->where('id', $inventory_frontend["id"])
//                        ->update(['locked' => true]);
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
                    $inventory = new ServiceInventory();
                    $inventory->day = $inventory_frontend["day"];
                    $inventory->date = $inventory_frontend["date"];
                    $inventory->inventory_num = 0;
                    $inventory->total_booking = 0;
                    $inventory->total_canceled = 0;
                    $inventory->locked = 0;
                    $inventory->service_rate_id = $inventory_frontend["service_rate_id"];
                    $inventory->save();
//                    $id = DB::table('service_inventories')->insertGetId([
//                        'day' => $inventory_frontend["day"],
//                        'date' => $inventory_frontend["date"],
//                        'inventory_num' => 0,
//                        'total_booking' => 0,
//                        'total_canceled' => 0,
//                        'locked' => false,
//                        'service_rate_id' => $inventory_frontend["service_rate_id"],
//                        'created_at' => date("Y-m-d H:i:s"),
//                        'updated_at' => date("Y-m-d H:i:s")
//                    ]);
                    activity()
                        ->performedOn(ServiceInventory::find($inventory->id))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('El usuario ' . Auth::user()->name . ' ha desbloqueado un día ' . $inventory_frontend["date"]);
                } else {
                    activity()
                        ->performedOn(ServiceInventory::find($inventory_frontend["id"]))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('El usuario ' . Auth::user()->name . ' ha desbloqueado un día ' . $inventory_frontend["date"]);
                    $inventory = ServiceInventory::find($inventory_frontend["id"]);
                    $inventory->locked = 0;
                    $inventory->save();
//                    DB::table('service_inventories')
//                        ->where('id', $inventory_frontend["id"])
//                        ->update(['locked' => false, 'updated_at' => date("Y-m-d H:i:s")]);

                }
            }
        });

        return Response::json(['success' => true]);
    }

    public function storeInventoryByDateRange(Request $request)
    {
        $service_id = $request->input('service_id');
        $service_rate_id = $request->input('service_rate_id');
        $date_from = Carbon::createFromFormat('d/m/Y', $request->input('dates_from'))->setTimezone('America/Lima');
        $date_to = Carbon::createFromFormat('d/m/Y', $request->input('dates_to'))->setTimezone('America/Lima');
        $days = $request->input('days');
        $availability = $request->input('availability');
        $difference_days = $date_from->diffInDays($date_to->addDay());

        $inventories_exists = ServiceInventory::whereBetween('date', [
            $date_from->format('Y-m-d'),
            $date_to->format('Y-m-d')
        ])->where('service_rate_id', $service_rate_id)->get();

        DB::transaction(function () use (
            $inventories_exists,
            $difference_days,
            $days,
            $availability,
            $date_from,
            $service_id,
            $service_rate_id
        ) {
            for ($i = 0; $i <= $difference_days; $i++) {
                $date = ($i === 0) ? $date_from : $date_from->addDay();
                $valid_day = $this->checkSelectDay($days, $date->dayOfWeek);
                if ($valid_day) {
                    date_default_timezone_set("America/Lima");
                    //ocurre para las tarifas que no estan asociadas a una bolsa
                    $inventory = $this->verifyExistInventoryInDate(
                        $inventories_exists,
                        $date_from->format('Y-m-d')
                    );
                    if (gettype($inventory) != "NULL") {
                        $inventory_database = DB::table('service_inventories')->where('id',
                            $inventory["id"])->first();
                        activity()
                            ->performedOn(ServiceInventory::find($inventory["id"]))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha Actualizado un Inventario de ' . $inventory_database->inventory_num . ' a ' . $availability);
                        $inventory = ServiceInventory::find($inventory["id"]);
                        $inventory->inventory_num = $availability;
                        $inventory->save();
//                        DB::table('service_inventories')
//                            ->where('id', $inventory["id"])
//                            ->update(['inventory_num' => $availability]);
                    } else {
//                        $id = DB::table('service_inventories')->insertGetId([
//                            'day' => $date_from->day,
//                            'date' => $date_from->format('Y-m-d'),
//                            'inventory_num' => $availability,
//                            'total_booking' => 0,
//                            'total_canceled' => 0,
//                            'locked' => false,
//                            'service_rate_id' => $service_rate_id,
//                            'created_at' => date("Y-m-d H:i:s"),
//                            'updated_at' => date("Y-m-d H:i:s")
//                        ]);
                        $inventory = new ServiceInventory();
                        $inventory->day = $date_from->day;
                        $inventory->date = $date_from->format('Y-m-d');
                        $inventory->inventory_num = $availability;
                        $inventory->total_booking = 0;
                        $inventory->total_canceled = 0;
                        $inventory->locked = 0;
                        $inventory->service_rate_id = $service_rate_id;
                        $inventory->save();
                        activity()
                            ->performedOn(ServiceInventory::find($inventory->id))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $date_from->format('Y-m-d')])
                            ->log('El usuario ' . Auth::user()->name . ' ha Creado un Inventario de ' . $availability);
                    }
                }
            }
            ProgressBar::updateOrCreate(
                [
                    'slug' => 'service_progress_availability',
                    'value' => 10,
                    'type' => 'service',
                    'object_id' => $service_id
                ]
            );
        });
        return Response::json(['success' => true]);
    }

    public function blockedInventoryByDateRange(Request $request)
    {
        $service_id = $request->input('service_id');
        $service_rate_id = $request->input('service_rate_id');
        $date_from = Carbon::createFromFormat('d/m/Y', $request->input('dates_from'))->setTimezone('America/Lima');
        $date_to = Carbon::createFromFormat('d/m/Y', $request->input('dates_to'))->setTimezone('America/Lima');
        $rate_selected = $request->input('rate_selected');
        $days = $request->input('days');


        if ($request->input('locked') == 1) {
            $locked = 1;
            $message = "bloqueado";
        } else {
            $locked = 0;
            $message = "desbloqueado";
        }
        $difference_days = $date_from->diffInDays($date_to->addDay());
        $inventories_exists = ServiceInventory::whereBetween('date', [
            $date_from->format('Y-m-d'),
            $date_to->format('Y-m-d')
        ])->where('service_rate_id', $service_rate_id)->get();


        DB::transaction(function () use (
            $inventories_exists,
            $difference_days,
            $days,
            $locked,
            $date_from,
            $message,
            $service_id,
            $service_rate_id
        ) {
            for ($i = 0; $i <= $difference_days; $i++) {
                $date = ($i === 0) ? $date_from : $date_from->addDay();
                $valid_day = $this->checkSelectDay($days, $date->dayOfWeek);
                if ($valid_day) {
                    date_default_timezone_set("America/Lima");
                    //ocurre para las tarifas que no estan asociadas a una bolsa
                    $inventory = $this->verifyExistInventoryInDate(
                        $inventories_exists,
                        $date_from->format('Y-m-d')
                    );
                    if (gettype($inventory) != "NULL") {
                        activity()
                            ->performedOn(ServiceInventory::find($inventory["id"]))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha ' . $message . ' un día ' . $inventory["date"]);
                        $inventory = ServiceInventory::find($inventory["id"]);
                        $inventory->locked = $locked;
                        $inventory->save();
//                        DB::table('service_inventories')
//                            ->where('id', $inventory["id"])
//                            ->update(['locked' => $locked]);
                    } else {
//                        $id = DB::table('service_inventories')->insertGetId([
//                            'day' => $date_from->day,
//                            'date' => $date_from->format('Y-m-d'),
//                            'inventory_num' => 0,
//                            'total_booking' => 0,
//                            'total_canceled' => 0,
//                            'locked' => $locked,
//                            'service_rate_id' => $service_rate_id,
//                            'created_at' => date("Y-m-d H:i:s"),
//                            'updated_at' => date("Y-m-d H:i:s")
//                        ]);
                        $inventory = new ServiceInventory();
                        $inventory->day = $date_from->day;
                        $inventory->date = $date_from->format('Y-m-d');
                        $inventory->inventory_num = 0;
                        $inventory->total_booking = 0;
                        $inventory->total_canceled = 0;
                        $inventory->locked = $locked;
                        $inventory->service_rate_id = $service_rate_id;
                        $inventory->save();
                        activity()
                            ->performedOn(ServiceInventory::find($inventory->id))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $date_from->format('Y-m-d')])
                            ->log('El usuario ' . Auth::user()->name . ' ha ' . $message . ' un dia ' . $date_from->format('Y-m-d'));
                    }
                }
            }
        });
        return Response::json(['success' => true]);
    }

    private function checkSelectDay($days, $date_of_week)
    {
        foreach ($days as $day) {
            if ($day["day"] === $date_of_week && $day["selected"] === true) {
                return true;
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
