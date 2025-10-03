<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductionPlannedOutage>
 */
class ProductionPlannedOutageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $startTime = $this->faker->dateTimeThisYear();
        $endTime = (clone $startTime)->modify('+'.$this->faker->numberBetween(10, 120).' minutes');

        return [
            'production_history_id' => 1,
            'planned_outage_name' => $this->faker->words(2, true),
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
    }
}
