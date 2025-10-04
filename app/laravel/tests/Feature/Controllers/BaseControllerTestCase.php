<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Base test class for controller feature tests
 */
abstract class BaseControllerTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->user = User::factory()->create();
    }

    /**
     * Test that a route requires authentication
     *
     * @param  string  $method  HTTP method
     * @param  string  $route  Route URL
     * @param  array  $data  Optional data for POST/PUT requests
     */
    protected function assertRequiresAuthentication(string $method, string $route, array $data = []): void
    {
        $response = match (strtoupper($method)) {
            'GET' => $this->get($route),
            'POST' => $this->post($route, $data),
            'PUT' => $this->put($route, $data),
            'DELETE' => $this->delete($route),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: $method")
        };

        $response->assertRedirect('/login');
    }

    /**
     * Test that authenticated user can access a route
     *
     * @param  string  $method  HTTP method
     * @param  string  $route  Route URL
     * @param  int  $expectedStatus  Expected HTTP status code
     * @param  array  $data  Optional data for POST/PUT requests
     */
    protected function assertAuthenticatedAccess(string $method, string $route, int $expectedStatus = 200, array $data = []): void
    {
        $response = match (strtoupper($method)) {
            'GET' => $this->actingAs($this->user)->get($route),
            'POST' => $this->actingAs($this->user)->post($route, $data),
            'PUT' => $this->actingAs($this->user)->put($route, $data),
            'DELETE' => $this->actingAs($this->user)->delete($route),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: $method")
        };

        $response->assertStatus($expectedStatus);
    }

    /**
     * Test validation errors for a form request
     *
     * @param  string  $method  HTTP method
     * @param  string  $route  Route URL
     * @param  array  $invalidData  Invalid form data
     * @param  array  $expectedErrors  Expected validation error keys
     */
    protected function assertValidationErrors(string $method, string $route, array $invalidData, array $expectedErrors): void
    {
        $response = match (strtoupper($method)) {
            'POST' => $this->actingAs($this->user)->post($route, $invalidData),
            'PUT' => $this->actingAs($this->user)->put($route, $invalidData),
            default => throw new \InvalidArgumentException('Validation tests only support POST/PUT methods')
        };

        $response->assertSessionHasErrors($expectedErrors);
    }
}
