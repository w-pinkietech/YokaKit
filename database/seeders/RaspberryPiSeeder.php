<?php

namespace Database\Seeders;

use App\Models\RaspberryPi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RaspberryPiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RaspberryPi::factory()->create([
            'raspberry_pi_name' => '本物',
            'ip_address' => '10.4.5.188',
        ]);
    }
}
