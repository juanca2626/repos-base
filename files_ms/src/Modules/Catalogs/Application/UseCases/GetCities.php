<?php

namespace Src\Modules\Catalogs\Application\UseCases;

use Src\Modules\Catalogs\Domain\Readers\CityReader;  
use Src\Shared\Logging\Traits\LogsDomainEvents; 

class GetCities
{
    use LogsDomainEvents;

    public function __construct(
       private CityReader $cityReader
    ) {}

    public function execute(array $city_isos): array
    {    
        $cities = $this->cityReader->all($city_isos);         
        return $cities;        
    }
}

 