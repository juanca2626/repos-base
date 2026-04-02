<?php

namespace App\Http\Controllers;

use App\Models\ClientSeller;
use App\Http\Traits\Quotes;
use App\Models\Quote;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Aurora\AuroraExternalApiService;

class ExportController extends Controller
{
    use Quotes;
    protected AuroraExternalApiService $auroraService;

    public function __construct(Request $request, AuroraExternalApiService $auroraService )
    {
        $this->auroraService = $auroraService;
        $this->auroraService->setAuthorization($request->header('authorization'));
    }

    public function rangesExport($quote_id, Request $request)
    {
        $user_type_id = $request->get('user_type_id');
        $user_id = $request->get('user_id');
        $client_id = null;
        if ($user_type_id == 4) {
            $client_id = ClientSeller::select('client_id')->where('user_id', $user_id)->first();
            $client_id = $client_id["client_id"];
        }

        if ($user_type_id == 3) {
            $client_id = $request->input('client_id');
        }

        $this->updateAmountAllServices($quote_id, $client_id);
        $lang = $request->get('lang');
        $quote_name = 'THE QUOTE HAS NO NAME';
        $quote = Quote::where('id', $quote_id)->first();
        if ($quote) {
            $quote_name = $quote->name;
        }
        $quote_name = str_replace("/", "-", $quote_name);

        return Excel::download(new \App\Exports\CategoryExport($quote_id, $client_id, $lang), $quote_name . '.xlsx');
    }

    public function passengersExport($quote_id, Request $request)
    {
        // try {
            $user_type_id = $request->get('user_type_id');
            $user_id = $request->get('user_id');
            $lang = $request->get('lang');
            $client_id = null;

            if (empty($user_type_id)) {
                return response()->json("No se envió el tipo de usuario, por favor intente nuevamente..", 500);
            }

            if (empty($user_id)) {
                return response()->json("No se envió el id de usuario, por favor intente nuevamente..", 500);
            }

            if (empty($lang)) {
                return response()->json("No se envió el idioma, por favor intente nuevamente..", 500);
            }

            if ($user_type_id == 4) {
                $client_id = ClientSeller::select('client_id')->where('user_id', $user_id)->first();
                $client_id = $client_id["client_id"];
            }

            if ($user_type_id == 3) {
                $client_id = $request->get('client_id');
            }
            $quote_name = \App\Models\Quote::where('id', $quote_id)->first()->name;
            $quote_name = str_replace("/", "-", $quote_name);

            return Excel::download(
                new \App\Exports\CategoryPassengerExport($quote_id, $client_id, $lang, $user_type_id),
                $quote_name . '.xlsx'
            );
        // } catch (\Exception $ex) {
        //     $error = [
        //         'file'     => $ex->getFile(),
        //         'line'     => $ex->getLine(),
        //         'detail'   => $ex->getMessage(),
        //         'message'  => $ex->getMessage(),
        //         'type'     => 'error',
        //         'success'  => false,
        //         'response' => 'ERR',
        //     ];

        //     return response()->json($error, 500);
        // }
    }
}
