<?php

namespace Src\Modules\File\Presentation\Http\Traits;

trait RoundLito
{
    /**
     * Decode correspondingly the response
     * @param  float $num
     * @param  string $module
     * @return float
     */
    public function roundLito(float $num,string $module=''): float
    { 
        $num = number_format($num, 2, '.', '');
        $res = explode('.', $num);
        $nEntero = $res[0];
        $nDecimal = 0;
        if (count($res) > 1) {
            $nDecimal = (int)$res[1];
        }
        //TODO Si el decimal es menor a 0.10 es igual 0
        if ($nDecimal <= 10) {
            $newDecimal = 0;
        } elseif ($nDecimal > 10 && $nDecimal <= 50) { //TODO si es mayor a 0.10 hasta 0.50 que sea 0.5
            $newDecimal = 5;

            if ($module == 'hotel') {
                $nEntero = ((int)$nEntero) + 1;
                $newDecimal = 0;
            }

        } else { //TODO y de 0.51 hasta 0.99 que sea 1 +
            $nEntero = ((int)$nEntero) + 1;
            $newDecimal = 0;
        }
        $numeroRed = $nEntero . '.' . $newDecimal;
        return $numeroRed;
    }  
}
