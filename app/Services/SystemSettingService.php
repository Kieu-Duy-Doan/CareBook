<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingService
{
    protected $logService;

    // Gọi SystemLogService vào để dùng chung
    public function __construct(SystemLogService $logService)
    {
        $this->logService = $logService;
    }

    // Lấy thông tin một cài đặt
    public function get(string $key, $default = null)
    {
        // Lưu tạm cấu hình vào bộ nhớ (Cache) để web chạy nhanh hơn, không phải hỏi DB liên tục
        $settings = Cache::rememberForever('system_settings', function () {
            return SystemSetting::all()->keyBy('key');
        });

        if (!$settings->has($key)) {
            return $default;
        }

        $setting = $settings->get($key);
        // Chuyển dữ liệu thành đúng định dạng (số, chữ, đúng/sai)
        return $this->castValue($setting->value, $setting->data_type);
    }

    // Tạo mới hoặc cập nhật một cài đặt
    public function set(string $key, $value, string $dataType = 'string', ?string $description = null, ?int $updatedBy = null)
    {
        $setting = SystemSetting::firstOrNew(['key' => $key]);

        // Kiểm tra xem dữ liệu đưa vào có đúng loại không
        if (!$this->validateValue($value, $dataType)) {
            throw new \InvalidArgumentException("Lỗi định dạng dữ liệu cấu hình: kiểu {$dataType} cho khoá {$key}");
        }

        $setting->value = is_array($value) ? json_encode($value) : (string)$value;
        $setting->data_type = $dataType;

        if ($description !== null) {
            $setting->description = $description;
        }

        if ($updatedBy !== null) {
            $setting->updated_by = $updatedBy;
        }

        $setting->save();

        // Xoá bộ nhớ tạm (Cache) để hệ thống nhận được dữ liệu mới ngay lập tức
        Cache::forget('system_settings');

        return $setting;
    }

    // Lưu nhiều cài đặt cùng lúc từ form màn hình Cài đặt hệ thống
    public function updateBulkSettings(array $settingsData, array $settingsTypes, $logoFile, int $userId, bool $removeLogo = false)
    {
        // 1. Nếu có ảnh logo thì lưu ảnh vào thư mục public/settings
        if ($logoFile) {
            $logoPath = $logoFile->store('settings', 'public');
            $settingsData['logo'] = $logoPath;
            $settingsTypes['logo'] = 'string';
        } elseif ($removeLogo) {
            // Xóa logo trong cấu hình
            $settingsData['logo'] = '';
            $settingsTypes['logo'] = 'string';
            
            // Xóa file vật lý khỏi ổ cứng để tiết kiệm dung lượng
            $oldLogo = $this->get('logo');
            if ($oldLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldLogo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldLogo);
            }
        }

        // Lấy dữ liệu cũ ra trước để lát nữa so sánh xem có ai thay đổi gì không
        $oldSettings = SystemSetting::all()->keyBy('key')->toArray();
        $changedData = [];

        // 2. Lưu từng thông số vào Database
        foreach ($settingsData as $key => $value) {
            $dataType = $settingsTypes[$key] ?? 'string';

            // Gọi hàm lưu dữ liệu ở phía trên
            $this->set($key, $value, $dataType, null, $userId);

            // 3. So sánh dữ liệu cũ và mới. Nếu khác nhau thì gom lại để ghi log
            // Ép thành dạng chữ (string) để so sánh cho chuẩn
            if (!isset($oldSettings[$key]) || $oldSettings[$key]['value'] !== (string)$value) {
                $changedData[$key] = [
                    'old' => $oldSettings[$key]['value'] ?? null,
                    'new' => $value
                ];
            }
        }

        // 4. Lưu lại lịch sử nếu có bất kì chỉnh sửa nào
        if (!empty($changedData) && $this->logService) {
            $this->logService->log(
                action: 'SETTINGS_UPDATED',
                module: 'settings',
                oldData: array_map(fn($item) => $item['old'], $changedData),
                newData: array_map(fn($item) => $item['new'], $changedData),
                userId: $userId
            );
        }
    }

    // Hàm phụ trợ: Ép lại kiểu dữ liệu khi lấy từ DB ra
    protected function castValue($value, string $dataType)
    {
        return match ($dataType) {
            'integer' => (int)$value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => (string)$value,
        };
    }

    // Hàm phụ trợ: Kiểm tra định dạng dữ liệu có đúng yêu cầu không
    protected function validateValue($value, string $dataType): bool
    {
        if ($dataType === 'json') {
            if (is_array($value)) return true;
            json_decode($value);
            return json_last_error() === JSON_ERROR_NONE;
        }

        if ($dataType === 'integer') {
            return is_numeric($value);
        }

        return true;
    }
}
