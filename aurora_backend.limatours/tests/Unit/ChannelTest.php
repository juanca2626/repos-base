<?php

namespace Tests\Unit;

use App\Channel;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class ChannelTest extends TestCase
{
    use ActingAs, RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testChannelsIndex()
    {
        $user = factory(User::class)->create();

        factory(Channel::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/channels');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    //store
    public function testChannelsStore()
    {
        $user = factory(User::class)->create();

        $newChannel = [
            'name' => $this->faker->name,
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/channels',
                $newChannel
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('channels', ['name' => $newChannel['name']]);

    }

    //show
    public function testChannelsShow()
    {
        $user = factory(User::class)->create();

        $channels = factory(Channel::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/channels/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $channels->name
                ]
            ]);
    }

    //update
    public function testChannelsUpdateWithName()
    {
        $user = factory(User::class)->create();

        factory(Channel::class)->create();

        $newChannel = [
            'name' => $this->faker->name
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/channels/1',
                $newChannel
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('channels', ['name' => $newChannel['name']]);
    }

    public function testChannelsDelete()
    {
        $user = factory(User::class)->create();

        $channels = factory(Channel::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/channels/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        //$this->assertSoftDeleted('channels', ['id' => $channels->id]);
    }
}
