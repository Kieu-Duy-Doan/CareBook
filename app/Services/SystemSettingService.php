<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingService
{
    /**
     * Lấy giá trị của một cài đặt, có cache.
     */
    public function get(string $key, $default = null)
    {
        $settings = Cache::rememberForever('system_settings', function () {
            return SystemSetting::all()->keyBy('key');
        });

        if (!$settings->has($key)) {
            return $default;
        }

        $setting = $settings->get($key);
        return $this->castValue($setting->value, $setting->data_type);
    }

    /**
     * Cập nhật hoặc tạo mới một cài đặt.
     */
    public function set(string $key, $value, string $dataType = 'string', ?string $description = null, ?int $updatedBy = null)
    {
        $setting = SystemSetting::firstOrNew(['key' => $key]);

        // Validate value based on data_type
        if (!$this->validateValue($value, $dataType)) {
            throw new \InvalidArgumentException("Invalid value format for type {$dataType} on key {$key}");
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

        Cache::forget('system_settings');

        return $setting;
    }

    /**
     * Ép kiểu dữ liệu khi đọc.
     */
    protected function castValue($value, string $dataType)
    {
        return match ($dataType) {
            'integer' => (int)$value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => (string)$value,
        };
    }

    /**
     * Kiểm tra tính hợp lệ của dữ liệu trước khi lưu.
     */
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
