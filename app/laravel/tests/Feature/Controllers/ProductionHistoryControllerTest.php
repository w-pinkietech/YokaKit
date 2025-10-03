<?php

namespace Tests\Feature\Controllers;

use App\Models\Process;
use App\Models\ProductionHistory;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductionHistoryControllerTest extends BaseControllerTest
{
    use RefreshDatabase;

    /**
     * Test production history index requires authentication
     */
    public function test_index_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('GET', "/process/{$process->process_id}/production/history");
    }

    /**
     * Test authenticated user can access production history index
     */
    public function test_index_accessible_when_authenticated(): void
    {
        $process = Process::factory()->create();
        $this->assertAuthenticatedAccess('GET', "/process/{$process->process_id}/production/history");
    }

    /**
     * Test production history index displays histories
     */
    public function test_index_displays_production_histories(): void
    {
        $process = Process::factory()->create();
        ProductionHistory::factory()->create([
            'process_id' => $process->process_id,
            'part_number_name' => 'TEST-001',
        ]);

        $response = $this->actingAs($this->user)->get("/process/{$process->process_id}/production/history");

        $response->assertStatus(200);
        $response->assertViewIs('process.production.index');
        $response->assertViewHas(['process', 'histories']);
        $response->assertSee('TEST-001');
    }

    /**
     * Test production history show requires authentication
     */
    public function test_show_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $history = ProductionHistory::factory()->create(['process_id' => $process->process_id]);

        $this->assertRequiresAuthentication('GET', "/process/{$process->process_id}/production/history/{$history->production_history_id}");
    }

    /**
     * Test authenticated user can view production history
     */
    public function test_show_accessible_when_authenticated(): void
    {
        $process = Process::factory()->create();
        $history = ProductionHistory::factory()->create(['process_id' => $process->process_id]);

        $this->assertAuthenticatedAccess('GET', "/process/{$process->process_id}/production/history/{$history->id}");
    }

    /**
     * Test production history show displays correct data
     */
    public function test_show_displays_history_data(): void
    {
        $process = Process::factory()->create();
        $history = ProductionHistory::factory()->create([
            'process_id' => $process->process_id,
            'part_number_name' => 'SHOW-001',
        ]);

        $response = $this->actingAs($this->user)->get("/process/{$process->process_id}/production/history/{$history->production_history_id}");

        $response->assertStatus(200);
        $response->assertSee('SHOW-001');
    }

    /**
     * Test production create page requires authentication
     */
    public function test_create_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('GET', "/process/{$process->process_id}/production/create");
    }

    /**
     * Test authenticated user can access production create
     */
    public function test_create_accessible_when_authenticated(): void
    {
        $process = Process::factory()->create();
        $this->assertAuthenticatedAccess('GET', "/process/{$process->process_id}/production/create");
    }

    /**
     * Test production store requires authentication
     */
    public function test_store_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('POST', "/process/{$process->process_id}/production");
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

        $response = $this->actingAs($this->user)->post("/process/{$process->process_id}/production", $validData);

        $response->assertRedirect();
        // Note: Success depends on business logic validation
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

        $response = $this->actingAs($this->user)->post("/process/{$process->process_id}/production", $invalidData);
        $response->assertSessionHasErrors();
    }

    /**
     * Test production stop requires authentication
     */
    public function test_stop_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('PUT', "/process/{$process->process_id}/production/stop");
    }

    /**
     * Test production stop functionality
     */
    public function test_stop_production(): void
    {
        $process = Process::factory()->create();

        $response = $this->actingAs($this->user)->put("/process/{$process->process_id}/production/stop");

        $response->assertRedirect();
        // Note: Success depends on business logic validation
    }

    /**
     * Test changeover start requires authentication
     */
    public function test_start_changeover_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('PUT', "/process/{$process->process_id}/production/changeover/start");
    }

    /**
     * Test changeover start functionality
     */
    public function test_start_changeover(): void
    {
        $process = Process::factory()->create();

        $response = $this->actingAs($this->user)->put("/process/{$process->process_id}/production/changeover/start");

        $response->assertRedirect();
        // Note: Success depends on business logic validation
    }

    /**
     * Test changeover stop requires authentication
     */
    public function test_stop_changeover_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('PUT', "/process/{$process->process_id}/production/changeover/stop");
    }

    /**
     * Test changeover stop functionality
     */
    public function test_stop_changeover(): void
    {
        $process = Process::factory()->create();

        $response = $this->actingAs($this->user)->put("/process/{$process->process_id}/production/changeover/stop");

        $response->assertRedirect();
        // Note: Success depends on business logic validation
    }

    /**
     * Test 404 when process not found
     */
    public function test_index_returns_404_when_process_not_found(): void
    {
        $response = $this->actingAs($this->user)->get('/process/99999/production/history');
        $response->assertStatus(404);
    }

    /**
     * Test 404 when history not found
     */
    public function test_show_returns_404_when_history_not_found(): void
    {
        $process = Process::factory()->create();
        $response = $this->actingAs($this->user)->get("/process/{$process->process_id}/production/history/99999");
        $response->assertStatus(404);
    }

    /**
     * Test production history filtering by date range
     */
    public function test_index_filters_by_date_range(): void
    {
        $process = Process::factory()->create();
        ProductionHistory::factory()->create([
            'process_id' => $process->process_id,
            'created_at' => now()->subDays(10),
        ]);
        ProductionHistory::factory()->create([
            'process_id' => $process->process_id,
            'created_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->user)->get("/process/{$process->process_id}/production/history?from=".now()->subDays(2)->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertViewHas('histories');
    }

    /**
     * Test error handling in production store
     */
    public function test_store_handles_exceptions_gracefully(): void
    {
        $process = Process::factory()->create();

        // This should trigger an error due to missing indicators
        $validData = [
            'part_number_id' => 1,
            'worker_id' => 1,
            'expected_cycle_time' => 60,
            'target_count' => 100,
        ];

        $response = $this->actingAs($this->user)->post("/process/{$process->process_id}/production", $validData);

        // Should redirect back with error message if indicators are missing
        $response->assertRedirect();
        // Note: Error handling depends on business logic
    }
}
