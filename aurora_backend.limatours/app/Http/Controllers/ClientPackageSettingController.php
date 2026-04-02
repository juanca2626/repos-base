<?php

namespace App\Http\Controllers;

use App\ClientPackageSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ClientPackageSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $client_id = $request->input('client_id');
            $package_id = $request->input('package_id');
            $setting = ClientPackageSetting::where('client_id', $client_id)
                ->where('package_id', $package_id)
                ->get();

            $data = [
                'data' => $setting,
                'success' => true
            ];
            return Response::json($data);
        } catch (\Exception $ex) {
            return Response::json(['error' => $ex->getMessage(), 'success' => false]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_reserve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|integer',
            'package_id' => 'required|integer',
            'reserve_from' => 'required|integer',
            'unit_duration' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'message' => $validator->getMessageBag()]);
        } else {
            try {
                $client_id = $request->input('client_id');
                $package_id = $request->input('package_id');
                $reserve_from = $request->input('reserve_from');
                $unit_duration = $request->input('unit_duration');
                $find = ClientPackageSetting::where('client_id', $client_id)
                    ->where('package_id', $package_id)
                    ->get();
                if (count($find) == 0) {
                    $new = new ClientPackageSetting();
                    $new->client_id = $client_id;
                    $new->package_id = $package_id;
                    $new->reservation_from = $reserve_from;
                    $new->unit_duration_reserve = $unit_duration;
                    $new->save();
                } else {
                    $find = ClientPackageSetting::find($find[0]['id']);
                    $find->reservation_from = $reserve_from;
                    $find->unit_duration_reserve = $unit_duration;
                    $find->save();
                }
                return Response::json(['success' => true]);
            } catch (\Exception $e) {
                return Response::json(['success' => false, 'message' => $e->getMessage()]);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ClientPackageSetting  $clientPackageSetting
     * @return \Illuminate\Http\Response
     */
    public function show(ClientPackageSetting $clientPackageSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ClientPackageSetting  $clientPackageSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(ClientPackageSetting $clientPackageSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientPackageSetting  $clientPackageSetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClientPackageSetting $clientPackageSetting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ClientPackageSetting  $clientPackageSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClientPackageSetting $clientPackageSetting)
    {
        //
    }
}
