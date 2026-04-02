<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class LitoFileStorageExport implements FromView
{
    use Exportable;

    protected $files;

    public function __construct($files)
    {
        $this->files = $files;
    }

    public function view(): View
    {
        return
            view('exports.lito_storage_files', [
                'files' => $this->files,
            ]);
    }
}
