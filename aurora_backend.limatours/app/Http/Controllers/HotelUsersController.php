<?php

namespace App\Http\Controllers;

use App\ChannelHotel;
use App\Hotel;
use App\HotelUser;
use App\User;
use Carbon\Carbon;
use DB;
use Mail;
use App\Mail\NotificationHotelUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use jeremykenedy\LaravelRoles\Models\Role;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\Users;

class HotelUsersController extends Controller
{
    use  Users;

    /**
     * @var int
     */
    private $default_role_id = 10;
    private $default_user_type_id = 2;

    public function __construct()
    {
        $this->middleware('permission:hotelusers.read')->only('index');
        $this->middleware('permission:hotelusers.create')->only('store');
        $this->middleware('permission:hotelusers.update')->only('update');
        $this->middleware('permission:hotelusers.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $hotel_id = $request->input("hotel_id");

        $hotel = ChannelHotel::where('hotel_id', $hotel_id)->where('channel_id', 1)->first();

        $users = User::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'user');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with('hotelUsers')->where('code', $hotel->code)->get();

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
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'code' => 'required|unique:users',
            'password' => 'required'
        ], [
            'required' => 'El :attribute field campo es requerido',
            'unique' => 'El codigo ya existe'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'errors' => $validator->errors()]);
        } else {

            $request->request->add(['user_type_id' => $this->default_user_type_id]);
            $request->request->add(['email_verified_at' => Carbon::now()]);
            $request->merge(['status' => 1]);

            $user = $this->userSave($request);

            if (!empty($user) && $user->count() > 0) {
                $hotelUser = new HotelUser();
                $hotelUser->hotel_id = $request->input('hotel_id');
                $hotelUser->user_id = $user->id;
                $hotelUser->range = $request->input('range');

                if ($hotelUser->save()) {
                    $data [] = [
                        'name' => $user->name,
                        'password' => $request->input('password'),
                        'code' => $user->code
                    ];
                    //Mail::to($user->email)->send(new NotificationHotelUser($data));
                }
            }

            $user = config('roles.defaultUserModel')::find($user->id);
            $role = Role::find($this->default_role_id);
            $user->attachRole($role);

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
        $arrayErrors = [];
        $countErrors = 0;
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:6|unique:users,code,' . $id,
            'name' => 'required',
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
            $request->request->add(['user_type_id' => $this->default_user_type_id]);
            $user = $this->userUpdate($request, $id);
            if (!empty($user) && $user->count() > 0) {
                $hotelUser = HotelUser::where('user_id', $user->id)->first();
                if ($hotelUser) {
                    $hotelUser->range = 0;
                    $hotelUser->save();
                } else {
                    $newHotelUser = new HotelUser();
                    $newHotelUser->range = 0;
                    $newHotelUser->user_id = $user->id;
                    $newHotelUser->hotel_id = $request->input('hotel_id');
                    $newHotelUser->save();
                }

            }

            $user = config('roles.defaultUserModel')::find($user->id);
            $user->detachAllRoles();
            $role = Role::find($this->default_role_id);
            $user->attachRole($role);

            return Response::json(['success' => true]);
        }
    }

    public function updateStatus($id, Request $request)
    {
        $hotel = User::find($id);
        $hotel->status = $request->input("status") ? '0' : '1';
        $hotel->save();
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
