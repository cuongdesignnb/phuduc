<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaIndexRequest;
use App\Http\Requests\Admin\MoveMediaRequest;
use App\Http\Requests\Admin\StoreMediaFolderRequest;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Http\Requests\Admin\UpdateMediaFolderRequest;
use App\Http\Requests\Admin\UpdateMediaRequest;
use App\Models\MediaFolder;
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
        return response()->json($media->picker($request->validated()));
    }

    public function store(StoreMediaRequest $request, AdminMediaService $media): JsonResponse|RedirectResponse
    {
        $created = $media->store($request->file('files', []), $request->validated('alt_text'), $request->validated('folder_id'));

        if ($request->expectsJson()) {
            return response()->json(['items' => $created, 'data' => $created]);
        }

        return back()->with('success', 'Tệp đã được tải lên.');
    }

    public function storeFolder(StoreMediaFolderRequest $request, AdminMediaService $media): JsonResponse
    {
        return response()->json(['folder' => $media->createFolder($request->validated())]);
    }

    public function updateFolder(UpdateMediaFolderRequest $request, MediaFolder $folder, AdminMediaService $media): JsonResponse
    {
        return response()->json(['folder' => $media->renameFolder($folder, (string) $request->validated('name'))]);
    }

    public function destroyFolder(MediaFolder $folder, AdminMediaService $media): JsonResponse
    {
        $media->destroyFolder($folder);

        return response()->json(['deleted' => true]);
    }

    public function move(MoveMediaRequest $request, AdminMediaService $media): JsonResponse
    {
        $media->move($request->validated('media_ids'), $request->validated('folder_id'));

        return response()->json(['moved' => true]);
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
