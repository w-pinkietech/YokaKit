<?php

namespace App\Repositories;

use App\Enums\ProductionStatus;
use App\Models\CycleTime;
use App\Models\Process;
use App\Models\ProductionHistory;
use App\Services\Utility;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * 生産履歴リポジトリ
 *
 * @extends AbstractRepository<ProductionHistory>
 */
class ProductionHistoryRepository extends AbstractRepository
{
    /**
     * モデルクラス
     *
     * @return class-string
     */
    public function model(): string
    {
        return ProductionHistory::class;
    }

    /**
     * 稼働履歴の生産ステータスを更新する
     *
     * @param ProductionHistory $history 対象の稼働履歴
     * @param ProductionStatus $status ステータス
     * @return void
     */
    public function updateStatus(ProductionHistory $history, ProductionStatus $status): void
    {
        $result = $this->updateModel($history, ['status' => $status]);
        Utility::throwIfException($history, $result);
    }

    /**
     * 稼働履歴を登録する
     *
     * @param Process $process 工程
     * @param CycleTime $cycleTime サイクルタイム
     * @param ProductionStatus $status ステータス
     * @param integer|null $goal 目標値
     * @return ProductionHistory|null 稼働履歴
     */
    public function storeHistory(Process $process, CycleTime $cycleTime, ProductionStatus $status, ?int $goal = null): ?ProductionHistory
    {
        $productionHistory = new ProductionHistory([
            'process_id' => $process->process_id,
            'part_number_id' => $cycleTime->part_number_id,
            'process_name' => $process->process_name,
            'plan_color' => $process->plan_color,
            'part_number_name' => $cycleTime->partNumber->part_number_name,
            'cycle_time' => $cycleTime->cycle_time,
            'over_time' => $cycleTime->over_time,
            'goal' => $goal,
            'start' => Utility::now(),
            'status' => $status,
        ]);
        return $this->storeModel($productionHistory) ? $productionHistory : null;
    }

    /**
     * 稼働を停止する
     *
     * @param ProductionHistory $history 稼働履歴
     * @param Carbon $date 停止時刻
     * @return boolean 成否
     */
    public function stop(ProductionHistory $history, Carbon $date): bool
    {
        return $this->updateModel($history, [
            'stop' => $date,
            'status' => ProductionStatus::COMPLETE(),
        ]);
    }

    /**
     * 指定した工程IDの稼働履歴を取得する
     *
     * @param integer $processId 工程ID
     * @param integer $page ページあたりの件数
     * @return LengthAwarePaginator<ProductionHistory>
     */
    public function histories(int $processId, int $page): LengthAwarePaginator
    {
        return $this->model
            ->where('process_id', $processId)
            ->orderBy('start', 'desc')
            ->with('indicatorLine')
            ->paginate($page);
    }
}
