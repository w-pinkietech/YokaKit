<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Contracts\LocalizedEnum;
use BenSampo\Enum\Enum;

/**
 * アンドンレイアウトの列数
 *
 * @method static AndonColumnSize ONE()
 * @method static AndonColumnSize TWO()
 * @method static AndonColumnSize THREE()
 * @method static AndonColumnSize FOUR()
 * @method static AndonColumnSize SIX()
 * @method static AndonColumnSize TWELVE()
 */
final class AndonColumnSize extends Enum implements LocalizedEnum
{
    public const ONE = 1;
    public const TWO = 2;
    public const THREE = 3;
    public const FOUR = 4;
    public const SIX = 6;
    public const TWELVE = 12;
}
