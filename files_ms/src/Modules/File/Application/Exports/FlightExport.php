<?php 

namespace Src\Modules\File\Application\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class FlightExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        // Usamos map para transformar los datos de los vuelos
        $flightsData = collect($this->data)->flatMap(function ($item) {
            // Mapeamos cada vuelo y transformamos sus datos
            return collect($item['flights'])->map(function ($flight) {
                //dd($flight);
              return [
                'fileItineraryId' => $flight['file_itinerary_id'] ?? null, 
                'airlineName' => $flight['airline_name'] ?? null, 
                'airlineCode' => $flight['airline_code'] ?? null, 
                'airlineNumber' => $flight['airline_number'] ?? null, 
                'pnr' => $flight['pnr'] ?? null, 
                'departureTime' => $flight['departure_time'] ?? null, 
                'arrivalTime' => $flight['arrival_time'] ?? null, 
                'nroPax' => $flight['nro_pax'] ?? null, 
            ];
            });
        });
       return collect($flightsData);
    }

    public function headings(): array
    {
        return [
            'file Itinerary Id', 'airline Name', 'airline Code','airline Number','pnr','departure Time','arrival Time','Nro Pax'
        ];
    }
}
