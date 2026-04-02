<?php 

namespace Src\Modules\File\Application\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class FilesExport implements FromView
{
    protected $files;

    public function __construct($files)
    {
        $this->files = $files;
    }

    public function view(): View
    {
        return view('exports.files', [
            'files' => $this->files
        ]);
    }
}