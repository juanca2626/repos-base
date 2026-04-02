<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Model::unguard();
        $this->call(FileClassificationTableSeeder::class);
        $this->call(TypeComponentServiceTableSeeder::class);
        $this->call(TypeCompositionTableSeeder::class);
        $this->call(VipsTableSeeder::class);
        $this->call(FileAmountReasonTableSeeder::class);
        $this->call(FileAmountTypeFlagTableSeeder::class);
        $this->call(LanguageTableSeeder::class);
        $this->call(StatusReasonTableSeeder::class);     
        $this->call(CategoryTableSeeder::class);                
        $this->call(FileReasonStatementTableSeeder::class);  
        $this->call(ServiceTimeSeeder::class);
    }
}
