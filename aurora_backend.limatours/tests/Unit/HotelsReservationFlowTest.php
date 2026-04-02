<?php

namespace Tests\Unit;

use App\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;

class HotelsReservationFlowTest extends BaseTestCase
{
    use CreatesApplication;

    protected $faker;

    /** @test * */
    private function actingAsAdmin()
    {
        $this->actingAs(User::find(1));
    }

    /** @test * */
    public function hotel_availability()
    {
        $this->actingAsAdmin();

        $response = $this->post('/services/hotels/available', [
            'client_id' => 1,
            'date_from' => '2019-12-18',
            'date_to' => '2019-12-20',
            'destiny' => ['code' => "PE,LIM", 'label' => "Perú,Lima"],
            'quantity_persons_rooms' => [
                [
                    'room' => 1,
                    'adults' => 2,
                    'child' => 0,
                    'ages_child' => [
                        ['child' => 1, 'age' => 1]
                    ]
                ]
            ],
            'quantity_rooms' => 1,
            'typeclass_id' => 'all',
        ])
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }
}
