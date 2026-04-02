<?php

namespace App\Imports\Services;

use App\Language;
use App\ServiceTranslation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMappedCells;

class ServiceLangImport implements ToCollection, WithBatchInserts, WithChunkReading,WithHeadingRow
{
    use Importable;

    public $lang;

    public function __construct($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        if ($this->lang == 2)
        {
            DB::transaction(function () use ($rows) {
                foreach ($rows as $key => $value) {
                    DB::table('service_translations')->where('service_id', trim($value['id']))
                        ->where('language_id', $this->lang)->update([
                            'name' => str_replace('.','',trim($value['nombre_comercial'])),
                            'description' => str_replace('.','',trim($value['nombre_comercial'])),
                            'itinerary' => trim($value['itinerario']),
                            'summary' => (trim($value['summary']) == 0) ? '':trim($value['summary']),
                        ]);
                }
            });
        }else{
            DB::transaction(function () use ($rows) {
                foreach ($rows as $key => $value) {
                    DB::table('service_translations')->where('service_id', trim($value['id']))
                        ->where('language_id', $this->lang)->update([
                            'name' => str_replace('.','',trim($value['nombre_comercial'])),
                            'description' => str_replace('.','',trim($value['descripcion'])),
                            'itinerary' => trim($value['itinerario']),
                            'summary' => trim($value['summary']),
                        ]);
                }
            });
        }

    }

    public function headingRow(): int
    {
        return 1;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
