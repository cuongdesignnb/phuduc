<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    protected $table = 'post_media';

    protected $fillable = ['post_id', 'media_id', 'sort_order'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function media()
    {
        return $this->belongsTo(MediaLibrary::class);
    }
}
