<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SeoService;
use App\Services\Storefront\StorefrontPageService;
use Inertia\Inertia;

class PageController extends Controller
{
    public function home(StorefrontPageService $storefront)
    {
        return Inertia::render('Guest/Home', $storefront->home());
    }

    public function about()
    {
        $settings = Setting::where('key', 'like', 'about.%')
            ->orWhere('key', 'like', 'site.%')
            ->pluck('value', 'key');

        return Inertia::render('Guest/About', [
            'settings' => $settings,
            'seo' => SeoService::meta([
                'title' => 'Giới thiệu',
                'description' => Setting::get('about.description', 'Giới thiệu về công ty'),
            ]),
            'jsonLd' => [
                SeoService::organizationJsonLd(),
                SeoService::breadcrumbJsonLd([
                    ['name' => 'Trang chủ', 'url' => url('/')],
                    ['name' => 'Giới thiệu'],
                ]),
            ],
        ]);
    }
}
