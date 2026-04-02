<?php

namespace Src\Shared\Domain\Services;
use libphonenumber\PhoneNumberUtil;

trait PhoneService
{
    /**
     * Decode correspondingly the response
     * @param  array $response
     * @return stdClass
     */
    public function phoneNumberFormat($number, $country_iso = null)
    { 
        $number = ltrim($number, "0");
        if (strpos($number, "+") === false)
        {
            $number = "+".$number;
        }

        // dd($number);

        $result = [];

        if($number == '')
        {
            return [
                'phone_code' => '',
                'phone_number' => '',
            ];
        }
        
        try
        {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $phoneNumberObject = $phoneUtil->parse($number, $country_iso);

            $result['phone_code'] = $phoneNumberObject->getCountryCode();
            $result['phone_number'] = $phoneNumberObject->getNationalNumber();
        }
        catch(\Exception $ex)
        {
            $result['phone_code'] = "";
            $result['phone_number'] = $number;
        }
        
        return $result;
    }

  
}
