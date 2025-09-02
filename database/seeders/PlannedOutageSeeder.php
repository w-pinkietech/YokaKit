<?php

namespace Database\Seeders;

use App\Models\PlannedOutage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlannedOutageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PlannedOutage::factory()->create([
            'planned_outage_name' => '昼休み',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);
    }
}
