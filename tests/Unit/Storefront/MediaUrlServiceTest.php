<?php

namespace Tests\Unit\Storefront;

use App\Services\Storefront\MediaUrlService;
use Tests\TestCase;

class MediaUrlServiceTest extends TestCase
{
    public function test_media_paths_are_resolved_once_and_absolute_urls_are_preserved(): void
    {
        $service = app(MediaUrlService::class);

        $this->assertNull($service->resolve(null));
        $this->assertSame('https://cdn.example.com/logo.png', $service->resolve('https://cdn.example.com/logo.png'));
        $this->assertSame(url('/storage/media/logo.png'), $service->resolve('media/logo.png'));
        $this->assertSame(url('/storage/media/logo.png'), $service->resolve('/storage/media/logo.png'));
    }
}
