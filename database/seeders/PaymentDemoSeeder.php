<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\ClinicalVisit;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Lấy ra vài Appointment (ưu tiên các appointment hôm nay hoặc gần đây)
        $appointments = Appointment::latest()->take(5)->get();
        $receptionist = User::where('role', 'receptionist')->first();

        if ($appointments->isEmpty()) {
            return;
        }

        // 2. Tạo dữ liệu CHỜ THANH TOÁN (Pending) cho 3 Appointment đầu tiên
        $pendingAppointments = $appointments->take(3);
        foreach ($pendingAppointments as $appointment) {
            $services = [
                ['name' => 'Khám chuyên khoa', 'amount' => 2000],
                ['name' => 'Xét nghiệm máu tổng quát', 'amount' => 3000],
                ['name' => 'Siêu âm ổ bụng', 'amount' => 5000],
            ];

            // Chọn ngẫu nhiên 1-3 dịch vụ
            $selectedServices = array_slice($services, 0, rand(1, 3));

            foreach ($selectedServices as $index => $service) {
                ClinicalVisit::create([
                    'appointment_id' => $appointment->id,
                    'doctor_profile_id' => $appointment->doctor_profile_id,
                    'room_id' => $appointment->room_id,
                    'visit_order' => $index + 1,
                    'is_origin' => ($index === 0),
                    'findings' => 'Chỉ định: ' . $service['name'],
                    'status' => 'waiting', // Trạng thái khám
                    'payment_amount' => $service['amount'],
                    'payment_status' => 'pending', // Trạng thái thanh toán
                    'payment_method' => null,
                ]);
            }
        }

        // 3. Tạo dữ liệu LỊCH SỬ GIAO DỊCH (Paid) cho 2 Appointment tiếp theo
        $paidAppointments = $appointments->slice(3, 2);
        foreach ($paidAppointments as $appointment) {
            $amount = rand(2, 10) * 1000; // 2k - 10k
            $method = rand(0, 1) ? 'cash' : 'qr';

            $visit = ClinicalVisit::create([
                'appointment_id' => $appointment->id,
                'doctor_profile_id' => $appointment->doctor_profile_id,
                'room_id' => $appointment->room_id,
                'visit_order' => 1,
                'is_origin' => true,
                'findings' => 'Khám và kê đơn',
                'status' => 'completed',
                'payment_amount' => $amount,
                'payment_status' => 'paid',
                'payment_method' => $method,
                'collected_by' => $receptionist->id ?? null,
                'paid_at' => now()->subMinutes(rand(10, 120)),
            ]);

            $payment = Payment::create([
                'appointment_id' => $appointment->id,
                'transaction_code' => ($method === 'qr' ? 'SEP' : 'CASH') . '-' . strtoupper(Str::random(6)),
                'amount' => $amount,
                'method' => $method,
                'status' => 'completed',
                'collected_by' => $receptionist->id ?? null,
                'paid_at' => $visit->paid_at,
                'note' => 'Thanh toán demo seeder',
            ]);

            // Map pivot
            $payment->clinicalVisits()->attach($visit->id, [
                'amount_allocated' => $amount
            ]);
        }
    }
}
