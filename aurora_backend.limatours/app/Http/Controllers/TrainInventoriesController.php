<?php

namespace App\Http\Controllers;

use App\TrainInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class TrainInventoriesController extends Controller
{

    public function index(Request $request)
    {
        $train_rate_id = $request->input('train_rate_id');
        $month = $request->input('month');
        $year = $request->input('year');

        $inventories = [];
        $fecha_carbon = Carbon::parse($year . "-" . $month . "-01");
        $days = cal_days_in_month(CAL_GREGORIAN, $fecha_carbon->month, $fecha_carbon->year);

        $TrainInventory = TrainInventory::whereBetween('date', [
            $year . "-" . $month . "-01",
            $year . "-" . $month . "-" . $days
        ])->where('train_rate_id', $train_rate_id)->get();

        $index = 0;
        for ($i = 1; $i <= $days; $i++) {
            $day_exists = $this->checkDayExists($TrainInventory, $i);
            $date = ($i === 1) ? $fecha_carbon->format('Y-m-d') : $fecha_carbon->addDay()->format('Y-m-d');
            if ($day_exists === null) {
                $inventories[$index]["day"] = $i;
                $inventories[$index]["id"] = '';
                $inventories[$index]["date"] = $date;
                $inventories[$index]["train_rate_id"] = $train_rate_id;
                $inventories[$index]["inventory_num"] = 0;
                $inventories[$index]["total_booking"] = 0;
                $inventories[$index]["locked"] = false;
                $inventories[$index]["class_selected"] = false;
                $inventories[$index]["class_locked"] = false;
                $inventories[$index]["selected"] = false;
                $inventories[$index]["class_intermediate"] = false;
            } else {
                $inventories[$index]["day"] = $TrainInventory[$day_exists]->day;
                $inventories[$index]["id"] = $TrainInventory[$day_exists]->id;
                $inventories[$index]["date"] = $TrainInventory[$day_exists]->date;
                $inventories[$index]["train_rate_id"] = $TrainInventory[$day_exists]->train_rate_id;
                $inventories[$index]["inventory_num"] = $TrainInventory[$day_exists]->inventory_num;
                $inventories[$index]["total_booking"] = $TrainInventory[$day_exists]->total_booking;
                $inventories[$index]["locked"] = (boolean)$TrainInventory[$day_exists]->locked;
                $inventories[$index]["class_selected"] = false;
                $inventories[$index]["class_locked"] = (boolean)$TrainInventory[$day_exists]->locked;;
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

    public function store(Request $request)
    {
        $train_template_id = $request->input('train_template_id');
        $train_rate_id = $request->input('train_rate_id');

        $inventories_frontend = $request->input('inventories_selected');
        date_default_timezone_set("America/Lima");
        DB::transaction(function () use ($inventories_frontend) {

            foreach ($inventories_frontend as $inventory_frontend) {
                if ($inventory_frontend["id"] === null) {
                    $id = DB::table('train_inventories')->insertGetId([
                        'day' => $inventory_frontend["day"],
                        'date' => $inventory_frontend["date"],
                        'inventory_num' => $inventory_frontend["inventory_num"],
                        'total_booking' => 0,
                        'total_canceled' => 0,
                        'locked' => $inventory_frontend["locked"],
                        'train_rate_id' => $inventory_frontend["train_rate_id"],
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s")
                    ]);

                    activity()
                        ->performedOn(TrainInventory::find($id))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('(Trenes) El usuario ' . Auth::user()->name . ' ha Creado un Inventario de '
                            . $inventory_frontend["inventory_num"]);

                } else {
                    $inventory_database = DB::table('train_inventories')->where('id',
                        $inventory_frontend["id"])->first();
                    activity()
                        ->performedOn(TrainInventory::find($inventory_frontend["id"]))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('(Trenes) El usuario ' . Auth::user()->name . ' ha Actualizado un Inventario de '
                            . $inventory_database->inventory_num . ' a ' . $inventory_frontend["inventory_num"]);

                    DB::table('train_inventories')
                        ->where('id', $inventory_frontend["id"])
                        ->update(['inventory_num' => $inventory_frontend["inventory_num"]]);
                }

            }
        });

        return Response::json(['success' => true]);
    }


    public function lockedDays(Request $request)
    {
        $inventories_frontend = $request->input('inventories_selected');
        date_default_timezone_set("America/Lima");
        DB::transaction(function () use ($inventories_frontend) {
            foreach ($inventories_frontend as $inventory_frontend) {
                if ($inventory_frontend["id"] === null) {
                    $id = DB::table('train_inventories')->insertGetId([
                        'day' => $inventory_frontend["day"],
                        'date' => $inventory_frontend["date"],
                        'inventory_num' => 0,
                        'total_booking' => 0,
                        'total_canceled' => 0,
                        'locked' => true,
                        'train_rate_id' => $inventory_frontend["train_rate_id"],
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s")
                    ]);
                    activity()
                        ->performedOn(TrainInventory::find($id))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('(Trenes) El usuario ' . Auth::user()->name . ' ha bloqueado un día ' . $inventory_frontend["date"]);
                } else {
                    activity()
                        ->performedOn(TrainInventory::find($inventory_frontend["id"]))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('(Trenes) El usuario ' . Auth::user()->name . ' ha bloqueado un día ' . $inventory_frontend["date"]);
                    DB::table('train_inventories')
                        ->where('id', $inventory_frontend["id"])
                        ->update(['locked' => true]);
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
                    $id = DB::table('train_inventories')->insertGetId([
                        'day' => $inventory_frontend["day"],
                        'date' => $inventory_frontend["date"],
                        'inventory_num' => 0,
                        'total_booking' => 0,
                        'total_canceled' => 0,
                        'locked' => false,
                        'train_rate_id' => $inventory_frontend["train_rate_id"],
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s")
                    ]);
                    activity()
                        ->performedOn(TrainInventory::find($id))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('(Trenes) El usuario ' . Auth::user()->name . ' ha desbloqueado un día ' . $inventory_frontend["date"]);
                } else {
                    activity()
                        ->performedOn(TrainInventory::find($inventory_frontend["id"]))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('(Trenes) El usuario ' . Auth::user()->name . ' ha desbloqueado un día ' . $inventory_frontend["date"]);

                    DB::table('train_inventories')
                        ->where('id', $inventory_frontend["id"])
                        ->update(['locked' => false, 'updated_at' => date("Y-m-d H:i:s")]);

                }
            }
        });

        return Response::json(['success' => true]);
    }


    public function storeInventoryByDateRange(Request $request)
    {
        $train_template_id = $request->input('train_template_id');
        $train_rate_id = $request->input('train_rate_id');
        $date_from = Carbon::createFromFormat('d/m/Y', $request->input('dates_from'))->setTimezone('America/Lima');
        $date_to = Carbon::createFromFormat('d/m/Y', $request->input('dates_to'))->setTimezone('America/Lima');
        $days = $request->input('days');
        $availability = $request->input('availability');
        $difference_days = $date_from->diffInDays($date_to->addDay());

        $inventories_exists = TrainInventory::whereBetween('date', [
            $date_from->format('Y-m-d'),
            $date_to->format('Y-m-d')
        ])->where('train_rate_id', $train_rate_id)->get();

        DB::transaction(function () use (
            $inventories_exists,
            $difference_days,
            $days,
            $availability,
            $date_from,
            $train_template_id,
            $train_rate_id
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
                        $inventory_database = DB::table('train_inventories')->where('id',
                            $inventory["id"])->first();
                        activity()
                            ->performedOn(TrainInventory::find($inventory["id"]))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory["date"]])
                            ->log('(Trenes) El usuario ' . Auth::user()->name . ' ha Actualizado un Inventario de ' . $inventory_database->inventory_num . ' a ' . $availability);

                        DB::table('train_inventories')
                            ->where('id', $inventory["id"])
                            ->update(['inventory_num' => $availability]);
                    } else {
                        $id = DB::table('train_inventories')->insertGetId([
                            'day' => $date_from->day,
                            'date' => $date_from->format('Y-m-d'),
                            'inventory_num' => $availability,
                            'total_booking' => 0,
                            'total_canceled' => 0,
                            'locked' => false,
                            'train_rate_id' => $train_rate_id,
                            'created_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
                        activity()
                            ->performedOn(TrainInventory::find($id))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $date_from->format('Y-m-d')])
                            ->log('(Trenes) El usuario ' . Auth::user()->name . ' ha Creado un Inventario de ' . $availability);
                    }
                }
            }

        });
        return Response::json(['success' => true]);
    }

    public function blockedInventoryByDateRange(Request $request)
    {
        $train_template_id = $request->input('train_template_id');
        $train_rate_id = $request->input('train_rate_id');
        $date_from = Carbon::createFromFormat('d/m/Y', $request->input('dates_from'))->setTimezone('America/Lima');
        $date_to = Carbon::createFromFormat('d/m/Y', $request->input('dates_to'))->setTimezone('America/Lima');
        $rate_selected = $request->input('rate_selected');
        $days = $request->input('days');


        if ($request->input('locked') == 1) {
            $locked = true;
            $message = "bloqueado";
        } else {
            $locked = false;
            $message = "desbloqueado";
        }
        $difference_days = $date_from->diffInDays($date_to->addDay());
        $inventories_exists = TrainInventory::whereBetween('date', [
            $date_from->format('Y-m-d'),
            $date_to->format('Y-m-d')
        ])->where('train_rate_id', $train_rate_id)->get();


        DB::transaction(function () use (
            $inventories_exists,
            $difference_days,
            $days,
            $locked,
            $date_from,
            $message,
            $train_template_id,
            $train_rate_id
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
                            ->performedOn(TrainInventory::find($inventory["id"]))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory["date"]])
                            ->log('(Trenes) El usuario ' . Auth::user()->name . ' ha ' . $message . ' un día ' . $inventory["date"]);
                        DB::table('train_inventories')
                            ->where('id', $inventory["id"])
                            ->update(['locked' => $locked]);
                    } else {
                        $id = DB::table('train_inventories')->insertGetId([
                            'day' => $date_from->day,
                            'date' => $date_from->format('Y-m-d'),
                            'inventory_num' => 0,
                            'total_booking' => 0,
                            'total_canceled' => 0,
                            'locked' => $locked,
                            'train_rate_id' => $train_rate_id,
                            'created_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
                        activity()
                            ->performedOn(TrainInventory::find($id))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $date_from->format('Y-m-d')])
                            ->log('(Trenes) El usuario ' . Auth::user()->name . ' ha ' . $message . ' un dia ' . $date_from->format('Y-m-d'));
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
