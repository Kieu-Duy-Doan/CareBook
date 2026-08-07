<?php

namespace App\Services;

use App\Models\Specialty;
use App\Models\DoctorProfile;
use App\Models\WorkSchedule;
use App\Models\DoctorLevelFee;
use App\Services\Security\ChatbotDataSanitizer;

class ChatbotToolsService
{
    protected ChatbotDataSanitizer $sanitizer;

    public function __construct(ChatbotDataSanitizer $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }

    public function getSpecialties(): string
    {
        $specialties = Specialty::where('is_active', true)
            ->select('name', 'description')
            ->get();
            
        if ($specialties->isEmpty()) {
            return "Hiện tại phòng khám chưa cập nhật danh sách chuyên khoa.";
        }

        return $this->sanitizer->formatDataList($specialties->toArray(), 'Danh sách chuyên khoa');
    }

    public function getDoctorInfo(?string $doctorName = null, ?string $specialtyName = null): string
    {
        $query = DoctorProfile::with(['user:id,full_name', 'specialties:id,name']);

        if ($doctorName) {
            $query->whereHas('user', function ($q) use ($doctorName) {
                $q->where('full_name', 'like', "%{$doctorName}%");
            });
        }

        if ($specialtyName) {
            $query->whereHas('specialties', function ($q) use ($specialtyName) {
                $q->where('name', 'like', "%{$specialtyName}%");
            });
        }

        $doctors = $query->get()->map(function ($doc) {
            return [
                'Tên bác sĩ' => $doc->full_title,
                'Kinh nghiệm' => $doc->experience_years ? $doc->experience_years . ' năm' : 'Chưa cập nhật',
                'Chuyên khoa' => $doc->primary_specialty ? $doc->primary_specialty->name : 'Đa khoa',
                'Mô tả' => $doc->bio
            ];
        });

        if ($doctors->isEmpty()) {
            return "Không tìm thấy thông tin bác sĩ phù hợp với yêu cầu.";
        }

        return $this->sanitizer->formatDataList($doctors->toArray(), 'Thông tin bác sĩ');
    }

    public function getDoctorSchedule(?string $doctorName = null, ?string $dayOfWeek = null): string
    {
        // 1: Sunday, 2: Monday, ..., 7: Saturday
        $query = WorkSchedule::where('is_active', true)
            ->with(['doctorProfile.user:id,full_name']);

        if ($doctorName) {
            $query->whereHas('doctorProfile.user', function ($q) use ($doctorName) {
                $q->where('full_name', 'like', "%{$doctorName}%");
            });
        }

        if ($dayOfWeek) {
            $dayMapping = [
                'chủ nhật' => 1, 'thu 2' => 2, 'thứ hai' => 2, 'thứ 2' => 2,
                'thu 3' => 3, 'thứ ba' => 3, 'thứ 3' => 3,
                'thu 4' => 4, 'thứ tư' => 4, 'thứ 4' => 4,
                'thu 5' => 5, 'thứ năm' => 5, 'thứ 5' => 5,
                'thu 6' => 6, 'thứ sáu' => 6, 'thứ 6' => 6,
                'thu 7' => 7, 'thứ bảy' => 7, 'thứ 7' => 7,
            ];
            $dayLower = mb_strtolower($dayOfWeek, 'UTF-8');
            if (isset($dayMapping[$dayLower])) {
                $query->where('day_of_week', $dayMapping[$dayLower]);
            }
        }

        $schedules = $query->get()->map(function ($sch) {
            return [
                'Bác sĩ' => $sch->doctorProfile->full_title ?? 'N/A',
                'Ngày làm việc' => $sch->day_name,
                'Khung giờ' => $sch->time_range,
                'Ca' => $sch->shift_label === 'morning' ? 'Sáng' : ($sch->shift_label === 'afternoon' ? 'Chiều' : 'Tối'),
            ];
        });

        if ($schedules->isEmpty()) {
            return "Không tìm thấy lịch làm việc phù hợp.";
        }

        return $this->sanitizer->formatDataList($schedules->toArray(), 'Lịch làm việc của bác sĩ');
    }

    public function getConsultationFees(): string
    {
        $fees = DoctorLevelFee::select('level', 'base_price', 'specific_price')->get()->map(function ($fee) {
            $levelName = match($fee->level) {
                'BS'    => 'Bác sĩ',
                'BSCK1' => 'Bác sĩ CK1',
                'BSCK2' => 'Bác sĩ CK2',
                'ThS'   => 'Thạc sĩ',
                'TS'    => 'Tiến sĩ',
                'PGS'   => 'Phó Giáo sư',
                'GS'    => 'Giáo sư',
                default => $fee->level,
            };
            return [
                'Cấp bậc bác sĩ' => $levelName,
                'Giá khám cơ bản' => number_format($fee->base_price, 0, ',', '.') . ' VNĐ',
                'Giá khám theo yêu cầu' => $fee->specific_price ? number_format($fee->specific_price, 0, ',', '.') . ' VNĐ' : 'Liên hệ'
            ];
        });

        if ($fees->isEmpty()) {
            return "Chưa có thông tin bảng giá dịch vụ.";
        }

        return $this->sanitizer->formatDataList($fees->toArray(), 'Bảng giá dịch vụ khám');
    }
}
