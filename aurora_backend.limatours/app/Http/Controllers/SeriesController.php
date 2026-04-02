<?php

namespace App\Http\Controllers;

use App\Serie;
use App\SerieCategory;
use App\SerieDeparture;
use App\SerieRange;
use App\SerieUser;
use App\Http\Traits\Serie as SerieTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class SeriesController extends Controller
{
    use SerieTrait;

    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit') ? $request->input('limit') : 5;
        $querySearch = $request->input('query');

        $serie_ids_shared = SerieUser::where('user_id', Auth::user()->id)->pluck('serie_id');

        $series = Serie::with(['user'])
            ->withCount(['users'])
            ->withCount(['messages'])
            ->where(function($query)use($serie_ids_shared){
                $query->where('user_id', Auth::user()->id)
                    ->orWhereIn('id', $serie_ids_shared);
            });

        if ($querySearch) {
            $series->where(function ($query) use ($querySearch) {
                $query->orWhere('name', 'like', '%'.$querySearch.'%');
            });
        }

        $count = $series->count();

        if ($paging === 1) {
            $series = $series->take($limit)->get();
        } else {
            $series = $series->skip($limit * ($paging - 1))->take($limit)->get();
        }

        // Es el permiso propio, será [] si es q es el autor
        $series = $series->transform(function ($ms) {
            $ms['permission'] = collect();
            $query_user = SerieUser::where('serie_id', $ms['id'])
                ->where('user_id', Auth::user()->id)->first();
            if ($query_user) {
                $ms['permission']->add($query_user);
            }
            return $ms;
        });

        $data = [
            'data' => $series,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);

    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'date_start' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {
            $parse_date_start =
                Carbon::createFromFormat('d/m/Y', $request->input('date_start'))->format('Y-m-d');
            $serie = new Serie();
            $serie->name = $request->input('name');
            $serie->date_start = $parse_date_start;
            $serie->comment = $request->input('comment');
            $serie->user_id = Auth::user()->id;
            $serie->save();
            return Response::json(['success' => true, 'serie_id'=>$serie->id]);
        }
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'date_start' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {
            $parse_date_start =
                Carbon::createFromFormat('d/m/Y', $request->input('date_start'))->format('Y-m-d');
            $serie = Serie::find($id);
            $serie->name = $request->input('name');
            $serie->date_start = $parse_date_start;
            $serie->comment = $request->input('comment');
            $serie->save();
            return Response::json(['success' => true]);
        }
    }

    public function show($id){

        $serie = Serie::with(['user', 'users'])
            ->withCount(['users'])
            ->withCount(['messages'])
            ->find($id);

        $data = [
            'success' => true,
            'data' => $serie
        ];

        return Response::json($data);
    }

    public function destroy($id){

        Serie::find($id)->delete();

        $data = [
            'success' => true
        ];

        return Response::json($data);
    }

    public function get_users($id)
    {
        $serie_users = SerieUser::where('serie_id', $id)
            ->with('user')->get();

        return Response::json([
            'success' => true,
            'data' => $serie_users
        ]);
    }

    public function import_master_sheet($master_sheet_id, Request $request){

        $validator = Validator::make($request->all(), [
            'include_messages' => 'required',
            'categories' => 'required',
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $name = $request->input('name');
            $categories = $request->input('categories');
            $include_messages = $request->input('include_messages');

            $data = $this->do_import_master_sheet($master_sheet_id, null, $name, $categories, $include_messages);

            $data['serie_id'] = Serie::orderBy('id', 'desc')->first()->id;

        }

        return Response::json($data);
    }

    /*
     * Requiere previamente usar la function destroy_content
    */
    public function import_master_sheet_update($serie_id, $master_sheet_id, Request $request){

        $validator = Validator::make($request->all(), [
            'include_messages' => 'required',
            'categories' => 'required',
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $name = $request->input('name');
            $categories = $request->input('categories');
            $include_messages = $request->input('include_messages');

            $data = $this->do_import_master_sheet($master_sheet_id, $serie_id, $name, $categories, $include_messages);

        }

        return Response::json($data);
    }

    public function destroy_content($id){

        DB::transaction(function () use ($id) {
            SerieCategory::where('serie_id', $id)->delete();
//            SerieDeparture::where('serie_id', $id)->delete();
//            SerieRange::where('serie_id', $id)->delete();
        });

        $data = [
            'success' => true
        ];

        return Response::json($data);
    }


    public function clone($serie_id_clone, Request $request){

        $validator = Validator::make($request->all(), [
            'include_messages' => 'required',
            'include_notes' => 'required',
            'include_reminders' => 'required',
            'name' => 'required',
            'categories' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $name = $request->input('name');
            $categories = $request->input('categories');
            $include_messages = $request->input('include_messages');
            $include_notes = $request->input('include_notes');
            $include_reminders = $request->input('include_reminders');

            $data = $this->do_clone($serie_id_clone, null, $name, $categories, $include_messages, $include_notes,
                $include_reminders);
            $data['serie_id'] = Serie::orderBy('id', 'desc')->first()->id;
        }

        return Response::json($data);
    }

    /*
     * Requiere previamente usar la function destroy_content
    */
    public function clone_update($serie_id, $serie_id_clone, Request $request){

        $validator = Validator::make($request->all(), [
            'include_messages' => 'required',
            'include_notes' => 'required',
            'include_reminders' => 'required',
            'name' => 'required',
            'categories' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $name = $request->input('name');
            $categories = $request->input('categories');
            $include_messages = $request->input('include_messages');
            $include_notes = $request->input('include_notes');
            $include_reminders = $request->input('include_reminders');

            $data = $this->do_clone($serie_id_clone, $serie_id, $name, $categories, $include_messages, $include_notes,
                $include_reminders);

        }

        return Response::json($data);
    }

    public function update_see_previous_messages($id, Request $request){

        $see_previous_messages = $request->input('see_previous_messages');
        $serie = Serie::find($id);

        $serie->see_previous_messages = $see_previous_messages;
        $serie->save();

        $data = [
            'success' => true
        ];

        return Response::json($data);
    }

    public function getSeriesALl()
    {
        $series = Serie::all(['id','code','name']);
        return Response::json([
            'success' => true,
            'data' => $series
        ]);
    }

}
