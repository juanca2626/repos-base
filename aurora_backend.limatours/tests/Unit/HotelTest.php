<?php

namespace Tests\Unit;

use App\Chain;
use App\Category;
use App\City;
use App\Country;
use App\Currency;
use App\District;
use App\Hotel;
use App\HotelType;
use App\State;
use App\TypeClass;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class HotelTest extends TestCase
{
    use ActingAs, RefreshDatabase;

    /**
     * State controller can List
     *
     * @return void
     */
    public function testIndex()
    {

        $user = factory(User::class)->create();

        factory(Hotel::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/hotels');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    public function testStore()
    {
        $user = factory(User::class)->create();

        $country = factory(Country::class)->create();
        $state = factory(State::class)->create();
        $city = factory(City::class)->create();
        $district = factory(District::class)->create();
        $chain = factory(Chain::class)->create();
        $category = factory(Category::class)->create();
        $currency = factory(Currency::class)->create();
        $hoteltype = factory(HotelType::class)->create();
        $typesclass = factory(TypeClass::class)->create();

        $newHotel = [
            'name' => $this->faker->name . ' ' . $this->faker->randomElement(['Hotel', 'Resort', 'Hotel & Spa']),
            'address' => $this->faker->address,
            'stars' => $this->faker->randomElement(['1', '2', '3', '4', '5']),
            'aurora_code' => $this->faker->swiftBicNumber,
            'web_site' => $this->faker->url,
            'status' => 1,
            'latitude' => $this->faker->latitude(-90, 90),
            'longitude' => $this->faker->longitude(-180, 180),
            'check_in_time' => $this->faker->time('H:i:s', 'now'),
            'check_out_time' => $this->faker->time('H:i:s', 'now'),
            'percentage_completion' => 0,
            'typeclass_id' => $typesclass->id,
            'chain_id' => $chain->id,
            'currency_id' => $currency->id,
            'hotel_type_id' => $hoteltype->id,
            'category_id' => $category->id,
            'country_id' => $country->id,
            'state_id' => $state->id,
            'city_id' => $city->id,
            'district_id' => $district->id
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/hotels',
                $newHotel
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('hotels', [
            'name' => $newHotel['name'],
            'address' => $newHotel['address'],
            'stars' => $newHotel['stars'],
            'aurora_code' => $newHotel['aurora_code'],
            'web_site' => $newHotel['web_site'],
            'status' => $newHotel['status'],
            'latitude' => $newHotel['latitude'],
            'longitude' => $newHotel['longitude'],
            'check_in_time' => $newHotel['check_in_time'],
            'check_out_time' => $newHotel['check_out_time'],
            'percentage_completion' => $newHotel['percentage_completion'],
            'typeclass_id' => $newHotel['typeclass_id'],
            'chain_id' => $newHotel['chain_id'],
            'currency_id' => $newHotel['currency_id'],
            'hotel_type_id' => $newHotel['hotel_type_id'],
            'category_id' => $newHotel['category_id'],
            'country_id' => $newHotel['country_id'],
            'state_id' => $newHotel['state_id'],
            'city_id' => $newHotel['city_id'],
            'district_id' => $newHotel['district_id']
        ]);
    }

    public function testShow()
    {

        $user = factory(User::class)->create();

        $hotel = factory(Hotel::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/hotels/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $hotel->name,
                    'address' => $hotel->address,
                    'stars' => $hotel->stars,
                    'aurora_code' => $hotel->aurora_code,
                    'web_site' => $hotel->web_site,
                    'status' => $hotel->status,
                    'latitude' => $hotel->latitude,
                    'longitude' => $hotel->longitude,
                    'check_in_time' => $hotel->check_in_time,
                    'check_out_time' => $hotel->check_out_time,
                    'percentage_completion' => $hotel->percentage_completion,
                    'country_id' => $hotel->country_id,
                    'state_id' => $hotel->state_id,
                    'city_id' => $hotel->city_id,
                    'district_id' => $hotel->district_id,
                    'chain_id' => $hotel->chain_id,
                    'category_id' => $hotel->category_id,
                    'currency_id' => $hotel->currency_id,
                    'hotel_type_id' => $hotel->hotel_type_id,
                    'typeclass_id' => $hotel->typeclass_id
                ]
            ]);
    }

    public function testUpdate()
    {

        $user = factory(User::class)->create();

        $country = factory(Country::class)->create();
        $state = factory(State::class)->create();
        $city = factory(City::class)->create();
        $district = factory(District::class)->create();
        $chain = factory(Chain::class)->create();
        $category = factory(Category::class)->create();
        $currency = factory(Currency::class)->create();
        $hoteltype = factory(HotelType::class)->create();
        $typesclass = factory(TypeClass::class)->create();

        factory(Hotel::class)->create();

        $newHotel = [
            'name' => $this->faker->name . ' ' . $this->faker->randomElement(['Hotel', 'Resort', 'Hotel & Spa']),
            'address' => $this->faker->address,
            'stars' => $this->faker->randomElement(['1', '2', '3', '4', '5']),
            'aurora_code' => $this->faker->swiftBicNumber,
            'web_site' => $this->faker->url,
            'status' => $this->faker->numberBetween(0, 1),
            'latitude' => $this->faker->latitude(-90, 90),
            'longitude' => $this->faker->longitude(-180, 180),
            'check_in_time' => $this->faker->time('H:i:s', 'now'),
            'check_out_time' => $this->faker->time('H:i:s', 'now'),
            'percentage_completion' => $this->faker->numberBetween(0, 100),
            'typeclass_id' => $typesclass->id,
            'chain_id' => $chain->id,
            'currency_id' => $currency->id,
            'hotel_type_id' => $hoteltype->id,
            'category_id' => $category->id,
            'country_id' => $country->id,
            'state_id' => $state->id,
            'city_id' => $city->id,
            'district_id' => $district->id
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/hotels/1',
                $newHotel
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('hotels', [
            'name' => $newHotel['name'],
            'address' => $newHotel['address'],
            'stars' => $newHotel['stars'],
            'aurora_code' => $newHotel['aurora_code'],
            'web_site' => $newHotel['web_site'],
            'status' => $newHotel['status'],
            'latitude' => $newHotel['latitude'],
            'longitude' => $newHotel['longitude'],
            'check_in_time' => $newHotel['check_in_time'],
            'check_out_time' => $newHotel['check_out_time'],
            'percentage_completion' => $newHotel['percentage_completion'],
            'kind_id' => $newHotel['kind_id'],
            'chain_id' => $newHotel['chain_id'],
            'currency_id' => $newHotel['currency_id'],
            'hotel_type_id' => $newHotel['hotel_type_id'],
            'category_id' => $newHotel['category_id'],
            'country_id' => $newHotel['country_id'],
            'state_id' => $newHotel['state_id'],
            'city_id' => $newHotel['city_id'],
            'district_id' => $newHotel['district_id']
        ]);
    }

    public function testDelete()
    {

        $user = factory(User::class)->create();

        $hotel = factory(Hotel::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/hotels/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
        //$this->assertSoftDeleted('hotels', ['id' => $hotel->id]);
    }
}
