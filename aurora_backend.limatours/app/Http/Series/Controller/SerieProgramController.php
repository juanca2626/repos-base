<?php

namespace App\Http\Series\Controller;

use App\SerieProgram;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SerieProgramController extends Controller
{
    public function index(Request $request)
    {
        try {
            $items = SerieProgram::query()
                ->orderBy('id')
                ->get(['id', 'name']);
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
        ]);

        $item = SerieProgram::create($data);

        return response()->json($item, 201);
    }

    public function show(SerieProgram $serieProgram)
    {
        $serieProgram->load(['departurePrograms', 'departures']);

        return response()->json($serieProgram);
    }

    public function update(Request $request, SerieProgram $serieProgram)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);

        $serieProgram->update($data);

        return response()->json($serieProgram->fresh()->load('departures'));
    }

    public function destroy(SerieProgram $serieProgram)
    {
        $serieProgram->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
