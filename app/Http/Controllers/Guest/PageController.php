<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Services\Storefront\AboutPageService;
use App\Services\Storefront\StorefrontPageService;
use Inertia\Inertia;

class PageController extends Controller
{
    public function home(StorefrontPageService $storefront)
    {
        return Inertia::render('Guest/Home', $storefront->home());
    }

    public function about(AboutPageService $about)
    {
        return Inertia::render('Guest/About', $about->page());
    }
}
