<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'question',
        'answer',
        'specialty_id',
        'keywords',
        'view_count',
        'is_active',
    ];
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }
}
