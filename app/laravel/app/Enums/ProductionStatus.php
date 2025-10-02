<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Contracts\LocalizedEnum;
use BenSampo\Enum\Enum;

/**
 * 生産ステータス
 *
 * @method static ProductionStatus RUNNING() 稼働中
 * @method static ProductionStatus CHANGEOVER() 段取り替え
 * @method static ProductionStatus BREAKDOWN() チョコ停
 * @method static ProductionStatus COMPLETE() 停止|完了
 */
final class ProductionStatus extends Enum implements LocalizedEnum
{
    public const RUNNING = 1;
    public const CHANGEOVER = 2;
    public const BREAKDOWN = 3;
    public const COMPLETE = 4;
}
