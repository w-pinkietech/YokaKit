<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AndonLayout>
 */
class AndonLayoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => 1,
            'process_id' => 1,
            'is_display' => $this->faker->boolean(80), // 80% chance of being displayed
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
