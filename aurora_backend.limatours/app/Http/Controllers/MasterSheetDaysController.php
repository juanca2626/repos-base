<?php

namespace App\Http\Controllers;

use App\MasterSheetDay;
use App\MasterSheetService;
use App\Http\Traits\MasterSheetDays;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class MasterSheetDaysController extends Controller
{

    use MasterSheetDays;

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'master_sheet_id' => 'required',
            'date_in' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $master_sheet_id = $request->input('master_sheet_id');
            $_date_in = Carbon::createFromFormat('d/m/Y', $request->input('date_in'))->format('Y-m-d');

            $master_sheet_day = new MasterSheetDay();
            $master_sheet_day->master_sheet_id = $master_sheet_id;
            $master_sheet_day->number = 0;
            $master_sheet_day->name = $request->input('name');
            $master_sheet_day->date_in = $_date_in;
            $master_sheet_day->save();

            $master_sheet_day_new = $this->update_numbers_return_day( $master_sheet_id, $master_sheet_day->id );

            return Response::json(['success' => true, 'data'=>$master_sheet_day_new]);
        }
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_in' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $master_sheet_id = $request->input('master_sheet_id');
            $_date_in = Carbon::createFromFormat('d/m/Y', $request->input('date_in'))->format('Y-m-d');

            $master_sheet_day = MasterSheetDay::find($id);
            $master_sheet_day->name = $request->input('name');
            $master_sheet_day->date_in = $_date_in;
            $master_sheet_day->save();

            $master_sheet_day_updated = $this->update_numbers_return_day( $master_sheet_id, $master_sheet_day->id );

            return Response::json(['success' => true, 'data'=>$master_sheet_day_updated]);
        }
    }

    public function show($id){

        $master_sheet_day = MasterSheetDay::with(['services'=>function($query){
            return $query->orderBy('check_in');
        }])
            ->find($id)->toArray();

        foreach ( $master_sheet_day['services'] as $k=> $service ){
            $master_sheet_day['services'][$k]['show_paxs'] = false;
            $master_sheet_day['services'][$k]['paxs'] =
                ($master_sheet_day['services'][$k]['paxs']===null || $master_sheet_day['services'][$k]['paxs'] === '')
                    ? 0 : $master_sheet_day['services'][$k]['paxs'];
            $master_sheet_day['services'][$k]['show_comment'] = false;
            $master_sheet_day['services'][$k]['show_tr'] = true;
            $master_sheet_day['services'][$k]['id_front'] =  $master_sheet_day['services'][$k]['id'];
            $master_sheet_day['services'][$k]['results'] = false;
        }

        return Response::json(['success' => true, 'data'=>$master_sheet_day]);
    }

    public function destroy($id){

        $master_sheet_day = MasterSheetDay::find($id);
        $master_sheet_day->delete();

        $this->update_numbers( $master_sheet_day->master_sheet_id );

        return Response::json(['success' => true]);
    }

    public function update_services($id, Request $request){

        $services = $request->input('services');
        $services_for_update_ids = [];

        foreach ( $services as $s ){
            if( $s['id'] !== '' && $s['id'] !== null ){
                $service = MasterSheetService::find($s['id']);
            } else {
                $service = new MasterSheetService;
                $service->master_sheet_day_id = $id;
            }
            $_service_code_stela = ( $s['service_code_stela'] === null ) ? '' : $s['service_code_stela'];
            $_description = ( $s['description'] === null ) ? '' : $s['description'];
            $_description_ES = ( $s['description_ES'] === null ) ? $_description : $s['description_ES'];
            $_description_EN = ( $s['description_EN'] === null ) ? $_description : $s['description_EN'];
            $_description_PT = ( $s['description_PT'] === null ) ? $_description : $s['description_PT'];
            $_description_IT = ( $s['description_IT'] === null ) ? $_description : $s['description_IT'];
            if( $_service_code_stela === '' ){
                $_description_ES = $_description;
                $_description_EN = $_description;
                $_description_PT = $_description;
                $_description_IT = $_description;
            }
            $service->service_code_stela = $_service_code_stela;
            $service->check_in = $s['check_in'];
            $service->check_out = $s['check_out'];
            $service->destination_city = $s['destination_city'];
            $service->origin_city = $s['origin_city'];
            $service->includes = $s['includes'];
            $service->description = $_description;
            $service->description_ES = $_description_ES;
            $service->description_EN = $_description_EN;
            $service->description_PT = $_description_PT;
            $service->description_IT = $_description_IT;
            $service->type_service = $s['type_service'];
            $service->status = $s['status'];
            $service->comment_status = $s['comment_status'];
            $service->comment = ( $s['comment'] === null ) ? '' : $s['comment'];
            $service->pax_status = $s['pax_status'];
            $service->paxs = ( $s['paxs'] === null ) ? '' : $s['paxs'];
            $service->attached = $s['attached'];
            $service->save();

            array_push($services_for_update_ids, $service->id);
        }

        MasterSheetService::whereNotIn('id', $services_for_update_ids)
            ->where('master_sheet_day_id', $id)
            ->delete();

        $this->update_destinations( $id );

        return Response::json(['success' => true]);

    }

}
