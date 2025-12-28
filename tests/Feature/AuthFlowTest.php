<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_login_profile_refresh_logout()
    {
        // Register (returns refresh token)
        $reg = $this->postJson('/api/ems/auth/register', [
            'username' => 'itest_user',
            'password' => 'Password123!'
        ]);
        $reg->assertStatus(200)->assertJson(['success' => true]);
        $token = $reg->json('data.token');
        $this->assertNotEmpty($token);
        $regRefresh = $reg->json('data.refresh_token');
        $this->assertNotEmpty($regRefresh);

        // Login (also returns refresh token)
        $login = $this->postJson('/api/ems/auth/login', ['username' => 'itest_user', 'password' => 'Password123!']);
        $login->assertStatus(200)->assertJson(['success' => true]);
        $loginToken = $login->json('data.token');
        $loginRefresh = $login->json('data.refresh_token');
        $this->assertNotEmpty($loginToken);
        $this->assertNotEmpty($loginRefresh);

        // Profile with token
        $profile = $this->withHeaders(['Authorization' => 'Bearer ' . $loginToken])->getJson('/api/ems/users/me');
        $profile->assertStatus(200)->assertJson(['success' => true]);

        // Refresh token (using refresh token flow)
        $refresh = $this->postJson('/api/ems/auth/refresh', ['refresh_token' => $loginRefresh]);
        $refresh->assertStatus(200)->assertJson(['success' => true]);
        $newToken = $refresh->json('data.token');
        $newRefresh = $refresh->json('data.refresh_token');
        $this->assertNotEmpty($newToken);
        $this->assertNotEmpty($newRefresh);
        $this->assertNotEquals($loginToken, $newToken);
        $this->assertNotEquals($loginRefresh, $newRefresh);

        // Logout using new refresh token and new access token
        $logout = $this->withHeaders(['Authorization' => 'Bearer ' . $newToken])->postJson('/api/ems/auth/logout', ['refresh_token' => $newRefresh]);
        $logout->assertStatus(200)->assertJson(['success' => true]);

        // New access token should now be revoked
        $check = $this->withHeaders(['Authorization' => 'Bearer ' . $newToken])->getJson('/api/ems/users/me');
        $check->assertStatus(401);
    }
}
