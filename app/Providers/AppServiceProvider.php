<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Inertia::share([
            'cart' => fn() => session()->get('cart', []),
            'flash' => fn() => [
                'success' => session('success'),
                'error' => session('error'),
            ],
            'fontSettings' => function () {
                if (!Schema::hasTable('settings')) return null;
                return [
                    'heading' => Setting::get('font.heading', 'Rajdhani'),
                    'body' => Setting::get('font.body', 'Inter'),
                ];
            },
            'primaryColor' => function () {
                if (!Schema::hasTable('settings')) return null;
                return Setting::get('site.primary_color', '#09DE52');
            },
        ]);
    }
}
