<?php

namespace App\Http\Controllers;

use App\File;
use App\Http\Stella\StellaService;
use App\LogModal;
use App\Reservation;
use App\Translation;
use App\City;
use App\ReservationsFlight;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    protected $stellaService;

    public function __construct(StellaService $stellaService)
    {
        $this->stellaService = $stellaService;
    }

    public function search_flights(Request $request, $nrofile)
    {
        try {
            $response = (array) $this->stellaService->search_flights($nrofile);
            return $response;
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function add_flight(Request $request, $nrofile)
    {
        try {
            $file = File::where('file_number', '=', $nrofile)->first();
            $flights = $request->__get('flights');
            $user_id = $request->__get('user_id');
            $reservation_flight = false;
            $success = false;
            $type = 'error';

            if ($file) {
                foreach ($flights as $flight) {
                    $flight = (object) $flight;

                    $type = @$flight->type;
                    $origin = @$flight->origin;
                    $destiny = @$flight->destiny;
                    $date = @$flight->date;
                    $types = ['AECFLT', 'AEIFLT'];

                    $reservation_flight = new ReservationsFlight;
                    $reservation_flight->reservation_id = $file->reservation_id;
                    $reservation_flight->code_flight = (!empty($type)) ? $types[$type] : '';
                    $reservation_flight->origin = (!empty($origin)) ? $origin['codciu'] : '';
                    $reservation_flight->destiny = (!empty($destiny)) ? $destiny['codciu'] : '';
                    $reservation_flight->date = (!empty($date)) ? $date : '';
                    $reservation_flight->adult_num = $file->adults;
                    $reservation_flight->child_num = $file->children;
                    $reservation_flight->inf_num = $file->infants;
                    $reservation_flight->create_user_id = $user_id;
                    $reservation_flight->status_email = 0;
                    $reservation_flight->save();

                    Reservation::where('id', '=', $file->reservation_id)->update([
                        'status_cron_job_reservation_stella' => 2
                    ]);

                    $success = true;
                    $type = 'success';
                }
            }

            return response()->json([
                'type' => $type,
                'success' => $success,
                'message' => 'Información guardada correctamente.',
                'file' => $file,
                'reservation_flight' => $reservation_flight,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function save_flight(Request $request, $nrofile, $nroite)
    {
        try {
            $params = [
                'type' => $request->__get('type'),
                'codsvs' => $request->__get('codsvs'),
                'origin' => $request->__get('origin'),
                'destiny' => $request->__get('destiny'),
                'date' => $request->__get('date'),
                'departure' => $request->__get('departure'),
                'arrival' => $request->__get('arrival'),
                'pnr' => $request->__get('pnr'),
                'airline' => $request->__get('airline'),
                'number_flight' => $request->__get('number_flight'),
                'paxs' => $request->__get('paxs'),
            ];

            $response = (array) $this->stellaService->save_flight($nrofile, $nroite, $params);
            return $response;
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function destinations(Request $request, $type)
    {
        $query = $request->__get('query');

        $params = [
            'type' => $type,
            'term' => ($query != '') ? $query : ''
        ];

        $response = (array) $this->stellaService->search_destinations($params);

        foreach ($response['data'] as $key => $value) {
            $value = $this->toArray($value);

            $translation = Translation::where('value', '=', $value['ciudad'])
                ->where('type', '=', 'city')->first();

            if ($translation) {
                City::where('id', '=', $translation['object_id'])->where(function ($query) {
                    $query->orWhere('iso', '=', '');
                    $query->orWhereNull('iso');
                })->update([
                    'iso' => $value['codciu']
                ]);
            }
        }

        // $response = $this->searchDestinations($type, $query);
        return response()->json($response);
    }

    public function airlines(Request $request)
    {
        $query = $request->__get('query');

        $params = [
            'term' => ($query != '') ? $query : ''
        ];

        $response = (array) $this->stellaService->search_airlines($params);

        // $response = $this->searchAirlines($query);
        return response()->json($response);
    }

    /*
    public function update_flight(Request $request)
    {
        $nrofile = $request->__get('nrofile');
        $nroite = $request->__get('nroite');
        $origin = $request->__get('origin');
        $destiny = $request->__get('destiny');
        $date = $request->__get('date');
        $departure = $request->__get('departure');
        $arrival = $request->__get('arrival');
        $pnr = $request->__get('pnr');
        $airline = $request->__get('airline');
        $number_flight = $request->__get('number_flight');
        $paxs = $request->__get('paxs');

        $response = $this->updateFlight($nrofile, $nroite, $origin, $destiny, $date, $departure, $arrival,
            $pnr, $airline, $number_flight, $paxs);
        return response()->json($response);
    }
    */

    public function modal_update(Request $request)
    {
        try {
            $log = new LogModal;
            $log->type = 'flights';
            $log->user_id = (int) $request->__get('user_id');
            $log->client_id = (int) $request->__get('client_id');
            $log->nrofile = $request->__get('nrofile');
            $log->data = json_encode($request->__get('data'));
            $log->save();

            return response()->json([
                'type' => 'success',
                'log' => $log,
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }
}
