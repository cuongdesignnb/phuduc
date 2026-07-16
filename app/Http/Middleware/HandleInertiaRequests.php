<?php

namespace App\Http\Middleware;

use App\Services\Storefront\NavigationService;
use App\Services\Storefront\SiteConfigurationService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'site' => fn () => app(SiteConfigurationService::class)->get(),
            'navigation' => fn () => app(NavigationService::class)->get(),
            'seo' => function () {
                $site = app(SiteConfigurationService::class)->get();

                return [
                    'title' => $site['name'],
                    'description' => $site['description'],
                    'ogImage' => $site['og_image_url'],
                    'ogType' => 'website',
                    'canonical' => url()->current(),
                    'robots' => 'index, follow',
                ];
            },
        ];
    }
}
