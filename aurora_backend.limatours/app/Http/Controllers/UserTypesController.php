<?php

namespace App\Http\Controllers;

use App\UserTypes;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function selectBox()
    {
        $userTypes = UserTypes::select('id', 'description')->get();

        $response = [];

        foreach ($userTypes as $userType) {
            array_push($response, ['value' => $userType->id, 'text' => $userType->description]);
        }

        return \Illuminate\Support\Facades\Response::json(['success' => true, 'data' => $response]);
    }

    public function get_companions()
    {
        $userTypes = UserTypes::select('id', 'description')->whereIn('code', ['S', 'T'])->get();

        return \Illuminate\Support\Facades\Response::json(['success' => true, 'data' => $userTypes]);
    }

}
