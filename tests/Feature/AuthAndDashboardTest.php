<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * 1. Buka /dashboard tanpa login -> Diarahkan ke /login
     */
    public function test_guest_cannot_access_dashboard_and_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * 2. Login password salah -> Muncul pesan error
     */
    public function test_login_with_wrong_password_shows_error(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'petugas@earsip.test',
            'password' => 'passwordsalah',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'Email atau password salah.']);
    }

    /**
     * 3. Login petugas@earsip.test / password -> Dashboard muncul dengan angka 8, 10, 2
     */
    public function test_petugas_can_login_and_see_dashboard_with_counts(): void
    {
        $loginResponse = $this->post('/login', [
            'email' => 'petugas@earsip.test',
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('8');
        $dashboardResponse->assertSee('10');
        $dashboardResponse->assertSee('2');
        $dashboardResponse->assertSee('Total anggota: 8');
        $dashboardResponse->assertSee('Total arsip: 10');
        $dashboardResponse->assertSee('Pinjaman macet: 2');
        $dashboardResponse->assertDontSee('/approval');
    }

    /**
     * 4. Petugas buka /approval -> 403
     */
    public function test_petugas_cannot_access_approval_page_and_gets_403(): void
    {
        $petugas = User::where('email', 'petugas@earsip.test')->first();

        $response = $this->actingAs($petugas)->get('/approval');

        $response->assertStatus(403);
    }

    /**
     * 5. Logout, lalu login pimpinan@earsip.test / password -> Menu Approval muncul dan /approval terbuka
     */
    public function test_pimpinan_can_login_and_see_approval_menu_and_access_approval(): void
    {
        // Petugas was logged in
        $petugas = User::where('email', 'petugas@earsip.test')->first();
        $this->actingAs($petugas);

        // Petugas logs out
        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect('/login');
        $this->assertGuest();

        // Login as pimpinan
        $loginResponse = $this->post('/login', [
            'email' => 'pimpinan@earsip.test',
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        // Check dashboard has menu approval
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('/approval');
        $dashboardResponse->assertSee('Approval');

        // Check /approval opens successfully
        $approvalResponse = $this->get('/approval');
        $approvalResponse->assertStatus(200);
        $approvalResponse->assertSee('Approval Pimpinan');
    }
}
