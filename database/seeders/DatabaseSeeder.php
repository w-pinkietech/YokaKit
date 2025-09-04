<?php

namespace Database\Seeders;

use App\Models\RaspberryPi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ProcessSeeder::class,
            PlannedOutageSeeder::class,
            ProcessPlannedOutageSeeder::class,
            PartNumberSeeder::class,
            CycleTimeSeeder::class,
            WorkerSeeder::class,
            RaspberryPiSeeder::class,
            LineSeeder::class,
        ]);

        RaspberryPi::factory()->create([
            'raspberry_pi_name' => '本物ラズパイ',
            'ip_address' => '10.4.5.188',
        ]);
    }
}
