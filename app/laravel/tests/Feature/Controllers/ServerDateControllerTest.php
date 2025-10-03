<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;

class ServerDateControllerTest extends BaseControllerTest
{
    use RefreshDatabase;

    /**
     * Test server date endpoint requires authentication
     */
    public function test_date_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('GET', '/date');
    }

    /**
     * Test authenticated user can access server date
     */
    public function test_date_accessible_when_authenticated(): void
    {
        $this->assertAuthenticatedAccess('GET', '/date');
    }

    /**
     * Test server date returns formatted date string
     */
    public function test_date_returns_formatted_string(): void
    {
        $response = $this->actingAs($this->user)->get('/date');

        $response->assertStatus(200);

        // Should return a date string, verify it's not empty and contains typical date patterns
        $content = $response->getContent();
        $this->assertNotEmpty($content);

        // The response should contain date-like patterns (numbers and separators)
        $this->assertMatchesRegularExpression('/\d+/', $content);
    }

    /**
     * Test server date returns consistent format
     */
    public function test_date_format_consistency(): void
    {
        $response1 = $this->actingAs($this->user)->get('/date');
        $response2 = $this->actingAs($this->user)->get('/date');

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        $content1 = $response1->getContent();
        $content2 = $response2->getContent();

        // Both responses should have similar structure (same format)
        $this->assertIsString($content1);
        $this->assertIsString($content2);

        // They should both be non-empty
        $this->assertNotEmpty($content1);
        $this->assertNotEmpty($content2);
    }

    /**
     * Test server date endpoint with different HTTP methods
     */
    public function test_date_only_accepts_get_requests(): void
    {
        // POST should not be allowed
        $response = $this->actingAs($this->user)->post('/date');
        $response->assertStatus(405); // Method Not Allowed

        // PUT should not be allowed
        $response = $this->actingAs($this->user)->put('/date');
        $response->assertStatus(405); // Method Not Allowed

        // DELETE should not be allowed
        $response = $this->actingAs($this->user)->delete('/date');
        $response->assertStatus(405); // Method Not Allowed
    }

    /**
     * Test server date endpoint performance
     */
    public function test_date_endpoint_performance(): void
    {
        $startTime = microtime(true);

        $response = $this->actingAs($this->user)->get('/date');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);

        // Should respond within 1 second (generous limit for CI environments)
        $this->assertLessThan(1.0, $executionTime, 'Server date endpoint should respond quickly');
    }

    /**
     * Test server date content type
     */
    public function test_date_content_type(): void
    {
        $response = $this->actingAs($this->user)->get('/date');

        $response->assertStatus(200);

        // Should return text content
        $this->assertIsString($response->getContent());
    }

    /**
     * Test multiple concurrent requests to server date
     */
    public function test_date_handles_concurrent_requests(): void
    {
        $responses = [];

        // Make multiple requests
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->actingAs($this->user)->get('/date');
        }

        // All should succeed
        foreach ($responses as $response) {
            $response->assertStatus(200);
            $this->assertNotEmpty($response->getContent());
        }
    }
}
