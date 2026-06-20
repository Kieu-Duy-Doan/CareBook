<?php

namespace App\Enums;

// Quản lý danh sách các hành động (Action) mà Chatbot có thể thực thi
enum ChatbotActionEnum: string
{
    case FAQ_LOOKUP = 'faq_lookup';
    case GUIDE_BOOKING = 'guide_booking';
    case INTRODUCE_SPECIALTY = 'introduce_specialty';
    case TRANSFER_STAFF = 'transfer_staff';

    public function label(): string
    {
        return match($this) {
            self::FAQ_LOOKUP => 'Tra cứu FAQ',
            self::GUIDE_BOOKING => 'Hướng dẫn đặt khám',
            self::INTRODUCE_SPECIALTY => 'Giới thiệu chuyên khoa',
            self::TRANSFER_STAFF => 'Chuyển nhân viên',
        };
    }
}
