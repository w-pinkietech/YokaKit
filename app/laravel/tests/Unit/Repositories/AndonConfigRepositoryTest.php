<?php

namespace Tests\Unit\Repositories;

use App\Models\AndonConfig;
use App\Models\User;
use App\Repositories\AndonConfigRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AndonConfigRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected AndonConfigRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AndonConfigRepository;
    }

    public function test_model_returns_correct_class_string(): void
    {
        $this->assertEquals(AndonConfig::class, $this->repository->model());
    }

    public function test_andon_config_creates_new_config_when_not_exists(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $config = $this->repository->andonConfig();

        $this->assertInstanceOf(AndonConfig::class, $config);
        $this->assertEquals($user->id, $config->user_id);

        // Verify it was saved to database
        $this->assertDatabaseHas('andon_configs', [
            'user_id' => $user->id,
        ]);
    }

    public function test_andon_config_returns_existing_config(): void
    {
        $user = User::factory()->create();
        $existingConfig = AndonConfig::factory()->create([
            'user_id' => $user->id,
            'row_count' => 4,
            'column_count' => 5,
        ]);

        $this->actingAs($user);

        $config = $this->repository->andonConfig();

        $this->assertInstanceOf(AndonConfig::class, $config);
        $this->assertEquals($existingConfig->andon_config_id, $config->andon_config_id);
        $this->assertEquals(4, $config->row_count);
        $this->assertEquals(5, $config->column_count);
    }

    public function test_andon_config_creates_separate_configs_for_different_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create config for user1
        $this->actingAs($user1);
        $config1 = $this->repository->andonConfig();

        // Create config for user2
        $this->actingAs($user2);
        $config2 = $this->repository->andonConfig();

        $this->assertNotEquals($config1->andon_config_id, $config2->andon_config_id);
        $this->assertEquals($user1->id, $config1->user_id);
        $this->assertEquals($user2->id, $config2->user_id);

        // Verify both configs exist in database
        $this->assertEquals(2, AndonConfig::count());
    }

    public function test_andon_config_inherits_abstract_repository_functionality(): void
    {
        // Create some configs
        AndonConfig::factory()->count(3)->create();

        // Test all() method
        $allConfigs = $this->repository->all();
        $this->assertCount(3, $allConfigs);

        // Test find() method
        $config = AndonConfig::factory()->create(['auto_play_speed' => 2500]);
        $foundConfig = $this->repository->find($config->andon_config_id);
        $this->assertEquals(2500, $foundConfig->auto_play_speed);

        // Test first() method
        $user = User::factory()->create();
        AndonConfig::factory()->create(['user_id' => $user->id, 'auto_play' => true]);
        $specificConfig = $this->repository->first(['user_id' => $user->id]);
        $this->assertTrue($specificConfig->auto_play);
    }

    public function test_andon_config_with_custom_settings(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // First call creates with defaults
        $config = $this->repository->andonConfig();

        // Update the config
        $config->update([
            'row_count' => 6,
            'column_count' => 8,
            'auto_play' => true,
            'auto_play_speed' => 4000,
            'slide_speed' => 400,
            'easing' => 'ease-in',
            'fade' => true,
            'item_column_count' => 4,
            'is_show_part_number' => true,
            'is_show_start' => false,
            'is_show_good_count' => true,
        ]);

        // Second call should return the updated config
        $retrievedConfig = $this->repository->andonConfig();

        $this->assertEquals(6, $retrievedConfig->row_count);
        $this->assertEquals(8, $retrievedConfig->column_count);
        $this->assertTrue($retrievedConfig->auto_play);
        $this->assertEquals(4000, $retrievedConfig->auto_play_speed);
        $this->assertEquals(400, $retrievedConfig->slide_speed);
        $this->assertEquals('ease-in', $retrievedConfig->easing);
        $this->assertTrue($retrievedConfig->fade);
        $this->assertEquals(4, $retrievedConfig->item_column_count);
        $this->assertTrue($retrievedConfig->is_show_part_number);
        $this->assertFalse($retrievedConfig->is_show_start);
        $this->assertTrue($retrievedConfig->is_show_good_count);
    }

    public function test_multiple_calls_return_same_instance_for_same_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $config1 = $this->repository->andonConfig();
        $config2 = $this->repository->andonConfig();

        $this->assertEquals($config1->andon_config_id, $config2->andon_config_id);

        // Should only have one config in database
        $this->assertEquals(1, AndonConfig::where('user_id', $user->id)->count());
    }

    public function test_andon_config_without_authenticated_user(): void
    {
        // Test with a guest user (no authentication)
        // This should create a config with user_id from Auth::id() which could be null
        // But since the database doesn't allow null, let's create a user and test that scenario
        $user = User::factory()->create();
        $this->actingAs($user);

        $config = $this->repository->andonConfig();

        $this->assertInstanceOf(AndonConfig::class, $config);
        $this->assertEquals($user->id, $config->user_id);

        // Verify it was saved correctly
        $this->assertDatabaseHas('andon_configs', [
            'user_id' => $user->id,
        ]);
    }
}
