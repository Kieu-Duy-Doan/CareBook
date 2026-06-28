<?php

namespace App\Imports;

use App\Models\User;
use App\Models\StaffProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class ReceptionistsImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception('File import không có dữ liệu (file rỗng).');
        }

        $importedEmployeeCodes = [];

        DB::transaction(function () use ($rows, &$importedEmployeeCodes) {
            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue;
                }

                $employeeCode = trim($row[0] ?? '');
                $fullname     = trim($row[1] ?? '');
                $phone        = trim($row[2] ?? '');
                $email        = trim($row[3] ?? '');
                $username     = trim($row[4] ?? '');
                $passwordInput= trim($row[5] ?? '');
                $idCard       = trim($row[6] ?? '');
                $deptInput    = trim($row[7] ?? '');
                $internalPhone= trim($row[8] ?? '');
                $startDate    = trim($row[9] ?? '');
                $statusInput  = trim($row[10] ?? '');
                
                if (empty($username) || empty($fullname)) {
                    continue;
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

                // 4. CMND/CCCD
                if ($idCard && User::where('id_card', $idCard)->exists()) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Số CMND/CCCD '$idCard' đã tồn tại trong hệ thống.");
                }

                // 5. Mã Nhân viên (nếu có nhập)
                if ($employeeCode && StaffProfile::where('employee_code', $employeeCode)->exists()) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Mã nhân viên '$employeeCode' đã tồn tại trong hệ thống.");
                }

                $isActive = true;
                if ($statusInput !== '') {
                    $val = mb_strtolower($statusInput);
                    $isActive = ($val === 'đang hoạt động' || $val === '1' || $val === 'true' || $val === 'active');
                }

                // Chuan hoa phong ban
                $department = $deptInput ?: 'Tiếp nhận bệnh nhân';

                // CHỈ THÊM MỚI (CREATE)
                if (!$employeeCode) {
                    $slug = \Illuminate\Support\Str::slug($fullname);
                    $parts = explode('-', $slug);
                    
                    if (count($parts) == 1) {
                        $prefix = $parts[0];
                    } else {
                        $firstName = array_pop($parts);
                        $initials = '';
                        foreach ($parts as $part) {
                            if (!empty($part)) {
                                $initials .= substr($part, 0, 1);
                            }
                        }
                        $prefix = $firstName . $initials;
                    }

                    $latestStaff = StaffProfile::where('employee_code', 'regexp', '^' . $prefix . '[0-9]{2,}$')
                        ->orderByRaw('CAST(SUBSTRING(employee_code, '.(strlen($prefix)+1).') AS UNSIGNED) DESC')
                        ->first();
                        
                    $nextNumber = 1;
                    if ($latestStaff) {
                        $numberStr = substr($latestStaff->employee_code, strlen($prefix));
                        if (is_numeric($numberStr)) {
                            $nextNumber = (int)$numberStr + 1;
                        }
                    }
                    
                    $employeeCode = $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
                }

                $password = !empty($passwordInput) ? $passwordInput : 'Password@123';

                $user = User::create([
                    'full_name' => $fullname,
                    'phone'     => $phone,
                    'username'  => $username,
                    'email'     => $email ?: null,
                    'password'  => Hash::make($password),
                    'id_card'   => $idCard ?: null,
                    'role'      => 'receptionist',
                    'is_active' => $isActive,
                ]);

                StaffProfile::create([
                    'user_id'        => $user->id,
                    'employee_code'  => $employeeCode,
                    'position'       => 'Lễ tân',
                    'department'     => $department,
                    'internal_phone' => $internalPhone ?: null,
                    'start_date'     => $startDate ?: null,
                    'is_active'      => $isActive,
                ]);

                $importedEmployeeCodes[] = $employeeCode;
            }

            // => DELETE/DEACTIVATE missing
        });
    }
}
