<?php

namespace App\Http\Controllers;
use App\User;
use App\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class UserNotificationsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            $response = ['success' => false];
        } else {
            $user_noti = new UserNotification();
            $user_noti->user_id = Auth::user()->id;
            $user_noti->token = $request->input('token');
            $user_noti->status = 1;

            if( $user_noti->save() ){
                $response = ['success' => true, 'object_id' => $user_noti->id];
            } else {
                $response = ['success' => false];
            }

        }

        return Response::json($response);
    }

    public function search(){
        $response = UserNotification::all();
        return Response::json($response);
    }

}
