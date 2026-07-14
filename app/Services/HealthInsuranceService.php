<?php

namespace App\Services;

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
        } elseif (empty($profile->insurance_expiry) || Carbon::parse($profile->insurance_expiry)->isPast()) {
            $isExpired = true;
            $insuranceRate = 0;
            $expiryDate = $profile->insurance_expiry ? Carbon::parse($profile->insurance_expiry)->format('d/m/Y') : 'Không rõ';
            $warningMessage = "Thẻ BHYT đã hết hạn ngày {$expiryDate}. Bệnh nhân thanh toán toàn bộ.";
        } else {
            $prefix = strtoupper(substr($profile->insurance_code, 0, 2));
            if (in_array($prefix, ['TE', 'HT'])) {
                $insuranceRate = 1.00;
            } elseif (in_array($prefix, ['DN', 'HC'])) {
                $insuranceRate = 0.95;
            } else {
                $insuranceRate = 0.80; // Phổ thông
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
