<?php

use App\Enums\ProductionStatus;
use App\Enums\RoleType;
use App\Enums\SensorType;

return [
    ProductionStatus::class => [
        ProductionStatus::RUNNING => '稼働中',
        ProductionStatus::CHANGEOVER => '段取り替え',
        ProductionStatus::BREAKDOWN => 'チョコ停',
        ProductionStatus::COMPLETE => '停止',
    ],
    RoleType::class => [
        RoleType::SYSTEM => 'システム管理者',
        RoleType::ADMIN => '管理者',
        RoleType::USER => 'ユーザー',
    ],
    SensorType::class => [
        SensorType::UNKNOWN => '不明',
        SensorType::GPIO_INPUT => '接点入力',
        SensorType::GPIO_OUTPUT => '接点出力',
        SensorType::AMMETER => '電流計',
        SensorType::DISTANCE => '測距',
        SensorType::THERMOCOUPLE => '熱電対',
        SensorType::ACCELERATION => '加速度',
        SensorType::DIFFERENCE_PRESSURE => '差圧',
        SensorType::ILLUMINANCE => '照度',
        SensorType::OTHER => 'その他',
    ],
];
