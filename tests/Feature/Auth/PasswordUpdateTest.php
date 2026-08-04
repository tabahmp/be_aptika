<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect('/profile');
    }

    public function test_password_can_be_updated_via_api(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->json('PUT', '/api/password', [
                'current_password' => 'password123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Password berhasil diperbarui.']);

        $this->assertTrue(Hash::check('newpassword123', $user->refresh()->password));
    }

    public function test_password_update_fails_with_invalid_current_password_via_api(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->json('PUT', '/api/password', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }
}
