<?php

namespace App\Http\Controllers;

use App\PackageCancellationPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class PackageCancellationPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $policies = PackageCancellationPolicy::orderBy('pax_from', 'asc');
        $count = $policies->count();

        if ($paging === 1) {
            $policies = $policies->take($limit)->get();
        } else {
            $policies = $policies->skip($limit * ($paging - 1))->take($limit)->get();
        }


        $data = [
            'data' => $policies,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $pax_from = $request->post('pax_from');
        $pax_to = $request->post('pax_to');
        $day_from = $request->post('day_from');
        $day_to = $request->post('day_to');
        $cancellation_fees = $request->post('cancellation_fees');

        $validate_duplicate = $this->validateRangePax($pax_from, $pax_to);
        if ($validate_duplicate) {
            return Response::json(['success' => false, 'error' => 'DUPLICATE_PAX']);
        } else {
            $new = new PackageCancellationPolicy();
            $new->pax_from = $pax_from;
            $new->pax_to = $pax_to;
            $new->day_from = $day_from;
            $new->day_to = $day_to;
            $new->cancellation_fees = $cancellation_fees;
            $new->save();
            return Response::json(['success' => true]);
        }
    }

    public function validateRangePax($pax_from, $pax_to, $id = null)
    {
        if($id !== null){
            $query = DB::select(
                DB::raw("select * from package_cancellation_policies where
                        (
                            (
                                ('$pax_from' >= pax_from and '$pax_from' <= pax_to) or
		                        ('$pax_to' >= pax_from and '$pax_to' <= pax_to)
                            ) or
                            (
                                (pax_from >= '$pax_from' and pax_to >= '$pax_from') and
		                        (pax_from <= '$pax_to' and pax_to <= '$pax_to')
                            )
                        ) and id != '$id' and deleted_at is null limit 1
                        "));
        }else{
            $query = DB::select(
                DB::raw("select * from package_cancellation_policies where
                        (
                            (
                                ('$pax_from' >= pax_from and '$pax_from' <= pax_to) or
		                        ('$pax_to' >= pax_from and '$pax_to' <= pax_to)
                            ) or
                            (
                                (pax_from >= '$pax_from' and pax_to >= '$pax_from') and
		                        (pax_from <= '$pax_to' and pax_to <= '$pax_to')
                            )
                        ) and deleted_at is null limit 1
                        "));
        }

        if (count($query) > 0) {
            $response = true;
        } else {
            $response = false;
        }

        return $response;

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PackageCancellationPolicy  $packageCancellationPolicy
     * @return \Illuminate\Http\Response
     */
    public function show($cancellation_policy)
    {
        $cancellation = PackageCancellationPolicy::find($cancellation_policy);
        return Response::json(['success' => true, 'data' => $cancellation]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PackageCancellationPolicy  $packageCancellationPolicy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $cancellation_policy)
    {
        $pax_from = $request->post('pax_from');
        $pax_from = $request->post('pax_from');
        $pax_to = $request->post('pax_to');
        $day_from = $request->post('day_from');
        $day_to = $request->post('day_to');
        $cancellation_fees = $request->post('cancellation_fees');

        $validate_duplicate = $this->validateRangePax($pax_from, $pax_to, $cancellation_policy);
        if ($validate_duplicate) {
            return Response::json(['success' => false, 'error' => 'DUPLICATE_PAX']);
        } else {
            $new = PackageCancellationPolicy::find($cancellation_policy);
            $new->pax_from = $pax_from;
            $new->pax_to = $pax_to;
            $new->day_from = $day_from;
            $new->day_to = $day_to;
            $new->cancellation_fees = $cancellation_fees;
            $new->save();
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PackageCancellationPolicy  $packageCancellationPolicy
     * @return \Illuminate\Http\Response
     */
    public function destroy($cancellation_policy)
    {
//        throw new \Exception(json_encode($cancellation_policy));
        $cancellation = PackageCancellationPolicy::find($cancellation_policy);
        $cancellation->delete();
        return Response::json(['success' => true]);
    }
}
