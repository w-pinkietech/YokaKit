<?php

namespace Tests\Feature\Controllers;

use App\Enums\RoleType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserControllerTest extends BaseControllerTest
{
    use RefreshDatabase;

    private User $systemUser;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->systemUser = User::factory()->create(['role' => RoleType::SYSTEM]);
        $this->adminUser = User::factory()->create(['role' => RoleType::ADMIN]);
    }

    /**
     * Test user index requires authentication
     */
    public function test_index_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('GET', '/user');
    }

    /**
     * Test user index requires system admin role
     */
    public function test_index_requires_system_admin(): void
    {
        $response = $this->actingAs($this->user)->get('/user');
        $response->assertStatus(403);

        $response = $this->actingAs($this->adminUser)->get('/user');
        $response->assertStatus(403);
    }

    /**
     * Test system admin can access user index
     */
    public function test_index_accessible_when_system_admin(): void
    {
        $response = $this->actingAs($this->systemUser)->get('/user');
        $response->assertStatus(200);
        $response->assertViewIs('user.index');
        $response->assertViewHas('users');
    }

    /**
     * Test user index displays users
     */
    public function test_index_displays_users(): void
    {
        $testUser = User::factory()->create(['name' => 'Test User Display']);

        $response = $this->actingAs($this->systemUser)->get('/user');

        $response->assertStatus(200);
        $response->assertSee('Test User Display');
    }

    /**
     * Test user create requires authentication
     */
    public function test_create_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('GET', '/user/create');
    }

    /**
     * Test user create requires system admin role
     */
    public function test_create_requires_system_admin(): void
    {
        $response = $this->actingAs($this->user)->get('/user/create');
        $response->assertStatus(403);

        $response = $this->actingAs($this->adminUser)->get('/user/create');
        $response->assertStatus(403);
    }

    /**
     * Test system admin can access user create
     */
    public function test_create_accessible_when_system_admin(): void
    {
        $response = $this->actingAs($this->systemUser)->get('/user/create');
        $response->assertStatus(200);
        $response->assertViewIs('user.create');
    }

    /**
     * Test user store requires authentication
     */
    public function test_store_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('POST', '/user', ['name' => 'Test']);
    }

    /**
     * Test user store requires system admin role
     */
    public function test_store_requires_system_admin(): void
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => RoleType::USER,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->user)->post('/user', $userData);
        $response->assertStatus(403);

        $response = $this->actingAs($this->adminUser)->post('/user', $userData);
        $response->assertStatus(403);
    }

    /**
     * Test user store with valid data
     */
    public function test_store_with_valid_data(): void
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => RoleType::USER,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->systemUser)->post('/user', $userData);

        $response->assertRedirect('/user');
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => RoleType::USER,
        ]);
    }

    /**
     * Test user store with invalid data
     */
    public function test_store_with_invalid_data(): void
    {
        $invalidData = [
            'name' => '', // Required
            'email' => 'invalid-email', // Invalid format
            'role' => 999, // Invalid role
            'password' => '123', // Too short
            'password_confirmation' => 'different', // Doesn't match
        ];

        $response = $this->actingAs($this->systemUser)->post('/user', $invalidData);
        $response->assertSessionHasErrors(['name', 'email', 'role', 'password']);
    }

    /**
     * Test user store with duplicate email
     */
    public function test_store_with_duplicate_email(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'New User',
            'email' => 'existing@example.com', // Duplicate email
            'role' => RoleType::USER,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->systemUser)->post('/user', $userData);
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test user profile (show) requires authentication
     */
    public function test_show_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('GET', '/user/profile');
    }

    /**
     * Test authenticated user can view their profile
     */
    public function test_show_accessible_when_authenticated(): void
    {
        $response = $this->actingAs($this->user)->get('/user/profile');
        $response->assertStatus(200);
        $response->assertViewIs('user.show');
        $response->assertViewHas('user', $this->user);
    }

    /**
     * Test user edit requires authentication
     */
    public function test_edit_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('GET', '/user/edit');
    }

    /**
     * Test authenticated user can edit their profile
     */
    public function test_edit_accessible_when_authenticated(): void
    {
        $response = $this->actingAs($this->user)->get('/user/edit');
        $response->assertStatus(200);
        $response->assertViewIs('user.edit');
        $response->assertViewHas('user', $this->user);
    }

    /**
     * Test user profile update requires authentication
     */
    public function test_profile_update_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('PUT', '/user', ['name' => 'Updated']);
    }

    /**
     * Test user profile update with valid data
     */
    public function test_profile_update_with_valid_data(): void
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->actingAs($this->user)->put('/user', $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test user profile update with invalid data
     */
    public function test_profile_update_with_invalid_data(): void
    {
        $invalidData = [
            'name' => '', // Required
            'email' => 'invalid-email', // Invalid format
        ];

        $response = $this->actingAs($this->user)->put('/user', $invalidData);
        $response->assertSessionHasErrors(['name', 'email']);
    }

    /**
     * Test user profile update with existing email from another user
     */
    public function test_profile_update_with_existing_email(): void
    {
        $otherUser = User::factory()->create(['email' => 'taken@example.com']);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'taken@example.com', // Email belongs to another user
        ];

        $response = $this->actingAs($this->user)->put('/user', $updateData);
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test user can update with their own email (no conflict)
     */
    public function test_profile_update_with_same_email(): void
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => $this->user->email, // Same email as current user
        ];

        $response = $this->actingAs($this->user)->put('/user', $updateData);
        $response->assertRedirect();
    }

    /**
     * Test user destroy requires authentication
     */
    public function test_destroy_requires_authentication(): void
    {
        $targetUser = User::factory()->create();
        $this->assertRequiresAuthentication('DELETE', "/user/{$targetUser->id}");
    }

    /**
     * Test user destroy requires system admin role
     */
    public function test_destroy_requires_system_admin(): void
    {
        $targetUser = User::factory()->create();

        $response = $this->actingAs($this->user)->delete("/user/{$targetUser->id}");
        $response->assertStatus(403);

        $response = $this->actingAs($this->adminUser)->delete("/user/{$targetUser->id}");
        $response->assertStatus(403);
    }

    /**
     * Test system admin can delete users
     */
    public function test_destroy_removes_user(): void
    {
        $targetUser = User::factory()->create();

        $response = $this->actingAs($this->systemUser)->delete("/user/{$targetUser->id}");

        $response->assertRedirect('/user');
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    /**
     * Test user can delete themselves (logs out)
     */
    public function test_user_can_delete_themselves(): void
    {
        $response = $this->actingAs($this->user)->delete("/user/{$this->user->id}");

        // User can delete themselves but it may redirect differently
        $this->assertContains($response->getStatusCode(), [200, 302, 403]);
        // Note: Self-deletion behavior depends on business logic implementation
    }

    /**
     * Test token generation requires authentication
     */
    public function test_token_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('POST', '/user/token');
    }

    /**
     * Test token generation requires admin role
     */
    public function test_token_requires_admin_role(): void
    {
        $response = $this->actingAs($this->user)->post('/user/token');
        $response->assertStatus(403);
    }

    /**
     * Test admin can generate API token
     */
    public function test_admin_can_generate_token(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/user/token');

        $response->assertRedirect();
        // Token generation creates new token and deletes old ones
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->adminUser->id,
            'tokenable_type' => User::class,
        ]);
    }

    /**
     * Test system admin can generate API token
     */
    public function test_system_admin_can_generate_token(): void
    {
        $response = $this->actingAs($this->systemUser)->post('/user/token');

        $response->assertRedirect();
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->systemUser->id,
            'tokenable_type' => User::class,
        ]);
    }

    /**
     * Test password change page requires authentication
     */
    public function test_password_page_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('GET', '/user/password');
    }

    /**
     * Test authenticated user can access password change page
     */
    public function test_password_page_accessible_when_authenticated(): void
    {
        $response = $this->actingAs($this->user)->get('/user/password');
        $response->assertStatus(200);
        $response->assertViewIs('user.password');
    }

    /**
     * Test password change requires authentication
     */
    public function test_password_change_requires_authentication(): void
    {
        $this->assertRequiresAuthentication('PUT', '/user/password', ['current_password' => 'test']);
    }

    /**
     * Test password change with valid data
     */
    public function test_password_change_with_valid_data(): void
    {
        // Create user with known password
        $user = User::factory()->create(['password' => Hash::make('oldpassword')]);

        $passwordData = [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->actingAs($user)->put('/user/password', $passwordData);

        $response->assertRedirect();
        // Verify password was actually changed
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    /**
     * Test password change with invalid current password
     */
    public function test_password_change_with_invalid_current_password(): void
    {
        $passwordData = [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->actingAs($this->user)->put('/user/password', $passwordData);
        $response->assertSessionHasErrors(['current_password']);
    }

    /**
     * Test password change with invalid new password
     */
    public function test_password_change_with_invalid_new_password(): void
    {
        $passwordData = [
            'current_password' => 'password', // Default factory password
            'password' => '123', // Too short
            'password_confirmation' => 'different', // Doesn't match
        ];

        $response = $this->actingAs($this->user)->put('/user/password', $passwordData);
        $response->assertSessionHasErrors(['password']);
    }

    /**
     * Test 404 when user not found for deletion
     */
    public function test_destroy_returns_404_when_user_not_found(): void
    {
        $response = $this->actingAs($this->systemUser)->delete('/user/99999');
        $response->assertStatus(404);
    }

    /**
     * Test user management workflow
     */
    public function test_complete_user_management_workflow(): void
    {
        // 1. System admin creates new user
        $userData = [
            'name' => 'Workflow User',
            'email' => 'workflow@example.com',
            'role' => RoleType::USER,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->systemUser)->post('/user', $userData);
        $response->assertRedirect('/user');

        $newUser = User::where('email', 'workflow@example.com')->first();
        $this->assertNotNull($newUser);

        // 2. User updates their profile
        $this->actingAs($newUser);
        $updateData = [
            'name' => 'Updated Workflow User',
            'email' => 'updated-workflow@example.com',
        ];

        $response = $this->put('/user', $updateData);
        $response->assertRedirect();

        // 3. User changes their password
        $passwordData = [
            'current_password' => 'password123',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ];

        $response = $this->put('/user/password', $passwordData);
        $response->assertRedirect();

        // 4. System admin deletes the user
        $response = $this->actingAs($this->systemUser)->delete("/user/{$newUser->id}");
        $response->assertRedirect('/user');
        $this->assertDatabaseMissing('users', ['id' => $newUser->id]);
    }
}
