<?php

namespace App\Http\Controllers;

use App\Client;
use App\Reservation;
use App\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $tickets = Ticket::with('service')->with('hotel');
        $tickets = $tickets->take($limit)->orderBy('id', 'desc')->get([
            'id',
            'type',
            'object_id',
            'file_code',
            'date_service',
            'origin',
            'action',
            'status',
            'created_at',
            'updated_at',
        ]);

        $tickets = $tickets->transform(function ($item) {
            $reservation = Reservation::where('file_code', $item['file_code'])->get();
            $client = Client::find($reservation[0]['client_id']);
            $item['reservation'] = $reservation;
            $item['client'] = $client;
            return $item;
        });

        $data = [
            'data' => $tickets,
            'count' => $tickets->count(),
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created cancellation file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $arrayErrors = [];
            $countErrors = 0;
            $validator = Validator::make($request->all(), [
                'file_code' => 'required|unique:tickets,file_code|exists:reservations,file_code',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $error) {

                    array_push($arrayErrors, $error);
                }
                $countErrors++;
            }

            if ($countErrors > 0) {
                return Response::json(['success' => false, 'message' => $errors], 422);
            } else {
                $ticket = new Ticket();
                $ticket->type = 'file';
                $ticket->file_code = $request->post('file_code');
                $ticket->origin = 'API';
                $ticket->action = 'cancellation';
                $ticket->status = 0;
                $ticket->save();
            }
            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function show(Ticket $ticket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update_status($id, Request $request)
    {
        $service = Ticket::find($id, ['id', 'status']);
        if ($request->input("status")) {
            $service->status = false;
        } else {
            $service->status = true;
        }
        $service->save();
        return Response::json(['success' => true]);
    }


}
