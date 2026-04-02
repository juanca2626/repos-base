<?php

namespace App\Http\Controllers;

use App\ProgressBar;
use App\PackageInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class PackageInventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $package_plan_rate_id = $request->input('package_plan_rate_id');
        $lang = $request->input('lang');
        $month = $request->input('month');
        $year = $request->input('year');

        $inventories = [];
        $fecha_carbon = Carbon::parse($year . "-" . $month . "-01");
        $days = cal_days_in_month(CAL_GREGORIAN, $fecha_carbon->month, $fecha_carbon->year);

        $PackageInventory = PackageInventory::whereBetween('date', [
            $year . "-" . $month . "-01",
            $year . "-" . $month . "-" . $days
        ])->where('package_plan_rate_id', $package_plan_rate_id)->get();

        $index = 0;
        for ($i = 1; $i <= $days; $i++) {
            $day_exists = $this->checkDayExists($PackageInventory, $i);
            $date = ($i === 1) ? $fecha_carbon->format('Y-m-d') : $fecha_carbon->addDay()->format('Y-m-d');
            if ($day_exists === null) {
                $inventories[$index]["day"] = $i;
                $inventories[$index]["id"] = '';
                $inventories[$index]["date"] = $date;
                $inventories[$index]["package_plan_rate_id"] = $package_plan_rate_id;
                $inventories[$index]["inventory_num"] = 0;
                $inventories[$index]["total_booking"] = 0;
                $inventories[$index]["locked"] = false;
                $inventories[$index]["class_selected"] = false;
                $inventories[$index]["class_locked"] = false;
                $inventories[$index]["selected"] = false;
                $inventories[$index]["class_intermediate"] = false;
            } else {
                $inventories[$index]["day"] = $PackageInventory[$day_exists]->day;
                $inventories[$index]["id"] = $PackageInventory[$day_exists]->id;
                $inventories[$index]["date"] = $PackageInventory[$day_exists]->date;
                $inventories[$index]["package_plan_rate_id"] = $PackageInventory[$day_exists]->package_plan_rate_id;
                $inventories[$index]["inventory_num"] = $PackageInventory[$day_exists]->inventory_num;
                $inventories[$index]["total_booking"] = $PackageInventory[$day_exists]->total_booking;
                $inventories[$index]["locked"] = (boolean)$PackageInventory[$day_exists]->locked;
                $inventories[$index]["class_selected"] = false;
                $inventories[$index]["class_locked"] = (boolean)$PackageInventory[$day_exists]->locked;;
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
        $package_plan_rate_id = $request->input('package_plan_rate_id');
        $inventories_frontend = $request->input('inventories_selected');
        date_default_timezone_set("America/Lima");
        DB::transaction(function () use ($inventories_frontend) {

            foreach ($inventories_frontend as $inventory_frontend) {
                if ($inventory_frontend["id"] === null) {

                    $package_inventory = new PackageInventory();
                    $package_inventory->day = $inventory_frontend["day"];
                    $package_inventory->date = $inventory_frontend["date"];
                    $package_inventory->inventory_num = $inventory_frontend["inventory_num"];
                    $package_inventory->total_booking = 0;
                    $package_inventory->total_cancelled = 0;
                    $package_inventory->locked = $inventory_frontend["locked"];
                    $package_inventory->package_plan_rate_id = $inventory_frontend["package_plan_rate_id"];
                    $package_inventory->save();
                    activity()
                        ->performedOn(PackageInventory::find($package_inventory->id))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('El usuario ' . Auth::user()->name . ' ha Creado un Inventario de '
                            . $inventory_frontend["inventory_num"]);

                } else {
                    $inventory_database = DB::table('package_inventories')->where('id',
                        $inventory_frontend["id"])->first();
                    activity()
                        ->performedOn(PackageInventory::find($inventory_frontend["id"]))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('El usuario ' . Auth::user()->name . ' ha Actualizado un Inventario de '
                            . $inventory_database->inventory_num . ' a ' . $inventory_frontend["inventory_num"]);

                    $package_inventory = PackageInventory::find($inventory_frontend["id"]);
                    $package_inventory->inventory_num = $inventory_frontend["inventory_num"];
                    $package_inventory->save();
                }

            }
        });
        ProgressBar::updateOrCreate(
            [
                'slug' => 'package_progress_quotes_inventories',
                'value' => 10,
                'type' => 'package',
                'object_id' => $package_plan_rate_id
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

                    $package_inventory = new PackageInventory();
                    $package_inventory->day = $inventory_frontend["day"];
                    $package_inventory->date = $inventory_frontend["date"];
                    $package_inventory->inventory_num = 0;
                    $package_inventory->total_booking = 0;
                    $package_inventory->total_cancelled = 0;
                    $package_inventory->locked = 1;
                    $package_inventory->package_plan_rate_id = $inventory_frontend["package_plan_rate_id"];
                    $package_inventory->save();
                    activity()
                        ->performedOn(PackageInventory::find($package_inventory->id))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('El usuario ' . Auth::user()->name . ' ha bloqueado un día ' . $inventory_frontend["date"]);
                } else {
                    activity()
                        ->performedOn(PackageInventory::find($inventory_frontend["id"]))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('El usuario ' . Auth::user()->name . ' ha bloqueado un día ' . $inventory_frontend["date"]);

                    $package_inventory = PackageInventory::find($inventory_frontend["id"]);
                    $package_inventory->locked = 1;
                    $package_inventory->save();
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

                    $package_inventory = new PackageInventory();
                    $package_inventory->day = $inventory_frontend["day"];
                    $package_inventory->date = $inventory_frontend["date"];
                    $package_inventory->inventory_num = 0;
                    $package_inventory->total_booking = 0;
                    $package_inventory->total_cancelled = 0;
                    $package_inventory->locked = 0;
                    $package_inventory->package_plan_rate_id = $inventory_frontend["package_plan_rate_id"];
                    $package_inventory->save();
                    activity()
                        ->performedOn(PackageInventory::find($package_inventory->id))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('El usuario ' . Auth::user()->name . ' ha desbloqueado un día ' . $inventory_frontend["date"]);
                } else {
                    activity()
                        ->performedOn(PackageInventory::find($inventory_frontend["id"]))
                        ->causedBy(Auth::user())
                        ->withProperties(['date_inventory' => $inventory_frontend["date"]])
                        ->log('El usuario ' . Auth::user()->name . ' ha desbloqueado un día ' . $inventory_frontend["date"]);

                    $package_inventory = PackageInventory::find($inventory_frontend["id"]);
                    $package_inventory->locked = 0;
                    $package_inventory->save();
                }
            }
        });

        return Response::json(['success' => true]);
    }

    public function storeInventoryByDateRange(Request $request)
    {
        $package_plan_rate_id = $request->input('package_plan_rate_id');
        $date_from = Carbon::createFromFormat('d/m/Y', $request->input('dates_from'))->setTimezone('America/Lima');
        $date_to = Carbon::createFromFormat('d/m/Y', $request->input('dates_to'))->setTimezone('America/Lima');
        $days = $request->input('days');
        $availability = $request->input('availability');
        $difference_days = $date_from->diffInDays($date_to->addDay());

        $inventories_exists = PackageInventory::whereBetween('date', [
            $date_from->format('Y-m-d'),
            $date_to->format('Y-m-d')
        ])->where('package_plan_rate_id', $package_plan_rate_id)->get();

        DB::transaction(function () use (
            $inventories_exists,
            $difference_days,
            $days,
            $availability,
            $date_from,
            $package_plan_rate_id
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
                        $inventory_database = DB::table('package_inventories')->where('id',
                            $inventory["id"])->first();
                        activity()
                            ->performedOn(PackageInventory::find($inventory["id"]))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha Actualizado un Inventario de ' . $inventory_database->inventory_num . ' a ' . $availability);

                        $package_inventory = PackageInventory::find($inventory["id"]);
                        $package_inventory->inventory_num = $availability;
                        $package_inventory->save();
                    } else {

                        $package_inventory = new PackageInventory();
                        $package_inventory->day = $date_from->day;
                        $package_inventory->date = $date_from->format('Y-m-d');
                        $package_inventory->inventory_num = $availability;
                        $package_inventory->total_booking = 0;
                        $package_inventory->total_cancelled = 0;
                        $package_inventory->locked = 0;
                        $package_inventory->package_plan_rate_id = $package_plan_rate_id;
                        $package_inventory->save();

                        activity()
                            ->performedOn(PackageInventory::find($package_inventory->id))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $date_from->format('Y-m-d')])
                            ->log('El usuario ' . Auth::user()->name . ' ha Creado un Inventario de ' . $availability);
                    }
                }
            }
            ProgressBar::updateOrCreate(
                [
                    'slug' => 'package_progress_quotes_inventories',
                    'value' => 10,
                    'type' => 'package',
                    'object_id' => $package_plan_rate_id
                ]
            );
        });
        return Response::json(['success' => true]);
    }

    public function blockedInventoryByDateRange(Request $request)
    {
        $package_plan_rate_id = $request->input('package_plan_rate_id');
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
        $inventories_exists = PackageInventory::whereBetween('date', [
            $date_from->format('Y-m-d'),
            $date_to->format('Y-m-d')
        ])->where('package_plan_rate_id', $package_plan_rate_id)->get();


        DB::transaction(function () use (
            $inventories_exists,
            $difference_days,
            $days,
            $locked,
            $date_from,
            $message,
            $package_plan_rate_id
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
                            ->performedOn(PackageInventory::find($inventory["id"]))
                            ->causedBy(Auth::user())
                            ->withProperties(['date_inventory' => $inventory["date"]])
                            ->log('El usuario ' . Auth::user()->name . ' ha ' . $message . ' un día ' . $inventory["date"]);

                        $package_inventory = PackageInventory::find($inventory["id"]);
                        $package_inventory->locked = (int)$locked;
                        $package_inventory->save();
                    } else {

                        $package_inventory = new PackageInventory();
                        $package_inventory->day = $date_from->day;
                        $package_inventory->date = $date_from->format('Y-m-d');
                        $package_inventory->total_booking = 0;
                        $package_inventory->total_cancelled = 0;
                        $package_inventory->locked = (int)$locked;
                        $package_inventory->package_plan_rate_id = $package_plan_rate_id;
                        $package_inventory->save();
                        activity()
                            ->performedOn(PackageInventory::find($package_inventory->id))
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
