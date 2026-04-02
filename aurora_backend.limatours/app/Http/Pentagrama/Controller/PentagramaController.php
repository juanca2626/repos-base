<?php

namespace App\Http\Pentagrama\Controller;

use App\Http\Controllers\Controller;
use App\Http\Pentagrama\Services\PentagramaService;
use App\Http\Pentagrama\Traits\PentagramaTrait;
use App\Http\Traits\Aurora3;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PentagramaController extends Controller
{
    use Aurora3;
    use PentagramaTrait;

    private $service;

    public function __construct(PentagramaService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = [
            'page' => (int)$request->input('page', 1),
            'limit' => (int)$request->input('limit', 15),
            'querySearch' => $request->input('queryCustom'),
            'filterBy' => $request->input('filterBy'),
            'filter' => $request->input('filter'),
            'order' => $request->input('order'),
            'reserve_passed' => $request->input('reserve_passed'),
        ];
        $result = $this->service->list($filters);
        return response()->json($result);
    }


    public function show(int $id, Request $request)
    {
        $filters = $request->all();
        $result = $this->service->detail($id, $filters);
        return Response::json($result);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $result = $this->service->create($data);
        return Response::json($result);
    }

    public function update(int $id, Request $request)
    {
        $data = $request->all();
        $result = $this->service->update($id, $data);
        return Response::json($result);
    }

}
