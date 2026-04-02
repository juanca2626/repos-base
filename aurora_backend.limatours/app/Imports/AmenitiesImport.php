<?php

namespace App\Imports;

use App\Amenity;
use App\Http\Traits\Translations;
use App\Translation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class AmenitiesImport implements ToCollection
{
    use Translations;

    public function collection(Collection $rows)
    {
        try
        {
            // dd($rows);

            foreach ($rows as $i => $params)
            {
                if($i > 0)
                {
                    $amenity = Amenity::where('id', '=', $params[0])->first();

                    if($amenity)
                    {
                        $_translations = [];

                        $translations = Translation::where('type', '=', 'amenity')
                            ->where('object_id', '=', $amenity->id)->get()->toArray();

                        foreach($translations as $key => $value)
                        {
                            if(isset($params[$value['language_id']]))
                            {
                                $value['value'] = $params[$value['language_id']];
                                $_translations[$value['language_id']] = [
                                    'id' => $value['id'],
                                    'amenity_name' => $value['value'],
                                ];
                            }
                        }
                    }

                    if(count($_translations) > 0)
                    {
                        $this->saveTranslation($_translations, 'amenity', $amenity->id);
                    }
                }
            }
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }
    }
}
