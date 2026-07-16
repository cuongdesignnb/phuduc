<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProductSeeder::class,
            MenuSeeder::class,
            PostSeeder::class,
            RealImageSeeder::class,
            ReviewSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
