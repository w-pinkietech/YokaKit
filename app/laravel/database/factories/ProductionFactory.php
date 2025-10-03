<?php

namespace Database\Factories;

use App\Enums\ProductionStatus;
use App\Models\ProductionLine;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Production>
 */
class ProductionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'production_line_id' => ProductionLine::factory(),
            'at' => Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s.u'),
            'count' => $this->faker->numberBetween(0, 1000),
            'defective_count' => $this->faker->numberBetween(0, 50),
            'status' => $this->faker->randomElement([
                ProductionStatus::RUNNING(),
                ProductionStatus::COMPLETE(),
                ProductionStatus::BREAKDOWN(),
                ProductionStatus::CHANGEOVER(),
            ]),
            'in_planned_outage' => $this->faker->boolean(10), // 10% chance
            'working_time' => $this->faker->numberBetween(1000, 10000),
            'loading_time' => $this->faker->numberBetween(900, 9500),
            'operating_time' => $this->faker->numberBetween(800, 9000),
            'net_time' => $this->faker->numberBetween(700, 8500),
            'breakdown_count' => $this->faker->numberBetween(0, 10),
            'auto_resume_count' => $this->faker->numberBetween(0, 5),
        ];
    }
}
