<?php

namespace App\Providers;

use App\Services\Storefront\CartSessionService;
use App\Services\Storefront\NavigationService;
use App\Services\Storefront\SiteConfigurationService;
use App\Services\Storefront\ThemeTokenService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
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

        $commerceSessionKey = function (Request $request): string {
            $key = $request->session()->get('_commerce_rate_limit_key');
            if (! is_string($key) || $key === '') {
                $key = Str::random(40);
                $request->session()->put('_commerce_rate_limit_key', $key);
            }

            return 'session:'.$key.'|ip:'.$request->ip();
        };

        RateLimiter::for('commerce-cart', fn (Request $request) => Limit::perMinute(60)->by($commerceSessionKey($request)));
        RateLimiter::for('commerce-checkout', fn (Request $request) => Limit::perMinute(10)->by($commerceSessionKey($request)));
        RateLimiter::for('commerce-reviews', fn (Request $request) => [
            Limit::perMinutes(10, 5)->by($commerceSessionKey($request)),
            Limit::perDay(20)->by('ip:'.$request->ip()),
        ]);
        RateLimiter::for('commerce-order-lookup', fn (Request $request) => Limit::perMinute(10)
            ->by('ip:'.$request->ip()));
        RateLimiter::for('commerce-warranty-lookup', fn (Request $request) => Limit::perMinute(10)
            ->by('ip:'.$request->ip()));

        View::composer('app', function ($view): void {
            $view->with('rootSite', app(SiteConfigurationService::class)->get());
        });

        Inertia::share([
            'cart_count' => fn () => app(CartSessionService::class)->count(),
            'flash' => Inertia::always(fn () => array_filter([
                'success' => session('success'),
                'warning' => session('warning'),
                'error' => session('error'),
            ], static fn ($value): bool => $value !== null)),
            'fontSettings' => fn () => app(SiteConfigurationService::class)->get()['fonts'],
            'primaryColor' => fn () => app(SiteConfigurationService::class)->get()['primary_color'],
        ]);
    }
}
