<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Travel;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTravelTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_cannot_access_adding_travel(): void
    {
        $response = $this->postJson('/api/v1/admin/travels');
        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_adding_travel(): void
    {
        // Prepare database and data for test
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::where('name', 'editor')->value('id')
        );

        // POST request to endpoint as user
        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels');

        // Expected HTTP status
        $response->assertStatus(403);
    }

    public function test_saves_travel_successfully_with_valid_data(): void
    {
        // Prepare DB and data for test
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::where('name', 'admin')->value('id')
        );

        $admin_travels_endpoint = '/api/v1/admin/travels';
        $public_travels_endpoint = '/api/v1/travels';

        // POST request as an admin user with invalid fields
        $response = $this->actingAs($user)->postJson($admin_travels_endpoint, [
            'name' => 'New travel',
        ]);

        $response->assertStatus(422);

        // POST request as an admin user with all fields
        $response = $this
            ->actingAs($user)
            ->postJson($admin_travels_endpoint, [
                'name' => 'New travel',
                'is_public' => 1,
                'description' => 'A great place to visit',
                'number_of_days' => 5,
            ]);

        $response->assertStatus(201);

        // Find the latest travel
        $response = $this->getJson($public_travels_endpoint);
        $response->assertJsonFragment(['name' => 'New travel']);
    }

    public function test_updates_travel_successfully_with_valid_data(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::where('name', 'editor')->value('id')
        );
        $travel = Travel::factory()->create();

        $response = $this
            ->actingAs($user)
            ->putJson('/api/v1/admin/travels/'.$travel->id, [
                'name' => 'Updated travel name',
                'is_public' => 1,
                'description' => 'Travel description',
                'number_of_days' => 5,
            ]);

        $response->assertStatus(200);

        $response = $this->getJson('/api/v1/travels');
        $response->assertJsonFragment(['name' => 'Updated travel name']);
    }
}
