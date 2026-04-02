<?php

namespace App\Http\Controllers;

use App\MasterServiceReleased;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class MasterServiceReleasedController extends Controller
{
    public function index(Request $request)
    {
        $master_service_id = $request->get('master_service_id');
        $year = ($request->has('year') and !empty($request->get('year'))) ? $request->get('year') : date('Y');

        $service_released = MasterServiceReleased::where('master_service_id', $master_service_id)
            ->whereYear('date_from', $year)
            ->orderBy('created_at', 'asc')
            ->get();

        return Response::json(['success' => true, 'data' => $service_released]);

    }

    public function store(Request $request)
    {
        $master_service_id = $request->get('master_service_id');
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');
        $every = $request->get('every');
        $type_discount = $request->get('type_discount');
        $value = $request->get('value');
        try {
            DB::beginTransaction();

            if ($this->validateReleasedRanges($master_service_id, $date_from, $date_to)) {
                return Response::json(['success' => false, 'error' => 'RANGE_EXIST']);
            }else {
                $master_service_released = new MasterServiceReleased();
                $master_service_released->master_service_id = $master_service_id;
                $master_service_released->date_from = $date_from;
                $master_service_released->date_to = $date_to;
                $master_service_released->every = $every;
                $master_service_released->type_discount = $type_discount;
                $master_service_released->value = $value;
                $master_service_released->save();
            }

            DB::commit();
            return Response::json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return Response::json(['success' => false, 'error' => $e->getMessage().' - '.$e->getLine()]);
        }
    }


    public function update($id, Request $request)
    {
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');
        $every = $request->get('every');
        $type_discount = $request->get('type_discount');
        $value = $request->get('value');
        try {
            DB::beginTransaction();

            $master_service_released = MasterServiceReleased::find($id);
            $master_service_released->date_from = $date_from;
            $master_service_released->date_to = $date_to;
            $master_service_released->every = $every;
            $master_service_released->type_discount = $type_discount;
            $master_service_released->value = $value;
            $master_service_released->save();

            DB::commit();
            return Response::json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return Response::json(['success' => false, 'error' => $e->getMessage().' - '.$e->getLine()]);
        }
    }


    public function validateReleasedRanges($master_service_id, $from, $to)
    {
        /*
            date_from: "2022-02-15"
             date_to: "2022-02-26"
         * */
        $query = DB::select(
            DB::raw("select * from master_service_released where
                        (
                            (
                                ('$from' >= master_service_released.date_from and '$from' <= master_service_released.date_to) or
		                        ('$to' >= master_service_released.date_from and '$to' <= master_service_released.date_to)
                            ) or
                            (
                                (master_service_released.date_from >= '$from' and master_service_released.date_to >= '$from') and
		                        (master_service_released.date_from <= '$to' and master_service_released.date_to <= '$to')
                            )
                        ) and master_service_released.master_service_id = '$master_service_id' and deleted_at is null limit 1
                        "));
        if (count($query) > 0) {
            $response = true;
        } else {
            $response = false;
        }

        return $response;
    }

    public function duplicatePeriod(Request $request)
    {
        $master_service_id = $request->get('master_service_id');
        $year_copy = $request->get('year_copy');
        $year_apply = $request->get('year_apply');
        try {
            $service_rate_released = MasterServiceReleased::where('master_service_id', $master_service_id)
                ->whereYear('date_from', $year_copy)
                ->get()->toArray();
            DB::beginTransaction();
            //Si ya existen datos del año, eliminamos
            MasterServiceReleased::where('master_service_id', $master_service_id)
                ->whereYear('date_from', $year_apply)
                ->delete();
            foreach ($service_rate_released as $service_rate) {
                    $date_from_explode = explode('-', $service_rate['date_from']);
                    $date_to_explode = explode('-', $service_rate['date_to']);
                    $new_date_from = $year_apply.'-'.$date_from_explode[1].'-'.$date_from_explode[2];
                    $new_date_to = $year_apply.'-'.$date_to_explode[1].'-'.$date_to_explode[2];
                    $new_released_ranges = new MasterServiceReleased();
                    $new_released_ranges->master_service_id = $master_service_id;
                    $new_released_ranges->date_from = $new_date_from;
                    $new_released_ranges->date_to = $new_date_to;
                    $new_released_ranges->every = $service_rate['every'];
                    $new_released_ranges->type_discount = $service_rate['type_discount'];
                    $new_released_ranges->value = $service_rate['value'];
                    $new_released_ranges->save();
            }
            DB::commit();
            return Response::json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return Response::json(['success' => false, 'error' => $e->getMessage().' - '.$e->getLine()]);
        }
    }


    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $service_released = MasterServiceReleased::find($id);
            $service_released->delete();
            DB::commit();
            return Response::json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return Response::json(['success' => true, 'error' => $e->getMessage().' - '.$e->getLine()]);
        }
    }

}
