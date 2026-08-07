<?php

namespace App\Services\Security;

class ChatbotDataSanitizer
{
    /**
     * Chuyển mảng dữ liệu thành chuỗi text an toàn cho AI đọc.
     * Chặn hiển thị các trường nhạy cảm dù có query nhầm.
     */
    public function formatDataList(array $data, string $title = 'Kết quả truy vấn'): string
    {
        $blockedKeys = ['password', 'token', 'remember_token', 'cccd', 'id_card', 'phone', 'email', 'secret'];
        
        $output = "[$title]:\n";
        
        foreach ($data as $index => $item) {
            $output .= "- Mục " . ($index + 1) . ": ";
            if (is_array($item)) {
                $details = [];
                foreach ($item as $key => $value) {
                    $lowerKey = strtolower($key);
                    $isBlocked = false;
                    foreach ($blockedKeys as $bKey) {
                        if (str_contains($lowerKey, $bKey)) {
                            $isBlocked = true;
                            break;
                        }
                    }

                    if (!$isBlocked && !is_null($value) && $value !== '') {
                        $details[] = "$key: $value";
                    }
                }
                $output .= implode(', ', $details) . "\n";
            } else {
                $output .= "$item\n";
            }
        }

        return $output;
    }
}
