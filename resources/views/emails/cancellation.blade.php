@extends('emails.layouts.base')

@section('content')
    <p>Xin chào <span class="highlight">{{ $appointment->patientProfile->full_name ?? 'quý khách' }}</span>,</p>
    
    @if($actor === 'patient')
        <p>Hệ thống ghi nhận bạn đã <strong style="color: #64748b;">HUỶ THÀNH CÔNG</strong> lịch hẹn sau:</p>
    @else
        <p>Chúng tôi rất lấy làm tiếc phải thông báo rằng lịch hẹn của bạn đã bị <strong style="color: #ef4444;">HUỶ BỎ</strong> vì lý do đột xuất từ phía Bác sĩ.</p>
    @endif
    
    <div class="box" style="border-left: 4px solid {{ $actor === 'patient' ? '#64748b' : '#ef4444' }};">
        <p style="margin-top: 0;"><strong>Thông tin lịch hẹn bị huỷ:</strong></p>
        <ul style="padding-left: 20px; margin-bottom: 0; line-height: 1.8;">
            <li>Mã lịch hẹn: <strong>{{ $appointment->appointment_code }}</strong></li>
            <li>Bác sĩ: <strong>{{ $appointment->doctorProfile->full_title ?? 'Chưa xác định' }}</strong></li>
            <li>Thời gian: <strong>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</strong> ngày <strong>{{ $appointment->appointment_date->format('d/m/Y') }}</strong></li>
            @if($actor !== 'patient')
                <li>Lý do huỷ: <strong>{{ $appointment->reason ?? 'Bác sĩ có lịch đột xuất không thể thay đổi.' }}</strong></li>
            @endif
        </ul>
    </div>

    @if($actor !== 'patient')
        @if(count($alternativeDoctors) > 0)
            <p>Để không làm gián đoạn việc thăm khám của bạn, chúng tôi xin gợi ý một số Bác sĩ chuyên khoa <strong>{{ $appointment->specialty->name ?? '' }}</strong> có lịch trống vào các ngày sắp tới:</p>
            
            @foreach($alternativeDoctors as $doc)
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; padding: 16px; border-radius: 6px; margin-bottom: 16px;">
                    <p style="margin: 0 0 8px 0; font-size: 18px; font-weight: bold; color: #0f172a;">{{ $doc->full_title ?? $doc['full_title'] }}</p>
                    <p style="margin: 0 0 16px 0; font-size: 14px; color: #64748b;">Có lịch vào ngày: <strong>{{ \Carbon\Carbon::parse($doc->alternative_date ?? $doc['alternative_date'])->format('d/m/Y') }}</strong></p>
                    
                    <a href="{{ url('/dat-lich?doctor_id=' . ($doc->id ?? $doc['id'])) }}" style="display: inline-block; background-color: #3b82f6; color: #ffffff; text-decoration: none; padding: 10px 20px; font-size: 16px; font-weight: bold; border-radius: 6px;">
                        Đặt lịch với Bác sĩ này
                    </a>
                </div>
            @endforeach
            
            <p>Hoặc bạn có thể tự xem toàn bộ danh sách để chọn lịch phù hợp nhất:</p>
        @else
            <p>Hiện tại chúng tôi không tìm thấy Bác sĩ cùng chuyên khoa có lịch rảnh trong 3 ngày tới. Rất mong bạn thông cảm và vui lòng chọn một ngày khác.</p>
        @endif
        
        <div class="btn-container">
            <a href="{{ url('/dat-lich?specialty_id=' . $appointment->specialty_id) }}" class="btn">Xem Tất Cả Bác Sĩ</a>
        </div>
    @else
        <p>Cảm ơn bạn đã thông báo. Nếu bạn có nhu cầu đặt lịch khác, vui lòng truy cập hệ thống của chúng tôi.</p>
        <div class="btn-container">
            <a href="{{ url('/dat-lich') }}" class="btn">Đặt Lịch Khám Mới</a>
        </div>
    @endif
@endsection
