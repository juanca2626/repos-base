<?php

namespace App\Http\Services\Controllers;

use App\Classification;
use App\Experience;
use App\Http\Controllers\Controller;
use App\ServiceCategory;
use App\ServiceType;
use App\TypeClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ServicesReservationsController extends Controller
{

    public function service_types(Request $request)
    {
        $lang = ($request->has('lang')) ? $request->input('lang') : 'es';
        $serviceTypes = ServiceType::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'servicetype');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();

        if ($serviceTypes->count() > 0) {
            $serviceTypes->transform(function ($type) {
                return [
                    "id" => $type["id"],
                    "name" => $type["translations"][0]["value"]
                ];
            });
            return Response::json(['success' => true, 'data' => $serviceTypes]);
        } else {
            return Response::json(['success' => true, 'data' => []]);
        }
    }

    public function categories(Request $request)
    {
        $lang = ($request->has('lang')) ? $request->input('lang') : 'es';
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
            $serviceCategoriesGroup[$key_c]['category'] = (count($serviceCategory['translations']) > 0) ? ucwords(strtolower($serviceCategory['translations'][0]['value'])) : 'null';
            foreach ($serviceCategory['service_sub_category'] as $key_s => $serviceSubCategory) {
                $serviceCategoriesGroup[$key_c]['sub_category'][$key_s]['id'] = $serviceSubCategory['id'];
                $serviceCategoriesGroup[$key_c]['sub_category'][$key_s]['name'] =  (count($serviceSubCategory['translations']) > 0) ? ucwords(strtolower($serviceSubCategory['translations'][0]['value'])) : 'null';
            }
        }
        return Response::json(['success' => true, 'data' => $serviceCategoriesGroup]);
    }

    public function experiences(Request $request)
    {
        $lang = ($request->has('lang')) ? $request->input('lang') : 'es';

        $experience = Experience::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'experience');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();

        if ($experience->count() > 0) {
            $experience->transform(function ($experience) {
                return [
                    "id" => $experience["id"],
                    "name" => $experience["translations"][0]["value"]
                ];
            });
            return Response::json(['success' => true, 'data' => $experience]);
        } else {
            return Response::json(['success' => true, 'data' => []]);
        }
    }

    public function classifications(Request $request)
    {
        $lang = ($request->has('lang')) ? $request->input('lang') : 'es';
        $classification = Classification::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'classification');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();

        if ($classification->count() > 0) {
            $classification->transform(function ($classification) {
                return [
                    "id" => $classification["id"],
                    "name" => $classification["translations"][0]["value"]
                ];
            });
            return Response::json(['success' => true, 'data' => $classification]);
        } else {
            return Response::json(['success' => true, 'data' => []]);
        }

    }


    public function type_class(Request $request)
    {
        $lang = ($request->has('lang')) ? $request->input('lang') : 'es';
        $serviceTypes = TypeClass::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'typeclass');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();

        if ($serviceTypes->count() > 0) {
            $serviceTypes->transform(function ($type) {
                return [
                    "id" => $type["id"],
                    "name" => $type["translations"][0]["value"]
                ];
            });
            return Response::json(['success' => true, 'data' => $serviceTypes]);
        } else {
            return Response::json(['success' => true, 'data' => []]);
        }
    }
}
