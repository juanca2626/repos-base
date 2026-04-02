<?php

namespace App\Imports;

use App\Imports\Services\ServiceLangImport;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ServiceTranslationsImport implements WithMultipleSheets
{

    use Importable;

    public function sheets(): array
    {
        return [
            'es' => new ServiceLangImport(1),
            'en' => new ServiceLangImport(2),
//            'pt' => new ServiceLangImport(3),
//            'it' => new ServiceLangImport(4),
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // E.g. you can log that a sheet was not found.
        info("Sheet {$sheetName} was skipped");
    }
}
