<?php

namespace Src\Modules\File\Domain\Model;

use Src\Shared\Domain\Entity;

class ServiceZero
{

    public string $time;
    public string $type;
    public string $privacy;
    public string $name;
    public string $skeleton;
    public string $itinerary;
    public string $supplier_code;
    public string $supplier;
    public string $created_at; 
    public string $updated_at; 
    public string $status; 
       
    public function __construct(
       
         string $time, 
         string $type, 
         string $privacy, 
         string $name, 
         $status, 
         $skeleton, 
         string $itinerary,
         $supplier_code,
         $supplier, 
         $created_at, 
         //$updated_at
         )
    {
        
        $this->time = $time;
        $this->type = $type;
        $this->privacy = $privacy;
        $this->name = $name;
        $this->skeleton = $skeleton ?? '';
        $this->itinerary = $itinerary;
        $this->supplier_code = $supplier_code ?? '';
        $this->supplier = $supplier ?? '';
        $this->created_at = $created_at ?? ''; // Asigna los valores
        //$this->updated_at = $updated_at ?? ''; // Asigna los valores
        $this->status = $status ?? 'pending'; // Asigna los valores
    }

    public function toArray(): array
        {
            return [
                
                'time' => $this->time,
                'type' => $this->type,
                'privacy' => $this->privacy,
                'name' => $this->name,
                'skeleton' => $this->skeleton,
                'itinerary' => $this->itinerary,
                'supplier_code' => $this->supplier_code,
                'supplier' => $this->supplier,
                'created_at' => $this->created_at,
                //'updated_at' => $this->updated_at,
                'status' => $this->status,
            ];
        }

    public function setId(int $id): void
    {
            $this->id = $id;
    }
    public function getTime()
{
    return $this->time;
}


    
}