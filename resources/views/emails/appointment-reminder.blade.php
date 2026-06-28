@extends('emails.layouts.base')

@section('content')
    <p>Xin chào <span class="highlight">{{ $appointment->patientProfile->full_name ?? 'quý khách' }}</span>,</p>
    
    <p>Đây là tin nhắn nhắc nhở từ Phòng khám CareBook. Bạn có một lịch hẹn sắp diễn ra vào <strong style="color: #0d9488;">{{ $timeframeLabel ?? 'thời gian tới' }}</strong>.</p>
    
    <div class="box">
        <p style="margin-top: 0;"><strong>Thông tin lịch hẹn:</strong></p>
        <ul style="padding-left: 20px; margin-bottom: 0; line-height: 1.8;">
            <li>Mã lịch hẹn: <strong>{{ $appointment->appointment_code }}</strong></li>
            <li>Bác sĩ: <strong>{{ $appointment->doctorProfile->full_title ?? 'Chưa xác định' }}</strong></li>
            <li>Thời gian: <strong>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</strong> ngày <strong>{{ $appointment->appointment_date->format('d/m/Y') }}</strong></li>
            <li>Phòng khám: <strong>{{ $appointment->room->name ?? 'Liên hệ lễ tân' }}</strong></li>
        </ul>
    </div>
    
    <p>Vui lòng sắp xếp thời gian để có mặt đúng giờ. Nếu bạn không thể đến được, vui lòng gọi cho chúng tôi qua số Hotline ngay lập tức.</p>
    
    <div class="btn-container">
        <a href="{{ url('/lich-hen') }}" class="btn">Xem Lịch Hẹn</a>
    </div>
@endsection
