<?php

namespace App\Http\Controllers;

use App\PackageRate;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class PackageRatesController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:packagerates.read')->only('index');
        $this->middleware('permission:packagerates.create')->only('store');
        $this->middleware('permission:packagerates.update')->only('update');
        $this->middleware('permission:packagerates.delete')->only('destroy');
    }

    /**
     * @param $package_id
     * @param Request $request
     * @return JsonResponse
     */
    public function index($package_id, Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');
        $sorting = $request->input('orderBy');
        $sortOrder = $request->input('ascending');

        $rates = PackageRate::with([
            'type_class.translations' => function ($query) use ($lang) {
                $query->where('type', 'typeclass');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'service_type.translations' => function ($query) use ($lang) {
                $query->where('type', 'servicetype');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('package_id', $package_id);

        $count = $rates->count();

        if ($querySearch) {
            $rates->where(function ($query) use ($querySearch) {
                $query->orWhere('reference_number', 'like', '%' . $querySearch . '%');
                $query->orWhere('simple', 'like', '%' . $querySearch . '%');
                $query->orWhere('double', 'like', '%' . $querySearch . '%');
                $query->orWhere('triple', 'like', '%' . $querySearch . '%');
                $query->orWhere('boy', 'like', '%' . $querySearch . '%');
                $query->orWhere('infant', 'like', '%' . $querySearch . '%');

                $query->orWhereHas('service_type.translations', function ($q) use ($querySearch) {

                    $q->where('value', 'like', '%' . $querySearch . '%');
                });
                $query->orWhereHas('type_class.translations', function ($q) use ($querySearch) {

                    $q->where('value', 'like', '%' . $querySearch . '%');
                });
            });
        }

        if ($sorting) {
            $asc = $sortOrder == 1 ? 'asc' : 'desc';
            $rates->orderBy($sorting, $asc);
        } else {
            $rates->orderBy('created_at', 'desc');
        }

        if ($paging == 1) {
            $rates = $rates->take($limit)->get();
        } else {
            $rates = $rates->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $rates,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    /**.
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stela_code' => 'required|unique:package_rates,reference_number'
        ]);

        if ($validator->fails()) {
            $response = ['success' => false];
        } else {
            $packageRate = new PackageRate();
            $packageRate->reference_number = $request->input('stela_code');
            $packageRate->simple = $request->input('simple');
            $packageRate->double = $request->input('double');
            $packageRate->triple = $request->input('triple');
            $packageRate->boy = $request->input('boy');
            $packageRate->infant = $request->input('infant');
            $packageRate->date_from =
                date("Y-m-d", strtotime(str_replace('/', '-', $request->input('date_from'))));
            $packageRate->date_to =
                date("Y-m-d", strtotime(str_replace('/', '-', $request->input('date_to'))));
            $packageRate->package_id = $request->input('package_id');
            $packageRate->type_class_id = $request->input('type_class');
            $packageRate->service_type_id = $request->input('service_type');
            $packageRate->save();

            $response = ['success' => true, 'object_id' => $packageRate->id];
        }

        return Response::json($response);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($package_id, $rate_id)
    {
        $rate = PackageRate::find($rate_id);

        return Response::json(['success' => true, 'data' => $rate]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update($package_id, $rate_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stela_code' => 'required|unique:package_rates,reference_number,' . $rate_id
        ]);

        if ($validator->fails()) {
            $response = ['success' => false];
        } else {
            $packageRate = PackageRate::find($rate_id);
            $packageRate->reference_number = $request->input('stela_code');
            $packageRate->simple = $request->input('simple');
            $packageRate->double = $request->input('double');
            $packageRate->triple = $request->input('triple');
            $packageRate->boy = $request->input('boy');
            $packageRate->infant = $request->input('infant');
            $packageRate->date_from =
                date("Y-m-d", strtotime(str_replace('/', '-', $request->input('date_from'))));
            $packageRate->date_to =
                date("Y-m-d", strtotime(str_replace('/', '-', $request->input('date_to'))));
            $packageRate->package_id = $request->input('package_id');
            $packageRate->type_class_id = $request->input('type_class');
            $packageRate->service_type_id = $request->input('service_type');
            $packageRate->save();

            $response = ['success' => true, 'object_id' => $packageRate->id];
        }
        return Response::json($response);
    }

    /**
     * @param $package_id
     * @param $rate_id
     * @return JsonResponse
     */
    public function destroy($package_id, $rate_id)
    {
        $rate = PackageRate::find($rate_id);
        $rate->delete();

        return Response::json(['success' => true]);

    }
}
