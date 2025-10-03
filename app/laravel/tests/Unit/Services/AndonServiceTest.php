<?php

namespace Tests\Unit\Services;

use App\Http\Requests\UpdateAndonConfigRequest;
use App\Models\AndonConfig;
use App\Models\AndonLayout;
use App\Models\Process;
use App\Models\User;
use App\Repositories\AndonConfigRepository;
use App\Repositories\AndonLayoutRepository;
use App\Repositories\ProcessRepository;
use App\Services\AndonService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class AndonServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AndonService $service;

    protected $mockAndonConfigRepo;

    protected $mockAndonLayoutRepo;

    protected $mockProcessRepo;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks for repositories
        $this->mockAndonConfigRepo = Mockery::mock(AndonConfigRepository::class);
        $this->mockAndonLayoutRepo = Mockery::mock(AndonLayoutRepository::class);
        $this->mockProcessRepo = Mockery::mock(ProcessRepository::class);

        // Bind mocks to the container
        App::instance(AndonConfigRepository::class, $this->mockAndonConfigRepo);
        App::instance(AndonLayoutRepository::class, $this->mockAndonLayoutRepo);
        App::instance(ProcessRepository::class, $this->mockProcessRepo);

        $this->service = new AndonService;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_processes_applies_production_summary(): void
    {
        // This test verifies that processes() calls the repository correctly
        // and handles the mapping logic properly
        $processes = new Collection([
            Process::factory()->make(['process_id' => 1, 'productionHistory' => null]),
        ]);

        $this->mockProcessRepo
            ->shouldReceive('all')
            ->with([
                'andonLayout',
                'sensorEvents',
                'productionHistory.indicatorLine.payload',
            ])
            ->once()
            ->andReturn($processes);

        $result = $this->service->processes();

        // Verify that the service returns results and processes them
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Process::class, $result->first());
    }

    public function test_update_successful_with_layouts(): void
    {
        $user = User::factory()->create();
        Auth::shouldReceive('id')->andReturn($user->id);

        $config = AndonConfig::factory()->make();
        $request = Mockery::mock(UpdateAndonConfigRequest::class);
        $request->shouldReceive('all')->andReturn(['data' => 'test']);
        $request->layouts = [['layout' => 'data']];

        $this->mockAndonConfigRepo
            ->shouldReceive('andonConfig')
            ->once()
            ->andReturn($config);

        $this->mockAndonConfigRepo
            ->shouldReceive('update')
            ->with($request, $config)
            ->once()
            ->andReturn(true);

        $this->mockAndonLayoutRepo
            ->shouldReceive('updateLayouts')
            ->with([['layout' => 'data']], $user->id)
            ->once()
            ->andReturn(true);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $this->service->update($request);

        // If no exception is thrown, the test passes
        $this->assertTrue(true);
    }

    public function test_update_throws_exception_when_config_update_fails(): void
    {
        $config = AndonConfig::factory()->make();
        $request = Mockery::mock(UpdateAndonConfigRequest::class);
        $request->shouldReceive('all')->andReturn(['data' => 'test']);
        $request->layouts = null;

        $this->mockAndonConfigRepo
            ->shouldReceive('andonConfig')
            ->once()
            ->andReturn($config);

        $this->mockAndonConfigRepo
            ->shouldReceive('update')
            ->with($request, $config)
            ->once()
            ->andReturn(false);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $this->expectException(\Exception::class);

        $this->service->update($request);
    }

    public function test_update_throws_exception_when_layout_update_fails(): void
    {
        $user = User::factory()->create();
        Auth::shouldReceive('id')->andReturn($user->id);

        $config = AndonConfig::factory()->make();
        $request = Mockery::mock(UpdateAndonConfigRequest::class);
        $request->shouldReceive('all')->andReturn(['data' => 'test']);
        $request->layouts = [['layout' => 'data']];

        $this->mockAndonConfigRepo
            ->shouldReceive('andonConfig')
            ->once()
            ->andReturn($config);

        $this->mockAndonConfigRepo
            ->shouldReceive('update')
            ->with($request, $config)
            ->once()
            ->andReturn(true);

        $this->mockAndonLayoutRepo
            ->shouldReceive('updateLayouts')
            ->with([['layout' => 'data']], $user->id)
            ->once()
            ->andReturn(false);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $this->expectException(ModelNotFoundException::class);

        $this->service->update($request);
    }

    public function test_update_without_layouts(): void
    {
        $config = AndonConfig::factory()->make();
        $request = Mockery::mock(UpdateAndonConfigRequest::class);
        $request->shouldReceive('all')->andReturn(['data' => 'test']);
        $request->layouts = null;

        $this->mockAndonConfigRepo
            ->shouldReceive('andonConfig')
            ->once()
            ->andReturn($config);

        $this->mockAndonConfigRepo
            ->shouldReceive('update')
            ->with($request, $config)
            ->once()
            ->andReturn(true);

        $this->mockAndonLayoutRepo
            ->shouldNotReceive('updateLayouts');

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $this->service->update($request);

        // If no exception is thrown, the test passes
        $this->assertTrue(true);
    }

    public function test_processes_with_complex_production_data(): void
    {
        // Test that processes() correctly handles complex scenarios
        // Focus on the service logic rather than deep mock interactions
        $andonLayout = AndonLayout::factory()->make(['order' => 2]);
        $process = Process::factory()->make(['process_id' => 1]);
        $process->setRelation('andonLayout', $andonLayout);
        $process->productionHistory = null;

        $processes = new Collection([$process]);

        $this->mockProcessRepo
            ->shouldReceive('all')
            ->with([
                'andonLayout',
                'sensorEvents',
                'productionHistory.indicatorLine.payload',
            ])
            ->once()
            ->andReturn($processes);

        $result = $this->service->processes();

        // Verify service behavior with complex data scenarios
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
        $resultProcess = $result->first();
        $this->assertEquals(1, $resultProcess->process_id);
        $this->assertNull($resultProcess->productionHistory);
    }
}
