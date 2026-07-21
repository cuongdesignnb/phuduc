<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            HomeContentSeeder::class,
        ]);
    }
}
