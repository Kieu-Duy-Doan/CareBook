@extends('emails.layouts.base')

@section('content')
    <p>Xin chào <span class="highlight">{{ $medicalRecord->appointment->patientProfile->full_name ?? 'quý khách' }}</span>,</p>

    <p>Phòng khám CareBook xin nhắc nhở bạn rằng <strong>ngày mai</strong> là thời điểm bạn được bác sĩ hẹn <strong>tái khám</strong>. Vui lòng sắp xếp thời gian để có thể đến khám đúng hẹn.</p>

    <div class="box">
        <p style="margin-top: 0;"><strong>Thông tin tái khám:</strong></p>
        <ul style="padding-left: 20px; margin-bottom: 0; line-height: 1.8;">
            <li>Ngày tái khám: <strong>{{ $medicalRecord->followup_date?->format('d/m/Y') ?? '—' }}</strong></li>
            <li>Bác sĩ phụ trách: <strong>{{ $medicalRecord->appointment->doctorProfile->full_title ?? 'Chưa xác định' }}</strong></li>
            @if($medicalRecord->diagnosis)
                <li>Chẩn đoán trước đó: <strong>{{ $medicalRecord->diagnosis }}</strong></li>
            @endif
            @if($medicalRecord->advice)
                <li>Lời dặn dò: <em>{{ $medicalRecord->advice }}</em></li>
            @endif
            <li>Mã lịch hẹn gốc: <strong>{{ $medicalRecord->appointment->appointment_code ?? '—' }}</strong></li>
        </ul>
    </div>

    <p>Nếu bạn chưa đặt lịch tái khám, hãy đặt lịch ngay để đảm bảo sức khỏe của bạn được theo dõi liên tục.</p>

    <div class="btn-container">
        <a href="{{ url('/dat-lich') }}" class="btn">Đặt Lịch Tái Khám</a>
    </div>

    <p style="font-size: 14px; color: #64748b;">Nếu bạn không thể đến, vui lòng liên hệ hotline để được hỗ trợ đổi lịch.</p>
@endsection
