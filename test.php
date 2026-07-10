<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$doctorUser = App\Models\User::where('role', 'doctor')->first();
if ($doctorUser) {
    echo "Doctor User ID: " . $doctorUser->id . "\n";
    $profile = $doctorUser->doctorProfile;
    if ($profile) {
        echo "Doctor Profile ID: " . $profile->id . "\n";
    } else {
        echo "No doctor profile for user!\n";
    }
}
$appointments = App\Models\Appointment::limit(5)->get();
foreach ($appointments as $a) {
    echo "Appointment " . $a->id . " has doctor_profile_id: " . $a->doctor_profile_id . "\n";
}
