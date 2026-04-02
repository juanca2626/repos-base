<?php

namespace Tests\Feature;

use App\Galery;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class GaleryTest extends TestCase
{
    use ActingAs, RefreshDatabase;

    public function testIndex()
    {
        $user = factory(User::class)->create();

        factory(Galery::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/galeries');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    public function testStore()
    {
        $user = factory(User::class)->create();

        $newGalery = [
            'type' => $this->faker->randomElement(['hotel', 'room', 'client']),
            'object_id' => $this->faker->randomDigit,
            'url' => $this->faker->imageUrl(40, 40),
            'position' => $this->faker->unique()->randomDigit,
            'state' => $this->faker->numberBetween(0, 1)
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/galeries',
                $newGalery
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('galeries', ['url' => $newGalery['url']]);
    }

    public function testShow()
    {
        $user = factory(User::class)->create();

        $galery = factory(Galery::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/galeries/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'type' => $galery->type,
                    'object_id' => $galery->object_id,
                    'url' => $galery->url,
                    'position' => $galery->position,
                    'state' => $galery->state

                ]
            ]);
    }

    //update
    public function testUpdate()
    {
        $user = factory(User::class)->create();

        factory(Galery::class)->create();

        $newGalery = [
            'type' => $this->faker->randomElement(['hotel', 'room', 'client']),
            'object_id' => $this->faker->randomDigit,
            'url' => $this->faker->imageUrl(40, 40),
            'position' => $this->faker->unique()->randomDigit,
            'state' => $this->faker->numberBetween(0, 1)
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/galeries/1',
                $newGalery
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('galeries', [
            'type' => $newGalery['type'],
            'object_id' => $newGalery['object_id'],
            'state' => $newGalery['state'],
        ]);
    }

    public function testDelete()
    {
        $user = factory(User::class)->create();

        $galery = factory(Galery::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/galeries/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertSoftDeleted('galeries', ['id' => $galery->id]);
    }
}
