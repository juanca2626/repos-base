<?php

namespace App\Imports;

use App\Service;
use App\Http\Traits\Translations;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class ServiceNotesImport implements ToCollection
{
    use Translations;

    public function collection(Collection $rows)
    {
        try
        {
            foreach ($rows as $i => $params)
            {
                if(!empty($params[0]))
                {
                    $service = Service::with(['service_translations'])
                        ->where('aurora_code', '=', $params[0])
                        ->first();
                    
                    if($service)
                    {
                        foreach($service->service_translations as $key => $value)
                        {
                            if(isset($params[$value['language_id']]) AND !empty($params[$value['language_id']]))
                            {
                                DB::table('service_translations')
                                    ->where('id', '=', $value->id)
                                    ->update([
                                        'updated_at' => date("Y-m-d H:i:s"),
                                        'summary' => $params[$value['language_id']],
                                        'summary_commercial' => $params[$value['language_id']],
                                    ]);
                            }
                        }
                    }
                }
            }
        }
        catch(\Exception $ex)
        {
            print_r($ex->getMessage());
            die;
        }
    }
}
