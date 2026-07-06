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
                $username     = trim($row[3] ?? '');
                $passwordInput= trim($row[4] ?? '');
                $idCard       = trim($row[5] ?? '');
                $email        = trim($row[6] ?? '');
                $statusInput  = trim($row[7] ?? '');
                $position     = trim($row[8] ?? '');
                $deptInput    = trim($row[9] ?? '');
                $internalPhone= trim($row[10] ?? '');
                $startDate    = trim($row[11] ?? '');

                if (empty($username) || empty($fullname)) {
                    continue;
                }

                // Xử lý chuẩn hoá ngày tháng từ Excel
                if ($startDate === '') {
                    $startDate = null;
                } elseif (is_numeric($startDate)) {
                    try {
                        $startDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($startDate)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $startDate = null;
                    }
                } elseif ($startDate) {
                    try {
                        $startDate = date('Y-m-d', strtotime($startDate));
                    } catch (\Exception $e) {
                        $startDate = null;
                    }
                }

                $isActive = true;
                if ($statusInput !== '') {
                    $val = mb_strtolower($statusInput);
                    $isActive = ($val === 'đang hoạt động' || $val === '1' || $val === 'true' || $val === 'active');
                }

                $department = $deptInput ?: 'Tiếp nhận bệnh nhân';
                $pos = $position ?: 'Lễ tân';

                $existingStaff = null;
                if ($employeeCode) {
                    $existingStaff = StaffProfile::where('employee_code', $employeeCode)->first();
                }

                if ($existingStaff) {
                    $user = $existingStaff->user;
                    
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

                    $existingStaff->fill([
                        'position'       => $pos,
                        'department'     => $department,
                        'internal_phone' => $internalPhone ?: null,
                        'start_date'     => $startDate ?: null,
                        'is_active'      => $isActive,
                    ]);
                    $staffDirty = $existingStaff->isDirty();
                    if ($staffDirty) {
                        $existingStaff->save();
                    }

                    if (!$userDirty && $staffDirty) {
                        $user->touch();
                    }

                    $importedEmployeeCodes[] = $employeeCode;
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
                        throw new \Exception("Dòng " . ($index + 1) . ": Số CMND/CCCD '$idCard' đã tồn tại trong hệ thống.");
                    }
                    if ($employeeCode && StaffProfile::where('employee_code', $employeeCode)->exists()) {
                        throw new \Exception("Dòng " . ($index + 1) . ": Mã nhân viên '$employeeCode' đã tồn tại trong hệ thống.");
                    }

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
                        'position'       => $pos,
                        'department'     => $department,
                        'internal_phone' => $internalPhone ?: null,
                        'start_date'     => $startDate ?: null,
                        'is_active'      => $isActive,
                    ]);

                    $importedEmployeeCodes[] = $employeeCode;
                }
            }
        });
    }
}
