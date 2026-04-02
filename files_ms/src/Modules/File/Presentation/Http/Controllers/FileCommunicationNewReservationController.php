<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Traits\ApiResponse; 
use Illuminate\Http\Request;  
use App\Http\Controllers\Controller; 
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery;
use Src\Modules\File\Application\UseCases\Queries\FindFileItineraryByIdArrayQuery;
use Src\Modules\File\Application\UseCases\Queries\FindFileItineraryByIdQuery;
use Src\Modules\File\Application\UseCases\Queries\SerachFileItineraryHotelByNewCommunication; 
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileCommunicationNewReservationController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request, $file_id)
    {
 
        try { 

            $params = $request->input();
         
            $file = (new FindFileByIdAllQuery($file_id))->handle();
            $file['itineraries'] = [];  
            array_push($file['itineraries'], (new FindFileItineraryByIdArrayQuery($request->input('send_communication_file_itinerary_id'), 'array'))->handle()); // El nuevo

            $file = (
                new SerachFileItineraryHotelByNewCommunication($file, $request->input('send_communication_file_itinerary_id'))
            )->handle(); 
 


            // return $this->successResponse(ResponseAlias::HTTP_OK, $fileItineraryByCancellation);

            $subject = "Reserva confirmada [".$file['file_number']."] - ".$file['itineraries']['object_code']."]."."-".$file['description'];
            $view = 'emails.reservations.hotels.reservation.confirmation';
            if(!$file['itineraries']['confirmation_status']){ 
                $subject = "Solicitud de reserva [".$file['file_number']."] - ".$file['itineraries']['object_code']."]."."-".$file['description'];
                $view = 'emails.reservations.hotels.reservation.solicitude';
            }
             
            $html_rate_cost = view($view, ["reservation" => $file, "notas" => $params['notas'], "attachments" => $params['attachments'], "type" => "cost"] )->render();
            $html_rate_sale = view($view, ["reservation" => $file, "notas" => $params['notas'], "attachments" => $params['attachments'], "type" => "sale"] )->render();
        
            return $this->successResponse(ResponseAlias::HTTP_OK, [
                'subject' => $subject,
                'html' => [
                    'hotel' => $html_rate_cost,
                    'client' => $html_rate_sale,
                    'executive' => $html_rate_sale,
                ],
                'executive_email' => [$file['executive_email']],
                'clients_email' => $file['client_executives'],
                'hotel_contacts' =>  $file['hotel_contacts']
            ]);

            // return view('emails.reservations.hotels.reservation.confirmation', ["reservation" => $file, "notas" => $params['notas'], "attachments" => $params['attachments'], "type" => "sale"]);
            // return view('emails.reservations.hotels.reservation.solicitude', ["reservation" => $file, "notas" => $params['notas'], "attachments" => $params['attachments'], "type" => "sale"]);
            
        } catch (\DomainException $domainException) {
                
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }
        
    }
 
}
