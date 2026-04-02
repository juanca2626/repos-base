<?php

namespace Src\Modules\File\Presentation\Http\Controllers\Download;

use File;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Traits\ApiResponse;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Src\Modules\File\Application\UseCases\Queries\FindFileStatementDetailsQuery;

class DownloadStatementsPdfController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request, $file_id)
    {
        try {
            $lang = App::currentLocale();
            Carbon::setLocale($lang);

            $dataLang = File::get(resource_path() . "/lang/" . $lang . "/statement.json");
            $trad = json_decode($dataLang);

            $file = (new FindFileStatementDetailsQuery($file_id))->handle();

            if (!$file['statement']) {
                throw new \DomainException('No statement was generated for this file');
            }

            $client_aurora = $file['client_aurora'];
            $statemet = $file['statement'];
            $debit_notes = $file['debit_notes'];
            $credit_notes = $file['credit_notes'];

            $limatours = config('global.limatours');

        } catch (\Exception $domainException) {

            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

        $statemet['date'] = Carbon::parse($statemet['date'])->isoFormat('MMMM DD Y');
        File::cleanDirectory(public_path('temp-zip'));
        if ((bool)$request->statement) {
            Pdf::loadView('pdfs.statement', compact('statemet', 'client_aurora', 'limatours', 'file', 'trad'))->setPaper('a4', 'portrait')->setWarnings(false)->save("temp-zip/statement.pdf");
        }
        if ((bool)$request->credit_note) {
            Pdf::loadView('pdfs.credit-note', compact('statemet', 'client_aurora', 'limatours', 'file', 'trad', 'credit_notes'))->setPaper('a4', 'portrait')->setWarnings(false)->save("temp-zip/credit-note.pdf");
        }
        if ((bool)$request->debit_note) {
            Pdf::loadView('pdfs.debit-note', compact('statemet', 'client_aurora', 'limatours', 'file', 'trad', 'debit_notes'))->setPaper('a4', 'portrait')->setWarnings(false)->save("temp-zip/debit-note.pdf");
        }
        
        return $this->downloadInZip();
    }


    private function downloadInZip()
    {
        $zip_file = public_path('statements.zip');
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $path = public_path('temp-zip'); // path to your pdf files
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($path) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
        $zip_new_name = "statements.zip";
        return response()->download($zip_file, $zip_new_name);
    }
}
