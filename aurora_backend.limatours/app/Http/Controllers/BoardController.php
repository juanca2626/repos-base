<?php

namespace App\Http\Controllers;

use App\ChannelHotel;
use App\City;
use App\Client;
use App\ClientExecutive;
use App\Contact;
use App\EquivalenceService;
use App\Hotel;
use App\Http\Stella\StellaService;
use App\Imports\PassengersImport;
use App\MasterService;
use App\Service;
use App\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{
    protected $stellaService;

    public function __construct(StellaService $stellaService)

    {
        $this->stellaService = $stellaService;
    }

    public function search_services_by_codes(Request $request)
    {
        try
        {
            $codes = $request->__get('codes'); $new_services = [];
            $services = Service::with('equivalence_services.master_service')
                ->whereIn('aurora_code', $codes)
                ->get()->toArray();

            foreach($services as $key => $value)
            {
                $value['aurora_code'] = strtoupper($value['aurora_code']);
                $new_services[$value['aurora_code']] = $value;
            }
                
            return response()->json($new_services);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function search_executives(Request $request)
    {
        try
        {
            $_executives = $request->__get('array_codes');
            $search = $request->__get('search');
            $executives = User::where('status', '=', 1);

            if(!empty($_executives))
            {
                $_executives = explode(",", $_executives);

                $executives = $executives->where(function ($query) use ($_executives) {
                    $query->orWhereIn('code', $_executives);
                    $query->orWhereIn('id', $_executives);
                });
            }
            else
            {
                if(!empty($search))
                {
                    $executives = $executives->where(function ($query) use ($search) {

                        $search = explode(" ", $search);

                        foreach($search as $value)
                        {
                            if($value != '')
                            {
                                $query->orWhere('code', 'like', '%' . $value . '%');
                                $query->orWhere('name', 'like', '%' . $value . '%');
                            }
                        }
                    });
                }

                $executives = $executives->whereHas('roles', function ($query) {
                    $query->whereIn('roles.id', [3, 6, 11, 13, 16, 30]);
                })->take(10);
            }

            $executives = $executives->get();

            return response()->json([
                'type' => 'success',
                'executives' => $executives,
                'search' => $search,
                'array_codes' => $_executives,
            ]);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function search_clients(Request $request)
    {
        try
        {
            $_clients = $request->__get('array_codes');
            $search = $request->__get('search');
            $clients = Client::where('status', '=', 1);

            if(!empty($_clients))
            {
                $_clients = explode(",", $_clients);

                $clients = $clients->where(function ($query) use ($_clients) {
                    $query->orWhereIn('id', $_clients);
                    $query->orWhereIn('code', $_clients);
                });
            }
            else
            {
                if(!empty($search))
                {
                    $clients = $clients->where(function ($query) use ($search) {

                        $search = explode(" ", $search);

                        foreach($search as $value)
                        {
                            if($value != '')
                            {
                                $query->orWhere('code', 'like', '%' . $value . '%');
                                $query->orWhere('name', 'like', '%' . $value . '%');
                            }
                        }
                    });
                }
                
                $clients = $clients->take(10);
            }
            
            $clients = $clients->get(); $all_clients = collect();

            $clients->each(function ($client) use (&$all_clients) {
                $all_clients[$client['id']] = $client['code'] . '-' . $client['name'];
            });

            return response()->json([
                'type' => 'success',
                'clients' => $all_clients,
                'all_clients' => $clients,
            ]);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function search_boss_executives(Request $request)
    {
        try
        {
            $codes = explode(",", $request->__get('array_codes'));
            $data = [
                'codes' => "'" . implode("','", $codes) . "'",
            ];

            $response = $this->toArray($this->stellaService->search_boss($data));
            // $response = [];
            $executives_boss = [];

            foreach($response as $key => $value)
            {
                $executives_boss[trim($value['nomesp'])] = trim($value['abrev1']);
            }
            
            return response()->json([
                'codes' => $codes,
                'executives_boss' => $executives_boss,
                'type' => 'success',
            ]);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function search_equivalence_services(Request $request)
    {
        try
        {
            $limit = $request->__get('first'); $id = (int) $request->__get('id_greater_than');
            $equivalence_services = EquivalenceService::where('id', '>', $id)->take($limit)->get();
            
            return response()->json($equivalence_services);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function search_master_services(Request $request)
    {
        try
        {
            $limit = $request->__get('first'); $id = (int) $request->__get('id_greater_than');
            $master_services = MasterService::where('id', '>', $id)->take($limit)->get();
            
            return response()->json($master_services);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function search_board(Request $request)
    {
        try
        {
            $type = $request->__get('type');

            $hoy = date("Y-m-d");
            $siete_dias = date("Y-m-d", strtotime("+7 days", strtotime(date("Y-m-d"))));
            $quince_dias = date("Y-m-d", strtotime("+15 days", strtotime(date("Y-m-d"))));
            $treinta_dias = date("Y-m-d", strtotime("+1 month", strtotime(date("Y-m-d"))));
            $limite_dias = date("Y-m-d", strtotime("+2 month", strtotime(date("Y-m-d"))));
            $response = [];

            switch($type)
            {
                case 'executive':
                {
                    $array = [
                        'identi' => 1,
                        'fecini' => $hoy,
                        'fecfin' => $siete_dias,
                        'codigo' => $request->__get('codigo')
                    ];
                    $response['response_siete'] = (array) $this->stellaService->board_files($array);

                    $array = [
                        'identi' => 1,
                        'fecini' => $siete_dias,
                        'fecfin' => $quince_dias,
                        'codigo' => $request->__get('codigo')
                    ];
                    $response['response_quince'] = (array) $this->stellaService->board_files($array);

                    $array = [
                        'identi' => 1,
                        'fecini' => $quince_dias,
                        'fecfin' => $treinta_dias,
                        'codigo' => $request->__get('codigo')
                    ];
                    $response['response_treinta'] = (array) $this->stellaService->board_files($array);

                    $array = [
                        'identi' => 1,
                        'fecini' => $treinta_dias,
                        'fecfin' => $limite_dias,
                        'codigo' => $request->__get('codigo')
                    ];
                    $response['response_mes'] = (array) $this->stellaService->board_files($array);
                };break;
                case 'boss':
                {
                    $array = [
                        'identi' => 5,
                        'fecini' => $hoy,
                        'fecfin' => $siete_dias,
                        'codigo' => $request->__get('codigo')
                    ];
                    $response = (array) $this->stellaService->board_files($array);
                }; break;
                case 'detail':
                {
                    $codigo = $request->__get('codigo');

                    if(is_array($codigo))
                    {
                        foreach($codigo as $key => $value)
                        {
                            $array = [
                                'identi' => 'T',
                                'fecini' => $value,
                                'fecfin' => $siete_dias,
                                'codigo' => 'MBB'
                            ];
                            $response[$value] = (array) $this->stellaService->board_files($array);
                        }
                    }
                    else
                    {
                        $array = [
                            'identi' => 'T',
                            'fecini' => $codigo,
                            'fecfin' => $siete_dias,
                            'codigo' => 'MBB'
                        ];
                        $response[$codigo] = (array) $this->stellaService->board_files($array);
                    }
                }; break;
            }
        }
        catch(\Exception $ex)
        {
            $response = [
                'type' => 'error',
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function infoByCommunication(Request $request){

        $executive_code = $request->input('executive_code');
        $hotel_id = $request->input('hotel_id');
        $client_id = $request->input('client_id');

        $executive = [];
        if($executive_code){
            $executiveSelected = User::where("code",$executive_code)->first();
            $executive = [
                'name' => $executiveSelected->name,
                'email' => $executiveSelected->email,
            ];
        }

        $contactHotel = [];
        if($hotel_id){
            $contactHotel = Contact::select('email')
                ->whereHotelId($hotel_id)->whereNotNull('email')
                ->groupBy('email')->pluck('email')->toArray();
        }

        $client = [];
        $client_executives_emails = [];
        if($client_id){
            $client = $this->getClient($client_id); 
            $client = [
                "code" => $client->code,
                "name" => $client->name,
                "pais" => $client->countries->translations[0]->value,
                "ciudad" => $client->city_name,
                "address" => $client->address,
                "ruc" => $client->ruc
            ];

            $client_executives_emails = $this->getClenteExecutive($client_id);
        }
           

        return [
            "executive" => $executive,
            "client" => $client ,
            "client_executives" => $client_executives_emails,
            "hotel_contacts" => $contactHotel, 
        ];

    }

    public function infoClientByCode(Request $request){

        $clients = Client::select('id', 'code', 'name')->whereIn('code', $request->input())->get();
        return response()->json($clients);
    }

    public function getClient($client_id, $lang="en"){

        $client = Client::with('markets')->with([
            'countries.translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])   
        ->where('id', $client_id)->first();

        return $client;

    }
    public function getClenteExecutive($client_id){

        $client_executives = ClientExecutive::where('client_id', $client_id)
            ->where('status', 1)
            ->where('use_email_reserve', 1)
            ->with('user')
            ->get();

        $client_executives_emails = [];

        foreach ($client_executives as $client_executive) {
            if ($client_executive->user && $client_executive->user->user_type_id == 3) {
           
                if(!in_array(trim($client_executive->user->email), $client_executives_emails)){                    
                   array_push($client_executives_emails, trim($client_executive->user->email));                 
                }
            }
        }

        return $client_executives_emails;
    }

    public function servicesDetails(Request $request){
 
        $reservations = $request->input('reservations', []);

        $results = [
            "services" => [],
            "hotels" => [],
            "flights" => []
        ];

        if(isset($reservations['services'])){
            $results['services'] = $this->getInfoServices($reservations['services']);
        }

        if(isset($reservations['hotels'])){
            $results['hotels'] = $this->getInfoHotels($reservations['hotels']);
        }

        if(isset($reservations['flights'])){
            $results['flights'] = $this->getInfoFlights($reservations['flights']);
        }

        return response()->json([
            'type' => 'success',
            'results' => $results
        ]);

    }

    public function getInfoServices($services)
    {

        $services = Service::whereIn('aurora_code', $services)
        ->with([
            'service_translations' => function ($query) {
                $query->with('language');
                $query->whereHas('language', function ($q) {
                    $q->where('state', 1);
                });
            }
        ])->with([
            'schedules' => function ($query) {
                $query->with('servicesScheduleDetail');
            }
        ])->get();
 
        $data = [];
        foreach($services as $service){

            $details = [];
            foreach($service->service_translations as $detail){
                array_push($details, [
                    'iso' => $detail->language->iso,
                    'name' => $detail->name,
                    'name_commercial' => $detail->name_commercial,
                    'description' => $detail->description,
                    'description_commercial' => $detail->description_commercial,
                    'itinerary' => $detail->itinerary,
                    'itinerary_commercial' => $detail->itinerary_commercial,
                    'summary' => $detail->summary,
                    'summary_commercial' => $detail->summary_commercial,
                    'itinerary' => $detail->itinerary,
                ]);
            }
            array_push($data, [
                'id' => $service->id,
                'code' => $service->aurora_code, 
                'details' => $details
            ]);
        }

        
        return $data;
    }

    public function getInfoHotels($hotels)
    { 
        $data = [];
        foreach($hotels as $hotel){ 


            $result = Hotel::with([
                'rates_plans' => function ($query) use ($hotel) { 

                    $query->with([
                        'meal.translations' => function ($query){ 
                            $query->with('language');
                            $query->whereHas('language', function ($q) {
                                $q->where('state', 1);
                            });
                            $query->where('type', 'meal');
                            $query->where('slug', 'meal_name');
                        }
                    ]);

                    $query->where('id', $hotel['hotel_rate']);
                }
            ])->where('id', $hotel['hotel_id'])->first();
            
            if($result){

                $meals = [];

                if(isset($result->rates_plans[0])){
                    $meals = collect($result->rates_plans[0]->meal->translations)->map(function ($row) {
                        return [
                            'iso' => $row->language->iso,
                            'name' => $row->value,
                        ];
                    });
                }

                array_push($data, [ 
                    'itinerary_id' => $hotel['itinerary_id'],
                    'hotel_id' => $hotel['hotel_id'],
                    'hotel_code' => $hotel['hotel_code'],
                    'hotel_name' => $result->name,
                    'hotel_stars' => $result->stars, 
                    'hotel_rate' => $hotel['hotel_rate'],                                                      
                    'url' => $result->web_site, 
                    'meals' => $meals 
                ]);
            }
        }

        return $data; 
    }

    public function getInfoFlights($flights)
    {
 
        $cities = City::with([
            'translations' => function ($query){
                $query->with('language');
                $query->where('type', 'city'); 
            }
        ])
        ->whereIn('iso', $flights)->get();

        $results = [];
        foreach($cities as $city){

            $translations = [];
            foreach($city->translations  as  $translate){
                array_push($translations, [
                    'iso' => $translate->language->iso,
                    'name' => $translate->value,
                ]);
            }
            array_push($results, [
                'iso' => $city['iso'],
                'translations' => $translations
            ]);
        }
        return $results;


        // ->with([
        //     'state' => function ($query) {
        //         $query->with([
        //             'country.translations' => function ($query) {
        //                 $query->where('type', 'country'); 
        //             }
        //         ]);
        //         $query->with([
        //             'translations' => function ($query) {
        //                 $query->where('type', 'state'); 
        //             }
        //         ]);
                  
        //     }
        // ])

    }
}
