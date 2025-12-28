<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_login_profile_refresh_logout()
    {
        // Register
        $reg = $this->postJson('/api/ems/auth/register', [
            'username' => 'itest_user',
            'password' => 'Password123!'
        ]);
        $reg->assertStatus(200)->assertJson(['success' => true]);
        $token = $reg->json('data.token');
        $this->assertNotEmpty($token);

        // Login
        $login = $this->postJson('/api/ems/auth/login', ['username' => 'itest_user', 'password' => 'Password123!']);
        $login->assertStatus(200)->assertJson(['success' => true]);
        $loginToken = $login->json('data.token');
        $this->assertNotEmpty($loginToken);

        // Profile with token
        $profile = $this->withHeaders(['Authorization' => 'Bearer ' . $loginToken])->getJson('/api/ems/users/me');
        $profile->assertStatus(200)->assertJson(['success' => true]);

        // Refresh token
        $refresh = $this->withHeaders(['Authorization' => 'Bearer ' . $loginToken])->postJson('/api/ems/auth/refresh');
        $refresh->assertStatus(200)->assertJson(['success' => true]);
        $newToken = $refresh->json('data.token');
        $this->assertNotEmpty($newToken);
        $this->assertNotEquals($loginToken, $newToken);

        // Logout using new token
        $logout = $this->withHeaders(['Authorization' => 'Bearer ' . $newToken])->postJson('/api/ems/auth/logout');
        $logout->assertStatus(200)->assertJson(['success' => true]);

        // Old token should now be revoked (new token was revoked by refresh and then new token revoked by logout)
        $check = $this->withHeaders(['Authorization' => 'Bearer ' . $newToken])->getJson('/api/ems/users/me');
        $check->assertStatus(401);
    }
}
