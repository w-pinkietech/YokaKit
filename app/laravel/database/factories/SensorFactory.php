<?php

namespace Database\Factories;

use App\Enums\SensorType;
use App\Models\Process;
use App\Models\RaspberryPi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sensor>
 */
class SensorFactory extends Factory
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
            'raspberry_pi_id' => RaspberryPi::factory(),
            'identification_number' => $this->faker->numberBetween(1, 999),
            'sensor_type' => $this->faker->randomElement($sensorTypes),
            'alarm_text' => $this->faker->sentence(3),
            'trigger' => $this->faker->boolean(),
        ];
    }
}
