<?php

namespace App\Http\Traits;

trait AddFeesPercent
{
    public function addFeesPercent(float $price, float $percentFee = 0): float
    {
        $amountFee = 0;
        if ($price > 0) {
            $amountFee = $percentFee > 0 ? ($percentFee / 100) * $price : 0;
        }

        return priceRound($amountFee);
    }
}
