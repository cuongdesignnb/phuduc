<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\NewsIndexRequest;
use App\Services\Storefront\NewsPageService;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function index(NewsIndexRequest $request, NewsPageService $news)
    {
        return Inertia::render('Guest/News/Index', $news->index($request->filters()));
    }

    public function show(string $slug, NewsPageService $news)
    {
        return Inertia::render('Guest/News/Show', $news->show($slug));
    }
}
