<?php

use App\Country;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhoneCodeCountryInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
 
        $file_countries = File::get("database/data/country_codes.json");
        $countries = json_decode($file_countries);
        foreach ($countries as $row) {

            $country = Country::where('iso', $row->country)->first();   
            if($country){    
                $country->phone_code = $row->code; 
                $country->save();
            }
            
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
