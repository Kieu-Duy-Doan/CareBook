<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorLevelFee extends Model
{
    protected $fillable = [
        'level',
        'base_price',
        'specific_price',
    ];
}
