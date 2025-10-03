<?php

namespace Database\Factories;

use App\Models\ProductionHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductionLine>
 */
class ProductionLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'production_history_id' => ProductionHistory::factory(),
            'line_id' => null,
            'parent_id' => null,
            'line_name' => $this->faker->words(2, true) . ' Line',
            'chart_color' => $this->faker->hexColor,
            'ip_address' => $this->faker->ipv4(),
            'pin_number' => $this->faker->numberBetween(2, 27),
            'defective' => false,
            'order' => $this->faker->numberBetween(1, 100),
            'indicator' => false,
            'offset_count' => null,
            'count' => $this->faker->numberBetween(0, 1000),
        ];
    }
}
