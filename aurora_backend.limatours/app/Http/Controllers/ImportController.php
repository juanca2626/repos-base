<?php

namespace App\Http\Controllers;

use App\Imports\ServiceNotesImport;
use App\Imports\ServiceTranslationsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Psy\Exception\FatalErrorException;

class ImportController extends Controller
{
    public function serviceTranslationsImport(Request $request)
    {
        try {
            Excel::import(new ServiceTranslationsImport(), request()->file('file'));
            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
            ]);
        }catch (\Error $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
            ]);
        }

    }
    
    public function services_notes(Request $request)
    {
        try
        {
            Excel::import(new ServiceNotesImport(), request()->file('file'));
            
            return response()->json([
                'success' => true
            ]);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }
}
