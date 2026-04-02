<?php

namespace App\Http\Controllers;

use App\TicketCircuit;
use App\TicketRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AvailableRouteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getAvailableRoutesByFilters($circuit_id, $route_id, $month, $year)
    {
        $routes = TicketCircuit::where('ticket_circuits.id', $circuit_id)
            ->join('ticket_routes', 'ticket_routes.ticket_circuit_id', '=', 'ticket_circuits.id')
            ->join('ticket_available_routes', 'ticket_available_routes.ticket_route_id', '=', 'ticket_routes.id')
            ->join('ticket_available_route_times', 'ticket_available_route_times.ticket_available_route_id', '=', 'ticket_available_routes.id')
            ->where('ticket_routes.id', $route_id)
            ->whereMonth('ticket_available_routes.date', $month)
            ->whereYear('ticket_available_routes.date', $year)
            ->select(
                'ticket_circuits.id as ticket_circuit_id',
                'ticket_routes.id as ticket_route_id',
                'ticket_available_routes.id as ticket_available_route_id',
                'ticket_available_routes.date',
                'ticket_available_route_times.time',
                'ticket_available_route_times.ticket_quantity'
            )
            ->orderBy('ticket_available_routes.date')
            ->orderBy('ticket_available_route_times.time')
            ->get();

        if ($routes->isEmpty()) {
            return response()->json(['message' => 'No hay datos para los filtros especificados'], 404);
        }

        $result = [];
        foreach ($routes as $route) {
            $date = $route->date;
            if (!isset($result[$date])) {
                $result[$date] = [
                    'circuit_id' => $route->ticket_circuit_id,
                    'route_id' => $route->ticket_route_id,
                    'date' => $date,
                    'times' => []
                ];
            }

            $time_value = $route->time;
            // Convertir el tiempo
            $time_value = date('H:i', strtotime($time_value));

            $result[$date]['times'][] = [
                'time' => $time_value,
                'ticket_quantity' => $route->ticket_quantity
            ];
        }

        return response()->json(array_values($result));
    }

    public function getCircuits()
    {
        $circuits = Cache::remember(
            'ticket_circuits_all',
            now()->addMinutes(15),
            function () {
                return TicketCircuit::all(['id', 'name']);
            }
        );

        return response()->json($circuits);
    }


    public function getRoutesByCircuit($circuit_id)
    {
        $cacheKey = "ticket_routes_by_circuit_{$circuit_id}";

        $routes = Cache::remember(
            $cacheKey,
            now()->addMinutes(15),
            function () use ($circuit_id) {
                return TicketRoute::where('ticket_circuit_id', $circuit_id)
                    ->orderBy('name')
                    ->get(['id', 'name']);
            }
        );

        return response()->json($routes);
    }

}
