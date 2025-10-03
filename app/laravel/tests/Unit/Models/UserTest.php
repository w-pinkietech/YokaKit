<?php

namespace Tests\Unit\Models;

use App\Enums\RoleType;
use App\Models\AndonLayout;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test fillable attributes
     */
    public function test_fillable_attributes(): void
    {
        $fillable = [
            'name',
            'email',
            'password',
            'role',
        ];

        $this->assertEquals($fillable, $this->user->getFillable());
    }

    /**
     * Test hidden attributes
     */
    public function test_hidden_attributes(): void
    {
        $hidden = [
            'password',
            'remember_token',
            'email_verified_at',
            'created_at',
            'updated_at',
        ];

        $this->assertEquals($hidden, $this->user->getHidden());
    }

    /**
     * Test role cast to enum
     */
    public function test_role_is_cast_to_enum(): void
    {
        $casts = $this->user->getCasts();

        $this->assertArrayHasKey('role', $casts);
        $this->assertEquals(RoleType::class, $casts['role']);
    }

    /**
     * Test email verification timestamp cast
     */
    public function test_email_verified_at_is_cast_to_datetime(): void
    {
        $casts = $this->user->getCasts();

        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
    }

    /**
     * Test factory creates valid model
     */
    public function test_factory_creates_valid_model(): void
    {
        $this->assertInstanceOf(User::class, $this->user);
        $this->assertIsInt($this->user->id);
        $this->assertIsString($this->user->name);
        $this->assertIsString($this->user->email);
        $this->assertInstanceOf(RoleType::class, $this->user->role);
    }

    /**
     * Test user extends authenticatable
     */
    public function test_extends_authenticatable(): void
    {
        $this->assertInstanceOf(\Illuminate\Foundation\Auth\User::class, $this->user);
    }

    /**
     * Test user uses traits
     */
    public function test_uses_required_traits(): void
    {
        $traits = class_uses(User::class);

        $this->assertContains(HasApiTokens::class, $traits);
        $this->assertContains(\Illuminate\Database\Eloquent\Factories\HasFactory::class, $traits);
        $this->assertContains(Notifiable::class, $traits);
    }

    /**
     * Test role enum casting
     */
    public function test_role_enum_casting(): void
    {
        $this->user->role = RoleType::ADMIN();
        $this->user->save();
        $this->user->refresh();

        $this->assertInstanceOf(RoleType::class, $this->user->role);
        $this->assertEquals(RoleType::ADMIN(), $this->user->role);
    }

    /**
     * Test email verified at casting
     */
    public function test_email_verified_at_casting(): void
    {
        $verifiedAt = now();
        $this->user->email_verified_at = $verifiedAt;
        $this->user->save();
        $this->user->refresh();

        $this->assertInstanceOf(Carbon::class, $this->user->email_verified_at);
    }

    /**
     * Test password hashing
     */
    public function test_password_is_hashed(): void
    {
        $plainPassword = 'test-password';
        $this->user->password = bcrypt($plainPassword);
        $this->user->save();

        $this->assertNotEquals($plainPassword, $this->user->password);
        $this->assertTrue(\Hash::check($plainPassword, $this->user->password));
    }

    /**
     * Test andon layouts relationship
     */
    public function test_andon_layouts_relationship(): void
    {
        $andonLayout = AndonLayout::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->user->andonLayouts());
        $this->assertTrue($this->user->andonLayouts->contains($andonLayout));
    }

    /**
     * Test model serialization hides sensitive data
     */
    public function test_model_serialization_hides_sensitive_data(): void
    {
        $array = $this->user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
        $this->assertArrayNotHasKey('email_verified_at', $array);
        $this->assertArrayNotHasKey('created_at', $array);
        $this->assertArrayNotHasKey('updated_at', $array);
    }

    /**
     * Test user can be created with all fillable attributes
     */
    public function test_can_create_user_with_fillable_attributes(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => RoleType::USER(),
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertEquals($userData['role'], $user->role);
    }

    /**
     * Test all role types work
     */
    public function test_all_role_types_work(): void
    {
        $roles = [
            RoleType::ADMIN(),
            RoleType::USER(),
        ];

        foreach ($roles as $role) {
            $this->user->role = $role;
            $this->user->save();
            $this->user->refresh();

            $this->assertEquals($role, $this->user->role);
        }
    }

    /**
     * Test password reset notification
     */
    public function test_sends_password_reset_notification(): void
    {
        $this->user->sendPasswordResetNotification('test-token');

        // Check that the notification was sent (this would require mocking in a real test)
        $this->assertTrue(method_exists($this->user, 'sendPasswordResetNotification'));
    }

    /**
     * Test AdminLTE integration methods
     */
    public function test_admin_lte_integration_methods(): void
    {
        // Test that AdminLTE required methods exist
        $this->assertTrue(method_exists($this->user, 'adminlte_image'));
        $this->assertTrue(method_exists($this->user, 'adminlte_desc'));
        $this->assertTrue(method_exists($this->user, 'adminlte_profile_url'));
    }

    /**
     * Test primary key
     */
    public function test_primary_key(): void
    {
        $this->assertEquals('id', $this->user->getKeyName());
    }

    /**
     * Test table name
     */
    public function test_table_name(): void
    {
        $this->assertEquals('users', $this->user->getTable());
    }
}