<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

class MedicalLookupController extends Controller
{
    /**
     * Hiển thị trang tra cứu bệnh án công khai
     */
    public function index()
    {
        return view('medical-lookup.index');
    }

    /**
     * Xử lý tìm kiếm hồ sơ bệnh án bằng SĐT và Họ tên
     */
    public function search(Request $request)
    {
        // 1. Giới hạn tần suất tra cứu (chống Brute-force / Crawl dữ liệu)
        $ip = $request->ip();
        $rateLimitKey = 'medical-lookup:' . $ip;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->withInput()->with('error', "Bạn đã thao tác tra cứu quá nhiều lần. Vui lòng thử lại sau {$seconds} giây.");
        }
        RateLimiter::hit($rateLimitKey, 60);

        // 2. Validate đầu vào
        $validated = $request->validate([
            'phone'     => 'required|string|min:9|max:15',
            'full_name' => 'required|string|min:2|max:100',
        ], [
            'phone.required'     => 'Vui lòng nhập số điện thoại đã đăng ký khám.',
            'phone.min'          => 'Số điện thoại không hợp lệ.',
            'phone.max'          => 'Số điện thoại không hợp lệ.',
            'full_name.required' => 'Vui lòng nhập đầy đủ họ và tên bệnh nhân.',
            'full_name.min'      => 'Họ và tên quá ngắn.',
            'full_name.max'      => 'Họ và tên không được vượt quá 100 ký tự.',
        ]);

        // 3. Chuẩn hóa dữ liệu tìm kiếm
        $rawPhone = trim($validated['phone']);
        $digitsOnly = preg_replace('/[^0-9]/', '', $rawPhone);
        $normalizedPhone = $digitsOnly;
        if (str_starts_with($digitsOnly, '84') && strlen($digitsOnly) >= 10) {
            $normalizedPhone = '0' . substr($digitsOnly, 2);
        }

        $cleanedName = mb_strtolower(trim(preg_replace('/\s+/', ' ', $validated['full_name'])));

        // 4. Tìm kiếm hồ sơ bệnh nhân
        $profiles = PatientProfile::where(function ($q) use ($normalizedPhone, $rawPhone, $digitsOnly) {
            $q->where('phone', $normalizedPhone)
              ->orWhere('phone', $rawPhone)
              ->orWhere('phone', $digitsOnly);
        })
        ->whereRaw('LOWER(TRIM(full_name)) = ?', [$cleanedName])
        ->get();

        if ($profiles->isEmpty()) {
            return back()->withInput()->with('error', 'Không tìm thấy hồ sơ bệnh nhân nào khớp với thông tin đã nhập. Quý khách vui lòng kiểm tra lại Số điện thoại và Họ tên.');
        }

        // 5. Lấy danh sách lịch hẹn đã có bệnh án (completed / có medicalRecord)
        $appointments = Appointment::with([
            'specialty',
            'room',
            'doctorProfile.user',
            'medicalRecord.doctorProfile.user',
            'medicalRecord.prescription',
            'clinicalVisits',
            'patientProfile',
        ])
        ->whereIn('patient_profile_id', $profiles->pluck('id'))
        ->whereHas('medicalRecord')
        ->orderByDesc('appointment_date')
        ->orderByDesc('appointment_time')
        ->get();

        if ($appointments->isEmpty()) {
            return back()->withInput()->with('warning', 'Đã tìm thấy thông tin bệnh nhân (' . e($profiles->first()->full_name) . '), tuy nhiên chưa có lượt khám nào có hồ sơ kết luận bệnh án được hoàn tất.');
        }

        // 6. Tạo Signed URL có hiệu lực trong 45 phút cho mỗi ca khám
        $appointments->each(function ($appointment) {
            $appointment->signed_url = URL::temporarySignedRoute(
                'medical-lookup.detail',
                now()->addMinutes(45),
                ['code' => $appointment->appointment_code]
            );
        });

        // 7. Che bớt số điện thoại vì lý do bảo mật
        $phoneDisplay = $rawPhone;
        if (strlen($digitsOnly) >= 7) {
            $phoneDisplay = substr($digitsOnly, 0, 3) . '****' . substr($digitsOnly, -3);
        }

        return view('medical-lookup.index', [
            'appointments'  => $appointments,
            'patientName'   => $profiles->first()->full_name,
            'maskedPhone'   => $phoneDisplay,
            'searched'      => true,
            'searchPhone'   => $rawPhone,
            'searchName'    => $validated['full_name'],
        ]);
    }

    /**
     * Xem chi tiết hồ sơ bệnh án (yêu cầu Signed URL hợp lệ trong 45 phút)
     */
    public function showDetail(Request $request, $code)
    {
        // 1. Kiểm tra chữ ký số Signed URL & thời hạn 45 phút
        if (!$request->hasValidSignature()) {
            return redirect()->route('medical-lookup.index')->with(
                'error',
                'Đường dẫn xem chi tiết bệnh án đã hết hạn (sau 45 phút) hoặc không hợp lệ. Để bảo mật thông tin y tế cá nhân, quý khách vui lòng nhập lại số điện thoại và họ tên để tra cứu lại.'
            );
        }

        // 2. Tải chi tiết lịch hẹn và hồ sơ bệnh án
        $appointment = Appointment::with([
            'patientProfile',
            'specialty',
            'room',
            'doctorProfile.user',
            'medicalRecord.doctorProfile.user',
            'medicalRecord.assistant',
            'medicalRecord.prescription',
            'clinicalVisits.doctorProfile.user',
            'clinicalVisits.room',
        ])
        ->where('appointment_code', $code)
        ->whereHas('medicalRecord')
        ->first();

        if (!$appointment) {
            return redirect()->route('medical-lookup.index')->with(
                'error',
                'Không tìm thấy hồ sơ bệnh án tương ứng với mã lượt khám này.'
            );
        }

        return view('medical-lookup.detail', compact('appointment'));
    }
}
