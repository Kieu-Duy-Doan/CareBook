<?php

namespace App\Services;

use App\Models\PatientProfile;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;

class PatientProfileService
{
    public function createProfile(array $data, array $medicalHistoryPaths = [])
    {
        return DB::transaction(function() use ($data, $medicalHistoryPaths) {
            $profile = PatientProfile::create([
                'patient_code'    => 'BN' . $data['id_card'],
                'owner_id'        => $data['owner_id'],
                'full_name'       => $data['full_name'],
                'date_of_birth'   => $data['date_of_birth'],
                'gender'          => $data['gender'],
                'id_card'         => $data['id_card'] ?? null,
                'phone'           => $data['phone'] ?? null,
                'address'         => $data['address'] ?? null,
                'occupation'      => $data['occupation'] ?? null,
                'ethnicity'       => $data['ethnicity'] ?? null,
                'insurance_code'  => $data['insurance_code'] ?? null,
                'insurance_place' => $data['insurance_place'] ?? null,
                'insurance_expiry'=> $data['insurance_expiry'] ?? null,
                'symptom_notes'   => $data['symptom_notes'] ?? null,
                'medical_history' => !empty($medicalHistoryPaths) ? $medicalHistoryPaths : null,
                'is_self'         => $data['is_self'],
            ]);

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'PATIENT_PROFILE_CREATED',
                'module'      => 'patients',
                'ref_type'    => 'patient_profiles',
                'ref_id'      => $profile->id,
                'description' => 'Thêm hồ sơ bệnh nhân mới: ' . $data['full_name'],
                'ip_address'  => request()->ip(),
            ]);

            return $profile;
        });
    }

    public function updateProfile(PatientProfile $profile, array $data)
    {
        return DB::transaction(function() use ($profile, $data) {
            $updateData = [
                'owner_id'        => $data['owner_id'],
                'is_self'         => $data['is_self'],
                'full_name'       => $data['full_name'],
                'date_of_birth'   => $data['date_of_birth'],
                'gender'          => $data['gender'],
                'phone'           => $data['phone'] ?? null,
                'address'         => $data['address'] ?? null,
                'occupation'      => $data['occupation'] ?? null,
                'ethnicity'       => $data['ethnicity'] ?? null,
                'insurance_code'  => $data['insurance_code'] ?? null,
                'insurance_place' => $data['insurance_place'] ?? null,
                'insurance_expiry'=> $data['insurance_expiry'] ?? null,
                'symptom_notes'   => $data['symptom_notes'] ?? null,
            ];

            if ($profile->card_id_change_count < 1 && isset($data['id_card']) && $data['id_card'] !== $profile->id_card) {
                $updateData['id_card'] = $data['id_card'];
                $updateData['patient_code'] = 'BN' . $data['id_card'];
                $updateData['card_id_change_count'] = $profile->card_id_change_count + 1;
            }

            $profile->update($updateData);

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'PATIENT_PROFILE_UPDATED',
                'module'      => 'patients',
                'ref_type'    => 'patient_profiles',
                'ref_id'      => $profile->id,
                'description' => 'Cập nhật thông tin hồ sơ: ' . $data['full_name'],
                'ip_address'  => request()->ip(),
            ]);

            return $profile;
        });
    }
}
