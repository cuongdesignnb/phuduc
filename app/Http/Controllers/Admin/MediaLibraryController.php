<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MediaLibrary;
use App\Jobs\ProcessMediaUpload;
use Illuminate\Support\Facades\Storage;

class MediaLibraryController extends Controller
{
    public function index(Request $request)
    {
        $medias = MediaLibrary::latest()->paginate(20);
        return response()->json($medias);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'alt_text' => 'nullable|string'
        ]);

        $file = $request->file('file');
        $path = $file->store('media', 'public');

        $media = MediaLibrary::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt_text' => $request->alt_text ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
        ]);

        if (strpos($media->mime_type, 'image/') === 0 && $media->mime_type !== 'image/webp') {
            ProcessMediaUpload::dispatch($media);
        }

        return response()->json(['message' => 'Upload successful', 'data' => $media], 201);
    }

    public function destroy($id)
    {
        $media = MediaLibrary::findOrFail($id);
        Storage::disk('public')->delete($media->file_path);
        $media->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
