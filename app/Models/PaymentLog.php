<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLog extends Model
{
    use HasFactory;

    protected $table = 'payment_logs';

    protected $fillable = [
        'appointment_id',
        'payment_id',
        'user_id',
        'ip_address',
        'action',
        'payload',
        'message',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper để tạo log kèm context người dùng hiện tại
     */
    public static function record(string $action, string $message, string $status = 'info', array $extra = []): self
    {
        return self::create(array_merge([
            'action' => $action,
            'message' => $message,
            'status' => $status,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
        ], $extra));
    }

    /**
     * Lấy tên tiếng Việt cho các action
     */
    public function getActionLabelAttribute(): string
    {
        $map = [
            'sync_sepay_success' => 'Đồng bộ SePay thành công',
            'sync_sepay_pages' => 'Kéo trang dữ liệu SePay',
            'sync_sepay_error' => 'Lỗi đồng bộ SePay',
            'reconciliation_run' => 'Chạy đối soát tự động/thủ công',
            'manual_match' => 'Khớp giao dịch thủ công',
            'webhook_sepay' => 'Nhận Webhook từ SePay',
            'payment_created' => 'Tạo thanh toán',
            'payment_approved' => 'Xác nhận thanh toán',
            'cash_payment' => 'Thanh toán tiền mặt',
            'zero_fee_payment' => 'Thanh toán 0đ / Miễn phí',
            'refund_request_created' => 'Tạo yêu cầu hoàn tiền',
            'refund_requested' => 'Yêu cầu hoàn tiền',
            'refund_reviewed' => 'Xử lý yêu cầu hoàn tiền',
        ];

        return $map[$this->action] ?? $this->action;
    }
}
