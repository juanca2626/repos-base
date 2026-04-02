<?php

namespace App\Http\Controllers;

use App\Ota;
use Illuminate\Http\Request;

class OtasController extends Controller
{
    public function index()
    {
        $otas = Ota::select('id','name')->whereNotIn('name',['tourcms','expedia','despegar', 'pentagrama'])->get();

        return response()->json($otas);
    }

    public function store(Request $request)
    {
        try
        {
            $ota = new Ota();
            $ota->name = $request->post("name");
            $ota->save();

            return response()->json("OTA creada satisfactoriamente");
        }
        catch(\Exception $ex)
        {
            return response()->json("El nombre ingresado ya existe. Por favor, intente con uno diferente", 500);
        }
    }


    public function update(Request $request, $id)
    {
        try
        {
            $ota = Ota::find($id);
            $ota->name = $request->post("name");
            $ota->save();

            return response()->json("OTA actualizada satisfactoriamente");
        }
        catch(\Exception $ex)
        {
            return response()->json("Ocurrió un error al actualizar la OTA", 500);
        }
    }


    public function destroy($id)
    {
        try
        {
            $ota = Ota::find($id);
            $ota->delete();

            return response()->json("OTA eliminada satisfactoriamente");
        }
        catch(\Exception $ex)
        {
            return response()->json("Ocurrió un error al eliminar la OTA", 500);
        }
    }
}
