<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsuranceType extends Model
{
    protected $fillable = [
        'prefix',
        'name',
        'coverage_percent',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'coverage_percent' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Tìm loại BHYT theo mã thẻ (2 ký tự đầu)
     */
    public static function findByInsuranceCode(?string $insuranceCode): ?self
    {
        if (empty($insuranceCode) || strlen($insuranceCode) < 2) {
            return null;
        }

        $prefix = strtoupper(substr($insuranceCode, 0, 2));

        return static::where('prefix', $prefix)->where('is_active', true)->first();
    }

    /**
     * Lấy tỷ lệ chi trả dạng thập phân (0.0 - 1.0)
     */
    public function getCoverageRateAttribute(): float
    {
        return $this->coverage_percent / 100;
    }
}
