<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $token,
        protected string $loginType = 'patient',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
            'login_type' => $this->loginType,
        ], false));

        $expire = config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Đặt lại mật khẩu - CareBook')
            ->greeting('Xin chào ' . ($notifiable->full_name ?: 'bạn') . '!')
            ->line('Bạn nhận được email này vì chúng tôi vừa nhận yêu cầu đặt lại mật khẩu cho tài khoản CareBook của bạn.')
            ->action('Đặt lại mật khẩu', $url)
            ->line("Liên kết có hiệu lực trong {$expire} phút.")
            ->line('Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.');
    }
}
