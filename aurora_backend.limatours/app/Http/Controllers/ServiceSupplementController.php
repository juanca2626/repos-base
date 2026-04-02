<?php

namespace App\Http\Controllers;

use App\ServiceSupplement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ServiceSupplementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        $supplements = ServiceSupplement::with([
            'supplements' => function ($query) {
                $query->select(['id', 'aurora_code', 'name', 'service_type_id']);
                $query->with([
                    'serviceType' => function ($query) {
                        $query->with([
                            'translations' => function ($query) {
                                $query->select('object_id', 'value');
                                $query->where('type', 'servicetype');
                                $query->where('language_id', 1);
                            },
                        ]);
                    }
                ]);
            }
        ])->where('service_id', $id)->get();
        return Response::json(['success' => true, 'data' => $supplements]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $service_id = $request->post('service_id');
            $object_id = $request->post('object_id');
            $type = $request->post('type');
            $days_to_charge = $request->post('days_to_charge');
            $charge_all_pax = $request->post('charge_all_pax');
            $findService = ServiceSupplement::where('service_id', $service_id)->where('object_id',
                $object_id)->limit(1)->count();
            if ($findService > 0) {
                return Response::json(['success' => false, 'error' => 'El suplemento ya se encuentra agregado']);
            } else {
                $supplement = new ServiceSupplement();
                $supplement->service_id = $service_id;
                $supplement->object_id = $object_id;
                $supplement->type = $type;
                $supplement->days_to_charge = json_encode($days_to_charge);
                if ($type == 'required') {
                    $supplement->charge_all_pax = true;
                } else {
                    $supplement->charge_all_pax = $charge_all_pax;
                }
                $supplement->save();
                return Response::json(['success' => true]);
            }

        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'error' => $exception->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ServiceSupplement  $serviceSupplement
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ServiceSupplement  $serviceSupplement
     * @return \Illuminate\Http\Response
     */
    public function edit(ServiceSupplement $serviceSupplement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ServiceSupplement  $serviceSupplement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $type = $request->post('type');
            $supplement = ServiceSupplement::find($id);
            $supplement->type = $type;
            $supplement->save();
            return Response::json(['success' => true]);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'error' => $exception->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ServiceSupplement  $serviceSupplement
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $supplement = ServiceSupplement::find($request->input('id'));
                $supplement->delete();
            });
            return Response::json(['success' => true]);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'error' => $exception->getMessage()]);
        }


    }

    public function updateChargeAllPax(Request $request, $id)
    {
        try {
            $charge_all_pax = $request->post('charge_all_pax');
            $supplement = ServiceSupplement::find($id);
            if ($charge_all_pax) {
                $supplement->charge_all_pax = 0;
            } else {
                $supplement->charge_all_pax = 1;
            }
            $supplement->save();
            return Response::json(['success' => true]);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'error' => $exception->getMessage()]);
        }
    }

    public function updateDaysToCharge(Request $request, $id)
    {
        try {
            $days_to_charge = $request->post('days_to_charge');
            $supplement = ServiceSupplement::find($id);
            $supplement->days_to_charge = json_encode($days_to_charge);
            $supplement->save();
            return Response::json(['success' => true]);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'error' => $exception->getMessage()]);
        }
    }


}
