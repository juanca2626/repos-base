<?php

namespace Tests\Unit;

use App\Facility;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class FacilityTest extends TestCase
{
    use ActingAs, RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testFacilitiesIndex()
    {
        $user = factory(User::class)->create();

        factory(Facility::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/facilities');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    //store
    public function testFacilitiesStore()
    {
        $user = factory(User::class)->create();

        $newFacility = [
            'name' => $this->faker->name,
            'price' => $this->faker->randomFloat(2, 0, 8),
            'status' => 1,
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/facilities',
                $newFacility
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('facilities', ['name' => $newFacility['name']]);

    }

    //show
    public function testFacilitiesShow()
    {
        $user = factory(User::class)->create();

        $facilities = factory(Facility::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/facilities/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $facilities->name,
                    'price' => $facilities->price,
                    'status' => $facilities->status

                ]
            ]);
    }

    //update
    public function testFacilitiesUpdateWithName()
    {
        $user = factory(User::class)->create();

        factory(Facility::class)->create();

        $newFacility = [
            'name' => $this->faker->name,
            'price' => $this->faker->randomFloat(2, 0, 8),
            'status' => 0
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/facilities/1',
                $newFacility
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('facilities', [
            'name' => $newFacility['name'],
            'price' => $newFacility['price'],
            'status' => $newFacility['status'],
        ]);
    }

    public function testFacilitiesDelete()
    {
        $user = factory(User::class)->create();

        $facilities = factory(Facility::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/facilities/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        //$this->assertSoftDeleted('facilities', ['id' => $facilities->id]);
    }
}
