<?php

namespace Database\Factories;

use App\Models\AndonConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AndonConfig>
 */
class AndonConfigFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AndonConfig::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'row_count' => $this->faker->numberBetween(2, 5),
            'column_count' => $this->faker->numberBetween(3, 6),
            'auto_play' => $this->faker->boolean(),
            'auto_play_speed' => $this->faker->numberBetween(1000, 5000),
            'slide_speed' => $this->faker->numberBetween(200, 500),
            'easing' => $this->faker->randomElement(['ease', 'linear', 'ease-in', 'ease-out']),
            'fade' => $this->faker->boolean(),
            'item_column_count' => $this->faker->numberBetween(2, 4),
            'is_show_part_number' => $this->faker->boolean(),
            'is_show_start' => $this->faker->boolean(),
            'is_show_good_count' => $this->faker->boolean(),
            'is_show_good_rate' => $this->faker->boolean(),
            'is_show_defective_count' => $this->faker->boolean(),
            'is_show_defective_rate' => $this->faker->boolean(),
            'is_show_plan_count' => $this->faker->boolean(),
            'is_show_achievement_rate' => $this->faker->boolean(),
            'is_show_cycle_time' => $this->faker->boolean(),
            'is_show_time_operating_rate' => $this->faker->boolean(),
            'is_show_performance_operating_rate' => $this->faker->boolean(),
            'is_show_overall_equipment_effectiveness' => $this->faker->boolean(),
        ];
    }
}
