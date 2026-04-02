<?php

namespace App\Http\Controllers;

use App\ClientInclusion;
use App\ClientServiceTypeActivity;
use App\HotelInclusionChildren;
use App\Inclusion;
use App\Language;
use App\Mail\NotificationInclusion;
use App\ServiceInclusion;
use App\Http\Traits\Translations;
use App\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class InclusionController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:inclusions.read')->only('index');
        $this->middleware('permission:inclusions.create')->only('store');
        $this->middleware('permission:inclusions.update')->only('update');
        $this->middleware('permission:inclusions.delete')->only('delete');
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
        $inclusion = Inclusion::with([
            'translations' => function ($query) {
                $query->where('type', 'inclusion');
                $query->with('language');
            }
        ])->orderBy('id', 'desc');

        if ($querySearch) {

            $translations = Translation::where('type', 'inclusion')->where(function ($query) use ($querySearch) {
                $query->orWhere('value', 'like', '% ' . $querySearch . '%');
                $query->orWhere('value', 'like', $querySearch . '%');
            })
                ->get();
            if ($translations->count() > 0) {
                $inclusion_ids = $translations->pluck('object_id');
                $inclusion->whereIn('id', $inclusion_ids);
            }
        }

        if (!empty($client_id)) {
            $inclusion = $inclusion->whereHas('client_inclusions', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            });
        } else {
            $inclusion = $inclusion->whereDoesntHave('client_inclusions');
        }

        $count = $inclusion->count();
        if ($paging === 1) {
            $inclusion = $inclusion->take($limit)->get();
        } else {
            $inclusion = $inclusion->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $inclusion,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
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
                'translations.*.inclusion_name' => 'unique:translations,value,NULL,id,type,inclusion'
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
            $days = $request->input("days");
            $inclusion = new Inclusion();
            $inclusion->monday = $days['monday'];
            $inclusion->tuesday = $days['tuesday'];
            $inclusion->wednesday = $days['wednesday'];
            $inclusion->thursday = $days['thursday'];
            $inclusion->friday = $days['friday'];
            $inclusion->saturday = $days['saturday'];
            $inclusion->sunday = $days['sunday'];
            $inclusion->save();
            $this->saveTranslation($request->input("translations"), 'inclusion', $inclusion->id);

            if (!empty($client_id)) {
                $newClientService = new ClientInclusion();
                $newClientService->client_id = $client_id;
                $newClientService->inclusion_id = $inclusion->id;
                $newClientService->save();
            }

            if (empty($client_id)) {
                $this->buildDataNotification(
                    $inclusion->id,
                    'create',
                    $request->input("translations"),
                    ['mlu@limatours.com.pe', 'vcq@limatours.com.pe'],
                    '');
            }

            if (($request->has('hasNotify') and $request->input('hasNotify')) and
                ($request->has('emails') and count($request->input('emails')) > 0 and empty($client_id))
            ) {
                $this->buildDataNotification(
                    $inclusion->id,
                    'create',
                    $request->input("translations"),
                    $request->input('emails'),
                    $request->input('message'));
            }
            return Response::json(['success' => true]);
        }

    }

    public function buildDataNotification($id, $action, $translations, $emails, $message)
    {
        $translation_dirty = [];
        foreach ($translations as $key => $translation) {
            if ($key == 1) {
                $translation_dirty[$key]['language'] = 'es';
                $translation_es = $translation['inclusion_name'];
            }
            if ($key == 2) {
                $translation_dirty[$key]['language'] = 'en';
            }
            if ($key == 3) {
                $translation_dirty[$key]['language'] = 'pt';
            }
            if ($key == 4) {
                $translation_dirty[$key]['language'] = 'it';
            }
            $translation_dirty[$key]['name'] = ($translation['inclusion_name'] == null) ? $translation_es : $translation['inclusion_name'];
        }

        $data = [
            'id' => $id,
            'action' => $action,
            'translations' => $translation_dirty,
            'message' => $message,
        ];
        Mail::to($emails)->send(new NotificationInclusion($data));
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $classification = Inclusion::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'inclusion');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $classification]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;
        $client_id = $this->getClientId($request);

        $validate = [];
        if (empty($client_id)) {
            $validate = [];
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
            $days = $request->input("days");
            $inclusion = Inclusion::find($id);
            $inclusion->monday = $days['monday'];
            $inclusion->tuesday = $days['tuesday'];
            $inclusion->wednesday = $days['wednesday'];
            $inclusion->thursday = $days['thursday'];
            $inclusion->friday = $days['friday'];
            $inclusion->saturday = $days['saturday'];
            $inclusion->sunday = $days['sunday'];
            $inclusion->save();

            $translations = $request->input("translations");
            if (is_array($translations)) {
                $translations = array_filter($translations, function ($translation) {
                    return !empty($translation['id']);
                });
            }

            $this->saveTranslation($translations, 'inclusion', $id);

            if (empty($client_id)) {
                $this->buildDataNotification(
                    $id,
                    'update',
                    $request->input("translations"),
                    ['mlu@limatours.com.pe','vcq@limatours.com.pe'],
                    '');
            }

            if (($request->has('hasNotify') and $request->input('hasNotify')) and
                ($request->has('emails') and count($request->input('emails')) > 0 and empty($client_id))
            ) {
                $this->buildDataNotification(
                    $id,
                    'update',
                    $request->input("translations"),
                    $request->input('emails'),
                    $request->input('message'));
            }
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $classification = Inclusion::find($id);
        $classification_used = ServiceInclusion::where('inclusion_id', $id)->get();
        $hotel_children_used = HotelInclusionChildren::where('inclusion_id', $id)->get();
        if ($classification_used->count() == 0 && $hotel_children_used->count() == 0) {
            $classification->delete();
            $this->deleteTranslation('inclusion', $id);
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
        $inclusions = Inclusion::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'inclusion');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ]);

        if (!empty($client_id)) {
            $inclusions = $inclusions->whereHas('client_inclusions', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            });
        } else {
            $inclusions = $inclusions->whereDoesntHave('client_inclusions');
        }

        $inclusions = $inclusions->get();


        return Response::json(['success' => true, 'data' => $inclusions]);
    }

    public function selectBoxFilter(Request $request)
    {

        $lang = $request->has('lang') ? $request->input('lang') : 'es';
        $limit = $request->has('limit') ? $request->input('limit') : 50;
        $filter = $request->has('filter') ? $request->input('filter') : '';
        $language = Language::where('iso', $lang)->first(['id']);

        $client_inclusions = ClientInclusion::get(['inclusion_id'])->pluck('inclusion_id');

        $inclusions = Translation::where('type', 'inclusion')
            ->where('slug', 'inclusion_name')
            ->where('language_id', $language->id);

        if (!empty($filter)) {
            $inclusions = $inclusions->where('value', 'like', '%' . $filter . '%');
        }

        $inclusions = $inclusions->whereNotIn('object_id', $client_inclusions)
            ->limit($limit)
            ->get(['object_id as id', 'value as name']);


        return Response::json(['success' => true, 'data' => $inclusions]);
    }

    public function updateOperability(Request $request, $id)
    {
        $day = $request->get('day');
        $status = $request->get('status');
        $inclusion = Inclusion::find($id);
        $inclusion->$day = $status;
        $inclusion->save();
        return Response::json(['success' => true, 'data' => $inclusion]);

    }

    public function translations_update(Request $request)
    {
        $data_update = $request->input('data_update');

        foreach ($data_update as $data) {
            $trans = Translation::find($data['id']);
            $trans->value = $data['value'];
            $trans->save();
        }

        return Response::json(['success' => true]);

    }


}
