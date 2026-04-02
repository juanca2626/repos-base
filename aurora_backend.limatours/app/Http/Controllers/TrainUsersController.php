<?php

namespace App\Http\Controllers;

use App\User;
use App\TrainUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TrainUsersController extends Controller
{
    public function index($train_id, Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('queryCustom');

        $train_users = TrainUser::where('train_id',$train_id)
            ->with(['user.roles'])
            ->with(['user.userType']);

        if ($querySearch) {
            $train_users->whereHas('user', function ($q) use ($querySearch) {
                $q->where('name', 'like', '%' . $querySearch . '%');
                $q->orWhere('code', 'like', '%' . $querySearch . '%');
            });
        }

        $count = $train_users->count();

        if ($paging === 1) {
            $train_users = $train_users->take($limit)->orderBy('created_at', 'desc')->get();
        } else {
            $train_users = $train_users->skip($limit * ($paging - 1))->orderBy('created_at', 'desc')
                ->take($limit)->get();
        }

        $data = [
            'data' => $train_users,
            'count' => $count,
        ];
        return Response::json($data);
    }


    public function store(Request $request)
    {
        $user_id = $request->input('user_id');
        $train_id = $request->input('train_id');
        $code = $request->input('code');

        $train_user = new TrainUser;
        $train_user->user_id = $user_id;
        $train_user->train_id = $train_id;
        $train_user->code = $code;
        if( $train_user->save() ){
            return Response::json(['success' => true]);
        } else {
            return Response::json(['success' => false, "message" => "Error interno"]);
        }
    }

    public function destroy($id)
    {
        $train_user = TrainUser::find($id);

        $train_user->delete();

        return Response::json(['success' => true]);

    }
}
