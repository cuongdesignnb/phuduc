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
    public function index(AdminHomeContentService $home): Response
    {
        return Inertia::render('Admin/HomeContent/Index', $home->page(request()->user()));
    }

    public function products(AdminHomeContentService $home): JsonResponse
    {
        return response()->json($this->lookupResponse($home->productLookup($this->lookupFilters())));
    }

    public function posts(AdminHomeContentService $home): JsonResponse
    {
        return response()->json($this->lookupResponse($home->postLookup($this->lookupFilters())));
    }

    public function save(SaveHomeContentRequest $request, AdminHomeContentService $home): RedirectResponse
    {
        $version = $home->save($request->validated());

        return back()->with('success', 'Homepage content saved.')->with('admin_version', $version);
    }

    /** @deprecated Compatibility shim for the pre-service test helper. */
    public function syncItems(HomeSection $section, array $items, array $allowedFields): void
    {
        app(AdminHomeContentService::class)->syncItemsForCompatibility($section, $items, $allowedFields);
    }

    private function lookupFilters(): array
    {
        $ids = request('ids', []);
        if (! is_array($ids)) {
            $ids = $ids === '' ? [] : explode(',', (string) $ids);
        }

        return ['search' => trim((string) request('search', '')), 'ids' => array_values(array_filter(array_map('intval', $ids))), 'limit' => min(20, max(1, (int) request('limit', 20)))];
    }

    private function lookupResponse(array $items): array
    {
        return ['items' => $items, 'data' => $items, 'filters' => $this->lookupFilters()];
    }
}
