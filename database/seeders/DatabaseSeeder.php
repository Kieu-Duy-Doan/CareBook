<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SpecialtySeeder::class,
            RoomSeeder::class,
            SpecialtyRoomSeeder::class,
            DoctorSpecialtySeeder::class,
            WorkScheduleSeeder::class,
            DoctorLevelFeeSeeder::class,
            AppointmentSeeder::class,
            PaymentDemoSeeder::class,
            ChatbotSeeder::class,
            FaqSeeder::class,
            PostSeeder::class,
        ]);
    }
}
