<?php

namespace App\Http\Services\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait ClientHotelUtilTrait
{
    // Función auxiliar para generar el rango de fechas
    public function generateDateRange($startDate, $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $dates = [];
        while ($start <= $end) {
            $dates[] = $start->format('Y-m-d');
            $start->addDay();
        }

        return $dates;
    }

    // Función para convertir objetos y arrays a arrays
    public function deepToArray($value)
    {
        if ($value instanceof Model) {
            return $value->toArray(); // usa el método del modelo
        }

        if ($value instanceof Collection) {
            $items = array_map(function ($item) {
                return $this->deepToArray($item);
            }, (array)$value);
            return $items;
        }

        if (is_object($value)) {
            $value = (array)$value; // convierte stdClass u objetos simples
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->deepToArray($item);
            }
        }

        return $value;
    }
}
