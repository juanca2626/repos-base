<?php

namespace App\Http\Traits;

trait AddFeesPercent
{
    /**
     * @param float $price
     * @param float $percentFee
     * @return float
     */
    public function addFeesPercent(float $price, float $percentFee = 0)
    {
        $amountFee = 0;
        if ($price > 0) {
            $amountFee = $percentFee > 0 ? ($percentFee / 100) * $price : 0;
        }

        return priceRound($amountFee);
    }
}
