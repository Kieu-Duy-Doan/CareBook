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
                $email      = trim($row[3] ?? '');
                $username   = trim($row[4] ?? '');
                $passwordInput = trim($row[5] ?? '');
                $level      = trim($row[6] ?? '');
                $title      = trim($row[7] ?? '');
                $experience = trim($row[8] ?? '');
                $cchn       = trim($row[9] ?? '');
                $primarySpecialtyName = trim($row[10] ?? '');
                $otherSpecialtiesString = trim($row[11] ?? '');
                $statusInput = trim($row[12] ?? '');

                if (empty($username) || empty($fullname)) {
                    continue; // Bỏ qua nếu thiếu trường bắt buộc
                }

                // Kiểm tra trùng lặp (Unique) ngay lập tức
                // 1. Username
                if (User::where('username', $username)->exists()) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Tên đăng nhập '$username' đã tồn tại trong hệ thống.");
                }

                // 2. Phone
                if ($phone && User::where('phone', $phone)->exists()) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Số điện thoại '$phone' đã tồn tại trong hệ thống.");
                }

                // 3. Email
                if ($email && User::where('email', $email)->exists()) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Email '$email' đã tồn tại trong hệ thống.");
                }

                // 4. Mã Bác sĩ (nếu có nhập)
                if ($doctorCode && DoctorProfile::where('doctor_code', $doctorCode)->exists()) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Mã bác sĩ '$doctorCode' đã tồn tại trong hệ thống.");
                }

                // 5. Số CCHN
                if ($cchn && DoctorProfile::where('license_number', $cchn)->exists()) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Số CCHN '$cchn' đã tồn tại trong hệ thống.");
                }

                // Trạng thái (nếu rỗng mặc định Đang hoạt động)
                $isActive = true;
                if ($statusInput !== '') {
                    $val = mb_strtolower($statusInput);
                    $isActive = ($val === 'đang hoạt động' || $val === '1' || $val === 'true' || $val === 'active');
                }

                // CHỈ THÊM MỚI (CREATE)
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
                    'experience_years' => $experience !== '' ? $experience : 0,
                    'license_number'   => $cchn ?: null,
                ]);

                $importedDoctorCodes[] = $doctorCode;

                // Đồng bộ chuyên khoa
                $syncData = [];
                if (!empty($primarySpecialtyName)) {
                    $primarySpecialty = \App\Models\Specialty::where('name', 'like', $primarySpecialtyName)->first();
                    if ($primarySpecialty) {
                        $syncData[$primarySpecialty->id] = ['is_primary' => 1];
                    }
                }

                if (!empty($otherSpecialtiesString)) {
                    $otherNames = array_map('trim', explode(',', $otherSpecialtiesString));
                    foreach ($otherNames as $name) {
                        if (!empty($name)) {
                            $sp = \App\Models\Specialty::where('name', 'like', $name)->first();
                            if ($sp && !isset($syncData[$sp->id])) {
                                $syncData[$sp->id] = ['is_primary' => 0];
                            }
                        }
                    }
                }
                
                if (count($syncData) > 0) {
                    $doctorToSync->specialties()->sync($syncData);
                }
            }
        });
    }
}
