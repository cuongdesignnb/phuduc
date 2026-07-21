<?php

namespace App\Providers;

use App\Services\Storefront\NavigationService;
use App\Services\Storefront\SiteConfigurationService;
use App\Services\Storefront\ThemeTokenService;
use Illuminate\Support\Facades\View;
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
        $this->app->scoped(ThemeTokenService::class);
        $this->app->scoped(SiteConfigurationService::class);
        $this->app->scoped(NavigationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        View::composer('app', function ($view): void {
            $view->with('rootSite', app(SiteConfigurationService::class)->get());
        });

        Inertia::share([
            'cart' => fn () => session()->get('cart', []),
            'flash' => fn () => [
                'success' => session('success'),
                'error' => session('error'),
            ],
            'fontSettings' => fn () => app(SiteConfigurationService::class)->get()['fonts'],
            'primaryColor' => fn () => app(SiteConfigurationService::class)->get()['primary_color'],
        ]);
    }
}
