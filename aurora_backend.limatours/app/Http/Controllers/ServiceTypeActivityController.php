<?php

namespace App\Http\Controllers;

use App\ClientServiceTypeActivity;
use App\Language;
use App\Mail\NotificationOperability;
use App\ServiceOperationActivity;
use App\ServiceTypeActivity;
use App\Http\Traits\Translations;
use App\Translation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ServiceTypeActivityController extends Controller
{

    use Translations;

    public function __construct()
    {
        $this->middleware('permission:servicetypeactivities.read')->only('index');
        $this->middleware('permission:servicetypeactivities.create')->only('store');
        $this->middleware('permission:servicetypeactivities.update')->only('update');
        $this->middleware('permission:servicetypeactivities.delete')->only('delete');
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
        $language_id = Language::where('iso', $lang)->first()->id;
        $typeActivity = ServiceTypeActivity::with([
            'translations' => function ($query) use ($querySearch, $language_id) {
                $query->select('id', 'object_id', 'value');
                $query->where('type', 'servicetypeactivity');
                $query->where('language_id', '=', $language_id);
            }
        ])->orderBy('id', 'desc');

        if ($querySearch) {

            $translations = Translation::where('type', 'servicetypeactivity')->where(function ($query) use ($querySearch) {
                $filters = explode(" ", $querySearch);

                foreach ($filters as $key => $value) {
                    $query->where('value', 'like', '%' . $value . '%');
                }
            })
                ->where('language_id', $language_id)
                ->get();
            if ($translations->count() > 0) {
                $inclusion_ids = $translations->pluck('object_id');
                $typeActivity->whereIn('id', $inclusion_ids);
            }
        }

        if (!empty($client_id)) {
            $typeActivity = $typeActivity->whereHas('client_service_type_activity', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            });
        } else {
            $typeActivity = $typeActivity->whereDoesntHave('client_service_type_activity');
        }

        $count = $typeActivity->count();
        if ($paging === 1) {
            $typeActivity = $typeActivity->take($limit)->get();
        } else {
            $typeActivity = $typeActivity->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $typeActivity,
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
                'translations.*.type_activity_name' => 'unique:translations,value,NULL,id,type,servicetypeactivity'
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
            return Response::json(['success' => false, 'error' => $errors]);
        } else {
            $activity = new ServiceTypeActivity();
            $activity->user_id = auth_user()->id;
            $activity->save();
            $this->saveTranslation($request->input("translations"), 'servicetypeactivity', $activity->id);

            if (!empty($client_id)) {
                $newClientService = new ClientServiceTypeActivity();
                $newClientService->client_id = $client_id;
                $newClientService->service_type_activity_id = $activity->id;
                $newClientService->save();
            }

            if (empty($client_id)) {
                $emails_ = ['mlu@limatours.com.pe', 'vcq@limatours.com.pe', auth_user()->email];

                $this->buildDataNotification(
                    $activity->id,
                    'create',
                    $request->input("translations"),
                    $emails_,
                    '');
            }

            if (($request->has('hasNotify') and $request->input('hasNotify')) and
                ($request->has('emails') and count($request->input('emails')) > 0 and empty($client_id))
            ) {
                $this->buildDataNotification(
                    $activity->id,
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
                $translation_es = $translation['type_activity_name'];
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
            $translation_dirty[$key]['name'] = ($translation['type_activity_name'] == null) ? $translation_es : $translation['type_activity_name'];
        }
        $data = [
            'id' => $id,
            'action' => $action,
            'translations' => $translation_dirty,
            'message' => $message,
        ];
        Mail::to($emails)->send(new NotificationOperability($data));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $activity = ServiceTypeActivity::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'servicetypeactivity');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $activity]);
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
        $arrayErrors = [];
        $countErrors = 0;
        $client_id = $this->getClientId($request);
        $validate = [];
        if (empty($client_id)) {
            $validate = [
                'translations.*.type_activity_name' => 'unique:translations,value,' . $id . ',object_id,type,servicetypeactivity,id,translations.*.id'
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
            $this->saveTranslation($request->input("translations"), 'servicetypeactivity', $id);

            if (empty($client_id)) {
                $emails_ = ['mlu@limatours.com.pe', 'vcq@limatours.com.pe', auth_user()->email];
                $user_creator = ServiceTypeActivity::find($id);
                if($user_creator->user_id != null){
                    $email_creator = User::find($user_creator->user_id)->email;
                    array_push($emails_, $email_creator);
                }
                $this->buildDataNotification(
                    $id,
                    'update',
                    $request->input("translations"),
                    $emails_,
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
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $activity = ServiceTypeActivity::find($id);
        $activity_used = ServiceOperationActivity::where('service_type_activity_id', $id)->get();
        if ($activity_used->count() == 0) {
            $activity->delete();
            $this->deleteTranslation('servicetypeactivity', $id);
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
        $activities = ServiceTypeActivity::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'servicetypeactivity');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ]);

        if (!empty($client_id)) {
            $activities = $activities->whereHas('client_service_type_activity', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            });
        } else {
            $activities = $activities->whereDoesntHave('client_service_type_activity');
        }

        $activities = $activities->get();

        return Response::json(['success' => true, 'data' => $activities]);
    }

    public function selectBoxFilter(Request $request)
    {
        $lang = $request->has('lang') ? $request->input('lang') : 'es';
        $limit = $request->has('limit') ? $request->input('limit') : 50;
        $filter = $request->has('filter') ? $request->input('filter') : '';
        $language = Language::where('iso', $lang)->first(['id']);

        $client_service_type_activity = ClientServiceTypeActivity::get(['service_type_activity_id'])->pluck('service_type_activity_id');

        $activities = Translation::where('type', 'servicetypeactivity')
            ->where('slug', 'type_activity_name')
            ->where('language_id', $language->id);

        if (!empty($filter)) {
            $activities = $activities->where('value', 'like', '%' . $filter . '%');
        }

        $activities = $activities->whereNotIn('object_id',$client_service_type_activity)
            ->limit($limit)
            ->get(['object_id as id', 'value as name']);

        return Response::json(['success' => true, 'data' => $activities]);
    }
}
