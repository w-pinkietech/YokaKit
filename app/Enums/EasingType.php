<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Contracts\LocalizedEnum;
use BenSampo\Enum\Enum;

/**
 * アニメーション種別
 *
 * @method static EasingType EASE()
 * @method static EasingType LINEAR()
 * @method static EasingType EASE_IN()
 * @method static EasingType EASE_OUT()
 * @method static EasingType EASE_IN_OUT()
 */
final class EasingType extends Enum implements LocalizedEnum
{
    public const EASE = 'ease';
    public const LINEAR = 'linear';
    public const EASE_IN = 'ease-in';
    public const EASE_OUT = 'ease-out';
    public const EASE_IN_OUT = 'ease-in-out';
}
