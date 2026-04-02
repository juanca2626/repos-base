<?php

namespace Tests\Feature;

use App\Meal;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class MealTest extends TestCase
{
    use ActingAs, RefreshDatabase;

    /**
     * Meal controller can List
     *
     * @return void
     */
    public function testIndex()
    {
        $user = factory(User::class)->create();

        factory(Meal::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/meals');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    public function testStore()
    {
        $user = factory(User::class)->create();

        $newMeal = [
            'name' => $this->faker->languageCode
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/meals',
                $newMeal
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('meals', ['name' => $newMeal['name']]);
    }

    public function testShow()
    {
        $user = factory(User::class)->create();

        $meal = factory(Meal::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/meals/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $meal->name
                ]
            ]);
    }

    public function testUpdateWithName()
    {
        $user = factory(User::class)->create();

        factory(Meal::class)->create();

        $newMeal = [
            'name' => $this->faker->country
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/meals/1',
                $newMeal
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('meals', ['name' => $newMeal['name']]);
    }

    public function testDelete()
    {
        $user = factory(User::class)->create();

        $meal = factory(Meal::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/meals/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertSoftDeleted('meals', ['id' => $meal->id]);
    }
}
