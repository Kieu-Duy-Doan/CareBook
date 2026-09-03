<?php

namespace App\Services;

use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;

class DoctorProfileService
{
    public function createDoctor(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'username' => $data['username'],
                'id_card' => $data['id_card'] ?? null,
                'email' => $data['email'] ?? null,
                'password' => bcrypt($data['password']),
                'role' => 'doctor',
                'is_active' => true,
            ]);

            $doctorCode = $this->_generateDoctorCode($data['full_name']);

            $level = $data['degree'];
            if (in_array($data['academic_rank'], ['GS', 'PGS'])) {
                $level = $data['academic_rank'];
            } elseif ($level === 'BSNT') {
                $level = 'BS';
            }

            $doctor = DoctorProfile::create([
                'user_id' => $user->id,
                'doctor_code' => $doctorCode,
                'academic_rank' => $data['academic_rank'],
                'degree' => $data['degree'],
                'current_position' => $data['current_position'],
                'doctor_type' => $data['doctor_type'],
                'level' => $level,
                'expertise' => $data['expertise'] ?? null,
                'experience_years' => $data['experience_years'] ?? null,
                'license_number' => $data['license_number'] ?? null,
                'bio' => $data['bio'] ?? null,
            ]);

            $syncData = [];
            foreach ($data['specialty_ids'] as $specialtyId) {
                $syncData[$specialtyId] = [
                    'is_primary' => ($specialtyId == $data['primary_specialty_id']) ? 1 : 0,
                ];
            }
            $doctor->specialties()->sync($syncData);

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'DOCTOR_CREATED',
                'module'      => 'doctors',
                'ref_type'    => 'doctor_profiles',
                'ref_id'      => $doctor->id,
                'description' => 'Thêm bác sĩ mới: ' . $data['full_name'],
                'ip_address'  => request()->ip(),
            ]);
            
            return $doctor;
        });
    }

    public function updateDoctor(DoctorProfile $doctor, array $data)
    {
        return DB::transaction(function() use ($doctor, $data) {
            $userData = [
                'full_name' => $data['full_name'],
                'phone'     => $data['phone'],
                'username'  => $data['username'],
                'id_card'   => $data['id_card'] ?? null,
                'email'     => $data['email'] ?? null,
            ];
            $doctor->user->update($userData);

            $level = $data['degree'];
            if (in_array($data['academic_rank'], ['GS', 'PGS'])) {
                $level = $data['academic_rank'];
            } elseif ($level === 'BSNT') {
                $level = 'BS';
            }

            $doctor->update([
                'doctor_code'      => $data['doctor_code'],
                'academic_rank'    => $data['academic_rank'],
                'degree'           => $data['degree'],
                'current_position' => $data['current_position'],
                'doctor_type'      => $data['doctor_type'],
                'level'            => $level,
                'expertise'        => $data['expertise'] ?? null,
                'experience_years' => $data['experience_years'] ?? null,
                'license_number'   => $data['license_number'] ?? null,
                'bio'              => $data['bio'] ?? null,
            ]);

            $syncData = [];
            foreach ($data['specialty_ids'] as $specialtyId) {
                $syncData[$specialtyId] = [
                    'is_primary' => ($specialtyId == $data['primary_specialty_id']) ? 1 : 0
                ];
            }
            $doctor->specialties()->sync($syncData);

            $doctor->touch();

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'DOCTOR_UPDATED',
                'module'      => 'doctors',
                'ref_type'    => 'doctor_profiles',
                'ref_id'      => $doctor->id,
                'description' => 'Cập nhật thông tin bác sĩ: ' . $data['full_name'],
                'ip_address'  => request()->ip(),
            ]);
            
            return $doctor;
        });
    }

    private function _generateDoctorCode($fullName)
    {
        $nameParts = explode(' ', trim($fullName));
        $firstName = array_pop($nameParts);
        $initials = '';
        foreach ($nameParts as $part) {
            if (!empty($part)) {
                $initials .= mb_substr($part, 0, 1);
            }
        }
        $baseCode = \Illuminate\Support\Str::slug($firstName . $initials, '');
        $baseCode = str_replace('-', '', $baseCode);

        $latestProfile = DoctorProfile::where('doctor_code', 'like', $baseCode . '%')
                                      ->orderBy('id', 'desc')
                                      ->first();
        $nextNumber = 1;
        if ($latestProfile) {
            $latestCode = $latestProfile->doctor_code;
            $numberPart = str_replace($baseCode, '', $latestCode);
            if (is_numeric($numberPart)) {
                $nextNumber = intval($numberPart) + 1;
            }
        }
        return $baseCode . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }
}
