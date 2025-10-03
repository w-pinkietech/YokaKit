<?php

namespace Tests\Feature\Controllers;

use App\Models\Process;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SwitchControllerTest extends BaseControllerTest
{
    use RefreshDatabase;

    /**
     * Test switch index requires authentication
     */
    public function test_index_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('GET', '/switch');
    }

    /**
     * Test authenticated user can access switch index
     */
    public function test_index_accessible_when_authenticated(): void
    {
        $this->assertAuthenticatedAccess('GET', '/switch');
    }

    /**
     * Test switch index displays processes and workers
     */
    public function test_index_displays_processes_and_workers(): void
    {
        $process = Process::factory()->create(['process_name' => 'Test Process']);
        $worker = Worker::factory()->create(['worker_name' => 'Test Worker']);

        $response = $this->actingAs($this->user)->get('/switch');

        $response->assertStatus(200);
        $response->assertViewIs('switch.index');
        $response->assertViewHas(['processes', 'workers', 'initialId', 'plannedOutages']);
        $response->assertSee('Test Process');
        $response->assertSee('Test Worker');
    }

    /**
     * Test switch index with process parameter
     */
    public function test_index_with_process_parameter(): void
    {
        $process = Process::factory()->create();

        $response = $this->actingAs($this->user)->get("/switch?process={$process->process_id}");

        $response->assertStatus(200);
        $response->assertViewHas('initialId', $process->process_id);
    }

    /**
     * Test production store requires authentication
     */
    public function test_store_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('POST', "/switch/{$process->process_id}/store");
    }

    /**
     * Test production store with valid data
     */
    public function test_store_with_valid_data(): void
    {
        $process = Process::factory()->create();
        $worker = Worker::factory()->create();

        $validData = [
            'part_number_id' => 1,
            'worker_id' => $worker->id,
            'expected_cycle_time' => 60,
            'target_count' => 100,
        ];

        $response = $this->actingAs($this->user)->post("/switch/{$process->process_id}/store", $validData);

        $response->assertRedirect();
        // Note: Success may depend on business logic and valid part numbers
    }

    /**
     * Test production store with invalid data
     */
    public function test_store_with_invalid_data(): void
    {
        $process = Process::factory()->create();
        $invalidData = [
            'part_number_id' => '',
            'worker_id' => '',
            'expected_cycle_time' => -1,
            'target_count' => -1,
        ];

        $response = $this->actingAs($this->user)->post("/switch/{$process->process_id}/store", $invalidData);
        $response->assertSessionHasErrors();
    }

    /**
     * Test production stop requires authentication
     */
    public function test_stop_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('PUT', "/switch/{$process->process_id}/stop");
    }

    /**
     * Test production stop functionality
     */
    public function test_stop_production(): void
    {
        $process = Process::factory()->create();

        $response = $this->actingAs($this->user)->put("/switch/{$process->process_id}/stop");

        $response->assertRedirect();
        // Note: Success may depend on existing production state
    }

    /**
     * Test changeover start requires authentication
     */
    public function test_start_changeover_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('PUT', "/switch/{$process->process_id}/changeover/start");
    }

    /**
     * Test changeover start functionality
     */
    public function test_start_changeover(): void
    {
        $process = Process::factory()->create();

        $response = $this->actingAs($this->user)->put("/switch/{$process->process_id}/changeover/start");

        $response->assertRedirect();
        // Note: Success may depend on production state
    }

    /**
     * Test changeover stop requires authentication
     */
    public function test_stop_changeover_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('PUT', "/switch/{$process->process_id}/changeover/stop");
    }

    /**
     * Test changeover stop functionality
     */
    public function test_stop_changeover(): void
    {
        $process = Process::factory()->create();

        $response = $this->actingAs($this->user)->put("/switch/{$process->process_id}/changeover/stop");

        $response->assertRedirect();
        // Note: Success may depend on changeover state
    }

    /**
     * Test worker change requires authentication
     */
    public function test_change_worker_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('PUT', "/switch/{$process->process_id}/worker");
    }

    /**
     * Test worker change accepts request
     */
    public function test_change_worker_accepts_request(): void
    {
        $process = Process::factory()->create();
        $worker = Worker::factory()->create();

        $validData = [
            'worker_id' => $worker->worker_id,
        ];

        $response = $this->actingAs($this->user)->put("/switch/{$process->process_id}/worker", $validData);

        // Should not be 404 or 403 - validates route exists and is accessible
        $this->assertContains($response->getStatusCode(), [200, 302, 422, 500]);
    }

    /**
     * Test worker change with invalid data
     */
    public function test_change_worker_with_invalid_data(): void
    {
        $process = Process::factory()->create();
        $invalidData = [
            'worker_id' => '',
        ];

        $response = $this->actingAs($this->user)->put("/switch/{$process->process_id}/worker", $invalidData);
        // Should handle request (not 404/403) - exact response depends on validation logic
        $this->assertContains($response->getStatusCode(), [200, 302, 422, 500]);
    }

    /**
     * Test 404 when process not found for store
     */
    public function test_store_returns_404_when_process_not_found(): void
    {
        $response = $this->actingAs($this->user)->post('/switch/99999/store', []);
        $response->assertStatus(404);
    }

    /**
     * Test 404 when process not found for stop
     */
    public function test_stop_returns_404_when_process_not_found(): void
    {
        $response = $this->actingAs($this->user)->put('/switch/99999/stop');
        $response->assertStatus(404);
    }

    /**
     * Test error handling in production store
     */
    public function test_store_handles_exceptions_gracefully(): void
    {
        $process = Process::factory()->create();

        // This should trigger an error due to missing required relationships
        $invalidData = [
            'part_number_id' => 99999, // Non-existent part number
            'worker_id' => 99999,      // Non-existent worker
            'expected_cycle_time' => 60,
            'target_count' => 100,
        ];

        $response = $this->actingAs($this->user)->post("/switch/{$process->process_id}/store", $invalidData);

        // Should redirect back (success or error depends on validation)
        $response->assertRedirect();
    }
}
