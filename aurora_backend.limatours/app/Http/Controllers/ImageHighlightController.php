<?php

namespace App\Http\Controllers;

use App\ImageHighlight;
use App\Language;
use App\PackageHighlight;
use App\Http\Traits\Images;
use App\Http\Traits\Translations;
use App\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use JD\Cloudder\Facades\Cloudder;

class ImageHighlightController extends Controller
{
    use Translations, Images;


    public function __construct()
    {
        $this->middleware('permission:highlights.read')->only('index');
        $this->middleware('permission:highlights.create')->only('store');
        $this->middleware('permission:highlights.update')->only('update');
        $this->middleware('permission:highlights.delete')->only('delete');
    }

    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $image_highlight = ImageHighlight::with([
            'translations' => function ($query) use ($language) {
                $query->where('type', 'image_highlights');
                $query->whereHas('language', function ($q) use ($language) {
                    $q->where('language_id', $language->id);
                });
            },
            'translations_content' => function ($query) use ($language) {
                $query->where('type', 'image_highlights');
                $query->whereHas('language', function ($q) use ($language) {
                    $q->where('language_id', $language->id);
                });
            }
        ]);

        if ($querySearch) {
            $translations = Translation::where('type', 'image_highlights')
                ->where('value', 'like', '%'.$querySearch.'%')
                ->where('language_id', $language->id)->get();

            if ($translations->count() > 0) {
                $image_highlight_ids = $translations->pluck('object_id');
                $image_highlight->whereIn('id', $image_highlight_ids);
            }
        }

        $count = $image_highlight->count();
        if ($paging === 1) {
            $image_highlight = $image_highlight->take($limit)->get();
        } else {
            $image_highlight = $image_highlight->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $image_highlight = $this->getMultiResizesCloudinary($image_highlight->toArray(), 'url');

        $data = [
            'data' => $image_highlight,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);

    }

    public function storeUpload(Request $request)
    {
        $image_file = $request->file('file')->getRealPath();
        $image = $request->file('file')->getClientOriginalName();
        $image = explode('.', $image);
        $image_name = 'sin-nombre';
        $translations_input = $request->input("translations");
        $decodedText = html_entity_decode($translations_input);
        $translations = json_decode($decodedText, true);

        $translations_content_input = $request->input("translations_content");
        $decodedText_content = html_entity_decode($translations_content_input);
        $translations_content = json_decode($decodedText_content, true);

//        throw new \Exception($translations);
        if (isset($image[0]) and $image[0] !== '') {
            $image_name = strtolower($image[0]);
        }

        $this->validate($request, [
            'file' => 'required|mimes:jpeg,bmp,jpg,png|between:1, 6000',
        ]);

        $upload = Cloudder::upload(
            $image_file,
            null,
            array(
                "folder" => 'highlights',
                "public_id" => $image_name
            )
        )->getResult();
//        throw new \Exception(json_encode($upload));
        if ($upload) {
            if ($request->has('id') and !empty($request->get('id'))) {
                $high = ImageHighlight::find($request->get('id'));
            } else {
                $high = new ImageHighlight();
            }
            $high->url = $upload['secure_url'];
            if ($high->save()) {
                $this->saveTranslation($translations, 'image_highlights', $high->id);
                $this->saveTranslation($translations_content, 'image_highlights', $high->id);
            }
        }
    }

    public function store(Request $request)
    {
        try {
            $high = new ImageHighlight();
            $high->url = $request->get('url');
            if ($high->save()) {
                $this->saveTranslation($request->input("translations"), 'image_highlights', $high->id);
                $this->saveTranslation($request->input("translations_content"), 'image_highlights', $high->id);
            }
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'data' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $high = ImageHighlight::find($id);
            if ($request->has('url') and !empty($request->get('url'))) {
                $high->url = $request->get('url');
            }
            if ($high->save()) {
                $this->saveTranslation($request->input("translations"), 'image_highlights', $id);
                $this->saveTranslation($request->input("translations_content"), 'image_highlights', $id);
            }
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'data' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $imageHighlight = ImageHighlight::with(['translations', 'translations_content'])
            ->where('id', $id)->get();

        $imageHighlight = $this->getMultiResizesCloudinary($imageHighlight->toArray(), 'url');

        return Response::json(['success' => true, 'data' => $imageHighlight]);
    }

    public function destroy($id)
    {
        try {
            $classification_used = PackageHighlight::where('image_highlight_id', $id)->get();
            if ($classification_used->count() == 0) {
                $classification = ImageHighlight::find($id);
                $classification->delete();
                $this->deleteTranslation('image_highlights', $id);
                $used = false;
                $success = true;
            } else {
                $used = true;
                $success = false;
            }
            return Response::json(['success' => $success, 'used' => $used]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'data' => $e->getMessage()]);
        }

    }

    public function updateStatus($id, Request $request)
    {
        $service = ImageHighlight::find($id, ['id', 'status']);
        if ($request->input("status")) {
            $service->status = 0;
        } else {
            $service->status = 1;
        }
        $service->save();
        return Response::json(['success' => true]);
    }

    public function getUsedPackage($id, Request $request)
    {
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();

        $image_highlight = ImageHighlight::with([
            'package_highlights' => function ($query) use ($language) {
                $query->select(['id', 'package_id', 'image_highlight_id']);
                $query->with([
                    'packages' => function ($query) use ($language) {
                        $query->select(['id', 'code']);
                        $query->with([
                            'translations' => function ($query) use ($language) {
                                $query->select(['id', 'package_id', 'tradename']);
                                $query->where('language_id', $language->id);
                            }
                        ]);
                    }
                ]);
            }
        ])->where('id', $id)->get(['id']);

        $package_transform = collect();

        foreach ($image_highlight as $package) {
            foreach ($package['package_highlights'] as $highlight) {
                $package_transform->add([
                    'id' => $highlight->id,
                    'package_id' => $highlight->packages->id,
                    'package' => $highlight->packages['translations'][0]['tradename'],
                ]);
            }
        }

        $data = [
            'data' => $package_transform,
            'count' => $package_transform->count(),
            'success' => true
        ];

        return Response::json($data);
    }
}
