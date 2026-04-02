<?php

namespace App\Http\Traits;

use App\Client;
use App\Hotel;
use App\RatesPlans;

trait AddHotelRateTaxesAndServices
{

    public $client_procedence_type = null;

    /**
     * @param array $hotel
     * @param Client $client
     * @param RatesPlans $ratePlans
     * @param float $price
     * @return array
     */
    public function getHotelApplicableFees($client, $hotel)
    {
        // Filtar lo Texes and services que seran aplicadon al hotel segun si el cliente es local o extranjero
        $this->client_procedence_type = (!$client['country_id'] or ($client['country_id'] == $hotel['country_id'])) ? 'local' : 'foreign';
        $applicable_fees = [];
        foreach ($hotel['taxes'] as $tax) {
            if ($tax['type'] == 't' and $hotel['country'][$this->client_procedence_type . '_tax'] == '1') {
                $applicable_fees['t'][] = $tax;
            }

            if ($tax['type'] == 's' and $hotel['country'][$this->client_procedence_type . '_service'] == '1') {
                $applicable_fees['s'][] = $tax;
            }
        }

        return $applicable_fees;
    }

    /**
     * @param $applicable_fees
     * @param $rate_plan
     * @param float $price
     * @return float
     */
    public function addHotelExtraFees($applicable_fees, $rate_plan, float $price)
    {
        // Add extra fees
        $extra_fees = 0;
        $apply_fees = [];

        if (isset($applicable_fees['t']) and $rate_plan['taxes']) {
            foreach ($applicable_fees['t'] as $tax) {
                $tax['total_amount'] = pricePercent($price, $tax['pivot']['amount']);

                $extra_fees += $tax['total_amount'];

                $apply_fees['t'][] = $tax;
            }
        }
        if (isset($applicable_fees['s']) and $rate_plan['services']) {
            foreach ($applicable_fees['s'] as $service) {
                $service['total_amount'] = pricePercent($price, $service['pivot']['amount']);

                $extra_fees += $service['total_amount'];

                $apply_fees['s'][] = $service;
            }
        }

        return collect([
            'apply_fees' => $apply_fees,
            'type_fees' => 'fees_for_' . $this->client_procedence_type,
            'amount_fees' => $extra_fees,
            'amount_before_fees' => $price,
            'amount_after_fees' => $price + $extra_fees,
        ]);
    }

    /**
     * @param array $hotel
     * @param array $client
     * @param array $ratePlans
     * @param float $price
     * @return float
     */
    public function addHotelRateTaxesAndServices(array $hotel, array $client, array $ratePlans, float $price)
    {
        $clientType = (!$client['country_id'] or ($client['country_id'] == $hotel['country_id'])) ? 'local' : 'foreign';

        $total_fees = 0;
        $apply_fees = [];
        foreach ($hotel['taxes'] as $tax) {
            switch ($tax['type']) {
                case 't':
                    if ($hotel['country'][$clientType . '_tax'] == '1' and $ratePlans['taxes']) {
                        $total_fees += pricePercent($price, $tax['pivot']['amount']);
                        $apply_fees[] = $tax;
                    }

                    break;
                case 's':
                    if ($hotel['country'][$clientType . '_service'] == '1' and $ratePlans['services']) {
                        $total_fees += pricePercent($price, $tax['pivot']['amount']);
                        $apply_fees[] = $tax;
                    }
                    break;
                default:
                    continue 2;
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

    /**
     * @param array $hotel
     * @param Client $client
     * @param RatesPlans $ratePlans
     * @param float $price
     * @return float
     */
    public function addHotelRateTaxesAndServicesNoClient(Hotel $hotel, RatesPlans $ratePlans, float $price = null)
    {
        if (!$price) {
            return $price;
        }
        $total_fees = 0;
        $apply_fees = [];
        foreach ($hotel['taxes'] as $tax) {
            switch ($tax['type']) {
                case 't':
                    if ($ratePlans['taxes']) {
                        $total_fees += pricePercent($price, $tax['pivot']['amount']);
                        $apply_fees[] = $tax;
                    }

                    break;
                case 's':
                    if ($ratePlans['services']) {
                        $total_fees += pricePercent($price, $tax['pivot']['amount']);
                        $apply_fees[] = $tax;
                    }
                    break;
                default:
                    continue 2;
            }
        }

        return $price + $total_fees;
    }
}
