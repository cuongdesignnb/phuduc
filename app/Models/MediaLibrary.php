<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaLibrary extends Model
{
    protected $fillable = [
        'file_name', 'file_path', 'mime_type', 'size', 'alt_text',
    ];
}
