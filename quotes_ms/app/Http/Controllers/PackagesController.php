<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PackagesController extends Controller
{
    public function getExtensionsQuote(Request $request)
    {

        $type_class = $request->post('type_class_id');
        $type_service = $request->post('type_service');
        $destination = $request->post('destination');
        $lang = $request->post('lang');
        $filter = $request->post('filter');
        $date_from = $request->input('date');
        $date_from = Carbon::parse($date_from);
        $date_from = $date_from->format('Y-m-d');
        $package = $request->input('package');

        $extensions = Package::with([
            'package_destinations.state.translations' => function ($query) use ($lang) {
                $query->where('type', 'state');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            },
        ])->with([
            'tag.translations' => function ($query) use ($lang) {
                $query->where('type', 'tag');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            },
        ])->with([
            'translations' => function ($query) use ($lang) {
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            },
        ])->with([
            'extension_recommended' => function ($query) use ($lang) {
                $query->select('id', 'package_id', 'extension_id');
            }
        ])->with([
            'plan_rates' => function ($query) use ($date_from, $type_service, $lang, $type_class) {
                $query->where('date_from', '<=', $date_from);
                $query->where('date_to', '>=', $date_from);
                $query->where('service_type_id', $type_service);
                $query->where('status', 1);
                $query->with([
                    'plan_rate_categories' => function ($q) use ($lang, $type_class) {
                        // if($type_class){
                        //    $q->where('type_class_id', $type_class);
                        // }

                        $q->whereHas('sale_rates');
                        $q->with([
                            'category' => function ($query) use ($lang) {
                                $query->with([
                                    'translations' => function ($q) use ($lang) {
                                        $q->where('type', 'typeclass');
                                        $q->whereHas('language', function ($q) use ($lang) {
                                            $q->where('iso', $lang);
                                        });
                                    }
                                ]);

                            }
                        ]);
                    }
                ]);
                $query->whereHas('plan_rate_categories', function ($q) use ($type_class) {
                    $q->whereHas('sale_rates');
                });
            }
        ])->with([
            'galleries' => function ($query) {
                $query->select('object_id', 'slug', 'url');
                $query->where('type', 'package');
            },
        ]);

        $extensions->whereHas('plan_rates', function ($query) use ($type_service, $type_class) {
            $query->where('status', 1);
            $query->where('service_type_id', $type_service);
            $query->whereHas('plan_rate_categories', function ($q) use ($type_class) {
                if ($type_class) {
                    $q->where('type_class_id', $type_class);
                }
                $q->whereHas('sale_rates');
            });
        });

        $extensions->whereHas('plan_rates', function ($query) use ($date_from) {
            $query->where('status', 1);
            $query->where('date_from', '<=', $date_from);
            $query->where('date_to', '>=', $date_from);
            $query->whereHas('plan_rate_categories.sale_rates');
        });

        if ($filter and $filter != '') {
            $extensions->where(function ($query) use ($filter) {

                $new_q = str_replace('P', '', str_replace('E', '', $filter));
                $query->orWhere('id', 'like', '%'.$new_q.'%');

                $query->orWhere('code', 'like', '%'.$filter.'%');

                $query->orWhereHas('translations', function ($q) use ($filter) {

                    $q->where('name', 'like', '%'.$filter.'%');
                });
            });
        }

        if ($destination) {
            $extensions->where('destinations', 'like', '%'.$destination.'%');
        }


        if ($package == 1) {
            $extensions = $extensions->where('status', 1)->where('extension', 0)->get();
        } else {
            $extensions = $extensions->where('status', 1)->where('extension', 1)->get();
        }

        return \response()->json($extensions, 200);

    }




}
