<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Traits\ApiResponse; 
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;  
use App\Http\Controllers\Controller;
use File;
use Src\Modules\File\Application\UseCases\Commands\GetCancelItineraryRoomUnitCommand;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery;
use Src\Modules\File\Application\UseCases\Queries\FindFileItineraryByIdArrayQuery;
use Src\Modules\File\Application\UseCases\Queries\FindFileItineraryByIdQuery;
use Src\Modules\File\Application\UseCases\Queries\SerachFileItineraryHotelByCancellation;
use Src\Modules\File\Application\UseCases\Queries\SerachFileItineraryHotelByModifyCommunication;
use Src\Modules\File\Application\UseCases\Queries\SerachFileItineraryHotelByNewCommunication;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraryHotelByModifyCommunication;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FileCommunicationModifyReservationController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request, $file_itinerary_id)
    {
         try
        {   
            $params = $request->input();
            $fileItinerary = (new FindFileItineraryByIdArrayQuery($file_itinerary_id, 'array'))->handle();
            $file_id = $fileItinerary['file_id'];

            $file = (new FindFileByIdAllQuery($file_id))->handle();

            $file['itineraries'] = [];
            array_push($file['itineraries'], $fileItinerary); // El modificado
            
            // reserva que se cancela   
            $roomsUnitForCancelation =  (new GetCancelItineraryRoomUnitCommand($file_itinerary_id))->handle();
            $fileItineraryByCancellation = (
                new SerachFileItineraryHotelByCancellation($file, $file_itinerary_id, $roomsUnitForCancelation)
            )->handle();
            

            // nuevo reserva

            $file = (new FindFileByIdAllQuery($file_id))->handle();
            $file['itineraries'] = [];
            array_push($file['itineraries'], $fileItinerary); // El modificado
            array_push($file['itineraries'], (new FindFileItineraryByIdArrayQuery($request->input('send_communication_file_itinerary_id'), 'array'))->handle()); // El nuevo

            $file = (
                new SerachFileItineraryHotelByModifyCommunication($file, $request->input())
            )->handle(); 
             
            $html = ""; $subject = "Modificación de reserva [".$file['file_number']."] - ".$file['itineraries']['object_code']."]."."-".$file['description'];
            if(count($file)>0){   

                $html_rate_cost = view("emails.reservations.hotels.reservation.modification", ["reservation" => $file, "file" => $fileItineraryByCancellation, "notas" => $params['notas'], "attachments" => $params['attachments'], "type" => "cost"] )->render();
                $html_rate_sale = view("emails.reservations.hotels.reservation.modification", ["reservation" => $file, "file" => $fileItineraryByCancellation, "notas" => $params['notas'], "attachments" => $params['attachments'], "type" => "sale"] )->render();                

                // return view('emails.reservations.hotels.reservation.modification', ["reservation" => $file, "file" => $fileItineraryByCancellation, "notas" => $params['notas'], "attachments" => $params['attachments'], "type" => "cost"]);
                                
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

            }


        } catch (\Exception $domainException) {
                    
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }  

         
    }
 
}
