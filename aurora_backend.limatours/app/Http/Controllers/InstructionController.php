<?php

namespace App\Http\Controllers;

use App\ClientInstruction;
use App\Instruction;
use App\Language;
use App\ServiceInstruction;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class InstructionController extends Controller
{
    use Translations;


    public function __construct()
    {
        $this->middleware('permission:instructions.read')->only('index');
        $this->middleware('permission:instructions.create')->only('store');
        $this->middleware('permission:instructions.update')->only('update');
        $this->middleware('permission:instructions.delete')->only('delete');
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
        $data_query = Instruction::with([
            'translations' => function ($query) use ($language) {
                $query->where('type', 'instruction');
                $query->where('language_id', $language->id);
            }
        ])->orderBy('id', 'desc');

        if (!empty($client_id)) {
            $data_query = $data_query->whereHas('client_instructions', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            });
        } else {
            $data_query = $data_query->whereDoesntHave('client_instructions');
        }

        $count = $data_query->count();
        if ($querySearch) {
            $data_query->where(function ($query) use ($querySearch, $language) {
                $query->whereHas('translations', function ($query) use ($querySearch, $language) {
                    $query->where('type', 'instruction');
                    $query->where('language_id', $language->id);
                    $query->where('value', 'like', '%'.$querySearch.'%');
                });
            });
        }

        if ($paging === 1) {
            $data_query = $data_query->take($limit)->get();
        } else {
            $data_query = $data_query->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $data_query,
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
                'translations.*.instruction_name' => 'unique:translations,value,NULL,id,type,instruction'
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
            $important_info = new Instruction();
            $important_info->save();

            if (!empty($client_id)) {
                $newClientService = new ClientInstruction();
                $newClientService->client_id = $client_id;
                $newClientService->instruction_id = $important_info->id;
                $newClientService->save();
            }
            $this->saveTranslation($request->input("translations"), 'instruction',
                $important_info->id);
            return Response::json(['success' => true]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Instruction  $instruction
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $instruction = Instruction::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'instruction');
                $query->where('object_id', $id);
            }
        ])->where('id', $id)->get();
        return Response::json(['success' => true, 'data' => $instruction]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Instruction  $instruction
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
                'translations.*.instruction_name' => 'unique:translations,value,'.$id.',object_id,type,instruction'
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
            $this->saveTranslation($request->input("translations"), 'instruction', $id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Instruction  $instruction
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $info_important_used = ServiceInstruction::where('instruction_id', $id)->limit(1)->get();
        if ($info_important_used->count() == 0) {
            $info_important = Instruction::find($id);
            $info_important->delete();
            $this->deleteTranslation('instruction', $id);
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
        $info_important = Instruction::with([
            'translations' => function ($query) use ($language) {
                $query->where('type', 'instruction');
                $query->where('language_id', $language->id);
            }
        ]);

        if (!empty($client_id)) {
            $info_important = $info_important->whereHas('client_instructions', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            });
        } else {
            $info_important = $info_important->whereDoesntHave('client_instructions');
        }

        $info_important = $info_important->get();
        return Response::json(['success' => true, 'data' => $info_important]);
    }
}
