<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_flow()
    {
        // Register user
        $reg = $this->postJson('/api/ems/auth/register', [
            'username' => 'pr_user',
            'password' => 'Password123!'
        ]);
        $reg->assertStatus(200)->assertJson(['success' => true]);

        $user = User::where('username', 'pr_user')->first();
        $this->assertNotNull($user);

        // Request password reset
        $req = $this->postJson('/api/ems/auth/password-reset/request', ['username' => null, 'phone' => $user->phone]);
        // Our implementation returns the token for testing; in production we would send SMS/email
        $req->assertStatus(200)->assertJson(['success' => true]);
        $token = $req->json('data.token');
        $this->assertNotEmpty($token);

        // Confirm reset
        $confirm = $this->postJson('/api/ems/auth/password-reset/confirm', ['token' => $token, 'password' => 'NewPass1!']);
        $confirm->assertStatus(200)->assertJson(['success' => true]);

        // Login with new password
        $login = $this->postJson('/api/ems/auth/login', ['username' => 'pr_user', 'password' => 'NewPass1!']);
        $login->assertStatus(200)->assertJson(['success' => true]);
    }
}
