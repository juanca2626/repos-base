<?php

namespace App\Http\Controllers;

use App\MasterSheetUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class MasterSheetUsersController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'master_sheet_id' => 'required',
            'user_ids' => 'required',
            'type_permission' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $master_sheet_id = $request->input('master_sheet_id');

            foreach ($request->input('user_ids') as $user_id) {
                $find_relation = MasterSheetUser::where('master_sheet_id', $master_sheet_id)
                    ->where('user_id', $user_id)->first();
                if($find_relation){
                    $find_relation->type_permission = $request->input('type_permission');
                    $find_relation->comment = $request->input('comment');
                    $find_relation->save();
                } else {
                    $master_sheet_user = new MasterSheetUser();
                    $master_sheet_user->master_sheet_id = $master_sheet_id;
                    $master_sheet_user->user_id = $user_id;
                    $master_sheet_user->type_permission = $request->input('type_permission');
                    $master_sheet_user->comment = $request->input('comment');
                    $master_sheet_user->save();
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
            $master_sheet_user = MasterSheetUser::find($id);
            $master_sheet_user->type_permission = $request->input('type_permission');
            $master_sheet_user->comment = $request->input('comment');
            $master_sheet_user->save();
            return Response::json(['success' => true]);
        }
    }

    public function destroy($id){

        MasterSheetUser::find($id)->delete();

        $data = [
            'success' => true
        ];

        return Response::json($data);
    }

}
