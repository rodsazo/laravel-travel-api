<?php

namespace Tests\Feature;

use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TravelsListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_travels_list_returns_pagination(): void
    {
        Travel::factory(16)->create(['is_public' => true ]);
        $response = $this->get('/api/v1/travels');
        $response->assertStatus(200);

        // Check that we get 10 records
        $response->assertJsonCount(10, 'data');

        // Check there are two pages
        $response->assertJsonPath('meta.last_page', 2 );

    }

    public function test_travels_list_shows_only_public_records(){
        // Create two travels:
        $nonPublicTravel = Travel::factory()->create(['is_public' =>  false]);
        $publicTravel = Travel::factory()->create(['is_public' => true ]);

        $response = $this->get('/api/v1/travels');
        $response->assertStatus( 200 );

        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $publicTravel->id]);
        $response->assertJsonMissing(['id' => $nonPublicTravel->id]);
    }
}
