<?php

namespace App\Http\Controllers;

use App\TagGroup;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TagGroupController extends Controller
{
    use Translations;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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

        $tag_groups = TagGroup::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'taggroup');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            },
        ]);

        $count = $tag_groups->count();

        if ($querySearch) {
            $tag_groups->whereHas('translations', function ($query) use ($querySearch, $lang) {
                $query->where('type', 'taggroup');
                $query->where('value', 'like', '%'.$querySearch.'%');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            });
        }

        if ($sorting) {
            $asc = $sortOrder == 1 ? 'asc' : 'desc';
            $tag_groups->orderBy($sorting, $asc);
        } else {
            $tag_groups->orderBy('created_at', 'desc');
        }

        if ($paging == 1) {
            $tag_groups = $tag_groups->take($limit)->get();
        } else {
            $tag_groups = $tag_groups->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $tag_groups,
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $tag_group = new TagGroup();

        $tag_group->save();

        $this->saveTranslation($request->input('translations'), 'taggroup', $tag_group->id);

        return Response::json(["success"=>true]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tag_groups = TagGroup::with([
            'translations' => function ($query) {
                $query->where('type', 'taggroup');

            },
        ])->where('id',$id)->first();

        return Response::json($tag_groups);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        $this->saveTranslation($request->input('translations'), 'taggroup', $id);

        return Response::json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tag_group = TagGroup::find($id);

        $tag_group->delete();

        return Response::json(['success' => true]);
    }
}
