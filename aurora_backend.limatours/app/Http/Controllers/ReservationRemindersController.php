<?php

namespace App\Http\Controllers;

use App\Reservation;
use App\ReservationReminder;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ReservationRemindersController extends Controller
{
    public function store($reservation_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'days_before' => 'required',
            'date' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $days_before = $request->input('days_before');
            $email = $request->input('email');
            $email_alt = $request->input('email_alt');
            $date = $request->input('date');

            try {

                $reservation = Reservation::find($reservation_id);
                if(!$reservation){
                    return Response::json(['success' => false, 'error' => "Reserva no válida"]);
                }

                $reservation_reminder = ReservationReminder::where('reservation_id', $reservation_id)->first();
                if(!$reservation_reminder){
                    $reservation_reminder = new ReservationReminder();
                }

                $reservation_reminder->reservation_id = $reservation_id;
                $reservation_reminder->days_before = $days_before;
                $reservation_reminder->email = $email;
                $reservation_reminder->email_alt = $email_alt;
                $reservation_reminder->date = Carbon::parse($date);
                $reservation_reminder->date_reminder = Carbon::parse($date)->subDays($days_before);
                $reservation_reminder->save();

                return Response::json(['success' => true]);
            } catch (\Exception $e) {
                return Response::json(['success' => false, 'error' => $e->getMessage()]);
            }

        }
    }

    public function destroy($reservation_id){

        $reservation = Reservation::find($reservation_id);
        if(!$reservation){
            return Response::json(['success' => false, 'error' => "Reserva no válida"]);
        }

        ReservationReminder::where('reservation_id', $reservation_id)->delete();

        $data = [
            'success' => true
        ];

        return Response::json($data);
    }

}
