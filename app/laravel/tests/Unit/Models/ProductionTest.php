<?php

namespace Tests\Unit\Models;

use App\Enums\ProductionStatus;
use App\Models\Production;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionTest extends TestCase
{
    use RefreshDatabase;

    protected Production $production;

    protected function setUp(): void
    {
        parent::setUp();
        $this->production = Production::factory()->create();
    }

    public function test_fillable_attributes(): void
    {
        $fillable = [
            'production_line_id',
            'at',
            'count',
            'defective_count',
            'status',
            'in_planned_outage',
            'working_time',
            'loading_time',
            'operating_time',
            'net_time',
            'breakdown_count',
            'auto_resume_count',
        ];

        $this->assertEquals($fillable, $this->production->getFillable());
    }

    public function test_hidden_attributes(): void
    {
        $hidden = [
            'production_id',
        ];

        $this->assertEquals($hidden, $this->production->getHidden());
    }

    public function test_primary_key(): void
    {
        $this->assertEquals('production_id', $this->production->getKeyName());
    }

    public function test_timestamps_disabled(): void
    {
        $this->assertFalse($this->production->timestamps);
    }

    public function test_casted_attributes(): void
    {
        $casts = [
            'at' => 'datetime:Y-m-d H:i:s.u',
            'status' => ProductionStatus::class,
            'in_planned_outage' => 'boolean',
        ];

        foreach ($casts as $attribute => $expectedCast) {
            $this->assertEquals($expectedCast, $this->production->getCasts()[$attribute]);
        }
    }

    public function test_appends_status_name(): void
    {
        $this->assertContains('status_name', $this->production->getAppends());
    }

    public function test_status_name_accessor(): void
    {
        $this->production->status = ProductionStatus::RUNNING();
        $this->assertEquals(ProductionStatus::RUNNING()->key, $this->production->status_name);
    }

    public function test_factory_creates_valid_model(): void
    {
        $this->assertInstanceOf(Production::class, $this->production);
        $this->assertIsInt($this->production->production_id);
        $this->assertIsInt($this->production->production_line_id);
        $this->assertInstanceOf(Carbon::class, $this->production->at);
        $this->assertIsInt($this->production->count);
        $this->assertIsInt($this->production->defective_count);
        $this->assertInstanceOf(ProductionStatus::class, $this->production->status);
        $this->assertIsBool($this->production->in_planned_outage);
    }

    public function test_at_field_includes_microseconds(): void
    {
        $microsecondTime = '2023-01-01 12:30:45.123456';
        $this->production->at = $microsecondTime;
        $this->production->save();

        $this->production->refresh();
        $this->assertStringContains('.', $this->production->at->format('Y-m-d H:i:s.u'));
    }

    public function test_status_is_cast_to_production_status_enum(): void
    {
        $this->assertInstanceOf(ProductionStatus::class, $this->production->status);
    }

    public function test_in_planned_outage_is_cast_to_boolean(): void
    {
        $this->production->in_planned_outage = 1;
        $this->production->save();
        $this->production->refresh();

        $this->assertIsBool($this->production->in_planned_outage);
        $this->assertTrue($this->production->in_planned_outage);
    }

    public function test_in_planned_outage_false_value(): void
    {
        $this->production->in_planned_outage = 0;
        $this->production->save();
        $this->production->refresh();

        $this->assertIsBool($this->production->in_planned_outage);
        $this->assertFalse($this->production->in_planned_outage);
    }

    public function test_production_timing_fields_are_integers(): void
    {
        $this->assertIsInt($this->production->working_time);
        $this->assertIsInt($this->production->loading_time);
        $this->assertIsInt($this->production->operating_time);
        $this->assertIsInt($this->production->net_time);
    }

    public function test_production_count_fields_are_integers(): void
    {
        $this->assertIsInt($this->production->count);
        $this->assertIsInt($this->production->defective_count);
        $this->assertIsInt($this->production->breakdown_count);
        $this->assertIsInt($this->production->auto_resume_count);
    }

    public function test_model_serialization_includes_status_name(): void
    {
        $this->production->status = ProductionStatus::BREAKDOWN();
        $array = $this->production->toArray();

        $this->assertArrayHasKey('status_name', $array);
        $this->assertEquals(ProductionStatus::BREAKDOWN()->key, $array['status_name']);
    }

    public function test_model_serialization_hides_production_id(): void
    {
        $array = $this->production->toArray();

        $this->assertArrayNotHasKey('production_id', $array);
    }

    public function test_model_serialization_does_not_include_timestamps(): void
    {
        $array = $this->production->toArray();

        $this->assertArrayNotHasKey('created_at', $array);
        $this->assertArrayNotHasKey('updated_at', $array);
    }

    public function test_status_name_updates_when_status_changes(): void
    {
        $this->production->status = ProductionStatus::RUNNING();
        $this->assertEquals(ProductionStatus::RUNNING()->key, $this->production->status_name);

        $this->production->status = ProductionStatus::COMPLETE();
        $this->assertEquals(ProductionStatus::COMPLETE()->key, $this->production->status_name);
    }

    public function test_datetime_format_precision(): void
    {
        $preciseTime = Carbon::parse('2023-01-01 12:30:45.123456');
        $this->production->at = $preciseTime;
        $this->production->save();

        $this->production->refresh();
        $formattedTime = $this->production->at->format('Y-m-d H:i:s.u');
        $this->assertEquals('2023-01-01 12:30:45.123456', $formattedTime);
    }

    public function test_can_create_production_with_all_fillable_fields(): void
    {
        $productionData = [
            'production_line_id' => 1,
            'at' => '2023-01-01 12:00:00.000000',
            'count' => 100,
            'defective_count' => 5,
            'status' => ProductionStatus::RUNNING(),
            'in_planned_outage' => false,
            'working_time' => 3600,
            'loading_time' => 3400,
            'operating_time' => 3200,
            'net_time' => 3000,
            'breakdown_count' => 2,
            'auto_resume_count' => 1,
        ];

        $production = Production::create($productionData);

        $this->assertInstanceOf(Production::class, $production);
        $this->assertEquals($productionData['count'], $production->count);
        $this->assertEquals($productionData['defective_count'], $production->defective_count);
        $this->assertEquals($productionData['working_time'], $production->working_time);
        $this->assertEquals($productionData['breakdown_count'], $production->breakdown_count);
    }

    public function test_all_production_status_enums_work(): void
    {
        $statuses = [
            ProductionStatus::RUNNING(),
            ProductionStatus::COMPLETE(),
            ProductionStatus::BREAKDOWN(),
            ProductionStatus::CHANGEOVER(),
        ];

        foreach ($statuses as $status) {
            $this->production->status = $status;
            $this->assertEquals($status->key, $this->production->status_name);
            $this->assertEquals($status, $this->production->status);
        }
    }
}