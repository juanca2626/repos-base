<?php

namespace App\Http\Controllers;

use App\Http\Requests\VirtualClassRequest;
use App\VirtualClassName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class VirtualClassNameController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:virtualclass.read')->only('index');
        $this->middleware('permission:virtualclass.create')->only('store');
        $this->middleware('permission:virtualclass.update')->only('update');
        $this->middleware('permission:virtualclass.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->get('lang');

        $virtualclass = VirtualClassName::with('clients')->with([
            'type_class' => function ($query) use ($lang) {
                $query->with([
                    'translations' => function ($query) use ($lang) {
                        $query->where('type', 'typeclass');
                        $query->where('slug', 'typeclass_name');
                        $query->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    },
                ]);
            },
        ])->get();

        return response()->json(['success' => true, 'data' => $virtualclass]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param VirtualClassRequest $request
     * @return JsonResponse
     */
    public function store(VirtualClassRequest $request)
    {
        $name = $request->post('name');
        $type_class_id = $request->post('type_class_id');
        $clients = $request->post('clients');

        DB::transaction(function () use ($name, $type_class_id, $clients) {
            $virtual_class_id = DB::table('virtual_class_names')->insertGetId([
                'name' => $name,
                'type_class_id' => $type_class_id,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
            foreach ($clients as $client_id) {
                DB::table('virtual_class_name_clients')->insert([
                    'virtual_class_name_id' => $virtual_class_id,
                    'client_id' => $client_id,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
            }
        });

        return response()->json(["message" => "Clase Virtual creada exitosamente"], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $virtualclass = TypeClass::where('id', "=", $id)->first();

        return Response::json(['success' => true, 'data' => $virtualclass]);
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
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'translations.*.typeclass_name' => 'unique:translations,value,'.$id.',object_id,type,typeclass',
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
            $this->saveTranslation($request->input('translations'), 'typeclass', $id);

            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $virtualclass = TypeClass::find($id);

        $virtualclass->delete();

        $this->deleteTranslation('typeclass', $id);

        return Response::json(['success' => true]);
    }
}
