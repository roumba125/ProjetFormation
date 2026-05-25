<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------
    // SÉCURITÉ — accès sans token
    // -------------------------------------------------------

    public function test_dashboard_admin_sans_token_retourne_401()
    {
        $response = $this->getJson('/api/v1/admin/dashboard');
        $response->assertStatus(401);
    }

    public function test_liste_moutons_admin_sans_token_retourne_401()
    {
        $response = $this->getJson('/api/v1/admin/sheep');
        $response->assertStatus(401);
    }

    // -------------------------------------------------------
    // SÉCURITÉ — client ne peut pas accéder à admin
    // -------------------------------------------------------

    public function test_client_ne_peut_pas_acceder_admin()
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client)
                         ->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------
    // ACCÈS LÉGITME — admin peut accéder
    // -------------------------------------------------------

    public function test_admin_peut_acceder_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')
                         ->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------
    // AUTH — login
    // -------------------------------------------------------

   public function test_login_avec_mauvais_credentials_retourne_401()
    {
    $response = $this->postJson('/api/v1/admin/login', [
        'email'    => 'faux@email.com',
        'password' => 'mauvais',
    ]);
    $response->assertStatus(401); // ✅ credentials invalides
    }

    public function test_login_avec_format_invalide_retourne_422()
    {
    $response = $this->postJson('/api/v1/admin/login', [
        'email'    => 'pas-un-email', // format invalide
        'password' => '',              // vide
    ]);
    $response->assertStatus(422); // ✅ validation échouée
    }

    public function test_login_admin_retourne_token()
    {
        $admin = User::factory()->create([
            'role'     => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/admin/login', [
            'email'    => $admin->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    }
}