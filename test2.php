<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$doctorProfile = App\Models\DoctorProfile::find(1);
$query = App\Models\Appointment::with(['patientProfile', 'specialty', 'room', 'bookedByUser'])
            ->where('doctor_profile_id', $doctorProfile->id)
            ->latest('appointment_date')
            ->latest('appointment_time');
            
echo $query->toSql() . "\n";
echo print_r($query->getBindings(), true) . "\n";
