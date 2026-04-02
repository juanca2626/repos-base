<?php

namespace Tests\Unit;

use App\RoomType;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class RoomTypeTest extends TestCase
{
    use ActingAs, RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testIndex()
    {
        $user = factory(User::class)->create();

        factory(RoomType::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/room_types');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    public function testStore()
    {
        $user = factory(User::class)->create();

        $newRoomType = [
            'name' => $this->faker->name
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/room_types',
                $newRoomType
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('room_types', ['name' => $newRoomType['name']]);
    }

    public function testShow()
    {
        $user = factory(User::class)->create();

        $room_type = factory(RoomType::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/room_types/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $room_type->name
                ]
            ]);
    }

    public function testUpdateWithName()
    {
        $user = factory(User::class)->create();

        factory(RoomType::class)->create();

        $newRoomType = [
            'name' => $this->faker->name
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/room_types/1',
                $newRoomType
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('room_types', ['name' => $newRoomType['name']]);
    }

    public function testDelete()
    {
        $user = factory(User::class)->create();

        $room_type = factory(RoomType::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/room_types/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertSoftDeleted('room_types', ['id' => $room_type->id]);
    }
}
