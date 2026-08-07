<?php

namespace App\Services;

use App\Models\User;
use App\Models\StaffProfile;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;

class ReceptionistProfileService
{
    public function createReceptionist(array $data)
    {
        return DB::transaction(function() use ($data) {
            $user = User::create([
                'full_name'  => $data['full_name'],
                'phone'      => $data['phone'],
                'username'   => $data['username'],
                'id_card'    => $data['id_card'] ?? null,
                'email'      => $data['email'] ?? null,
                'password'   => bcrypt($data['password']),
                'role'       => 'receptionist',
                'is_active'  => true,
            ]);

            StaffProfile::create([
                'user_id'        => $user->id,
                'employee_code'  => $data['employee_code'],
                'position'       => 'Lễ tân',
                'department'     => $data['department'] ?? null,
                'internal_phone' => $data['internal_phone'] ?? null,
                'start_date'     => $data['start_date'] ?? null,
                'is_active'      => true,
            ]);

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'RECEPTIONIST_CREATED',
                'module'      => 'receptionists',
                'ref_type'    => 'users',
                'ref_id'      => $user->id,
                'description' => 'Thêm lễ tân mới: ' . $data['full_name'],
                'ip_address'  => request()->ip(),
            ]);
            
            return $user;
        });
    }

    public function updateReceptionist(User $receptionist, array $data)
    {
        return DB::transaction(function() use ($receptionist, $data) {
            $userData = [
                'full_name' => $data['full_name'],
                'phone'     => $data['phone'],
                'username'  => $data['username'],
                'id_card'   => $data['id_card'] ?? null,
                'email'     => $data['email'] ?? null,
            ];
            $receptionist->update($userData);

            $receptionist->staffProfile()->updateOrCreate(
                ['user_id' => $receptionist->id],
                [
                    'employee_code'  => $data['employee_code'],
                    'position'       => 'Lễ tân',
                    'department'     => $data['department'] ?? null,
                    'internal_phone' => $data['internal_phone'] ?? null,
                    'start_date'     => $data['start_date'] ?? null,
                ]
            );

            $receptionist->touch();

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'RECEPTIONIST_UPDATED',
                'module'      => 'receptionists',
                'ref_type'    => 'users',
                'ref_id'      => $receptionist->id,
                'description' => 'Cập nhật thông tin lễ tân: ' . $data['full_name'],
                'ip_address'  => request()->ip(),
            ]);
            
            return $receptionist;
        });
    }
}
