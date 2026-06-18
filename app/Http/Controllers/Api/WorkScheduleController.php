<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WorkScheduleService;
use App\Models\WorkSchedule;
use App\Models\ScheduleOverride;
use App\Models\Appointment;
use Carbon\Carbon;
use Exception;

class WorkScheduleController extends Controller
{
    protected $workScheduleService;

    public function __construct(WorkScheduleService $workScheduleService)
    {
        $this->workScheduleService = $workScheduleService;
    }

    /**
     * Fetch available time slots for a specific doctor on a specific date.
     *
     * @param Request $request
     * @param int $doctorId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableSlots(Request $request, $doctorId)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        try {
            $slots = $this->workScheduleService->getAvailableSlots($doctorId, $request->date);
            $room = null;
            $carbonDate = Carbon::parse($request->date);
            $dayOfWeek = $carbonDate->dayOfWeek + 1;
            $maxSlots = 1;

            $override = ScheduleOverride::where('doctor_profile_id', $doctorId)
                ->whereDate('override_date', $carbonDate->toDateString())
                ->first();

            if ($override && $override->room) {
                $room = $override->room;
            } else {
                $schedule = WorkSchedule::where('doctor_profile_id', $doctorId)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('is_active', true)
                    ->first();

                if ($schedule && $schedule->room) {
                    $room = $schedule->room;
                    $maxSlots = $schedule->max_slots ?? 1;
                }
            }

            // Lấy số lượng lịch hẹn cho mỗi slot
            $bookedAppointments = Appointment::where('doctor_profile_id', $doctorId)
                ->whereDate('appointment_date', $carbonDate->toDateString())
                ->whereNotIn('status', ['cancelled', 'absent'])
                ->pluck('appointment_time')
                ->map(function ($time) {
                    return substr($time, 0, 5);
                })
                ->toArray();

            // Xây dựng chi tiết slot với info booked
            $slotDetails = array_map(function ($slot) use ($bookedAppointments, $maxSlots) {
                $bookedCount = count(array_filter($bookedAppointments, fn($t) => $t === $slot));
                return [
                    'time' => $slot,
                    'bookedCount' => $bookedCount,
                    'maxSlots' => $maxSlots,
                    'isFull' => $bookedCount >= $maxSlots,
                ];
            }, $slots);

            return response()->json([
                'success' => true,
                'data' => [
                    'slots' => $slotDetails,
                    'room' => $room ? [
                        'id' => $room->id,
                        'name' => $room->name,
                        'room_number' => $room->room_number,
                    ] : null,
                ],
                'message' => empty($slots) ? 'Không có ca khám nào trống trong ngày này.' : 'Lấy danh sách ca khám thành công.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tính toán lịch khám.',
                'error' => $e->getMessage() // In production, hide the exact error
            ], 500);
        }
    }

    public function getWorkSchedule(Request $request)
    {
        try {
            $doctorId = $request->route('doctorId');
            $appointmentDate = $request->route('appointmentDate');

            // Hoặc nếu muốn chi tiết theo ngày
            $carbonDate = Carbon::parse($appointmentDate);
            $dayOfWeek = $carbonDate->dayOfWeek + 1;

            if ($dayOfWeek == 8) {
                $dayOfWeek = 1;
            }

            $todaySchedules = WorkSchedule::where('doctor_profile_id', $doctorId)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->with('room')
                ->get();

            $overrides = ScheduleOverride::where('doctor_profile_id', $doctorId)
                ->whereDate('override_date', $carbonDate->toDateString())
                ->where('type', 'extra')
                ->with('room')
                ->get();

            // dd($todaySchedules->toArray(), $overrides->toArray());

            if ($todaySchedules->isEmpty() && $overrides->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'today_schedule' => [], // Lịch hôm nay
                    ],
                    'message' => 'Lịch làm việc trống',
                ]);
            }


            $slots = [];

            // Nếu lịch hằng ngày không trống
            $slotNormals = [];
            if (!$todaySchedules->isEmpty()) {
                foreach ($todaySchedules as $key => $todaySchedule) {
                    $startTime = $todaySchedule->start_time;
                    $endTime = $todaySchedule->end_time;
                    $duration = $todaySchedule->slot_duration_minutes;


                    $current = Carbon::createFromFormat('H:i:s', $startTime);
                    $end = Carbon::createFromFormat('H:i:s', $endTime);

                    while ($current->lt($end)) {

                        $slot = $current->copy();

                        $slotNormals[] = [
                            'time' => $slot->format('H:i'),
                            'room' => $todaySchedule->room,
                        ];

                        $current->addMinutes($duration);
                    }
                }
            }

            // Nếu lịch ghi đè không trống
            $slotOverrides = [];
            if (!$overrides->isEmpty()) {
                foreach ($overrides as $key => $override) {
                    $startTime = $override->start_time;
                    $endTime = $override->end_time;
                    $duration = $override->slot_duration_minutes ?? 15;


                    $current = Carbon::createFromFormat('H:i:s', $startTime);
                    $end = Carbon::createFromFormat('H:i:s', $endTime);

                    while ($current->lt($end)) {

                        $slot = $current->copy();

                        $slotOverrides[] = [
                            'time' => $slot->format('H:i'),
                            'room' => $override->room,
                        ];

                        $current->addMinutes($duration);
                    }
                }
            }


            // Nếu lịch hằng ngày không trống, lịch ghi đè trống.
            if ($overrides->isEmpty() && !$todaySchedules->isEmpty()) {
                $slots = $slotNormals;
            }


            // Nếu lịch hằng ngày trống, lịch ghi đè không trống
            if (!$overrides->isEmpty() && $todaySchedules->isEmpty()) {
                $slots = $slotOverrides;
            }

            // Nếu lịch hằng ngày không trống, lịch ghi đè không trống
            if (!$overrides->isEmpty() && !$todaySchedules->isEmpty()) {
                $time1 = Carbon::createFromFormat('H:i', $slotNormals[0]['time']);
                $time2 = Carbon::createFromFormat('H:i', $slotOverrides[0]['time']);

                if ($time1->lt($time2)) {
                    $slots = array_merge($slotNormals, $slotOverrides);
                } else {
                    $slots = array_merge($slotOverrides, $slotNormals);
                }
            }

            // Phản hồi
            return response()->json([
                'success' => true,
                'data' => [
                    'today_schedule' => $slots, // Lịch hôm nay
                ],
                'message' => 'Lấy lịch làm việc thành công.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy lịch làm việc.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
