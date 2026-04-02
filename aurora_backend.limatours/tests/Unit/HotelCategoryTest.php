<?php

namespace Tests\Unit;

use App\HotelCategory;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class HotelCategoryTest extends TestCase
{
    use ActingAs, RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    //index
    public function testCategoriesIndex()
    {
        $user = factory(User::class)->create();

        factory(HotelCategory::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/hotelcategories');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    //store
    public function testCategoriesStore()
    {
        $user = factory(User::class)->create();

        $newHotelCategory = [
            'name' => $this->faker->name,
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/hotelcategories',
                $newHotelCategory
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('hotel_categories', ['name' => $newHotelCategory['name']]);
    }

    //show

    public function testCategoriesShow()
    {
        $user = factory(User::class)->create();

        $hotelcategory = factory(HotelCategory::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/hotelcategories/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $hotelcategory->name
                ]
            ]);
    }

    //update
    public function testCategoriesUpdateWithName()
    {
        $user = factory(User::class)->create();

        factory(HotelCategory::class)->create();

        $newHotelCategory = [
            'name' => $this->faker->name
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/hotelcategories/1',
                $newHotelCategory
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('hotel_categories', ['name' => $newHotelCategory['name']]);
    }

    public function testCategoriesDelete()
    {
        $user = factory(User::class)->create();

        $hotelcategory = factory(HotelCategory::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/hotelcategories/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertSoftDeleted('hotel_categories', ['id' => $hotelcategory->id]);
    }
}
