<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatSession;
use App\Models\ChatMessage;

// Quản lý và theo dõi các phiên trò chuyện của người dùng với Chatbot
class ChatSessionController extends Controller
{
    // Hiển thị danh sách các phiên trò chuyện (có hỗ trợ lọc theo trạng thái, cắm cờ)
    public function index(Request $request)
    {
        $query = ChatSession::with('user')->withCount('messages')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_flagged')) {
            $query->whereHas('messages', function ($q) {
                $q->where('is_flagged', true);
            });
        }

        $sessions = $query->paginate(20)->withQueryString();

        return view('admin.chatbot.sessions.index', compact('sessions'));
    }
    // Xem chi tiết toàn bộ nội dung tin nhắn trong một phiên chat
    public function show($id)
    {
        $session = ChatSession::with('user')->findOrFail($id);

        $messages = ChatMessage::where('session_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.chatbot.sessions.show', compact('session', 'messages'));
    }
    // Đánh dấu (cắm cờ) tin nhắn có nội dung cần theo dõi đặc biệt
    public function toggleFlag(Request $request, $messageId)
    {
        // Chặn quyền truy cập nếu không phải Admin
        abort_if(!$request->user()->isAdmin(), 403, 'Unauthorized access.');

        $message = ChatMessage::findOrFail($messageId);
        $message->is_flagged = !$message->is_flagged;
        $message->save();

        return response()->json([
            'success' => true,
            'is_flagged' => $message->is_flagged
        ]);
    }
}
