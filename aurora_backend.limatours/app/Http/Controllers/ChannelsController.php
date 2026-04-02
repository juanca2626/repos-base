<?php

namespace App\Http\Controllers;

use App\Channel;
use App\RatesPlans;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ChannelsController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:channels.read')->only('index');
        $this->middleware('permission:channels.create')->only('store');
        $this->middleware('permission:channels.update')->only('update');
        $this->middleware('permission:channels.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index()
    {
        $channels = Channel::all();
        return Response::json(['success' => true, 'data' => $channels]);
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
            'name' => 'required|unique:channels,name'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false]);
        } else {
            $channels = new Channel();
            $channels->name = $request->input('name');
            $channels->code = $request->input('code');
            $channels->status = $request->input('status');
            $channels->save();

            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $channel = Channel::find($id);

        return Response::json(['success' => true, 'data' => $channel]);
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:channels,name,' . $id
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false]);
        } else {
            $channel = Channel::find($id);
            $channel->name = $request->input('name');
            $channel->code = $request->input('code');
            $channel->status = $request->input('status');
            $channel->save();
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $channel = Channel::find($id);

        $channel->delete();

        return Response::json(['success' => true]);
    }

    public function selected()
    {
        $channels = Channel::select('id', 'name')
            ->where('status', 1)
            ->get();
        $result = [];
        foreach ($channels as $channel) {
            if(in_array($channel->id, [1,6])){
                    array_push($result, ['text' => $channel->name, 'value' => $channel->id]);
            }
            
        }
        
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function selectBox()
    {
        $channels = Channel::select('id', 'name')
            ->where('status', 1)
            ->get();
        $result = [];
        foreach ($channels as $channel) {
            array_push($result, ['text' => $channel->name, 'value' => $channel->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function selectHotelsBox()
    {
        $channels = Channel::select('id', 'name')
                    ->where('status', 1)
                    ->get();


        $result = [];
        foreach ($channels as $channel) {
            array_push($result, ['text' => $channel->name, 'value' => $channel->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }
    public function updateStatus($id, Request $request)
    {
        $channels = Channel::find($id);
        if ($request->input("status")) {
            $channels->status = false;
        } else {
            $channels->status = true;
        }
        $channels->save();
        return Response::json(['success' => true]);
    }

    public function getChannelsInventory()
    {

        $channels = Channel::select('id', 'name')
            ->whereHas('hotels', function ($q)  {
                $q->where('state', '1');
            })
            ->where('status', 1)
            ->where('id', '!=', 1)
            ->get();

        return Response::json(['success' => true, 'data' => $channels]);

    }
    public function getChannelIdByRatePlan($rate_plan_id){
        $rate_plan = RatesPlans::find($rate_plan_id);

        return Response::json(['success' => true, 'data' => $rate_plan->channel_id]);
    }

}
