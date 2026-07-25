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
        $path = MediaLibrary::firstOrFail()->file_path;
        $this->assertStringStartsWith('media/', $path);
        Storage::disk('public')->assertExists($path);
    }
}
