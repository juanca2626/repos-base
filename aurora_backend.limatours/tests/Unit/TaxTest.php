<?php

namespace Tests\Feature;

use App\Country;
use App\Tax;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class TaxTest extends TestCase
{
    use ActingAs, RefreshDatabase;

    /**
     * State controller can List
     *
     * @return void
     */
    public function testIndex()
    {
        $user = factory(User::class)->create();

        factory(Tax::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/taxes');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    public function testStore()
    {
        $user = factory(User::class)->create();

        $country = factory(Country::class)->create();


        $newTax = [
            'name' => $this->faker->randomDigit,
            'country_id' => $country->id
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/taxes',
                $newTax
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('taxes', ['name' => $newTax['name'], 'country_id' => $newTax['country_id']]);
    }

    public function testShow()
    {
        $user = factory(User::class)->create();

        $tax = factory(Tax::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/taxes/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $tax->name,
                    'country_id' => 1
                ]
            ]);
    }

    public function testUpdate()
    {
        $user = factory(User::class)->create();

        factory(Tax::class)->create();

        $country = factory(Country::class)->create();

        $newTax = [
            'name' => $this->faker->randomDigit,
            'country_id' => $country->id
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/taxes/1',
                $newTax
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('taxes', ['name' => $newTax['name'], 'country_id' => $newTax['country_id']]);
    }

    public function testDelete()
    {
        $user = factory(User::class)->create();

        $tax = factory(Tax::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/taxes/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertSoftDeleted('taxes', ['id' => $tax->id]);
    }
}
