<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'order_code',
        'amount',
        'payment_method',
        'status',
        'transaction_id',
        'collected_by',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
