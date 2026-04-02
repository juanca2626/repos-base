<?php

namespace App\Http\Controllers;

use App\Vista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class VistaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getInfoWeb($client_id)
    {
        try {
            $vista = Vista::where('client_id', $client_id)->where('status', 1)->get();
            $count = $vista->count();
            $data = [
                'data' => $vista,
                'count' => $count,
                'success' => true
            ];
            return Response::json($data);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Vista $vista
     * @return \Illuminate\Http\Response
     */
    public function show($vista_id, Request $request)
    {
        try {
            $vista = Vista::find($vista_id);
            $data = [
                'data' => $vista,
                'success' => true
            ];
            return Response::json($data);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Vista $vista
     * @return \Illuminate\Http\Response
     */
    public function edit($vista_id, Request $request)
    {
        try {
            $vista = Vista::find($vista_id);
            $vista->name = $request->input('name');
            $vista->email = $request->input('email');
            $vista->phone = $request->input('phone');
            $vista->direction = $request->input('direction');
            $vista->facebook = $request->input('facebook');
            $vista->instagram = $request->input('instagram');
            $vista->twitter = $request->input('twitter');
            $vista->whatsapp = $request->input('whatsapp');
            $vista->save();
            $data = [
                'success' => true
            ];
            return Response::json($data);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Vista $vista
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vista $vista)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Vista $vista
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vista $vista)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Vista $vista
     * @return \Illuminate\Http\Response
     */
    public function showInfo($client_id, Request $request)
    {
        try {
            $vista = Vista::where('client_id', $client_id)->first([
                'name',
                'email',
                'phone',
                'logo_url',
                'direction',
                'type_banner_main',
                'facebook',
                'instagram',
                'twitter',
                'whatsapp',
                'instagram'
            ]);
            $data = [
                'data' => $vista,
                'success' => true
            ];
            return Response::json($data);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }
}
