<?php
namespace Src\Modules\File\Domain\Model;

use Src\Shared\Domain\Entity;

class RatesServiceZero
{

    public string $type_passenger;
    public string $passenger_range_min;
    public string $passenger_range_max;
    public string $net_cost;
    public string $service_tax;
    public string $general_tax;
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
       
         string $type_passenger,
         string $passenger_range_min,
         string $passenger_range_max,
         string $net_cost,
         string $service_tax,
         string $general_tax,
         string $file_service_zero_id
         )
    {
        
        $this->type_passenger = $type_passenger;
        $this->passenger_range_min = $passenger_range_min;
        $this->passenger_range_max = $passenger_range_max;
        $this->net_cost = $net_cost;
        $this->service_tax = $service_tax;
        $this->general_tax = $general_tax;
        $this->file_service_zero_id = $file_service_zero_id;
    }

    public function toArray(): array
        {
            return [
                
                'type_passenger' => $this->type_passenger,
                'passenger_range_min' => $this->passenger_range_min,
                'passenger_range_max' => $this->passenger_range_max,
                'net_cost' => $this->net_cost,
                'service_tax' => $this->service_tax,
                'general_tax' => $this->general_tax,
                'file_service_zero_id' => $this->file_service_zero_id,
            ];
        }
}