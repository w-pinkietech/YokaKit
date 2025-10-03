<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    /**
     * Test home page redirects to login when not authenticated
     *
     * @return void
     */
    public function test_home_page_redirects_to_login_when_not_authenticated()
    {
        $response = $this->get('/home');

        $response->assertRedirect('/login');
    }

    /**
     * Test home page is accessible when authenticated
     *
     * @return void
     */
    public function test_home_page_is_accessible_when_authenticated()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
        $response->assertViewIs('home');
    }

    /**
     * Test root redirects to home
     *
     * @return void
     */
    public function test_root_redirects_to_home()
    {
        $response = $this->get('/');

        $response->assertRedirect('/home');
    }

    /**
     * Test login page is accessible
     *
     * @return void
     */
    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Test user can login with correct credentials
     *
     * @return void
     */
    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test user cannot login with incorrect credentials
     *
     * @return void
     */
    public function test_user_cannot_login_with_incorrect_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test user can logout
     *
     * @return void
     */
    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
