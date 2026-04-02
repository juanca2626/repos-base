<?php

namespace App\Http\Controllers;

use App\Note;
use App\SerieNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class NotesController extends Controller
{
    public function index($entity, $entity_id, Request $request)
    {

        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit') ? $request->input('limit') : 50;
        $querySearch = $request->input('query');

        $notes = [];

        if( $entity === 'series' ){
            $notes = SerieNote::where('serie_id', $entity_id);
        }

        $notes = $notes->with(['note.user']);

        if ($querySearch) {
            $notes->whereHas('note', function ($q) use ($querySearch) {
                $q->where('note', 'like', '%'.$querySearch.'%');
                $q->orWhere('title', 'like', '%'.$querySearch.'%');
            });
        }

        $count = $notes->count();

        if ($paging === 1) {
            $notes = $notes->take($limit)->orderBy('created_at', 'desc')->get();
        } else {
            $notes = $notes->skip($limit * ($paging - 1))->take($limit)->orderBy('created_at', 'desc')->get();
        }

        $notes->transform(function($query){
            $query['show_edit'] = false;
            return $query;
        });

        return Response::json([
            'success' => true,
            'data' => $notes,
            'count' => $count
        ]);
    }

    public function store($entity, $entity_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'note' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {
            $note = new Note();
            $note->note = $request->input('note');
            $note->status = 1;
            $note->user_id = Auth::user()->id;
            $note->title = $request->input('title');
            $note->attached = $request->input('attached');
            $note->primary_color = $request->input('primary_color');
            $note->secondary_color = $request->input('secondary_color');
            $note->save();

            if( $entity === 'series' ){
                $new_relation = new SerieNote();
                $new_relation->serie_id = $entity_id;
                $new_relation->note_id = $note->id;
                $new_relation->save();
            }

            return Response::json(['success' => true, 'note_id'=>$note->id]);
        }
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'note' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {
            $note = Note::find($id);
            $note->note = $request->input('note');
            $note->status = 1;
            $note->title = $request->input('title');
            $note->attached = $request->input('attached');
            $note->primary_color = $request->input('primary_color');
            $note->secondary_color = $request->input('secondary_color');
            $note->save();

            return Response::json(['success' => true]);
        }
    }

    public function destroy($entity, $entity_id){

        $r_boolean = false;

        if( $entity === 'series' ){
            $r_boolean = SerieNote::find($entity_id)->delete();
        }

        $data = [
            'success' => $r_boolean
        ];

        return Response::json($data);
    }

    public function get_total($entity, $entity_id){

        $total = 0;

        if( $entity === 'series' ){
            $total = SerieNote::where('serie_id',$entity_id)->count();
        }

        $data = [
            'success' => true,
            'data' => $total
        ];

        return Response::json($data);
    }

}
