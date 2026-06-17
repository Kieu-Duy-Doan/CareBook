<?php

namespace App\Services;

use App\Models\WorkSchedule;
use App\Models\ScheduleOverride;
use App\Models\Appointment;
use Carbon\Carbon;

class WorkScheduleService
{
    /**
     * Get available time slots for a doctor on a specific date.
     *
     * @param int $doctorId (doctor_profile_id)
     * @param string $date (Y-m-d)
     * @return array
     */
    public function getAvailableSlots($doctorId, $date)
    {
        $carbonDate = Carbon::parse($date);
        
        // Carbon dayOfWeek: 0 = Sunday, 1 = Monday, ... 6 = Saturday
        // In our DB, day_of_week might be 1=Sunday, 2=Monday, 3=Tuesday... Let's map it.
        // Let's assume standard ISO or the DB's WorkSchedule mapping: 1=Sun, 2=Mon...7=Sat
        // Wait, Carbon->dayOfWeekIso: 1=Mon...7=Sun.
        // Let's assume 1=Sunday, 2=Monday, 3=Tuesday... 7=Saturday
        $dayOfWeek = $carbonDate->dayOfWeek + 1;

        // 1. Check for overrides
        $override = ScheduleOverride::where('doctor_profile_id', $doctorId)
            ->whereDate('override_date', $carbonDate->toDateString())
            ->first();

        $scheduleConfig = null;

        if ($override) {
            if ($override->type === 'close') {
                return []; // Doctor is absent, no slots available
            }
            // If it's an 'add' or 'replace' override, we use its time settings
            // Assuming the override has start_time and end_time
            // Note: Our DB might not have slot_duration_minutes in override, we might need to fallback to the regular schedule or use a default (e.g., 30 mins)
            $scheduleConfig = [
                'start_time' => $override->start_time,
                'end_time' => $override->end_time,
                // Fallback to regular schedule's duration or default 30
                'slot_duration_minutes' => 30, 
            ];

            // Try to find regular duration to match
            $regularSchedule = WorkSchedule::where('doctor_profile_id', $doctorId)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->first();
            
            if ($regularSchedule) {
                $scheduleConfig['slot_duration_minutes'] = $regularSchedule->slot_duration_minutes;
            }
        } else {
            // 2. Fetch regular schedule
            $regularSchedule = WorkSchedule::where('doctor_profile_id', $doctorId)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->first();

            if (!$regularSchedule) {
                return []; // No schedule set for this day
            }

            $scheduleConfig = [
                'start_time' => $regularSchedule->start_time,
                'end_time' => $regularSchedule->end_time,
                'slot_duration_minutes' => $regularSchedule->slot_duration_minutes,
            ];
        }

        // 3. Generate slots
        $slots = $this->generateSlots(
            $scheduleConfig['start_time'], 
            $scheduleConfig['end_time'], 
            $scheduleConfig['slot_duration_minutes']
        );

        // 4. Fetch booked appointments for this date
        $bookedAppointments = Appointment::where('doctor_profile_id', $doctorId)
            ->whereDate('appointment_date', $carbonDate->toDateString())
            ->whereNotIn('status', ['cancelled', 'absent'])
            ->pluck('appointment_time')
            ->map(function ($time) {
                // Return only H:i
                return substr($time, 0, 5);
            })
            ->toArray();

        // 5. Filter out booked slots
        $availableSlots = array_filter($slots, function($slot) use ($bookedAppointments, $carbonDate) {
            // Remove slots that are already booked
            if (in_array($slot, $bookedAppointments)) {
                return false;
            }
            
            // If the date is today, remove past time slots
            if ($carbonDate->isToday()) {
                $slotTime = Carbon::createFromFormat('H:i', $slot);
                if ($slotTime->isPast()) {
                    return false;
                }
            }

            return true;
        });

        return array_values($availableSlots);
    }

    /**
     * Helper method to generate time slots given start, end, and duration.
     *
     * @param string $startTime (H:i:s or H:i)
     * @param string $endTime (H:i:s or H:i)
     * @param int $durationInMinutes
     * @return array
     */
    private function generateSlots($startTime, $endTime, $durationInMinutes)
    {
        $slots = [];
        
        $current = Carbon::createFromFormat('H:i', substr($startTime, 0, 5));
        $end = Carbon::createFromFormat('H:i', substr($endTime, 0, 5));

        while ($current->copy()->addMinutes($durationInMinutes)->lte($end)) {
            $slots[] = $current->format('H:i');
            $current->addMinutes($durationInMinutes);
        }

        return $slots;
    }
}
