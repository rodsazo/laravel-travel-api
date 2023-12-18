<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_tours_list_by_travel_slug_returns_correct_tours(): void
    {
        $travel = Travel::factory()->create(['name' => 'Test Travel', 'slug' => 'test-travel']);
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $tour->id]);
    }

    public function test_tour_price_is_shown_correctly(): void
    {
        $travel = Travel::factory()->create();
        Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 223.56,
        ]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['price' => '223.56']);
    }

    public function test_tours_list_returns_pagination(): void
    {
        $travel = Travel::factory()->create();
        Tour::factory(16)->create(['travel_id' => $travel->id]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
        $response->assertJsonPath('meta.last_page', 2);

    }

    public function test_tours_list_sorts_by_starting_date_correctly(): void
    {
        $travel = Travel::factory()->create();
        $laterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(5),
        ]);
        $earlierTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now(),
            'ending_date' => now()->addDays(1),
        ]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.id', $earlierTour->id);
        $response->assertJsonPath('data.1.id', $laterTour->id);
    }

    public function test_tours_list_sorts_by_price_correctly(): void
    {
        $travel = Travel::factory()->create();
        $expensiveTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 1000,
        ]);
        $cheapLaterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(5),
        ]);
        $cheapEarlierTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
            'starting_date' => now(),
            'ending_date' => now()->addDays(2),
        ]);

        $url = '/api/v1/travels/'.$travel->slug.'/tours?sortBy=price&sortOrder=asc';
        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.id', $cheapEarlierTour->id);
        $response->assertJsonPath('data.1.id', $cheapLaterTour->id);
        $response->assertJsonPath('data.2.id', $expensiveTour->id);

    }

    public function test_tours_list_filters_by_price_correctly(): void
    {
        $travel = Travel::factory()->create();
        $cheapTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
        ]);
        $expensiveTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 200,
        ]);

        $endpoint = 'api/v1/travels/'.$travel->slug.'/tours';

        // Greater than
        $request = $this->get($endpoint.'?priceFrom=150');
        $request->assertStatus(200);
        $request->assertJsonCount(1, 'data');
        $request->assertJsonMissing(['id' => $cheapTour->id]);
        $request->assertJsonFragment(['id' => $expensiveTour->id]);

        // Lower than
        $request = $this->get($endpoint.'?priceTo=150');
        $request->assertStatus(200);
        $request->assertJsonCount(1, 'data');
        $request->assertJsonMissing(['id' => $expensiveTour->id]);
        $request->assertJsonFragment(['id' => $cheapTour->id]);
    }

    public function test_tours_list_filters_by_starting_date_correctly(): void
    {
        // Prepare data
        $travel = Travel::factory()->create();
        $laterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(4),
        ]);
        $earlierTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now(),
            'ending_date' => now()->addDays(2),
        ]);

        $endpoint = 'api/v1/travels/'.$travel->slug.'/tours';

        // Test dateFrom:

        // - Earlier than both
        $response = $this->get($endpoint.'?dateFrom='.now()->subDay());
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id' => $earlierTour->id]);
        $response->assertJsonFragment(['id' => $laterTour->id]);

        // - Earlier than one
        $response = $this->get($endpoint.'?dateFrom='.now()->addDays(1));
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $laterTour->id]);
        $response->assertJsonMissing(['id' => $earlierTour->id]);

        // - Earlier than none
        $response = $this->get($endpoint.'?dateFrom='.now()->addDays(5));
        $response->assertJsonCount(0, 'data');
        $response->assertJsonMissing(['id' => $laterTour->id]);
        $response->assertJsonMissing(['id' => $earlierTour->id]);

        // Test dateTo:

        // - Later than both
        $response = $this->get($endpoint.'?dateTo='.now()->addDays(3));
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id' => $earlierTour->id]);
        $response->assertJsonFragment(['id' => $laterTour->id]);

        // - Later than one
        $response = $this->get($endpoint.'?dateTo='.now()->addDays(1));
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $earlierTour->id]);
        $response->assertJsonMissing(['id' => $laterTour->id]);

        // - Later than none
        $response = $this->get($endpoint.'?dateTo='.now()->subDay());
        $response->assertJsonCount(0, 'data');
        $response->assertJsonMissing(['id' => $earlierTour->id]);
        $response->assertJsonMissing(['id' => $laterTour->id]);

        // Test dateFrom and dateTo

        // - Match only one result
        $response = $this->get($endpoint.'?dateFrom='.now()->subDay().'&dateTo='.now()->addDay());
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $earlierTour->id]);
        $response->assertJsonMissing(['id' => $laterTour->id]);
    }

    public function test_tour_list_returns_validation_errors(): void
    {
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);

        $endpoint = 'api/v1/travels/'.$travel->slug.'/tours';

        $response = $this->getJson($endpoint.'?dateFrom=123');
        $response->assertStatus(422);

        $response = $this->getJson($endpoint.'?priceFrom=abc');
        $response->assertStatus(422);

    }
}
