<?php

namespace Tests\Unit;

use App\User;
use Tests\TestCase;
use Tests\Utils\ActingAs;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use ActingAs, RefreshDatabase;

    /**
     * User controller can List
     *
     * @return void
     */
    public function testIndex()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/users');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    public function testStore()
    {
        $user = factory(User::class)->create();

        $newUser = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => $this->faker->password
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/users',
                $newUser
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('users', ['name' => $newUser['name'], 'email' => $newUser['email']]);
    }

    public function testShow()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/users/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);
    }

    public function testUpdateWithPassword()
    {
        $user = factory(User::class)->create();

        $newUser = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => $this->faker->password
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/users/1',
                $newUser
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('users', ['name' => $newUser['name'], 'email' => $newUser['email']]);
    }

    public function testUpdateWithoutPassword()
    {
        $user = factory(User::class)->create();

        $newUser = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/users/1',
                $newUser
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('users', $newUser);
    }

    public function testDelete()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/users/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertSoftDeleted('users', ['email' => $user->email]);
    }
}
