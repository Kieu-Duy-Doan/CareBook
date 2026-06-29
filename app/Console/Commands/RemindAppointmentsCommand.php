<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Jobs\ProcessAppointmentNotificationJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class RemindAppointmentsCommand extends Command
{
    protected $signature = 'appointments:remind';
    protected $description = 'Gửi nhắc nhở lịch khám trước 2 tiếng và 30 phút';

    public function handle()
    {
        Log::info('Start RemindAppointmentsCommand');
        $now = now();
        $count2h = 0;
        $count30m = 0;

        $appointments = Appointment::whereIn('status', ['pending', 'checked_in'])
            ->whereDate('appointment_date', $now->toDateString())
            ->where(function($q) {
                $q->where('reminded_2h', false)
                  ->orWhere('reminded_30m', false);
            })
            ->get();

        foreach ($appointments as $appointment) {
            $appointmentDateTime = Carbon::parse($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time);
            $minutesToAppointment = $now->diffInMinutes($appointmentDateTime, false);

            if (!$appointment->reminded_2h && $minutesToAppointment > 0 && $minutesToAppointment <= 125) {
                ProcessAppointmentNotificationJob::dispatch($appointment, 'reminder_2h');
                $appointment->reminded_2h = true;
                $appointment->save();
                $count2h++;
            }
            
            if (!$appointment->reminded_30m && $minutesToAppointment > 0 && $minutesToAppointment <= 35) {
                ProcessAppointmentNotificationJob::dispatch($appointment, 'reminder_30m');
                $appointment->reminded_30m = true;
                $appointment->save();
                $count30m++;
            }
        }

        $this->info("Đã gửi nhắc nhở: {$count2h} (2h) và {$count30m} (30m).");
        Log::info("End RemindAppointmentsCommand: {$count2h} (2h), {$count30m} (30m)");
    }
}
