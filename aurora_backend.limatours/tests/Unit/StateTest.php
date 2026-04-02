<?php

namespace Tests\Feature;

use App\State;
use App\User;
use App\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class StateTest extends TestCase
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

        factory(State::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/states');

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


        $newState = [
            'name' => $this->faker->city,
            'country_id'=> $country->id
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/states',
                $newState
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('states', ['name' => $newState['name'], 'country_id'=>$newState['country_id']]);
    }

    public function testShow()
    {
        $user = factory(User::class)->create();

        $state = factory(State::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/states/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $state->name,
                    'country_id'=>1
                ]
            ]);
    }

    public function testUpdate()
    {
        $user = factory(User::class)->create();

        factory(State::class)->create();

        $country = factory(Country::class)->create();

        $newState = [
            'name' => $this->faker->city,
            'country_id' => $country->id
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/states/1',
                $newState
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('states', ['name' => $newState['name'],'country_id'=> $newState['country_id']]);
    }

    public function testDelete()
    {
        $user = factory(User::class)->create();

        $state = factory(State::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/states/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertSoftDeleted('states', ['id' => $state->id]);
    }
}
