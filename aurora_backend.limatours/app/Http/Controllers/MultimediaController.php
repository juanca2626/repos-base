<?php

namespace App\Http\Controllers;

use App\Language;
use App\Multimedia;
use App\MultimediaPhotoFilter;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use JD\Cloudder\Facades\Cloudder;

class MultimediaController extends Controller
{
    use Translations;

    /**
     * Display a listing of the resource.
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try
        {
            $paging = $request->input('page') ? $request->input('page') : 1;
            $lang = $request->input('lang');
            $limit = $request->input('limit');
            $type = ($request->has('type')) ? $request->post('type') : 'destination';
            $language = Language::where('iso', $lang)->first();
            $multimedia_deleted = [];

            $cloudinary = Cloudder::subfolders('peru');
            $cloudinary_folders = $cloudinary['folders'];
            $cloudinary_folders_path = collect($cloudinary_folders)->pluck('path');

            $multimedia = Multimedia::with([
                'translations' => function ($query) use ($language) {
                    $query->where('type', 'multimedia');
                    $query->where('slug', 'destiny_name');
                    $query->where('language_id', $language->id);
                }
            ])
            ->where('type', $type)
            ->whereIn('folder', $cloudinary_folders_path)->get();

            $folders = $multimedia->pluck('folder')->toArray();

            if(count($cloudinary_folders) != count($multimedia))
            {
                foreach($cloudinary_folders as $folder)
                {
                    if(!in_array($folder['path'], $folders, true))
                    {
                        $translations = [
                            1 => [
                                'destiny_name' => $folder['name']
                            ],
                            2 => [
                                'destiny_name' => $folder['name']
                            ],
                            3 => [
                                'destiny_name' => $folder['name']
                            ]
                        ];

                        $multimedia = new Multimedia();
                        $multimedia->folder = $folder['path'];
                        $multimedia->type = $type;
                        $multimedia->status = 1;
                        $multimedia->save();
                        $this->saveTranslation($translations, 'multimedia', $multimedia->id);
                    }
                }

                // Eliminar los que no se encuentren en los folders de cloudinary..
                $multimedia_deleted = Multimedia::where('type', $type)
                    ->whereNotIn('folder', $cloudinary_folders_path)
                    ->pluck('id')->toArray();

                foreach($multimedia_deleted as $multimedia_id)
                {
                    Multimedia::where('id', $multimedia_id)->delete();
                    $this->deleteTranslation('multimedia', $multimedia_id);
                }

                $multimedia = Multimedia::with([
                    'translations' => function ($query) use ($language) {
                        $query->where('type', 'multimedia');
                        $query->where('slug', 'destiny_name');
                        $query->where('language_id', $language->id);
                    }
                ])
                ->where('type', $type)
                ->whereIn('folder', $cloudinary_folders_path)->get();
            }

            if ($paging === 1) {
                $multimedia = $multimedia->take($limit)->orderBy('id', 'desc')->get([
                    'id',
                    'folder',
                    'status',
                    'translations',
                    'created_at',
                    'updated_at',
                ]);
            }

            $count = $multimedia->count();

            $data = [
                'data' => $multimedia,
                'deleted' => $multimedia_deleted,
                'folders' => $folders,
                'count' => $count,
                'success' => true
            ];

            return Response::json($data);
        }
        catch(\Exception $e)
        {
            return $this->throwError($e);
        }
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
    public function store(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.destiny_name' => 'unique:translations,value,NULL,id,type,multimedia',
            'folder' => 'required|unique:multimedia,folder'
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
            $multimedia = new Multimedia();
            $multimedia->folder = $request->post('folder');
            $multimedia->type = ($request->has('type')) ? $request->post('type') : 'destination';
            $multimedia->status = 1;
            $multimedia->save();
            $this->saveTranslation($request->input("translations"), 'multimedia', $multimedia->id);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $classification = Multimedia::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'multimedia');
                $query->where('slug', 'destiny_name');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $classification]);
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
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            // 'translations.*.destiny_name' => 'unique:translations,value,'.$id.',object_id,type,multimedia',
            'folder' => 'required'
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
            $multimedia = Multimedia::find($id);
            $multimedia->folder = $request->post('folder');
            $multimedia->type = ($request->has('type')) ? $request->post('type') : 'destination';
            $multimedia->save();
            $this->saveTranslation($request->input("translations"), 'multimedia', $multimedia->id);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $multimedia = Multimedia::find($id);
        $multimedia->delete();
        return Response::json(['success' => true]);
    }

    public function changeStatus($id, Request $request)
    {
        $multimedia = Multimedia::find($id);
        if ($request->input("status")) {
            $multimedia->status = false;
        } else {
            $multimedia->status = true;
        }
        $multimedia->save();
        return Response::json(['success' => true]);
    }

    public function getDestinations(Request $request)
    {

        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $multimedia = Multimedia::with([
            'translations' => function ($query) use ($language) {
                $query->where('type', 'multimedia');
                $query->where('slug', 'destiny_name');
                $query->where('language_id', $language->id);
            }
        ])->where('type', 'destination')->where('status', 1)->get();
        $multimedia_data = [];
        $multimedia->transform(function ($item) use ($multimedia_data) {
            $multimedia_data['name'] = (count($item['translations']) > 0) ? $item['translations'][0]['value'] : '-';
            $multimedia_data['path'] = $item['folder'];
            return $multimedia_data;
        });

        return Response::json(['success' => true, 'data' => $multimedia]);
    }

    public function getFilters(Request $request)
    {
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $multimedia = Multimedia::with([
            'translations' => function ($query) use ($language) {
                $query->where('type', 'multimedia');
                $query->where('slug', 'destiny_name');
                $query->where('language_id', $language->id);
            }
        ])->with([
            'photo_filters' => function ($query) use ($language) {
                $query->with([
                    'translations' => function ($query) use ($language) {
                        $query->where('type', 'multimedia');
                        $query->where('slug', 'tag_name');
                        $query->where('language_id', $language->id);
                    }
                ]);
            }
        ])->where('type', 'tag')->get();

        $multimedia_data = [
            'interests' => [],
            'composition' => [],
            'type_of_service' => [],
            'media_type' => [],
        ];

        /*
        $multimedia->transform(function ($item) use ($multimedia_data) {
            $multimedia_data[$item['folder']] = $item['photo_filters'];
            return $multimedia_data;
        });
        */

        // dd($multimedia);

        $data = [
            'success' => true,
            'data' => $multimedia
        ];

        /*
        $data = [
            'success' => true,
            'data' => [
                'interests' => $multimedia[0]['interests'],
                'composition' => $multimedia[1]['composition'],
                'type_of_service' => $multimedia[2]['type_of_service'],
                'media_type' => $multimedia[3]['media_type'],
            ]
        ];
        */

        return Response::json($data);
    }

    public function store_filter(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.tag_name' => 'unique:translations,value,NULL,id,type,multimedia',
            'tag' => 'required|unique:multimedia_photo_filters,tag'
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
            $multimedia_id = Multimedia::where('folder',$request->post('type_tag'))->first();
            $multimedia_filter = new MultimediaPhotoFilter();
            $multimedia_filter->tag = $request->post('tag');
            $multimedia_filter->multimedia_id = $multimedia_id->id;
            $multimedia_filter->status = 1;
            $multimedia_filter->save();
            $this->saveTranslation($request->input("translations"), 'multimedia', $multimedia_filter->id);
        }

    }

    public function destroy_filter($id)
    {
        $multimedia = MultimediaPhotoFilter::find($id);
        $multimedia->delete();
        return Response::json(['success' => true]);
    }

    public function showFilter($id)
    {
        $multimedia = MultimediaPhotoFilter::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'multimedia');
                $query->where('slug', 'tag_name');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $multimedia]);
    }

    public function update_filter(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.tag_name' => 'unique:translations,value,'.$id.',object_id,type,multimedia',
            'tag' => 'required'
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
            $multimedia = MultimediaPhotoFilter::find($id);
            $multimedia->tag = $request->post('tag');
            $multimedia->save();
            $this->saveTranslation($request->input("translations"), 'multimedia', $multimedia->id);
        }
    }

    public function changeStatusFilter($id, Request $request)
    {
        $multimedia = MultimediaPhotoFilter::find($id);
        if ($request->input("status")) {
            $multimedia->status = false;
        } else {
            $multimedia->status = true;
        }
        $multimedia->save();
        return Response::json(['success' => true]);
    }


}
