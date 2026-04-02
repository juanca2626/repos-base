<?php

namespace App\Http\Controllers;

use App\PackageSchedule;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PackageSchedulesController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:packageschedules.read')->only('index');
        $this->middleware('permission:packageschedules.create')->only('store');
        $this->middleware('permission:packageschedules.update')->only('update');
        $this->middleware('permission:packageschedules.delete')->only('destroy');
    }

    /**
     * @param $package_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($package_id)
    {
        $schedules = PackageSchedule::where('package_id', $package_id)->get();
        $data = [
            'data' => $schedules,
            'success' => true
        ];
        return Response::json($data);
    }

    /**
     * @param $package_id
     * @param Request $request
     * @return JsonResponse
     */
    public function store($package_id, Request $request)
    {
        $schedule = new PackageSchedule();
        $schedule->date_from =
            date("Y-m-d", strtotime(str_replace('/', '-', $request->input('date_from'))));
        $schedule->date_to =
            date("Y-m-d", strtotime(str_replace('/', '-', $request->input('date_to'))));
        $schedule->state = 1;
        $schedule->room = $request->input('room');
        $schedule->monday = (int)$request->input('monday');
        $schedule->tuesday = (int)$request->input('tuesday');
        $schedule->wednesday = (int)$request->input('wednesday');
        $schedule->thursday = (int)$request->input('thursday');
        $schedule->friday = (int)$request->input('friday');
        $schedule->saturday = (int)$request->input('saturday');
        $schedule->sunday = (int)$request->input('sunday');
        $schedule->package_id = $package_id;
        $schedule->save();

        $response = ['success' => true, 'object_id' => $schedule->id];

        return Response::json($response);
    }

    /**
     * @param $package_id
     * @param $schedule_id
     * @return JsonResponse
     */
    public function destroy($package_id, $schedule_id)
    {
        $schedule = PackageSchedule::find($schedule_id);
        $schedule->delete();

        return Response::json(['success' => true]);
    }

    /**
     * @param $package_id
     * @param $schedule_id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateState($package_id, $schedule_id, Request $request)
    {
        $schedule = PackageSchedule::find($schedule_id);
        if ($request->input("state")) {
            $schedule->state = 0;
        } else {
            $schedule->state = 1;
        }
        $schedule->save();
        return Response::json(['success' => true]);
    }
}
