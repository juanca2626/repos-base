<?php

namespace App\Http\Controllers;

use App\Galery;
use App\Hotel;
use App\ProgressBar;
use App\Http\Traits\Images;
use App\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

/**
 * Class GaleriesController
 * @package App\Http\Controllers
 */
class GaleriesController extends Controller
{
    use Images;

    public function __construct()
    {
//        $this->middleware('permission:galeries.read')->only('index');
//        $this->middleware('permission:galeries.create')->only('store');
//        $this->middleware('permission:galeries.update')->only('update');
//        $this->middleware('permission:galeries.delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {

        $galeries = Galery::all();
        return Response::json(['success' => true, 'data' => $galeries]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return JsonResponse
     */

    public function store(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:facility,hotel,room,amenity,client,package,service,classification,train,state',
            'object_id' => 'required|integer|numeric',
            'position' => 'required|integer|numeric|unique:galeries,position,NULL,id,type,'.$request->input("type").',object_id,'.$request->input("object_id").',deleted_at,NULL',
            'url' => 'nullable|string|max:100',
            'state' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }

            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false, 'errors' => $arrayErrors]);
        } else {
            $galery = new Galery();
            $galery->type = $request->input("type");
            $galery->object_id = $request->input("object_id");
            $galery->position = $request->input("position");
            $galery->slug = $request->input("slug");
            $galery->url = "test.png";
            $galery->state = $request->input("state");
            $galery->save();
            if ($request->input("url") == '') {
                $galery->url = md5($request->input("object_id").'_'.$galery->id).'.png';
            } else {
                $galery->url = $request->input("url");
            }
            $galery->save();

            if ($request->input("image") != '') {
                $this->imagesSave(Auth::user()->id.$request->input("image"), 'galeries',
                    $galery->object_id.'_'.$galery->id);
            } else {
                $this->imagesSave(Auth::user()->id, 'galeries', $galery->object_id.'_'.$galery->id);
            }
            if ($galery->type === "hotel" && $galery->slug === "hotel_gallery") {
                ProgressBar::updateOrCreate([
                    'slug' => 'hotel_progress_gallery',
                    'value' => 5,
                    'type' => 'hotel',
                    'object_id' => $galery->object_id
                ]);
            }
            if ($galery->type === "room") {
                ProgressBar::updateOrCreate([
                    'slug' => 'room_progress_gallery',
                    'value' => 20,
                    'type' => 'room',
                    'object_id' => $galery->object_id
                ]);
            }
            if ($galery->type === "service" && $galery->slug === "service_gallery") {
                ProgressBar::updateOrCreate([
                    'slug' => 'service_progress_gallery',
                    'value' => 10,
                    'type' => 'service',
                    'object_id' => $galery->object_id
                ]);
            }

            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $galery = Galery::find($id);

        return Response::json([
            'success' => true,
            'data' => $galery,
            'image' => $this->imagesExists('galeries', $galery->object_id.'_'.$galery->id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:facility,hotel,room,amenity,client,package,service,classification,train,state',
            'object_id' => 'required|integer|numeric',
            'position' => 'required|integer|numeric|unique:galeries,position,'.$id.',id,type,'.$request->input("type").',object_id,'.$request->input("object_id"),
            'url' => 'nullable|string|max:100',
            'state' => 'required|boolean',

        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }

            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false, 'errors' => $arrayErrors]);
        } else {
            $galery = Galery::find($id);
            $galery->type = $request->input("type");
            $galery->object_id = $request->input("object_id");
            $galery->position = $request->input("position");
            $galery->slug = $request->input("slug");
//            $galery->url = "test.png";
            $galery->state = $request->input("state");
            $galery->save();
            if ($request->input("url") == '') {
                $galery->url = md5($request->input("object_id").'_'.$galery->id).'.png';
            } else {
                $galery->url = $request->input("url");
            }
            $galery->save();

            $this->imagesSave(Auth::user()->id, 'galeries', $galery->object_id.'_'.$galery->id);

            if ($galery->type === "hotel" && $galery->slug === "hotel_gallery") {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'hotel_progress_gallery',
                        'value' => 5,
                        'type' => 'hotel',
                        'object_id' => $galery->object_id
                    ]
                );
            }
            if ($galery->type === "room") {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'room_progress_gallery',
                        'value' => 20,
                        'type' => 'room',
                        'object_id' => $galery->object_id
                    ]
                );
            }
            if ($galery->type === "service" && $galery->slug === "service_gallery") {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'service_progress_gallery',
                        'value' => 10,
                        'type' => 'service',
                        'object_id' => $galery->object_id
                    ]
                );
            }
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $galery = Galery::find($id);

        $galery->delete();

        return Response::json(['success' => true]);
    }

    //cambia estado desde el listado de la galeria
    public function changeStatus($id, Request $request)
    {
        $galery = Galery::find($id);
        if ($request->input("state")) {
            $galery->state = false;
        } else {
            $galery->state = true;
        }
        $galery->save();

        return Response::json(['success' => true]);
    }

    public function uploadImage(Request $request)
    {
        $timestamp_image = microtime();
        $response = [
            'success' => false,
            'name' => '',
            'message' => '',
            'timestamp' => $timestamp_image
        ];

        if ($request->file('file')) {
            $response = $this->imagesSaveTmp(Auth::user()->id.$timestamp_image, 'galeries', $request);
            $response["timestamp"] = $timestamp_image;
        } else {
            $response['message'] = "File didnt upload";
        }

        return Response::json($response);
    }

    public function removeImage($id)
    {
        $galery = Galery::find($id);
        $this->imagesRemove('galeries', $galery->object_id.'_'.$id);
        $galery->delete();

        return Response::json(['success' => true]);
    }

    //cambia estado desde el modulo de habitaciones
    public function updateState($id, Request $request)
    {
        $galery = Galery::find($id);
        $galery->state = $request->input("state");
        $galery->save();

        return Response::json(['success' => true]);
    }

    public function updatePositions(Request $request)
    {
        foreach ($request->input("images") as $key => $image) {
            $galery = Galery::find($image["id"]);
            $galery->position = $key + 1;
            $galery->save();
        }
        return Response::json(['success' => true]);
    }

    public function maxPosition(Request $request)
    {
        $max_position = Galery::where('object_id', $request->input("object_id"))
            ->where('type', $request->input("type"))
            ->max('position');

        if ($max_position === null or $max_position === '') {
            $max_position = 0;
        }

        return Response::json(['success' => true, 'position' => $max_position]);
    }

    public function addUrls(Request $request)
    {
        $galery = new Galery();
        $galery->type = $request->input("type");
        $galery->object_id = $request->input("object_id");
        $galery->position = $request->input("position");
        $galery->slug = $request->input("slug");
        $galery->url = $request->input("url");
        $galery->state = $request->input("state");
        $galery->save();

        if ($galery->type === "service" && $galery->slug === "service_gallery") {
            ProgressBar::updateOrCreate(
                [
                    'slug' => 'service_progress_gallery',
                    'value' => 10,
                    'type' => 'service',
                    'object_id' => $galery->object_id
                ]
            );
        }

        return Response::json(['success' => true]);
    }

    // ------------ galeria hotel - logo -------------------- //
    public function galleryHotelLogo(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $gallery = Galery::where('object_id', $request->input("object_id"))
            ->where('slug', 'hotel_logo')
            ->first();
        if ($gallery != null) {
            $gallery->restore();

            $galleryup = Galery::find($gallery->id);
            $galleryup->type = $request->input("type");
            $galleryup->object_id = $request->input("object_id");
            $galleryup->position = $request->input("position");
            $galleryup->slug = $request->input("slug");
            $galleryup->state = $request->input("state");
            $galleryup->save();


            if ($request->input("url") == '') {
                $galleryup->url = md5($request->input("object_id").'_'.$galleryup->id).'.png';
            } else {
                $galleryup->url = $request->input("url");
            }

            $galleryup->save();

            if ($request->input("image") != '') {
                $this->imagesSave(
                    Auth::user()->id.$request->input("image"),
                    'galeries',
                    $galleryup->object_id.'_'.$galleryup->id
                );
            } else {
                $this->imagesSave(Auth::user()->id, 'galeries', $galleryup->object_id.'_'.$galleryup->id);
            }
            if ($gallery->type === "hotel" && $gallery->slug === "hotel_logo") {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'hotel_progress_logo',
                        'value' => 5,
                        'type' => 'hotel',
                        'object_id' => $gallery->object_id
                    ]
                );
            }
        } else {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:facility,hotel,room,amenity,client,package,service,classification,train,state',
                'object_id' => 'required|integer|numeric',
                'position' => 'required|integer|numeric|unique:galeries,position,NULL,id,type,'.$request->input("type").',object_id,'.$request->input("object_id"),
                'url' => 'nullable|string|max:100',
                'slug' => 'nullable|string|in:hotel_logo',
                'state' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();

                foreach ($errors->all() as $error) {
                    array_push($arrayErrors, $error);
                }

                $countErrors++;
            }

            if ($countErrors > 0) {
                return Response::json(['success' => false, 'errors' => $arrayErrors]);
            } else {
                $gallery = new Galery();
                $gallery->type = $request->input("type");
                $gallery->object_id = $request->input("object_id");
                $gallery->position = $request->input("position");
                $gallery->slug = $request->input("slug");
                $gallery->url = "test.png";
                $gallery->state = $request->input("state");
                $gallery->save();
            }

            if ($request->input("url") == '') {
                $gallery->url = md5($request->input("object_id").'_'.$gallery->id).'.png';
            } else {
                $gallery->url = $request->input("url");
            }

            $gallery->save();

            if ($request->input("image") != '') {
                $this->imagesSave(
                    Auth::user()->id.$request->input("image"),
                    'galeries',
                    $gallery->object_id.'_'.$gallery->id
                );
            } else {
                $this->imagesSave(Auth::user()->id, 'galeries', $gallery->object_id.'_'.$gallery->id);
            }
            if ($gallery->type === "hotel" && $gallery->slug === "hotel_logo") {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'hotel_progress_logo',
                        'value' => 5,
                        'type' => 'hotel',
                        'object_id' => $gallery->object_id
                    ]
                );
            }
        }

        return Response::json(['success' => true]);
    }

    // ------------ galeria Client - logo -------------------- //
    public function galleryClientLogo(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $gallery = Galery::where('object_id', $request->input("object_id"))
            ->where('slug', 'client_logo')
            ->where('type', 'client')
            ->first();
        if ($gallery != null) {
            $gallery->restore();

            $galleryup = Galery::find($gallery->id);
            $galleryup->type = $request->input("type");
            $galleryup->object_id = $request->input("object_id");
            $galleryup->position = $request->input("position");
            $galleryup->slug = $request->input("slug");
            $galleryup->state = $request->input("state");
            $galleryup->save();


            if ($request->input("url") == '') {
                $galleryup->url = md5($request->input("object_id").'_'.$galleryup->id).'.png';
            } else {
                $galleryup->url = $request->input("url");
            }
            $galleryup->save();

            if ($request->input("image") != '') {
                $this->imagesSave(
                    Auth::user()->id.$request->input("image"),
                    'galeries',
                    $galleryup->object_id.'_'.$galleryup->id
                );
            } else {
                $this->imagesSave(Auth::user()->id, 'galeries', $galleryup->object_id.'_'.$galleryup->id);
            }
            if ($gallery->type === "client" && $gallery->slug === "client_logo") {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'client_progress_logo',
                        'value' => 5,
                        'type' => 'client',
                        'object_id' => $gallery->object_id
                    ]
                );
            }
        } else {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:facility,hotel,room,amenity,client,package,service,classification,train,state',
                'object_id' => 'required|integer|numeric',
                'position' => 'required|integer|numeric|unique:galeries,position,NULL,id,type,'.$request->input("type").',object_id,'.$request->input("object_id").',deleted_at,NULL',
                'url' => 'nullable|string|max:100',
                'slug' => 'nullable|string|in:client_logo',
                'state' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $error) {
                    array_push($arrayErrors, $error);
                }

                $countErrors++;
            }

            if ($countErrors > 0) {
                return Response::json(['success' => false, 'error' => $validator->getMessageBag()->toArray()]);
            } else {
                $gallery = new Galery();
                $gallery->type = $request->input("type");
                $gallery->object_id = $request->input("object_id");
                $gallery->position = $request->input("position");
                $gallery->slug = $request->input("slug");
                $gallery->url = "test.png";
                $gallery->state = $request->input("state");
                $gallery->save();
            }

            if ($request->input("url") == '') {
                $gallery->url = md5($request->input("object_id").'_'.$gallery->id).'.png';
            } else {
                $gallery->url = $request->input("url");
            }

            $gallery->save();

            if ($request->input("image") != '') {
                $this->imagesSave(
                    Auth::user()->id.$request->input("image"),
                    'galeries',
                    $gallery->object_id.'_'.$gallery->id
                );
            } else {
                $this->imagesSave(Auth::user()->id, 'galeries', $gallery->object_id.'_'.$gallery->id);
            }
            if ($gallery->type === "client" && $gallery->slug === "client_logo") {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'client_progress_logo',
                        'value' => 5,
                        'type' => 'client',
                        'object_id' => $gallery->object_id
                    ]
                );
            }
        }

        return Response::json(['success' => true]);
    }

    // ------------ galeria Seller - logo -------------------- //
    public function gallerySellerLogo(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $gallery = Galery::where('object_id', $request->input("object_id"))
            ->where('slug', 'seller_logo')
            ->first();
        if ($gallery != null) {
            $gallery->restore();

            $galleryup = Galery::find($gallery->id);
            $galleryup->type = $request->input("type");
            $galleryup->object_id = $request->input("object_id");
            $galleryup->position = $request->input("position");
            $galleryup->slug = $request->input("slug");
            $galleryup->state = $request->input("state");
            $galleryup->save();


            if ($request->input("url") == '') {
                $galleryup->url = md5($request->input("object_id").'_'.$galleryup->id).'.png';
            } else {
                $galleryup->url = $request->input("url");
            }

            $galleryup->save();

            if ($request->input("image") != '') {
                $this->imagesSave(
                    Auth::user()->id.$request->input("image"),
                    'galeries',
                    $galleryup->object_id.'_'.$galleryup->id
                );
            } else {
                $this->imagesSave(Auth::user()->id, 'galeries', $galleryup->object_id.'_'.$galleryup->id);
            }
            if ($gallery->type === "seller" && $gallery->slug === "seller_logo") {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'seller_progress_logo',
                        'value' => 5,
                        'type' => 'seller',
                        'object_id' => $gallery->object_id
                    ]
                );
            }
        } else {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:facility,hotel,room,amenity,client,seller,package,service,classification,train,state',
                'object_id' => 'required|integer|numeric',
                'position' => 'required|integer|numeric|unique:galeries,position,NULL,id,type,'.$request->input("type").',object_id,'.$request->input("object_id"),
                'url' => 'nullable|string|max:100',
                'slug' => 'nullable|string|in:seller_logo',
                'state' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $error) {
                    array_push($arrayErrors, $error);
                }

                $countErrors++;
            }

            if ($countErrors > 0) {
                return Response::json(['success' => false, 'errors' => $arrayErrors]);
            } else {
                $gallery = new Galery();
                $gallery->type = $request->input("type");
                $gallery->object_id = $request->input("object_id");
                $gallery->position = $request->input("position");
                $gallery->slug = $request->input("slug");
                $gallery->url = "test.png";
                $gallery->state = $request->input("state");
                $gallery->save();
            }

            if ($request->input("url") == '') {
                $gallery->url = md5($request->input("object_id").'_'.$gallery->id).'.png';
            } else {
                $gallery->url = $request->input("url");
            }

            $gallery->save();

            if ($request->input("image") != '') {
                $this->imagesSave(
                    Auth::user()->id.$request->input("image"),
                    'galeries',
                    $gallery->object_id.'_'.$gallery->id
                );
            } else {
                $this->imagesSave(Auth::user()->id, 'galeries', $gallery->object_id.'_'.$gallery->id);
            }
            if ($gallery->type === "seller" && $gallery->slug === "seller_logo") {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'seller_progress_logo',
                        'value' => 5,
                        'type' => 'seller',
                        'object_id' => $gallery->object_id
                    ]
                );
            }
        }

        return Response::json(['success' => true]);
    }

    public function removeImageLogo($id)
    {
        $gallery = Galery::find($id);
        $gallery->delete();
        $this->imagesRemove('galeries', $gallery->object_id.'_'.$id);
        return Response::json(['success' => true]);
    }
    // ------------ galeria hotel - logo -------------------- //
    // ------------ galeria facility -------------------- //
    public function galleryFacilities(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $gallery = Galery::where('object_id', $request->input("object_id"))
            ->where('slug', 'facility')
            ->first();
        if ($gallery != null) {
            $gallery->restore();

            $galleryup = Galery::find($gallery->id);
            $galleryup->type = $request->input("type");
            $galleryup->object_id = $request->input("object_id");
            $galleryup->position = $request->input("position");
            $galleryup->slug = $request->input("slug");
            $galleryup->state = $request->input("state");
            $galleryup->save();


            if ($request->input("url") == '') {
                $galleryup->url = md5($request->input("object_id").'_'.$galleryup->id).'.png';
            } else {
                $galleryup->url = $request->input("url");
            }

            $galleryup->save();

            if ($request->input("image") != '') {
                $this->imagesSave(
                    Auth::user()->id.$request->input("image"),
                    'galeries',
                    $galleryup->object_id.'_'.$galleryup->id
                );
            } else {
                $this->imagesSave(Auth::user()->id, 'galeries', $galleryup->object_id.'_'.$galleryup->id);
            }
        } else {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:facility,hotel,room,amenity,client,package,service,classification,train,state',
                'object_id' => 'required|integer|numeric',
                'position' => 'required|integer|numeric|unique:galeries,position,NULL,id,type,'.$request->input("type").',object_id,'.$request->input("object_id"),
                'url' => 'nullable|string|max:100',
                'slug' => 'nullable|string|in:facility',
                'state' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();

                foreach ($errors->all() as $error) {
                    array_push($arrayErrors, $error);
                }

                $countErrors++;
            }

            if ($countErrors > 0) {
                return Response::json(['success' => false, 'errors' => $arrayErrors]);
            } else {
                $gallery = new Galery();
                $gallery->type = $request->input("type");
                $gallery->object_id = $request->input("object_id");
                $gallery->position = $request->input("position");
                $gallery->slug = $request->input("slug");
                $gallery->url = "test.png";
                $gallery->state = $request->input("state");
                $gallery->save();
            }

            if ($request->input("url") == '') {
                $gallery->url = md5($request->input("object_id").'_'.$gallery->id).'.png';
            } else {
                $gallery->url = $request->input("url");
            }

            $gallery->save();

            if ($request->input("image") != '') {
                $this->imagesSave(
                    Auth::user()->id.$request->input("image"),
                    'galeries',
                    $gallery->object_id.'_'.$gallery->id
                );
            } else {
                $this->imagesSave(Auth::user()->id, 'galeries', $gallery->object_id.'_'.$gallery->id);
            }
        }
        return Response::json(['success' => true]);
    }

    public function removeImageFacility($id)
    {
        $gallery = Galery::find($id);
        $gallery->delete();
        $this->imagesRemove('galeries', $gallery->object_id.'_'.$id);
        return Response::json(['success' => true]);
    }
    // ------------ galeria facility -------------------- //
    // ------------ galeria amenity -------------------- //
    public function galleryAmenity(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $gallery = Galery::where('object_id', $request->input("object_id"))
            ->where('slug', 'amenity')
            ->first();
        if ($gallery != null) {
            $gallery->restore();

            $galleryup = Galery::find($gallery->id);
            $galleryup->type = $request->input("type");
            $galleryup->object_id = $request->input("object_id");
            $galleryup->position = $request->input("position");
            $galleryup->slug = $request->input("slug");
            $galleryup->state = $request->input("state");
            $galleryup->save();


            if ($request->input("url") == '') {
                $galleryup->url = md5($request->input("object_id").'_'.$galleryup->id).'.png';
            } else {
                $galleryup->url = $request->input("url");
            }

            $galleryup->save();

            if ($request->input("image") != '') {
                $this->imagesSave(
                    Auth::user()->id.$request->input("image"),
                    'galeries',
                    $galleryup->object_id.'_'.$galleryup->id
                );
            } else {
                $this->imagesSave(Auth::user()->id, 'galeries', $galleryup->object_id.'_'.$galleryup->id);
            }
        } else {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:facility,hotel,room,amenity,client,package,service,classification,train,state',
                'object_id' => 'required|integer|numeric',
                'position' => 'required|integer|numeric|unique:galeries,position,NULL,id,type,'.$request->input("type").',object_id,'.$request->input("object_id"),
                'url' => 'nullable|string|max:100',
                'slug' => 'nullable|string|in:amenity',
                'state' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();

                foreach ($errors->all() as $error) {
                    array_push($arrayErrors, $error);
                }

                $countErrors++;
            }

            if ($countErrors > 0) {
                return Response::json(['success' => false, 'errors' => $arrayErrors]);
            } else {
                $gallery = new Galery();
                $gallery->type = $request->input("type");
                $gallery->object_id = $request->input("object_id");
                $gallery->position = $request->input("position");
                $gallery->slug = $request->input("slug");
                $gallery->url = "test.png";
                $gallery->state = $request->input("state");
                $gallery->save();
            }

            if ($request->input("url") == '') {
                $gallery->url = md5($request->input("object_id").'_'.$gallery->id).'.png';
            } else {
                $gallery->url = $request->input("url");
            }

            $gallery->save();

            if ($request->input("image") != '') {
                $this->imagesSave(
                    Auth::user()->id.$request->input("image"),
                    'galeries',
                    $gallery->object_id.'_'.$gallery->id
                );
            } else {
                $this->imagesSave(Auth::user()->id, 'galeries', $gallery->object_id.'_'.$gallery->id);
            }
        }

        return Response::json(['success' => true]);
    }

    public function removeImageAmenity($id)
    {
        $gallery = Galery::find($id);
        $gallery->delete();
        $this->imagesRemove('galeries', $gallery->object_id.'_'.$id);
        return Response::json(['success' => true]);
    }
    // ------------ galeria amenity -------------------- //
    //-------------galeria hotel manage ---------------- //
    public function indexHotelGallery($id)
    {
        $images = $this->searchGalleryCloudinary('hotel', $id);
        $galleries = [];

        foreach($images as $key => $value)
        {
            $galleries[$key]['object_id'] = $id;
            $galleries[$key]['type'] = 'hotel';
            $galleries[$key]['slug'] = 'hotel_gallery';
            $galleries[$key]['url'] = $value;
            $galleries[$key]['position'] = ($key + 1);
            $galleries[$key]['state'] = 1;
        }

        return Response::json(['success' => true, 'data' => $galleries]);
    }

    public function maxPositionHotelGallery(Request $request)
    {
        $max_position = Galery::where('object_id', $request->input("object_id"))
            ->where('type', 'hotel')
            ->where('slug', 'hotel_gallery')->max('position');

        return Response::json(['success' => true, 'position' => $max_position]);
    }

    //-------------galeria hotel manage ---------------- //
    //-------------galeria package manage ---------------- //
    public function indexPackageGallery($id)
    {
        $galleries = Galery::where('object_id', $id)
            ->where('type', 'package')
            ->where('slug', 'package_gallery')->orderBy('position')->get();

        return Response::json(['success' => true, 'data' => $galleries]);
    }

    public function maxPositionPackageGallery(Request $request)
    {
        $max_position = Galery::where('object_id', $request->input("object_id"))
            ->where('type', 'package')
            ->where('slug', 'package_gallery')->max('position');

        return Response::json(['success' => true, 'position' => $max_position]);
    }

    //-------------galeria package manage ---------------- //
    //-------------galeria service manage ---------------- //
    public function indexServiceGallery($id)
    {
        $images = $this->searchGalleryCloudinary('service', $id);
        $galleries = [];

        foreach($images as $key => $value)
        {
            $galleries[$key]['object_id'] = $id;
            $galleries[$key]['type'] = 'service';
            $galleries[$key]['slug'] = 'service_gallery';
            $galleries[$key]['url'] = $value;
            $galleries[$key]['position'] = ($key + 1);
            $galleries[$key]['state'] = 1;
        }

        return Response::json(['success' => true, 'data' => $galleries]);
    }

    public function maxPositionServiceGallery(Request $request)
    {
        $max_position = Galery::where('object_id', $request->input("object_id"))
            ->where('type', 'service')
            ->where('slug', 'service_gallery')->max('position');

        return Response::json(['success' => true, 'position' => $max_position]);
    }

    //-------------galeria service manage ---------------- //
    //-------------galeria train manage ---------------- //
    public function indexTrainGallery($id)
    {
        $galleries = Galery::where('object_id', $id)
            ->where('type', 'train')
            ->where('slug', 'train_gallery')->get();

        return Response::json(['success' => true, 'data' => $galleries]);
    }

    public function maxPositionTrainGallery(Request $request)
    {
        $max_position = Galery::where('object_id', $request->input("object_id"))
            ->where('type', 'train')
            ->where('slug', 'train_gallery')->max('position');

        return Response::json(['success' => true, 'position' => $max_position]);
    }

    //-------------galeria train manage ---------------- //
    // ------------ galeria classification -------------------- //
    public function galleryClassification(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $gallery = Galery::where('object_id', $request->input("object_id"))
            ->where('slug', 'classification')
            ->first();
        if ($gallery != null) {
            $gallery->restore();

            $galleryup = Galery::find($gallery->id);
            $galleryup->type = $request->input("type");
            $galleryup->object_id = $request->input("object_id");
            $galleryup->position = $request->input("position");
            $galleryup->slug = $request->input("slug");
            $galleryup->state = $request->input("state");
            $galleryup->save();


            if ($request->input("url") == '') {
                $galleryup->url = md5($request->input("object_id").'_'.$galleryup->id).'.png';
            } else {
                $galleryup->url = $request->input("url");
            }

            $galleryup->save();

            if ($request->input("image") != '') {
                $this->imagesSave(
                    Auth::user()->id.$request->input("image"),
                    'galeries',
                    $galleryup->object_id.'_'.$galleryup->id
                );
            } else {
                $this->imagesSave(Auth::user()->id, 'galeries', $galleryup->object_id.'_'.$galleryup->id);
            }
        } else {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:facility,hotel,room,amenity,client,package,service,classification,train,state',
                'object_id' => 'required|integer|numeric',
                'position' => 'required|integer|numeric|unique:galeries,position,NULL,id,type,'.$request->input("type").',object_id,'.$request->input("object_id"),
                'url' => 'nullable|string|max:100',
                'slug' => 'nullable|string|in:classification',
                'state' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();

                foreach ($errors->all() as $error) {
                    array_push($arrayErrors, $error);
                }

                $countErrors++;
            }

            if ($countErrors > 0) {
                return Response::json(['success' => false, 'errors' => $arrayErrors]);
            } else {
                $gallery = new Galery();
                $gallery->type = $request->input("type");
                $gallery->object_id = $request->input("object_id");
                $gallery->position = $request->input("position");
                $gallery->slug = $request->input("slug");
                $gallery->url = "test.png";
                $gallery->state = $request->input("state");
                $gallery->save();
            }

            if ($request->input("url") == '') {
                $gallery->url = md5($request->input("object_id").'_'.$gallery->id).'.png';
            } else {
                $gallery->url = $request->input("url");
            }

            $gallery->save();

            if ($request->input("image") != '') {
                $this->imagesSave(
                    Auth::user()->id.$request->input("image"),
                    'galeries',
                    $gallery->object_id.'_'.$gallery->id
                );
            } else {
                $this->imagesSave(Auth::user()->id, 'galeries', $gallery->object_id.'_'.$gallery->id);
            }
        }

        return Response::json(['success' => true]);
    }

    public function removeImageClassification($id)
    {
        $gallery = Galery::find($id);
        $gallery->delete();
        $this->imagesRemove('galeries', $gallery->object_id.'_'.$id);
        return Response::json(['success' => true]);
    }

    //-------------galeria state manage ---------------- //
    public function indexStateGallery($id)
    {
        $galleries = Galery::where('object_id', $id)
            ->where('type', 'state')
            ->where('slug', 'state_gallery')->orderBy('position')->get();

        return Response::json(['success' => true, 'data' => $galleries]);
    }

    public function maxPositionStateGallery(Request $request)
    {
        $max_position = Galery::where('object_id', $request->input("object_id"))
            ->where('type', 'state')
            ->where('slug', 'state_gallery')->max('position');
        return Response::json(['success' => true, 'position' => $max_position]);
    }

    //-------------galeria state manage ---------------- //


    // ------------ Inicio galeria categoria de preguntas -------------------- //
    public function galleryQuestionCategory(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $gallery = Galery::where('object_id', $request->input("object_id"))
            ->where('slug', 'question_category')
            ->first();
        if ($gallery != null) {
            $gallery->restore();

            $galleryup = Galery::find($gallery->id);
            $galleryup->type = $request->input("type");
            $galleryup->object_id = $request->input("object_id");
            $galleryup->position = $request->input("position");
            $galleryup->slug = $request->input("slug");
            $galleryup->state = $request->input("state");
            $galleryup->save();


            if ($request->input("url") == '') {
                $galleryup->url = md5($request->input("object_id").'_'.$galleryup->id).'.png';
            } else {
                $galleryup->url = $request->input("url");
            }

            $galleryup->save();

            if ($request->input("image") != '') {
                $this->imagesSave(
                    Auth::user()->id.$request->input("image"),
                    'galeries',
                    $galleryup->object_id.'_'.$galleryup->id
                );
            } else {
                $this->imagesSave(Auth::user()->id, 'galeries', $galleryup->object_id.'_'.$galleryup->id);
            }
        } else {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:facility,hotel,room,amenity,client,package,service,classification,train,state,question_category',
                'object_id' => 'required|integer|numeric',
                'position' => 'required|integer|numeric|unique:galeries,position,NULL,id,type,'.$request->input("type").',object_id,'.$request->input("object_id"),
                'url' => 'nullable|string|max:100',
                'slug' => 'nullable|string|in:question_category',
                'state' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();

                foreach ($errors->all() as $error) {
                    array_push($arrayErrors, $error);
                }

                $countErrors++;
            }

            if ($countErrors > 0) {
                return Response::json(['success' => false, 'errors' => $arrayErrors]);
            } else {
                $gallery = new Galery();
                $gallery->type = $request->input("type");
                $gallery->object_id = $request->input("object_id");
                $gallery->position = $request->input("position");
                $gallery->slug = $request->input("slug");
                $gallery->url = "test.png";
                $gallery->state = $request->input("state");
                $gallery->save();
            }

            if ($request->input("url") == '') {
                $gallery->url = md5($request->input("object_id").'_'.$gallery->id).'.png';
            } else {
                $gallery->url = $request->input("url");
            }

            $gallery->save();

            if ($request->input("image") != '') {
                $this->imagesSave(
                    Auth::user()->id.$request->input("image"),
                    'galeries',
                    $gallery->object_id.'_'.$gallery->id
                );
            } else {
                $this->imagesSave(Auth::user()->id, 'galeries', $gallery->object_id.'_'.$gallery->id);
            }
        }

        return Response::json(['success' => true]);
    }

    public function removeImageQuestionCategory($id)
    {
        $gallery = Galery::find($id);
        $gallery->delete();
        $this->imagesRemove('galeries', $gallery->object_id.'_'.$id);
        return Response::json(['success' => true]);
    }
    // ------------ Fin categoria de preguntas -------------------- //
}
