<?php

namespace Database\Seeders;

use App\Models\Process;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProcessSeeder extends Seeder
{
    protected $model = \App\Models\Process::class;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Process::factory()->create([
            'process_name' => '工程1',
            'plan_color' => '#FFFFFF',
        ])->create([
            'process_name' => '工程2',
            'plan_color' => '#FFFFFF',
        ])->create([
            'process_name' => '工程3',
            'plan_color' => '#FFFFFF',
        ]);
    }
}
