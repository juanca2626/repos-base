<?php

namespace App\Http\Controllers;

use App\ClientInformationImportantService;
use App\InformationImportantService;
use App\Language;
use App\ServiceInformationImportant;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class InformationImportantServiceController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:informationimportantservices.read')->only('index');
        $this->middleware('permission:informationimportantservices.create')->only('store');
        $this->middleware('permission:informationimportantservices.update')->only('update');
        $this->middleware('permission:informationimportantservices.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $client_id = $this->getClientId($request);
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $important_info = InformationImportantService::with([
            'translations' => function ($query) use ($language) {
                $query->where('type', 'information_important_service');
                $query->where('language_id', $language->id);
            }
        ])->orderBy('id', 'desc');

        if (!empty($client_id)) {
            $important_info = $important_info->whereHas('client_info_important', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            });
        } else {
            $important_info = $important_info->whereDoesntHave('client_info_important');
        }

        $count = $important_info->count();
        if ($querySearch) {
            $important_info->where(function ($query) use ($querySearch, $language) {
                $query->whereHas('translations', function ($query) use ($querySearch, $language) {
                    $query->where('type', 'information_important_service');
                    $query->where('language_id', $language->id);
                    $query->where('value', 'like', '%'.$querySearch.'%');
                });
            });
        }

        if ($paging === 1) {
            $important_info = $important_info->take($limit)->get();
        } else {
            $important_info = $important_info->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $important_info,
            'count' => $count,
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
        $arrayErrors = [];
        $countErrors = 0;
        $client_id = $this->getClientId($request);

        $validate = [];
        if (empty($client_id)) {
            $validate = [
                'translations.*.info_important_service_name' => 'unique:translations,value,NULL,id,type,information_important_service'
            ];
        }

        $validator = Validator::make($request->all(), $validate);

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
            $important_info = new InformationImportantService();
            $important_info->save();
            if (!empty($client_id)) {
                $newClientService = new ClientInformationImportantService();
                $newClientService->client_id = $client_id;
                $newClientService->info_important_service_id = $important_info->id;
                $newClientService->save();
            }
            $this->saveTranslation($request->input("translations"), 'information_important_service',
                $important_info->id);
            return Response::json(['success' => true]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\InformationImportantService  $inportantInformationService
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $classification = InformationImportantService::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'information_important_service');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $classification]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\InformationImportantService  $inportantInformationService
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;
        $client_id = $this->getClientId($request);

        $validate = [];
        if (empty($client_id)) {
            $validate = [
                'translations.*.info_important_service_name' => 'unique:translations,value,'.$id.',object_id,type,information_important_service'
            ];
        }

        $validator = Validator::make($request->all(), $validate);

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
            $this->saveTranslation($request->input("translations"), 'information_important_service', $id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\InformationImportantService  $inportantInformationService
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $info_important = InformationImportantService::find($id);
        $info_important_used = ServiceInformationImportant::where('info_important_service_id', $id)->get();
        if ($info_important_used->count() == 0) {
            $info_important->delete();
            $this->deleteTranslation('information_important_service', $id);
            $used = false;
            $success = true;
        } else {
            $used = true;
            $success = false;
        }
        return Response::json(['success' => $success, 'used' => $used]);
    }

    public function selectBox(Request $request)
    {
        $client_id = $this->getClientId($request);
        $lang = $request->input('lang');
        $language = Language::where('iso', $lang)->first();
        $info_important = InformationImportantService::with([
            'translations' => function ($query) use ($language) {
                $query->where('type', 'information_important_service');
                $query->where('language_id', $language->id);
            }
        ]);

        if (!empty($client_id)) {
            $info_important = $info_important->whereHas('client_info_important', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            });
        } else {
            $info_important = $info_important->whereDoesntHave('client_info_important');
        }

        $info_important = $info_important->get();
        return Response::json(['success' => true, 'data' => $info_important]);
    }
}
