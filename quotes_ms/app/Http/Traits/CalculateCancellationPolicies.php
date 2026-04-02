<?php

namespace App\Http\Traits;

use Carbon\Carbon;

trait CalculateCancellationPolicies
{
    // TODO: crear inferfas para hacer administrables los mensajes de la cancelacion $message
    public $mensajes_penalties = [
        'es' => [
            'NOT_REFOUNDABLE'        => 'La tarifa no tiene reembolso por cancelaciones',
            'MESSAGE_PENALTY'        => 'Puede cancelar sin penalidad hasta el [APPLY_DATE_FLAG]. Después de la fecha indicada se aplica un cargo de USD [PRICE_FLAG]', // 'Desde el [APPLY_DATE_FLAG] se aplica un cargo de USD [PRICE_FLAG]',
            'MESSAGE_IN_PENALTY'     => 'Puede cancelar sin penalidad hasta el [APPLY_DATE_FLAG]. Después de la fecha indicada se aplica un cargo de USD [PRICE_FLAG]', // 'Puede cancelar con un cargo de USD [PRICE_FLAG]',
            'MESSAGE_BEFORE_PENALTY' => 'Puede cancelar sin penalidad hasta el [APPLY_DATE_FLAG]. Después de la fecha indicada se aplica un cargo de USD [PRICE_FLAG]',
        ],
        'en' => [
            'NOT_REFOUNDABLE'        => 'Rate has no refound for cancelations',
            'MESSAGE_PENALTY'        => 'You have until the [APPLY_DATE_FLAG] to cancel without penalties, After that date will be charged USD [PRICE_FLAG]', // 'From [APPLY_DATE_FLAG] can by cancel with a penalty of USD [PRICE_FLAG]',
            'MESSAGE_IN_PENALTY'     => 'You have until the [APPLY_DATE_FLAG] to cancel without penalties, After that date will be charged USD [PRICE_FLAG]', // 'Can by cancel with a penalty of USD [PRICE_FLAG]',
            'MESSAGE_BEFORE_PENALTY' => 'You have until the [APPLY_DATE_FLAG] to cancel without penalties, After that date will be charged USD [PRICE_FLAG]',
        ],
        'pt' => [
            'NOT_REFOUNDABLE'        => 'Taxa não tem reembolso para cancelamentos',
            'MESSAGE_PENALTY'        => 'Você tem até [APPLY_DATE_FLAG] para cancelar sem penalidades. Após essa data, será cobrado em USD [PRICE_FLAG]', //'De [APPLY_DATE_FLAG] pode ser cancelado com uma multa de USD [PRICE_FLAG]',
            'MESSAGE_IN_PENALTY'     => 'Você tem até [APPLY_DATE_FLAG] para cancelar sem penalidades. Após essa data, será cobrado em USD [PRICE_FLAG]', //'Pode ser cancelado com uma multa de USD [PRICE_FLAG]',
            'MESSAGE_BEFORE_PENALTY' => 'Você tem até [APPLY_DATE_FLAG] para cancelar sem penalidades. Após essa data, será cobrado em USD [PRICE_FLAG]',
        ]
    ];

    public $REPACE_FLAGS = [
        '[PRICE_FLAG]',
        '[DAYS_REMAIN_FLAG]',
        '[APPLY_DATE_FLAG]',
        '[EXPIRE_DATE_FLAG]'
    ];

    /**
     * @param $selected_policies_cancelation
     * @param $guest_quantity
     * @param $rooms_quantity
     * @return array
     */
    public function getCancellationPolicyByTypeFit($selected_policies_cancelation, $guest_quantity, $rooms_quantity)
    {
        if (isset($selected_policies_cancelation['code']) and $selected_policies_cancelation['code'] == 'CANCELLATION_POLICY_CHANNELS') {
            $selected_policies_cancelation = collect([$selected_policies_cancelation]);
        }
        // se le da prioridad a los tipo pasajero
        $selected_policy_cancelation = $selected_policies_cancelation->first(function ($item) use ($guest_quantity) {
            // type_fit == 1 | PAX
            return ($item['type_fit'] == '1') and
                ($item['min_num'] <= $guest_quantity and $item['max_num'] >= $guest_quantity);
        });

        // si no se encuentra una cancelacion tipo pasajeros se busca tipo room
        if (!$selected_policy_cancelation) {
            $selected_policy_cancelation = $selected_policies_cancelation->first(function ($item) use (
                $rooms_quantity
            ) {
                // type_fit == 2 | ROOM
                return ($item['type_fit'] == '2') and
                    ($item['min_num'] <= $rooms_quantity and $item['max_num'] >= $rooms_quantity);
            });
        }

        // si no se encuentra una cancelacion tipo fit buscamos la orimera que se pueda usar en cualquiera
        if (!$selected_policy_cancelation) {
            $selected_policy_cancelation = $selected_policies_cancelation->first(function ($item) use (
                $rooms_quantity
            ) {
                return $item['type_fit'] != '1' and $item['type_fit'] != '2';
            });
        }

        return $selected_policy_cancelation;
    }

    /**
     * @param Carbon $current_date
     * @param Carbon $check_in
     * @param Carbon $check_out
     * @param float $total_amount_rate
     * @param $selected_policies_cancelation
     * @param $guest_quantity
     * @param $rooms_quantity
     * @return array
     */
    public function calculateCancellationPolicies(
        Carbon $current_date,
        Carbon $check_in,
        Carbon $check_out,
        float $total_amount_rate,
        $_selected_policies_cancelation,
        $guest_quantity,
        $rooms_quantity
    ) {
        $selected_policy_cancelation = $this->getCancellationPolicyByTypeFit(
            $_selected_policies_cancelation,
            $guest_quantity,
            $rooms_quantity
        );

        $resevation_days = difDateDays($check_in, $check_out);
        $days_before_check_in = difDateDays($check_in, $current_date);
        $argsPenalty = collect();
        if (!empty($selected_policy_cancelation['policy_cancellation_parameter'])) {
            $argsPenalty = collect($selected_policy_cancelation['policy_cancellation_parameter']);
        }

        // buscar el registro de NOT_REFOUNDABLE que es "min_day" = 0 and "max_day" = 0
        $NOT_REFOUNDABLE = $argsPenalty->first(function ($penalty) {
            return $penalty["min_day"] == 0 and $penalty["max_day"] == 0 and strtoupper($penalty["penalty"]["name"]) == 'TOTAL_RESERVATION';
        });

        // si encontramos un registro de NOT_REFOUNDABLE lo sacamos el array principal
        if ($NOT_REFOUNDABLE) {
            $NOT_REFOUNDABLE["penalty"]["name"] = 'NOT_REFOUNDABLE';
            $argsPenalty = collect([$NOT_REFOUNDABLE]);
        } else {
            $argsPenalty = $argsPenalty->reject(function ($penalty) use ($days_before_check_in) {
                if ($penalty["max_day"] > $days_before_check_in) {
                    // Si $days_before_check_in es menor a "max_day" la penalidad ya se paso
                    // y debemos sacarlo del array principal
                    return true;
                }

                return false;
            });
        }

        // print_r($argsPenalty); die;

        $apply_panties = collect();
        foreach ($argsPenalty as $policies_rate) {
            $penalty_code = strtoupper($policies_rate["penalty"]["name"]);

            // si min_day es cero y max_day es mayos a cero entonces el primer dia de penalidad se calcula con max_day
            if ($policies_rate["min_day"] == 0 and $policies_rate["max_day"] > 0) {
                // ultimo dia de la penalidad
                $apply_date = $policies_rate["max_day"] == 0 ? $check_in : subDateDays(
                    $check_in,
                    $policies_rate["max_day"]
                );

                // ultimo dia de la penalidad
                $expire_date = $check_in;

            } elseif ($policies_rate["min_day"] == 0 and $policies_rate["max_day"] == 0) {
                // primer dia de la penalidad
                $apply_date = $current_date;

                // ultimo dia de la penalidad
                $expire_date = $check_in;

            } else {
                // primer dia de la penalidad
                $apply_date = $policies_rate["min_day"] == 0 ? $current_date : subDateDays(
                    $check_in,
                    $policies_rate["min_day"]
                );
                if ($apply_date->isBefore($current_date)) {
                    $apply_date = $current_date->copy();
                }

                // ultimo dia de la penalidad
                $expire_date = $policies_rate["max_day"] == 0 ? $check_in : subDateDays(
                    $check_in,
                    $policies_rate["max_day"]
                );
            }

            if ($apply_date->isBefore($current_date)) {
                $apply_date = $current_date->copy();
            }

            // calcular cuandos dias quedan antes de la penalidad
            $days_remaing = difDateDays($current_date, $apply_date);
            $days_remain = $days_remaing < 1 ? 0 : (int)$days_remaing;

            if ($penalty_code == 'NOT_REFOUNDABLE') {
                $penalty_price = $total_amount_rate;
                $lang_iso = \Config::get('app.locale');
                $message = $this->mensajes_penalties[$lang_iso]['NOT_REFOUNDABLE'];
            } else {
                // Calcular el importe de la penalidad
                switch ($penalty_code) {
                    case "NIGTH":
                        $penalty_price = ($total_amount_rate / $resevation_days) * $policies_rate["amount"];
                        break;
                    case "PERCENTAGE":
                        $penalty_price = pricePercent($total_amount_rate, $policies_rate["amount"]);
                        break;
                    case "TOTAL_RESERVATION":
                        $penalty_price = $total_amount_rate;
                        break;
                }

                $message = $this->getMassagePenalty(
                    $penalty_price,
                    $days_remain,
                    $apply_date->format('d-m-Y'),
                    $expire_date->format('d-m-Y')
                );
            }

            $apply_panties->add(collect([
                'from'                => $policies_rate["min_day"],
                'to'                  => $policies_rate["max_day"],
                'apply_date'          => $apply_date->format('d-m-Y'),
                'expire_date'         => $expire_date->format('d-m-Y'),
                'days_remain'         => $days_remain,
                'penalty_price'       => priceRound($penalty_price),
                'penalty_code'        => $penalty_code,
                'message'             => $message,
                'policies_found'      => 1,
                'days_before_checkin' => $days_before_check_in
            ]));
        }

        // si no se encontraron politicas que coincidan, se aplica politica NOT_REFOUNDABLE
        if ($apply_panties->count() == 0) {
            $apply_panties->add(collect([
                'selected_policy_cancelation_prev' => $_selected_policies_cancelation,
                'selected_policy_cancelation'      => $selected_policy_cancelation,
                'from'                             => 0,
                'to'                               => 0,
                'apply_date'                       => $current_date->format('d-m-Y'),
                'expire_date'                      => $check_in->format('d-m-Y'),
                'days_remain'                      => 0,
                'penalty_price'                    => priceRound($total_amount_rate),
                'penalty_code'                     => 'NOT_REFOUNDABLE',
                'message'                          => $this->getMassageNotRefoundable(
                    $total_amount_rate,
                    0,
                    $current_date->format('d-m-Y'),
                    $check_in->format('d-m-Y')
                ),
                'policies_found'      => 0,
                'days_before_checkin' => $days_before_check_in
            ]));
        } else {
            $apply_panties = $apply_panties->sortBy('days_remain');
        }

        $result = [
            'selected_policy_cancelation' => $selected_policy_cancelation,
            'next_penalty'                => collect($apply_panties->first()),
            'penalties'                   => $apply_panties->values()
        ];

        if ($result['next_penalty']['message'] != 'NOT_REFOUNDABLE') {
            if ($result['next_penalty']['days_remain'] == 0) {
                $result['next_penalty']['message'] = $this->getMassageInPenalty(
                    $result['next_penalty']['penalty_price'],
                    $result['next_penalty']['days_remain'],
                    $result['next_penalty']['apply_date'],
                    $result['next_penalty']['expire_date']
                );
            } else {
                $result['next_penalty']['message'] = $this->getMassageBeforePenalty(
                    $result['next_penalty']['penalty_price'],
                    $result['next_penalty']['days_remain'],
                    $result['next_penalty']['apply_date'],
                    $result['next_penalty']['expire_date']
                );
            }
        }

        return $result;
    }

    /**
     * @param $penalty_price
     * @param $days_remain
     * @param $apply_date
     * @param $expire_date
     * @return mixed
     */
    public function getMassageNotRefoundable($penalty_price, $days_remain, $apply_date, $expire_date)
    {
        $lang_iso = \Config::get('app.locale');

        return str_replace(
            $this->REPACE_FLAGS,
            [
                $penalty_price,
                $days_remain,
                $apply_date,
                $expire_date
            ],
            $this->mensajes_penalties[$lang_iso]['NOT_REFOUNDABLE']
        );
    }

    /**
     * @param $penalty_price
     * @param $days_remain
     * @param $apply_date
     * @param $expire_date
     * @return mixed
     */
    public function getMassageInPenalty($penalty_price, $days_remain, $apply_date, $expire_date)
    {
        $lang_iso = \Config::get('app.locale');

        return str_replace(
            $this->REPACE_FLAGS,
            [
                $penalty_price,
                $days_remain,
                $apply_date,
                $expire_date
            ],
            $this->mensajes_penalties[$lang_iso]['MESSAGE_IN_PENALTY']
        );
    }

    /**
     * @param $penalty_price
     * @param $days_remain
     * @param $apply_date
     * @param $expire_date
     * @return mixed
     */
    public function getMassagePenalty($penalty_price, $days_remain, $apply_date, $expire_date)
    {
        $lang_iso = \Config::get('app.locale');

        return str_replace(
            $this->REPACE_FLAGS,
            [
                $penalty_price,
                $days_remain,
                $apply_date,
                $expire_date
            ],
            $this->mensajes_penalties[$lang_iso]['MESSAGE_PENALTY']
        );
    }

    /**
     * @param $penalty_price
     * @param $days_remain
     * @param $apply_date
     * @param $expire_date
     * @return mixed
     */
    public function getMassageBeforePenalty($penalty_price, $days_remain, $apply_date, $expire_date)
    {
        $lang_iso = \Config::get('app.locale');

        return str_replace(
            $this->REPACE_FLAGS,
            [
                $penalty_price,
                $days_remain,
                $apply_date,
                $expire_date
            ],
            $this->mensajes_penalties[$lang_iso]['MESSAGE_BEFORE_PENALTY']
        );
    }


    public function calculateCancellationPoliciesServices(
        Carbon $current_date,
        Carbon $check_in,
        float $total_amount_rate,
        $selected_policies_cancelation,
        $quantity_persons,
        Carbon $service_start_time
    ) {
        $apply_politics = collect();
        $selected_policy_cancelation = $this->getCancellationPolicyByTypeFitService(
            $selected_policies_cancelation,
            $quantity_persons
        );
        $argsPenalty = collect($selected_policy_cancelation);
        $argsPenalty = $argsPenalty->filter(function ($penalty) use ($current_date, $service_start_time) {
            if ($penalty["unit_duration"] === 1) { //Horas
                $days_before_check_in = difDateHours($current_date, $service_start_time);
                if (($penalty["from"] <= (int)$days_before_check_in and $penalty["to"] >= (int)$days_before_check_in)) {
                    return $penalty;
                }

            } elseif ($penalty["unit_duration"] === 2) { //Dias
                $days_before_check_in = difDateDays($current_date, $service_start_time);
                if (($penalty["from"] <= (int)$days_before_check_in and $penalty["to"] >= (int)$days_before_check_in)) {
                    return $penalty;
                }
            }
        });

        $penalty_price = 0;
        $apply_date = $check_in;
        $expire_date = $check_in;
        foreach ($argsPenalty as $policies_rate) {
            if ($apply_date->isBefore($current_date)) {
                $apply_date = $current_date->copy();
            }

            // calcular cuandos dias quedan antes de la penalidad
            if ($policies_rate["unit_duration"] === 1) { //Horas
                $days_remaing = difDateHours($current_date, $apply_date);
                $days_remain = $days_remaing < 1 ? 0 : (int)$days_remaing;

            } elseif ($policies_rate["unit_duration"] === 2) { //Dias
                $days_remaing = difDateDays($current_date, $apply_date);
                $days_remain = $days_remaing < 1 ? 0 : (int)$days_remaing;
            }


            $penalty_code = strtoupper($policies_rate["penalty_name"]);
            switch ($penalty_code) {
                case "PAX":
                    $penalty_price = priceRound($total_amount_rate);
                    //                  $penalty_price = ($total_amount_rate / $resevation_days) * $policies_rate["amount"];
                    break;
                case "PERCENTAGE":
                    $penalty_price = pricePercent($total_amount_rate, $policies_rate["amount"]);
                    break;
                case "AMOUNT":
                    $penalty_price = $policies_rate["amount"];
                    break;
            }

            $message = $this->getMassagePenaltyService($penalty_price, $days_remain, $apply_date);
            $apply_politics->add(collect([
                'from'          => $policies_rate["from"],
                'to'            => $policies_rate["to"],
                'min_num'       => $policies_rate["min_num"],
                'max_num'       => $policies_rate["max_num"],
                'unit_duration' => ($policies_rate["unit_duration"] === 1) ? 'hour' : 'day',
                'apply_date'    => $apply_date->format('d-m-Y H:i'),
                'expire_date'   => $expire_date->format('d-m-Y H:i'),
                'days_remain'   => $days_remain,
                'penalty_price' => priceRound($penalty_price),
                'penalty_code'  => $penalty_code,
                'message'       => $message,
            ]));

        }

        if ($apply_politics->count() == 0) {
            $apply_politics->add(collect([
                'from'          => 0,
                'to'            => 0,
                'min_num'       => 0,
                'max_num'       => 0,
                'unit_duration' => 0,
                'apply_date'    => $current_date->format('d-m-Y'),
                'expire_date'   => $expire_date->format('d-m-Y'),
                'days_remain'   => 0,
                'penalty_price' => priceRound($total_amount_rate),
                'penalty_code'  => 'NOT',
                'message'       => $this->getMassageNotRefoundable(
                    $total_amount_rate,
                    0,
                    $current_date->format('d-m-Y'),
                    $check_in->format('d-m-Y')
                ),
            ]));
        }

        $result = [
            'penalties' => $apply_politics->values()
        ];

        return $result;
    }

    public function getCancellationPolicyByTypeFitService($selected_policies_cancelation, $quantity_persons)
    {

        // se le da prioridad a los tipo pasajero
        $selected_policy_cancelation = $selected_policies_cancelation->filter(function ($item, $key) use (
            $quantity_persons
        ) {
            // type_fit == 1 | PAX
            return ($item['min_num'] <= $quantity_persons and $item['max_num'] >= $quantity_persons);
        });

        // si no se encuentra una cancelacion tipo pasajeros se devuelve todas
        if ($selected_policy_cancelation->count() == 0) {
            $selected_policy_cancelation = $selected_policies_cancelation;
        }

        return $selected_policy_cancelation;
    }

    public function getMassagePenaltyService($penalty_price, $days_remain, $apply_date, $expire_date = '')
    {
        $lang_iso = \Config::get('app.locale');

        return str_replace(
            $this->REPACE_FLAGS,
            [
                $penalty_price,
                $days_remain,
                $apply_date,
                $expire_date
            ],
            $this->mensajes_penalties[$lang_iso]['MESSAGE_PENALTY']
        );
    }

    public function getMassageInPenaltyService($penalty_price, $days_remain, $apply_date, $expire_date)
    {
        $lang_iso = \Config::get('app.locale');

        return str_replace(
            $this->REPACE_FLAGS,
            [
                $penalty_price,
                $days_remain,
                $apply_date,
                $expire_date
            ],
            $this->mensajes_penalties[$lang_iso]['MESSAGE_IN_PENALTY']
        );
    }
}
