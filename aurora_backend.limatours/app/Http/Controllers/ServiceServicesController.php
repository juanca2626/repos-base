<?php

namespace App\Http\Controllers;

use App\Client;
use App\Service;
use App\ServiceCategory;
use App\ServiceSchedule;
use App\ServiceType;
use App\Http\Traits\Translations;
use App\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ServiceServicesController extends Controller
{
    use Translations;
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clients(Request $request)
    {

        $querySearch = strtoupper($request->input('query'));

        $customers = Client::where(function ($query) use ($querySearch) {
                $query->orWhere('code', 'like', '%' . $querySearch . '%');
                $query->orWhere('name', 'like', '%' . $querySearch . '%');
            })
            ->take(10)->get();

        return Response::json(['success' => true, 'data' => $customers]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function cities(Request $request){

        $lang = ( $request->input('lang') != '' ) ? $request->input('lang') : 'ES';

        $cities = City::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'city');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'state.translations' => function ($query) use ($lang) {
                $query->where('type', 'state');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();

        return Response::json(['success' => true, 'data' => $cities]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories(Request $request){

        $lang = ( $request->input('lang') != '' ) ? $request->input('lang') : 'ES';
        $categories = ServiceCategory::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'servicecategory');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $categories]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function types(Request $request){

        $lang = ( $request->input('lang') != '' ) ? $request->input('lang') : 'ES';
        $types = ServiceType::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'servicetype');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $types]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function services(Request $request)
    {
        $lang = ( $request->input('lang') != '' ) ? $request->input('lang') : 'ES';

        $services = Service::with([
            'serviceType.translations' => function ($query) use ($lang) {
                $query->where('type', 'servicetype');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'serviceSubCategory.serviceCategories.translations' => function ($query) use ($lang) {
                $query->where('type', 'servicecategory');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $services]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectBox(Request $request)
    {
        $querySearch = strtoupper($request->input('query'));

        $services = Service::where(function ($query) use ($querySearch) {
            $query->orWhere('aurora_code', 'like', '%' . $querySearch . '%');
            $query->orWhere('name', 'like', '%' . $querySearch . '%');
        })->take(10)->get();

        return Response::json(['success' => true, 'data' => $services]);
    }

    /**
     * @param $service_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function schedules($service_id, Request $request)
    {
        $schedules = ServiceSchedule::with([
            'servicesScheduleDetail'
        ])->where('service_id', $service_id)->get();

        $data = [
            'data' => $schedules,
            'success' => true
        ];

        return Response::json($data);
    }

}
