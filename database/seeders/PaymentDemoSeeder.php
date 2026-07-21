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
        $receptionist = User::where('role', 'receptionist')->first();

        // Lấy danh sách các ClinicalVisit đã được tạo từ AppointmentSeeder (status = completed)
        $completedVisits = ClinicalVisit::where('status', 'completed')
            ->where('payment_status', 'pending')
            ->get();

        foreach ($completedVisits as $visit) {
            $method = rand(0, 1) ? 'cash' : 'qr';
            $amount = $visit->payment_amount;

            if ($amount <= 0) continue;

            $visit->update([
                'payment_status' => 'paid',
                'payment_method' => $method,
                'collected_by' => $receptionist->id ?? null,
                'paid_at' => now()->subMinutes(rand(10, 120)),
            ]);

            $payment = Payment::create([
                'appointment_id' => $visit->appointment_id,
                'transaction_code' => ($method === 'qr' ? 'SEP' : 'CASH') . '-' . strtoupper(Str::random(8)),
                'amount' => $amount,
                'method' => $method,
                'status' => 'completed',
                'collected_by' => $receptionist->id ?? null,
                'paid_at' => $visit->paid_at,
                'note' => 'Thanh toán demo',
            ]);

            // Map pivot
            $payment->clinicalVisits()->attach($visit->id, [
                'amount_allocated' => $amount
            ]);
        }
    }
}
