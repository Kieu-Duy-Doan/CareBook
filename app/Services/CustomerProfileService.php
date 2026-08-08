<?php

namespace App\Services;

use App\Models\User;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\PatientProfile;

class CustomerProfileService
{
    public function createCustomer(array $data, array $medicalHistoryPaths = [])
    {
        return DB::transaction(function() use ($data, $medicalHistoryPaths) {
            $userData = [
                'full_name' => $data['full_name'],
                'phone'     => $data['phone'],
                'password'  => Hash::make($data['password']),
                'role'      => 'patient',
                'is_active' => true,
                'email'     => $data['email'] ?? null,
                'username'  => $data['username'] ?? null,
                'id_card'   => $data['id_card'],
            ];
            
            $user = User::create($userData);

            $profile = PatientProfile::create([
                'patient_code'    => 'BN' . $data['id_card'],
                'owner_id'        => $user->id,
                'full_name'       => $data['profile_full_name'] ?? $data['full_name'],
                'date_of_birth'   => $data['date_of_birth'],
                'gender'          => $data['gender'],
                'id_card'         => $data['id_card'],
                'phone'           => $data['profile_phone'] ?? $data['phone'],
                'address'         => $data['address'] ?? null,
                'occupation'      => $data['occupation'] ?? null,
                'ethnicity'       => $data['ethnicity'] ?? null,
                'insurance_code'  => $data['insurance_code'] ?? null,
                'insurance_place' => $data['insurance_place'] ?? null,
                'insurance_expiry'=> $data['insurance_expiry'] ?? null,
                'symptom_notes'   => $data['symptom_notes'] ?? null,
                'medical_history' => !empty($medicalHistoryPaths) ? $medicalHistoryPaths : null,
                'is_self'         => true, 
            ]);

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'USER_CREATED',
                'module'      => 'customers',
                'ref_type'    => 'users',
                'ref_id'      => $user->id,
                'description' => 'Thêm khách hàng (Tài khoản) mới: ' . $user->full_name,
                'ip_address'  => request()->ip(),
            ]);

            return $user;
        });
    }

    public function updateCustomer(User $customer, array $data, $selfProfile, array $medicalHistoryPaths = [], array $deletedMedicalHistories = [])
    {
        return DB::transaction(function() use ($customer, $data, $selfProfile, $medicalHistoryPaths, $deletedMedicalHistories) {
            $userData = [
                'full_name' => $data['full_name'],
                'phone'     => $data['phone'],
                'email'     => $data['email'] ?? null,
                'username'  => $data['username'] ?? null,
            ];

            if (isset($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            if (array_key_exists('id_card', $data) && $data['id_card'] !== $customer->id_card) {
                $userData['id_card'] = $data['id_card'];
            }

            $customer->update($userData);

            if ($selfProfile) {
                $profileData = [
                    'full_name'       => $data['profile_full_name'] ?? $data['full_name'],
                    'date_of_birth'   => $data['date_of_birth'],
                    'gender'          => $data['gender'],
                    'phone'           => $data['profile_phone'] ?? $data['phone'],
                    'address'         => $data['address'] ?? null,
                    'occupation'      => $data['occupation'] ?? null,
                    'ethnicity'       => $data['ethnicity'] ?? null,
                    'insurance_code'  => $data['insurance_code'] ?? null,
                    'insurance_place' => $data['insurance_place'] ?? null,
                    'insurance_expiry'=> $data['insurance_expiry'] ?? null,
                    'symptom_notes'   => $data['symptom_notes'] ?? null,
                ];

                if (array_key_exists('id_card', $data) && $data['id_card'] !== $selfProfile->id_card) {
                    $profileData['id_card'] = $data['id_card'];
                    $profileData['patient_code'] = 'BN' . $data['id_card'];
                    $profileData['card_id_change_count'] = $selfProfile->card_id_change_count + 1;
                }

                $currentMedicalHistories = is_string($selfProfile->medical_history) ? json_decode($selfProfile->medical_history, true) : ($selfProfile->medical_history ?? []);
                
                if (!empty($deletedMedicalHistories)) {
                    foreach ($deletedMedicalHistories as $d) {
                        Storage::disk('public')->delete($d);
                        if (($key = array_search($d, $currentMedicalHistories)) !== false) {
                            unset($currentMedicalHistories[$key]);
                        }
                    }
                    $currentMedicalHistories = array_values($currentMedicalHistories);
                }

                if (!empty($medicalHistoryPaths)) {
                    $currentMedicalHistories = array_merge($currentMedicalHistories, $medicalHistoryPaths);
                }

                $profileData['medical_history'] = !empty($currentMedicalHistories) ? $currentMedicalHistories : null;

                $selfProfile->update($profileData);
            }

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'USER_UPDATED',
                'module'      => 'customers',
                'ref_type'    => 'users',
                'ref_id'      => $customer->id,
                'description' => 'Cập nhật thông tin khách hàng: ' . $customer->full_name,
                'ip_address'  => request()->ip(),
            ]);

            return $customer;
        });
    }
}
