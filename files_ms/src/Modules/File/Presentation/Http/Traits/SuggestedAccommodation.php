<?php

namespace Src\Modules\File\Presentation\Http\Traits;

trait SuggestedAccommodation
{
 
    public function suggested_accommodation($file)
    { 
        $accommodation_all = [];

        foreach($file->itineraries as $itinerary){
            if($itinerary['entity'] == 'hotel'){
                $accommodation = [
                    'sgl' => 0,
                    'dbl' => 0,
                    'tpl' => 0,
                ];
                foreach($itinerary['rooms'] as $room){
                    if($room['occupation'] == 1){
                        $accommodation['sgl'] = $accommodation['sgl'] + $room['total_rooms'];
                    }
                    if($room['occupation'] == 2){
                        $accommodation['dbl'] = $accommodation['dbl'] + $room['total_rooms'];
                    }
                    if($room['occupation'] == 3){
                        $accommodation['tpl'] = $accommodation['tpl'] + $room['total_rooms'];
                    }
                }                
                array_push($accommodation_all, $accommodation);
            }
        }

        if(count($accommodation_all)>0)
        {
            $accommodation = $this->obtenerItemMasRepetido($accommodation_all);
        }else{
            $accommodation = [
                'sgl' => 0,
                'dbl' => 0,
                'tpl' => 0,
            ];
        }
        
        return $accommodation;
    }  

    function obtenerItemMasRepetido(array $array) {
        // Convertimos cada elemento a JSON para poder compararlos como strings
        $jsonItems = array_map('json_encode', $array);
        
        // Contamos repeticiones
        $conteo = array_count_values($jsonItems);
    
        // Ordenamos de mayor a menor
        arsort($conteo);
    
        // Obtenemos el primero (más repetido)
        $jsonMasRepetido = array_key_first($conteo);
        $itemMasRepetido = json_decode($jsonMasRepetido, true);
    
        return $itemMasRepetido;
    }
}
