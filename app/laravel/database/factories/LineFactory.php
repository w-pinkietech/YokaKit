<?php

namespace Database\Factories;

use App\Models\Process;
use App\Models\RaspberryPi;
use App\Models\Worker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Line>
 */
class LineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'line_name' => __('yokakit.line')."：{$this->faker->unique()->realText(10)}",
            'chart_color' => $this->faker->hexColor,
            'pin_number' => $this->faker->numberBetween(2, 27),
            'worker_id' => Worker::factory(),
            'process_id' => Process::factory(),
            'raspberry_pi_id' => RaspberryPi::factory(),
            'order' => $this->faker->numberBetween(1, 10),
            'defective' => $this->faker->boolean(20), // 20% chance of defective
        ];
    }
}
