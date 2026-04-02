<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ServicesLatamExport implements WithMultipleSheets
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->data as $locale => $services) {
            // Creamos una instancia de la hoja para cada idioma
            $sheets[] = new ServicesLatamSheet($locale, $services);
        }
        return $sheets;
    }
}
