<?php

namespace App\Http\Controllers;

use App\Service;
use App\ServiceCategory;
use App\ServiceSubCategory;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ServiceSubCategoriesController extends Controller
{
    use Translations;

    public function __construct()
    {
//        $this->middleware('permission:servicesubcategories.read')->only('index');
        $this->middleware('permission:servicesubcategories.create')->only('store');
        $this->middleware('permission:servicesubcategories.update')->only('update');
        $this->middleware('permission:servicesubcategories.delete')->only('delete');
    }


    public function index()
    {
        $subcategories = ServiceCategory::with([
            'translations' => function ($query) {
                $query->where('type', 'servicecategory');
               $query->where('language_id',1);
            }
        ])->with([
            'serviceSubCategory' => function ($query) {
                $query->with([
                    'translations' => function ($query) {
                        $query->where('type', 'servicesubcategory');
                        $query->where('language_id',1);
                    }
                ]);
                $query->orderBy('order');
            }
        ])->orderBy('order')->get();

        return \response()->json($subcategories,200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;
        $validator = Validator::make($request->all(), [
            'service_category_id' => 'required',
            'translations.*.servicesubcategory_name' => 'unique:translations,value,NULL,id,type,servicesubcategory'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }
            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false, 'error' => $arrayErrors]);
        } else {
            $subcategory = new ServiceSubCategory();
            $subcategory->service_category_id = $request->input('service_category_id');;
            $subcategory->save();
            $this->saveTranslation($request->input("translations"), 'servicesubcategory', $subcategory->id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $subcategory = ServiceSubCategory::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'servicesubcategory');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $subcategory]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.servicesubcategory_name' => 'unique:translations,value,' . $id . ',object_id,type,servicesubcategory'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }
            $countErrors++;
        }

        if ($countErrors > 0) {
            return Response::json(['success' => false]);
        } else {
            $subcategory = ServiceSubCategory::find($id);
            $subcategory->save();
            $this->saveTranslation($request->input("translations"), 'servicesubcategory', $subcategory);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $subcategory_used = Service::where('service_sub_category_id', $id)->take(1)->get();
        if ($subcategory_used->count() == 0) {
            $subcategory = ServiceSubCategory::find($id);
            $subcategory->delete();
            $this->deleteTranslation('servicesubcategory', $id);
            $used = false;
            $success = true;
        } else {
            $used = true;
            $success = false;
        }
        return Response::json(['success' => $success, 'used' => $used]);
    }
}
