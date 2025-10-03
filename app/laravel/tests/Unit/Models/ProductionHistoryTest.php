<?php

namespace Tests\Unit\Models;

use App\Enums\ProductionStatus;
use App\Models\Process;
use App\Models\ProductionHistory;
use App\Models\ProductionLine;
use App\Models\ProductionPlannedOutage;
use App\Services\Utility;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected ProductionHistory $productionHistory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productionHistory = ProductionHistory::factory()->create();
    }

    public function test_fillable_attributes(): void
    {
        $fillable = [
            'process_id',
            'part_number_id',
            'process_name',
            'part_number_name',
            'plan_color',
            'cycle_time',
            'over_time',
            'goal',
            'start',
            'stop',
            'status',
        ];

        $this->assertEquals($fillable, $this->productionHistory->getFillable());
    }

    public function test_hidden_attributes(): void
    {
        $hidden = [
            'created_at',
            'updated_at',
        ];

        $this->assertEquals($hidden, $this->productionHistory->getHidden());
    }

    public function test_primary_key(): void
    {
        $this->assertEquals('production_history_id', $this->productionHistory->getKeyName());
    }

    public function test_casted_attributes(): void
    {
        $casts = [
            'start' => 'datetime',
            'stop' => 'datetime',
            'status' => ProductionStatus::class,
        ];

        foreach ($casts as $attribute => $expectedCast) {
            $this->assertEquals($expectedCast, $this->productionHistory->getCasts()[$attribute]);
        }
    }

    public function test_appends_status_name(): void
    {
        $this->assertContains('status_name', $this->productionHistory->getAppends());
    }

    public function test_status_name_accessor(): void
    {
        $this->productionHistory->status = ProductionStatus::RUNNING();
        $this->assertEquals(ProductionStatus::RUNNING()->key, $this->productionHistory->status_name);
    }

    public function test_factory_creates_valid_model(): void
    {
        $this->assertInstanceOf(ProductionHistory::class, $this->productionHistory);
        $this->assertIsInt($this->productionHistory->production_history_id);
        $this->assertIsString($this->productionHistory->process_name);
        $this->assertIsString($this->productionHistory->part_number_name);
        $this->assertInstanceOf(Carbon::class, $this->productionHistory->start);
        $this->assertInstanceOf(ProductionStatus::class, $this->productionHistory->status);
    }

    public function test_production_lines_relationship(): void
    {
        $productionLine = ProductionLine::factory()->create([
            'production_history_id' => $this->productionHistory->production_history_id,
            'defective' => false,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->productionHistory->productionLines());
        $this->assertTrue($this->productionHistory->productionLines->contains($productionLine));
    }

    public function test_production_lines_excludes_defective(): void
    {
        ProductionLine::factory()->create([
            'production_history_id' => $this->productionHistory->production_history_id,
            'defective' => true,
        ]);

        $this->assertCount(0, $this->productionHistory->productionLines);
    }

    public function test_indicator_line_relationship(): void
    {
        $indicatorLine = ProductionLine::factory()->create([
            'production_history_id' => $this->productionHistory->production_history_id,
            'indicator' => true,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $this->productionHistory->indicatorLine());
        $this->assertEquals($indicatorLine->production_line_id, $this->productionHistory->indicatorLine->production_line_id);
    }

    public function test_production_planned_outages_relationship(): void
    {
        $productionPlannedOutage = ProductionPlannedOutage::factory()->create([
            'production_history_id' => $this->productionHistory->production_history_id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->productionHistory->productionPlannedOutages());
        $this->assertTrue($this->productionHistory->productionPlannedOutages->contains($productionPlannedOutage));
    }

    public function test_process_relationship(): void
    {
        $process = Process::factory()->create([
            'production_history_id' => $this->productionHistory->production_history_id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $this->productionHistory->process());
        $this->assertEquals($process->process_id, $this->productionHistory->process->process_id);
    }

    public function test_is_complete_returns_true_when_complete(): void
    {
        $this->productionHistory->status = ProductionStatus::COMPLETE();
        $this->assertTrue($this->productionHistory->isComplete());
    }

    public function test_is_complete_returns_false_when_not_complete(): void
    {
        $this->productionHistory->status = ProductionStatus::RUNNING();
        $this->assertFalse($this->productionHistory->isComplete());
    }

    public function test_period_calculates_duration_when_stopped(): void
    {
        $start = Carbon::parse('2023-01-01 10:00:00');
        $stop = Carbon::parse('2023-01-01 12:30:45');

        $this->productionHistory->start = $start;
        $this->productionHistory->stop = $stop;

        $expectedPeriod = '2:30:45';
        $this->assertEquals($expectedPeriod, $this->productionHistory->period());
    }

    public function test_period_uses_current_time_when_not_stopped(): void
    {
        $start = Carbon::parse('2023-01-01 10:00:00');
        $this->productionHistory->start = $start;
        $this->productionHistory->stop = null;

        // Mock Utility::now() to return a specific time
        $now = Carbon::parse('2023-01-01 11:15:30');
        Utility::shouldReceive('now')->andReturn($now);

        $expectedPeriod = '1:15:30';
        $this->assertEquals($expectedPeriod, $this->productionHistory->period());
    }

    public function test_period_formats_single_digits_with_leading_zeros(): void
    {
        $start = Carbon::parse('2023-01-01 10:00:00');
        $stop = Carbon::parse('2023-01-01 10:05:03');

        $this->productionHistory->start = $start;
        $this->productionHistory->stop = $stop;

        $expectedPeriod = '0:05:03';
        $this->assertEquals($expectedPeriod, $this->productionHistory->period());
    }

    public function test_last_product_count_returns_indicator_line_count(): void
    {
        $indicatorLine = ProductionLine::factory()->create([
            'production_history_id' => $this->productionHistory->production_history_id,
            'indicator' => true,
            'count' => 150,
        ]);

        $this->assertEquals(150, $this->productionHistory->lastProductCount());
    }

    public function test_last_product_count_returns_zero_when_no_indicator_line(): void
    {
        $this->assertEquals(0, $this->productionHistory->lastProductCount());
    }

    public function test_cycle_time_ms_converts_seconds_to_milliseconds(): void
    {
        $this->productionHistory->cycle_time = 1.5; // 1.5 seconds

        $this->assertEquals(1500, $this->productionHistory->cycleTimeMs());
    }

    public function test_over_time_ms_converts_seconds_to_milliseconds(): void
    {
        $this->productionHistory->over_time = 0.25; // 0.25 seconds

        $this->assertEquals(250, $this->productionHistory->overTimeMs());
    }

    public function test_cycle_time_ms_handles_fractional_seconds(): void
    {
        $this->productionHistory->cycle_time = 2.75; // 2.75 seconds

        $this->assertEquals(2750, $this->productionHistory->cycleTimeMs());
    }

    public function test_summary_returns_indicator_line_summary(): void
    {
        $mockSummary = ['oee' => 85.5, 'efficiency' => 90.0];
        
        $indicatorLine = ProductionLine::factory()->create([
            'production_history_id' => $this->productionHistory->production_history_id,
            'indicator' => true,
        ]);

        // Mock the summary method
        $indicatorLine->shouldReceive('summary')->andReturn($mockSummary);
        $this->productionHistory->setRelation('indicatorLine', $indicatorLine);

        $this->assertEquals($mockSummary, $this->productionHistory->summary());
    }

    public function test_summary_returns_null_when_no_indicator_line(): void
    {
        $this->assertNull($this->productionHistory->summary());
    }

    public function test_start_and_stop_are_cast_to_carbon_instances(): void
    {
        $this->assertInstanceOf(Carbon::class, $this->productionHistory->start);
        
        if ($this->productionHistory->stop) {
            $this->assertInstanceOf(Carbon::class, $this->productionHistory->stop);
        }
    }

    public function test_status_is_cast_to_production_status_enum(): void
    {
        $this->assertInstanceOf(ProductionStatus::class, $this->productionHistory->status);
    }

    public function test_model_serialization_includes_status_name(): void
    {
        $this->productionHistory->status = ProductionStatus::BREAKDOWN();
        $array = $this->productionHistory->toArray();

        $this->assertArrayHasKey('status_name', $array);
        $this->assertEquals(ProductionStatus::BREAKDOWN()->key, $array['status_name']);
    }

    public function test_model_serialization_hides_timestamps(): void
    {
        $array = $this->productionHistory->toArray();

        $this->assertArrayNotHasKey('created_at', $array);
        $this->assertArrayNotHasKey('updated_at', $array);
    }
}