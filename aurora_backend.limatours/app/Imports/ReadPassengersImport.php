<?php

namespace App\Imports;
 
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ReadPassengersImport implements ToCollection, WithStartRow
{
    public $data;

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        try
        { 
            $results = [];
            foreach ($rows as $i => $params)
            {                
                 array_push($results, [
                    'first_name' => $params[1],
                    'last_name' => $params[2],
                    'doctype_iso' => $params[3],
                    'document_number' => $params[4],
                    'country_iso' => $params[5],
                    'gender' => $params[6],
                    'birthday' => $params[7] ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($params[7])->format('d/m/Y') : '',
                    'email' => $params[8],
                    'phone_code' => $params[9],
                    'phone' => $params[10],
                    'tiphab' => $params[11],
                    'medical_restrictions' => $params[12],
                    'dietary_restrictions' => $params[13]
                 ]);
            }

            $this->data = $results;
        }
        catch(\Exception $ex)
        {
            return $ex->getMessage();
        }
    }
}
