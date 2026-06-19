<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotIntent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'intent_name',
        'description',
        'example_phrases',
        'action',
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
    public function responses()
    {
        return $this->hasMany(ChatbotResponse::class, 'intent_id');
    }
}
