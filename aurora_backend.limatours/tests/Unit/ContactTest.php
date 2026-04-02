<?php

namespace Tests\Feature;

use App\Contact;
use App\Hotel;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils\ActingAs;

class ContactTest extends TestCase
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

        factory(Contact::class)->create();

        $response = $this->actingAs($user)->json('GET', '/api/contacts');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    public function testStore()
    {
        $user = factory(User::class)->create();

        $hotel = factory(Hotel::class)->create();


        $newTax = [
            'name' => $this->faker->name,
            'surname' => $this->faker->name,
            'lastname' => $this->faker->name,
            'position' => $this->faker->city,
            'hotel_id' => $hotel->id
        ];

        $response = $this->actingAs($user)
            ->json(
                'POST',
                '/api/contacts',
                $newTax
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('contacts', [
            'name' => $newTax['name'],
            'surname' => $newTax['surname'],
            'lastname' => $newTax['lastname'],
            'hotel_id' => $newTax['hotel_id']
        ]);
    }

    public function testShow()
    {
        $user = factory(User::class)->create();

        $contact = factory(Contact::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'GET',
                '/api/contacts/1'
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => $contact->name,
                    'surname' => $contact->surname,
                    'lastname' => $contact->lastname,
                    'position' => $contact->position,
                    'hotel_id' => 1
                ]
            ]);
    }

    public function testUpdate()
    {
        $user = factory(User::class)->create();

        factory(Tax::class)->create();

        $country = factory(Country::class)->create();

        $newTax = [
            'name' => $this->faker->name,
            'surname' => $this->faker->name,
            'lastname' => $this->faker->name,
            'position' => $this->faker->city,
            'hotel_id' => $hotel->id
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                '/api/contacts/1',
                $newTax
            );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('contacts', [
            'name' => $newTax['name'],
            'surname' => $newTax['surname'],
            'lastname' => $newTax['lastname'],
            'hotel_id' => $newTax['hotel_id']
        ]);
    }

    public function testDelete()
    {
        $user = factory(User::class)->create();

        $contact = factory(Contact::class)->create();

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                '/api/contacts/1'
            );
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
    }
}
