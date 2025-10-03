<?php

namespace Tests\Unit\Services;

use App\Http\Requests\UpdateLineWorkerRequest;
use App\Models\Line;
use App\Models\Process;
use App\Models\ProductionHistory;
use App\Models\ProductionLine;
use App\Models\Worker;
use App\Repositories\LineRepository;
use App\Repositories\ProducerRepository;
use App\Repositories\ProductionLineRepository;
use App\Repositories\WorkerRepository;
use App\Services\SwitchService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SwitchServiceTest extends TestCase
{
    use RefreshDatabase;

    private SwitchService $service;

    private LineRepository $lineRepo;

    private ProductionLineRepository $productionLineRepo;

    private ProducerRepository $producerRepo;

    private WorkerRepository $workerRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lineRepo = $this->createMock(LineRepository::class);
        $this->productionLineRepo = $this->createMock(ProductionLineRepository::class);
        $this->producerRepo = $this->createMock(ProducerRepository::class);
        $this->workerRepo = $this->createMock(WorkerRepository::class);

        App::instance(LineRepository::class, $this->lineRepo);
        App::instance(ProductionLineRepository::class, $this->productionLineRepo);
        App::instance(ProducerRepository::class, $this->producerRepo);
        App::instance(WorkerRepository::class, $this->workerRepo);

        $this->service = new SwitchService;
    }

    public function test_update_producer_worker_replaces_existing_worker()
    {
        $now = Carbon::now();
        $productionLineId = 1;
        $oldWorkerId = 1;
        $newWorkerId = 2;

        $existingProducer = $this->createMock(\App\Models\Producer::class);
        $existingProducer->worker_id = $oldWorkerId;

        $newWorker = Worker::factory()->make([
            'worker_id' => $newWorkerId,
        ]);

        $this->producerRepo->expects($this->once())
            ->method('stop')
            ->with($existingProducer, $now);

        $this->workerRepo->expects($this->once())
            ->method('find')
            ->with($newWorkerId)
            ->willReturn($newWorker);

        $this->producerRepo->expects($this->once())
            ->method('save')
            ->with($newWorker, $productionLineId, $now)
            ->willReturn(true);

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('updateProducerWorker');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, $existingProducer, $newWorkerId, $productionLineId, $now);

        $this->assertTrue($result);
    }

    public function test_update_producer_worker_creates_new_producer_when_none_exists()
    {
        $now = Carbon::now();
        $productionLineId = 1;
        $workerId = 1;

        $worker = Worker::factory()->make([
            'worker_id' => $workerId,
        ]);

        $this->workerRepo->expects($this->once())
            ->method('find')
            ->with($workerId)
            ->willReturn($worker);

        $this->producerRepo->expects($this->once())
            ->method('save')
            ->with($worker, $productionLineId, $now)
            ->willReturn(true);

        $this->producerRepo->expects($this->never())
            ->method('stop');

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('updateProducerWorker');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, null, $workerId, $productionLineId, $now);

        $this->assertTrue($result);
    }

    public function test_update_producer_worker_removes_producer_when_worker_is_null()
    {
        $now = Carbon::now();
        $productionLineId = 1;

        $existingProducer = $this->createMock(\App\Models\Producer::class);
        $existingProducer->worker_id = 1;

        $this->producerRepo->expects($this->once())
            ->method('stop')
            ->with($existingProducer, $now);

        $this->producerRepo->expects($this->never())
            ->method('save');

        $this->workerRepo->expects($this->never())
            ->method('find');

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('updateProducerWorker');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, $existingProducer, null, $productionLineId, $now);

        $this->assertTrue($result);
    }

    public function test_update_producer_worker_returns_true_when_no_changes_needed()
    {
        $now = Carbon::now();
        $productionLineId = 1;

        $this->producerRepo->expects($this->never())
            ->method('stop');

        $this->producerRepo->expects($this->never())
            ->method('save');

        $this->workerRepo->expects($this->never())
            ->method('find');

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('updateProducerWorker');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, null, null, $productionLineId, $now);

        $this->assertTrue($result);
    }

    public function test_update_line_worker_updates_multiple_lines_correctly()
    {
        $process = Process::factory()->make(['process_id' => 1]);
        $productionHistory = ProductionHistory::factory()->make(['production_history_id' => 1]);
        $process->setRelation('productionHistory', $productionHistory);

        $line1 = Line::factory()->make(['line_id' => 1]);
        $line2 = Line::factory()->make(['line_id' => 2]);

        $productionLine1 = ProductionLine::factory()->make([
            'production_line_id' => 1,
            'line_id' => 1,
            'defective' => false,
            'production_history_id' => 1,
        ]);

        $productionLine2 = ProductionLine::factory()->make([
            'production_line_id' => 2,
            'line_id' => 2,
            'defective' => false,
            'production_history_id' => 1,
        ]);

        $productionHistory->setRelation('productionLines', collect([$productionLine1, $productionLine2]));

        $worker1 = Worker::factory()->make(['worker_id' => 1]);
        $worker2 = Worker::factory()->make(['worker_id' => 2]);

        $producer1 = $this->createMock(\App\Models\Producer::class);
        $producer1->worker_id = 3;
        $producer2 = null;

        $request = $this->createMock(UpdateLineWorkerRequest::class);
        $request->lines = [
            ['line_id' => 1, 'worker_id' => 1],
            ['line_id' => 2, 'worker_id' => 2],
        ];

        $this->lineRepo->expects($this->exactly(2))
            ->method('updateWorker')
            ->willReturnCallback(function ($lineId, $workerId) {
                $this->assertContains([$lineId, $workerId], [[1, 1], [2, 2]]);

                return true;
            });

        $productionLines = [$productionLine1, $productionLine2];
        $lineIndex = 0;
        $this->productionLineRepo->expects($this->exactly(2))
            ->method('first')
            ->willReturnCallback(function () use ($productionLines, &$lineIndex) {
                return $productionLines[$lineIndex++];
            });

        $producers = [$producer1, $producer2];
        $producerIndex = 0;
        $this->producerRepo->expects($this->exactly(2))
            ->method('findBy')
            ->willReturnCallback(function () use ($producers, &$producerIndex) {
                return $producers[$producerIndex++];
            });

        $workers = [$worker1, $worker2];
        $workerIndex = 0;
        $this->workerRepo->expects($this->exactly(2))
            ->method('find')
            ->willReturnCallback(function () use ($workers, &$workerIndex) {
                return $workers[$workerIndex++];
            });

        $this->producerRepo->expects($this->once())
            ->method('stop')
            ->with($producer1, $this->isInstanceOf(Carbon::class));

        $this->producerRepo->expects($this->exactly(2))
            ->method('save')
            ->willReturn(true);

        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $this->service->updateLineWorker($request, $process);

        // Test passed if no exceptions thrown
        $this->assertTrue(true);
    }

    public function test_update_line_worker_skips_defective_lines()
    {
        $process = Process::factory()->make(['process_id' => 1]);
        $productionHistory = ProductionHistory::factory()->make(['production_history_id' => 1]);
        $process->setRelation('productionHistory', $productionHistory);

        $productionLine = ProductionLine::factory()->make([
            'production_line_id' => 1,
            'line_id' => 1,
            'defective' => true, // This is a defective line
            'production_history_id' => 1,
        ]);

        $productionHistory->setRelation('productionLines', collect([$productionLine]));

        $request = $this->createMock(UpdateLineWorkerRequest::class);
        $request->lines = [
            ['line_id' => 1, 'worker_id' => 1],
        ];

        $this->lineRepo->expects($this->once())
            ->method('updateWorker')
            ->with(1, 1);

        $this->productionLineRepo->expects($this->once())
            ->method('first')
            ->willReturn($productionLine);

        // Should not call producer methods for defective lines
        $this->producerRepo->expects($this->never())
            ->method('findBy');

        $this->producerRepo->expects($this->never())
            ->method('save');

        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $this->service->updateLineWorker($request, $process);

        // Test passed if no exceptions thrown
        $this->assertTrue(true);
    }

    public function test_update_line_worker_throws_exception_when_save_fails()
    {
        $this->expectException(\Exception::class);

        $process = Process::factory()->make(['process_id' => 1]);
        $productionHistory = ProductionHistory::factory()->make(['production_history_id' => 1]);
        $process->setRelation('productionHistory', $productionHistory);

        $productionLine = ProductionLine::factory()->make([
            'production_line_id' => 1,
            'line_id' => 1,
            'defective' => false,
            'production_history_id' => 1,
        ]);

        $productionHistory->setRelation('productionLines', collect([$productionLine]));

        $worker = Worker::factory()->make(['worker_id' => 1]);

        $request = $this->createMock(UpdateLineWorkerRequest::class);
        $request->lines = [
            ['line_id' => 1, 'worker_id' => 1],
        ];

        $this->lineRepo->expects($this->once())
            ->method('updateWorker')
            ->with(1, 1);

        $this->productionLineRepo->expects($this->once())
            ->method('first')
            ->willReturn($productionLine);

        $this->producerRepo->expects($this->once())
            ->method('findBy')
            ->willReturn(null);

        $this->workerRepo->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($worker);

        $this->producerRepo->expects($this->once())
            ->method('save')
            ->willReturn(false); // Save fails

        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $this->service->updateLineWorker($request, $process);
    }
}
