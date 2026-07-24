<?php

namespace App\Jobs;

use App\Models\MediaLibrary;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessMediaUpload implements ShouldQueue
{
    use Queueable;

    public function __construct(public MediaLibrary $mediaLibrary) {}

    public function handle(): void
    {
        // Media paths are immutable. Processing must happen before the row is created.
    }
}
