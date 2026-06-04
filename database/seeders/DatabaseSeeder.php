<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@phuducev.vn',
        ]);

        $this->call([
            SettingSeeder::class,
            ProductSeeder::class,
            MenuSeeder::class,
            PostSeeder::class,
            RealImageSeeder::class,
            HomeContentSeeder::class,
            ReviewSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
