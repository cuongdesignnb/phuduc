<?php

namespace Tests\Feature\Admin;

use App\Services\Admin\Media\AdminMediaService;
use App\Models\MediaLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_uses_uuid_path_and_creates_media_row(): void
    {
        Storage::fake('public');
        $result = app(AdminMediaService::class)->store([UploadedFile::fake()->image('logo.png')]);
        $this->assertCount(1, $result);
        $media = MediaLibrary::firstOrFail();
        $path = $media->file_path;
        $this->assertStringStartsWith('media/', $path);
        $this->assertStringEndsWith('.webp', $path);
        $this->assertSame('logo.webp', $media->file_name);
        $this->assertSame('image/webp', $media->mime_type);
        Storage::disk('public')->assertExists($path);
        $this->assertCount(1, Storage::disk('public')->allFiles('media'));
    }

    public function test_jpeg_upload_is_converted_without_retaining_the_original_extension(): void
    {
        Storage::fake('public');

        app(AdminMediaService::class)->store([UploadedFile::fake()->image('hero.jpg')]);

        $media = MediaLibrary::firstOrFail();
        $this->assertSame('hero.webp', $media->file_name);
        $this->assertSame('image/webp', $media->mime_type);
        $this->assertStringEndsWith('.webp', $media->file_path);
        $this->assertStringNotContainsString('.jpg', $media->file_path);
    }
}
