<?php

namespace Tests\Feature\Admin;

use App\Models\MediaFolder;
use App\Models\MediaLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaFolderContractTest extends TestCase
{
    use Pr3bTestHelpers, RefreshDatabase;

    public function test_media_can_be_uploaded_picked_and_moved_between_virtual_folders(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $folderResponse = $this->actingAs($admin)->postJson(route('admin.media.folders.store'), ['name' => 'Sản phẩm']);
        $folderResponse->assertOk()->assertJsonPath('folder.name', 'Sản phẩm');
        $folder = MediaFolder::query()->firstOrFail();

        $upload = $this->actingAs($admin)
            ->withHeader('Accept', 'application/json')
            ->post(route('admin.media.store'), [
                'folder_id' => $folder->id,
                'files' => [UploadedFile::fake()->image('product-card.jpg')],
            ]);

        $upload->assertOk()->assertJsonPath('items.0.folder_id', $folder->id);
        $media = MediaLibrary::query()->firstOrFail();

        $this->actingAs($admin)
            ->getJson(route('admin.media.data', ['folder_id' => $folder->id, 'media_type' => 'image']))
            ->assertOk()
            ->assertJsonPath('items.0.id', $media->id)
            ->assertJsonPath('filters.folder_id', $folder->id)
            ->assertJsonStructure(['folders', 'pagination']);

        $this->actingAs($admin)
            ->postJson(route('admin.media.move'), ['media_ids' => [$media->id], 'folder_id' => null])
            ->assertOk()
            ->assertJsonPath('moved', true);

        $this->assertNull($media->refresh()->folder_id);
    }
}
