<?php

namespace App\Http\Series\Controller;

use App\SerieDepartureProgram;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SerieDepartureProgramController extends Controller
{
    public function index(Request $request)
    {
        try {

            $departureId = $request->get('serie_departure_id');
            $items = \App\SerieDepartureProgram::with('program')
                ->where('serie_departure_id', $departureId)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->program->name,
                        'date' => $item->date->format('Y-m-d'),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'serie_program_id' => 'required|integer|exists:serie_programs,id',
            'serie_departure_id' => 'required|integer|exists:serie_departures,id',
            'date' => 'nullable|date',
        ]);

        $item = SerieDepartureProgram::create($data);

        return response()->json($item->load(['program', 'departure']), 201);
    }

    public function show(SerieDepartureProgram $serieDepartureProgram)
    {
        $serieDepartureProgram->load(['program', 'departure', 'trackingControls']);

        return response()->json($serieDepartureProgram);
    }

    public function update(Request $request, SerieDepartureProgram $serieDepartureProgram)
    {
        $data = $request->validate([
            'serie_program_id' => 'sometimes|required|integer|exists:serie_programs,id',
            'serie_departure_id' => 'sometimes|required|integer|exists:serie_departures,id',
            'date' => 'sometimes|nullable|date',
        ]);

        $serieDepartureProgram->update($data);

        return response()->json($serieDepartureProgram->fresh()->load(['program', 'departure']));
    }

    public function destroy(SerieDepartureProgram $serieDepartureProgram)
    {
        $serieDepartureProgram->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
