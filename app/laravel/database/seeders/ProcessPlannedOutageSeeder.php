<?php

namespace Database\Seeders;

use App\Models\ProcessPlannedOutage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProcessPlannedOutageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProcessPlannedOutage::factory()->count(1)->create();
    }
}
