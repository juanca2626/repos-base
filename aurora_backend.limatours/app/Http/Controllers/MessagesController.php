<?php

namespace App\Http\Controllers;

use App\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class MessagesController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entity' => 'required',
            'object_id' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $entity = $request->input('entity');
            $object_id = $request->input('object_id');

            $paging = $request->input('page') ? $request->input('page') : 1;
            $limit = $request->input('limit') ? $request->input('limit') : 5;
            $querySearch = $request->input('query');

            $messages = Message::with(['user'])
                ->where('entity',$entity)
                ->where('object_id',$object_id);

            if ($querySearch) {
                $messages->where(function ($query) use ($querySearch) {
                    $query->orWhere('message', 'like', '%' . $querySearch . '%');
                });
            }

            $count = $messages->count();

            if ($paging === 1) {
                $messages = $messages->orderby('created_at', 'desc')
                    ->take($limit)->get();
            } else {
                $messages = $messages->orderby('created_at', 'desc')
                    ->skip($limit * ($paging - 1))->take($limit)->get();
            }

            $messages = $messages->transform(function ($ms) {
                $ms['show_edit'] = false;
                $ms['message_edit'] = $ms['message'];
                $ms['show_delete'] = false;
                $ms['show_reply'] = false;
                return $ms;
            });

            $data = [
                'data' => $messages,
                'count' => $count,
                'success' => true
            ];
        }

        return Response::json($data);

    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entity' => 'required',
            'object_id' => 'required',
            'message' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {
            $message = new Message();
            $message->user_id = Auth::user()->id;
            $message->entity = $request->input('entity');
            $message->object_id = $request->input('object_id');
            $message->message = $request->input('message');
            $message->reply_id = $request->input('reply_id');
            $message->attached = $request->input('attached');
            $message->save();
            return Response::json(['success' => true]);
        }
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {
            $message = Message::find($id);
            $message->message = $request->input('message');
            $message->attached = $request->input('attached');
            $message->save();
            return Response::json(['success' => true]);
        }
    }


    public function destroy($id){

        Message::find($id)->delete();
        Message::where('reply_id',$id)->delete();

        $data = [
            'success' => true
        ];

        return Response::json($data);
    }

}
