<?php

$file = __DIR__ . '/database/seeders/UserSeeder.php';
$content = file_get_contents($file);

// Replace PatientProfile::create([
// with PatientProfile::create(['medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],

$newContent = preg_replace(
    "/PatientProfile::create\(\[/",
    "PatientProfile::create([\n            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],",
    $content
);

file_put_contents($file, $newContent);
echo "Updated UserSeeder.php successfully!";
