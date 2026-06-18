<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialty extends Model {
   protected $fillable = [
        'name',
        'description',
        'image_url',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'specialty_rooms')->withPivot('is_primary');
    }

    public function doctors()
    {
        return $this->belongsToMany(DoctorProfile::class, 'doctor_specialties')->withPivot('is_primary');
    }
}
?>