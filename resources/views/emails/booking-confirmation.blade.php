@extends('emails.layouts.base')

@section('content')
    <p>Xin chào <span class="highlight">{{ $appointment->patientProfile->full_name ?? 'quý khách' }}</span>,</p>
    
    <p>Cảm ơn bạn đã tin tưởng và đặt lịch khám tại Phòng khám CareBook.</p>
    
    <div class="box">
        <p style="margin-top: 0;"><strong>Thông tin lịch hẹn:</strong></p>
        <ul style="padding-left: 20px; margin-bottom: 0; line-height: 1.8;">
            <li>Mã lịch hẹn: <strong>{{ $appointment->appointment_code }}</strong></li>
            <li>Bác sĩ: <strong>{{ $appointment->doctorProfile->full_title ?? 'Chưa xác định' }}</strong></li>
            <li>Chuyên khoa: <strong>{{ $appointment->specialty->name ?? 'Chưa xác định' }}</strong></li>
            <li>Thời gian: <strong>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</strong> ngày <strong>{{ $appointment->appointment_date->format('d/m/Y') }}</strong></li>
            <li>Phòng khám: <strong>{{ $appointment->room->name ?? 'Sẽ xếp sau' }}</strong></li>
        </ul>
    </div>
    
    <p>Vui lòng có mặt tại quầy lễ tân trước 15 phút để làm thủ tục. Nếu bạn cần thay đổi lịch, vui lòng truy cập website và quản lý lịch hẹn của bạn.</p>
    
    <div class="btn-container">
        <a href="{{ url('/lich-hen') }}" class="btn">Xem Chi Tiết Lịch Hẹn</a>
    </div>
@endsection
