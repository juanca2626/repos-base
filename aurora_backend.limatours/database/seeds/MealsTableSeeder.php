<?php

use App\Meal;
use App\Http\Traits\Translations;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// @codingStandardsIgnoreLine
class MealsTableSeeder extends Seeder
{
    use Translations;

    /**
     * Run the database seeds.
     *
     * @param Faker $faker
     * @return void
     */
    public function run(Faker $faker)
    {
        if (App::environment('local') === true) {
            $meal = new Meal();
            $meal->save();

            $translation = [
                1 => [
                    'id' => '',
                    'meal_name' => $faker->word
                ],
                2 => [
                    'id' => '',
                    'meal_name' => $faker->word
                ],
                3 => [
                    'id' => '',
                    'meal_name' => $faker->word
                ],
                4 => [
                    'id' => '',
                    'meal_name' => $faker->word
                ]
            ];

            $this->saveTranslation($translation, 'meal', $meal->id);
        }
    }
}
