<?php

namespace App\Http\Controllers;

use App\Language;
use App\Package;
use App\Tag;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TagsController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:tags.read')->only('index');
        $this->middleware('permission:tags.create')->only('store');
        $this->middleware('permission:tags.update')->only('update');
        $this->middleware('permission:tags.delete')->only('destroy');
    }

    use Translations;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $group_id)
    {
        //Todo: Refactorizar Bloque de pagination
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');

        $querySearch = $request->input('query');
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $tags = Tag::with([
            'translations' => function ($query) use ($language) {
                $query->where('type', 'tag');
                $query->where('language_id', $language->id);
            },
        ])->where('tag_group_id', $group_id);

        $count = $tags->count();

        if ($querySearch) {
            $tags->whereHas('translations', function ($query) use ($querySearch, $lang) {
                $query->where('type', 'tag');
                $query->where('value', 'like', '%'.$querySearch.'%');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            });
        }


        $tags->orderBy('id', 'desc');

        if ($paging == 1) {
            $tags = $tags->take($limit)->get();
        } else {
            $tags = $tags->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $tags,
            'count' => $count,
            'success' => true,
        ];

        return Response::json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$group_id)
    {
        $tag = new Tag();
        $tag->tag_group_id = $group_id;
        $tag->color = $request->get('color');
        $tag->save();

        $this->saveTranslation($request->input('translations'), 'tag', $tag->id);

        return Response::json(["success" => true]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($group_id, $tag_id)
    {
        $tags = Tag::with('translations')->where('id', $tag_id)->where('tag_group_id', $group_id)->first();

        return Response::json($tags);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $group_id, $tag_id)
    {
        $tag = Tag::find($tag_id);
        if ($tag) {
            $tag->color = $request->get('color');
            $tag->save();
            $this->saveTranslation($request->input('translations'), 'tag', $tag_id);
            return Response::json(['success' => true]);
        } else {
            return Response::json(['success' => false]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($group_id, $tag_id)
    {

        $tag_used = Package::where('tag_id', $tag_id)->select(['id', 'tag_id'])->count();
        if ($tag_used == 0) {
            $tag = Tag::find($tag_id);
            $tag->delete();
            $this->deleteTranslation('tag', $tag_id);
            $used = false;
            $success = true;
        } else {
            $used = true;
            $success = false;
        }

        return Response::json(['success' => $success, 'used' => $used]);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function selectBox(Request $request)
    {

        $lang = $request->input('lang');

        $tags = Tag::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'tag');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
                $query->orderBy('value', 'asc');
            }
        ])->with([
            'tag_group.translations' => function ($query) use ($lang) {
                $query->where('type', 'taggroup');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->whereHas('tag_group')->get();

        return Response::json(['success' => true, 'data' => $tags]);

    }
}
