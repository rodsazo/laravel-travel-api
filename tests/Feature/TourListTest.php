<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TourListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_tours_list_by_travel_slug_returns_correct_tours(): void
    {
        $travel = Travel::factory()->create(['name' => 'Test Travel','slug' => 'test-travel']);
        $tour = $travel->tours()->create([
            'name' => 'Test Travel By Night',
            'price' => 249.99,
            'starting_date' => '2023-12-12',
            'ending_date'=> '2023-12-15'
        ]);

        $response = $this->get('/api/v1/travels/' . $travel->slug . '/tours');

        $response->assertStatus(200);

        $response->assertJsonFragment(['id' => $tour->id ]);

        $response->assertStatus(200);
    }

    public function test_tour_price_is_shown_correctly(){

    }

    public function test_tours_list_returns_pagination(){

    }
}
