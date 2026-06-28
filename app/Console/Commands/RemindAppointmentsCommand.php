<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Jobs\SendAppointmentReminderJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class RemindAppointmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi thông báo nhắc lịch khám cho bệnh nhân (trước 2h và 30m)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Start RemindAppointmentsCommand');
        $now = now();
        $count2h = 0;
        $count30m = 0;

        // Quét các lịch khám trong ngày hôm nay
        // Do scheduler chạy mỗi 5 phút, ta sẽ lấy khoảng thời gian quét rộng hơn một chút để không bị lọt (ví dụ +5 phút)
        $appointments = Appointment::whereIn('status', ['pending', 'checked_in'])
            ->whereDate('appointment_date', $now->toDateString())
            ->where(function($q) {
                $q->where('reminded_2h', false)
                  ->orWhere('reminded_30m', false);
            })
            ->get();

        foreach ($appointments as $appointment) {
            $appointmentDateTime = Carbon::parse($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time);
            $minutesToAppointment = $now->diffInMinutes($appointmentDateTime, false); // false để có giá trị âm nếu đã qua giờ

            // Nếu giờ khám còn cách từ 115 đến 125 phút -> Nhắc 2 tiếng
            if (!$appointment->reminded_2h && $minutesToAppointment > 0 && $minutesToAppointment <= 125) {
                SendAppointmentReminderJob::dispatch($appointment, '2 tiếng');
                $appointment->reminded_2h = true;
                $appointment->save();
                $count2h++;
            }
            
            // Nếu giờ khám còn cách từ 25 đến 35 phút -> Nhắc 30 phút
            // Hoặc nếu nó đã vượt quá mốc 30 phút (do queue bị chậm) thì nhắc luôn
            if (!$appointment->reminded_30m && $minutesToAppointment > 0 && $minutesToAppointment <= 35) {
                SendAppointmentReminderJob::dispatch($appointment, '30 phút');
                $appointment->reminded_30m = true;
                $appointment->save();
                $count30m++;
            }
        }

        Log::info("RemindAppointmentsCommand Finished: Reminded 2h: {$count2h}, Reminded 30m: {$count30m}");
        $this->info("Đã xử lý nhắc lịch. 2 tiếng: {$count2h}, 30 phút: {$count30m}");
    }
}
