<?php

namespace App\Console\Commands;

use App\Mail\FollowupReminderMail;
use App\Models\MedicalRecord;
use App\Services\UserNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RemindFollowupsCommand extends Command
{
    protected $signature = 'followups:remind';

    protected $description = 'Gửi thông báo và email nhắc nhở tái khám cho bệnh nhân trước 1 ngày';

    public function handle(UserNotificationService $notificationService): void
    {
        $this->info('Bắt đầu xử lý nhắc tái khám...');
        Log::info('[RemindFollowupsCommand] Start');

        $tomorrow = Carbon::tomorrow()->toDateString();

        $records = MedicalRecord::with([
            'appointment.patientProfile',
            'appointment.bookedByUser',
            'appointment.doctorProfile',
        ])
            ->whereDate('followup_date', $tomorrow)
            ->where('followup_reminded', false)
            ->whereNotNull('followup_date')
            ->get();

        if ($records->isEmpty()) {
            $this->info('Không có lịch tái khám nào cần nhắc hôm nay.');
            Log::info('[RemindFollowupsCommand] No followups to remind today.');
            return;
        }

        $count = 0;

        foreach ($records as $record) {
            $patientUser = $record->appointment->bookedByUser ?? null;

            if (!$patientUser) {
                Log::warning("[RemindFollowupsCommand] MedicalRecord #{$record->id}: Không tìm thấy user bệnh nhân.");
                continue;
            }

            try {
                // 1. Gửi thông báo in-web
                $notificationService->notifyFollowupReminder($record);

                // 2. Gửi email nếu bệnh nhân có email
                if ($patientUser->email) {
                    Mail::to($patientUser->email)->queue(new FollowupReminderMail($record));
                }

                // 3. Đánh dấu đã gửi nhắc nhở
                $record->update(['followup_reminded' => true]);

                $count++;
                $this->line("  ✓ Đã nhắc: {$patientUser->email} — MedicalRecord #{$record->id} (Tái khám: {$record->followup_date->format('d/m/Y')})");
                Log::info("[RemindFollowupsCommand] Reminded MedicalRecord #{$record->id} for user #{$patientUser->id}");
            } catch (\Throwable $e) {
                Log::error("[RemindFollowupsCommand] Lỗi với MedicalRecord #{$record->id}: " . $e->getMessage());
                $this->error("  ✗ Lỗi với MedicalRecord #{$record->id}: " . $e->getMessage());
            }
        }

        $this->info("Hoàn tất! Đã gửi nhắc nhở cho {$count} bệnh nhân.");
        Log::info("[RemindFollowupsCommand] Done. Sent: {$count}");
    }
}
