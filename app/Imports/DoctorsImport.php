<?php

namespace App\Imports;

use App\Models\User;
use App\Models\DoctorProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DoctorsImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception('File import không có dữ liệu (file rỗng).');
        }

        $importedDoctorCodes = [];

        DB::transaction(function () use ($rows, &$importedDoctorCodes) {
            foreach ($rows as $index => $row) {
                // Bỏ qua dòng tiêu đề (dòng đầu tiên)
                if ($index === 0) {
                    continue;
                }

                $doctorCode = trim($row[0] ?? '');
                $fullname   = trim($row[1] ?? '');
                $phone      = trim($row[2] ?? '');
                $username   = trim($row[3] ?? '');
                $passwordInput = trim($row[4] ?? '');
                $idCard     = trim($row[5] ?? '');
                $email      = trim($row[6] ?? '');
                $statusInput = trim($row[7] ?? '');
                $title      = trim($row[8] ?? '');
                $level      = trim($row[9] ?? '');
                $expertise  = trim($row[10] ?? '');
                $experience = trim($row[11] ?? '');
                $cchn       = trim($row[12] ?? '');
                $bio        = trim($row[13] ?? '');

                if (empty($username) || empty($fullname)) {
                    continue; // Bỏ qua nếu thiếu trường bắt buộc
                }

                // Trạng thái (nếu rỗng mặc định Đang hoạt động)
                $isActive = true;
                if ($statusInput !== '') {
                    $val = mb_strtolower($statusInput);
                    $isActive = ($val === 'đang hoạt động' || $val === '1' || $val === 'true' || $val === 'active');
                }

                $existingDoctor = null;
                if ($doctorCode) {
                    $existingDoctor = DoctorProfile::where('doctor_code', $doctorCode)->first();
                }

                if ($existingDoctor) {
                    $user = $existingDoctor->user;
                    
                    // Kiểm tra trùng lặp khi Update
                    if ($username !== $user->username && User::where('username', $username)->exists()) {
                        throw new \Exception("Dòng " . ($index + 1) . ": Tên đăng nhập '$username' đã tồn tại trong hệ thống.");
                    }
                    if ($phone && $phone !== $user->phone && User::where('phone', $phone)->exists()) {
                        throw new \Exception("Dòng " . ($index + 1) . ": Số điện thoại '$phone' đã tồn tại trong hệ thống.");
                    }
                    if ($email && $email !== $user->email && User::where('email', $email)->exists()) {
                        throw new \Exception("Dòng " . ($index + 1) . ": Email '$email' đã tồn tại trong hệ thống.");
                    }
                    if ($idCard && $idCard !== $user->id_card && User::where('id_card', $idCard)->exists()) {
                        throw new \Exception("Dòng " . ($index + 1) . ": CMND/CCCD '$idCard' đã tồn tại trong hệ thống.");
                    }
                    if ($cchn && $cchn !== $existingDoctor->license_number && DoctorProfile::where('license_number', $cchn)->exists()) {
                        throw new \Exception("Dòng " . ($index + 1) . ": Số CCHN '$cchn' đã tồn tại trong hệ thống.");
                    }

                    $userData = [
                        'full_name' => $fullname,
                        'phone'     => $phone,
                        'username'  => $username,
                        'id_card'   => $idCard ?: null,
                        'email'     => $email ?: null,
                        'is_active' => $isActive,
                    ];

                    if (!empty($passwordInput)) {
                        $userData['password'] = Hash::make($passwordInput);
                    }

                    $user->fill($userData);
                    $userDirty = $user->isDirty();
                    if ($userDirty) {
                        $user->save();
                    }

                    $existingDoctor->fill([
                        'academic_title'   => $title ?: null,
                        'level'            => $level ?: 'BS',
                        'expertise'        => $expertise ?: null,
                        'experience_years' => $experience !== '' ? $experience : 0,
                        'license_number'   => $cchn ?: null,
                        'bio'              => $bio ?: null,
                    ]);
                    $doctorDirty = $existingDoctor->isDirty();
                    if ($doctorDirty) {
                        $existingDoctor->save();
                    }

                    // Chỉ đẩy lên đầu nếu có thực sự thay đổi dữ liệu (ở User hoặc DoctorProfile)
                    if ($userDirty && !$doctorDirty) {
                        $existingDoctor->touch();
                    }

                    $importedDoctorCodes[] = $doctorCode;
                } else {
                    // Kiểm tra trùng lặp khi Create
                    if (User::where('username', $username)->exists()) {
                        throw new \Exception("Dòng " . ($index + 1) . ": Tên đăng nhập '$username' đã tồn tại trong hệ thống.");
                    }
                    if ($phone && User::where('phone', $phone)->exists()) {
                        throw new \Exception("Dòng " . ($index + 1) . ": Số điện thoại '$phone' đã tồn tại trong hệ thống.");
                    }
                    if ($email && User::where('email', $email)->exists()) {
                        throw new \Exception("Dòng " . ($index + 1) . ": Email '$email' đã tồn tại trong hệ thống.");
                    }
                    if ($idCard && User::where('id_card', $idCard)->exists()) {
                        throw new \Exception("Dòng " . ($index + 1) . ": CMND/CCCD '$idCard' đã tồn tại trong hệ thống.");
                    }
                    if ($cchn && DoctorProfile::where('license_number', $cchn)->exists()) {
                        throw new \Exception("Dòng " . ($index + 1) . ": Số CCHN '$cchn' đã tồn tại trong hệ thống.");
                    }
                    if ($doctorCode && DoctorProfile::where('doctor_code', $doctorCode)->exists()) {
                        throw new \Exception("Dòng " . ($index + 1) . ": Mã bác sĩ '$doctorCode' đã tồn tại trong hệ thống.");
                    }

                    if (!$doctorCode) {
                        $slug = \Illuminate\Support\Str::slug($fullname); // "bui-xuan-huan"
                        $parts = explode('-', $slug);
                        
                        if (count($parts) == 1) {
                            $prefix = $parts[0];
                        } else {
                            $firstName = array_pop($parts); // huan
                            $initials = '';
                            foreach ($parts as $part) {
                                if (!empty($part)) {
                                    $initials .= substr($part, 0, 1); // b, x
                                }
                            }
                            $prefix = $firstName . $initials; // huanbx
                        }

                        $latestDoctor = DoctorProfile::where('doctor_code', 'regexp', '^' . $prefix . '[0-9]{2,}$')
                            ->orderByRaw('CAST(SUBSTRING(doctor_code, '.(strlen($prefix)+1).') AS UNSIGNED) DESC')
                            ->first();
                            
                        $nextNumber = 1;
                        if ($latestDoctor) {
                            $numberStr = substr($latestDoctor->doctor_code, strlen($prefix));
                            if (is_numeric($numberStr)) {
                                $nextNumber = (int)$numberStr + 1;
                            }
                        }
                        
                        $doctorCode = $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
                    }

                    $password = !empty($passwordInput) ? $passwordInput : 'Password@123';

                    $user = User::create([
                        'full_name' => $fullname,
                        'phone'     => $phone,
                        'username'  => $username,
                        'id_card'   => $idCard ?: null,
                        'email'     => $email ?: null,
                        'password'  => Hash::make($password),
                        'role'      => 'doctor',
                        'is_active' => $isActive,
                    ]);

                    $doctorToSync = DoctorProfile::create([
                        'user_id'          => $user->id,
                        'doctor_code'      => $doctorCode,
                        'academic_title'   => $title ?: null,
                        'level'            => $level ?: 'BS',
                        'expertise'        => $expertise ?: null,
                        'experience_years' => $experience !== '' ? $experience : 0,
                        'license_number'   => $cchn ?: null,
                        'bio'              => $bio ?: null,
                    ]);

                    $importedDoctorCodes[] = $doctorCode;
                }
            }
        });
    }
}
