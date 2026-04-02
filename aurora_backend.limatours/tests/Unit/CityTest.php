<?php

namespace Tests\Feature;

use App\City;
use App\State;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class CityTest extends TestCase
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

        factory(City::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/cities');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    public function testStore()
    {
        $user = factory(User::class)->create();

        $state = factory(State::class)->create();


        $newCity = [
            'name' => $this->faker->city,
            'state_id' => $state->id
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/cities',
                $newCity
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('cities', ['name' => $newCity['name'], 'state_id' => $newCity['state_id']]);
    }

    public function testShow()
    {
        $user = factory(User::class)->create();

        $city = factory(City::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/cities/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $city->name,
                    'state_id' => 1
                ]
            ]);
    }

    public function testUpdate()
    {
        $user = factory(User::class)->create();

        factory(City::class)->create();

        $state = factory(State::class)->create();

        $newCity = [
            'name' => $this->faker->city,
            'state_id' => $state->id
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/cities/1',
                $newCity
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('cities', ['name' => $newCity['name'], 'state_id' => $newCity['state_id']]);
    }

    public function testDelete()
    {
        $user = factory(User::class)->create();

        $city = factory(City::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/cities/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertSoftDeleted('cities', ['id' => $city->id]);
    }
}
