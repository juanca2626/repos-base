<?php

namespace Tests\Unit;

use App\HotelType;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class HotelTypeTest extends TestCase
{
    use ActingAs, RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testHotelTypesIndex()
    {
        $user = factory(User::class)->create();

        factory(HotelType::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/hotel_types');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    //store
    public function testHotelTypesStore()
    {
        $user = factory(User::class)->create();

        $newHotelType = [
            'name' => $this->faker->name,
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/hotel_types',
                $newHotelType
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('hotel_types', ['name' => $newHotelType['name']]);

    }

    //show
    public function testHotelTypesShow()
    {
        $user = factory(User::class)->create();

        $hotel_types = factory(HotelType::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/hotel_types/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $hotel_types->name
                ]
            ]);
    }

    //update
    public function testHotelTypesUpdateWithName()
    {
        $user = factory(User::class)->create();

        factory(HotelType::class)->create();

        $newHotelType = [
            'name' => $this->faker->name
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/hotel_types/1',
                $newHotelType
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('hotel_types', ['name' => $newHotelType['name']]);
    }

    public function testHotelTypesDelete()
    {
        $user = factory(User::class)->create();

        $hotel_types = factory(HotelType::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/hotel_types/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        //$this->assertSoftDeleted('hotel_types', ['id' => $hotel_types->id]);
    }
}
