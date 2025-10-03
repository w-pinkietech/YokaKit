<?php

namespace Tests\Feature\Controllers;

use App\Enums\RoleType;
use App\Models\PartNumber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PartNumberControllerTest extends BaseControllerTest
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create(['role' => RoleType::ADMIN]);
    }

    /**
     * Test part number index requires authentication
     */
    public function test_index_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('GET', '/part-number');
    }

    /**
     * Test authenticated user can access part number index
     */
    public function test_index_accessible_when_authenticated(): void
    {
        $this->assertAuthenticatedAccess('GET', '/part-number');
    }

    /**
     * Test part number index displays part numbers
     */
    public function test_index_displays_part_numbers(): void
    {
        $partNumber = PartNumber::factory()->create(['part_number_name' => 'TEST-PART-001']);

        $response = $this->actingAs($this->user)->get('/part-number');

        $response->assertStatus(200);
        $response->assertViewIs('part-number.index');
        $response->assertViewHas('partNumbers');
        $response->assertSee('TEST-PART-001');
    }

    /**
     * Test part number create requires authentication
     */
    public function test_create_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('GET', '/part-number/create');
    }

    /**
     * Test part number create requires admin role
     */
    public function test_create_requires_admin_role(): void
    {
        $response = $this->actingAs($this->user)->get('/part-number/create');
        $response->assertStatus(403);
    }

    /**
     * Test admin user can access part number create
     */
    public function test_create_accessible_when_admin(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/part-number/create');
        $response->assertStatus(200);
        $response->assertViewIs('part-number.create');
    }

    /**
     * Test part number store requires authentication
     */
    public function test_store_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('POST', '/part-number', ['part_number_name' => 'Test']);
    }

    /**
     * Test part number store requires admin role
     */
    public function test_store_requires_admin_role(): void
    {
        $partNumberData = [
            'part_number_name' => 'NEW-PART-001',
            'barcode' => '1234567890',
            'remark' => 'Test part number',
        ];

        $response = $this->actingAs($this->user)->post('/part-number', $partNumberData);
        $response->assertStatus(403);
    }

    /**
     * Test part number store with valid data
     */
    public function test_store_with_valid_data(): void
    {
        $partNumberData = [
            'part_number_name' => 'NEW-PART-001',
            'barcode' => '1234567890',
            'remark' => 'Test part number for manufacturing',
        ];

        $response = $this->actingAs($this->adminUser)->post('/part-number', $partNumberData);

        $response->assertRedirect('/part-number');
        $this->assertDatabaseHas('part_numbers', [
            'part_number_name' => 'NEW-PART-001',
            'barcode' => '1234567890',
            'remark' => 'Test part number for manufacturing',
        ]);
    }

    /**
     * Test part number store with minimal data (only required fields)
     */
    public function test_store_with_minimal_data(): void
    {
        $partNumberData = [
            'part_number_name' => 'MINIMAL-PART-001',
        ];

        $response = $this->actingAs($this->adminUser)->post('/part-number', $partNumberData);

        $response->assertRedirect('/part-number');
        $this->assertDatabaseHas('part_numbers', [
            'part_number_name' => 'MINIMAL-PART-001',
        ]);
    }

    /**
     * Test part number store with invalid data
     */
    public function test_store_with_invalid_data(): void
    {
        $invalidData = [
            'part_number_name' => '', // Required field
            'barcode' => str_repeat('x', 65), // Too long (max 64)
            'remark' => str_repeat('x', 257), // Too long (max 256)
        ];

        $response = $this->actingAs($this->adminUser)->post('/part-number', $invalidData);
        $response->assertSessionHasErrors(['part_number_name', 'barcode', 'remark']);
    }

    /**
     * Test part number store with duplicate name
     */
    public function test_store_with_duplicate_name(): void
    {
        $existingPartNumber = PartNumber::factory()->create(['part_number_name' => 'EXISTING-PART']);

        $partNumberData = [
            'part_number_name' => 'EXISTING-PART', // Duplicate name
            'barcode' => '9876543210',
        ];

        $response = $this->actingAs($this->adminUser)->post('/part-number', $partNumberData);
        $response->assertSessionHasErrors(['part_number_name']);
    }

    /**
     * Test part number store with duplicate barcode
     */
    public function test_store_with_duplicate_barcode(): void
    {
        $existingPartNumber = PartNumber::factory()->create(['barcode' => 'EXISTING-BARCODE']);

        $partNumberData = [
            'part_number_name' => 'NEW-PART-002',
            'barcode' => 'EXISTING-BARCODE', // Duplicate barcode
        ];

        $response = $this->actingAs($this->adminUser)->post('/part-number', $partNumberData);
        $response->assertSessionHasErrors(['barcode']);
    }

    /**
     * Test part number edit requires authentication
     */
    public function test_edit_requires_authentication(): void
    {
        $partNumber = PartNumber::factory()->create();
        $this->assertRequiresAuthentication('GET', "/part-number/{$partNumber->part_number_id}/edit");
    }

    /**
     * Test part number edit requires admin role
     */
    public function test_edit_requires_admin_role(): void
    {
        $partNumber = PartNumber::factory()->create();

        $response = $this->actingAs($this->user)->get("/part-number/{$partNumber->part_number_id}/edit");
        $response->assertStatus(403);
    }

    /**
     * Test admin user can edit part number
     */
    public function test_edit_accessible_when_admin(): void
    {
        $partNumber = PartNumber::factory()->create();

        $response = $this->actingAs($this->adminUser)->get("/part-number/{$partNumber->part_number_id}/edit");
        $response->assertStatus(200);
        $response->assertViewIs('part-number.edit');
        $response->assertViewHas('partNumber', $partNumber);
    }

    /**
     * Test part number update requires authentication
     */
    public function test_update_requires_authentication(): void
    {
        $partNumber = PartNumber::factory()->create();
        $this->assertRequiresAuthentication('PUT', "/part-number/{$partNumber->part_number_id}", ['part_number_name' => 'Updated']);
    }

    /**
     * Test part number update requires admin role
     */
    public function test_update_requires_admin_role(): void
    {
        $partNumber = PartNumber::factory()->create();
        $updateData = [
            'part_number_name' => 'UPDATED-PART',
            'barcode' => 'UPDATED-BARCODE',
        ];

        $response = $this->actingAs($this->user)->put("/part-number/{$partNumber->part_number_id}", $updateData);
        $response->assertStatus(403);
    }

    /**
     * Test part number update with valid data
     */
    public function test_update_with_valid_data(): void
    {
        $partNumber = PartNumber::factory()->create([
            'part_number_name' => 'ORIGINAL-PART',
            'barcode' => 'ORIGINAL-BARCODE',
        ]);

        $updateData = [
            'part_number_name' => 'UPDATED-PART',
            'barcode' => 'UPDATED-BARCODE',
            'remark' => 'Updated remark',
        ];

        $response = $this->actingAs($this->adminUser)->put("/part-number/{$partNumber->part_number_id}", $updateData);

        $response->assertRedirect('/part-number');
        $this->assertDatabaseHas('part_numbers', [
            'part_number_id' => $partNumber->part_number_id,
            'part_number_name' => 'UPDATED-PART',
            'barcode' => 'UPDATED-BARCODE',
            'remark' => 'Updated remark',
        ]);
    }

    /**
     * Test part number update with invalid data
     */
    public function test_update_with_invalid_data(): void
    {
        $partNumber = PartNumber::factory()->create();

        $invalidData = [
            'part_number_name' => '', // Required field
            'barcode' => str_repeat('x', 65), // Too long
            'remark' => str_repeat('x', 257), // Too long
        ];

        $response = $this->actingAs($this->adminUser)->put("/part-number/{$partNumber->part_number_id}", $invalidData);
        $response->assertSessionHasErrors(['part_number_name', 'barcode', 'remark']);
    }

    /**
     * Test part number update with duplicate name from another part
     */
    public function test_update_with_duplicate_name_from_another_part(): void
    {
        $partNumber1 = PartNumber::factory()->create(['part_number_name' => 'PART-001']);
        $partNumber2 = PartNumber::factory()->create(['part_number_name' => 'PART-002']);

        $updateData = [
            'part_number_name' => 'PART-001', // Name already used by partNumber1
        ];

        $response = $this->actingAs($this->adminUser)->put("/part-number/{$partNumber2->part_number_id}", $updateData);
        $response->assertSessionHasErrors(['part_number_name']);
    }

    /**
     * Test part number update can keep same name (no conflict with itself)
     */
    public function test_update_can_keep_same_name(): void
    {
        $partNumber = PartNumber::factory()->create(['part_number_name' => 'PART-001']);

        $updateData = [
            'part_number_name' => 'PART-001', // Same name as current part
            'remark' => 'Updated remark only',
        ];

        $response = $this->actingAs($this->adminUser)->put("/part-number/{$partNumber->part_number_id}", $updateData);
        $response->assertRedirect('/part-number');
    }

    /**
     * Test part number destroy requires authentication
     */
    public function test_destroy_requires_authentication(): void
    {
        $partNumber = PartNumber::factory()->create();
        $this->assertRequiresAuthentication('DELETE', "/part-number/{$partNumber->part_number_id}");
    }

    /**
     * Test part number destroy requires admin role
     */
    public function test_destroy_requires_admin_role(): void
    {
        $partNumber = PartNumber::factory()->create();

        $response = $this->actingAs($this->user)->delete("/part-number/{$partNumber->part_number_id}");
        $response->assertStatus(403);
    }

    /**
     * Test admin can delete part number
     */
    public function test_destroy_removes_part_number(): void
    {
        $partNumber = PartNumber::factory()->create();

        $response = $this->actingAs($this->adminUser)->delete("/part-number/{$partNumber->part_number_id}");

        $response->assertRedirect('/part-number');
        $this->assertDatabaseMissing('part_numbers', ['part_number_id' => $partNumber->part_number_id]);
    }

    /**
     * Test 404 when part number not found for edit
     */
    public function test_edit_returns_404_when_part_number_not_found(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/part-number/99999/edit');
        $response->assertStatus(404);
    }

    /**
     * Test 404 when part number not found for update
     */
    public function test_update_returns_404_when_part_number_not_found(): void
    {
        $response = $this->actingAs($this->adminUser)->put('/part-number/99999', ['part_number_name' => 'Test']);
        $response->assertStatus(404);
    }

    /**
     * Test 404 when part number not found for destroy
     */
    public function test_destroy_returns_404_when_part_number_not_found(): void
    {
        $response = $this->actingAs($this->adminUser)->delete('/part-number/99999');
        $response->assertStatus(404);
    }

    /**
     * Test complete part number management workflow
     */
    public function test_complete_part_number_workflow(): void
    {
        // 1. Admin creates new part number
        $createData = [
            'part_number_name' => 'WORKFLOW-PART-001',
            'barcode' => 'WF001234567890',
            'remark' => 'Part for workflow testing',
        ];

        $response = $this->actingAs($this->adminUser)->post('/part-number', $createData);
        $response->assertRedirect('/part-number');

        $partNumber = PartNumber::where('part_number_name', 'WORKFLOW-PART-001')->first();
        $this->assertNotNull($partNumber);

        // 2. Admin updates the part number
        $updateData = [
            'part_number_name' => 'WORKFLOW-PART-001-UPDATED',
            'barcode' => 'WF001234567890-UPD',
            'remark' => 'Updated part for workflow testing',
        ];

        $response = $this->actingAs($this->adminUser)->put("/part-number/{$partNumber->part_number_id}", $updateData);
        $response->assertRedirect('/part-number');

        $this->assertDatabaseHas('part_numbers', [
            'part_number_id' => $partNumber->part_number_id,
            'part_number_name' => 'WORKFLOW-PART-001-UPDATED',
            'barcode' => 'WF001234567890-UPD',
        ]);

        // 3. Admin deletes the part number
        $response = $this->actingAs($this->adminUser)->delete("/part-number/{$partNumber->part_number_id}");
        $response->assertRedirect('/part-number');

        $this->assertDatabaseMissing('part_numbers', ['part_number_id' => $partNumber->part_number_id]);
    }

    /**
     * Test part number name length validation
     */
    public function test_part_number_name_length_validation(): void
    {
        $invalidData = [
            'part_number_name' => str_repeat('x', 33), // Exceeds max 32 characters
        ];

        $response = $this->actingAs($this->adminUser)->post('/part-number', $invalidData);
        $response->assertSessionHasErrors(['part_number_name']);
    }

    /**
     * Test barcode can be null
     */
    public function test_barcode_can_be_null(): void
    {
        $partNumberData = [
            'part_number_name' => 'PART-WITHOUT-BARCODE',
            'barcode' => null,
            'remark' => 'Part without barcode',
        ];

        $response = $this->actingAs($this->adminUser)->post('/part-number', $partNumberData);

        $response->assertRedirect('/part-number');
        $this->assertDatabaseHas('part_numbers', [
            'part_number_name' => 'PART-WITHOUT-BARCODE',
            'barcode' => null,
        ]);
    }

    /**
     * Test remark can be null
     */
    public function test_remark_can_be_null(): void
    {
        $partNumberData = [
            'part_number_name' => 'PART-WITHOUT-REMARK',
            'barcode' => '1111111111',
            'remark' => null,
        ];

        $response = $this->actingAs($this->adminUser)->post('/part-number', $partNumberData);

        $response->assertRedirect('/part-number');
        $this->assertDatabaseHas('part_numbers', [
            'part_number_name' => 'PART-WITHOUT-REMARK',
            'remark' => null,
        ]);
    }
}
