<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaIndexRequest;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Http\Requests\Admin\UpdateMediaRequest;
use App\Models\MediaLibrary;
use App\Services\Admin\Media\AdminMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MediaLibraryController extends Controller
{
    public function index(MediaIndexRequest $request, AdminMediaService $media): Response
    {
        return Inertia::render('Admin/Media/Index', $media->page($request->user(), $request->validated()));
    }

    public function data(MediaIndexRequest $request, AdminMediaService $media): JsonResponse
    {
        return response()->json(['data' => $media->picker($request->validated())]);
    }

    public function store(StoreMediaRequest $request, AdminMediaService $media): RedirectResponse
    {
        $media->store($request->file('files', []), $request->validated('alt_text'));

        return back()->with('success', 'Tệp đã được tải lên.');
    }

    public function update(UpdateMediaRequest $request, MediaLibrary $media, AdminMediaService $service): RedirectResponse
    {
        $service->update($media, (string) $request->validated('alt_text', ''));

        return back()->with('success', 'Thông tin Media đã được cập nhật.');
    }

    public function destroy(MediaLibrary $media, AdminMediaService $service): RedirectResponse
    {
        $service->destroy($media);

        return redirect()->route('admin.media.index')->with('success', 'Media đã được xóa.');
    }
}
