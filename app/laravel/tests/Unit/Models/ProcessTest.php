<?php

namespace Tests\Unit\Models;

use App\Enums\ProductionStatus;
use App\Models\AndonLayout;
use App\Models\Line;
use App\Models\OnOff;
use App\Models\OnOffEvent;
use App\Models\PartNumber;
use App\Models\PlannedOutage;
use App\Models\Process;
use App\Models\ProductionHistory;
use App\Models\RaspberryPi;
use App\Models\Sensor;
use App\Models\SensorEvent;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessTest extends TestCase
{
    use RefreshDatabase;

    protected Process $process;

    protected function setUp(): void
    {
        parent::setUp();
        $this->process = Process::factory()->create();
    }

    public function test_fillable_attributes(): void
    {
        $fillable = [
            'production_history_id',
            'process_name',
            'plan_color',
            'remark',
        ];

        $this->assertEquals($fillable, $this->process->getFillable());
    }

    public function test_hidden_attributes(): void
    {
        $hidden = [
            'created_at',
            'updated_at',
        ];

        $this->assertEquals($hidden, $this->process->getHidden());
    }

    public function test_primary_key(): void
    {
        $this->assertEquals('process_id', $this->process->getKeyName());
    }

    public function test_factory_creates_valid_model(): void
    {
        $this->assertInstanceOf(Process::class, $this->process);
        $this->assertIsInt($this->process->process_id);
        $this->assertIsString($this->process->process_name);
        $this->assertIsString($this->process->plan_color);
    }

    public function test_planned_outages_relationship(): void
    {
        $plannedOutage = PlannedOutage::factory()->create();
        $this->process->plannedOutages()->attach($plannedOutage);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $this->process->plannedOutages());
        $this->assertTrue($this->process->plannedOutages->contains($plannedOutage));
    }

    public function test_part_numbers_relationship(): void
    {
        $partNumber = PartNumber::factory()->create();
        $this->process->partNumbers()->attach($partNumber, [
            'cycle_time' => 30,
            'over_time' => 5,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $this->process->partNumbers());
        $this->assertTrue($this->process->partNumbers->contains($partNumber));
        $this->assertEquals(30, $this->process->partNumbers->first()->pivot->cycle_time);
    }

    public function test_raspberry_pis_relationship(): void
    {
        $raspberryPi = RaspberryPi::factory()->create();
        $worker = Worker::factory()->create();

        Line::factory()->create([
            'process_id' => $this->process->process_id,
            'raspberry_pi_id' => $raspberryPi->raspberry_pi_id,
            'worker_id' => $worker->worker_id,
            'order' => 1,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $this->process->raspberryPis());
        $this->assertTrue($this->process->raspberryPis->contains($raspberryPi));
    }

    public function test_lines_relationship(): void
    {
        $worker = Worker::factory()->create();
        $raspberryPi = RaspberryPi::factory()->create();

        $line = Line::factory()->create([
            'process_id' => $this->process->process_id,
            'worker_id' => $worker->worker_id,
            'raspberry_pi_id' => $raspberryPi->raspberry_pi_id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->process->lines());
        $this->assertTrue($this->process->lines->contains($line));
    }

    public function test_sensors_relationship(): void
    {
        $raspberryPi = RaspberryPi::factory()->create();
        $sensor = Sensor::factory()->create([
            'process_id' => $this->process->process_id,
            'raspberry_pi_id' => $raspberryPi->raspberry_pi_id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->process->sensors());
        $this->assertTrue($this->process->sensors->contains($sensor));
    }

    public function test_sensor_events_relationship_filters_recent_and_triggered(): void
    {
        $sensor = Sensor::factory()->create(['process_id' => $this->process->process_id]);

        // Create recent triggered event
        $recentEvent = SensorEvent::factory()->create([
            'process_id' => $this->process->process_id,
            'sensor_id' => $sensor->sensor_id,
            'at' => now(),
            'trigger' => true,
            'signal' => true,
        ]);

        // Create old event (should be filtered out)
        SensorEvent::factory()->create([
            'process_id' => $this->process->process_id,
            'sensor_id' => $sensor->sensor_id,
            'at' => now()->subDays(10),
            'trigger' => true,
            'signal' => true,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->process->sensorEvents());
        $this->assertTrue($this->process->sensorEvents->contains($recentEvent));
        $this->assertCount(1, $this->process->sensorEvents);
    }

    public function test_on_offs_relationship(): void
    {
        $raspberryPi = RaspberryPi::factory()->create();
        $onOff = OnOff::factory()->create([
            'process_id' => $this->process->process_id,
            'raspberry_pi_id' => $raspberryPi->raspberry_pi_id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->process->onOffs());
        $this->assertTrue($this->process->onOffs->contains($onOff));
    }

    public function test_on_off_events_relationship_limits_recent_events(): void
    {
        // Create multiple events
        for ($i = 0; $i < 15; $i++) {
            OnOffEvent::factory()->create([
                'process_id' => $this->process->process_id,
                'at' => now()->subHours($i),
            ]);
        }

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->process->onOffEvents());
        $this->assertLessThanOrEqual(10, $this->process->onOffEvents->count());
    }

    public function test_production_history_relationship(): void
    {
        $productionHistory = ProductionHistory::factory()->create();
        $this->process->update(['production_history_id' => $productionHistory->production_history_id]);
        $this->process->refresh();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $this->process->productionHistory());
        $this->assertEquals($productionHistory->production_history_id, $this->process->productionHistory->production_history_id);
    }

    public function test_andon_layout_relationship_with_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $andonLayout = AndonLayout::create([
            'user_id' => $user->id,
            'process_id' => $this->process->process_id,
            'is_display' => true,
            'order' => 1,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $this->process->andonLayout());
        $this->assertEquals($andonLayout->andon_layout_id, $this->process->andonLayout->andon_layout_id);
    }

    public function test_is_running_when_not_stopped(): void
    {
        $productionHistory = ProductionHistory::factory()->create([
            'status' => ProductionStatus::RUNNING(),
        ]);
        $this->process->update(['production_history_id' => $productionHistory->production_history_id]);
        $this->process->refresh();

        $this->assertTrue($this->process->isRunning());
        $this->assertFalse($this->process->isStopped());
    }

    public function test_is_stopped_when_complete(): void
    {
        $productionHistory = ProductionHistory::factory()->create([
            'status' => ProductionStatus::COMPLETE(),
        ]);
        $this->process->update(['production_history_id' => $productionHistory->production_history_id]);
        $this->process->refresh();

        $this->assertFalse($this->process->isRunning());
        $this->assertTrue($this->process->isStopped());
    }

    public function test_is_stopped_when_no_production_history(): void
    {
        $this->assertTrue($this->process->isStopped());
        $this->assertFalse($this->process->isRunning());
    }

    public function test_is_changeover(): void
    {
        $productionHistory = ProductionHistory::factory()->create([
            'status' => ProductionStatus::CHANGEOVER(),
        ]);
        $this->process->update(['production_history_id' => $productionHistory->production_history_id]);
        $this->process->refresh();

        $this->assertTrue($this->process->isChangeover());
    }

    public function test_is_not_changeover(): void
    {
        $productionHistory = ProductionHistory::factory()->create([
            'status' => ProductionStatus::RUNNING(),
        ]);
        $this->process->update(['production_history_id' => $productionHistory->production_history_id]);
        $this->process->refresh();

        $this->assertFalse($this->process->isChangeover());
    }

    public function test_status_returns_production_history_status(): void
    {
        $productionHistory = ProductionHistory::factory()->create([
            'status' => ProductionStatus::RUNNING(),
        ]);
        $this->process->update(['production_history_id' => $productionHistory->production_history_id]);
        $this->process->refresh();

        $this->assertEquals(ProductionStatus::RUNNING(), $this->process->status());
    }

    public function test_status_returns_complete_when_no_production_history(): void
    {
        $this->assertEquals(ProductionStatus::COMPLETE(), $this->process->status());
    }

    public function test_info_returns_mapped_process_data(): void
    {
        $info = $this->process->info();

        $this->assertIsArray($info);
        $this->assertArrayHasKey('process_id', $info);
        $this->assertArrayHasKey('process_name', $info);
        $this->assertArrayHasKey('status', $info);
        $this->assertArrayHasKey('production_history', $info);
    }

    public function test_info_removes_sensitive_production_history_fields(): void
    {
        $productionHistory = ProductionHistory::factory()->create();
        $this->process->update(['production_history_id' => $productionHistory->production_history_id]);
        $this->process->refresh();

        $info = $this->process->info();

        // Check sensitive fields are removed from main info
        $this->assertArrayNotHasKey('plan_color', $info);
        $this->assertArrayNotHasKey('production_history_id', $info);
        $this->assertArrayNotHasKey('remark', $info);

        // Check sensitive fields are removed from production_history
        $this->assertIsArray($info['production_history']);
        $this->assertArrayNotHasKey('production_history_id', $info['production_history']);
        $this->assertArrayNotHasKey('status', $info['production_history']);
        $this->assertArrayNotHasKey('status_name', $info['production_history']);
        $this->assertArrayNotHasKey('plan_color', $info['production_history']);
        $this->assertArrayNotHasKey('process_id', $info['production_history']);
        $this->assertArrayNotHasKey('process_name', $info['production_history']);
    }

    public function test_info_with_null_production_history(): void
    {
        $info = $this->process->info();

        $this->assertNull($info['production_history']);
    }
}
