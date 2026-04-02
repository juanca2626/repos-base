<?php

namespace App\Http\Controllers;

use App\ChannelHotel;
use App\Hotel;
use App\Language;
use App\ConfigMarkup;
use App\DateRangeHotel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
class ConfigMarkupsController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:tagservices.read')->only('index');
        // $this->middleware('permission:tagservices.create')->only('store');
        // $this->middleware('permission:tagservices.update')->only('update');
        // $this->middleware('permission:tagservices.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try
        {
            $type = $request->__get('type'); $lang = $request->__get('lang');
            $config_markups = ConfigMarkup::where('type', '=', $type)->where('status', '>', 0);

            if($type == 1)
            {
                $config_markups = $config_markups->with(['service_category.translations' => function ($query) use ($lang) {
                    $lang = Language::where('iso', '=', $lang)->first();
                    $query->where('language_id', '=', $lang->id);
                }]);
            }

            if($type == 2)
            {
                $config_markups = $config_markups->with(['hotel_category.translations' => function ($query) use ($lang) {
                    $lang = Language::where('iso', '=', $lang)->first();
                    $query->where('language_id', '=', $lang->id);
                }]);
            }

            $config_markups = $config_markups->get();
            return Response::json(['success' => true, 'data' => $config_markups]);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try
        {
            $form = (object) $request->__get('form');
            $type = $request->__get('type');

            $count = ConfigMarkup::where('type', '=', $type)
                ->where('category_id', '=', $form->category_id)
                ->where('status', '<>', 2)
                ->where('status', '<>', 0)
                ->count();

            if($count == 0 || @$form->id > 0)
            {
                if(@$form->id > 0)
                {
                    $config_markup = ConfigMarkup::find($form->id);

                    if($config_markup->markup != $form->markup)
                    {
                        $config_markup->prev = $config_markup->markup;
                    }
                }
                else
                {
                    $config_markup = new ConfigMarkup;
                    $config_markup->type = $type;
                    $config_markup->category_id = $form->category_id;
                }

                $config_markup->markup = $form->markup;
                $config_markup->status = 1; // Pendiente..
                $config_markup->percent = 0;
                $config_markup->save();

                return response()->json([
                    'success' => true,
                    'config_markup' => $config_markup
                ]);
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'message' => 'La configuración de protección ya existe'
                ]);
            }
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try
        {
            $config_markup = ConfigMarkup::find($id);
            $config_markup->status = 0;
            $config_markup->save();

            return response()->json([
                'success' => true,
                'config_markup' => $config_markup
            ]);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function getHotelMarkups(Request $request){

        $year = $request->get('year');
        
        $results = DateRangeHotel::whereYear('date_from', $year)
            ->where('flag_migrate', 1)
            ->groupBy('rate_plan_id')
            ->get();
        $data = [];
        foreach ($results as $result) {
            $hotel = Hotel::find($result->hotel_id);
            $channel_hotel = ChannelHotel::where('hotel_id', $result->hotel_id)->where('channel_id', 1)->first();
            $data[] = [
                'code' => $channel_hotel->code,
                'hotel' => $hotel->name,
                'hotel_id' => $result->hotel_id,
                'url_plan_rate' => url('/#/hotels/' . $result->hotel_id . '/manage_hotel/rates/rates/cost/edit/' . $result->rate_plan_id),
                'created_at' => $result->created_at
            ];
        }
        usort($data, function ($a, $b) {
            return $a['hotel_id'] - $b['hotel_id'];
        });
        return Excel::download(new \App\Exports\ConfigMarkupHotelsExport($data),
                    'hotel-markups-' . $year . '' . '.xlsx');
    }

}
