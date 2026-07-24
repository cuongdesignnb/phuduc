<?php

namespace App\Services\Admin\Media;

use App\Models\MediaLibrary;
use App\Models\User;
use App\Services\Admin\AdminPageService;
use App\Services\Admin\AdminPresentationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminMediaService
{
    public function __construct(
        private readonly AdminMediaPresentationService $presentation,
        private readonly MediaReferenceService $references,
        private readonly AdminPageService $pages,
        private readonly AdminPresentationService $adminPresentation,
        private readonly AdminImageStorageService $storage,
    ) {}

    /** @return array<string, mixed> */
    public function page(User $user, array $filters): array
    {
        $paginator = MediaLibrary::query()
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search): void {
                $query->where('file_name', 'like', '%'.addcslashes($search, '%_\\').'%')
                    ->orWhere('alt_text', 'like', '%'.addcslashes($search, '%_\\').'%');
            }))
            ->when($filters['media_type'] ?? null, fn ($query, $type) => $type === 'image'
                ? $query->where('mime_type', 'like', 'image/%')
                : $query->where('mime_type', 'not like', 'image/%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $referenceMap = $this->references->forPaths($paginator->getCollection()->pluck('file_path')->all());

        return $this->pages->envelope($user, 'admin_media_index', 'Media Library', [
            ['label' => 'Media', 'url' => route('admin.media.index')],
        ], [
            'items' => $paginator->getCollection()->map(fn (MediaLibrary $media) => $this->presentation->item($media, $referenceMap[$this->references->normalize($media->file_path)] ?? []))->values()->all(),
            'pagination' => $this->adminPresentation->pagination($paginator),
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'media_type' => (string) ($filters['media_type'] ?? ''),
            ],
            'upload' => ['max_files' => 20, 'max_file_size' => 10 * 1024 * 1024, 'max_request_size' => 50 * 1024 * 1024],
        ]);
    }

    /** @return array<string, mixed> */
    public function picker(array $filters): array
    {
        // Picker cap: limit(20).
        $limit = min(20, max(1, (int) ($filters['limit'] ?? 20)));
        $ids = collect($filters['ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
        $query = MediaLibrary::query()->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search): void {
            $value = '%'.addcslashes($search, '%_\\').'%';
            $query->where('file_name', 'like', $value)->orWhere('alt_text', 'like', $value);
        }));
        $paginator = $query->latest()->paginate($limit, ['*'], 'page', (int) ($filters['page'] ?? 1));
        $selected = $ids === [] ? collect() : MediaLibrary::query()->whereIn('id', $ids)->get();
        $items = $selected->concat($paginator->getCollection())->unique('id')->map(fn (MediaLibrary $media) => $this->presentation->pickerItem($media))->values()->all();

        return [
            'items' => $items,
            'pagination' => $this->adminPresentation->pagination($paginator),
            'filters' => ['search' => (string) ($filters['search'] ?? ''), 'ids' => $ids, 'limit' => $limit],
            'data' => $items,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function store(array $files, ?string $altText = null): array
    {
        $created = [];
        $storedPaths = [];

        try {
            DB::transaction(function () use ($files, $altText, &$created, &$storedPaths): void {
                foreach ($files as $file) {
                    if (! $file instanceof UploadedFile) {
                        continue;
                    }
                    $stored = $this->storage->store($file, 'media');
                    $storedPaths[] = $stored['path'];
                    $media = MediaLibrary::create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $stored['path'],
                        'mime_type' => $stored['mime_type'],
                        'size' => $stored['size'],
                        'alt_text' => $altText ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    ]);
                    $created[] = $this->presentation->item($media, []);
                }
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $exception;
        }

        return $created;
    }

    public function update(MediaLibrary $media, string $altText): MediaLibrary
    {
        $media->update(['alt_text' => $altText]);

        return $media->refresh();
    }

    public function destroy(MediaLibrary $media): void
    {
        $references = $this->references->references($media);
        if ($references !== []) {
            throw ValidationException::withMessages(['media' => 'File đang được sử dụng và chưa thể xóa.', 'reference_types' => $references]);
        }

        $path = $media->file_path;
        DB::transaction(function () use ($media): void {
            $media->delete();
        });
        DB::afterCommit(fn () => Storage::disk('public')->delete($path));
    }
}
