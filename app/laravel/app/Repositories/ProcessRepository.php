<?php

namespace App\Repositories;

use App\Models\Process;

/**
 * 工程リポジトリ
 *
 * @extends AbstractRepository<Process>
 */
class ProcessRepository extends AbstractRepository
{
    /**
     * モデルクラス
     *
     * @return class-string
     */
    public function model(): string
    {
        return Process::class;
    }

    /**
     * 指定した工程の生産を開始する
     *
     * @param Process $process 工程
     * @param integer $productionHistoryId 工程履歴ID
     * @return boolean 成否
     */
    public function start(Process $process, int $productionHistoryId): bool
    {
        return $this->updateModel($process, ['production_history_id' => $productionHistoryId]);
    }

    /**
     * 指定した工程の生産を停止する
     *
     * @param Process $process 工程
     * @return boolean 成否
     */
    public function stop(Process $process): bool
    {
        return $this->updateModel($process, ['production_history_id' => null]);
    }
}
