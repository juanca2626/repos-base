<?php

namespace App\Http\Controllers;

use App\Country;
use App\Hotel;
use App\State;
use Illuminate\Http\Request;  
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportUbigeoController extends Controller
{
    public function update_iso_states(Request $request)
    {
        $file = File::get(database_path('data/states_iso_hyperguest.json'));
        $states = json_decode($file);
        $created_at = date("Y-m-d H:i:s");
        $stela_states = [];
        // DB::transaction(function () use ($inclusions, $created_at) {
            foreach ($states as $state) {
                array_push($stela_states,
                    [
                        'country_iso' => trim($state->codgru),
                        'state_iso' => trim($state->codigo),
                        'state_name' => trim($state->descri),
                    ]
                );
            }
        // });
       
       $stela_states = collect($stela_states);

 
       $no_encuentra_por_hotel = $this->searchForHotel($stela_states);  

       return Response::json($no_encuentra_por_hotel);

    }
    
    private function searchForHotel($stela_states)
    {
       // 103= ecuador,  5= argentina, 82= colombia
       $hotels = Hotel::with('country')->with([
            'state.translations' => function ($query) {
                $query->where('language_id', 1);
                }
            ])->select('country_id','state_id')->whereIn('country_id',[103, 5, 82])->get()->unique('state_id')->values();      
       $no_encuentra_por_pais = [];
       foreach($hotels as $hotel)
       {
            
            $state = $hotel->state;
                        
            $resultado = $stela_states->filter(function ($item) use ($state) {  
                return $this->normalizar($item['state_name']) === $this->normalizar(trim($state->translations[0]->value));
            });

            if($resultado->isEmpty()){

                $resultado = $stela_states->filter(function ($item) use ($state) {
                    return Str::contains(
                        $this->normalizar($item['state_name']),
                        $this->normalizar($state->translations[0]->value)
                    );
                });

            }

            if($resultado->isEmpty()){           
                $no_encuentra_por_pais[$hotel->country->iso][] = [
                    'id' => $state->id,
                    'name' => $state->translations[0]->value
                ];
            }else{    
                // dd($resultado, $resultado->first());      
                State::where('id', $hotel->state_id)->update([ 
                    'iso' => $resultado->first()['state_iso']
                ]);                
            }
           

       }

       return $no_encuentra_por_pais;


    }    

    private function searchForCountry($stela_states)
    {

       $countries = Country::whereIn('iso',['AR', 'EC', 'CO'])->get();
       $no_encuentra_por_pais = [];
       foreach($countries as $country)
       {
            $states = State::with([
            'translations' => function ($query) {
                $query->where('language_id', 1);
                }
            ])->where('country_id', $country->id)->get();

            foreach($states as $state)
            {
               
                $resultado = $stela_states->filter(function ($item) use ($state) {  
                    return $this->normalizar($item['state_name']) === $this->normalizar(trim($state->translations[0]->value));
                });

                if($resultado->isEmpty()){

                    $resultado = $stela_states->filter(function ($item) use ($state) {
                        return Str::contains(
                            $this->normalizar($item['state_name']),
                            $this->normalizar($state->translations[0]->value)
                        );
                    });

                }

                if($resultado->isEmpty()){
                    // dd($resultado);
                    $no_encuentra_por_pais[$country->iso][] = [
                        'id' => $state->id,
                        'name' => $state->translations[0]->value
                    ];
                }
            }

       }

       return $no_encuentra_por_pais;


    }


     /**
     * Normaliza cadenas para ignorar mayúsculas y acentos.
     */
    private function normalizar($cadena)
    {
        if (class_exists('\Normalizer')) {
            $cadena = \Normalizer::normalize($cadena, \Normalizer::FORM_D);
            $cadena = preg_replace('/\p{Mn}/u', '', $cadena); // elimina marcas (acentos)
        }

        return strtolower($cadena);
    }

}
