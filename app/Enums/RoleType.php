<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Contracts\LocalizedEnum;
use BenSampo\Enum\Enum;

/**
 * ユーザー権限種別
 *
 * @method static RoleType SYSTEM() システム管理者
 * @method static RoleType ADMIN() 管理者
 * @method static RoleType USER() ユーザー
 */
final class RoleType extends Enum implements LocalizedEnum
{
    public const SYSTEM = 1;
    public const ADMIN = 5;
    public const USER = 10;
}
