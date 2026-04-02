<?php

namespace App\Http\Series\Controller;

use App\SerieHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SerieHeaderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $items = SerieHeader::query()
                ->orderBy('id')
                ->get(['id', 'name','year']);

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'error' => $e->getMessage(),
            ]);
        }

    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:2100',
        ]);

        $item = SerieHeader::create($data);

        return response()->json($item, 201);
    }

    public function show(SerieHeader $serieHeader)
    {
        $serieHeader->load('departures');

        return response()->json($serieHeader);
    }

    public function update(Request $request, SerieHeader $serieHeader)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'year' => 'sometimes|required|integer|min:1900|max:2100',
        ]);

        $serieHeader->update($data);

        return response()->json($serieHeader->fresh('departures'));
    }

    public function destroy(SerieHeader $serieHeader)
    {
        $serieHeader->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
