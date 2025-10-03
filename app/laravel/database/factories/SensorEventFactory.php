<?php

namespace Database\Factories;

use App\Enums\SensorType;
use App\Models\Process;
use App\Models\Sensor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SensorEvent>
 */
class SensorEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $sensorTypes = [
            SensorType::GPIO_INPUT,
            SensorType::GPIO_OUTPUT,
            SensorType::AMMETER,
            SensorType::DISTANCE,
            SensorType::THERMOCOUPLE,
            SensorType::ACCELERATION,
            SensorType::DIFFERENCE_PRESSURE,
            SensorType::ILLUMINANCE,
        ];

        return [
            'process_id' => Process::factory(),
            'sensor_id' => Sensor::factory(),
            'ip_address' => $this->faker->ipv4(),
            'identification_number' => $this->faker->numberBetween(1, 999),
            'sensor_type' => $this->faker->randomElement($sensorTypes),
            'alarm_text' => $this->faker->sentence(3),
            'trigger' => $this->faker->boolean(),
            'signal' => $this->faker->boolean(),
            'value' => $this->faker->randomFloat(2, 0, 100),
            'at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
