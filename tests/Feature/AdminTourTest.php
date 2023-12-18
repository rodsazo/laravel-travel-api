<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Travel;
use Database\Seeders\RoleSeeder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTourTest extends TestCase
{
    use RefreshDatabase;
    public function test_public_user_cannot_access_adding_tour(): void
    {
        $travel = Travel::factory()->create();
        $response = $this->postJson('/api/v1/admin/travels/'.$travel->id.'/tours');
        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_adding_tour() : void
    {
        $this->seed( RoleSeeder::class );
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::where('name', 'editor')->value('id')
        );

        $travel = Travel::factory()->create();

        $response = $this
            ->actingAs( $user )
            ->postJson( '/api/v1/admin/travels/'.$travel->id.'/tours');

        $response->assertStatus(403);

    }

    public function test_saves_tour_successfully_with_valid_data() : void
    {
        $this->seed( RoleSeeder::class );
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::where('name', 'admin')->value('id')
        );
        $travel = Travel::factory()->create();

        // Creating the tour
        $endpoint = '/api/v1/admin/travels/' . $travel->id . '/tours';
        $response = $this
            ->actingAs( $user )
            ->postJson( $endpoint, [
                'name' => 'Un bonito día en Cusco',
                'starting_date' => '2023-12-12',
                'ending_date' => '2023-12-15',
                'price' => 150.99
            ]);

        $response->assertStatus(201);

        // Checking the tour exists
        $response = $this
            ->getJson('/api/v1/travels/' . $travel->slug . '/tours');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['name' => 'Un bonito día en Cusco']);
    }
}
