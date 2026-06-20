<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'thumbnail_url',
        'specialty_id',
        'post_type',
        'view_count',
        'author_id',
        'is_published',
        'published_at',
    ];

protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}