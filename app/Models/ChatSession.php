<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'session_token',
        'ended_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'ended_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'session_id');
    }
}
