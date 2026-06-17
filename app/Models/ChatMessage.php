<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'sender',
        'message',
        'intent_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }

    public function intent()
    {
        return $this->belongsTo(ChatbotIntent::class, 'intent_id');
    }
}
