<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveHomeContentRequest;
use App\Models\HomeSection;
use App\Services\Admin\Content\AdminHomeContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class HomeContentController extends Controller
{
    public function index(AdminHomeContentService $home): Response { return Inertia::render('Admin/HomeContent/Index', $home->page(request()->user())); }
    public function products(AdminHomeContentService $home): JsonResponse { return response()->json(['data' => $home->productLookup(['search' => trim((string) request('search', ''))])]); }
    public function posts(AdminHomeContentService $home): JsonResponse { return response()->json(['data' => $home->postLookup(['search' => trim((string) request('search', ''))])]); }
    public function save(SaveHomeContentRequest $request, AdminHomeContentService $home): RedirectResponse { $home->save($request->validated()); return back()->with('success', 'Homepage content saved.'); }

    /** @deprecated Compatibility shim for the pre-service test helper. */
    public function syncItems(HomeSection $section, array $items, array $allowedFields): void
    {
        app(AdminHomeContentService::class)->syncItemsForCompatibility($section, $items, $allowedFields);
    }
}
