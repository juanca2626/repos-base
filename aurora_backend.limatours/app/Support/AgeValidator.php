<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AgeValidator
{
    public const MIN_ADULT_AGE = 18;
    public const MAX_AGE = 120;

    public function isValidDate($date)
    {
        $isInvalidFormat = Validator::make(
            ['date' => $date],
            ['date' => 'date_format:Y-m-d']
        )->fails();

        if ($isInvalidFormat) {
            return false;
        }

        $tempDateBirth = Carbon::createFromFormat('Y-m-d', $date);

        if ($tempDateBirth->isFuture()) {
            return false;
        }
        
        $diffInYears = Carbon::now()->diffInYears($tempDateBirth);

        return $diffInYears <= self::MAX_AGE;
    }

    public function isAdult($date)
    {
        $tempDateBirth = Carbon::createFromFormat('Y-m-d', $date);

        $diffInYears = Carbon::now()->diffInYears($tempDateBirth);

        return $diffInYears >= self::MIN_ADULT_AGE;
    }

    public function isChild($date)
    {
        $tempDateBirth = Carbon::createFromFormat('Y-m-d', $date);

        $diffInYears = Carbon::now()->diffInYears($tempDateBirth);

        return $diffInYears < self::MIN_ADULT_AGE;
    }
}
