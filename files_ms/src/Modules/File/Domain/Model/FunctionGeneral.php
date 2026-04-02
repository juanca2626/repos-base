<?php

namespace Src\Modules\File\Domain\Model;

use Src\Shared\Domain\Entity;
use Carbon\Carbon;

final class FunctionGeneral extends Entity
{
    public function __construct(
    ){

    }

    /**
     * @param Carbon $date1
     * @param Carbon $date2
     * @return string
     */    
    function difDateHours(Carbon $date1, Carbon $date2)
    {
        return (clone $date1)->diffInHours($date2);
    }

    /**
     * @param Carbon $date1
     * @param Carbon $date2
     * @return string
     */
    function difDateDays(Carbon $date1, Carbon $date2)
    {
        return (clone $date1)->diff($date2)->format('%a');
    }


    /**
     * @param float $value
     * @param int $decimals
     * @return float
     */
    function priceRound(float $value, int $decimals = 2)
    {
        return number_format(round($value, $decimals), $decimals);
    }

    /**
     * @param float $price
     * @param float $percent
     * @return float
     */
    function pricePercent(float $price, float $percent)
    {
        return ($price * ($percent / 100));
    }


    /**
     * @return array
     */
    public function toArray(): array
    {
        return [];
    }
}