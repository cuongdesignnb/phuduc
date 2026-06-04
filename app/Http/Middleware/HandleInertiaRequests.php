<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use App\Models\Setting;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'siteSettings' => fn() => Setting::whereIn('key', [
                'site.name', 'site.logo', 'site.phone', 'site.email', 'site.address',
            ])->pluck('value', 'key'),
            'menus' => fn() => Menu::with(['items.allChildren' => fn($q) => $q->orderBy('sort_order')])
                ->get()
                ->keyBy('location'),
            'seo' => fn() => SeoService::meta(),
        ];
    }
}
