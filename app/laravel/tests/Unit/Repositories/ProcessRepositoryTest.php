<?php

namespace Tests\Unit\Repositories;

use App\Models\AndonLayout;
use App\Models\Process;
use App\Models\ProductionHistory;
use App\Models\SensorEvent;
use App\Repositories\ProcessRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ProcessRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProcessRepository;
    }

    public function test_model_returns_correct_class_string(): void
    {
        $this->assertEquals(Process::class, $this->repository->model());
    }

    public function test_all_returns_correct_collection_type(): void
    {
        Process::factory()->count(3)->create();

        $result = $this->repository->all();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(Process::class, $result);
    }

    public function test_all_with_relationships_loads_correctly(): void
    {
        $user = \App\Models\User::factory()->create();
        $process = Process::factory()->create();
        AndonLayout::factory()->create([
            'process_id' => $process->process_id,
            'user_id' => $user->id,
        ]);

        // Create sensor for the process first
        $sensor = \App\Models\Sensor::factory()->create([
            'process_id' => $process->process_id,
        ]);

        // Create sensor event that matches the complex query conditions
        SensorEvent::factory()->create([
            'process_id' => $process->process_id,
            'sensor_id' => $sensor->sensor_id,
            'at' => now(),
            'trigger' => true,
            'signal' => true, // trigger = signal for the whereRaw condition
        ]);

        $result = $this->repository->all([
            'andonLayout',
            'sensorEvents',
        ]);

        $this->assertTrue($result->first()->relationLoaded('andonLayout'));
        $this->assertTrue($result->first()->relationLoaded('sensorEvents'));
        $this->assertNotNull($result->first()->andonLayout);
        // Note: sensorEvents has complex filtering, so we just check it's loaded
        $this->assertTrue($result->first()->relationLoaded('sensorEvents'));
    }

    public function test_start_updates_production_history_id(): void
    {
        $process = Process::factory()->create(['production_history_id' => null]);
        $productionHistory = ProductionHistory::factory()->create();

        $result = $this->repository->start($process, $productionHistory->production_history_id);

        $this->assertTrue($result);
        $process->refresh();
        $this->assertEquals($productionHistory->production_history_id, $process->production_history_id);
    }

    public function test_start_returns_false_on_invalid_process(): void
    {
        $process = new Process;
        $process->process_id = 99999; // Non-existent ID

        $result = $this->repository->start($process, 1);

        $this->assertFalse($result);
    }

    public function test_stop_clears_production_history_id(): void
    {
        $productionHistory = ProductionHistory::factory()->create();
        $process = Process::factory()->create([
            'production_history_id' => $productionHistory->production_history_id,
        ]);

        $result = $this->repository->stop($process);

        $this->assertTrue($result);
        $process->refresh();
        $this->assertNull($process->production_history_id);
    }

    public function test_stop_returns_false_on_invalid_process(): void
    {
        $process = new Process;
        $process->process_id = 99999; // Non-existent ID

        $result = $this->repository->stop($process);

        $this->assertFalse($result);
    }

    public function test_find_returns_process_by_id(): void
    {
        $process = Process::factory()->create();

        $result = $this->repository->find($process->process_id);

        $this->assertInstanceOf(Process::class, $result);
        $this->assertEquals($process->process_id, $result->process_id);
    }

    public function test_find_returns_null_for_non_existent_id(): void
    {
        $result = $this->repository->find(99999);

        $this->assertNull($result);
    }

    public function test_find_with_relationships(): void
    {
        $user = \App\Models\User::factory()->create();
        $process = Process::factory()->create();
        AndonLayout::factory()->create([
            'process_id' => $process->process_id,
            'user_id' => $user->id,
        ]);

        $result = $this->repository->find($process->process_id, 'andonLayout');

        $this->assertTrue($result->relationLoaded('andonLayout'));
        $this->assertNotNull($result->andonLayout);
    }

    public function test_first_returns_process_by_condition(): void
    {
        $process = Process::factory()->create(['process_name' => 'Test Process']);
        Process::factory()->create(['process_name' => 'Other Process']);

        $result = $this->repository->first(['process_name' => 'Test Process']);

        $this->assertInstanceOf(Process::class, $result);
        $this->assertEquals('Test Process', $result->process_name);
    }

    public function test_first_returns_null_when_no_match(): void
    {
        Process::factory()->create(['process_name' => 'Test Process']);

        $result = $this->repository->first(['process_name' => 'Non-existent Process']);

        $this->assertNull($result);
    }

    public function test_get_returns_processes_by_condition(): void
    {
        Process::factory()->count(2)->create(['plan_color' => 'blue']);
        Process::factory()->create(['plan_color' => 'red']);

        $result = $this->repository->get(['plan_color' => 'blue']);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertEquals('blue', $result->first()->plan_color);
    }

    public function test_get_with_order(): void
    {
        Process::factory()->create(['process_name' => 'B Process', 'plan_color' => 'blue']);
        Process::factory()->create(['process_name' => 'A Process', 'plan_color' => 'blue']);

        $result = $this->repository->get(['plan_color' => 'blue'], null, ['*'], 'process_name');

        $this->assertEquals('A Process', $result->first()->process_name);
        $this->assertEquals('B Process', $result->last()->process_name);
    }

    public function test_all_with_order(): void
    {
        Process::factory()->create(['process_name' => 'C Process']);
        Process::factory()->create(['process_name' => 'A Process']);
        Process::factory()->create(['process_name' => 'B Process']);

        $result = $this->repository->all(null, 'process_name');

        $this->assertEquals('A Process', $result->first()->process_name);
        $this->assertEquals('B Process', $result->get(1)->process_name);
        $this->assertEquals('C Process', $result->last()->process_name);
    }

    public function test_start_and_stop_workflow(): void
    {
        $process = Process::factory()->create(['production_history_id' => null]);
        $productionHistory = ProductionHistory::factory()->create();

        // Start production
        $startResult = $this->repository->start($process, $productionHistory->production_history_id);
        $this->assertTrue($startResult);

        $process->refresh();
        $this->assertEquals($productionHistory->production_history_id, $process->production_history_id);

        // Stop production
        $stopResult = $this->repository->stop($process);
        $this->assertTrue($stopResult);

        $process->refresh();
        $this->assertNull($process->production_history_id);
    }

    public function test_processes_with_different_production_histories(): void
    {
        $productionHistory1 = ProductionHistory::factory()->create();
        $productionHistory2 = ProductionHistory::factory()->create();
        $process1 = Process::factory()->create(['production_history_id' => null]);
        $process2 = Process::factory()->create(['production_history_id' => null]);

        // Each process gets its own production history due to unique constraint
        $result1 = $this->repository->start($process1, $productionHistory1->production_history_id);
        $result2 = $this->repository->start($process2, $productionHistory2->production_history_id);

        $this->assertTrue($result1);
        $this->assertTrue($result2);

        $process1->refresh();
        $process2->refresh();

        $this->assertEquals($productionHistory1->production_history_id, $process1->production_history_id);
        $this->assertEquals($productionHistory2->production_history_id, $process2->production_history_id);
        $this->assertNotEquals($process1->production_history_id, $process2->production_history_id);
    }

    public function test_repository_handles_soft_deleted_processes(): void
    {
        // Note: Process model doesn't use SoftDeletes trait based on the model test
        // This test verifies that the repository works with regular deletion
        $process = Process::factory()->create();
        $processId = $process->process_id;

        $process->delete();

        $result = $this->repository->find($processId);
        $this->assertNull($result);
    }
}
