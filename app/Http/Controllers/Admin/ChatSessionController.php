<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatSession;
use App\Models\ChatMessage;

class ChatSessionController extends Controller
{
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
    public function show($id)
    {
        $session = ChatSession::with('user')->findOrFail($id);

        $messages = ChatMessage::where('session_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.chatbot.sessions.show', compact('session', 'messages'));
    }
    public function toggleFlag($messageId)
    {
        $message = ChatMessage::findOrFail($messageId);
        $message->is_flagged = !$message->is_flagged;
        $message->save();

        return response()->json([
            'success' => true,
            'is_flagged' => $message->is_flagged
        ]);
    }
}
