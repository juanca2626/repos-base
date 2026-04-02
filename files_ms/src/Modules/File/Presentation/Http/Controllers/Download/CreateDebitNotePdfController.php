<?php

namespace Src\Modules\File\Presentation\Http\Controllers\Download;

use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Src\Modules\File\Application\UseCases\Queries\FindFileStatementDetailsQuery;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use File;

class CreateDebitNotePdfController extends Controller
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

            $limatours = config('global.limatours');

        } catch (\Exception $domainException) {

            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }

        $statemet['date'] = Carbon::parse($statemet['date'])->isoFormat('MMMM DD Y');


        return Pdf::loadView('pdfs.debit-note', compact('statemet', 'client_aurora', 'limatours', 'file', 'trad', 'debit_notes'))->setPaper('a4', 'portrait')->setWarnings(false)->download('statement.pdf');

    }

}
