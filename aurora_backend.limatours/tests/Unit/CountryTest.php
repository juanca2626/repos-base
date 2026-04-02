<?php

namespace Tests\Unit;

use App\Country;
use App\Translation;
use App\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class CountryTest extends TestCase
{
    use ActingAs, RefreshDatabase;

    /**
     * Country controller can List
     *
     * @return void
     */
    public function testIndex()
    {
        $user = factory(User::class)->create();

        $faker = Faker::create();
        $country = factory(Country::class)->create();
        $country_name = $faker->country;

        factory(Translation::class)->create([
            'type' => 'country',
            'object_id' => $country->id,
            'slug' => 'country_name',
            'value' => $country_name,
            'language_id' => 1,
            'created_at' => Carbon::now('America/Lima')->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now('America/Lima')->format('Y-m-d H:i:s')
        ]);

        $response = $this->actingAs($user)->json('GET', '/api/countries?lang=es');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

//    public function testStore()
//    {
//        $user = factory(User::class)->create();
//
//        $newCountry = [
//            'name' => $this->faker->country,
//        ];
//
//        $response = $this->actingAs($user)
//            ->json(
//                'POST',
//                '/api/countries',
//                $newCountry
//            );
//
//        $response->assertStatus(200)
//            ->assertJson([
//                'success' => true
//            ]);
//
//        $this->assertDatabaseHas('countries', ['name' => $newCountry['name']]);
//    }
//
//    public function testShow()
//    {
//        $user = factory(User::class)->create();
//
//        $country = factory(Country::class)->create();
//
//        $response = $this->actingAs($user)
//            ->json(
//                'GET',
//                '/api/countries/1'
//            );
//
//        $response->assertStatus(200)
//            ->assertJson([
//                'success' => true,
//                'data' => [
//                    'id' => 1,
//                    'name' => $country->name
//                ]
//            ]);
//    }
//
//    public function testUpdateWithName()
//    {
//        $user = factory(User::class)->create();
//
//        factory(Country::class)->create();
//
//        $newCountry = [
//            'name' => $this->faker->country
//        ];
//
//        $response = $this->actingAs($user)
//            ->json(
//                'PUT',
//                '/api/countries/1',
//                $newCountry
//            );
//
//        $response->assertStatus(200)
//            ->assertJson([
//                'success' => true
//            ]);
//
//        $this->assertDatabaseHas('countries', ['name' => $newCountry['name']]);
//    }
//
//    public function testDelete()
//    {
//        $user = factory(User::class)->create();
//
//        $country = factory(Country::class)->create();
//
//        $response = $this->actingAs($user)
//            ->json(
//                'DELETE',
//                '/api/countries/1'
//            );
//        $response->assertStatus(200)
//            ->assertJson([
//                'success' => true
//            ]);
//
//        $this->assertSoftDeleted('countries', ['id' => $country->id]);
//    }
}
