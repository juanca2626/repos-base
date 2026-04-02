<?php

namespace App\Http\Controllers;

use App\Package;
use App\PackageChild;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PackageChildrenController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:packagechildren.read')->only('index');
        $this->middleware('permission:packagechildren.create')->only('store');
        $this->middleware('permission:packagechildren.update')->only('update');
        $this->middleware('permission:packagechildren.delete')->only('destroy');
    }

    /**
     * @param $package_id
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index($package_id, Request $request)
    {
        $children = PackageChild::where('package_id', $package_id)->get();

        $data = [
            'data' => $children,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * @param $package_id
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store($package_id, Request $request)
    {

        $check = PackageChild::where('package_id', $request->input('package_id'))->where('has_bed',
            (boolean)$request->input('has_bed'))->count();
        if ($check === 0) {
            $package = Package::find($package_id);
            $package->allow_child = 1;
            $package->save();

            $packageChild = new PackageChild();
            $packageChild->min_age = $request->input('child_min_age');
            $packageChild->max_age = $request->input('child_max_age');
            $packageChild->percentage = $request->input('percentage');
            $packageChild->has_bed = (boolean)$request->input('has_bed');
            $packageChild->status = 1;
            $packageChild->package_id = $request->input('package_id');
            $packageChild->save();
            $response = ['success' => true, 'object_id' => $packageChild->id];
            return Response::json($response);
        }else{
            $response = ['success' => false, 'data' => 'Ya existe la configuracion ingresada'];
            return Response::json($response);
        }

    }

    /**
     * @param $package_id
     * @param $rate_id
     * @return JsonResponse
     */
    public function destroy($package_id, $child_id)
    {
        $child = PackageChild::find($child_id);
        $child->delete();

        return Response::json(['success' => true]);
    }

    public function updateStatus($package_id, $child_id, Request $request)
    {
        $child = PackageChild::find($child_id);
        if ($request->input("status")) {
            $child->status = false;
        } else {
            $child->status = true;
        }
        $child->save();
        return Response::json(['success' => true]);
    }

    public function updatePercentage($id, Request $request)
    {
        $child = PackageChild::find($id);
        $child->percentage = $request->get('percentage');
        $child->save();
        return Response::json(['success' => true]);
    }
}
