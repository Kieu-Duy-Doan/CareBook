<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\DoctorProfile;
use App\Models\Specialty;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Thống kê lượt khám theo trạng thái
     */
    public function getAppointmentStats(Carbon $dateFrom, Carbon $dateTo, ?int $doctorId = null, ?int $specialtyId = null): array
    {
        $query = Appointment::query()
            ->whereDate('appointment_date', '>=', $dateFrom)
            ->whereDate('appointment_date', '<=', $dateTo);

        if ($doctorId) {
            $query->where('doctor_profile_id', $doctorId);
        }
        if ($specialtyId) {
            $query->where('specialty_id', $specialtyId);
        }

        $total = (clone $query)->count();
        $completed = (clone $query)->where('status', 'completed')->count();
        $cancelled = (clone $query)->where('status', 'cancelled')->count();
        $absent = (clone $query)->where('status', 'absent')->count();
        $pending = (clone $query)->where('status', 'pending')->count();
        $examining = (clone $query)->whereIn('status', ['checked_in', 'examining'])->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'absent' => $absent,
            'pending' => $pending,
            'examining' => $examining,
        ];
    }

    /**
     * Thống kê lượt khám theo ca làm việc
     */
    public function getAppointmentsByShift(Carbon $dateFrom, Carbon $dateTo, ?int $doctorId = null, ?int $specialtyId = null): array
    {
        $query = Appointment::query()
            ->whereDate('appointment_date', '>=', $dateFrom)
            ->whereDate('appointment_date', '<=', $dateTo)
            ->where('status', 'completed');

        if ($doctorId) {
            $query->where('doctor_profile_id', $doctorId);
        }
        if ($specialtyId) {
            $query->where('specialty_id', $specialtyId);
        }

        $morning = (clone $query)->whereTime('appointment_time', '<', '12:00:00')->count();
        $afternoon = (clone $query)->whereTime('appointment_time', '>=', '12:00:00')->count();

        return [
            'morning' => $morning,
            'afternoon' => $afternoon,
        ];
    }

    /**
     * Thống kê lượt khám theo bác sĩ
     */
    public function getStatsByDoctor(Carbon $dateFrom, Carbon $dateTo, ?int $specialtyId = null): array
    {
        $query = Appointment::query()
            ->select(
                'doctor_profile_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled"),
                DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent"),
            )
            ->whereDate('appointment_date', '>=', $dateFrom)
            ->whereDate('appointment_date', '<=', $dateTo)
            ->groupBy('doctor_profile_id');

        if ($specialtyId) {
            $query->where('specialty_id', $specialtyId);
        }

        $results = $query->get();

        $doctorStats = [];
        foreach ($results as $row) {
            $doctor = DoctorProfile::with('user')->find($row->doctor_profile_id);
            if (!$doctor) continue;

            $doctorStats[] = [
                'doctor_name' => $doctor->user->full_name ?? 'N/A',
                'doctor_title' => $doctor->full_title ?? '',
                'specialty' => $doctor->specialties->first()->name ?? 'N/A',
                'total' => $row->total,
                'completed' => $row->completed,
                'cancelled' => $row->cancelled,
                'absent' => $row->absent,
            ];
        }

        usort($doctorStats, fn($a, $b) => $b['completed'] - $a['completed']);

        return $doctorStats;
    }

    /**
     * Thống kê lượt khám theo chuyên khoa
     */
    public function getStatsBySpecialty(Carbon $dateFrom, Carbon $dateTo): array
    {
        $results = Appointment::query()
            ->select(
                'specialty_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
            )
            ->whereDate('appointment_date', '>=', $dateFrom)
            ->whereDate('appointment_date', '<=', $dateTo)
            ->groupBy('specialty_id')
            ->get();

        $stats = [];
        foreach ($results as $row) {
            $specialty = Specialty::find($row->specialty_id);
            $stats[] = [
                'name' => $specialty->name ?? 'N/A',
                'total' => $row->total,
                'completed' => $row->completed,
            ];
        }

        usort($stats, fn($a, $b) => $b['total'] - $a['total']);

        return $stats;
    }

    /**
     * Thống kê doanh thu phân tách theo nguồn (BN trả / BHYT / Chờ thu)
     */
    public function getRevenueStats(Carbon $dateFrom, Carbon $dateTo, ?int $doctorId = null, ?int $specialtyId = null): array
    {
        // Query payments qua appointments trong khoảng ngày
        $paymentQuery = Payment::query()
            ->whereHas('appointment', function ($q) use ($dateFrom, $dateTo, $doctorId, $specialtyId) {
                $q->whereDate('appointment_date', '>=', $dateFrom)
                  ->whereDate('appointment_date', '<=', $dateTo);
                if ($doctorId) {
                    $q->where('doctor_profile_id', $doctorId);
                }
                if ($specialtyId) {
                    $q->where('specialty_id', $specialtyId);
                }
            });

        // Tổng doanh thu đã thanh toán hoàn tất
        $totalRevenue = (clone $paymentQuery)
            ->where('status', 'completed')
            ->sum('amount');

        // Doanh thu thực thu từ bệnh nhân (cash + qr)
        $patientRevenue = (clone $paymentQuery)
            ->where('status', 'completed')
            ->whereIn('method', ['cash', 'qr'])
            ->sum('amount');

        // Doanh thu BHYT chi trả
        $insuranceRevenue = (clone $paymentQuery)
            ->where('status', 'completed')
            ->where('method', 'insurance')
            ->sum('amount');

        // Chờ thu (pending clinical visits)
        $pendingRevenue = \App\Models\ClinicalVisit::where('payment_status', 'pending')
            ->whereHas('appointment', function ($q) use ($dateFrom, $dateTo, $doctorId, $specialtyId) {
                $q->whereDate('appointment_date', '>=', $dateFrom)
                  ->whereDate('appointment_date', '<=', $dateTo);
                if ($doctorId) {
                    $q->where('doctor_profile_id', $doctorId);
                }
                if ($specialtyId) {
                    $q->where('specialty_id', $specialtyId);
                }
            })
            ->sum('payment_amount');

        // Phân tách theo phương thức
        $cashRevenue = (clone $paymentQuery)
            ->where('status', 'completed')
            ->where('method', 'cash')
            ->sum('amount');

        $qrRevenue = (clone $paymentQuery)
            ->where('status', 'completed')
            ->where('method', 'qr')
            ->sum('amount');

        $waivedRevenue = (clone $paymentQuery)
            ->where('status', 'completed')
            ->where('method', 'waived')
            ->sum('amount');

        return [
            'total_revenue' => $totalRevenue,
            'patient_revenue' => $patientRevenue,
            'insurance_revenue' => $insuranceRevenue,
            'pending_revenue' => $pendingRevenue,
            'cash_revenue' => $cashRevenue,
            'qr_revenue' => $qrRevenue,
            'waived_revenue' => $waivedRevenue,
        ];
    }
}
