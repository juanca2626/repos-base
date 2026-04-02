<?php

namespace App\Http\Controllers;

use App\SerieUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class SerieUsersController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serie_id' => 'required',
            'user_ids' => 'required',
            'type_permission' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $serie_id = $request->input('serie_id');

            foreach ($request->input('user_ids') as $user_id) {
                $find_relation = SerieUser::where('serie_id', $serie_id)
                    ->where('user_id', $user_id)->first();
                if($find_relation){
                    $find_relation->type_permission = $request->input('type_permission');
                    $find_relation->comment = $request->input('comment');
                    $find_relation->save();
                } else {
                    $serie_user = new SerieUser();
                    $serie_user->serie_id = $serie_id;
                    $serie_user->user_id = $user_id;
                    $serie_user->type_permission = $request->input('type_permission');
                    $serie_user->comment = $request->input('comment');
                    $serie_user->save();
                }
            }
            return Response::json(['success' => true]);
        }
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_permission' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {
            $serie_user = SerieUser::find($id);
            $serie_user->type_permission = $request->input('type_permission');
            $serie_user->comment = $request->input('comment');
            $serie_user->save();
            return Response::json(['success' => true]);
        }
    }

    public function destroy($id){

        SerieUser::find($id)->delete();

        $data = [
            'success' => true
        ];

        return Response::json($data);
    }

}
