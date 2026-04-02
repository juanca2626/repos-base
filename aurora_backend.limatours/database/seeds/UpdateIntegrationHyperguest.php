<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\IntegrationHyperguest;
 
class UpdateIntegrationHyperguest extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        IntegrationHyperguest::where('id',1)->update([
            'email_contact'=>'brenda.correa@hyperguest.com,vgg@limatours.com.pe,kya@limatours.com.pe',
            'commission_amount' => 1.12
        ]);
    }
}
