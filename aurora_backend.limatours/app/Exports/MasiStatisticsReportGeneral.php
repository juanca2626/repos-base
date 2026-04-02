<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MasiStatisticsReportGeneral implements WithMultipleSheets
{
    use Exportable;
    protected $files;
    protected $file_without_data;

    public function __construct($files, $file_without_data = [])
    {
        $this->files = $files;
        $this->file_without_data = $file_without_data;
    }

    /**
     * @inheritDoc
     */
    public function sheets(): array
    {
        // TODO: Implement sheets() method.
        $sheets = [];
        $sheets[] = new MasiStatisticsExport('FILES CON DATOS' ,$this->files);
        $sheets[] = new MasiStatisticsExport('FILES SIN DATOS' ,$this->file_without_data);

        return $sheets;
    }
}
