<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'session_id',
        'role',
        'content',
        'intent_detected',
        'is_flagged',
        'metadata',
        'created_at',
    ];
    protected function casts(): array
    {
        return [
            'is_flagged' => 'boolean',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }
    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }
}
