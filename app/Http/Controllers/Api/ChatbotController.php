<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatbotIntent;
use App\Models\ChatbotResponse;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\Faq;
use Illuminate\Support\Str;
use App\Services\ChatbotAIService;

// Api xử lý tin nhắn từ người dùng gửi tới Chatbot
class ChatbotController extends Controller
{
    protected ChatbotAIService $aiService;

    public function __construct(ChatbotAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function sendMessage(Request $request)
    {
        // Kiểm tra tính hợp lệ của tin nhắn
        $request->validate([
            'message' => 'required|string',
            'session_token' => 'nullable|string'
        ]);

        $messageText = trim($request->input('message'));
        $sessionToken = $request->input('session_token');
        $session = null;

        // Khôi phục phiên chat cũ nếu có
        if ($sessionToken) {
            $session = ChatSession::where('session_token', $sessionToken)->where('status', 'active')->first();
        }

        // Tạo phiên chat mới nếu chưa có
        if (!$session) {
            $sessionToken = (string) Str::uuid();
            $session = ChatSession::create([
                'session_token' => $sessionToken,
                'user_id' => auth()->guard('web')->id(),
                'status' => 'active'
            ]);
        }

        // Đẩy tin nhắn qua AI Service để phân tích và tạo câu trả lời
        $matchResult = $this->aiService->processMessage($messageText);
        
        // Lưu lại lịch sử tin nhắn của người dùng
        ChatMessage::create([
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $messageText,
            'intent_detected' => $matchResult['intent_name']
        ]);
        ChatMessage::create([
            'session_id' => $session->id,
            'role' => 'assistant',
            'content' => $matchResult['reply'],
            'metadata' => $matchResult['metadata']
        ]);

        return response()->json([
            'reply' => $matchResult['reply'],
            'metadata' => $matchResult['metadata'],
            'session_token' => $sessionToken
        ]);
    }
}
