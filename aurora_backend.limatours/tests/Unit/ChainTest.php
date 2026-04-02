<?php

namespace Tests\Unit;

use App\Chain;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class ChainTest extends TestCase
{
    use ActingAs, RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    //index
    public function testChainsIndex()
    {
        $user = factory(User::class)->create();

        factory(Chain::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/chains');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    //store
    public function testChainsStore()
    {
        $user = factory(User::class)->create();

        $newChain = [
            'name' => $this->faker->name,
            'status' => 1
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/chains',
                $newChain
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('chains', ['name' => $newChain['name']]);
    }

    //show

    public function testChainsShow()
    {
        $user = factory(User::class)->create();

        $chain = factory(Chain::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/chains/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $chain->name,
                    'status' => $chain->status,
                ]
            ]);
    }

    //update
    public function testChainsUpdateWithName()
    {
        $user = factory(User::class)->create();

        factory(Chain::class)->create();

        $newChain = [
            'name' => $this->faker->name,
            'status' => $this->faker->status,  
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/chains/1',
                $newChain
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('chains', ['name' => $newChain['name']]);
    }

    public function testChainsDelete()
    {
        $user = factory(User::class)->create();

        $chain = factory(Chain::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/chains/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertSoftDeleted('chains', ['id' => $chain->id]);
    }





}
