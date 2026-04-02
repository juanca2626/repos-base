<?php

namespace Src\Modules\File\Domain\Model;

use Src\Shared\Domain\Entity;

class OperationServiceZero
{

    public string $city_in;
    public string $city_out;
    public string $start_date;
    public string $end_date;
    public string $start_validity;
    public string $days_operation;
    public string $operating_hours;
    public string $code_country;
    public string $created_at; 
    public string $updated_at; 
    public string $country; 
    public string $file_service_zero_id;
       
    public function __construct(
       
         string $city_in, 
         string $city_out, 
         string $start_date, 
         string $end_date, 
         string $start_validity, 
         string $days_operation,
         string $operating_hours,
         string $code_country, 
         string $created_at,
         string $updated_at, 
         string $country,
         string $file_service_zero_id
         )
    {
        
        $this->city_in = $city_in;
        $this->city_out = $city_out;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->start_validity = $start_validity ?? '';
        $this->days_operation = $days_operation;
        $this->operating_hours = $operating_hours ?? '';
        $this->code_country = $code_country ?? '';
        $this->created_at = $created_at ?? ''; // Asigna los valores
        $this->updated_at = $updated_at ?? ''; // Asigna los valores
        $this->country = $country ?? ''; // Asigna los valores
        $this->file_service_zero_id = $file_service_zero_id;
    }

    public function toArray(): array
        {
            return [
                
                'city_in' => $this->city_in,
                'city_out' => $this->city_out,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'start_validity' => $this->start_validity,
                'days_operation' => $this->days_operation,
                'operating_hours' => $this->operating_hours,
                'code_country' => $this->code_country,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'country' => $this->country,
                'file_service_zero_id' => $this->file_service_zero_id,
            ];
        }



    
}
