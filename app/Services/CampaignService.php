<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CampaignService
{
    // Lấy danh sách các chiến dịch thông báo
    public function getCampaigns(array $filters, int $perPage = 20)
    {
        $query = Notification::selectRaw('
            batch_id,
            title,
            content,
            type,
            MAX(created_at) as created_at,
            MAX(scheduled_at) as scheduled_at,
            COUNT(DISTINCT user_id) as total_recipients,
            SUM(CASE WHEN channel = "email" THEN 1 ELSE 0 END) as total_email,
            SUM(CASE WHEN channel = "in_web" THEN 1 ELSE 0 END) as total_in_web,
            SUM(CASE WHEN channel = "email" AND is_sent = 1 THEN 1 ELSE 0 END) as sent_email_count,
            SUM(CASE WHEN channel = "in_web" AND is_read = 1 THEN 1 ELSE 0 END) as read_in_web_count,
            MAX(is_sent) as is_sent
        ')
            ->whereNotNull('batch_id')
            ->groupBy('batch_id', 'title', 'content', 'type')
            ->orderBy('created_at', 'desc');

        // Bộ lọc tìm kiếm trên giao diện
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['channel'])) {
            $query->where('channel', $filters['channel']);
        }
        if (!empty($filters['status'])) {
            $status = $filters['status'];
            if ($status === 'sent') {
                $query->havingRaw('MAX(is_sent) = 1');
            } elseif ($status === 'pending') {
                $query->havingRaw('MAX(is_sent) = 0');
            }
        }

        return $query->paginate($perPage)->withQueryString();
    }

    // Xử lý tạo thông báo mới
    public function createCampaign(array $data)
    {
        $now = now();
        $batchId = Str::uuid()->toString();
        $insertData = [];

        // 1. Chuẩn bị mảng dữ liệu khổng lồ
        foreach ($data['user_ids'] as $userId) {
            foreach ($data['channels'] as $channel) {
                $insertData[] = [
                    'batch_id' => $batchId,
                    'user_id' => $userId,
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'type' => $data['type'],
                    'channel' => $channel,
                    'scheduled_at' => $data['scheduled_at'] ?? null,
                    'is_sent' => false,
                    'is_read' => false,
                    'created_at' => $now,
                ];
            }
        }

        // 2. Lưu từng chunk 500 dòng
        foreach (array_chunk($insertData, 500) as $chunk) {
            Notification::insert($chunk);
        }

        // 3. Dispatch job gửi email
        $this->dispatchEmails($batchId);
    }

    // Ném email vào hàng đợi
    private function dispatchEmails(string $batchId)
    {
        $emailNotifications = Notification::where('batch_id', $batchId)
            ->where('channel', 'email')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->get();

        foreach ($emailNotifications as $notification) {
            \App\Jobs\SendEmailNotificationJob::dispatch($notification->id);
        }
    }

    // Xóa chiến dịch thông báo
    public function deleteCampaign(string $batchId)
    {
        return Notification::where('batch_id', $batchId)->delete();
    }

    // Gửi lại thông báo lỗi
    public function resendCampaign(string $batchId)
    {
        return Notification::where('batch_id', $batchId)
            ->where('channel', 'email')
            ->update(['is_sent' => false]);
    }
}
