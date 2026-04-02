<?php

namespace App\Http\Controllers;

use App\Release;
use Illuminate\Http\Request;

class ReleaseController extends Controller
{

    public function index(Request $request)
    {
        $hotel_id = $request->get('hotel_id');

        $releases = Release::where('hotel_id',$hotel_id)->with('room.translations')->get();

        return response()->json($releases,200);
    }

    public function store(Request $request)
    {
        $hotel_id = $request->post('hotel_id');
        $room_id = $request->post('room_id');
        $quantity = $request->post('quantity');

        $release = new Release();
        $release->hotel_id = $hotel_id;
        $release->room_id = $room_id;
        $release->quantity = $quantity;
        $release->save();

        return response()->json("Liberado Guardado",200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $release = Release::find($id);
        $release->delete();

        return response()->json("Liberado Eliminado",200);
    }
}
