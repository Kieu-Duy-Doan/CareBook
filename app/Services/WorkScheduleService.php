<?php

namespace App\Services;

use App\Models\WorkSchedule;
use App\Models\ScheduleOverride;
use App\Models\Appointment;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;
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

    public function createSchedule(array $data, $userId)
    {
        $data['is_active'] = isset($data['is_active']) ? (bool)$data['is_active'] : false;
        
        $schedule = WorkSchedule::create($data);

        SystemLog::create([
            'user_id' => $userId,
            'action' => 'WORK_SCHEDULE_CREATED',
            'module' => 'work_schedule',
            'ref_type' => 'work_schedule',
            'ref_id' => $schedule->id,
            'description' => 'Thêm ca trực cho bác sĩ ID ' . $schedule->doctor_profile_id,
            'ip_address' => request()->ip()
        ]);

        return $schedule;
    }

    public function updateSchedule(WorkSchedule $schedule, array $data, $userId)
    {
        $data['is_active'] = isset($data['is_active']) ? (bool)$data['is_active'] : false;
        
        $schedule->update($data);

        SystemLog::create([
            'user_id' => $userId,
            'action' => 'WORK_SCHEDULE_UPDATED',
            'module' => 'work_schedule',
            'ref_type' => 'work_schedule',
            'ref_id' => $schedule->id,
            'description' => 'Cập nhật ca trực cho bác sĩ ID ' . $schedule->doctor_profile_id,
            'ip_address' => request()->ip()
        ]);

        return $schedule;
    }

    public function createOverride(array $data, $userId)
    {
        $data['created_by'] = $userId;
        $override = ScheduleOverride::create($data);

        SystemLog::create([
            'user_id' => $userId,
            'action' => 'SCHEDULE_OVERRIDE_CREATED',
            'module' => 'schedule_override',
            'ref_type' => 'schedule_override',
            'ref_id' => $override->id,
            'description' => 'Thêm ngoại lệ lịch ' . $override->type,
            'ip_address' => request()->ip()
        ]);

        if ($override->type === 'close') {
            $appointmentsToCancel = Appointment::where('doctor_profile_id', $override->doctor_profile_id)
                ->whereDate('appointment_date', $override->override_date)
                ->where('appointment_time', '>=', $override->start_time)
                ->where('appointment_time', '<=', $override->end_time)
                ->whereNotIn('status', ['cancelled', 'completed', 'absent'])
                ->get();

            foreach ($appointmentsToCancel as $appointment) {
                $oldStatus = $appointment->status;
                $appointment->status = 'cancelled';
                $appointment->reason = 'Lịch hẹn bị huỷ tự động do bác sĩ có lịch đột xuất không thể khám.';
                $appointment->save();

                \App\Models\AppointmentLog::create([
                    'appointment_id' => $appointment->id,
                    'changed_by' => $userId,
                    'old_status' => $oldStatus,
                    'new_status' => 'cancelled',
                    'action' => \App\Models\AppointmentLog::ACTION_SYSTEM_UPDATE,
                    'reason' => 'Hủy tự động do bác sĩ thêm ngoại lệ nghỉ/đóng ca.',
                ]);

                \App\Jobs\ProcessAppointmentNotificationJob::dispatch(
                    $appointment,
                    'admin_cancel',
                    'Hệ thống'
                );
            }
        }

        return $override;
    }

    public function transferSchedules(array $data, $userId)
    {
        $fromDoctorId = $data['from_doctor_id'];
        $toDoctorId = $data['to_doctor_id'];
        $transferType = $data['transfer_type'];

        DB::beginTransaction();

        try {
            if ($transferType === 'all') {
                $schedulesA = WorkSchedule::where('doctor_profile_id', $fromDoctorId)->get();

                foreach ($schedulesA as $scheduleA) {
                    $existsTime = WorkSchedule::where('doctor_profile_id', $toDoctorId)
                        ->where('day_of_week', $scheduleA->day_of_week)
                        ->where('is_active', true)
                        ->where(function ($query) use ($scheduleA) {
                            $query->where('start_time', '<', $scheduleA->end_time)
                                ->where('end_time', '>', $scheduleA->start_time);
                        })
                        ->exists();

                    if ($existsTime) {
                        DB::rollBack();
                        throw new \Exception('Bác sĩ đích bị trùng lịch làm việc vào ' . $scheduleA->day_name . ' (' . substr($scheduleA->start_time, 0, 5) . ' - ' . substr($scheduleA->end_time, 0, 5) . '). Không thể chuyển.');
                    }

                    $scheduleA->doctor_profile_id = $toDoctorId;
                    $scheduleA->save();
                }

                $appointmentsToUpdate = \App\Models\Appointment::where('doctor_profile_id', $fromDoctorId)
                    ->where('appointment_date', '>=', now()->toDateString())
                    ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                    ->get();

                foreach ($appointmentsToUpdate as $appointment) {
                    $appointment->doctor_profile_id = $toDoctorId;
                    $appointment->save();
                }

                $overridesToUpdate = ScheduleOverride::where('doctor_profile_id', $fromDoctorId)
                    ->where('override_date', '>=', now()->toDateString())
                    ->get();

                foreach ($overridesToUpdate as $override) {
                    $override->doctor_profile_id = $toDoctorId;
                    $override->save();
                }

                $logDesc = "Chuyển toàn bộ ca khám và lịch hẹn từ BS $fromDoctorId sang BS $toDoctorId";
            } else {
                $startDate = $data['start_date'];
                $endDate = $data['end_date'];

                $appointmentsToUpdate = \App\Models\Appointment::where('doctor_profile_id', $fromDoctorId)
                    ->whereBetween('appointment_date', [$startDate, $endDate])
                    ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                    ->get();

                foreach ($appointmentsToUpdate as $appointment) {
                    $appointment->doctor_profile_id = $toDoctorId;
                    $appointment->save();
                }

                $overridesToUpdate = ScheduleOverride::where('doctor_profile_id', $fromDoctorId)
                    ->whereBetween('override_date', [$startDate, $endDate])
                    ->get();

                foreach ($overridesToUpdate as $override) {
                    $override->doctor_profile_id = $toDoctorId;
                    $override->save();
                }

                $logDesc = "Chuyển ca khám từ BS $fromDoctorId sang BS $toDoctorId (Từ $startDate đến $endDate)";
            }

            SystemLog::create([
                'user_id' => $userId,
                'action' => 'WORK_SCHEDULE_TRANSFERRED',
                'module' => 'work_schedule',
                'description' => $logDesc,
                'ip_address' => request()->ip()
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
