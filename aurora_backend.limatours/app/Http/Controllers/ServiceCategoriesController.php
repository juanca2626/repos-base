<?php

namespace App\Http\Controllers;

use App\Language;
use App\ServiceCategory;
use App\ServiceSubCategory;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ServiceCategoriesController extends Controller
{
    use Translations;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');

        $serviceCategory = ServiceCategory::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'servicecategory');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'serviceSubCategory.translations' => function ($query) use ($lang) {
                $query->where('type', 'servicesubcategory');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ]);

        $count = $serviceCategory->count();
        if ($querySearch) {
            $serviceCategory->where(function ($query) use ($querySearch, $lang) {
                $query->whereHas('translations', function ($query) use ($querySearch, $lang) {
                    $query->where('type', 'servicecategory');
                    $query->where('value', 'like', '%'.$querySearch.'%');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                });
            });
        }
        if ($paging === 1) {
            $serviceCategory = $serviceCategory->take($limit)->get();
        } else {
            $serviceCategory = $serviceCategory->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $serviceCategory,
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
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.servicecategory_name' => 'unique:translations,value,NULL,id,type,servicecategory'
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
            $category = new ServiceCategory();
            $category->save();
            $this->saveTranslation($request->input("translations"), 'servicecategory', $category->id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ServiceCategory  $categoryService
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = ServiceCategory::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'servicecategory');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $category]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ServiceCategory  $categoryService
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.servicecategory_name' => 'unique:translations,value,'.$id.',object_id,type,servicecategory'
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
            $category = ServiceCategory::find($id);
            $category->save();
            $this->saveTranslation($request->input("translations"), 'servicecategory', $category);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ServiceCategory  $categoryService
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $service_type_used = DB::table('services as s')->selectRaw('count(*) as count')
            ->join('service_sub_categories as sb', 's.service_sub_category_id', '=', 'sb.id')
            ->join('service_categories as sc', 'sc.id', '=', 'sb.service_category_id')
            ->where('sc.id', $id)
            ->get();
        if ($service_type_used[0]->count == 0) {
            $category = ServiceCategory::find($id);
            $category->delete();
            $this->deleteTranslation('servicecategory', $id);
            $used = false;
            $success = true;
        } else {
            $used = true;
            $success = false;
        }

        return Response::json(['success' => $success, 'used' => $used]);

    }

    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $restrictions = ServiceCategory::with([
            'translations' => function ($query) use ($language) {
                $query->select(['object_id', 'value']);
                $query->where('type', 'servicecategory');
                $query->where('language_id', $language->id);
            }
        ])->orderBy('order')->get(['id']);
        return Response::json(['success' => true, 'data' => $restrictions]);
    }

    public function getSubCategories($id, $lang)
    {
        $subCategories = ServiceSubCategory::where('service_category_id', $id)
            ->with([
                'translations' => function ($query) use ($lang) {
                    $query->where('type', 'servicesubcategory');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->get();
        return Response::json(['success' => true, 'data' => $subCategories]);
    }

    public function selectBoxGroup(Request $request)
    {
        $lang = $request->input('lang');

        $serviceCategories = ServiceCategory::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'servicecategory');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'serviceSubCategory.translations' => function ($query) use ($lang) {
                $query->where('type', 'servicesubcategory');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get()->toArray();
        $serviceCategoriesGroup = [];
        foreach ($serviceCategories as $key_c => $serviceCategory) {
            $serviceCategoriesGroup[$key_c]['category'] = ucwords(strtolower($serviceCategory['translations'][0]['value']));
            foreach ($serviceCategory['service_sub_category'] as $key_s => $serviceSubCategory) {
                $serviceCategoriesGroup[$key_c]['sub_category'][$key_s]['id'] = $serviceSubCategory['id'];
                $serviceCategoriesGroup[$key_c]['sub_category'][$key_s]['name'] = ucwords(strtolower($serviceSubCategory['translations'][0]['value']));
            }
        }
        return Response::json(['success' => true, 'data' => $serviceCategoriesGroup]);
    }
}
