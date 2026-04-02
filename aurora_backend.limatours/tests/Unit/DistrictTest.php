<?php

namespace Tests\Feature;

use App\City;
use App\District;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class DistrictTest extends TestCase
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

        factory(District::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/districts');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    public function testStore()
    {
        $user = factory(User::class)->create();

        $city = factory(City::class)->create();


        $newDistrict = [
            'name' => $this->faker->city,
            'city_id' => $city->id
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/districts',
                $newDistrict
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('districts', ['name' => $newDistrict['name'], 'city_id' => $newDistrict['city_id']]);
    }

    public function testShow()
    {
        $user = factory(User::class)->create();

        $district = factory(District::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/districts/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $district->name,
                    'city_id' => 1
                ]
            ]);
    }

    public function testUpdate()
    {
        $user = factory(User::class)->create();

        factory(District::class)->create();

        $city = factory(City::class)->create();

        $newDistrict = [
            'name' => $this->faker->city,
            'city_id' => $city->id
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/districts/1',
                $newDistrict
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('districts', ['name' => $newDistrict['name'], 'city_id' => $newDistrict['city_id']]);
    }

    public function testDelete()
    {
        $user = factory(User::class)->create();

        $district = factory(District::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/districts/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertSoftDeleted('districts', ['id' => $district->id]);
    }
}
