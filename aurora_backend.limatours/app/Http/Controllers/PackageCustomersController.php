<?php

namespace App\Http\Controllers;

use App\PackageCustomer;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class PackageCustomersController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:packagecustomers.read')->only('index');
        $this->middleware('permission:packagecustomers.create')->only('store');
        $this->middleware('permission:packagecustomers.update')->only('update');
        $this->middleware('permission:packagecustomers.delete')->only('destroy');
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
        $sorting = $request->input('orderBy');
        $sortOrder = $request->input('ascending');

        $customers = PackageCustomer::with(['client'])->where('package_id', $package_id);

        $count = $customers->count();

        if ($querySearch) {
            $customers->where(function ($query) use ($querySearch) {

                $query->orWhereHas('client', function ($q) use ($querySearch) {

                    $q->where('name', 'like', '%' . $querySearch . '%');
                });
            });
        }

        if ($sorting) {
            $asc = $sortOrder == 1 ? 'asc' : 'desc';
            $customers->orderBy($sorting, $asc);
        } else {
            $customers->orderBy('created_at', 'desc');
        }

        if ($paging == 1) {
            $customers = $customers->take($limit)->get();
        } else {
            $customers = $customers->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $customers,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|unique:package_customers,client_id,NULL,id,package_id,' . $request->input('package_id')
        ]);
        if ($validator->fails()) {
            $response = ['success' => false];
        } else {
            $packageCustomer = new PackageCustomer();
            $packageCustomer->client_id = $request->input('customer_id');
            $packageCustomer->status = 1;
            $packageCustomer->package_id = $request->input('package_id');
            $packageCustomer->save();

            $response = ['success' => true, 'object_id' => $packageCustomer->id];
        }
        return Response::json($response);
    }

    /**
     * @param $package_id
     * @param $rate_id
     * @return JsonResponse
     */
    public function destroy($package_id, $customer_id)
    {
        $customer = PackageCustomer::find($customer_id);
        $customer->delete();

        return Response::json(['success' => true]);
    }

    public function updateStatus($package_id, $customer_id, Request $request)
    {
        $customer = PackageCustomer::find($customer_id);
        if ($request->input("status")) {
            $customer->status = false;
        } else {
            $customer->status = true;
        }
        $customer->save();
        return Response::json(['success' => true]);
    }

}
