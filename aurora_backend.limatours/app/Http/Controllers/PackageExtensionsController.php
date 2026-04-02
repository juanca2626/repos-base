<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Package;
use App\PackageExtension;
use Illuminate\Support\Facades\Response;

class PackageExtensionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:packages.read')->only('index');
        $this->middleware('permission:packages.create')->only('store');
        $this->middleware('permission:packages.update')->only('update');
        $this->middleware('permission:packages.delete')->only('delete');
    }

    public function index($package_id, Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');

        $package_extensions = PackageExtension::
            with(['extension.translations'=> function ($query) use ($lang) {
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }])
            ->where('package_id', $package_id);


            if ($querySearch) {
                $package_extensions->where(function ($query) use ($querySearch) {
                    $query->orWhereHas('extension', function ($q) use ($querySearch) {

                        $q->where('code', 'like', '%'.$querySearch.'%');
                    });

                    $query->orWhereHas('extension.translations', function ($q) use ($querySearch) {

                        $q->where('name', 'like', '%'.$querySearch.'%');
                    });
                });
            }

            $count = $package_extensions->count();

            if ($paging === 1) {
                $package_extensions = $package_extensions->take($limit)->get();
            } else {
                $package_extensions = $package_extensions->skip($limit * ($paging - 1))->take($limit)->get();
            }

            if ($count > 0) {
                for ($j = 0; $j < count($package_extensions); $j++) {
                    $package_extensions[$j]["selected"] = false;
                }
            }

            $data = [
                'data' => $package_extensions,
                'count' => $count,
                'success' => true
            ];

        return Response::json($data);
    }

    public function store($package_id, Request $request)
    {
        $data = $request->input('data');
        $package_extension = new PackageExtension();
        $package_extension->package_id = $package_id;
        $package_extension->extension_id = $data['id'];
        $package_extension->save();

        return Response::json(['success' => true]);
    }

    public function storeAll($package_id, Request $request)
    {

        $package_extensions = PackageExtension::select(['extension_id'])->where('package_id',$package_id);

        $extensions = Package::select(['id'])->where('extension',1);

        if ($package_extensions->count() > 0) {
            $extensions->whereNotIn('id', $package_extensions);
        }

        $extensions = $extensions->get();

        foreach ($extensions as $key => $ext) {
            $package_extension = new PackageExtension();
            $package_extension->package_id = $package_id;
            $package_extension->extension_id = $ext['id'];
            $package_extension->save();
        }

        return Response::json(['success' => true]);
    }

    public function storeInverse(Request $request)
    {
        $data = $request->input('data');
        PackageExtension::find($data['id'])->delete();
        return Response::json(['success' => true]);
    }

    public function storeInverseAll($package_id)
    {
        $success = false;
        $ids = PackageExtension::where('package_id', $package_id)->get();
        if(count($ids) > 0){
            PackageExtension::find($ids->pluck('id'))->each(function ($exntesion) {
                $exntesion->delete();
            });
            $success = true;
        }
        return Response::json(['success' => $success]);
    }


}
