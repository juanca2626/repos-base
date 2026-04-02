<?php

namespace App\Http\Controllers;

use App\Language;
use App\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Cache;

class TranslationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:translations.read')->only('index');
        $this->middleware('permission:translations.create')->only('store');
        $this->middleware('permission:translations.update')->only('update');
        $this->middleware('permission:translations.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $sorting = $request->input('orderBy');
        $sortOrder = $request->input('ascending');

        $translations = Translation::with('language')
            ->where('type', 'label');

        if ($request->input('language')) {
            $translations = $translations->where('language_id', $request->input('language'));
        }

        if ($request->input('modules')) {
            $translations = $translations->where('slug', 'like', $request->input('modules') . '.%');
        }

        $count = $translations->count();

        if ($querySearch) {
            $translations->where(function ($query) use ($querySearch) {
                $query->orWhere('type', 'like', '%' . $querySearch . '%');
                $query->orWhere('slug', 'like', '%' . $querySearch . '%');
                $query->orWhere('value', 'like', '%' . $querySearch . '%');
                $query->orWhereHas('language', function ($q) use ($querySearch) {

                    $q->where('name', 'like', '%' . $querySearch . '%');
                });
            });
        }

        if ($sorting) {
            $asc = $sortOrder == 1 ? 'asc' : 'desc';
            $translations->orderBy($sorting, $asc);
        } else {
            $translations->orderBy('created_at', 'asc');
        }

        if ($paging === 1) {
            $translations = $translations->take($limit)->get();
        } else {
            $translations = $translations->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $modules = [];
        foreach ($translations as $translation) {
            if (in_array(explode(".", $translation->slug)[0], $modules) === false) {
                array_push($modules, explode(".", $translation->slug)[0]);
            }
        }

        $data = [
            'data' => $translations,
            'modules' => $modules,
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
        foreach ($request->input('values') as $key => $value) {
            if (!$key or !$value) {
                continue;
            }
            Translation::updateOrCreate(
                [
                    'type' => 'label',
                    'language_id' => $key,
                    'object_id' => 0,
                    'slug' => $request->input('slug')
                ],
                [
                    'value' => $value
                ]
            );
        }

        Cache::forget('translations');

        return Response::json(['success' => true]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $translation = Translation::find($id);

        $translation = Translation::where('slug', $translation->slug)->get()->keyBy('language_id');

        return Response::json(['success' => true, 'data' => $translation]);
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
        foreach ($request->input('values') as $key => $value) {
            Translation::updateOrCreate(
                [
                    'type' => 'label',
                    'language_id' => $key,
                    'object_id' => 0,
                    'slug' => $request->input('slug')
                ],
                [
                    'value' => $value
                ]
            );
        }

        Cache::forget('translations');

        return Response::json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $translation = Translation::find($id);

        $translation->delete();

        Cache::forget('translations');

        return Response::json(['success' => true]);
    }

    public function getJson(Request $request)
    {
        if (Cache::has('translations')) {
            $result = Cache::get('translations');
            $cache_translations = true;
        } else {
            $languages = Language::select('id', 'iso')
                ->get()
                ->keyBy('id')
                ->toArray();
            $translations = Translation::where('type', 'label')
                ->orderBy('slug', 'asc')
                ->orderBy('language_id', 'asc')
                ->get();
            $result = [];

            foreach ($languages as $language) {
                $result[$language['iso']] = [];
            }

            foreach ($translations as $translation) {
                $currentLanguage = $languages[$translation->language_id]['iso'];
                $values = $this->stringToArray($translation->slug . '.' . $translation->value);

                if (array_key_exists(array_keys($values)[0], $result[$currentLanguage]) === false) {
                    $result[$currentLanguage][array_keys($values)[0]] = $values[array_keys($values)[0]];
                } else {
                    $tmpArray = $result[$currentLanguage];
                    $result[$currentLanguage] = array_replace_recursive($tmpArray, $values);
                }
            }
            Cache::forever('translations',$result);
            $cache_translations = false;
        }

        return Response::json(['success' => true, 'data' => $result, 'cache_translations' => $cache_translations]);
    }

    private function stringToArray($string)
    {
        $separator = '.';
        $pos = strpos($string, $separator);

        if ($pos === false) {
            return $string;
        }

        $key = substr($string, 0, $pos);
        $string = substr($string, $pos + 1);

        $result = array(
            $key => $this->stringToArray($string),
        );

        return $result;
    }
}
