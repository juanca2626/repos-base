<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Carbon\Carbon;
use App\Models\Quote;
use App\Models\QuoteService;
use App\Models\QuoteServiceAmount;
use App\Models\QuoteServiceRoomHyperguest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Traits\Quotes as TraitQuote;

class QuotesRateHPullController extends Controller
{
    use TraitQuote;
    
    public function index($quote_id, Request $request)
    {
         
    }

    public function updateRateHyperguestPull($quote_id, Request $request)
    {
        $results = $request->data;
        $quote_service = [] ;
        foreach($results as $data)
        {    
            $quote_service_delete =   isset($data['quote_service_delete']) ? $data['quote_service_delete'] : 0;
            if(!isset($quote_service[$data['quote_service_id']]))
            {
                $quote_service[$data['quote_service_id']] = [];
                $quote_service[$data['quote_service_id']]['quote_service_id'] = $data['quote_service_id'];
                $quote_service[$data['quote_service_id']]['quote_service_delete'] = $quote_service_delete;
                $quote_service[$data['quote_service_id']]['amount'] = [];
            } 
            if($quote_service_delete !== 1 )
            {
                $quote_service[$data['quote_service_id']]['amount'][] =  $data;
            }
        } 

        foreach($quote_service as $quote_service_id => $services)
        {
            QuoteService::where('id', $quote_service_id )->update(['on_request' => 0]);
            QuoteServiceAmount::where('quote_service_id', $quote_service_id )->delete();
             
            if(count($services['amount'])>0)
            {
                foreach($services['amount'] as  $data)
                {                
                    QuoteServiceAmount::insert([
                            'quote_service_id'               => $data['quote_service_id'],
                            'date_service'                   => Carbon::parse($data['date_service'])->format('d/m/Y'),
                            'price_per_night_without_markup' => $data['price_per_night_without_markup'],
                            'price_per_night'                => $data['price_per_night'],
                            'price_adult_without_markup'     => $data['price_adult_without_markup'],
                            'price_adult'                    => $data['price_adult'],
                            'price_child_without_markup'     => $data['price_child_without_markup'],
                            'price_child'                    => $data['price_child'],
                            'price_teenagers_without_markup' => $data['price_teenagers_without_markup'],
                            'price_teenagers'                => $data['price_teenagers'],
                            'created_at'                     => Carbon::now(),
                            'updated_at'                     => Carbon::now(),
                    ]);
                }
            }

        }

        return Response::json([
            'success'        => true 
         ]);
    }

    public function getRateHyperguestPull($quote_id, Request $request)
    {
        $client_id = $this->getClientId($request);
        $quote_service_ids = $request->input('quote_service_ids', []); 


        if(!$client_id)
        {            
            throw new \Exception('client no selected');
        }

        $quote = $this->getQuoteNew($quote_id, $quote_service_ids);        
        $markup_quote = $quote->markup ? $quote->markup : 0;
  
        $hotels_select = [];

        if ($quote) { 
            if ($quote->operation == 'passengers') {
                foreach ($quote->categories as $category) {
                    foreach ($category->services as $quote_service) {                                               
                        if ($quote_service->type == 'hotel' and ($quote_service->single>0 or $quote_service->double>0 or $quote_service->triple>0 ) ) {                                                    
                            if ($quote_service->hyperguest_pull) {   
                                                                
                                if($quote_service->markup_regionalization>0)
                                {
                                    $markup = $quote_service->markup_regionalization;
                                }else{
                                    if(!$markup_quote)
                                    {
                                        $markup = $this->getMarkupHotel($client_id, $quote_service->object_id, $quote_service->date_in_format);
                                    }else{
                                        $markup = $markup_quote;
                                    }
                                }

                                array_push($hotels_select,  [                                    
                                    'quote_service_id' => $quote_service->id,
                                    'hotel_id' => $quote_service->object_id,
                                    'date_in' => $quote_service->date_in,
                                    'date_out' => $quote_service->date_out,
                                    'hyperguest_pull' => $quote_service->hyperguest_pull,
                                    'params' => $this->getParamsForSearchNew($quote_service, $client_id, $markup),
                                    'passengers_quantity' => $quote_service->passengers->count(),
                                    'service_rate_plan_room' => $quote_service->service_rooms_hyperguest->first()
                                ]);                                
                            }
                        }
                    }
                }
            }
        }

         return Response::json([
            'success'        => true,
            'result' => $hotels_select
         ]);
    }

    /**
     * @param  bool  $withRelationShip | True => Trae la cotizacion con sus relaciones
     * @param  bool  $servicesLooked | True => Trae todos los servicios asi estan bloqueados
     */
    public function getQuoteNew($quote_id, $quote_service_ids = [] ): mixed
    {
        $quote = Quote::where('id', $quote_id);
         
        $quote = $quote->with([
            'categories' => function ($query) use ($quote_service_ids){
                $query->select(['id', 'quote_id', 'type_class_id', 'created_at', 'updated_at']);
                $query->with([
                    'services' => function ($query) use ($quote_service_ids){
                        $query->select([
                            'id',
                            'quote_category_id',
                            'type',
                            'object_id',
                            'order',
                            'date_in',
                            'date_out',
                            'hour_in',
                            'nights',
                            'adult',
                            'child',
                            'infant',
                            'single',
                            'double',
                            'double_child',
                            'triple',
                            'triple_child',
                            'triple_active',
                            'locked',
                            'on_request',
                            'extension_id',
                            'new_extension_id',
                            'parent_service_id',
                            'optional',
                            'code_flight',
                            'origin',
                            'destiny',
                            'date_flight',
                            'notes',
                            'schedule_id',
                            'is_file',
                            'file_itinerary_id',
                            'file_status',
                            'file_amount_sale',
                            'file_amount_cost',
                            'hyperguest_pull'
                        ]);
                        $query->with(['service_rooms_hyperguest']);                            
                        $query->with(['passengers']);
                        $query->where('locked', 0);

                        if(count($quote_service_ids)>0)
                        {
                            $query->whereIn('id', $quote_service_ids);
                        }

                    },
                ]);

            },
        ]);                               
 

        return $quote->first([
            'id',
            'code',
            'name',
            'date_in',
            'cities',
            'nights', 
            'user_id',
            'service_type_id',
            'status', 
            'created_at',
            'updated_at',
            'operation',
            'markup'
        ]);
    }    


    private function getParamsForSearchNew($service, $client_id, $set_markup=NULL)
    {      
        //  dd($service);                     
        $hotel = Hotel::where('id', $service['object_id'])
        ->with(['country.translations'=>function($query){
            $query->where('language_id',1);
        }, 'state.translations'=>function($query){
            $query->where('language_id',1);
        }, 'city.translations'=>function($query){
            $query->where('language_id',1);
        }])
        ->first();

        $ages_child = [];
        $i=1;
        foreach ($service['passengers'] as $index => $passenger) {
            if ($passenger['passenger']['type'] == 'CHD') {
                $age = isset($passenger['passenger']['age_child']) ? $passenger['passenger']['age_child']['age'] : 1;  
                array_push($ages_child, [
                    'child' => $i,
                    'age' => $age
                ]);
                $i++;              
            }  
        }

        return [
            'client_id' => $client_id,
            'hotels_id' => [
                $hotel->id
            ],
            'hotels_search_code' => '',
            'lang' => 'en',
            'destiny' => [
                "code" => $hotel->country->iso.",".$hotel->state->iso,
                "label" => $hotel->country->translations[0]->value.",".$hotel->state->translations[0]->value,
            ],
            'date_from' => $service['date_in_format'],
            'date_to' => $service['date_out_format'],
            'typeclass_id' => 'all',
            'quantity_rooms' => 1,
            'quantity_persons_rooms' => [
                [
                    "room" => 1,
                    "adults" => $service['adult'],
                    "child" => $service['child'],
                    "ages_child" => $ages_child,
                ],
            ],
            'set_markup' => $set_markup, // se respeta el markup que colocaron en la cotización
            'zero_rates' => true
        ];

    } 
    
    


    public function clearRateHyperguestPull($quote_id, Request $request)
    {       
        $quote_service = [] ;
  
        if ($quote_id !== null && $quote_id != "null" && $quote_id !== '') 
        {       
        
            $quotes = Quote::with('categories')->find($quote_id);

            if(isset($quotes->categories))
            {                
                foreach($quotes->categories as $category)
                {
                    $quote_service = QuoteService::where('quote_category_id', $category->id)->where('hyperguest_pull', '1')->get();

                    foreach($quote_service as $services)
                    {                
                        QuoteServiceAmount::where('quote_service_id', $services->id )->delete();                                 
                    }

                }
            }
        }

        return Response::json([
            'success'        => true 
        ]);
    }

}
