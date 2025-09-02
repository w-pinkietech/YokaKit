<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'name' => 'システム管理者',
            'email' => 'iot@fitc.pref.fukuoka.jp',
            'role' => 1,
            'password' => Hash::make('fitc1234')
        ])->create([
            'name' => '田口智之',
            'email' => 'taguchi-t@fitc.pref.fukuoka.jp',
            'role' => 5,
            'password' => Hash::make('taguchi-t')
        ])->create([
            'name' => '林宏充',
            'email' => 'hayashih@fitc.pref.fukuoka.jp',
            'role' => 5,
            'password' => Hash::make('hayashih')
        ])->create([
            'name' => '渡邉恭弘',
            'email' => 'y-watanabe@fitc.pref.fukuoka.jp',
            'role' => 5,
            'password' => Hash::make('y-watanabe')
        ])->create([
            'name' => 'ゲスト',
            'email' => 'fen@fitc.pref.fukuoka.jp',
            'role' => 10,
            'password' => Hash::make('fen')
        ]);
    }
}
