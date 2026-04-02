<?php


namespace App\Http\Traits;


use Illuminate\Http\Request;
use Exception;

trait Currencies
{
    public function convert_currency($amount, $exchage_rate)
    {
        return priceRound($amount * $exchage_rate,2);
    }
}
