<?php

namespace App\Http\Series\Controller;

use App\SerieTrackingControl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;

class SerieTrackingControlController extends Controller
{
    public function index(Request $request)
    {
        try {

            $query = SerieTrackingControl::query()
                // columnas del tracking control (incluye FKs)
                ->select([
                    'id',
                    'serie_departure_program_id',
                    'file',
                    'passenger_group_name',
                    'qty_passengers',
                    'client_id',
                    'user_id',
                    'ticket_mapi',
                    'observation',
                    'created_at',
                ])

                // WITH separados + select de columnas por relación
                ->with([
                    'departureProgram' => function ($q) {
                        $q->select(['id', 'serie_program_id', 'serie_departure_id', 'date']);
                    },
                    'departureProgram.program' => function ($q) {
                        $q->select(['id', 'name']);
                    },
                    'departureProgram.departure' => function ($q) {
                        $q->select(['id', 'name', 'has_holiday', 'name_holiday']);
                    },
                    'client' => function ($q) {
                        $q->select(['id', 'name']); // ajusta columnas reales
                    },
                    'user' => function ($q) {
                        $q->select(['id', 'name']); // o 'username' según tu tabla
                    },
                ]);

            if ($request->filled('serie_departure_program_id')) {
                $query->where('serie_departure_program_id', $request->serie_departure_program_id);
            }

            if ($request->filled('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            $items = $query->get();

            // Agrupar por departure (departureProgram.departure)
            $grouped = $items->groupBy(function ($item) {
                return optional(optional($item->departureProgram)->departure)->id;
            })->sortKeys()
                ->map(function ($group) {
                    $first = $group->first();
                    $departure = optional(optional($first->departureProgram)->departure);

                    return [
                        'departure' => [
                            'id' => $departure->id,
                            'name' => $departure->name,
                            'has_holiday' => $departure->has_holiday,
                            'name_holiday' => $departure->name_holiday,
                        ],
                        'items' => $group->values(),
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $grouped,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getTrackingByClient(Request $request)
    {
        try {

            $query = SerieTrackingControl::query()
                // columnas del tracking control (incluye FKs)
                ->select([
                    'id',
                    'serie_departure_program_id',
                    'file',
                    'passenger_group_name',
                    'qty_passengers',
                    'client_id',
                    'user_id',
                    'ticket_mapi',
                    'observation',
                    'created_at',
                ])

                // WITH separados + select de columnas por relación
                ->with([
                    'departureProgram' => function ($q) {
                        $q->select(['id', 'serie_program_id', 'serie_departure_id', 'date']);
                    },
                    'departureProgram.program' => function ($q) {
                        $q->select(['id', 'name']);
                    },
                    'departureProgram.departure' => function ($q) {
                        $q->select(['id', 'name', 'has_holiday', 'name_holiday']);
                    },
                    'client' => function ($q) {
                        $q->select(['id', 'name']); // ajusta columnas reales
                    },
                    'user' => function ($q) {
                        $q->select(['id', 'name']); // o 'username' según tu tabla
                    },
                ]);


            $items = $query->get();

            // Agrupar por departure (departureProgram.departure)
            $grouped = $items->groupBy(function ($item) {
                return optional(optional($item->departureProgram)->departure)->id; // departure_id (puede ser null)
            })
                ->sortKeys()
                ->map(function ($groupByDeparture) {

                    $first = $groupByDeparture->first();
                    $departure = optional(optional($first->departureProgram)->departure);

                    // Segundo nivel: agrupar por serie_departure_program_id
                    $programGroups = $groupByDeparture
                        ->groupBy('serie_departure_program_id')
                        ->map(function ($groupByProgram, $serieDepartureProgramId) {

                            $firstItem = $groupByProgram->first();

                            $serieProgramName = optional(
                                optional(optional($firstItem)->departureProgram)->program
                            )->name;

                            $totalQty = $groupByProgram->sum(function ($row) {
                                return (int)($row->qty_passengers ?? 0);
                            });

                            return [
                                'serie_departure_program_id' => (int)$serieDepartureProgramId,
                                'serie_program_name' => $serieProgramName, // <- aquí ya viene
                                'date' => Carbon::parse($firstItem->departureProgram->date)->format('d/m/Y'), // <- aquí ya viene
                                'total_qty_passengers' => $totalQty,
                            ];
                        })
                        ->values();

                    // Total opcional por departure (si también lo quieres)
                    $totalDepartureQty = $groupByDeparture->sum(function ($row) {
                        return (int)($row->qty_passengers ?? 0);
                    });

                    return [
                        'departure' => [
                            'id' => $departure->id,
                            'name' => $departure->name,
                            'has_holiday' => $departure->has_holiday,
                            'name_holiday' => $departure->name_holiday,
                        ],
                        'total_qty_passengers' => $totalDepartureQty, // opcional
                        'programs' => $programGroups,                 // <- aquí va tu segundo nivel
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $grouped,
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
        try {
            $data = $request->validate([
                'serie_departure_program_id' => 'required|integer|exists:serie_departure_programs,id',
                'file' => 'nullable|integer',
                'passenger_group_name' => 'nullable|string|max:255',
                'qty_passengers' => 'nullable|integer|min:0',
                'client_id' => 'required|integer|exists:clients,id',
                'user_id' => 'required|integer|exists:users,id',
                'ticket_mapi' => 'nullable|string|max:255',
                'observation' => 'nullable|string|max:255',
            ]);

            SerieTrackingControl::create($data);
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, SerieTrackingControl $tracking_control)
    {

        try {
            $data = $request->validate([
                'serie_departure_program_id' => 'sometimes|required|integer|exists:serie_departure_programs,id',
                'file' => 'sometimes|nullable|integer',
                'passenger_group_name' => 'sometimes|nullable|string|max:255',
                'qty_passengers' => 'sometimes|nullable|integer|min:0',
                'client_id' => 'sometimes|required|integer|exists:clients,id',
                'user_id' => 'sometimes|required|integer|exists:users,id',
                'ticket_mapi' => 'sometimes|nullable|string|max:255',
                'observation' => 'sometimes|nullable|string|max:255',
            ]);
            $tracking_control->update($data);
            return response()->json([
                'success' => true,
                'data' => $tracking_control->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(SerieTrackingControl $trackingControl)
    {
        try {
            $trackingControl->delete();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
