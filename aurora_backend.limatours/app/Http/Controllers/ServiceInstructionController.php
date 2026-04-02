<?php

namespace App\Http\Controllers;

use App\Language;
use App\ServiceInstruction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ServiceInstructionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $service_id)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $lang = $request->input('lang');
        $language = Language::where('iso',$lang)->first();
        if (!empty($service_id)) {
            $service_instructions = ServiceInstruction::with([
                'instructions.translations' => function ($query) use ($language) {
                    $query->where('type', 'instruction');
                    $query->where('language_id', $language->id);
                }
            ])->where('service_id', $service_id);

            $count = $service_instructions->count();

            if ($paging === 1) {
                $service_instructions = $service_instructions->take($limit)->orderBy('id')->get();
            } else {
                $service_instructions = $service_instructions->skip($limit * ($paging - 1))->take($limit)->orderBy('id')->get();
            }
        } else {
            $service_instructions = [];
            $count = 0;
        }

        $data = [
            'data' => $service_instructions,
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
        $validator = Validator::make($request->all(), [
            'instruction_id' => 'required',
            'service_id' => 'required',
        ]);

        $validateInclusion = ServiceInstruction::where('service_id', $request->input('service_id'))
            ->where('instruction_id', $request->input('instruction_id'))
            ->count();
        if ($validator->fails() or $validateInclusion > 0) {
            return Response::json(['success' => false, 'error' => 'validation_duplicate']);
        } else {
            $serviceInclusion = new ServiceInstruction();
            $serviceInclusion->service_id = $request->input('service_id');
            $serviceInclusion->instruction_id = $request->input('instruction_id');
            $serviceInclusion->save();
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ServiceInstruction  $serviceInstruction
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $service = ServiceInstruction::find($id);
            $service->delete();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e]);
        }
    }
}
