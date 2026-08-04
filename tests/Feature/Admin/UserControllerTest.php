<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_with_position_and_phone(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => 1,
        ]);

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->json('POST', '/api/admin/users', [
                'name' => 'Vanesa Rizka Alfatihah',
                'email' => 'vanesa@aptika.com',
                'password' => 'password123',
                'role' => 'user',
                'is_active' => 1,
                'position' => 'Intern',
                'phone' => '082113322488',
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('message', 'User created successfully');

        $this->assertDatabaseHas('users', [
            'email' => 'vanesa@aptika.com',
            'position' => 'Intern',
            'phone' => '082113322488',
        ]);
    }
}
