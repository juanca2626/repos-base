<?php

use Carbon\Carbon;

if (! function_exists('priceRound')) {
    function priceRound(float $value, int $decimals = 2): float
    {
        return number_format(round($value, $decimals), $decimals);
    }
}

if (! function_exists('roundLito')) {
    /**
     * Redondeo de precios Lito
     */
    function roundLito(float $num, string $module = ''): string
    {
        $num = number_format($num, 2, '.', '');
        $res = explode('.', $num);
        $nEntero = $res[0];
        $nDecimal = 0;
        if (count($res) > 1) {
            $nDecimal = (int) $res[1];
        }
        //TODO Si el decimal es menor a 0.10 es igual 0
        if ($nDecimal <= 10) {
            $newDecimal = 0;
        } elseif ($nDecimal <= 50) { //TODO si es mayor a 0.10 hasta 0.50 que sea 0.5
            $newDecimal = 5;

            if ($module == 'hotel') {
                $nEntero = ((int) $nEntero) + 1;
                $newDecimal = 0;
            }

        } else { //TODO y de 0.51 hasta 0.99 que sea 1 +
            $nEntero = ((int) $nEntero) + 1;
            $newDecimal = 0;
        }

        return $nEntero.'.'.$newDecimal;
    }
}

if (! function_exists('convertDate')) {
    function convertDate($_date, $char_from, $char_to, bool $orientation): string
    {
        $explode = explode($char_from, $_date);

        return ($orientation)
            ? $explode[2].$char_to.$explode[1].$char_to.$explode[0]
            : $explode[0].$char_to.$explode[1].$char_to.$explode[2];
    }
}

if (!function_exists('clearNameRoom')) {
    /**
     * @param $stateIso
     * @return string
     */
    function clearNameRoom($name)
    {
        $name = str_replace("SGL", "", $name);
        $name = str_replace("DBL", "", $name);
        $name = str_replace("TPL", "", $name);
        $name = str_replace("MAT", "", $name);
        $name = str_replace("MATRIMONIAL", "", $name);
        $name = str_replace("+ CAMA ADICIONAL", "", $name);
        $name = str_replace("+ ADD BED", "", $name);
        $name = str_replace("+ SOFA CAMA", "", $name);
        $name = str_replace("TRP", "", $name);
        $name = str_replace("+ CAMA ADD", "", $name);
        $name = str_replace("SIMPLE", "", $name);

        return  $name;
    }
}

if (!function_exists('difDateDays')) {
    /**
     * @param  Carbon  $date1
     * @param  Carbon  $date2
     * @return string
     */
    function difDateDays(Carbon $date1, Carbon $date2)
    {
        return (clone $date1)->diff($date2)->format('%a');
    }
}

if (!function_exists('subDateDays')) {
    /**
     * @param  Carbon  $date
     * @param  int  $amount
     * @return \DateTime
     */
    function subDateDays(Carbon $date, int $amount)
    {
        return (clone $date)->subDays($amount);
    }
}

if (!function_exists('pricePercent')) {
    /**
     * @param  float  $price
     * @param  float  $percent
     * @return float
     */
    function pricePercent(float $price, float $percent)
    {
        return ($price * ($percent / 100));
    }
}
