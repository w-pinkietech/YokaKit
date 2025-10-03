<?php

namespace Tests\Feature\Controllers;

use App\Enums\RoleType;
use App\Models\Process;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessControllerTest extends BaseControllerTest
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create(['role' => RoleType::ADMIN]);
    }

    /**
     * Test process index requires authentication
     */
    public function test_index_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('GET', '/process');
    }

    /**
     * Test authenticated user can access process index
     */
    public function test_index_accessible_when_authenticated(): void
    {
        $this->assertAuthenticatedAccess('GET', '/process');
    }

    /**
     * Test process index displays processes
     */
    public function test_index_displays_processes(): void
    {
        $process = Process::factory()->create(['process_name' => 'Test Process']);

        $response = $this->actingAs($this->user)->get('/process');

        $response->assertStatus(200);
        $response->assertViewIs('process.index');
        $response->assertViewHas('processes');
        $response->assertSee('Test Process');
    }

    /**
     * Test process create page requires authentication
     */
    public function test_create_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('GET', '/process/create');
    }

    /**
     * Test admin user can access process create
     */
    public function test_create_accessible_when_admin(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/process/create');
        $response->assertStatus(200);
    }

    /**
     * Test process store requires authentication
     */
    public function test_store_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('POST', '/process', ['name' => 'Test']);
    }

    /**
     * Test process store with valid data
     */
    public function test_store_with_valid_data(): void
    {
        $validData = [
            'process_name' => 'New Process',
            'plan_color' => '#FF0000',
        ];

        $response = $this->actingAs($this->adminUser)->post('/process', $validData);

        $response->assertRedirect();
        // Note: Success depends on business logic validation
        $this->assertDatabaseHas('processes', ['process_name' => 'New Process']);
    }

    /**
     * Test process store with invalid data
     */
    public function test_store_with_invalid_data(): void
    {
        $invalidData = [
            'process_name' => '', // Required field
        ];

        $response = $this->actingAs($this->adminUser)->post('/process', $invalidData);
        $response->assertSessionHasErrors(['process_name']);
    }

    /**
     * Test process show requires authentication
     */
    public function test_show_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('GET', "/process/{$process->process_id}");
    }

    /**
     * Test authenticated user can view process
     */
    public function test_show_accessible_when_authenticated(): void
    {
        $process = Process::factory()->create();
        $this->assertAuthenticatedAccess('GET', "/process/{$process->process_id}");
    }

    /**
     * Test process show displays correct data
     */
    public function test_show_displays_process_data(): void
    {
        $process = Process::factory()->create(['process_name' => 'Display Process']);

        $response = $this->actingAs($this->user)->get("/process/{$process->process_id}");

        $response->assertStatus(200);
        $response->assertSee('Display Process');
    }

    /**
     * Test process edit requires authentication
     */
    public function test_edit_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('GET', "/process/{$process->process_id}/edit");
    }

    /**
     * Test admin user can edit process
     */
    public function test_edit_accessible_when_admin(): void
    {
        $process = Process::factory()->create();
        $response = $this->actingAs($this->adminUser)->get("/process/{$process->process_id}/edit");
        $response->assertStatus(200);
    }

    /**
     * Test process update requires authentication
     */
    public function test_update_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('PUT', "/process/{$process->process_id}", ['name' => 'Updated']);
    }

    /**
     * Test process update with valid data
     */
    public function test_update_with_valid_data(): void
    {
        $process = Process::factory()->create();
        $updateData = [
            'process_name' => 'Updated Process',
            'plan_color' => '#00FF00',
        ];

        $response = $this->actingAs($this->adminUser)->put("/process/{$process->process_id}", $updateData);

        $response->assertRedirect();
        // Note: Success depends on business logic validation
        $this->assertDatabaseHas('processes', ['process_id' => $process->process_id, 'process_name' => 'Updated Process']);
    }

    /**
     * Test process update with invalid data
     */
    public function test_update_with_invalid_data(): void
    {
        $process = Process::factory()->create();
        $invalidData = [
            'process_name' => '', // Required field
        ];

        $response = $this->actingAs($this->adminUser)->put("/process/{$process->process_id}", $invalidData);
        $response->assertSessionHasErrors(['process_name']);
    }

    /**
     * Test process delete requires authentication
     */
    public function test_destroy_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('DELETE', "/process/{$process->process_id}");
    }

    /**
     * Test process delete by authenticated user
     */
    public function test_destroy_removes_process(): void
    {
        $process = Process::factory()->create();

        $response = $this->actingAs($this->adminUser)->delete("/process/{$process->process_id}");

        $response->assertRedirect();
        // Note: Success depends on business logic validation
        $this->assertDatabaseMissing('processes', ['process_id' => $process->process_id]);
    }

    /**
     * Test process select requires authentication
     */
    public function test_select_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('GET', "/process/{$process->process_id}/select");
    }

    /**
     * Test process switching requires authentication
     */
    public function test_switching_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('PUT', "/process/{$process->process_id}/switching");
    }

    /**
     * Test process stop requires authentication
     */
    public function test_stop_requires_authentication(): void
    {
        $process = Process::factory()->create();
        $this->assertRequiresAuthentication('PUT', "/process/{$process->process_id}/stop");
    }

    /**
     * Test 404 when process not found
     */
    public function test_show_returns_404_when_process_not_found(): void
    {
        $response = $this->actingAs($this->user)->get('/process/99999');
        $response->assertStatus(404);
    }

    /**
     * Test 404 when editing non-existent process
     */
    public function test_edit_returns_404_when_process_not_found(): void
    {
        $response = $this->actingAs($this->user)->get('/process/99999/edit');
        $response->assertStatus(404);
    }
}
