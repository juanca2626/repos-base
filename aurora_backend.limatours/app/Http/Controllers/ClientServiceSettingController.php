<?php

namespace App\Http\Controllers;

use App\ClientServiceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ClientServiceSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $client_id = $request->input('client_id');
            $service_id = $request->input('service_id');
            $setting = ClientServiceSetting::where('client_id', $client_id)
                ->where('service_id', $service_id)
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_reserve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|integer',
            'service_id' => 'required|integer',
            'reserve_from' => 'required|integer',
            'unit_duration' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'message' => $validator->getMessageBag()]);
        } else {
            try {
                $client_id = $request->input('client_id');
                $service_id = $request->input('service_id');
                $reserve_from = $request->input('reserve_from');
                $unit_duration = $request->input('unit_duration');
                $find = ClientServiceSetting::where('client_id', $client_id)
                    ->where('service_id', $service_id)
                    ->get();
                if (count($find) == 0) {
                    $new = new ClientServiceSetting();
                    $new->client_id = $client_id;
                    $new->service_id = $service_id;
                    $new->reservation_from = $reserve_from;
                    $new->unit_duration_reserve = $unit_duration;
                    $new->save();
                } else {
                    $find = ClientServiceSetting::find($find[0]['id']);
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
     * @param  \App\ClientServiceSetting  $clientServiceSetting
     * @return \Illuminate\Http\Response
     */
    public function show(ClientServiceSetting $clientServiceSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ClientServiceSetting  $clientServiceSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(ClientServiceSetting $clientServiceSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientServiceSetting  $clientServiceSetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClientServiceSetting $clientServiceSetting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ClientServiceSetting  $clientServiceSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClientServiceSetting $clientServiceSetting)
    {
        //
    }
}
