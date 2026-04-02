<?php

namespace App\Http\Controllers;

use App\ChannelUser;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use jeremykenedy\LaravelRoles\Models\Role;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\Users;

class ChannelUsersController extends Controller
{
    use  Users;

    public function __construct()
    {
        $this->middleware('permission:channelusers.read')->only('index');
        $this->middleware('permission:channelusers.create')->only('store');
        $this->middleware('permission:channelusers.update')->only('update');
        $this->middleware('permission:channelusers.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $channel_id = $request->input("channel_id");

        $users = User::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'user');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
            ,
            'channelUsers'
        ])->channel($channel_id)->get();

        return Response::json(['success' => true, 'data' => $users]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }

            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false]);
        } else {
            $data = [];
           
            $request->request-> add(['user_type_id' => 3]);
            $request->request-> add(['email_verified_at' => Carbon::now()]);
            $request -> merge(['status' => '1']);

            $user = $this->userSave($request);
            
            if (!empty($user) && $user->count() > 0) {
                $channelUser = new ChannelUser();
                $channelUser->channel_id = $request->input('channel_id');
                $channelUser->user_id = $user->id;
                $channelUser->save();
            }
        }
        
        return Response::json(['success' => true]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $user = User::where('id', $id)->with('roles')->first();

        return Response::json(['success' => true, 'data' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = $this->userUpdate($request, $id);
            
        return Response::json(['success' => true]);
    }

    public function updateStatus($id, Request $request)
    {
        $channelUser = User::find($id);
        $channelUser->status = $request->input("status") ? '0' : '1';
        $channelUser->save();
        return Response::json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $user = User::find($id);

        $user->delete();

        return Response::json(['success' => true]);
    }
}
