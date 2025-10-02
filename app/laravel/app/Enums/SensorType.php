<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Contracts\LocalizedEnum;
use BenSampo\Enum\Enum;

/**
 * センサー種別
 *
 * @method static SensorType UNKNOWN() 不明 (0)
 * @method static SensorType GPIO_INPUT() 接点入力 (257)
 * @method static SensorType GPIO_OUTPUT() 接点出力 (258)
 * @method static SensorType AMMETER() 電流計 (259)
 * @method static SensorType DISTANCE() 測距 (260)
 * @method static SensorType THERMOCOUPLE() 熱電対 (261)
 * @method static SensorType ACCELERATION() 加速度 (262)
 * @method static SensorType DIFFERENCE_PRESSURE() 差圧 (263)
 * @method static SensorType ILLUMINANCE() 照度 (264)
 * @method static SensorType OTHER() その他 (65535)
 */
final class SensorType extends Enum implements LocalizedEnum
{
    public const UNKNOWN = 0;
    public const GPIO_INPUT = 0x0101;
    public const GPIO_OUTPUT = 0x0102;
    public const AMMETER = 0x0103;
    public const DISTANCE = 0x0104;
    public const THERMOCOUPLE = 0x0105;
    public const ACCELERATION = 0x0106;
    public const DIFFERENCE_PRESSURE = 0x0107;
    public const ILLUMINANCE = 0x0108;
    public const OTHER = 0xFFFF;
}
