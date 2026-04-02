<?php

namespace Tests\Unit;

use App\Amenity;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class AmenityTest extends TestCase
{
    use ActingAs, RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testAmenitiesIndex()
    {
        $user = factory(User::class)->create();

        factory(Amenity::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/amenities');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    //store
    public function testAmenitiesStore()
    {
        $user = factory(User::class)->create();

        $newAmenity = [
            'name' => $this->faker->name,
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/amenities',
                $newAmenity
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('amenities', ['name' => $newAmenity['name']]);

    }

    //show
    public function testAmenitiesShow()
    {
        $user = factory(User::class)->create();

        $amenities = factory(Amenity::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/amenities/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $amenities->name
                ]
            ]);
    }

    //update
    public function testAmenitiesUpdateWithName()
    {
        $user = factory(User::class)->create();

        factory(Amenity::class)->create();

        $newAmenity = [
            'name' => $this->faker->name
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/amenities/1',
                $newAmenity
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('amenities', ['name' => $newAmenity['name']]);
    }

    public function testAmenitiesDelete()
    {
        $user = factory(User::class)->create();

        $amenities = factory(Amenity::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/amenities/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        //$this->assertSoftDeleted('amenities', ['id' => $amenities->id]);
    }
}
