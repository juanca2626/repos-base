<?php

namespace Tests\Unit;

use App\Language;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class LanguagesTest extends TestCase
{
    use ActingAs, RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testLanguagesIndex()
    {
        $user = factory(User::class)->create();

        factory(Language::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/languages');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    //store
    public function testLanguagesStore()
    {
        $user = factory(User::class)->create();

        $newLanguage = [
            'name' => $this->faker->languageCode(),
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/languages',
                $newLanguage
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('languages', ['name' => $newLanguage['name']]);

    }

    //show
    public function testLanguagesShow()
    {
        $user = factory(User::class)->create();

        $languages = factory(Language::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/languages/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $languages->name
                ]
            ]);
    }

    //update
    public function testLanguagesUpdateWithName()
    {
        $user = factory(User::class)->create();

        factory(Language::class)->create();

        $newLanguage = [
            'name' => $this->faker->languageCode()
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/languages/1',
                $newLanguage
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('languages', ['name' => $newLanguage['name']]);
    }

    public function testLanguagesDelete()
    {
        $user = factory(User::class)->create();

        $languages = factory(Language::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/languages/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        //$this->assertSoftDeleted('languages', ['id' => $languages->id]);
    }


}
