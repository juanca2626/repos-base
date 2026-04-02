<?php

namespace App\Exports;

use App\Language;
use App\TranslationFrontend;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Contracts\View\View;

class TranslationModuleLangExport implements  FromView, WithEvents, WithTitle
{
    use Exportable;

    protected $module_id;
    protected $language_id;
    protected $lang;

    public function __construct($module_id = null, $language_id = null, $lang = null)
    {
        $this->module_id = $module_id;
        $this->language_id = $language_id;
        $this->lang = $lang;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $latestBLColumn = $event->sheet->getHighestDataColumn();
                $latestBLRow = $event->sheet->getHighestDataRow();

                $event->sheet->getColumnDimension('A')->setWidth(20);
                foreach (range('B', $latestBLColumn) as $columnID) {
                    $event->sheet->getColumnDimension($columnID)
                        ->setAutoSize(true);
                }

            },
        ];
    }

    public function view(): View
    {
        $data = [
            'translations' => [],
            'languages'=>[]

        ];
        $languages =  Language::select('iso')->where('state',1)->get();
        $enabled_languages = Language::where('state',1)->pluck('id');
        $translations = TranslationFrontend::where('module_id',$this->module_id)->whereIn('language_id',$enabled_languages)->get()->groupBy('slug');

        foreach ($translations as $slug =>$translations)
        {
            array_push($data["translations"],[
                'slug'=>$slug,
                'translations'=>$translations
            ]);
        }
//        $data["translations"] = $translations;
        $data["languages"] = $languages;
//        var_export( json_encode( $data ) ); die;
//        dd($data["categories"][0]["services"]);
        return
            view('exports.translations', [
                'data' => $data,
            ]);
    }

    public function title(): string
    {
        return 'Traducciones';
    }
}
