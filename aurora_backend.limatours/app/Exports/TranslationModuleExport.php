<?php

namespace App\Exports;

use App\Language;
use App\TranslationFrontend;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TranslationModuleExport implements WithMultipleSheets
{
    use Exportable;

    protected $module_id;


    public function __construct($module_id = null)
    {
        $this->module_id = $module_id;
    }
    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];


        $sheets[] = new TranslationModuleLangExport($this->module_id);


        return $sheets;
    }
}
