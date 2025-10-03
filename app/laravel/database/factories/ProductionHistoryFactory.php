<?php

namespace Database\Factories;

use App\Enums\ProductionStatus;
use App\Models\PartNumber;
use App\Models\Process;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductionHistory>
 */
class ProductionHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $start = $this->faker->dateTimeBetween('-1 week', 'now');
        $isComplete = $this->faker->boolean(30);

        return [
            'process_id' => Process::factory(),
            'part_number_id' => PartNumber::factory(),
            'process_name' => $this->faker->word().' Process',
            'part_number_name' => 'PN-'.$this->faker->numberBetween(1000, 9999),
            'plan_color' => $this->faker->hexColor,
            'cycle_time' => $this->faker->randomFloat(2, 0.5, 5.0), // 0.5 to 5 seconds
            'over_time' => $this->faker->randomFloat(2, 0.1, 1.0), // 0.1 to 1 second
            'goal' => $this->faker->numberBetween(100, 1000),
            'start' => $start,
            'stop' => $isComplete ? $this->faker->dateTimeBetween($start, 'now') : null,
            'status' => $isComplete ? ProductionStatus::COMPLETE() : $this->faker->randomElement([
                ProductionStatus::RUNNING(),
                ProductionStatus::BREAKDOWN(),
                ProductionStatus::CHANGEOVER(),
            ]),
        ];
    }
}
