<?php

namespace Src\Modules\File\Presentation\Http\Traits;

trait CalculateProfitability
{
    /**
     * Decode correspondingly the response
     * @param  array $response
     * @return stdClass
     */
    public function calculateProfitability($totalSale,$totalCost)
    { 
        $profitability = 0;
        if($totalCost > 0){
            $profitability = round((($totalSale - $totalCost ) / $totalCost) * 100, 2);
        }    
        return $profitability;
    }

  
}
