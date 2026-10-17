<?php

namespace App\Http\Middleware;

use App\Services\Admin\AdminNavigationService;
use App\Services\Admin\AdminPermissionService;
use App\Services\Storefront\NavigationService;
use App\Services\Storefront\SiteConfigurationService;
use App\Services\Storefront\StorefrontSeoService;
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
            'seo' => fn () => app(StorefrontSeoService::class)->meta(),
            'admin' => function (Request $request): array {
                $user = $request->user();

                return [
                    'navigation' => app(AdminNavigationService::class)->for($user),
                    'permissions' => app(AdminPermissionService::class)->for($user),
                ];
            },
        ];
    }
}
