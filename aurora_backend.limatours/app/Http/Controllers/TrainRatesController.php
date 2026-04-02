<?php

namespace App\Http\Controllers;

use App\TrainRate;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TrainRatesController extends Controller
{
    use Translations;

    public function index($train_template_id, Request $request)
    {
        $rates = TrainRate::select(
            'id',
            'name',
            'train_template_id'
        )
            ->where('train_template_id', $train_template_id)
            ->get();

        return Response::json(['success' => true, 'data' => $rates]);
    }

    /**
     * @param $train_template_id
     * @param Request $request
     * @return JsonResponse
     */
    public function store($train_template_id, Request $request)
    {
        $train_rate = new TrainRate();
        $train_rate->name = $request->input('name');
        $train_rate->status = true;
        $train_rate->train_template_id = $train_template_id;
        $train_rate->save();

        $this->saveTranslation($request->input("translations"), 'trainrate', $train_rate->id);

        return Response::json(['success' => true, 'rate_plan' => $train_rate->id]);
    }

    public function show($rateID, Request $request)
    {
        $ratesPlans = TrainRate::with('translations')
            ->where('id', $rateID)->first();

        return Response::json(['success' => true, 'data' => $ratesPlans]);
    }

    public function update($rateID, Request $request)
    {
        $ratesPlan = TrainRate::find($rateID);
        $ratesPlan->name = $request->input('name');
        $ratesPlan->status = true;
        $ratesPlan->save();

        $this->saveTranslation($request->input("translations"), 'trainrate', $ratesPlan->id);

        return Response::json([
            'success' => true,
            'rate_plan' => $ratesPlan->id
        ]);
    }


    public function destroy( $rateID, Request $request)
    {
        TrainRate::find($rateID)->delete();
        return Response::json(['success' => true]);
    }

    public function selectBox($train_template_id, Request $request)
    {
        $rates = TrainRate::select('id', 'name')->where('train_template_id', $train_template_id)
            ->where('status', 1)->get();

        return Response::json(['success' => true, 'data' => $rates]);
    }

}
