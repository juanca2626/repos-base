<?php

namespace Tests\Unit;

use App\TypeClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class TypeClassTest extends TestCase
{
    
    use ActingAs, RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    //index
    public function testTypesClassIndex()
    {
        $user = factory(User::class)->create();

        factory(TypeClass::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/typesclass');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    //store
    public function testTypesClassStore()
    {
        $user = factory(User::class)->create();

        $newTypeClass = [
            'name' => $this->faker->name,
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/typesclass',
                $newTypeClass
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('typesclass', ['name' => $newTypeClass['name']]);
    }

    //show
    public function testTypesClassShow()
    {
        $user = factory(User::class)->create();

        $typesclass = factory(TypeClass::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/typesclass/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $typesclass->name
                ]
            ]);
    }

    //update
    public function testTypesClassUpdateWithName()
    {
        $user = factory(User::class)->create();

        factory(TypeClass::class)->create();

        $newTypeClass = [
            'name' => $this->faker->name
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/typesclass/1',
                $newTypeClass
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('typesclass', ['name' => $newTypeClass['name']]);
    }

    public function testTypesClassDelete()
    {
        $user = factory(User::class)->create();

        $typesclass = factory(TypeClass::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/typesclass/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertSoftDeleted('typesclass', ['id' => $typesclass->id]);
    }
}
