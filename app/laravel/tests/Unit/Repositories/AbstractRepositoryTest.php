<?php

namespace Tests\Unit\Repositories;

use App\Models\Process;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

// Concrete implementation for testing
class TestRepository extends AbstractRepository
{
    public function model(): string
    {
        return Process::class;
    }
}

class AbstractRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected TestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TestRepository;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_constructor_creates_model_instance(): void
    {
        $repository = new TestRepository;

        $reflection = new \ReflectionClass($repository);
        $property = $reflection->getProperty('model');
        $property->setAccessible(true);

        $this->assertInstanceOf(Process::class, $property->getValue($repository));
    }

    public function test_constructor_with_provided_model(): void
    {
        $process = Process::factory()->create();
        $repository = new TestRepository($process);

        $reflection = new \ReflectionClass($repository);
        $property = $reflection->getProperty('model');
        $property->setAccessible(true);

        $this->assertSame($process, $property->getValue($repository));
    }

    public function test_all_returns_all_models(): void
    {
        Process::factory()->count(3)->create();

        $result = $this->repository->all();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(Process::class, $result);
    }

    public function test_all_with_relationships(): void
    {
        $process = Process::factory()->create();

        $result = $this->repository->all(['andonLayout', 'sensorEvents']);

        $this->assertTrue($result->first()->relationLoaded('andonLayout'));
        $this->assertTrue($result->first()->relationLoaded('sensorEvents'));
    }

    public function test_all_with_order(): void
    {
        Process::factory()->create(['process_name' => 'C Order Test']);
        Process::factory()->create(['process_name' => 'A Order Test']);
        Process::factory()->create(['process_name' => 'B Order Test']);

        $result = $this->repository->all(null, 'process_name');

        $this->assertEquals('A Order Test', $result->first()->process_name);
        $this->assertEquals('B Order Test', $result->get(1)->process_name);
        $this->assertEquals('C Order Test', $result->last()->process_name);
    }

    public function test_all_with_relationships_and_order(): void
    {
        Process::factory()->create(['process_name' => 'B Rel Test']);
        Process::factory()->create(['process_name' => 'A Rel Test']);

        $result = $this->repository->all(['andonLayout'], 'process_name');

        $this->assertEquals('A Rel Test', $result->first()->process_name);
        $this->assertEquals('B Rel Test', $result->last()->process_name);
        $this->assertTrue($result->first()->relationLoaded('andonLayout'));
    }

    public function test_find_returns_model_by_id(): void
    {
        $process = Process::factory()->create();

        $result = $this->repository->find($process->process_id);

        $this->assertInstanceOf(Process::class, $result);
        $this->assertEquals($process->process_id, $result->process_id);
    }

    public function test_find_returns_null_for_non_existent(): void
    {
        $result = $this->repository->find(99999);

        $this->assertNull($result);
    }

    public function test_find_with_relationships(): void
    {
        $process = Process::factory()->create();

        $result = $this->repository->find($process->process_id, 'andonLayout');

        $this->assertTrue($result->relationLoaded('andonLayout'));
    }

    public function test_find_with_array_relationships(): void
    {
        $process = Process::factory()->create();

        $result = $this->repository->find($process->process_id, ['andonLayout', 'sensorEvents']);

        $this->assertTrue($result->relationLoaded('andonLayout'));
        $this->assertTrue($result->relationLoaded('sensorEvents'));
    }

    public function test_first_returns_first_matching_model(): void
    {
        Process::factory()->create(['process_name' => 'First', 'plan_color' => 'red']);
        Process::factory()->create(['process_name' => 'Second', 'plan_color' => 'red']);

        $result = $this->repository->first(['plan_color' => 'red']);

        $this->assertEquals('First', $result->process_name);
    }

    public function test_first_with_multiple_conditions(): void
    {
        Process::factory()->create(['process_name' => 'Test Red', 'plan_color' => 'red']);
        $blue = Process::factory()->create(['process_name' => 'Test Blue', 'plan_color' => 'blue']);

        $result = $this->repository->first(['process_name' => 'Test Blue', 'plan_color' => 'blue']);

        $this->assertEquals($blue->process_id, $result->process_id);
        $this->assertEquals('blue', $result->plan_color);
    }

    public function test_first_returns_null_when_no_match(): void
    {
        Process::factory()->create(['plan_color' => 'red']);

        $result = $this->repository->first(['plan_color' => 'blue']);

        $this->assertNull($result);
    }

    public function test_get_returns_matching_models(): void
    {
        Process::factory()->count(2)->create(['plan_color' => 'red']);
        Process::factory()->create(['plan_color' => 'blue']);

        $result = $this->repository->get(['plan_color' => 'red']);

        $this->assertCount(2, $result);
        $this->assertEquals('red', $result->first()->plan_color);
    }

    public function test_get_with_specific_columns(): void
    {
        Process::factory()->create(['process_name' => 'Test', 'plan_color' => 'red']);

        $result = $this->repository->get(['plan_color' => 'red'], null, ['process_id', 'process_name']);

        $this->assertArrayHasKey('process_id', $result->first()->toArray());
        $this->assertArrayHasKey('process_name', $result->first()->toArray());
        $this->assertArrayNotHasKey('plan_color', $result->first()->toArray());
    }

    public function test_get_with_order(): void
    {
        Process::factory()->create(['process_name' => 'B Get Test', 'plan_color' => 'red']);
        Process::factory()->create(['process_name' => 'A Get Test', 'plan_color' => 'red']);

        $result = $this->repository->get(['plan_color' => 'red'], null, ['*'], 'process_name');

        $this->assertEquals('A Get Test', $result->first()->process_name);
        $this->assertEquals('B Get Test', $result->last()->process_name);
    }

    public function test_store_creates_new_model(): void
    {
        $request = Mockery::mock(FormRequest::class);
        $request->shouldReceive('all')->andReturn([
            'process_name' => 'New Process',
            'plan_color' => 'green',
            'remark' => 'Test remark',
        ]);

        Log::shouldReceive('debug')->once();

        $result = $this->repository->store($request);

        $this->assertTrue($result);
        $this->assertDatabaseHas('processes', [
            'process_name' => 'New Process',
            'plan_color' => 'green',
        ]);
    }

    public function test_update_modifies_existing_model(): void
    {
        $process = Process::factory()->create(['process_name' => 'Old Name']);

        $request = Mockery::mock(FormRequest::class);
        $request->shouldReceive('all')->andReturn([
            'process_name' => 'New Name',
        ]);

        Log::shouldReceive('debug')->once();

        $result = $this->repository->update($request, $process);

        $this->assertTrue($result);
        $process->refresh();
        $this->assertEquals('New Name', $process->process_name);
    }

    public function test_destroy_deletes_model(): void
    {
        $process = Process::factory()->create();
        $processId = $process->process_id;

        Log::shouldReceive('debug')->once();

        $result = $this->repository->destroy($process);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('processes', ['process_id' => $processId]);
    }

    public function test_destroy_logs_warning_on_failure(): void
    {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('delete')->andReturn(false);
        $process->shouldReceive('getRawOriginal')->andReturn(['id' => 1]);

        Log::shouldReceive('warning')->once();

        $result = $this->repository->destroy($process);

        $this->assertFalse($result);
    }

    public function test_update_model_with_model_instance(): void
    {
        $process = Process::factory()->create(['process_name' => 'Old']);

        $reflection = new \ReflectionClass($this->repository);
        $method = $reflection->getMethod('updateModel');
        $method->setAccessible(true);

        Log::shouldReceive('debug')->once();

        $result = $method->invoke($this->repository, $process, ['process_name' => 'New']);

        $this->assertTrue($result);
        $process->refresh();
        $this->assertEquals('New', $process->process_name);
    }

    public function test_update_model_with_builder(): void
    {
        Process::factory()->count(3)->create(['plan_color' => 'red']);

        $builder = Process::where('plan_color', 'red');

        $reflection = new \ReflectionClass($this->repository);
        $method = $reflection->getMethod('updateModel');
        $method->setAccessible(true);

        Log::shouldReceive('debug')->once();

        $result = $method->invoke($this->repository, $builder, ['plan_color' => 'blue']);

        $this->assertTrue($result);
        $this->assertEquals(3, Process::where('plan_color', 'blue')->count());
        $this->assertEquals(0, Process::where('plan_color', 'red')->count());
    }

    public function test_update_model_logs_warning_on_builder_failure(): void
    {
        $builder = Process::where('plan_color', 'non-existent');

        $reflection = new \ReflectionClass($this->repository);
        $method = $reflection->getMethod('updateModel');
        $method->setAccessible(true);

        Log::shouldReceive('warning')->once();

        $result = $method->invoke($this->repository, $builder, ['plan_color' => 'blue']);

        $this->assertFalse($result);
    }

    public function test_store_model_saves_model(): void
    {
        $process = new Process([
            'process_name' => 'Test Process',
            'plan_color' => 'yellow',
        ]);

        $reflection = new \ReflectionClass($this->repository);
        $method = $reflection->getMethod('storeModel');
        $method->setAccessible(true);

        Log::shouldReceive('debug')->once();

        $result = $method->invoke($this->repository, $process);

        $this->assertTrue($result);
        $this->assertDatabaseHas('processes', [
            'process_name' => 'Test Process',
            'plan_color' => 'yellow',
        ]);
    }

    public function test_store_model_logs_warning_on_failure(): void
    {
        $model = Mockery::mock(Process::class);
        $model->shouldReceive('save')->andReturn(false);
        $model->shouldReceive('toArray')->andReturn(['id' => 1]);

        $reflection = new \ReflectionClass($this->repository);
        $method = $reflection->getMethod('storeModel');
        $method->setAccessible(true);

        Log::shouldReceive('warning')->once();

        $result = $method->invoke($this->repository, $model);

        $this->assertFalse($result);
    }

    public function test_complex_query_scenario(): void
    {
        // Create test data
        Process::factory()->create(['process_name' => 'A', 'plan_color' => 'red', 'remark' => 'important']);
        Process::factory()->create(['process_name' => 'B', 'plan_color' => 'red', 'remark' => null]);
        Process::factory()->create(['process_name' => 'C', 'plan_color' => 'blue', 'remark' => 'important']);

        // Test multiple conditions
        $redProcesses = $this->repository->get(['plan_color' => 'red']);
        $this->assertCount(2, $redProcesses);

        // Test with relationships and ordering
        $allProcesses = $this->repository->all(['andonLayout'], 'process_name');
        $this->assertEquals('A', $allProcesses->first()->process_name);
        $this->assertEquals('C', $allProcesses->last()->process_name);

        // Test first with multiple conditions
        $specificProcess = $this->repository->first(['plan_color' => 'red', 'remark' => null]);
        $this->assertEquals('B', $specificProcess->process_name);
    }
}
