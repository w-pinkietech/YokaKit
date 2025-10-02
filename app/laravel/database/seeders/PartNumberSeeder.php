<?php

namespace Database\Seeders;

use App\Models\PartNumber;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartNumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PartNumber::factory()->create([
            'part_number_name' => '品番1',
        ])->create([
            'part_number_name' => '品番2',
        ])->create([
            'part_number_name' => '品番3',
        ]);
    }
}
