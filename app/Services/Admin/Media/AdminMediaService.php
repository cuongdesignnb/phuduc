<?php

namespace App\Services\Admin\Media;

use App\Models\MediaLibrary;
use App\Models\MediaFolder;
use App\Models\User;
use App\Services\Admin\AdminPageService;
use App\Services\Admin\AdminPresentationService;
use App\Support\Media\ImageMimeTypes;
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
                ? $query->whereIn('mime_type', ImageMimeTypes::ALLOWLIST)
                : $query->whereNotIn('mime_type', ImageMimeTypes::ALLOWLIST))
            ->when($filters['folder_id'] ?? null, fn ($query, $folderId) => $query->where('folder_id', $folderId))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $referenceMap = $this->references->forPaths($paginator->getCollection()->pluck('file_path')->all());

        return $this->pages->envelope($user, 'admin_media_index', 'Thư viện Media', [
            ['label' => 'Thư viện Media', 'url' => route('admin.media.index')],
        ], [
            'items' => $paginator->getCollection()->map(fn (MediaLibrary $media) => $this->presentation->item($media, $referenceMap[$this->references->normalize($media->file_path)] ?? []))->values()->all(),
            'pagination' => $this->adminPresentation->pagination($paginator),
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'media_type' => (string) ($filters['media_type'] ?? ''),
                'folder_id' => ($filters['folder_id'] ?? null) ? (int) $filters['folder_id'] : null,
            ],
            'folders' => $this->folderTree(),
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
        }))->when($filters['media_type'] ?? null, fn ($query, $type) => $type === 'image'
            ? $query->whereIn('mime_type', ImageMimeTypes::ALLOWLIST)
            : $query->whereNotIn('mime_type', ImageMimeTypes::ALLOWLIST))
            ->when($filters['folder_id'] ?? null, fn ($query, $folderId) => $query->where('folder_id', $folderId));
        $paginator = $query->latest()->paginate($limit, ['*'], 'page', (int) ($filters['page'] ?? 1));
        $selected = $ids === [] ? collect() : MediaLibrary::query()->whereIn('id', $ids)->when(($filters['media_type'] ?? null) === 'image', fn ($query) => $query->whereIn('mime_type', ImageMimeTypes::ALLOWLIST))->when(($filters['media_type'] ?? null) === 'file', fn ($query) => $query->whereNotIn('mime_type', ImageMimeTypes::ALLOWLIST))->get();
        $items = $selected->concat($paginator->getCollection())->unique('id')->map(fn (MediaLibrary $media) => $this->presentation->pickerItem($media))->values()->all();

        return [
            'items' => $items,
            'pagination' => $this->adminPresentation->pagination($paginator),
            'filters' => ['search' => (string) ($filters['search'] ?? ''), 'media_type' => (string) ($filters['media_type'] ?? ''), 'folder_id' => ($filters['folder_id'] ?? null) ? (int) $filters['folder_id'] : null, 'ids' => $ids, 'limit' => $limit],
            'folders' => $this->folderTree(),
            'data' => $items,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function store(array $files, ?string $altText = null, ?int $folderId = null): array
    {
        $created = [];
        $storedPaths = [];

        if ($folderId !== null) {
            MediaFolder::query()->findOrFail($folderId);
        }

        try {
            DB::transaction(function () use ($files, $altText, $folderId, &$created, &$storedPaths): void {
                foreach ($files as $file) {
                    if (! $file instanceof UploadedFile) {
                        continue;
                    }
                    $stored = $this->storage->store($file, 'media');
                    $storedPaths[] = $stored['path'];
                    $media = MediaLibrary::create([
                        'folder_id' => $folderId,
                        'file_name' => $stored['file_name'],
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

    /** @return array<string, mixed> */
    public function createFolder(array $data): array
    {
        $parentId = $data['parent_id'] ?? null;
        $this->assertFolderNameAvailable((string) $data['name'], $parentId);
        $folder = MediaFolder::create(['parent_id' => $parentId, 'name' => $data['name']]);

        return $this->folderItem($folder->loadCount('media'));
    }

    /** @return array<string, mixed> */
    public function renameFolder(MediaFolder $folder, string $name): array
    {
        $this->assertFolderNameAvailable($name, $folder->parent_id, $folder->id);
        $folder->update(['name' => $name]);

        return $this->folderItem($folder->refresh()->loadCount('media'));
    }

    public function move(array $mediaIds, ?int $folderId): void
    {
        if ($folderId !== null) {
            MediaFolder::query()->findOrFail($folderId);
        }

        MediaLibrary::query()->whereIn('id', array_map('intval', $mediaIds))->update(['folder_id' => $folderId]);
    }

    public function destroyFolder(MediaFolder $folder): void
    {
        if ($folder->media()->exists() || $folder->children()->exists()) {
            throw ValidationException::withMessages(['folder' => 'Thư mục phải trống trước khi xóa.']);
        }

        $folder->delete();
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

    /** @return list<array<string, mixed>> */
    private function folderTree(): array
    {
        $folders = MediaFolder::query()
            ->withCount('media')
            ->orderBy('name')
            ->orderBy('id')
            ->get();
        $byParent = $folders->groupBy(fn (MediaFolder $folder) => (int) ($folder->parent_id ?? 0));
        $flatten = function (?int $parentId, int $depth) use (&$flatten, $byParent): array {
            return collect($byParent->get((int) ($parentId ?? 0), []))
                ->flatMap(fn (MediaFolder $folder) => [
                    $this->folderItem($folder, $depth),
                    ...$flatten((int) $folder->id, $depth + 1),
                ])
                ->values()
                ->all();
        };

        return $flatten(null, 0);
    }

    /** @return array<string, mixed> */
    private function folderItem(MediaFolder $folder, int $depth = 0): array
    {
        return [
            'id' => (int) $folder->id,
            'parent_id' => $folder->parent_id ? (int) $folder->parent_id : null,
            'name' => $folder->name,
            'depth' => $depth,
            'media_count' => (int) ($folder->media_count ?? 0),
        ];
    }

    private function assertFolderNameAvailable(string $name, mixed $parentId, ?int $ignoreId = null): void
    {
        $exists = MediaFolder::query()
            ->where('name', $name)
            ->when($parentId === null, fn ($query) => $query->whereNull('parent_id'), fn ($query) => $query->where('parent_id', $parentId))
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
        if ($exists) {
            throw ValidationException::withMessages(['name' => 'Tên thư mục đã tồn tại trong vị trí này.']);
        }
    }
}
