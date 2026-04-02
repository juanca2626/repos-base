<?php

namespace App\Http\Controllers;

use App\MasterSheet;
use App\MasterSheetDay;
use App\MasterSheetService;
use App\Http\Traits\MasterSheetDays;
use App\MasterSheetUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class MasterSheetsController extends Controller
{

    use MasterSheetDays;

    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit') ? $request->input('limit') : 5;
        $querySearch = $request->input('query');

        $master_sheet_ids_shared = MasterSheetUser::where('user_id', Auth::user()->id)->pluck('master_sheet_id');

        $master_sheets = MasterSheet::with(['user'])
            ->withCount(['users'])
            ->where(function($query)use($master_sheet_ids_shared){
                $query->where('user_id', Auth::user()->id)
                    ->orWhereIn('id', $master_sheet_ids_shared);
            });

        if ($querySearch) {
            $master_sheets->where(function ($query) use ($querySearch) {
                $query->orWhere('name', 'like', '%'.$querySearch.'%');
            });
        }

        $count = $master_sheets->count();

        if ($paging === 1) {
            $master_sheets = $master_sheets->take($limit)->get();
        } else {
            $master_sheets = $master_sheets->skip($limit * ($paging - 1))->take($limit)->get();
        }

        // Es el permiso propio, será [] si es q es el autor
        $master_sheets = $master_sheets->transform(function ($ms) {
            $ms['permission'] = collect();
            $query_user = MasterSheetUser::where('master_sheet_id', $ms['id'])
                ->where('user_id', Auth::user()->id)->first();
            if ($query_user) {
                $ms['permission']->add($query_user);
            }
            return $ms;
        });

        $data = [
            'data' => $master_sheets,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);

    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'date_out' => 'required',
            'paxes' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {
            $parse_date_out =
                Carbon::createFromFormat('d/m/Y', $request->input('date_out'))->format('Y-m-d');
            $master_sheet = new MasterSheet();
            $master_sheet->name = $request->input('name');
            $master_sheet->user_id = Auth::user()->id;
            $master_sheet->date_out = $parse_date_out;
            $master_sheet->paxes = $request->input('paxes');
            $master_sheet->leader = $request->input('leader');
            $master_sheet->includes_scort = $request->input('includes_scort');
            $master_sheet->comment = $request->input('comment');
            $master_sheet->save();
            return Response::json(['success' => true, 'master_sheet_id'=>$master_sheet->id]);
        }
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'date_out' => 'required',
            'paxes' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {
            $parse_date_out =
                Carbon::createFromFormat('d/m/Y', $request->input('date_out'))->format('Y-m-d');
            $master_sheet = MasterSheet::find($id);
            $master_sheet->name = $request->input('name');
            $master_sheet->date_out = $parse_date_out;
            $master_sheet->paxes = $request->input('paxes');
            $master_sheet->leader = $request->input('leader');
            $master_sheet->includes_scort = $request->input('includes_scort');
            $master_sheet->comment = $request->input('comment');
            $master_sheet->save();
            return Response::json(['success' => true]);
        }
    }

    public function destroy($id){

        MasterSheet::find($id)->delete();

        $data = [
            'success' => true
        ];

        return Response::json($data);
    }

    public function show($id){

        $master_sheet = MasterSheet::with(['user', 'users'])
            ->withCount(['users'])
            ->withCount(['messages'])
            ->find($id);

        $data = [
            'success' => true,
            'data' => $master_sheet
        ];

        return Response::json($data);
    }

    public function get_users($master_sheet_id)
    {
        $master_sheet_users = MasterSheetUser::where('master_sheet_id', $master_sheet_id)
            ->with('user')->get();

        return Response::json([
            'success' => true,
            'data' => $master_sheet_users
        ]);
    }

    public function get_days($master_sheet_id){
        $master_sheet_days = MasterSheetDay::with(['services'=>function($query){
            return $query->orderBy('check_in');
        }])
            ->where('master_sheet_id', $master_sheet_id)
            ->orderBy('number')
            ->orderBy('updated_at','desc')
            ->get();

        $master_sheet_days = $master_sheet_days->transform(function ($msd, $k) {
            $msd['row_type'] = 'regular';
            $msd['row_class'] = '';
            return $msd;
        });

        $master_sheet_days = $master_sheet_days->toArray();
        $_repeat_days = [];
        foreach( $master_sheet_days as $k => $msd ){
            // Logica para dias repetidos
            if( isset( $_repeat_days[$msd['number']] ) ){
                if( $_repeat_days[$msd['number']] == 1 ){
                    $master_sheet_days[$k-1]['row_class'] = 'warning';
                }
                $master_sheet_days[$k]['row_class'] = 'warning';
                $_repeat_days[$msd['number']]++;
            } else {
                $_repeat_days[$msd['number']] = 1;
            }
            // logica para valores default usados en front
            foreach( $msd['services'] as $s_k => $service ){
                $master_sheet_days[$k]['services'][$s_k]['show_edit'] = false;
            }
        };

        $_master_sheet_days = []; // push de dias vacios
        foreach ( $master_sheet_days as $key => $day ){
            if( $key > 0 && $master_sheet_days[$key-1]['row_type'] == 'regular' && $day['row_type'] == 'regular' ){
                $previous_date = Carbon::parse($master_sheet_days[$key-1]['date_in']);
                $this_date = Carbon::parse($day['date_in']);
                $diff_in_days = $previous_date->diffInDays($this_date);
                if( $diff_in_days > 1 ){
                    $_free_days = $diff_in_days - 1;
                    array_push( $_master_sheet_days, [
                        "row_type" => "free",
                        "diff_in_days" => $diff_in_days,
                        "free_days" => $_free_days,
                        "number_in" => $master_sheet_days[$key-1]['number'] + 1,
                        "number_out" => $master_sheet_days[$key-1]['number'] + $_free_days,
                        "date_in" =>
                            Carbon::parse($master_sheet_days[$key-1]['date_in'])->addDays(1)->format('Y-m-d'),
                        "date_out" =>
                            Carbon::parse($day['date_in'])->subDays(1)->format('Y-m-d')
                    ] );
                    array_push( $_master_sheet_days, $day );
                } else {
                    array_push( $_master_sheet_days, $day );
                }
            } else {
                array_push( $_master_sheet_days, $day );
            }
        }

        $master_sheet_days = $_master_sheet_days;

        $data = [
            'success' => true,
            'data' => $master_sheet_days
        ];

        return Response::json($data);
    }

    public function move_day($master_sheet_id, $master_sheet_day_id, Request $request){

        $direction = $request->input('direction');

        $days = MasterSheetDay::where('master_sheet_id',$master_sheet_id)
            ->orderBy('date_in')
            ->orderBy('updated_at','desc')
            ->get();

        foreach ( $days as $k => $day ){
            if( $day->id == $master_sheet_day_id ){
                $date_to_put = Carbon::parse($day->date_in);
                if( $direction == 'up' ){
                    $date_to_put = $date_to_put->subDays(1)->format('Y-m-d');
                    if($k >= 1){
                        if( $days[$k-1]->date_in == $date_to_put ){
                            $days[$k-1]->date_in = $day->date_in;
                            $days[$k-1]->save();
                        }
                    }
                } else { // down
                    $date_to_put = $date_to_put->addDays(1)->format('Y-m-d');
                    if($k < (count($days)-1) ){
                        if( $days[$k+1]->date_in == $date_to_put ){
                            $days[$k+1]->date_in = $day->date_in;
                            $days[$k+1]->save();
                        }
                    }
                }
                $day->date_in = $date_to_put;
                $day->save();
            }
        }
        // UPDATE NUMBERS DAY
        $this->update_numbers( $master_sheet_id );

        $data = [
            'success' => true
        ];

        return Response::json($data);

    }

    public function get_all_comments($id){

        $days_ids = MasterSheetDay::where('master_sheet_id', $id)->pluck('id');

        $comments = MasterSheetService::whereIn('master_sheet_day_id', $days_ids)
            ->where('comment_status', 1)
            ->with('day')
            ->get();

        $comments->transform(function($query){
            $query['show_edit'] = false;
            return $query;
        });

        return Response::json([ "success" => true, "data" => $comments ]);

    }

    public function get_total_comments($id){

        $days_ids = MasterSheetDay::where('master_sheet_id', $id)->pluck('id');

        $total_comments = MasterSheetService::whereIn('master_sheet_day_id', $days_ids)
            ->where('comment_status', 1)
            ->count();

        return Response::json([ "success" => true, "data" => $total_comments ]);

    }

    public function clone_master_sheet($id, Request $request){

        $master_sheet_id_for_clone = $request->input('master_sheet_id_for_clone');

        DB::transaction(function () use ($id, $master_sheet_id_for_clone) {

            MasterSheetDay::where('master_sheet_id',$id)->delete();

            $days = MasterSheetDay::where('master_sheet_id', $master_sheet_id_for_clone)
                ->with(['services'])
                ->orderBy('date_in')
                ->orderBy('updated_at','desc')
                ->get();

            foreach ( $days as $day ){
                $new_master_sheet_day = new MasterSheetDay();
                $new_master_sheet_day->master_sheet_id = $id;
                $new_master_sheet_day->number = $day->number;
                $new_master_sheet_day->name = $day->name;
                $new_master_sheet_day->destinations = $day->destinations;
                $new_master_sheet_day->date_in = $day->date_in;
                $new_master_sheet_day->save();

                foreach ( $day->services as $service ){
                    $new_master_sheet_service = new MasterSheetService();
                    $new_master_sheet_service->master_sheet_day_id = $new_master_sheet_day->id;
                    $new_master_sheet_service->service_code_stela = $service->service_code_stela;
                    $new_master_sheet_service->check_in = $service->check_in;
                    $new_master_sheet_service->check_out = $service->check_out;
                    $new_master_sheet_service->destination_city = $service->destination_city;
                    $new_master_sheet_service->origin_city =  $service->origin_city;
                    $new_master_sheet_service->includes = $service->includes;
                    $new_master_sheet_service->description = $service->description;
                    $new_master_sheet_service->description_ES = $service->description_ES;
                    $new_master_sheet_service->description_EN = $service->description_EN;
                    $new_master_sheet_service->description_PT = $service->description_PT;
                    $new_master_sheet_service->description_IT = $service->description_IT;
                    $new_master_sheet_service->type_service = $service->type_service;
                    $new_master_sheet_service->status = $service->status;
                    $new_master_sheet_service->comment_status = $service->comment_status;
                    $new_master_sheet_service->comment = $service->comment;
                    $new_master_sheet_service->attached = $service->attached;
                    $new_master_sheet_service->save();
                }
            }
        });

        $data = [
            'success' => true
        ];

        return Response::json($data);

    }

    public function clone_package_services_stela($id, Request $request){

        $package_services = $request->input('package_services');

        DB::transaction(function () use ($id, $package_services) {

            $dates_taken = [];

            MasterSheetDay::where('master_sheet_id',$id)->delete();

            foreach ( $package_services as $p_service ){

                if( !(isset( $dates_taken[$p_service['date_in']] )) ){

                    $new_master_sheet_day = new MasterSheetDay();
                    $new_master_sheet_day->master_sheet_id = $id;
                    $new_master_sheet_day->number = 0; // Después se reordena
                    $new_master_sheet_day->name = "";
                    $new_master_sheet_day->destinations = "";
                    $new_master_sheet_day->date_in = $p_service['date_in'];
                    $new_master_sheet_day->save();

                    $dates_taken[$p_service['date_in']] = $new_master_sheet_day->id;
                }

                if( $p_service['type'] === 'hotel' ){
                    $descri_es_ = $p_service['stela_codes'][0]['descri_es'];
                    $descri_en_ = $p_service['stela_codes'][0]['descri_en'];
                    $descri_pt_ = $p_service['stela_codes'][0]['descri_pt'];
                    $descri_it_ = $p_service['stela_codes'][0]['descri_it'];
                    $new_master_sheet_service = new MasterSheetService();
                    $new_master_sheet_service->master_sheet_day_id = $dates_taken[$p_service['date_in']];
                    $new_master_sheet_service->service_code_stela = $p_service['equivalence'];
                    $new_master_sheet_service->check_in = $p_service['check_in'];
                    $new_master_sheet_service->check_out = $p_service['check_out'];
                    $new_master_sheet_service->origin_city =  $p_service['stela_codes'][0]['ciudes'];
                    $new_master_sheet_service->destination_city = $p_service['stela_codes'][0]['ciuhas'];
                    $new_master_sheet_service->includes = "";
                    $new_master_sheet_service->description = $p_service['stela_codes'][0]['descri'];
                    $new_master_sheet_service->description_ES =
                        ($descri_es_ !== "undefined" && $descri_es_ !== null && $descri_es_ !== '' )
                            ? $descri_es_ : $p_service['stela_codes'][0]['descri'];
                    $new_master_sheet_service->description_EN =
                        ($descri_en_ !== "undefined" && $descri_en_ !== null && $descri_en_ !== '' )
                            ? $descri_en_ : $p_service['stela_codes'][0]['descri'];
                    $new_master_sheet_service->description_PT =
                        ($descri_pt_ !== "undefined" && $descri_pt_ !== null && $descri_pt_ !== '' )
                            ? $descri_pt_ : $p_service['stela_codes'][0]['descri'];
                    $new_master_sheet_service->description_IT =
                        ($descri_it_ !== "undefined" && $descri_it_ !== null && $descri_it_ !== '' )
                            ? $descri_it_ : $p_service['stela_codes'][0]['descri'];
                    $new_master_sheet_service->type_service = $p_service['type'];
                    $new_master_sheet_service->status = 0;
                    $new_master_sheet_service->comment_status = 0;
                    $new_master_sheet_service->comment = "";
                    $new_master_sheet_service->attached = "";
                    $new_master_sheet_service->save();
                } else { // service
                    foreach ( $p_service['stela_codes'] as $stela_code ){
                        $descri_es_ = $stela_code['descri_es'];
                        $descri_en_ = $stela_code['descri_en'];
                        $descri_pt_ = $stela_code['descri_pt'];
                        $descri_it_ = $stela_code['descri_it'];
                        $new_master_sheet_service = new MasterSheetService();
                        $new_master_sheet_service->master_sheet_day_id = $dates_taken[$p_service['date_in']];
                        $new_master_sheet_service->service_code_stela = $stela_code['codigo'];
                        $new_master_sheet_service->check_in = "";
                        $new_master_sheet_service->check_out = "";
                        $new_master_sheet_service->origin_city =  $stela_code['ciudes'];
                        $new_master_sheet_service->destination_city = $stela_code['ciuhas'];
                        $new_master_sheet_service->includes = "";
                        $new_master_sheet_service->description = $stela_code['descri'];
                        $new_master_sheet_service->description_ES =
                            ($descri_es_ !== "undefined" && $descri_es_ !== null && $descri_es_ !== '' )
                                ? $descri_es_ : $stela_code['descri'];
                        $new_master_sheet_service->description_EN =
                            ($descri_en_ !== "undefined" && $descri_en_ !== null && $descri_en_ !== '' )
                                ? $descri_en_ : $stela_code['descri'];
                        $new_master_sheet_service->description_PT =
                            ($descri_pt_ !== "undefined" && $descri_pt_ !== null && $descri_pt_ !== '' )
                                ? $descri_pt_ : $stela_code['descri'];
                        $new_master_sheet_service->description_IT =
                            ($descri_it_ !== "undefined" && $descri_it_ !== null && $descri_it_ !== '' )
                                ? $descri_it_ : $stela_code['descri'];
                        $new_master_sheet_service->type_service = $p_service['type'];
                        $new_master_sheet_service->status = 1;
                        $new_master_sheet_service->comment_status = 0;
                        $new_master_sheet_service->comment = "";
                        $new_master_sheet_service->attached = "";
                        $new_master_sheet_service->save();
                    }
                }
            }

            $this->update_numbers( $id );
            $days = MasterSheetDay::where('master_sheet_id',$id)->get();
            foreach ( $days as $day ){
                $this->update_destinations( $day->id );
            }

        });

        $data = [
            'success' => true
        ];

        return Response::json($data);

    }

    public function clone_file($id, Request $request){

        $services = $request->input('services');

        DB::transaction(function () use ($id, $services) {

            $dates_taken = [];

            MasterSheetDay::where('master_sheet_id',$id)->delete();

            foreach ( $services as $service ){

                if( !(isset( $dates_taken[$service['fecin']] )) ){

                    $new_master_sheet_day = new MasterSheetDay();
                    $new_master_sheet_day->master_sheet_id = $id;
                    $new_master_sheet_day->number = 0; // Después se reordena
                    $new_master_sheet_day->name = "";
                    $new_master_sheet_day->destinations = "";
                    $new_master_sheet_day->date_in = $service['fecin'];
                    $new_master_sheet_day->save();

                    $dates_taken[$service['fecin']] = $new_master_sheet_day->id;
                }

                $descri_es_ = $service['descri_es'];
                $descri_en_ = $service['descri_en'];
                $descri_pt_ = $service['descri_pt'];
                $descri_it_ = $service['descri_it'];

                $new_master_sheet_service = new MasterSheetService();
                $new_master_sheet_service->master_sheet_day_id = $dates_taken[$service['fecin']];
                $new_master_sheet_service->service_code_stela = $service['codsvs'];
                $new_master_sheet_service->check_in = $service['horin'];
                $new_master_sheet_service->check_out = $service['horout'];
                $new_master_sheet_service->origin_city =  $service['ciuin'];
                $new_master_sheet_service->destination_city = $service['ciuout'];
                $new_master_sheet_service->includes = "";
                $new_master_sheet_service->description = $service['descri'];
                $new_master_sheet_service->description_ES =
                    ($descri_es_ !== "undefined" && $descri_es_ !== null && $descri_es_ !== '' )
                        ? $descri_es_ : $service['descri'];
                $new_master_sheet_service->description_EN =
                    ($descri_en_ !== "undefined" && $descri_en_ !== null && $descri_en_ !== '' )
                        ? $descri_en_ : $service['descri'];
                $new_master_sheet_service->description_PT =
                    ($descri_pt_ !== "undefined" && $descri_pt_ !== null && $descri_pt_ !== '' )
                        ? $descri_pt_ : $service['descri'];
                $new_master_sheet_service->description_IT =
                    ($descri_it_ !== "undefined" && $descri_it_ !== null && $descri_it_ !== '' )
                        ? $descri_it_ : $service['descri'];
                $new_master_sheet_service->type_service = $service['type'];
                $new_master_sheet_service->status = 0;
                $new_master_sheet_service->comment_status = 0;
                $new_master_sheet_service->comment = "";
                $new_master_sheet_service->attached = "";
                $new_master_sheet_service->save();
            }

            $this->update_numbers( $id );
            $days = MasterSheetDay::where('master_sheet_id',$id)->get();
            foreach ( $days as $day ){
                $this->update_destinations( $day->id );
            }

        });

        $data = [
            'success' => true
        ];

        return Response::json($data);

    }

}
