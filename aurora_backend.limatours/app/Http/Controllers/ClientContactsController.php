<?php

namespace App\Http\Controllers;

use App\ClientContact;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ClientContactsController extends Controller
{

    public function index(Request $request)
    {
        // Validación de parámetros
        $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'see_in_operations' => 'sometimes|boolean',
        ]);

        // Construcción de la consulta inicial
        $query = ClientContact::where('client_id', $request->client_id);

        // Aplicación condicional del filtro 'see_in_operations'
        if ($request->filled('see_in_operations')) {
            $query->where('see_in_operations', $request->see_in_operations);
        }

//        dd($query->toSql(), $query->getBindings());
        // Obtención de los resultados ordenados
        $contacts = $query->orderBy('order')->get();

        // Retorno de la respuesta JSON
        return Response::json(['success' => true, 'data' => $contacts]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'client_id' => 'required',
            'surname' => 'required',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false]);
        } else {

            $count_contacts = ClientContact::where('client_id', $request->input('client_id'))->count();

            $newClass = new ClientContact();
            $newClass->client_id = $request->input('client_id');
            $newClass->order = $count_contacts + 1;
            $newClass->type_code = $request->input('type_code');
            $newClass->name = $request->input('name');
            $newClass->surname = $request->input('surname');
            $newClass->email = $request->input('email');
            $newClass->phone = $request->input('phone');
            $newClass->see_in_operations = $request->input('see_in_operations');
            $newClass->birthday_date =
                ($request->input('birthday_date') !== null)
                    ? convertDate($request->input('birthday_date'), '/', '-', 1)
                    : '';
            $newClass->save();

            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $contact = ClientContact::find($id);

        return Response::json(['success' => true, 'data' => $contact]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false]);
        } else {
            $newClass = ClientContact::find($id);
            $newClass->type_code = $request->input('type_code');
            $newClass->name = $request->input('name');
            $newClass->surname = $request->input('surname');
            $newClass->email = $request->input('email');
            $newClass->phone = $request->input('phone');
            $newClass->see_in_operations = $request->input('see_in_operations');
            $newClass->birthday_date =
                ($request->input('birthday_date') !== null)
                    ? convertDate($request->input('birthday_date'), '/', '-', 1)
                    : '';
            $newClass->save();
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $chains = ClientContact::find($id);

        $chains->delete();

        return Response::json(['success' => true]);
    }

    public function updateSeeInOperations($client_id, Request $request)
    {
        $clientContact = ClientContact::find($client_id);
        if ($request->input("status")) {
            $clientContact->see_in_operations = false;
        } else {
            $clientContact->see_in_operations = true;
        }
        $clientContact->save();
        return Response::json(['success' => true]);
    }

}
