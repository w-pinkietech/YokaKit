<?php

namespace Tests\Unit\Jobs;

use App\Data\PayloadData;
use App\Enums\ProductionStatus;
use App\Events\ProductionSummaryNotification;
use App\Jobs\BreakdownJudgeJob;
use App\Jobs\FinishBreakdownJob;
use App\Models\DefectiveProduction;
use App\Models\Production;
use App\Models\ProductionHistory;
use App\Models\ProductionLine;
use App\Repositories\DefectiveProductionRepository;
use App\Repositories\PayloadRepository;
use App\Repositories\ProductionLineRepository;
use App\Repositories\ProductionRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FinishBreakdownJobTest extends TestCase
{
    private const TEST_PROCESS_NAME = 'Test Process';
    private const TEST_PART_NAME = 'Test Part';

    private int $productionLineId = 1;
    private int $count = 10;
    private Carbon $date;
    private $payloadData;
    private $production;
    private $lineInHistory;
    private $productionHistory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->date = Carbon::now();
        Event::fake();

        // Setup common mocks
        $this->setupPayloadData();
        $this->setupProduction();
        $this->setupProductionHistory();
        $this->mockDatabaseTransaction();
    }

    private function setupPayloadData(): void
    {
        $this->payloadData = $this->createMock(PayloadData::class);
        $this->payloadData->method('status')->willReturn(ProductionStatus::RUNNING());
        $this->payloadData->method('toArray')->willReturn([]);
        $this->payloadData->method('inPlannedOutage')->willReturn(false);
        $this->payloadData->breakdowns = [];
    }

    private function setupProduction(): void
    {
        $this->production = Mockery::mock(Production::class)->makePartial();
        $this->production->shouldIgnoreMissing();
        $this->production->at = Carbon::now();
    }

    private function setupProductionHistory(): void
    {
        $this->lineInHistory = Mockery::mock(ProductionLine::class)->makePartial();
        $this->lineInHistory->shouldIgnoreMissing();
        $this->lineInHistory->production_line_id = 1;

        $this->productionHistory = Mockery::mock(ProductionHistory::class)->makePartial();
        $this->productionHistory->shouldIgnoreMissing();
        $this->productionHistory->productionLines = [$this->lineInHistory];
        $this->productionHistory->start = Carbon::now();
        $this->productionHistory->process_id = 1;
        $this->productionHistory->process_name = self::TEST_PROCESS_NAME;
        $this->productionHistory->production_history_id = 1;
        $this->productionHistory->part_number_id = 1;
        $this->productionHistory->part_number_name = self::TEST_PART_NAME;
        $this->productionHistory->shouldReceive('overTimeMs')->andReturn(5000);
    }

    private function mockDatabaseTransaction(): void
    {
        DB::shouldReceive('transaction')
            ->andReturnUsing(function ($callback) {
                return $callback();
            });
    }

    private function mockAppMake(
        PayloadRepository $payloadRepository,
        ProductionLineRepository $productionLineRepository,
        ProductionRepository $productionRepository,
        ?DefectiveProductionRepository $defectiveProductionRepository = null
    ): void {
        App::shouldReceive('make')
            ->with(PayloadRepository::class)
            ->andReturn($payloadRepository);
        App::shouldReceive('make')
            ->with(ProductionLineRepository::class)
            ->andReturn($productionLineRepository);
        App::shouldReceive('make')
            ->with(ProductionRepository::class)
            ->andReturn($productionRepository);

        if ($defectiveProductionRepository !== null) {
            App::shouldReceive('make')
                ->with(DefectiveProductionRepository::class)
                ->andReturn($defectiveProductionRepository);
        }
    }

    #[Test]
    public function it_handles_non_defective_production(): void
    {
        $payloadRepository = $this->createMock(PayloadRepository::class);
        $productionLineRepository = $this->createMock(ProductionLineRepository::class);
        $productionRepository = $this->createMock(ProductionRepository::class);

        $productionLine = Mockery::mock(ProductionLine::class)->makePartial();
        $productionLine->shouldIgnoreMissing();
        $productionLine->defective = false;
        $productionLine->productionHistory = $this->productionHistory;

        $payloadRepository->expects($this->once())
            ->method('updatePayload')
            ->willReturnCallback(fn($line, $callback) => $callback($this->payloadData) ?? $this->payloadData);

        $productionLineRepository->expects($this->once())
            ->method('find')
            ->with($this->productionLineId)
            ->willReturn($productionLine);

        $productionRepository->expects($this->once())
            ->method('save')
            ->willReturn($this->production);

        $this->mockAppMake($payloadRepository, $productionLineRepository, $productionRepository);

        // Mock BreakdownJudgeJob::delayedDispatch
        Mockery::mock('alias:' . BreakdownJudgeJob::class)
            ->shouldReceive('delayedDispatch')
            ->withAnyArgs();

        $job = new FinishBreakdownJob($this->productionLineId, $this->count, $this->date);
        $job->handle();

        Event::assertDispatched(ProductionSummaryNotification::class);
    }

    #[Test]
    public function it_handles_defective_production(): void
    {
        $payloadRepository = $this->createMock(PayloadRepository::class);
        $productionLineRepository = $this->createMock(ProductionLineRepository::class);
        $productionRepository = $this->createMock(ProductionRepository::class);
        $defectiveProductionRepository = $this->createMock(DefectiveProductionRepository::class);

        $defectiveProduction = Mockery::mock(DefectiveProduction::class)->makePartial();
        $defectiveProduction->shouldIgnoreMissing();

        $productionLine = Mockery::mock(ProductionLine::class)->makePartial();
        $productionLine->shouldIgnoreMissing();
        $productionLine->defective = true;
        $productionLine->parent_id = 1;
        $productionLine->productionHistory = $this->productionHistory;

        $payloadRepository->expects($this->once())
            ->method('updatePayload')
            ->willReturnCallback(fn($line, $callback) => $callback($this->payloadData) ?? $this->payloadData);

        $productionLineRepository->expects($this->once())
            ->method('find')
            ->willReturn($productionLine);

        $productionRepository->expects($this->once())
            ->method('save')
            ->willReturn($this->production);

        $defectiveProductionRepository->expects($this->once())
            ->method('save')
            ->willReturn($defectiveProduction);

        $this->mockAppMake($payloadRepository, $productionLineRepository, $productionRepository, $defectiveProductionRepository);

        // Mock BreakdownJudgeJob::delayedDispatch
        Mockery::mock('alias:' . BreakdownJudgeJob::class)
            ->shouldReceive('delayedDispatch')
            ->withAnyArgs();

        $job = new FinishBreakdownJob($this->productionLineId, $this->count, $this->date);
        $job->handle();
    }

    #[Test]
    public function it_dispatches_breakdown_judge_job(): void
    {
        $payloadRepository = $this->createMock(PayloadRepository::class);
        $productionLineRepository = $this->createMock(ProductionLineRepository::class);
        $productionRepository = $this->createMock(ProductionRepository::class);

        $productionLine = Mockery::mock(ProductionLine::class)->makePartial();
        $productionLine->shouldIgnoreMissing();
        $productionLine->defective = false;
        $productionLine->productionHistory = $this->productionHistory;

        $payloadRepository->expects($this->once())
            ->method('updatePayload')
            ->willReturnCallback(fn($line, $callback) => $callback($this->payloadData) ?? $this->payloadData);

        $productionLineRepository->expects($this->once())
            ->method('find')
            ->with($this->productionLineId)
            ->willReturn($productionLine);

        $productionRepository->expects($this->once())
            ->method('save')
            ->willReturn($this->production);

        $this->mockAppMake($payloadRepository, $productionLineRepository, $productionRepository);

        // Override the default mock to verify delayedDispatch is called with correct arguments
        $breakdownJudgeJobMock = Mockery::mock('alias:' . BreakdownJudgeJob::class);
        $breakdownJudgeJobMock->shouldReceive('delayedDispatch')
            ->once()
            ->with(5000, $this->production);

        $job = new FinishBreakdownJob($this->productionLineId, $this->count, $this->date);
        $job->handle();
    }
}
