<?php

namespace Tests\Unit;

use App\Currency;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class CurrenciesTest extends TestCase
{
    use ActingAs, RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    //index

    public function testCurrenciesIndex()
    {
        $user = factory(User::class)->create();

        factory(Currency::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/currencies');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    //store
    public function testCurrenciesStore()
    {
        $user = factory(User::class)->create();

        $newCurrency = [
            'name' => $this->faker->countryCode(),
            'symbol' => '$',
            'iso' => $this->faker->currencyCode(),

        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/currencies',
                $newCurrency
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('currencies', ['name' => $newCurrency['name']]);

    }

    //show

    public function testCurrenciesShow()
    {
        $user = factory(User::class)->create();

        $currency = factory(Currency::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/currencies/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => $currency->name,
                    'iso' => $currency->iso,
                    'symbol' => '$'
                ]
            ]);
    }

//update
    public function testCurrenciesUpdateWithName()
    {
        $user = factory(User::class)->create();

        factory(Currency::class)->create();

        $newCurrency = [
            'name' => 'dragma',
            'symbol' => 'Dgt',
            'iso' => 'DRM'
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/currencies/1',
                $newCurrency
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('currencies', ['name' => $newCurrency['name']]);
    }

    public function testCurrenciesDelete()
    {
        $user = factory(User::class)->create();

        $currency = factory(Currency::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/currencies/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertSoftDeleted('currencies', ['id' => $currency->id]);
    }
}
