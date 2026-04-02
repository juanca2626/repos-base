<?php

namespace App\Http\Controllers;

use App\Http\Traits\Translations;
use App\Translation;
use App\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpParser\Node\Stmt\TryCatch;

class ZonesController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:zones.read')->only('index');
        $this->middleware('permission:zones.create')->only('store');
        $this->middleware('permission:zones.update')->only('update');
        $this->middleware('permission:zones.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        //Todo: Refactorizar Bloque de pagination
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');
        $sorting = $request->input('orderBy');
        $sortOrder = $request->input('ascending');

        $zones = Zone::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'zone');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.translations' => function ($query) use ($lang) {
                $query->where('type', 'city');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.state.translations' => function ($query) use ($lang) {
                $query->where('type', 'state');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.state.country.translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ]);

        $count = $zones->count();

        if ($querySearch) {
            $zones->orWhereHas('city', function ($query) use ($querySearch, $lang) {

                $query->whereHas('translations', function ($query) use ($querySearch, $lang) {
                    $query->where('type', 'city');
                    $query->where('value', 'like', '%' . $querySearch . '%');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                });
            });
            $zones->orWhereHas('translations', function ($query) use ($querySearch, $lang) {
                $query->where('type', 'zone');
                $query->where('value', 'like', '%' . $querySearch . '%');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            });
        }

        if ($sorting) {
            $asc = $sortOrder == 1 ? 'asc' : 'desc';
            $zones->orderBy($sorting, $asc);
        } else {
            $zones->orderBy('created_at', 'desc');
        }

        if ($paging == 1) {
            $zones = $zones->take($limit)->get();
        } else {
            $zones = $zones->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $zones,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $arrayErrors = [];
        $validator = Validator::make($request->all(), [
            'city_id' => "required|exists:cities,id",
            'translations.*.zone_name' => "",
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $arrayErrors[] = $error;
            }
        } else {
            try {
                // Validacion manual si exite el nombre de la zona para el mismo city_id
                $this->validateZonaName($request->input("translations"), $request->input('city_id'));
            } catch (\Exception $exception) {
                $arrayErrors[] = $exception->getMessage();
            }
        }

        if (count($arrayErrors) > 0) {
            return Response::json([
                'success' => false,
                'message' => $arrayErrors
            ]);
        } else {
            $zone = new Zone();
            $zone->city_id = $request->input('city_id');
            $zone->save();

            $this->saveTranslation($request->input("translations"), 'zone', $zone->id);

            return Response::json(['success' => true]);
        }
    }

    /**
     * @param $translations
     * @param $city_id
     * @param null $zone_id
     * @throws \Exception
     */
    private function validateZonaName($translations, $city_id, $zone_id = null)
    {
        foreach ($translations as $key => $translation) {
            $zonesT = Translation::whereHas('zone', function ($query) use ($city_id) {
                $query->where('city_id', $city_id);
            })
                ->when(!empty($zone_id), function ($query) use ($zone_id) {
                    $query->where('object_id', '!=', $zone_id);
                })
                ->where('value', $translation['zone_name'])
                ->where('slug', 'zone_name')
                ->where('type', 'zone')
                ->first();

            if ($zonesT) {
                throw new \Exception('Zone Name ' . $translation['zone_name'] . ' already exist for selected City');
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show($id, Request $request)
    {
        $lang = $request->input('lang');

        $zone = Zone::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'zone');
                $query->where('object_id', $id);
            }
        ])->with([
            'city.translations' => function ($query) use ($lang) {
                $query->where('type', 'city');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.state.translations' => function ($query) use ($lang) {
                $query->where('type', 'state');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.state.country.translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('id', $id)->get();

        return Response::json(['success' => true, 'data' => $zone]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'city_id' => "exists:cities,id",
            'translations.*.zone_name' => ''
//            'translations.*.zone_name' => 'unique:translations,value,' . $id . ',object_id,type,zone'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }

            $countErrors++;
        }

        try {
            // Validacion manual si exite el nombre de la zona para el mismo city_id
            $this->validateZonaName($request->input("translations"), $request->input('city_id'), $id);
        } catch (\Exception $exception) {
            $arrayErrors[] = $exception->getMessage();
        }

        if ($countErrors > 0) {
            return Response::json(['success' => false, 'error' => $arrayErrors]);
        } else {
            $zone = Zone::find($id);
            if ($request->input('city_id') != null) {
                $zone->city_id = $request->input('city_id');
                $zone->save();
            }
            $this->saveTranslation($request->input('translations'), 'zone', $id);

            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $zone = Zone::find($id);

        if ($zone->hotels()->count() > 0) {
            return Response::json([
                'success' => false,
                'message' => 'Can not delete a Zone related to Hotels'
            ]);
        }

        $zone->delete();

        $this->deleteTranslation('zone', $id);

        return Response::json(['success' => true]);
    }

    public function getZones($id, $lang)
    {
        $zone = Zone::where('city_id', $id)
            ->with([
                'translations' => function ($query) use ($lang) {
                    $query->where('type', 'zone');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->get();
        return Response::json(['success' => true, 'data' => $zone]);
    }
    public function getZonesState($id, $lang)
    {
        $zone = Zone::whereHas('city', function ($query) use ($id) {
            $query->where('state_id', $id);
        })->with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'zone');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $zone]);
    }    
}
