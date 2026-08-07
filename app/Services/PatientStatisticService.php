<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\ClinicalVisit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PatientStatisticService
{
    public function getStatistics(int $userId, array $filters)
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? date('Y');

        $query = Appointment::where('booked_by_user_id', $userId);
        
        if ($startDate && $endDate) {
            $query->whereBetween('appointment_date', [$startDate, $endDate]);
        } elseif ($month) {
            $query->whereMonth('appointment_date', $month)
                  ->whereYear('appointment_date', $year);
        } else {
            $query->whereYear('appointment_date', $year);
        }

        $totalAppointments = (clone $query)->count();
        $completedAppointments = (clone $query)->where('status', 'completed')->count();
        $cancelledAppointments = (clone $query)->where('status', 'cancelled')->count();

        // Get payments for the filtered appointments
        $appointmentIds = $query->pluck('id');
        
        $payments = Payment::whereIn('appointment_id', $appointmentIds)
            ->where('status', 'completed')
            ->get();

        $totalPaid = $payments->sum('amount');
        $cashPaid = $payments->where('method', 'cash')->sum('amount');
        $qrPaid = $payments->where('method', 'qr')->sum('amount');
        
        // Calculate BHYT
        $visits = ClinicalVisit::whereIn('appointment_id', $appointmentIds)
            ->where('payment_status', 'paid')
            ->get();
            
        $totalBaseCost = $visits->sum('payment_amount');
        $totalBHYT = max(0, $totalBaseCost - $totalPaid);

        // Chart Data
        $chartLabels = [];
        $appointmentData = [];
        $revenueData = [];

        // If filtering by date range <= 31 days or by month, group by day, else group by month
        $isDaily = false;
        if ($startDate && $endDate) {
            $diff = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));
            if ($diff <= 31) $isDaily = true;
        } elseif ($month) {
            $isDaily = true;
        }

        if ($isDaily) {
            $appointmentsByDate = (clone $query)
                ->select(DB::raw('DATE(appointment_date) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->pluck('count', 'date');
                
            $paymentsByDate = Payment::whereIn('appointment_id', $appointmentIds)
                ->where('status', 'completed')
                ->select(DB::raw('DATE(paid_at) as date'), DB::raw('SUM(amount) as total'))
                ->groupBy('date')
                ->pluck('total', 'date');
                
            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate);
                $end = Carbon::parse($endDate);
            } else {
                $start = Carbon::createFromDate($year, $month, 1);
                $end = $start->copy()->endOfMonth();
            }
            
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $dateStr = $d->format('Y-m-d');
                $chartLabels[] = $d->format('d/m');
                $appointmentData[] = $appointmentsByDate->get($dateStr, 0);
                $revenueData[] = $paymentsByDate->get($dateStr, 0);
            }
        } else {
            $appointmentsByMonth = (clone $query)
                ->select(DB::raw('MONTH(appointment_date) as month'), DB::raw('count(*) as count'))
                ->groupBy('month')
                ->pluck('count', 'month');
                
            $paymentsByMonth = Payment::whereIn('appointment_id', $appointmentIds)
                ->where('status', 'completed')
                ->select(DB::raw('MONTH(paid_at) as month'), DB::raw('SUM(amount) as total'))
                ->groupBy('month')
                ->pluck('total', 'month');

            for ($m = 1; $m <= 12; $m++) {
                $chartLabels[] = "Tháng $m";
                $appointmentData[] = $appointmentsByMonth->get($m, 0);
                $revenueData[] = $paymentsByMonth->get($m, 0);
            }
        }

        $paymentMethodChart = [
            'labels' => ['Tiền mặt', 'Chuyển khoản QR'],
            'data' => [$cashPaid, $qrPaid]
        ];

        return compact(
            'totalAppointments',
            'completedAppointments',
            'cancelledAppointments',
            'totalPaid',
            'cashPaid',
            'qrPaid',
            'totalBHYT',
            'chartLabels',
            'appointmentData',
            'revenueData',
            'paymentMethodChart',
            'startDate',
            'endDate',
            'month',
            'year'
        );
    }
}
