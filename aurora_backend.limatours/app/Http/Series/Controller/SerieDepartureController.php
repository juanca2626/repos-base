<?php

namespace App\Http\Series\Controller;

use App\SerieDeparture;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SerieDepartureController extends Controller
{
    public function index(Request $request)
    {
        try {

            $items = SerieDeparture::query()
                ->orderBy('id')
                ->get(['id', 'serie_header_id', 'name', 'has_holiday', 'name_holiday', 'has_extra_departure']);

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
            'serie_header_id' => 'required|integer|exists:serie_headers,id',
            'name' => 'required|string|max:255',
            'has_holiday' => 'nullable|boolean',
            'name_holiday' => 'nullable|string|max:255',
            'link_guidelines' => 'nullable|string|max:255',
        ]);

        // Normalización: si has_holiday = false, name_holiday debería ser null
        if (array_key_exists('has_holiday', $data) && !$data['has_holiday']) {
            $data['name_holiday'] = null;
        }

        $item = SerieDeparture::create($data);

        return response()->json($item->load('header'), 201);
    }

    public function show(SerieDeparture $serieDeparture)
    {
        $serieDeparture->load(['header', 'departurePrograms', 'programs']);

        return response()->json($serieDeparture);
    }

    public function update(Request $request, SerieDeparture $serieDeparture)
    {
        $data = $request->validate([
            'serie_header_id' => 'sometimes|required|integer|exists:serie_headers,id',
            'name' => 'sometimes|required|string|max:255',
            'has_holiday' => 'sometimes|nullable|boolean',
            'name_holiday' => 'sometimes|nullable|string|max:255',
            'link_guidelines' => 'sometimes|nullable|string|max:255',
        ]);

        if (array_key_exists('has_holiday', $data) && !$data['has_holiday']) {
            $data['name_holiday'] = null;
        }

        $serieDeparture->update($data);

        return response()->json($serieDeparture->fresh()->load(['header', 'programs']));
    }

    public function destroy(SerieDeparture $serieDeparture)
    {
        $serieDeparture->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
