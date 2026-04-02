<?php

namespace App\Http\Services\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Traits\Reservations;
use App\Http\Traits\CalculateCancellationlPolicies;
use App\Http\Traits\CalculateHotelTxesAndServices;

/**
 * Class SearchTokenHotelController
 * @package App\Http\Services\Controllers
 */
class SearchTokenHotelController extends Controller
{
    use Reservations;
    use CalculateHotelTxesAndServices;
    use CalculateCancellationlPolicies;

    public function __construct()
    {
        $this->reservation_errors = collect();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try { 
            
            $guests = [];
            foreach($request->input('guests') as $guest){
                if(!isset($guest['given_name'])){                    
                    $guest['given_name'] = ''; 
                } 
                if(!isset($guest['surname'])){                    
                    $guest['surname'] = ''; 
                } 
                array_push($guests, $guest);
            }

            $reservations = [];
            foreach($request->input('reservations') as $reservation){
                if(!isset($reservation['child_ages'])){                    
                    $reservation['child_ages'] = NULL; 
                } 
                array_push($reservations, $reservation);
            }

            $request->merge(['guests' => $guests]);
            $request->merge(['reservations' => $reservations]);
            $request->merge(['file_code' => '']);
            $response = $this->preReservation($request);
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'error' => [
                    'error_code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ];
            return Response::json($response);
        }
        return Response::json($response);
    }
 



}
