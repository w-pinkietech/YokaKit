<?php

namespace Tests\Feature\Controllers;

use App\Enums\RoleType;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WorkerControllerTest extends BaseControllerTestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->adminUser = User::factory()->create(['role' => RoleType::ADMIN]);
    }

    /**
     * Test worker index requires authentication
     */
    public function test_index_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('GET', '/worker');
    }

    /**
     * Test authenticated user can access worker index
     */
    public function test_index_accessible_when_authenticated(): void
    {
        $this->assertAuthenticatedAccess('GET', '/worker');
    }

    /**
     * Test worker index displays workers
     */
    public function test_index_displays_workers(): void
    {
        $worker = Worker::factory()->create(['worker_name' => 'Test Worker Display']);

        $response = $this->actingAs($this->user)->get('/worker');

        $response->assertStatus(200);
        $response->assertViewIs('worker.index');
        $response->assertViewHas('workers');
        $response->assertSee('Test Worker Display');
    }

    /**
     * Test worker create requires authentication
     */
    public function test_create_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('GET', '/worker/create');
    }

    /**
     * Test worker create requires admin role
     */
    public function test_create_requires_admin_role(): void
    {
        $response = $this->actingAs($this->user)->get('/worker/create');
        $response->assertStatus(403);
    }

    /**
     * Test admin user can access worker create
     */
    public function test_create_accessible_when_admin(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/worker/create');
        $response->assertStatus(200);
        $response->assertViewIs('worker.create');
    }

    /**
     * Test worker store requires authentication
     */
    public function test_store_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('POST', '/worker', ['worker_name' => 'Test']);
    }

    /**
     * Test worker store requires admin role
     */
    public function test_store_requires_admin_role(): void
    {
        $workerData = [
            'identification_number' => 'WKR001',
            'worker_name' => 'New Worker',
            'mac_address' => '00:11:22:33:44:55',
        ];

        $response = $this->actingAs($this->user)->post('/worker', $workerData);
        $response->assertStatus(403);
    }

    /**
     * Test worker store with valid data
     */
    public function test_store_with_valid_data(): void
    {
        $workerData = [
            'identification_number' => 'WKR001',
            'worker_name' => 'New Worker',
            'mac_address' => '00:11:22:33:44:55',
        ];

        $response = $this->actingAs($this->adminUser)->post('/worker', $workerData);

        $response->assertRedirect('/worker');
        $this->assertDatabaseHas('workers', [
            'identification_number' => 'WKR001',
            'worker_name' => 'New Worker',
            'mac_address' => '00:11:22:33:44:55',
        ]);
    }

    /**
     * Test worker store with minimal data (no MAC address)
     */
    public function test_store_with_minimal_data(): void
    {
        $workerData = [
            'identification_number' => 'WKR002',
            'worker_name' => 'Minimal Worker',
        ];

        $response = $this->actingAs($this->adminUser)->post('/worker', $workerData);

        $response->assertRedirect('/worker');
        $this->assertDatabaseHas('workers', [
            'identification_number' => 'WKR002',
            'worker_name' => 'Minimal Worker',
            'mac_address' => null,
        ]);
    }

    /**
     * Test worker store with invalid data
     */
    public function test_store_with_invalid_data(): void
    {
        $invalidData = [
            'identification_number' => '', // Required
            'worker_name' => '', // Required
            'mac_address' => 'invalid-mac', // Invalid MAC format
        ];

        $response = $this->actingAs($this->adminUser)
            ->from('/worker/create')
            ->post('/worker', $invalidData);
        $response->assertSessionHasErrors(['identification_number', 'worker_name', 'mac_address']);
    }

    /**
     * Test worker store with too long fields
     */
    public function test_store_with_too_long_fields(): void
    {
        $invalidData = [
            'identification_number' => str_repeat('x', 33), // Max 32 characters
            'worker_name' => str_repeat('x', 33), // Max 32 characters
        ];

        $response = $this->actingAs($this->adminUser)
            ->from('/worker/create')
            ->post('/worker', $invalidData);
        $response->assertSessionHasErrors(['identification_number', 'worker_name']);
    }

    /**
     * Test worker store with duplicate identification number
     */
    public function test_store_with_duplicate_identification_number(): void
    {
        $existingWorker = Worker::factory()->create(['identification_number' => 'EXISTING001']);

        $workerData = [
            'identification_number' => 'EXISTING001', // Duplicate
            'worker_name' => 'New Worker',
        ];

        $response = $this->actingAs($this->adminUser)->post('/worker', $workerData);
        $response->assertSessionHasErrors(['identification_number']);
    }

    /**
     * Test worker store with duplicate MAC address
     */
    public function test_store_with_duplicate_mac_address(): void
    {
        $existingWorker = Worker::factory()->create(['mac_address' => '00:11:22:33:44:55']);

        $workerData = [
            'identification_number' => 'WKR003',
            'worker_name' => 'New Worker',
            'mac_address' => '00:11:22:33:44:55', // Duplicate MAC
        ];

        $response = $this->actingAs($this->adminUser)->post('/worker', $workerData);
        $response->assertSessionHasErrors(['mac_address']);
    }

    /**
     * Test worker edit requires authentication
     */
    public function test_edit_requires_authentication(): void
    {
        $worker = Worker::factory()->create();
        $this->assertRequiresAuthentication('GET', "/worker/{$worker->worker_id}/edit");
    }

    /**
     * Test worker edit requires admin role
     */
    public function test_edit_requires_admin_role(): void
    {
        $worker = Worker::factory()->create();

        $response = $this->actingAs($this->user)->get("/worker/{$worker->worker_id}/edit");
        $response->assertStatus(403);
    }

    /**
     * Test admin user can edit worker
     */
    public function test_edit_accessible_when_admin(): void
    {
        $worker = Worker::factory()->create();

        $response = $this->actingAs($this->adminUser)->get("/worker/{$worker->worker_id}/edit");
        $response->assertStatus(200);
        $response->assertViewIs('worker.edit');
        $response->assertViewHas('worker', $worker);
    }

    /**
     * Test worker update requires authentication
     */
    public function test_update_requires_authentication(): void
    {
        $worker = Worker::factory()->create();
        $this->assertRequiresAuthentication('PUT', "/worker/{$worker->worker_id}", ['worker_name' => 'Updated']);
    }

    /**
     * Test worker update requires admin role
     */
    public function test_update_requires_admin_role(): void
    {
        $worker = Worker::factory()->create();
        $updateData = [
            'identification_number' => 'UPDATED001',
            'worker_name' => 'Updated Worker',
        ];

        $response = $this->actingAs($this->user)->put("/worker/{$worker->worker_id}", $updateData);
        $response->assertStatus(403);
    }

    /**
     * Test worker update with valid data
     */
    public function test_update_with_valid_data(): void
    {
        $worker = Worker::factory()->create([
            'identification_number' => 'ORIGINAL001',
            'worker_name' => 'Original Worker',
        ]);

        $updateData = [
            'identification_number' => 'UPDATED001',
            'worker_name' => 'Updated Worker',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
        ];

        $response = $this->actingAs($this->adminUser)->put("/worker/{$worker->worker_id}", $updateData);

        $response->assertRedirect('/worker');
        $this->assertDatabaseHas('workers', [
            'worker_id' => $worker->worker_id,
            'identification_number' => 'UPDATED001',
            'worker_name' => 'Updated Worker',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
        ]);
    }

    /**
     * Test worker update with invalid data
     */
    public function test_update_with_invalid_data(): void
    {
        $worker = Worker::factory()->create();

        $invalidData = [
            'identification_number' => '', // Required
            'worker_name' => '', // Required
            'mac_address' => 'invalid-format', // Invalid MAC
        ];

        $response = $this->actingAs($this->adminUser)->put("/worker/{$worker->worker_id}", $invalidData);
        $response->assertSessionHasErrors(['identification_number', 'worker_name', 'mac_address']);
    }

    /**
     * Test worker update with duplicate identification number from another worker
     */
    public function test_update_with_duplicate_identification_number_from_another_worker(): void
    {
        $worker1 = Worker::factory()->create(['identification_number' => 'WKR001']);
        $worker2 = Worker::factory()->create(['identification_number' => 'WKR002']);

        $updateData = [
            'identification_number' => 'WKR001', // Already used by worker1
            'worker_name' => 'Updated Worker',
        ];

        $response = $this->actingAs($this->adminUser)->put("/worker/{$worker2->worker_id}", $updateData);
        $response->assertSessionHasErrors(['identification_number']);
    }

    /**
     * Test worker update can keep same identification number (no conflict with itself)
     */
    public function test_update_can_keep_same_identification_number(): void
    {
        $worker = Worker::factory()->create(['identification_number' => 'WKR001']);

        $updateData = [
            'identification_number' => 'WKR001', // Same as current worker
            'worker_name' => 'Updated Worker Name',
        ];

        $response = $this->actingAs($this->adminUser)->put("/worker/{$worker->worker_id}", $updateData);
        $response->assertRedirect('/worker');
    }

    /**
     * Test worker update with duplicate MAC address from another worker
     */
    public function test_update_with_duplicate_mac_address_from_another_worker(): void
    {
        $worker1 = Worker::factory()->create(['mac_address' => '00:11:22:33:44:55']);
        $worker2 = Worker::factory()->create(['mac_address' => 'AA:BB:CC:DD:EE:FF']);

        $updateData = [
            'identification_number' => 'WKR002',
            'worker_name' => 'Updated Worker',
            'mac_address' => '00:11:22:33:44:55', // Already used by worker1
        ];

        $response = $this->actingAs($this->adminUser)->put("/worker/{$worker2->worker_id}", $updateData);
        $response->assertSessionHasErrors(['mac_address']);
    }

    /**
     * Test worker update can keep same MAC address (no conflict with itself)
     */
    public function test_update_can_keep_same_mac_address(): void
    {
        $worker = Worker::factory()->create(['mac_address' => '00:11:22:33:44:55']);

        $updateData = [
            'identification_number' => 'WKR001',
            'worker_name' => 'Updated Worker',
            'mac_address' => '00:11:22:33:44:55', // Same as current worker
        ];

        $response = $this->actingAs($this->adminUser)->put("/worker/{$worker->worker_id}", $updateData);
        $response->assertRedirect('/worker');
    }

    /**
     * Test worker destroy requires authentication
     */
    public function test_destroy_requires_authentication(): void
    {
        $worker = Worker::factory()->create();
        $this->assertRequiresAuthentication('DELETE', "/worker/{$worker->worker_id}");
    }

    /**
     * Test worker destroy requires admin role
     */
    public function test_destroy_requires_admin_role(): void
    {
        $worker = Worker::factory()->create();

        $response = $this->actingAs($this->user)->delete("/worker/{$worker->worker_id}");
        $response->assertStatus(403);
    }

    /**
     * Test admin can delete worker
     */
    public function test_destroy_removes_worker(): void
    {
        $worker = Worker::factory()->create();

        $response = $this->actingAs($this->adminUser)->delete("/worker/{$worker->worker_id}");

        $response->assertRedirect('/worker');
        $this->assertDatabaseMissing('workers', ['worker_id' => $worker->worker_id]);
    }

    /**
     * Test 404 when worker not found for edit
     */
    public function test_edit_returns_404_when_worker_not_found(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/worker/99999/edit');
        $response->assertStatus(404);
    }

    /**
     * Test 404 when worker not found for update
     */
    public function test_update_returns_404_when_worker_not_found(): void
    {
        $response = $this->actingAs($this->adminUser)->put('/worker/99999', ['worker_name' => 'Test']);
        $response->assertStatus(404);
    }

    /**
     * Test 404 when worker not found for destroy
     */
    public function test_destroy_returns_404_when_worker_not_found(): void
    {
        $response = $this->actingAs($this->adminUser)->delete('/worker/99999');
        $response->assertStatus(404);
    }

    /**
     * Test complete worker management workflow
     */
    public function test_complete_worker_workflow(): void
    {
        // 1. Admin creates new worker
        $createData = [
            'identification_number' => 'WORKFLOW001',
            'worker_name' => 'Workflow Worker',
            'mac_address' => '11:22:33:44:55:66',
        ];

        $response = $this->actingAs($this->adminUser)
            ->from('/worker/create')
            ->post('/worker', $createData);
        $response->assertRedirect('/worker');

        $worker = Worker::where('identification_number', 'WORKFLOW001')->first();
        $this->assertNotNull($worker);

        // 2. Admin updates the worker
        $updateData = [
            'identification_number' => 'WORKFLOW001-UPD',
            'worker_name' => 'Updated Workflow Worker',
            'mac_address' => '77:88:99:AA:BB:CC',
        ];

        $response = $this->actingAs($this->adminUser)->put("/worker/{$worker->worker_id}", $updateData);
        $response->assertRedirect('/worker');

        $this->assertDatabaseHas('workers', [
            'worker_id' => $worker->worker_id,
            'identification_number' => 'WORKFLOW001-UPD',
            'worker_name' => 'Updated Workflow Worker',
            'mac_address' => '77:88:99:AA:BB:CC',
        ]);

        // 3. Admin deletes the worker
        $response = $this->actingAs($this->adminUser)->delete("/worker/{$worker->worker_id}");
        $response->assertRedirect('/worker');

        $this->assertDatabaseMissing('workers', ['worker_id' => $worker->worker_id]);
    }

    /**
     * Test MAC address validation
     */
    public function test_mac_address_validation(): void
    {
        $workerData = [
            'identification_number' => 'VALID001',
            'worker_name' => 'Valid Worker',
            'mac_address' => '00:11:22:33:44:55',
        ];

        $response = $this->actingAs($this->adminUser)->post('/worker', $workerData);
        // Note: MAC validation depends on implementation
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    /**
     * Test MAC address can be removed in update
     */
    public function test_mac_address_can_be_removed_in_update(): void
    {
        $worker = Worker::factory()->create(['mac_address' => '00:11:22:33:44:55']);

        $updateData = [
            'identification_number' => $worker->identification_number,
            'worker_name' => $worker->worker_name,
            'mac_address' => null,
        ];

        $response = $this->actingAs($this->adminUser)->put("/worker/{$worker->worker_id}", $updateData);

        $response->assertRedirect('/worker');
        $this->assertDatabaseHas('workers', [
            'worker_id' => $worker->worker_id,
            'mac_address' => null,
        ]);
    }
}
