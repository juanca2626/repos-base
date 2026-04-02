<?php

namespace App\Http\Controllers;

use App\FixedOutput;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class FixedOutputsController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:fixedoutputs.read')->only('index');
        $this->middleware('permission:fixedoutputs.create')->only('store');
        $this->middleware('permission:fixedoutputs.update')->only('update');
        $this->middleware('permission:fixedoutputs.delete')->only('destroy');
    }

    /**
     * @param $package_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($package_id, Request $request)
    {

        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $sorting = $request->input('orderBy');
        $sortOrder = $request->input('ascending');

        $fixed_outputs = FixedOutput::where('package_id', $package_id);

        $count = $fixed_outputs->count();

        if ($querySearch) {
            $fixed_outputs->where(function ($query) use ($querySearch) {
                $query->orWhere('date', 'like', '%' . $querySearch . '%');
                $query->orWhere('room', 'like', '%' . $querySearch . '%');
            });
        }

        if ($sorting) {
            $asc = $sortOrder == 1 ? 'asc' : 'desc';
            $fixed_outputs->orderBy($sorting, $asc);
        } else {
            $fixed_outputs->orderBy('date', 'asc');
        }

        if ($paging == 1) {
            $fixed_outputs = $fixed_outputs->take($limit)->get();
        } else {
            $fixed_outputs = $fixed_outputs->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $data = [
            'data' => $fixed_outputs,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $fixed_output = new FixedOutput();
        $fixed_output->date =
            date("Y-m-d", strtotime(str_replace('/', '-', $request->input('date'))));
        $fixed_output->state = 1;
        $fixed_output->room = $request->input('room');
        $fixed_output->package_id = $request->input('package_id');
        $fixed_output->save();

        $response = ['success' => true, 'object_id' => $fixed_output->id];

        return Response::json($response);
    }

    /**
     * @param $package_id
     * @param $fixed_output_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($package_id, $fixed_output_id)
    {
        $fixed_output = FixedOutput::find($fixed_output_id);
        $fixed_output->delete();

        return Response::json(['success' => true]);
    }

    /**
     * @param $package_id
     * @param $fixed_output_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateState($package_id, $fixed_output_id, Request $request)
    {
        $fixed_output = FixedOutput::find($fixed_output_id);
        if ($request->input("state")) {
            $fixed_output->state = 0;
        } else {
            $fixed_output->state = 1;
        }
        $fixed_output->save();
        return Response::json(['success' => true]);
    }

}
