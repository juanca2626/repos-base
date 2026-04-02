<?php

namespace App\Http\Controllers;

use App\PackageFixedSaleRate;
use App\PackagePlanRateCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PackageFixedSaleRateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($package_plan_rate_id)
    {
        $plant_rate_categories = PackagePlanRateCategory::where('package_plan_rate_id', $package_plan_rate_id)
            ->with([
                'category' => function ($query) {
                    $query->select(['id']);
                    $query->with([
                        'translations' => function ($query) {
                            $query->select(['id', 'value', 'object_id']);
                            $query->where('type', 'typeclass');
                            $query->where('language_id', 1);

                        }
                    ]);
                }
            ])
            ->get(['id', 'type_class_id']);
        $package_fixed_sale_rates = PackageFixedSaleRate::whereIn('package_plan_rate_category_id',
            $plant_rate_categories->pluck('id'))->get();
        $fixed_rates_transform = collect();
        if ($package_fixed_sale_rates->count() === 0) {
            foreach ($plant_rate_categories as $category) {
                $fixed_rates_transform->add([
                    'category' => $category->category->translations[0]->value,
                    'rates' => [
                        'id' => null,
                        'package_plan_rate_category_id' => $category->id,
                        'simple' => 0,
                        'double' => 0,
                        'triple' => 0,
                        'child_with_bed' => 0,
                        'child_without_bed' => 0
                    ]
                ]);
            }
        } else {
            foreach ($package_fixed_sale_rates as $fixed_sale_rate) {
                $search = $plant_rate_categories->first(function ($item) use ($fixed_sale_rate) {
                    return $item->id === $fixed_sale_rate->package_plan_rate_category_id;
                });
                if ($search) {
                    $fixed_rates_transform->add([
                        'category' => $search->category->translations[0]->value,
                        'rates' => [
                            'id' => $fixed_sale_rate->id,
                            'package_plan_rate_category_id' => $search->id,
                            'simple' => $fixed_sale_rate->simple,
                            'double' => $fixed_sale_rate->double,
                            'triple' => $fixed_sale_rate->triple,
                            'child_with_bed' => $fixed_sale_rate->child_with_bed,
                            'child_without_bed' => $fixed_sale_rate->child_without_bed
                        ]
                    ]);
                }
            }

            $search_not_categories = $plant_rate_categories->whereNotIn('id',
                $package_fixed_sale_rates->pluck('package_plan_rate_category_id'))->values();
            foreach ($search_not_categories as $category) {
                $fixed_rates_transform->add([
                    'category' => $category->category->translations[0]->value,
                    'rates' => [
                        'id' => null,
                        'package_plan_rate_category_id' => $category->id,
                        'simple' => 0,
                        'double' => 0,
                        'triple' => 0,
                        'child_with_bed' => 0,
                        'child_without_bed' => 0
                    ]
                ]);
            }
        }

        $data = [
            'data' => $fixed_rates_transform,
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
        $id = $request->post('id');
        $package_plan_rate_category_id = $request->post('package_plan_rate_category_id');
        $simple = $request->post('simple');
        $double = $request->post('double');
        $triple = $request->post('triple');
        $child_with_bed = $request->post('child_with_bed');
        $child_without_bed = $request->post('child_without_bed');

        if (empty($id)) {
            $package_fixed_sale_rates = new PackageFixedSaleRate();
            $package_fixed_sale_rates->package_plan_rate_category_id = $package_plan_rate_category_id;
            $package_fixed_sale_rates->simple = $simple;
            $package_fixed_sale_rates->double = $double;
            $package_fixed_sale_rates->triple = $triple;
            $package_fixed_sale_rates->child_with_bed = $child_with_bed;
            $package_fixed_sale_rates->child_without_bed = $child_without_bed;
            $package_fixed_sale_rates->save();
        } else {
            $package_fixed_sale_rates = PackageFixedSaleRate::find($id);
            $package_fixed_sale_rates->simple = $simple;
            $package_fixed_sale_rates->double = $double;
            $package_fixed_sale_rates->triple = $triple;
            $package_fixed_sale_rates->child_with_bed = $child_with_bed;
            $package_fixed_sale_rates->child_without_bed = $child_without_bed;
            $package_fixed_sale_rates->save();
        }
        $data = [
            'success' => true
        ];
        return Response::json($data);
    }

    public function storeAll(Request $request)
    {
        $package_plan_rate_id = $request->post('package_plan_rate_id');
        $simple = $request->post('simple');
        $double = $request->post('double');
        $triple = $request->post('triple');
        $child_with_bed = $request->post('child_with_bed');
        $child_without_bed = $request->post('child_without_bed');

        $plant_rate_categories = PackagePlanRateCategory::where('package_plan_rate_id', $package_plan_rate_id)
            ->get(['id', 'type_class_id']);
        $package_fixed_sale_rates = PackageFixedSaleRate::whereIn('package_plan_rate_category_id',
            $plant_rate_categories->pluck('id'))->get();
        if ($package_fixed_sale_rates->count() === 0) {
            foreach ($plant_rate_categories as $category) {
                $package_fixed_sale_rates = new PackageFixedSaleRate();
                $package_fixed_sale_rates->package_plan_rate_category_id = $category->id;
                $package_fixed_sale_rates->simple = $simple;
                $package_fixed_sale_rates->double = $double;
                $package_fixed_sale_rates->triple = $triple;
                $package_fixed_sale_rates->child_with_bed = $child_with_bed;
                $package_fixed_sale_rates->child_without_bed = $child_without_bed;
                $package_fixed_sale_rates->save();
            }
        } else {
            foreach ($package_fixed_sale_rates as $fixed_sale_rate) {
                $package_fixed_sale_rates = PackageFixedSaleRate::find($fixed_sale_rate->id);
                $package_fixed_sale_rates->simple = $simple;
                $package_fixed_sale_rates->double = $double;
                $package_fixed_sale_rates->triple = $triple;
                $package_fixed_sale_rates->child_with_bed = $child_with_bed;
                $package_fixed_sale_rates->child_without_bed = $child_without_bed;
                $package_fixed_sale_rates->save();
            }

            $search_not_categories = $plant_rate_categories->whereNotIn('id',
                $package_fixed_sale_rates->pluck('package_plan_rate_category_id'))->values();
            foreach ($search_not_categories as $category) {
                $package_fixed_sale_rates = new PackageFixedSaleRate();
                $package_fixed_sale_rates->package_plan_rate_category_id = $category->id;
                $package_fixed_sale_rates->simple = $simple;
                $package_fixed_sale_rates->double = $double;
                $package_fixed_sale_rates->triple = $triple;
                $package_fixed_sale_rates->child_with_bed = $child_with_bed;
                $package_fixed_sale_rates->child_without_bed = $child_without_bed;
                $package_fixed_sale_rates->save();
            }


        }


        $data = [
            'success' => true
        ];
        return Response::json($data);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PackageFixedSaleRate  $packageFixedSaleRate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PackageFixedSaleRate $packageFixedSaleRate)
    {
        //
    }


}
