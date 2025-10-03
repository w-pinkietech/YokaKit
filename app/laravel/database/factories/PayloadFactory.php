<?php

namespace Database\Factories;

use App\Models\Payload;
use App\Models\ProductionLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payload>
 */
class PayloadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payload::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'production_line_id' => ProductionLine::factory(),
            'payload' => [
                'lineId' => $this->faker->numberBetween(1, 100),
                'defectiveCounts' => [
                    ['count' => $this->faker->numberBetween(0, 10), 'at' => now()->toISOString()],
                ],
                'start' => now()->subHours(2)->toISOString(),
                'cycleTimeMs' => $this->faker->numberBetween(10000, 60000),
                'overTimeMs' => $this->faker->numberBetween(1000, 5000),
                'plannedOutages' => [],
                'changeovers' => [],
                'indicator' => true,
            ],
        ];
    }
}
