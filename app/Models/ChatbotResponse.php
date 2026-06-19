<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotResponse extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'intent_id',
        'content',
        'priority',
        'use_count',
        'is_active',
        'created_at',
    ];
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
    public function intent()
    {
        return $this->belongsTo(ChatbotIntent::class, 'intent_id');
    }
}
