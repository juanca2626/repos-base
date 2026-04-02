<?php

namespace App\Http\Traits;

use App\Client;
use App\Hotel;

trait CalculateHotelTxesAndServices
{
    use AddFeesPercent;

    /**
     * @param $hotel_id
     * @param $client_id
     * @param float $price
     * @return float
     */
    public function calculateHotelTxesAndServices($hotel_id, $client_id, float $price)
    {
        $hotel = Hotel::find($hotel_id);
        $client = Client::find($client_id);

        $clientType = $client->country_id == $hotel->country->id ? 'local' : 'foreign';

        $total_fees = 0;
        $apply_fees = [];
        foreach ($hotel->taxes as $tax) {
            switch ($tax->type) {
                case 't';
                    $feesTypeFor = $clientType . '_tax';
                    break;
                case 's';
                    $feesTypeFor = $clientType . '_service';
                    break;
                default:
                    continue 2;
            }

            if ($hotel->country->{$feesTypeFor} == '1') {
                $total_fees += $this->addFeesPercent($price, $tax->value);
                $apply_fees[] = $tax;
            }
        }

        return collect([
            'apply_fees' => $apply_fees,
            'type_fees' => 'fees_for_' . $clientType,
            'amount_fees' => $total_fees,
            'amount_before_fees' => $price,
            'amount_after_fees' => $price + $total_fees,
        ]);
    }
}
