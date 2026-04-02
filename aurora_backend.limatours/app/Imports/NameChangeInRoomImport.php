<?php

namespace App\Imports;

use App\Translation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class NameChangeInRoomImport implements ToCollection
{ 

    public function collection(Collection $rows)
    {
        try
        { 

            foreach ($rows as $i => $row)
            {
                if(is_numeric($row[3])){
                     
                    $room_id = $row[3];
                    $room_name_es = trim($row[5]);
                    $room_name_en = trim($row[7]);
                    $room_name_pt = trim($row[9]);

                    $translations = Translation::where('type', '=', 'room')->where('slug', '=', 'room_name')->where('object_id', '=', $room_id)->get();

                    if($room_name_es){
                        $translation_es = $translations->first(function($item) {
                            return $item->language_id == 1;
                        }); 
                        if($translation_es){
                            $translation_es->value = $room_name_es;
                            $translation_es->save();
                        }
                    }
                      
                    if($room_name_en){
                        $translation_en = $translations->first(function($item) {
                            return $item->language_id == 2;
                        }); 
                        if($translation_en){
                            $translation_en->value = $room_name_en;
                            $translation_en->save();
                        }
                    }

                    if($room_name_pt){
                        $translation_pt = $translations->first(function($item) {
                            return $item->language_id == 3;
                        }); 
                        if($translation_pt){
                            $translation_pt->value = $room_name_pt;
                            $translation_pt->save();
                        }                              
                    }
                     
                }

            }
        }
        catch(\Exception $ex)
        {
            echo $ex->getMessage();
        }
    }
}
