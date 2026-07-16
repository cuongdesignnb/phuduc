<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(ProductionDefaultsSeeder::class);

        if (! app()->environment('production')) {
            User::query()->firstOrCreate(
                ['email' => 'admin@phuducev.vn'],
                [
                    'name' => 'Admin',
                    'password' => 'password',
                    'is_admin' => true,
                    'email_verified_at' => now(),
                ],
            );

            $this->call(DemoContentSeeder::class);
        }
    }
}
