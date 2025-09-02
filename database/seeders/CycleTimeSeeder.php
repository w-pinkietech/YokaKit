<?php

namespace Database\Seeders;

use App\Models\CycleTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CycleTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CycleTime::factory()->count(1)->create();
    }
}
