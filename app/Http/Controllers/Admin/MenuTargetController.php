<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuTargetIndexRequest;
use App\Services\Admin\Content\AdminMenuTargetService;
use Illuminate\Http\JsonResponse;

class MenuTargetController extends Controller
{
    public function products(MenuTargetIndexRequest $request, AdminMenuTargetService $targets): JsonResponse
    {
        return response()->json(['items' => $targets->products($request->validated()), 'filters' => $request->validated()]);
    }

    public function posts(MenuTargetIndexRequest $request, AdminMenuTargetService $targets): JsonResponse
    {
        return response()->json(['items' => $targets->posts($request->validated()), 'filters' => $request->validated()]);
    }

    public function categories(MenuTargetIndexRequest $request, AdminMenuTargetService $targets): JsonResponse
    {
        return response()->json(['items' => $targets->categories($request->validated()), 'filters' => $request->validated()]);
    }
}
