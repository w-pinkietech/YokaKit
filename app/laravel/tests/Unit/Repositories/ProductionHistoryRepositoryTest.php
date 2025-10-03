<?php

namespace Tests\Unit\Repositories;

use App\Enums\ProductionStatus;
use App\Models\CycleTime;
use App\Models\PartNumber;
use App\Models\Process;
use App\Models\ProductionHistory;
use App\Repositories\ProductionHistoryRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ProductionHistoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ProductionHistoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductionHistoryRepository;
    }

    public function test_model_returns_correct_class_string(): void
    {
        $this->assertEquals(ProductionHistory::class, $this->repository->model());
    }

    public function test_update_status_successfully_updates(): void
    {
        $history = ProductionHistory::factory()->create([
            'status' => ProductionStatus::RUNNING(),
        ]);

        $this->repository->updateStatus($history, ProductionStatus::BREAKDOWN());

        $history->refresh();
        $this->assertEquals(ProductionStatus::BREAKDOWN(), $history->status);
    }

    public function test_update_status_throws_exception_on_failure(): void
    {
        $history = new ProductionHistory;
        $history->production_history_id = 99999; // Non-existent ID

        $this->expectException(\Exception::class);

        $this->repository->updateStatus($history, ProductionStatus::BREAKDOWN());
    }

    public function test_store_history_creates_new_production_history(): void
    {
        $process = Process::factory()->create([
            'process_name' => 'Test Process',
            'plan_color' => 'blue',
        ]);

        $partNumber = PartNumber::factory()->create([
            'part_number_name' => 'Test Part',
        ]);

        $cycleTime = CycleTime::factory()->create([
            'process_id' => $process->process_id,
            'part_number_id' => $partNumber->part_number_id,
            'cycle_time' => 30,
            'over_time' => 5,
        ]);

        $status = ProductionStatus::RUNNING();
        $goal = 100;

        $result = $this->repository->storeHistory($process, $cycleTime, $status, $goal);

        $this->assertInstanceOf(ProductionHistory::class, $result);
        $this->assertEquals($process->process_id, $result->process_id);
        $this->assertEquals($partNumber->part_number_id, $result->part_number_id);
        $this->assertEquals('Test Process', $result->process_name);
        $this->assertEquals('blue', $result->plan_color);
        $this->assertEquals('Test Part', $result->part_number_name);
        $this->assertEquals(30, $result->cycle_time);
        $this->assertEquals(5, $result->over_time);
        $this->assertEquals(100, $result->goal);
        $this->assertEquals(ProductionStatus::RUNNING(), $result->status);
        $this->assertNotNull($result->start);
    }

    public function test_store_history_without_goal(): void
    {
        $process = Process::factory()->create();
        $partNumber = PartNumber::factory()->create();
        $cycleTime = CycleTime::factory()->create([
            'process_id' => $process->process_id,
            'part_number_id' => $partNumber->part_number_id,
        ]);
        $status = ProductionStatus::CHANGEOVER();

        $result = $this->repository->storeHistory($process, $cycleTime, $status);

        $this->assertInstanceOf(ProductionHistory::class, $result);
        $this->assertNull($result->goal);
        $this->assertEquals(ProductionStatus::CHANGEOVER(), $result->status);
    }

    public function test_stop_updates_production_history(): void
    {
        $history = ProductionHistory::factory()->create([
            'status' => ProductionStatus::RUNNING(),
            'stop' => null,
        ]);

        $stopTime = Carbon::now()->addHours(2);

        $result = $this->repository->stop($history, $stopTime);

        $this->assertTrue($result);

        $history->refresh();
        $this->assertEquals($stopTime->format('Y-m-d H:i:s'), $history->stop->format('Y-m-d H:i:s'));
        $this->assertEquals(ProductionStatus::COMPLETE(), $history->status);
    }

    public function test_stop_returns_false_on_invalid_history(): void
    {
        $history = ProductionHistory::factory()->make(['production_history_id' => 99999]);
        $stopTime = Carbon::now();

        $result = $this->repository->stop($history, $stopTime);

        $this->assertFalse($result);
    }

    public function test_histories_returns_paginated_results(): void
    {
        $process = Process::factory()->create();

        // Create multiple production histories for the process
        ProductionHistory::factory()->count(15)->create([
            'process_id' => $process->process_id,
            'start' => fn () => Carbon::now()->subHours(rand(1, 24)),
        ]);

        // Create some for other processes
        ProductionHistory::factory()->count(5)->create();

        $result = $this->repository->histories($process->process_id, 10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(15, $result->total());
        $this->assertCount(10, $result->items());

        // Verify all items belong to the correct process
        foreach ($result->items() as $item) {
            $this->assertEquals($process->process_id, $item->process_id);
        }
    }

    public function test_histories_orders_by_start_descending(): void
    {
        $process = Process::factory()->create();

        $oldest = ProductionHistory::factory()->create([
            'process_id' => $process->process_id,
            'start' => Carbon::now()->subDays(3),
        ]);

        $newest = ProductionHistory::factory()->create([
            'process_id' => $process->process_id,
            'start' => Carbon::now(),
        ]);

        $middle = ProductionHistory::factory()->create([
            'process_id' => $process->process_id,
            'start' => Carbon::now()->subDay(),
        ]);

        $result = $this->repository->histories($process->process_id, 10);

        $items = $result->items();
        $this->assertEquals($newest->production_history_id, $items[0]->production_history_id);
        $this->assertEquals($middle->production_history_id, $items[1]->production_history_id);
        $this->assertEquals($oldest->production_history_id, $items[2]->production_history_id);
    }

    public function test_histories_loads_indicator_line_relationship(): void
    {
        $process = Process::factory()->create();

        ProductionHistory::factory()->create([
            'process_id' => $process->process_id,
        ]);

        $result = $this->repository->histories($process->process_id, 10);

        $this->assertTrue($result->first()->relationLoaded('indicatorLine'));
    }

    public function test_histories_returns_empty_paginator_for_process_without_histories(): void
    {
        $process = Process::factory()->create();

        $result = $this->repository->histories($process->process_id, 10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(0, $result->total());
        $this->assertCount(0, $result->items());
    }

    public function test_complete_production_workflow(): void
    {
        // Create necessary models
        $process = Process::factory()->create();
        $partNumber = PartNumber::factory()->create();
        $cycleTime = CycleTime::factory()->create([
            'process_id' => $process->process_id,
            'part_number_id' => $partNumber->part_number_id,
        ]);

        // Start production
        $history = $this->repository->storeHistory(
            $process,
            $cycleTime,
            ProductionStatus::RUNNING(),
            500
        );

        $this->assertNotNull($history);
        $this->assertEquals(ProductionStatus::RUNNING(), $history->status);

        // Simulate breakdown
        $this->repository->updateStatus($history, ProductionStatus::BREAKDOWN());
        $history->refresh();
        $this->assertEquals(ProductionStatus::BREAKDOWN(), $history->status);

        // Resume production
        $this->repository->updateStatus($history, ProductionStatus::RUNNING());
        $history->refresh();
        $this->assertEquals(ProductionStatus::RUNNING(), $history->status);

        // Stop production
        $stopTime = Carbon::now()->addHours(8);
        $result = $this->repository->stop($history, $stopTime);

        $this->assertTrue($result);
        $history->refresh();
        $this->assertEquals(ProductionStatus::COMPLETE(), $history->status);
        $this->assertEquals($stopTime->format('Y-m-d H:i:s'), $history->stop->format('Y-m-d H:i:s'));
    }
}
