<?php

namespace Tests\Feature\Admin;

use App\Models\MediaLibrary;
use App\Services\Admin\Media\AdminMediaService;
use App\Services\Admin\Media\MediaAssetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MediaExactMimeAllowlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_exact_four_image_mimes_are_accepted(): void
    {
        foreach (['image/jpeg', 'image/png', 'image/webp', 'image/gif'] as $mime) {
            $media = MediaLibrary::create(['file_name' => 'image', 'file_path' => "media/{$mime}", 'mime_type' => $mime, 'size' => 1]);
            $this->assertSame($media->id, app(MediaAssetService::class)->requireImage($media->id)->id);
        }
        foreach (['image/svg+xml', 'application/pdf', 'video/mp4'] as $mime) {
            $media = MediaLibrary::create(['file_name' => 'unsafe', 'file_path' => "media/{$mime}", 'mime_type' => $mime, 'size' => 1]);
            try {
                app(MediaAssetService::class)->requireImage($media->id);
                $this->fail("Unsafe MIME accepted: {$mime}");
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_image_picker_does_not_hydrate_an_svg_as_an_image(): void
    {
        $svg = MediaLibrary::create(['file_name' => 'icon.svg', 'file_path' => 'media/icon.svg', 'mime_type' => 'image/svg+xml', 'size' => 1]);
        $data = app(AdminMediaService::class)->picker(['media_type' => 'image', 'ids' => [$svg->id]]);

        $this->assertSame([], $data['items']);
    }
}
