<?php

namespace Tests\Unit\Repositories;

use App\Enums\RoleType;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository;
    }

    public function test_model_returns_correct_class_string(): void
    {
        $this->assertEquals(User::class, $this->repository->model());
    }

    public function test_create_user_with_enum_role(): void
    {
        $result = $this->repository->create(
            'Test User',
            'test@example.com',
            'password123',
            RoleType::ADMIN
        );

        $this->assertTrue($result);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals(RoleType::ADMIN, $user->role->value);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_create_user_with_numeric_role(): void
    {
        $result = $this->repository->create(
            'Another User',
            'another@example.com',
            'securepass',
            RoleType::SYSTEM
        );

        $this->assertTrue($result);

        $user = User::where('email', 'another@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Another User', $user->name);
        $this->assertEquals(RoleType::SYSTEM, $user->role->value);
        $this->assertTrue(Hash::check('securepass', $user->password));
    }

    public function test_create_hashes_password(): void
    {
        $plainPassword = 'mySecretPassword';

        $this->repository->create(
            'Password Test User',
            'passtest@example.com',
            $plainPassword,
            RoleType::USER
        );

        $user = User::where('email', 'passtest@example.com')->first();

        // Password should be hashed, not plain text
        $this->assertNotEquals($plainPassword, $user->password);
        $this->assertTrue(Hash::check($plainPassword, $user->password));
        $this->assertFalse(Hash::check('wrongpassword', $user->password));
    }

    public function test_inherits_abstract_repository_functionality(): void
    {
        // Test that UserRepository inherits all AbstractRepository methods
        User::factory()->count(3)->create();

        // Test all() method
        $allUsers = $this->repository->all();
        $this->assertCount(3, $allUsers);

        // Test find() method
        $user = User::factory()->create(['email' => 'findme@example.com']);
        $foundUser = $this->repository->find($user->id);
        $this->assertEquals('findme@example.com', $foundUser->email);

        // Test first() method
        $specificUser = $this->repository->first(['email' => 'findme@example.com']);
        $this->assertEquals($user->id, $specificUser->id);
    }

    public function test_create_multiple_users(): void
    {
        $users = [
            ['name' => 'User 1', 'email' => 'user1@test.com', 'password' => 'pass1', 'role' => RoleType::ADMIN],
            ['name' => 'User 2', 'email' => 'user2@test.com', 'password' => 'pass2', 'role' => RoleType::USER],
            ['name' => 'User 3', 'email' => 'user3@test.com', 'password' => 'pass3', 'role' => RoleType::SYSTEM],
        ];

        foreach ($users as $userData) {
            $result = $this->repository->create(
                $userData['name'],
                $userData['email'],
                $userData['password'],
                $userData['role']
            );
            $this->assertTrue($result);
        }

        $this->assertEquals(3, User::count());

        // Verify each user
        foreach ($users as $userData) {
            $user = User::where('email', $userData['email'])->first();
            $this->assertNotNull($user);
            $this->assertEquals($userData['name'], $user->name);
            $this->assertEquals($userData['role'], $user->role->value);
            $this->assertTrue(Hash::check($userData['password'], $user->password));
        }
    }

    public function test_create_with_special_characters(): void
    {
        $result = $this->repository->create(
            "User O'Brien",
            'special.email+tag@example.com',
            'P@ssw0rd!#$%',
            RoleType::ADMIN
        );

        $this->assertTrue($result);

        $user = User::where('email', 'special.email+tag@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals("User O'Brien", $user->name);
        $this->assertTrue(Hash::check('P@ssw0rd!#$%', $user->password));
    }

    public function test_password_is_not_stored_in_plain_text(): void
    {
        $password = 'supersecret';

        $this->repository->create(
            'Security Test',
            'security@test.com',
            $password,
            RoleType::USER
        );

        $this->assertDatabaseMissing('users', [
            'password' => $password,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'security@test.com',
            'name' => 'Security Test',
        ]);
    }
}
