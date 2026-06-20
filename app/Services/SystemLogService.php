<?php

namespace App\Services;

use App\Models\SystemLog;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemLogService
{
    // Ghi lại lịch sử hệ thống (người nào, làm gì, thay đổi cái gì)
    public function log(
        string $action,
        ?string $module = null,
        ?string $refType = null,
        ?int $refId = null,
        ?array $oldData = null,
        ?array $newData = null,
        ?int $userId = null
    ): SystemLog {
        return SystemLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'module' => $module,
            'ref_type' => $refType,
            'ref_id' => $refId,
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // Xuất dữ liệu Nhật ký ra file JSON
    // Dùng cách tải nối tiếp (streaming) để máy chủ không bị đơ hoặc lỗi RAM khi file quá nặng
    public function exportJsonStream($query): StreamedResponse
    {
        return response()->streamDownload(function () use ($query) {
            echo "[\n";
            $first = true;
            
            // Dùng cursor() để lấy từng dòng một từ Database ra thay vì lấy hết 1 cục nặng
            foreach ($query->cursor() as $log) {
                if (!$first) {
                    echo ",\n"; // Thêm dấu phẩy giữa các mảng dữ liệu
                }
                $first = false;
                
                // Sắp xếp lại thông tin cho gọn gàng trước khi in ra file
                $exportLog = [
                    'id' => $log->id,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'user' => $log->user ? $log->user->full_name : 'Hệ thống',
                    'action' => $log->action,
                    'module' => $log->module,
                    'ref_type' => $log->ref_type,
                    'ref_id' => $log->ref_id,
                    'old_data' => $log->old_data,
                    'new_data' => $log->new_data,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent
                ];
                
                // In ra file theo chuẩn JSON, hỗ trợ hiển thị tiếng Việt không bị lỗi font
                echo json_encode($exportLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            echo "\n]";
        }, 'system_logs_' . date('Ymd_His') . '.json', [
            'Content-Type' => 'application/json',
        ]);
    }
}
