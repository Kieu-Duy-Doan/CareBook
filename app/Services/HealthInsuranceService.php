<?php

namespace App\Services;

use App\Models\InsuranceType;
use App\Models\PatientProfile;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HealthInsuranceService
{
    /**
     * Calculate insurance coverage based on patient profile and a collection of ClinicalVisits.
     * 
     * @param PatientProfile $profile
     * @param Collection $visits Array of ClinicalVisit
     * @return array
     */
    public function calculate(PatientProfile $profile, Collection $visits): array
    {
        $isExpired = false;
        $warningMessage = null;
        $insuranceRate = 0;

        if (empty($profile->insurance_code)) {
            $insuranceRate = 0;
        } elseif (!empty($profile->insurance_expiry) && Carbon::parse($profile->insurance_expiry)->isPast()) {
            $isExpired = true;
            $insuranceRate = 0;
            $expiryDate = Carbon::parse($profile->insurance_expiry)->format('d/m/Y');
            $warningMessage = "Thẻ BHYT đã hết hạn ngày {$expiryDate}. Bệnh nhân thanh toán toàn bộ.";
        } else {
            // Tra cứu tỷ lệ chi trả từ bảng insurance_types thay vì fix cứng
            $insuranceType = InsuranceType::findByInsuranceCode($profile->insurance_code);

            if ($insuranceType) {
                $insuranceRate = $insuranceType->coverage_rate;
            } else {
                // Fallback: mã BHYT hợp lệ nhưng chưa được cấu hình trong hệ thống
                $insuranceRate = 0.80;
                $warningMessage = "Mã BHYT chưa được cấu hình trong hệ thống. Áp dụng tỷ lệ mặc định 80%.";
            }
        }

        $totalAmount = 0;
        foreach ($visits as $visit) {
            $totalAmount += $visit->payment_amount;
        }

        $insuranceCovers = round($totalAmount * $insuranceRate);
        $patientPays = $totalAmount - $insuranceCovers;

        return [
            'total_amount' => $totalAmount,
            'insurance_rate' => $insuranceRate,
            'insurance_covers' => $insuranceCovers,
            'patient_pays' => $patientPays,
            'is_expired' => $isExpired,
            'warning_message' => $warningMessage,
        ];
    }
}
