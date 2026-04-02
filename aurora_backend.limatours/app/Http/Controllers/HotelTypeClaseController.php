<?php

namespace App\Http\Controllers;

use App\ChannelHotel;
use App\Hotel;
use App\HotelPreferentials;
use App\HotelTypeClass;
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

class HotelTypeClaseController extends Controller
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
       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
       
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
    public function destroy($year)
    {

        // HotelTypeClass::where('year', $year)->delete();
        // HotelPreferentials::where('year', $year)->delete();
        // return Response::json(['success' => true]);
    }
}
